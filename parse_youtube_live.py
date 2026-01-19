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
    
    # 步驟2: 嘗試多種方式查找 ytInitialPlayerResponse
    print("嘗試查找 ytInitialPlayerResponse...")
    
    # 方式1: 直接匹配
    match = re.search(r'ytInitialPlayerResponse\s*=\s*({.+?});', html)
    
    # 方式2: 如果方式1失敗，嘗試查找在 window["ytInitialPlayerResponse"] 中
    if not match:
        match = re.search(r'window\["ytInitialPlayerResponse"\]\s*=\s*({.+?});', html)
    
    # 方式3: 嘗試查找在 script 標籤中
    if not match:
        match = re.search(r'var ytInitialPlayerResponse\s*=\s*({.+?});', html)
    
    # 方式4: 嘗試查找嵌入式 JSON
    if not match:
        # 查找包含 ytInitialPlayerResponse 的 script 標籤
        pattern = re.compile(r'<script[^>]*>.*?ytInitialPlayerResponse.*?({.*?})</script>', re.DOTALL)
        matches = pattern.findall(html)
        if matches:
            match = matches[0]
            if isinstance(match, str):
                # 如果是字符串，直接使用
                match = re.match(r'({.*})', match, re.DOTALL)
    
    if not match:
        # 打印部分 HTML 以調試
        print("HTML 前 1000 字符:")
        print(html[:1000])
        print("\n嘗試查找其他可能的響應...")
        
        # 查找所有可能的 JSON 響應
        json_patterns = [
            r'ytInitialPlayerResponse\s*=\s*({.+?})\s*;',
            r'ytInitialPlayerResponse\s*:\s*({.+?})',
            r'"player_response":"({.+?})"',
        ]
        
        for pattern in json_patterns:
            matches = re.findall(pattern, html, re.DOTALL)
            if matches:
                print(f"找到模式: {pattern}")
                print(f"匹配數量: {len(matches)}")
                if matches:
                    match_str = matches[0]
                    try:
                        player_response = json.loads(match_str)
                        if 'streamingData' in player_response:
                            print("成功解析到包含 streamingData 的響應")
                            match = type('Match', (), {'group': lambda x: match_str})()
                            break
                    except json.JSONDecodeError:
                        continue
    
    if not match:
        raise Exception("無法找到 ytInitialPlayerResponse")
    
    # 解析 JSON
    try:
        if isinstance(match, str):
            player_response = json.loads(match)
        else:
            player_response = json.loads(match.group(1))
    except json.JSONDecodeError as e:
        print(f"JSON 解析錯誤: {e}")
        # 嘗試清理 JSON
        json_str = match.group(1) if hasattr(match, 'group') else match
        # 移除尾部的逗號
        json_str = re.sub(r',\s*}', '}', json_str)
        json_str = re.sub(r',\s*]', ']', json_str)
        try:
            player_response = json.loads(json_str)
        except:
            raise Exception("JSON 解析失敗，可能是格式問題")
    
    print("成功獲取 player_response")
    print(f"videoDetails: {player_response.get('videoDetails', {}).get('title', '未知')}")
    print(f"isLive: {player_response.get('videoDetails', {}).get('isLive', False)}")
    
    # 檢查是否為直播
    is_live = player_response.get('videoDetails', {}).get('isLive', False)
    if not is_live:
        print("警告: 影片可能不是直播，但仍嘗試獲取 HLS URL")
    
    # 檢查是否有 streamingData
    if 'streamingData' not in player_response:
        print("player_response 內容:")
        print(json.dumps(player_response, indent=2)[:500] + "...")
        raise Exception("streamingData 不存在")
    
    streaming_data = player_response['streamingData']
    
    # 嘗試獲取 hlsManifestUrl
    if 'hlsManifestUrl' in streaming_data:
        hls_url = streaming_data['hlsManifestUrl']
        print(f"提取到的主 HLS URL: {hls_url}")
        return hls_url
    else:
        print("streamingData 內容:")
        print(json.dumps(streaming_data, indent=2)[:500] + "...")
        
        # 檢查是否有其他直播相關的 URL
        if 'formats' in streaming_data:
            formats = streaming_data['formats']
            print(f"找到 {len(formats)} 個格式")
            for fmt in formats:
                if 'url' in fmt:
                    print(f"格式: {fmt.get('itag', '未知')}, 類型: {fmt.get('mimeType', '未知')}")
        
        if 'adaptiveFormats' in streaming_data:
            adaptive_formats = streaming_data['adaptiveFormats']
            print(f"找到 {len(adaptive_formats)} 個自適應格式")
        
        raise Exception("這不是直播影片或無法找到 HLS URL")

