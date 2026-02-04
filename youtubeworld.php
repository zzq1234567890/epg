<?php
// 設定 HTTP 標頭，確保輸出為純文字 UTF-8
header('Content-Type:text/plain ; charset=UTF-8');
$fp = "youtubeworld.m3u";

// 錯誤日誌記錄
function log_error($message) {
    file_put_contents('youtube_error.log', date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL, FILE_APPEND);
}

// 輔助函數：處理 JSON 中的 Unicode 轉義序列
function decode_unicode_escape($str) {
    return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', function ($match) {
        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
    }, $str);
}

// 輔助函數：處理 Unicode 轉碼 (適合 PHP7+)
function replace_unicode_escape_sequence($match) {       
    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');     
}

// 從 URL 獲取 M3U 內容
function get_remote_m3u($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    $content = curl_exec($ch);
    
    if (curl_errno($ch)) {
        log_error('遠程M3U獲取失敗: ' . curl_error($ch));
        curl_close($ch);
        return false;
    }
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code != 200) {
        log_error('遠程M3U HTTP錯誤: ' . $http_code);
        return false;
    }
    
    return $content;
}

function extract_youtube_live($url, $cookie) {
    $ch = curl_init();
    
    $headers = [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept-Language: zh-TW,zh;q=0.9,en-US;q=0.8,en;q=0.7',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7'
    ];
    
    if (!empty($cookie)) {
        $headers[] = 'Cookie: ' . $cookie;
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $html = curl_exec($ch);
    
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        log_error('YouTube直播提取失敗: ' . $error);
        return ['error' => $error];
    }
    
    curl_close($ch);
    
    // 嘗試多種方式提取 ytInitialData
    $data = null;
    
    // 方式1: 常規匹配
    if (preg_match('/var ytInitialData\s*=\s*(\{.*?\});<\/script>/s', $html, $matches)) {
        $data = json_decode($matches[1], true);
    }
    
    // 方式2: 嘗試其他可能的模式
    if (!$data && preg_match('/ytInitialData\s*=\s*(\{.*?\});<\/script>/s', $html, $matches)) {
        $data = json_decode($matches[1], true);
    }
    
    // 方式3: 嘗試從 window["ytInitialData"] 提取
    if (!$data && preg_match('/window\[["\']ytInitialData["\']\]\s*=\s*(\{.*?\});<\/script>/s', $html, $matches)) {
        $data = json_decode($matches[1], true);
    }
    
    if (!$data) {
        log_error('無法找到 ytInitialData，HTML長度: ' . strlen($html));
        return ['error' => 'Could not find ytInitialData'];
    }
    
    $videos = [];
    
    // 遞歸查找函數 - 改進版本
    $find_videos = function($obj) use (&$videos, &$find_videos) {
        if (is_array($obj)) {
            // 檢查是否為視頻渲染器
            if (isset($obj['videoRenderer'])) {
                $v = $obj['videoRenderer'];
                $title = '';
                $video_id = isset($v['videoId']) ? $v['videoId'] : '';
                
                // 嘗試多種方式獲取標題
                if (isset($v['title']['runs'][0]['text'])) {
                    $title = $v['title']['runs'][0]['text'];
                } elseif (isset($v['title']['simpleText'])) {
                    $title = $v['title']['simpleText'];
                } elseif (isset($v['title']['accessibility']['accessibilityData']['label'])) {
                    $title = $v['title']['accessibility']['accessibilityData']['label'];
                }
                
                // 檢查是否為直播（有直播標識）
                $is_live = false;
                if (isset($v['badges'])) {
                    foreach ($v['badges'] as $badge) {
                        if (isset($badge['metadataBadgeRenderer']['label']) && 
                            strpos(strtolower($badge['metadataBadgeRenderer']['label']), 'live') !== false) {
                            $is_live = true;
                            break;
                        }
                    }
                }
                
                // 如果標題和ID都存在，且是直播，則添加到列表
                if ($title && $video_id && $is_live) {
                    $videos[] = [
                        'title' => trim($title),
                        'video_id' => $video_id
                    ];
                }
            } 
            // 檢查 richItemRenderer
            elseif (isset($obj['richItemRenderer']['content']['videoRenderer'])) {
                $v = $obj['richItemRenderer']['content']['videoRenderer'];
                $title = isset($v['title']['runs'][0]['text']) ? $v['title']['runs'][0]['text'] : '';
                $video_id = isset($v['videoId']) ? $v['videoId'] : '';
                
                if ($title && $video_id) {
                    $videos[] = [
                        'title' => trim($title),
                        'video_id' => $video_id
                    ];
                }
            }
            
            // 遞歸搜索
            foreach ($obj as $key => $value) {
                $find_videos($value);
            }
        }
    };
    
    $find_videos($data);
    
    // 去重
    $unique_videos = [];
    $seen_ids = [];
    foreach ($videos as $v) {
        if (!in_array($v['video_id'], $seen_ids)) {
            $unique_videos[] = $v;
            $seen_ids[] = $v['video_id'];
        }
    }
    
    log_error('成功提取YouTube直播數量: ' . count($unique_videos));
    return $unique_videos;
}

