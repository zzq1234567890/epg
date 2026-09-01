#!/usr/bin/env python3
"""
从 YouTube 直播 M3U 列表中提取 m3u8 流地址，生成新的 M3U 文件
"""

import re
import os
import sys
from urllib.parse import urlparse

import yt_dlp

# ========== 配置 ==========
INPUT_M3U_URL = "https://raw.githubusercontent.com/zzq1234567890/epg/refs/heads/main/youtubeworld.m3u"
OUTPUT_M3U_PATH = "output.m3u"  # 生成的文件名，可自行修改

# yt-dlp 提取选项
YDL_OPTS = {
    "quiet": True,
    "no_warnings": True,
    "extract_flat": True,          # 只获取元数据，不下载
    "force_generic_extractor": False,
}


def parse_m3u(content):
    """
    解析 M3U 内容，提取频道名和 YouTube URL
    返回: [(channel_name, youtube_url), ...]
    """
    entries = []
    lines = content.splitlines()
    i = 0
    while i < len(lines):
        line = lines[i].strip()
        # 匹配 #EXTINF 行，提取频道名（最后一个逗号后面的内容）
        if line.startswith("#EXTINF"):
            # 提取频道名：取最后一个逗号之后的部分
            if "," in line:
                channel_name = line.split(",")[-1].strip()
            else:
                channel_name = "Unknown"
            # 下一行应该是 URL
            if i + 1 < len(lines):
                url_line = lines[i + 1].strip()
                # 检查是否是 YouTube 链接
                if "youtube.com/watch" in url_line or "youtu.be/" in url_line:
                    entries.append((channel_name, url_line))
            i += 2
        else:
            i += 1
    return entries


def get_m3u8_url(youtube_url):
    """
    使用 yt-dlp 提取直播流的 m3u8 地址
    返回: m3u8_url 或 None
    """
    try:
        with yt_dlp.YoutubeDL(YDL_OPTS) as ydl:
            info = ydl.extract_info(youtube_url, download=False)
            if info is None:
                return None

            # 优先从 formats 中找 m3u8 格式
            if "formats" in info and info["formats"]:
                for fmt in info["formats"]:
                    # m3u8 通常以 .m3u8 结尾或 protocol 为 m3u8
                    url = fmt.get("url", "")
                    if url and (".m3u8" in url or fmt.get("protocol") == "m3u8"):
                        return url
                # 如果没找到 m3u8，返回第一个可用的格式 URL
                return info["formats"][0].get("url")

            # 备用：直接取 url 字段
            if "url" in info and info["url"]:
                return info["url"]

            return None
    except Exception as e:
        print(f"  [错误] {youtube_url}: {e}", file=sys.stderr)
        return None


def main():
    print("📥 正在下载源 M3U 文件...")
    import urllib.request
    try:
        with urllib.request.urlopen(INPUT_M3U_URL, timeout=30) as resp:
            content = resp.read().decode("utf-8")
    except Exception as e:
        print(f"❌ 下载失败: {e}", file=sys.stderr)
        sys.exit(1)

    print("📋 正在解析频道列表...")
    entries = parse_m3u(content)
    print(f"✅ 找到 {len(entries)} 个频道")

    if not entries:
        print("⚠️ 未找到任何频道，退出")
        sys.exit(0)

    print("🔄 正在提取 m3u8 流地址（可能需要一些时间）...")
    new_lines = []
    success_count = 0

    for idx, (name, url) in enumerate(entries, 1):
        print(f"  [{idx}/{len(entries)}] {name}")
        m3u8_url = get_m3u8_url(url)

        if m3u8_url:
            # 保留原始 #EXTINF 行风格，只替换 URL
            # 构造新条目：保持原 #EXTINF 信息 + 新的 m3u8 地址
            # 这里简化处理：直接生成新的 #EXTINF + m3u8 地址
            new_lines.append(f'#EXTINF:-1 tvg-logo="" group-title="yt-dlp",{name}')
            new_lines.append(m3u8_url)
            success_count += 1
        else:
            print(f"    ⚠️ 无法提取 m3u8，跳过")
            # 保留原始条目作为备用
            new_lines.append(f'#EXTINF:-1 tvg-logo="" group-title="yt-dlp (fallback)",{name}')
            new_lines.append(url)

    # 写入新 M3U
    with open(OUTPUT_M3U_PATH, "w", encoding="utf-8") as f:
        f.write("#EXTM3U\n")
        f.write("\n".join(new_lines))

    print(f"✅ 完成！成功提取 {success_count}/{len(entries)} 个 m3u8 地址")
    print(f"📁 输出文件: {OUTPUT_M3U_PATH}")


if __name__ == "__main__":
    main()
