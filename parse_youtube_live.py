import requests
import re
import json
import sys
import os
import time
from urllib.parse import urlparse, parse_qs

# 函數：從 YouTube 頁面提取 hlsManifestUrl
def extract_hls_url(video_url):
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Referer': 'https://www.youtube.com/',
        'Origin': 'https://www.youtube.com',
        'Accept': '*/*',
        'Accept-Language': 'en-US,en;q=0.9',
    }
    
    # 步驟1: 獲取影片頁面 HTML
    response = requests.get(video_url, headers=headers)
    if response.status_code != 200:
        raise Exception(f"無法獲取影片頁面，狀態碼: {response.status_code}")
    
    html = response.text
    
    # 步驟2: 從 ytInitialPlayerResponse 中提取 hlsManifestUrl
    match = re.search(r'ytInitialPlayerResponse\s*=\s*({.+?});', html)
    if not match:
        raise Exception("無法找到 ytInitialPlayerResponse")
    
    player_response = json.loads(match.group(1))
    
    # 檢查是否為直播
    if 'streamingData' not in player_response or 'hlsManifestUrl' not in player_response['streamingData']:
        raise Exception("這不是直播影片或無法找到 HLS URL")
    
    hls_url = player_response['streamingData']['hlsManifestUrl']
    print(f"提取到的主 HLS URL: {hls_url}")
    return hls_url

# 函數：解析主 M3U8 清單，獲取最高品質的 variant M3U8
def get_variant_m3u8(master_m3u8_url):
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Referer': 'https://www.youtube.com/',
    }
    
    response = requests.get(master_m3u8_url, headers=headers)
    if response.status_code != 200:
        raise Exception(f"無法獲取主 M3U8 清單，狀態碼: {response.status_code}")
    
    content = response.text
    lines = content.splitlines()
    
    variants = []
    current_bandwidth = None
    for line in lines:
        if line.startswith('#EXT-X-STREAM-INF:'):
            match = re.search(r'BANDWIDTH=(\d+)', line)
            if match:
                current_bandwidth = int(match.group(1))
        elif line.strip() and current_bandwidth:
            variants.append((current_bandwidth, line.strip()))
            current_bandwidth = None
    
    if not variants:
        raise Exception("無法找到 variant 清單")
    
    # 選擇最高 BANDWIDTH 的 variant
    variants.sort(key=lambda x: x[0], reverse=True)
    highest_variant_url = variants[0][1]
    
    # 如果是相對 URL，轉為絕對
    parsed_master = urlparse(master_m3u8_url)
    if not highest_variant_url.startswith('http'):
        highest_variant_url = f"{parsed_master.scheme}://{parsed_master.netloc}{highest_variant_url}"
    
    print(f"選擇的最高品質 variant M3U8 URL: {highest_variant_url}")
    return highest_variant_url

