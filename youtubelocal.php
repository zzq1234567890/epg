<?php
error_reporting(0); 
date_default_timezone_set('Asia/Shanghai');
header('Content-Type: text/plain; charset=utf-8');

$urlk = "https://raw.githubusercontent.com/zzq1234567890/epg/refs/heads/main/youtubeworld.m3u";
$fp = "youtubelocal.m3u";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $urlk,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
]);

$remote_m3u = curl_exec($ch);

// 错误检查
if(curl_errno($ch)) {
    die('CURL 错误: ' . curl_error($ch));
}

curl_close($ch);

if($remote_m3u === false) {
    die('无法获取远程内容');
}

// 修正：使用 str_replace() 而不是 replace()
$remote_m3u = str_replace(
    'https://www.youtube.com/watch?v',
    'http://127.0.0.1:8081/youtube.php?v', 
    $remote_m3u
);

// 写入文件
if(file_put_contents($fp, $remote_m3u) === false) {
    die('无法写入文件');
}

// 可选：输出成功信息
//echo "文件已成功处理并保存为: $fp\n";娄麓娄脌锟铰甭Ｂ疵ξ? $fp\n";
