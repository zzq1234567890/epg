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

SOURCE_URL = "https://raw.githubusercontent.com/zzq1234567890/epg/refs/heads/main/youtubeworld.m3u"
OUTPUT_DIR = Path("output")
OUTPUT_FILE = OUTPUT_DIR / "youtubeworld_m3u8.m3u"

# yt-dlp 選項：只取 HLS，優先最高畫質，不要下載
YDL_OPTS = {
    "quiet": True,
    "no_warnings": True,
    "skip_download": True,
    "extract_flat": False,
    "format": "best[protocol^=m3u8]/bestaudio[protocol^=m3u8]/best",
    "youtube_include_dash_manifest": False,
    "nocheckcertificate": True,
    "geo_bypass": True,
    # 可選：如果需要 cookies 可取消註解
    # "cookiefile": "cookies.txt",
}

def fetch_source_m3u(url: str) -> str:
    req = Request(url, headers={"User-Agent": "Mozilla/5.0"})
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
            # 下一行應該是 url
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

            # 優先找 hls / m3u8 格式
            formats = info.get("formats") or []
            # 過濾出真正的 m3u8
            hls_formats = [
                f for f in formats
                if f.get("protocol") in ("m3u8", "m3u8_native", "http_dash_segments")
                or (f.get("url") and ".m3u8" in f.get("url", ""))
            ]

            if hls_formats:
                # 取最高畫質
                hls_formats.sort(
                    key=lambda x: (x.get("height") or 0, x.get("tbr") or 0),
                    reverse=True
                )
                return hls_formats[0].get("url")

            # 退而求其次：直接用 url 或 manifest
            if info.get("url") and ".m3u8" in info.get("url", ""):
                return info["url"]
            if info.get("manifest_url"):
                return info["manifest_url"]

            # 最後嘗試用 -g 邏輯（有些情況 format 選不到）
            return None
    except Exception as e:
        print(f"  [失敗] {youtube_url} -> {e}", file=sys.stderr)
        return None

def main():
    print("正在下載來源 m3u...")
    source = fetch_source_m3u(SOURCE_URL)
    entries = parse_m3u(source)
    print(f"共解析到 {len(entries)} 個節目")

    OUTPUT_DIR.mkdir(parents=True, exist_ok=True)

    success = 0
    failed = 0
    results = []

    for idx, entry in enumerate(entries, 1):
        yt_url = entry["url"]
        print(f"[{idx}/{len(entries)}] 處理: {yt_url}")

        m3u8 = get_m3u8_url(yt_url)
        if m3u8:
            results.append({"extinf": entry["extinf"], "url": m3u8})
            success += 1
            print(f"  → 成功")
        else:
            # 保留原始 YouTube 連結作為 fallback（可選）
            # results.append(entry)
            failed += 1
            print(f"  → 跳過（無法取得 m3u8）")

        # 避免被 YouTube 限速，稍微延遲
        time.sleep(0.8)

    # 寫入新 m3u
    with open(OUTPUT_FILE, "w", encoding="utf-8") as f:
        f.write("#EXTM3U\n")
        f.write("# 由 yt-dlp 自動提取 m3u8，來源: youtubeworld.m3u\n")
        f.write(f"# 更新時間: {time.strftime('%Y-%m-%d %H:%M:%S')}\n")
        for item in results:
            f.write(item["extinf"] + "\n")
            f.write(item["url"] + "\n")

    print(f"\n完成！成功: {success}，失敗: {failed}")
    print(f"輸出檔案: {OUTPUT_FILE}")

if __name__ == "__main__":
    main()
