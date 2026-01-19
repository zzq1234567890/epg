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

# 函數：解析 variant M3U8，獲取 TS 片段並下載（示範下載前 5 個片段）
def parse_and_download_ts(variant_m3u8_url, output_dir='ts_segments'):
    if not os.path.exists(output_dir):
        os.makedirs(output_dir)
    
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Referer': 'https://www.youtube.com/',
    }
    
    # 因為直播 M3U8 會持續更新，這裡示範循環抓取幾次
    downloaded = 0
    max_downloads = 5  # 只下載前 5 個作為示範
    
    while downloaded < max_downloads:
        response = requests.get(variant_m3u8_url, headers=headers)
        if response.status_code != 200:
            raise Exception(f"無法獲取 variant M3U8，狀態碼: {response.status_code}")
        
        content = response.text
        lines = content.splitlines()
        
        sequence = None
        ts_urls = []
        for line in lines:
            if line.startswith('#EXT-X-MEDIA-SEQUENCE:'):
                sequence = int(line.split(':')[1])
            elif line.startswith('#EXTINF:') or line.startswith('#EXT-X-PROGRAM-DATE-TIME:'):
                continue
            elif line.strip() and not line.startswith('#'):
                ts_urls.append(line.strip())
        
        if not ts_urls:
            print("暫無新 TS 片段，等待 5 秒...")
            time.sleep(5)
            continue
        
        # 下載新 TS 片段
        for ts_url in ts_urls:
            if downloaded >= max_downloads:
                break
            
            # 如果是相對 URL，轉絕對
            parsed_variant = urlparse(variant_m3u8_url)
            if not ts_url.startswith('http'):
                ts_url = f"{parsed_variant.scheme}://{parsed_variant.netloc}{ts_url}"
            
            # 提取 sq 參數作為檔名
            parsed_ts = urlparse(ts_url)
            params = parse_qs(parsed_ts.query)
            sq = params.get('sq', ['unknown'])[0]
            filename = f"segment_{sq}.ts"
            filepath = os.path.join(output_dir, filename)
            
            # 下載 TS
            ts_response = requests.get(ts_url, headers=headers, stream=True)
            if ts_response.status_code == 200:
                with open(filepath, 'wb') as f:
                    for chunk in ts_response.iter_content(chunk_size=8192):
                        f.write(chunk)
                print(f"下載成功: {filename}")
                downloaded += 1
            else:
                print(f"下載失敗: {ts_url}，狀態碼: {ts_response.status_code}")
        
        # 等待下一次更新（直播通常每 5-10 秒更新一次）
        time.sleep(5)
    
    print(f"示範下載完成，共 {downloaded} 個 TS 片段")

# 主程式
if __name__ == "__main__":
    video_url = "https://www.youtube.com/watch?v=fN9uYWCjQaw"
    
    try:
        # 步驟1: 提取主 HLS URL
        master_hls_url = extract_hls_url(video_url)
        
        # 步驟2: 獲取最高品質 variant M3U8
        variant_m3u8_url = get_variant_m3u8(master_hls_url)
        
        # 步驟3: 解析 variant M3U8 並下載 TS 片段（示範）
        parse_and_download_ts(variant_m3u8_url)
        
    except Exception as e:
        print(f"錯誤: {str(e)}")
        sys.exit(1)