# 函數：生成 live.m3u8 文件
def generate_live_m3u8(variant_m3u8_url, output_dir='ts_segments'):
    """生成用於播放的 live.m3u8 文件"""
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Referer': 'https://www.youtube.com/',
    }
    
    # 獲取 variant m3u8 內容
    response = requests.get(variant_m3u8_url, headers=headers)
    if response.status_code != 200:
        raise Exception(f"無法獲取 variant M3U8，狀態碼: {response.status_code}")
    
    variant_content = response.text
    
    # 解析 variant m3u8 獲取媒體序列號和 TS 片段
    lines = variant_content.splitlines()
    
    # 準備生成 live.m3u8 的內容
    live_m3u8_content = []
    ts_segments = []
    
    for line in lines:
        if line.startswith('#EXT-X-MEDIA-SEQUENCE:'):
            # 保留媒體序列號
            live_m3u8_content.append(line)
        elif line.startswith('#EXT-X-PLAYLIST-TYPE:'):
            # 保留播放列表類型
            live_m3u8_content.append(line)
        elif line.startswith('#EXT-X-TARGETDURATION:'):
            # 保留目標時長
            live_m3u8_content.append(line)
        elif line.startswith('#EXT-X-VERSION:'):
            # 保留版本
            live_m3u8_content.append(line)
        elif line.startswith('#EXTINF:'):
            # 保留時長信息
            live_m3u8_content.append(line)
        elif line.startswith('#EXT-X-PROGRAM-DATE-TIME:'):
            # 保留時間戳
            live_m3u8_content.append(line)
        elif line.strip() and not line.startswith('#'):
            # 這是 TS 片段 URL
            ts_url = line.strip()
            # 如果是相對 URL，轉絕對
            parsed_variant = urlparse(variant_m3u8_url)
            if not ts_url.startswith('http'):
                ts_url = f"{parsed_variant.scheme}://{parsed_variant.netloc}{ts_url}"
            
            # 提取 sq 參數作為檔名
            parsed_ts = urlparse(ts_url)
            params = parse_qs(parsed_ts.query)
            sq = params.get('sq', ['unknown'])[0]
            filename = f"segment_{sq}.ts"
            
            # 下載 TS 片段
            ts_response = requests.get(ts_url, headers=headers, stream=True)
            if ts_response.status_code == 200:
                filepath = os.path.join(output_dir, filename)
                with open(filepath, 'wb') as f:
                    for chunk in ts_response.iter_content(chunk_size=8192):
                        f.write(chunk)
                print(f"下載成功: {filename}")
                ts_segments.append(filename)
            else:
                print(f"下載失敗: {ts_url}，狀態碼: {ts_response.status_code}")
    
    # 生成 live.m3u8 文件
    if ts_segments:
        live_m3u8_content.append('#EXT-X-MEDIA-SEQUENCE:0')
        live_m3u8_content.append('#EXT-X-PLAYLIST-TYPE:VOD')  # 或 EVENT
        live_m3u8_content.append('#EXT-X-VERSION:3')
        live_m3u8_content.append('#EXT-X-TARGETDURATION:10')
        
        for ts_file in ts_segments:
            live_m3u8_content.append('#EXTINF:10.000,')
            live_m3u8_content.append(ts_file)
        
        live_m3u8_content.append('#EXT-X-ENDLIST')
        
        # 寫入 live.m3u8 文件
        live_m3u8_path = os.path.join(output_dir, 'live.m3u8')
        with open(live_m3u8_path, 'w', encoding='utf-8') as f:
            f.write('\n'.join(live_m3u8_content))
        
        print(f"live.m3u8 文件已生成: {live_m3u8_path}")
        print(f"包含 {len(ts_segments)} 個 TS 片段")
        
        return live_m3u8_path, ts_segments
    
    return None, []

# 主程式
if __name__ == "__main__":
    video_url = "https://www.youtube.com/watch?v=fN9uYWCjQaw"
    
    try:
        # 創建輸出目錄
        output_dir = 'ts_segments'
        if not os.path.exists(output_dir):
            os.makedirs(output_dir)
        
        # 步驟1: 提取主 HLS URL
        master_hls_url = extract_hls_url(video_url)
        
        # 步驟2: 獲取最高品質 variant M3U8
        variant_m3u8_url = get_variant_m3u8(master_hls_url)
        
        # 步驟3: 生成 live.m3u8 文件並下載 TS 片段
        live_m3u8_path, downloaded_segments = generate_live_m3u8(variant_m3u8_url, output_dir)
        
        if live_m3u8_path:
            print(f"\n直播 M3U8 文件已生成: {live_m3u8_path}")
            print("您可以使用 VLC 或其他播放器打開此文件播放下載的直播內容")
            
            # 顯示生成的 live.m3u8 內容
            print("\nlive.m3u8 內容前10行:")
            with open(live_m3u8_path, 'r', encoding='utf-8') as f:
                lines = f.readlines()
                for i, line in enumerate(lines[:10]):
                    print(f"  {i+1}: {line.strip()}")
                if len(lines) > 10:
                    print(f"  ... (共 {len(lines)} 行)")
        
    except Exception as e:
        print(f"錯誤: {str(e)}")
        sys.exit(1)