// 通用函數：處理YouTube播放列表
function process_youtube_playlist($url, $group_title, $category) {
    $result = '';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        log_error($category . '播放列表獲取失敗: ' . curl_error($ch));
        curl_close($ch);
        return $result;
    }
    
    curl_close($ch);
    
    if (!$response) {
        return $result;
    }
    
    // 清理響應
    $response = str_replace('#', '', $response);
    $response = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $response);
    
    // 提取視頻ID
    $video_ids = [];
    if (preg_match_all('/"videoId":"([a-zA-Z0-9_-]{11})"/i', $response, $matches)) {
        $video_ids = array_unique($matches[1]);
    }
    
    // 提取標題
    $titles = [];
    if (preg_match_all('/"title":\{"runs":\[\{"text":"(.*?)"/i', $response, $matches)) {
        $titles = $matches[1];
    }
    
    // 提取縮略圖
    $thumbnails = [];
    if (preg_match_all('/"thumbnail":\{"thumbnails":\[\{"url":"(https:\/\/i\.ytimg\.com\/vi\/[^"]+)/i', $response, $matches)) {
        $thumbnails = $matches[1];
    }
    
    // 生成M3U條目
    $count = min(count($video_ids), count($titles), 50); // 限制最大數量
    
    for ($i = 0; $i < $count; $i++) {
        $title = isset($titles[$i]) ? htmlspecialchars_decode($titles[$i], ENT_QUOTES) : 'Unknown Title';
        $video_id = $video_ids[$i];
        $logo = isset($thumbnails[$i]) ? $thumbnails[$i] : '';
        
        // 清理標題中的特殊字符
        $title = preg_replace('/[\x00-\x1F\x7F]/', '', $title);
        $title = str_replace(["\r", "\n"], '', $title);
        
        $result .= "#EXTINF:-1 tvg-id=\"\" tvg-name=\"\" tvg-logo=\"" . $logo . "\" group-title=\"youtube" . $group_title . "\"," . $title . "\r\n";
        $result .= "https://www.youtube.com/watch?v=" . $video_id . "\r\n";
    }
    
    log_error($category . '播放列表處理完成，數量: ' . $count);
    return $result;
}

// 開始生成M3U
$chn = "#EXTM3U\r\n";

// 獲取遠程M3U內容並合併
$remote_m3u_url = "https://raw.githubusercontent.com/zzq12345/epgtest/refs/heads/main/yu.m3u";
$remote_content = get_remote_m3u($remote_m3u_url);

if ($remote_content) {
    // 如果遠程內容包含#EXTM3U標頭，則移除
    if (strpos($remote_content, "#EXTM3U") === 0) {
        $first_newline = strpos($remote_content, "\n");
        if ($first_newline !== false) {
            $remote_content = substr($remote_content, $first_newline + 1);
        }
    }
    $chn .= $remote_content;
}

// 獲取YouTube正在直播的內容
$url = "https://www.youtube.com/channel/UC4R8DWoMoI7CAwX8_LjQHig/livetab?ss=CKEK";
$cookie = ""; // 如果需要cookie，請在這裡填寫

$results = extract_youtube_live($url, $cookie);

if (!isset($results['error'])) {
    foreach ($results as $res) {
        if (isset($res['title']) && isset($res['video_id'])) {
            $title = preg_replace('/[\x00-\x1F\x7F]/', '', $res['title']);
            $title = str_replace(["\r", "\n"], '', $title);
            
            $chn .= "#EXTINF:-1 tvg-id=\"\" tvg-name=\"\" tvg-logo=\"\" group-title=\"youtube正在直播\"," . $title . "\r\n";
            $chn .= "https://www.youtube.com/watch?v=" . $res['video_id'] . "\r\n";
        }
    }
}

/*
 處理各個播放列表
$playlists = [
    ['url' => 'https://www.youtube.com/playlist?list=PLd8qbe5zE33trmwWLpiCr7DjzsoUb0-Jj', 'group' => '中文新聞直播', 'category' => '中文新聞直播'],
    ['url' => 'https://www.youtube.com/playlist?list=PLd8qbe5zE33v8XouhXYxUjn954xIPaSEN', 'group' => '臨時直播', 'category' => '臨時直播'],
    ['url' => 'https://www.youtube.com/playlist?list=PLd8qbe5zE33tLKb-vGy-iHc5YCNXBHwbx', 'group' => '跨年直播', 'category' => '跨年直播'],
    ['url' => 'https://www.youtube.com/playlist?list=PLd8qbe5zE33s5OSV4qzMMkCWoYItL7otl', 'group' => '國外新聞', 'category' => '國外新聞'],
    ['url' => 'https://www.youtube.com/playlist?list=PLd8qbe5zE33t4Q78Q1TxE8K953dMiTC9S', 'group' => '娛樂', 'category' => '娛樂'],
    ['url' => 'https://www.youtube.com/playlist?list=PLiCvVJzBupKlQ50jZqLas7SAztTMEYv1f', 'group' => '遊戲直播', 'category' => '遊戲直播'],
    ['url' => 'https://www.youtube.com/playlist?list=PL8fVUTBmJhHJrxHg_uNTMyRmsWbFltuQV', 'group' => '運動直播', 'category' => '運動直播'],
    ['url' => 'https://www.youtube.com/playlist?list=PLd8qbe5zE33ufMlSpRWhDEUplpYlVsyaS', 'group' => '少兒', 'category' => '少兒'],
    ['url' => 'https://www.youtube.com/playlist?list=PLd8qbe5zE33uW6vfsO9ZZGCUzbStqtNxS', 'group' => '語言學習', 'category' => '語言學習'],
    ['url' => 'https://www.youtube.com/playlist?list=PLd8qbe5zE33v2Ip13eAc38OgpPksxQ7mE', 'group' => '街景直播', 'category' => '街景直播'],
    ['url' => 'https://www.youtube.com/playlist?list=PLd8qbe5zE33tN_4OSmIvc1QM82jCP4BI3', 'group' => '廣告直播', 'category' => '廣告直播'],
];

foreach ($playlists as $playlist) {
    $chn .= process_youtube_playlist($playlist['url'], $playlist['group'], $playlist['category']);
}

// 輸出結果
// echo $chn;
*/
// 寫入文件

// 可選：直接輸出文件
file_put_contents($fp, $chn);
?>
