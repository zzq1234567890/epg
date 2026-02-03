<?php
// 設定 HTTP 標頭，確保輸出為純文字 UTF-8
header( 'Content-Type:text/plain ; charset=UTF-8');
$fp="youtubeworld.m3u";//压缩版本的扩展名后加.gz

// 輔助函數：處理 JSON 中的 Unicode 轉義序列 (例如 \uXXXX)
function decode_unicode_escape($str) {
    return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', function ($match) {
        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
    }, $str);
}




// 輔助函數：處理 Unicode 轉碼 (適合 PHP7+)
function replace_unicode_escape_sequence($match)
{       
    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');     
}          

function escape($str)
{
    preg_match_all("/[\x80-\xff].|[\x01-\x7f]+/", $str, $r);
    $ar = $r[0];
    foreach ($ar as $k => $v) {
        if (ord($v[0]) < 128)
            $ar[$k] = rawurlencode($v);
        else
            $ar[$k] = "%u" . bin2hex(iconv("UTF-8", "UCS-2", $v));
    }
    return join("", $ar);
}
//適合php7以上
         
function extract_youtube_live($url, $cookie) {
    $ch = curl_init();
    
    $headers = [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Cookie: ' . $cookie,
        'Accept-Language: zh-TW,zh;q=0.9,en-US;q=0.8,en;q=0.7',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7'
    ];
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $html = curl_exec($ch);
    
    if (curl_errno($ch)) {
        return ['error' => curl_error($ch)];
    }
    
    curl_close($ch);
    
    // 提取 ytInitialData
    if (preg_match('/var ytInitialData = (\{.*?\});<\/script>/', $html, $matches)) {
        $data = json_decode($matches[1], true);
    } else {
        return ['error' => 'Could not find ytInitialData'];
    }
    
    $videos = [];
    
    // 遞歸查找函數
    $find_videos = function($obj) use (&$videos, &$find_videos) {
        if (is_array($obj)) {
            if (isset($obj['videoRenderer'])) {
                $v = $obj['videoRenderer'];
                $title = isset($v['title']['runs'][0]['text']) ? $v['title']['runs'][0]['text'] : '';
                $video_id = isset($v['videoId']) ? $v['videoId'] : '';
                if ($title && $video_id) {
                    $videos[] = ['title' => $title, 'video_id' => $video_id];
                }
            } elseif (isset($obj['richItemRenderer']['content']['videoRenderer'])) {
                $v = $obj['richItemRenderer']['content']['videoRenderer'];
                $title = isset($v['title']['runs'][0]['text']) ? $v['title']['runs'][0]['text'] : '';
                $video_id = isset($v['videoId']) ? $v['videoId'] : '';
                if ($title && $video_id) {
                    $videos[] = ['title' => $title, 'video_id' => $video_id];
                }
            }
            
            foreach ($obj as $key => $value) {
                $find_videos($value);
            }
        }
    };
    
    $find_videos($data);
    
    // 去重，因為 YouTube 數據中可能會有重複的渲染對象
    $unique_videos = [];
    $seen_ids = [];
    foreach ($videos as $v) {
        if (!in_array($v['video_id'], $seen_ids)) {
            $unique_videos[] = $v;
            $seen_ids[] = $v['video_id'];
        }
    }
    
    return $unique_videos;
}
$chn= "#EXTM3U  \r\n";

$url = "https://www.youtube.com/channel/UC4R8DWoMoI7CAwX8_LjQHig/livetab?ss=CKEK";
$cookie = " ";

$results = extract_youtube_live($url, $cookie);

 {
    foreach ($results as $res) {

   $chn.= "#EXTINF:-1 tvg-id=\"\" tvg-name=\"\" tvg-logo=\"\" group-title=\"youtube正在直播\"," .str_replace('#','',$res['title']). "\r\n";
    // 修正：直接使用當前索引的網址，不使用 *4
    $chn.= "https://www.youtube.com/watch?v=" . $res['video_id'] . "\r\n";
        //echo "Title: " . $res['title'] . " | Video ID: " . $res['video_id'] . "\r\n";
    }
}


//$chn. "新聞,#genre#\r\n";
$url8='https://www.youtube.com/playlist?list=PLd8qbe5zE33trmwWLpiCr7DjzsoUb0-Jj';//中文新聞直播綜合
$ch8=curl_init();
curl_setopt($ch8,CURLOPT_URL,$url8);                  
curl_setopt($ch8,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch8, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch8, CURLOPT_SSL_VERIFYHOST, FALSE);

