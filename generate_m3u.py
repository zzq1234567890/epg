#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import re
import sys
import time
from pathlib import Path
from urllib.request import urlopen, Request

try:
    import yt_dlp
except ImportError:
    print("請先安裝 yt-dlp: pip install -U yt-dlp")
    sys.exit(1)

# ==================== 設定區 ====================
SOURCE_URL = "https://raw.githubusercontent.com/zzq1234567890/epg/refs/heads/main/youtubeworld.m3u"
OUTPUT_FILE = Path("youtubeworld_m3u8.m3u")   # 輸出檔案名稱（可自行修改）
COOKIES_FILE = "cookies.txt"                   # cookies 檔案

# yt-dlp 選項
YDL_OPTS = {
    "quiet": True,
    "no_warnings": True,
    "skip_download": True,
    "format": "best[protocol^=m3u8]/bestaudio[protocol^=m3u8]/best",
    "cookiefile": COOKIES_FILE,
    "nocheckcertificate": True,
    "geo_bypass": True,
    "sleep_interval": 1,
    "max_sleep_interval": 3,
    "extractor_args": {
        "youtube": {
            "player_client": ["android", "web"],
        }
    },
}
# ==============================================

def fetch_source_m3u(url: str) -> str:
    req = Request(url, headers={
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
    })
    with urlopen(req, timeout=30) as resp:
        return resp.read().decode("utf-8", errors="ignore")

def parse_m3u(content: str):
    """解析 m3u，回傳 list of dict: {extinf, url}"""
    entries = []
    lines = content.splitlines()
    i = 0
    while i < len(lines):
        line = lines[i].strip()
        if line.startswith("#EXTINF"):
            extinf = line
            if i + 1 < len(lines):
                url = lines[i + 1].strip()
                if url and not url.startswith("#"):
                    entries.append({"extinf": extinf, "url": url})
                    i += 2
                    continue
        i += 1
    return entries

def get_m3u8_url(youtube_url: str) -> str | None:
    """用 yt-dlp 提取 m3u8 直鏈"""
    try:
        with yt_dlp.YoutubeDL(YDL_OPTS) as ydl:
            info = ydl.extract_info(youtube_url, download=False)
            if not info:
                return None

            formats = info.get("formats") or []

            # 優先找真正的 m3u8 / hls
            hls_formats = []
            for f in formats:
                url = f.get("url") or ""
                protocol = f.get("protocol") or ""
                if (
                    protocol in ("m3u8", "m3u8_native")
                    or ".m3u8" in url
                ):
                    hls_formats.append(f)

            if hls_formats:
                # 按畫質排序（高到低）
                hls_formats.sort(
                    key=lambda x: (x.get("height") or 0, x.get("tbr") or 0),
                    reverse=True
                )
                return hls_formats[0].get("url")

            # 退而求其次
            if info.get("url") and ".m3u8" in str(info.get("url")):
                return info["url"]
            if info.get("manifest_url"):
                return info["manifest_url"]

            return None

    except Exception as e:
        print(f"  [失敗] {youtube_url} -> {e}", file=sys.stderr)
        return None

def main():
    # 檢查 cookies 是否存在
    if not Path(COOKIES_FILE).exists():
        print(f"錯誤：找不到 {COOKIES_FILE}，請確認 workflow 有正確寫入 cookies")
        sys.exit(1)

    print("正在下載來源 m3u...")
    try:
        source = fetch_source_m3u(SOURCE_URL)
    except Exception as e:
        print(f"下載來源 m3u 失敗: {e}")
        sys.exit(1)

    entries = parse_m3u(source)
    print(f"共解析到 {len(entries)} 個節目\n")

    success = 0
    failed = 0
    results = []

    for idx, entry in enumerate(entries, 1):
        yt_url = entry["url"]
        print(f"[{idx}/{len(entries)}] 處理: {yt_url}")

        m3u8 = get_m3u8_url(yt_url)
        if m3u8:
            results.append({
                "extinf": entry["extinf"],
                "url": m3u8
            })
            success += 1
            print("  → 成功取得 m3u8")
        else:
            failed += 1
            print("  → 跳過（無法取得 m3u8）")

        time.sleep(0.6)  # 避免請求過快

    # 寫入新 m3u
    with open(OUTPUT_FILE, "w", encoding="utf-8") as f:
        f.write("#EXTM3U\n")
        f.write("# 自動由 yt-dlp 提取 m3u8\n")
        f.write(f"# 來源: {SOURCE_URL}\n")
        f.write(f"# 更新時間: {time.strftime('%Y-%m-%d %H:%M:%S')}\n")
        f.write(f"# 成功: {success} / 總數: {len(entries)}\n\n")

        for item in results:
            f.write(item["extinf"] + "\n")
            f.write(item["url"] + "\n")

    print(f"\n========== 完成 ==========")
    print(f"成功: {success}")
    print(f"失敗: {failed}")
    print(f"輸出檔案: {OUTPUT_FILE.resolve()}")

if __name__ == "__main__":
    main()
