#!/usr/bin/env python3
import re
import subprocess
import os
import time
from pathlib import Path

def extract_youtube_ids(file_path):
    """從M3U8文件中提取YouTube視頻ID"""
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # 匹配YouTube視頻ID
    pattern = r'youtube\.com/watch\?v=([a-zA-Z0-9_-]+)'
    video_ids = re.findall(pattern, content)
    return list(set(video_ids))  # 去重

def extract_m3u8_url(video_id):
    """使用yt-dlp提取M3U8鏈接"""
    url = f"https://www.youtube.com/watch?v={video_id}"
    
    try:
        # 使用yt-dlp提取最佳質量的流鏈接
        cmd = ["yt-dlp", "-g", "-f", "best", url]
        result = subprocess.run(cmd, capture_output=True, text=True, timeout=30)
        
        if result.returncode == 0:
            m3u8_url = result.stdout.strip()
            if m3u8_url and "http" in m3u8_url:
                return m3u8_url
    except Exception as e:
        print(f"錯誤提取 {video_id}: {str(e)}")
    
    return None

def create_new_m3u8(original_file, m3u8_data):
    """創建新的M3U8文件"""
    with open(original_file, 'r', encoding='utf-8') as f:
        lines = f.readlines()
    
    new_lines = []
    for line in lines:
        # 查找YouTube鏈接並替換
        match = re.search(r'(https://www\.youtube\.com/watch\?v=)([a-zA-Z0-9_-]+)', line)
        if match:
            video_id = match.group(2)
            if video_id in m3u8_data and m3u8_data[video_id]:
                # 替換為M3U8鏈接
                new_url = m3u8_data[video_id]
                new_line = line.replace(match.group(0), new_url)
                new_lines.append(new_line)
                print(f"✅ 替換: {video_id}")
            else:
                new_lines.append(line)
                print(f"⚠️  保留原始: {video_id}")
        else:
            new_lines.append(line)
    
    # 寫入新文件
    with open('yt.m3u8', 'w', encoding='utf-8') as f:
        f.writelines(new_lines)
    
    print(f"🎉 創建完成: yt.m3u8")

def main():
    original_file = "youtube.txt"
    
    if not os.path.exists(original_file):
        print(f"❌ 找不到文件: {original_file}")
        return
    
    print("📋 讀取原始播放列表...")
    video_ids = extract_youtube_ids(original_file)
    print(f"🔍 找到 {len(video_ids)} 個視頻")
    
    m3u8_data = {}
    success_count = 0
    
    for i, video_id in enumerate(video_ids, 1):
        print(f"⏳ 處理第 {i}/{len(video_ids)}: {video_id}")
        
        m3u8_url = extract_m3u8_url(video_id)
        if m3u8_url:
            m3u8_data[video_id] = m3u8_url
            success_count += 1
            print(f"  ✅ 提取成功")
        else:
            print(f"  ❌ 提取失敗")
        
        # 避免請求過快
        time.sleep(1)
    
    print(f"📊 統計: {success_count}/{len(video_ids)} 成功")
    
    create_new_m3u8(original_file, m3u8_data)

if __name__ == "__main__":
    main()