$re8=curl_exec($ch8);
curl_close($ch8);
$re8= preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re8);// 適合php7
preg_match_all('/"thumbnail":\{"thumbnails":\[\{"url":"(.*?)",/i',$re8,$piem8,PREG_SET_ORDER);//logo
preg_match_all('/\{"playlistVideoRenderer":\{"videoId":"(.*?)",/i',$re8,$piec8,PREG_SET_ORDER);//vid
preg_match_all('/"shortBylineText":\{"runs":\[\{"text":"(.*?)",/i',$re8,$piek8,PREG_SET_ORDER);//標題
$tru8=count($piec8);
for ($k8 = 0; $k8 < $tru8 && isset($piek8[$k8][1]) && isset($piec8[$k8][1]); $k8++) {
    // 安全檢查：確保所有需要的元素都存在
    $logo = isset($piem8[$k8][1]) ? $piem8[$k8][1] : "";
    $chn.= "#EXTINF:-1 tvg-id=\"\" tvg-name=\"\" tvg-logo=\"".$logo."\" group-title=\"youtube中文新聞直播\",".$piek8[$k8][1]."\r\n";
    $chn.= "https://www.youtube.com/watch?v=".$piec8[$k8][1]."\r\n";
}

//$chn. "臨時直播,#genre#\r\n";
$url12='https://www.youtube.com/playlist?list=PLd8qbe5zE33v8XouhXYxUjn954xIPaSEN';//臨時直播
$ch12=curl_init();
curl_setopt($ch12,CURLOPT_URL,$url12);                  
curl_setopt($ch12,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch12, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch12, CURLOPT_SSL_VERIFYHOST, FALSE);
$re12=curl_exec($ch12);
curl_close($ch12);
$re12 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re12);// 適合php7
preg_match_all('/"thumbnail":\{"thumbnails":\[\{"url":"(.*?)",/i',$re12,$piem12,PREG_SET_ORDER);//logo
preg_match_all('/\{"playlistVideoRenderer":\{"videoId":"(.*?)",/i',$re12,$piec12,PREG_SET_ORDER);//vid
preg_match_all('/"shortBylineText":\{"runs":\[\{"text":"(.*?)",/i',$re12,$piek12,PREG_SET_ORDER);//標題
$tru12=count($piec12);
for ($k12 = 0; $k12 < $tru12 && isset($piek12[$k12][1]) && isset($piec12[$k12][1]); $k12++) {
    $logo = isset($piem12[$k12][1]) ? $piem12[$k12][1] : "";
    $chn.= "#EXTINF:-1 tvg-id=\"\" tvg-name=\"\" tvg-logo=\"".$logo."\" group-title=\"youtube臨時直播\",".$piek12[$k12][1]."\r\n";
    $chn.= "https://www.youtube.com/watch?v=".$piec12[$k12][1]."\r\n";
}

$url18='https://www.youtube.com/playlist?list=PLd8qbe5zE33tLKb-vGy-iHc5YCNXBHwbx';//跨年直播
$ch18=curl_init();
curl_setopt($ch18,CURLOPT_URL,$url18);                  
curl_setopt($ch18,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch18, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch18, CURLOPT_SSL_VERIFYHOST, FALSE);

$re18=curl_exec($ch18);
curl_close($ch18);
$re18= preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re18);// 適合php7
preg_match_all('/"thumbnail":\{"thumbnails":\[\{"url":"(.*?)",/i',$re18,$piem18,PREG_SET_ORDER);//logo
preg_match_all('/\{"playlistVideoRenderer":\{"videoId":"(.*?)",/i',$re18,$piec18,PREG_SET_ORDER);//vid
preg_match_all('/"shortBylineText":\{"runs":\[\{"text":"(.*?)",/i',$re18,$piek18,PREG_SET_ORDER);//標題
$tru18=count($piec18);
for ($k18 = 0; $k18 < $tru18 && isset($piek18[$k18][1]) && isset($piec18[$k18][1]); $k18++) {
    $logo = isset($piem18[$k18][1]) ? $piem18[$k18][1] : "";
    $chn.= "#EXTINF:-1 tvg-id=\"\" tvg-name=\"\" tvg-logo=\"".$logo."\" group-title=\"youtube跨年直播\",".$piek18[$k18][1]."\r\n";
    $chn.= "https://www.youtube.com/watch?v=".$piec18[$k18][1]."\r\n";
}