# 函數：解析主 M3U8 清單，獲取最高品質的 variant M3U8
def get_variant_m3u8(master_m3u8_url):
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Referer': 'https://www.youtube.com/',
    }
    
    print(f"獲取主 M3U8: {master_m3u8_url}")
    response = requests.get(master_m3u8_url, headers=headers)
    if response.status_code != 200:
        raise Exception(f"無法獲取主 M3U8 清單，狀態碼: {response.status_code}")
    
    content = response.text
    lines = content.splitlines()
    
    print(f"M3U8 行數: {len(lines)}")
    
    variants = []
    current_bandwidth = None
    current_resolution = None
    
    for line in lines:
        if line.startswith('#EXT-X-STREAM-INF:'):
            match = re.search(r'BANDWIDTH=(\d+)', line)
            if match:
                current_bandwidth = int(match.group(1))
            
            match_res = re.search(r'RESOLUTION=(\d+x\d+)', line)
            if match_res:
                current_resolution = match_res.group(1)
        elif line.strip() and current_bandwidth:
            variant_info = {
                'bandwidth': current_bandwidth,
                'url': line.strip(),
                'resolution': current_resolution
            }
            variants.append(variant_info)
            current_bandwidth = None
            current_resolution = None
    
    if not variants:
        print("無法找到 variant 清單，嘗試直接使用主 M3U8 作為 variant")
        return master_m3u8_url
    
    # 打印找到的 variant
    print(f"找到 {len(variants)} 個 variant:")
    for i, variant in enumerate(variants):
        print(f"  {i+1}. 帶寬: {variant['bandwidth']}bps, 分辨率: {variant['resolution']}")
    
    # 選擇最高 BANDWIDTH 的 variant
    variants.sort(key=lambda x: x['bandwidth'], reverse=True)
    highest_variant_url = variants[0]['url']
    
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
    
    print(f"獲取 variant M3U8: {variant_m3u8_url}")
    
    # 獲取 variant m3u8 內容
    response = requests.get(variant_m3u8_url, headers=headers)
    if response.status_code != 200:
        raise Exception(f"無法獲取 variant M3U8，狀態碼: {response.status_code}")
    
    variant_content = response.text
    print(f"variant M3U8 內容大小: {len(variant_content)} 字符")
    
    # 確保輸出目錄存在
    if not os.path.exists(output_dir):
        os.makedirs(output_dir)
    
    # 解析 variant m3u8
    lines = variant_content.splitlines()
    
    ts_segments = []
    segment_count = 0
    max_segments = 10  # 限制下載的片段數量
    
    for line in lines:
        if line.strip() and not line.startswith('#'):
            ts_url = line.strip()
            
            # 如果是相對 URL，轉絕對
            parsed_variant = urlparse(variant_m3u8_url)
            if not ts_url.startswith('http'):
                ts_url = f"{parsed_variant.scheme}://{parsed_variant.netloc}{ts_url}"
            
            if segment_count >= max_segments:
                print(f"已達到最大下載數量 {max_segments}")
                break
            
            # 提取 sq 參數作為檔名
            parsed_ts = urlparse(ts_url)
            params = parse_qs(parsed_ts.query)
            sq = params.get('sq', [str(segment_count)])[0]
            filename = f"segment_{sq}.ts"
            
            # 下載 TS 片段
            print(f"下載 TS 片段 {segment_count + 1}: {filename}")
            try:
                ts_response = requests.get(ts_url, headers=headers, stream=True, timeout=30)
                if ts_response.status_code == 200:
                    filepath = os.path.join(output_dir, filename)
                    with open(filepath, 'wb') as f:
                        for chunk in ts_response.iter_content(chunk_size=8192):
                            f.write(chunk)
                    print(f"下載成功: {filename} ({os.path.getsize(filepath)} bytes)")
                    ts_segments.append(filename)
                    segment_count += 1
                else:
                    print(f"下載失敗: {ts_url}，狀態碼: {ts_response.status_code}")
            except Exception as e:
                print(f"下載異常: {e}")
    
    # 生成 live.m3u8 文件
    if ts_segments:
        live_m3u8_content = [
            '#EXTM3U',
            '#EXT-X-VERSION:3',
            '#EXT-X-TARGETDURATION:10',
            '#EXT-X-MEDIA-SEQUENCE:0',
            '#EXT-X-PLAYLIST-TYPE:VOD',
        ]
        
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