//$chn. "國外新聞,#genre#\r\n";
$url4='https://www.youtube.com/playlist?list=PLd8qbe5zE33s5OSV4qzMMkCWoYItL7otl';//國外新聞直播

$ch4=curl_init();
curl_setopt($ch4,CURLOPT_URL,$url4);                  
curl_setopt($ch4,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch4, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch4, CURLOPT_SSL_VERIFYHOST, FALSE);
$re4=curl_exec($ch4);
curl_close($ch4);
$re4 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re4);// 適合php7
preg_match_all('/"thumbnail":\{"thumbnails":\[\{"url":"(.*?)",/i',$re4,$piem4,PREG_SET_ORDER);//logo
preg_match_all('/\{"playlistVideoRenderer":\{"videoId":"(.*?)",/i',$re4,$piec4,PREG_SET_ORDER);//vid
preg_match_all('/"shortBylineText":\{"runs":\[\{"text":"(.*?)",/i',$re4,$piek4,PREG_SET_ORDER);//標題
$tru4=count($piec4);
for ($k4 = 0; $k4 < $tru4 && isset($piek4[$k4][1]) && isset($piec4[$k4][1]); $k4++) {
    $logo = isset($piem4[$k4][1]) ? $piem4[$k4][1] : "";
    $chn.= "#EXTINF:-1 tvg-id=\"\" tvg-name=\"\" tvg-logo=\"".$logo."\" group-title=\"youtube國外新聞\",".$piek4[$k4][1]."\r\n";
    $chn.= "https://www.youtube.com/watch?v=".$piec4[$k4][1]."\r\n";
}

//$chn. "娛樂,#genre#\r\n";
$url6='https://www.youtube.com/playlist?list=PLd8qbe5zE33t4Q78Q1TxE8K953dMiTC9S';//娛樂
$ch6=curl_init();
curl_setopt($ch6,CURLOPT_URL,$url6);                  
curl_setopt($ch6,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch6, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch6, CURLOPT_SSL_VERIFYHOST, FALSE);
$re6=curl_exec($ch6);
curl_close($ch6);
$re6= preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re6);// 適合php7
preg_match_all('/"thumbnail":\{"thumbnails":\[\{"url":"(.*?)",/i',$re6,$piem6,PREG_SET_ORDER);//logo
preg_match_all('/\{"playlistVideoRenderer":\{"videoId":"(.*?)",/i',$re6,$piec6,PREG_SET_ORDER);//vid
preg_match_all('/"shortBylineText":\{"runs":\[\{"text":"(.*?)",/i',$re6,$piek6,PREG_SET_ORDER);//標題
$tru6=count($piec6);
for ($k6 = 0; $k6 < $tru6 && isset($piek6[$k6][1]) && isset($piec6[$k6][1]); $k6++) {
    $logo = isset($piem6[$k6][1]) ? $piem6[$k6][1] : "";
    $chn.= "#EXTINF:-1 tvg-id=\"\" tvg-name=\"\" tvg-logo=\"".$logo."\" group-title=\"youtube娛樂\",".$piek6[$k6][1]."\r\n";
    $chn.= "https://www.youtube.com/watch?v=".$piec6[$k6][1]."\r\n";
}

//游戏直播2
$url2='https://www.youtube.com/playlist?list=PLiCvVJzBupKlQ50jZqLas7SAztTMEYv1f';//遊戲
$ch2=curl_init();
curl_setopt($ch2,CURLOPT_URL,$url2);                  
curl_setopt($ch2,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, FALSE);
$re2=curl_exec($ch2);
curl_close($ch2);
$re2 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re2);// 適合php7
preg_match_all('/\{"playlistVideoRenderer":\{"videoId":"(.*?)",/i',$re2,$piec2,PREG_SET_ORDER);//vid
preg_match_all('/"shortBylineText":\{"runs":\[\{"text":"(.*?)",/i',$re2,$piek2,PREG_SET_ORDER);//標題
preg_match_all('/"thumbnail":\{"thumbnails":\[\{"url":"(.*?)",/i',$re2,$piem2,PREG_SET_ORDER);//logo
$tru2=count($piec2);
for ($k2 = 0; $k2 < $tru2 && isset($piek2[$k2][1]) && isset($piec2[$k2][1]); $k2++) {
    $logo = isset($piem2[$k2][1]) ? $piem2[$k2][1] : "";
    $chn.= "#EXTINF:-1 tvg-id=\"\" tvg-name=\"\" tvg-logo=\"".$logo."\" group-title=\"youtube遊戲直播\",".$piek2[$k2][1]."\r\n";
    $chn.= "https://www.youtube.com/watch?v=".$piec2[$k2][1]."\r\n";
}