# 測試其他可能的直播 URL
def test_different_urls():
    """測試不同的直播 URL"""
    test_urls = [
        "https://www.youtube.com/watch?v=5qap5aO4i9A",  # lofi hip hop 直播
        "https://www.youtube.com/watch?v=jfKfPfyJRdk",  # 另一個流行直播
        "https://www.youtube.com/watch?v=21X5lGlDOfg",  # NASA 直播
        "https://www.youtube.com/watch?v=wZZ7oFKsKzY",  # 實時新聞
    ]
    
    for url in test_urls:
        print(f"\n嘗試測試 URL: {url}")
        try:
            master_hls_url = extract_hls_url(url)
            print(f"成功獲取 HLS URL: {master_hls_url}")
            return url, master_hls_url
        except Exception as e:
            print(f"失敗: {e}")
    
    return None, None

# 主程式
if __name__ == "__main__":
    # 創建輸出目錄
    output_dir = 'ts_segments'
    if not os.path.exists(output_dir):
        os.makedirs(output_dir)
    
    try:
        # 嘗試原始 URL
        video_url = "https://www.youtube.com/watch?v=fN9uYWCjQaw"
        print(f"嘗試原始 URL: {video_url}")
        
        try:
            # 步驟1: 提取主 HLS URL
            master_hls_url = extract_hls_url(video_url)
        except Exception as e:
            print(f"原始 URL 失敗: {e}")
            print("\n嘗試其他直播 URL...")
            
            # 測試其他可能的直播 URL
            video_url, master_hls_url = test_different_urls()
            
            if not master_hls_url:
                raise Exception("所有測試 URL 都失敗了，請確保提供的是直播 URL")
        
        # 步驟2: 獲取最高品質 variant M3U8
        variant_m3u8_url = get_variant_m3u8(master_hls_url)
        
        # 步驟3: 生成 live.m3u8 文件並下載 TS 片段
        live_m3u8_path, downloaded_segments = generate_live_m3u8(variant_m3u8_url, output_dir)
        
        if live_m3u8_path:
            print(f"\n直播 M3U8 文件已生成: {live_m3u8_path}")
            print("您可以使用以下命令播放:")
            print(f"  vlc {live_m3u8_path}")
            print(f"  ffplay {live_m3u8_path}")
            
            # 顯示生成的 live.m3u8 內容
            print("\nlive.m3u8 內容:")
            with open(live_m3u8_path, 'r', encoding='utf-8') as f:
                content = f.read()
                print(content)
        
        print(f"\n處理完成，使用的 URL: {video_url}")
        
    except Exception as e:
        print(f"錯誤: {str(e)}")
        print("\n建議:")
        print("1. 確保提供的 URL 是正在進行的 YouTube 直播")
        print("2. 檢查網路連接")
        print("3. 嘗試不同的直播 URL")
        print("4. YouTube 可能更新了頁面結構，需要更新解析邏輯")
        sys.exit(1)