//$chn. "運動直播,#genre#\r\n";
$url3='https://www.youtube.com/playlist?list=PL8fVUTBmJhHJrxHg_uNTMyRmsWbFltuQV';//運動
$ch3=curl_init();
curl_setopt($ch3,CURLOPT_URL,$url3);                  
curl_setopt($ch3,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch3, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch3, CURLOPT_SSL_VERIFYHOST, FALSE);
$re3=curl_exec($ch3);
curl_close($ch3);
$re3 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re3);// 適合php7
preg_match_all('/\{"playlistVideoRenderer":\{"videoId":"(.*?)",/i',$re3,$piec3,PREG_SET_ORDER);//vid
preg_match_all('/"thumbnail":\{"thumbnails":\[\{"url":"(.*?)",/i',$re3,$piem3,PREG_SET_ORDER);//logo
preg_match_all('/"shortBylineText":\{"runs":\[\{"text":"(.*?)",/i',$re3,$piek3,PREG_SET_ORDER);//標題
$tru3=count($piec3);
for ($k3 = 0; $k3 < $tru3 && isset($piek3[$k3][1]) && isset($piec3[$k3][1]); $k3++) {
    $logo = isset($piem3[$k3][1]) ? $piem3[$k3][1] : "";
    $chn.= "#EXTINF:-1 tvg-id=\"\" tvg-name=\"\" tvg-logo=\"".$logo."\" group-title=\"youtube運動直播\",".$piek3[$k3][1]."\r\n";
    $chn.= "https://www.youtube.com/watch?v=".$piec3[$k3][1]."\r\n";
}

//$chn. "少兒,#genre#\r\n";
$url5='https://www.youtube.com/playlist?list=PLd8qbe5zE33ufMlSpRWhDEUplpYlVsyaS';//少兒
$ch5=curl_init();
curl_setopt($ch5,CURLOPT_URL,$url5);                  
curl_setopt($ch5,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch5, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch5, CURLOPT_SSL_VERIFYHOST, FALSE);

$re5=curl_exec($ch5);
curl_close($ch5);
$re5= preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re5);// 適合php7
preg_match_all('/"thumbnail":\{"thumbnails":\[\{"url":"(.*?)",/i',$re5,$piem5,PREG_SET_ORDER);//logo
preg_match_all('/\{"playlistVideoRenderer":\{"videoId":"(.*?)",/i',$re5,$piec5,PREG_SET_ORDER);//vid
preg_match_all('/"shortBylineText":\{"runs":\[\{"text":"(.*?)",/i',$re5,$piek5,PREG_SET_ORDER);//標題

$tru5=count($piec5);
for ($k5 = 0; $k5 < $tru5 && isset($piek5[$k5][1]) && isset($piec5[$k5][1]); $k5++) {
    $logo = isset($piem5[$k5][1]) ? $piem5[$k5][1] : "";
    $chn.= "#EXTINF:-1 tvg-id=\"\" tvg-name=\"\" tvg-logo=\"".$logo."\" group-title=\"youtube少兒\",".$piek5[$k5][1]."\r\n";
    $chn.= "https://www.youtube.com/watch?v=".$piec5[$k5][1]."\r\n";
}

//$chn. "英語學習,#genre#\r\n";
$url9='https://www.youtube.com/playlist?list=PLd8qbe5zE33uW6vfsO9ZZGCUzbStqtNxS';//直播綜合
$ch9=curl_init();
curl_setopt($ch9,CURLOPT_URL,$url9);                  
curl_setopt($ch9,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch9, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch9, CURLOPT_SSL_VERIFYHOST, FALSE);

$re9=curl_exec($ch9);
curl_close($ch9);
$re9= preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re9);// 適合php7
preg_match_all('/"thumbnail":\{"thumbnails":\[\{"url":"(.*?)",/i',$re9,$piem9,PREG_SET_ORDER);//logo
preg_match_all('/\{"playlistVideoRenderer":\{"videoId":"(.*?)",/i',$re9,$piec9,PREG_SET_ORDER);//vid
preg_match_all('/"shortBylineText":\{"runs":\[\{"text":"(.*?)",/i',$re9,$piek9,PREG_SET_ORDER);//標題
$tru9=count($piec9);
for ($k9 = 0; $k9 < $tru9 && isset($piek9[$k9][1]) && isset($piec9[$k9][1]); $k9++) {
    $logo = isset($piem9[$k9][1]) ? $piem9[$k9][1] : "";
    $chn.= "#EXTINF:-1 tvg-id=\"\" tvg-name=\"\" tvg-logo=\"".$logo."\" group-title=\"youtube語言學習\",".$piek9[$k9][1]."\r\n";
    $chn.= "https://www.youtube.com/watch?v=".$piec9[$k9][1]."\r\n";
}

//$chn. "街景,#genre#\r\n";
$url7='https://www.youtube.com/playlist?list=PLd8qbe5zE33v2Ip13eAc38OgpPksxQ7mE';//直播綜合
$ch7=curl_init();
curl_setopt($ch7,CURLOPT_URL,$url7);                  
curl_setopt($ch7,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch7, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch7, CURLOPT_SSL_VERIFYHOST, FALSE);

$re7=curl_exec($ch7);
curl_close($ch7);
$re7= preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re7);// 適合php7
preg_match_all('/"thumbnail":\{"thumbnails":\[\{"url":"(.*?)",/i',$re7,$piem7,PREG_SET_ORDER);//logo
preg_match_all('/\{"playlistVideoRenderer":\{"videoId":"(.*?)",/i',$re7,$piec7,PREG_SET_ORDER);//vid
preg_match_all('/"shortBylineText":\{"runs":\[\{"text":"(.*?)",/i',$re7,$piek7,PREG_SET_ORDER);//標題
$tru7=count($piec7);
for ($k7 = 0; $k7 < $tru7 && isset($piek7[$k7][1]) && isset($piec7[$k7][1]); $k7++) {
    $logo = isset($piem7[$k7][1]) ? $piem7[$k7][1] : "";
    $chn.= "#EXTINF:-1 tvg-id=\"\" tvg-name=\"\" tvg-logo=\"".$logo."\" group-title=\"youtube街景直播\",".$piek7[$k7][1]."\r\n";
    $chn.= "https://www.youtube.com/watch?v=".$piec7[$k7][1]."\r\n";
}

//$chn. "廣告,#genre#\r\n";
$url10='https://www.youtube.com/playlist?list=PLd8qbe5zE33tN_4OSmIvc1QM82jCP4BI3';//廣告
$ch10=curl_init();
curl_setopt($ch10,CURLOPT_URL,$url10);                  
curl_setopt($ch10,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch10, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch10, CURLOPT_SSL_VERIFYHOST, FALSE);

$re10=curl_exec($ch10);
curl_close($ch10);
$re10= preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re10);// 適合php7
preg_match_all('/"thumbnail":\{"thumbnails":\[\{"url":"(.*?)",/i',$re10,$piem10,PREG_SET_ORDER);//logo
preg_match_all('/\{"playlistVideoRenderer":\{"videoId":"(.*?)",/i',$re10,$piec10,PREG_SET_ORDER);//vid
preg_match_all('/"shortBylineText":\{"runs":\[\{"text":"(.*?)",/i',$re10,$piek10,PREG_SET_ORDER);//標題
$tru10=count($piec10);
for ($k10 = 0; $k10 < $tru10 && isset($piek10[$k10][1]) && isset($piec10[$k10][1]); $k10++) {
    $logo = isset($piem10[$k10][1]) ? $piem10[$k10][1] : "";
    $chn.= "#EXTINF:-1 tvg-id=\"\" tvg-name=\"\" tvg-logo=\"".$logo."\" group-title=\"youtube廣告直播\",".$piek10[$k10][1]."\r\n";
    $chn.= "https://www.youtube.com/watch?v=".$piec10[$k10][1]."\r\n";
}

// 刪除有問題的部分（$url51相關代碼），因為它使用了不完整的YouTube API調用

// 輸出結果
//echo $chn;

// 可選：寫入文件
 file_put_contents($fp, $chn);
?>
