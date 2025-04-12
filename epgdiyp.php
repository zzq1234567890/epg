<?php

$xml = simplexml_load_string(epgziyong.xml);
if (!$xml) {
    die("XML 解析失败");
}

// 提取第一个节目信息以确定频道和日期（假设数据统一）
$firstProgramme = $xml->programme[0];
$jsonData = [
    'channel_name' => (string)$firstProgramme['channel'],
    'date' => substr((string)$firstProgramme['start'], 0, 4) . '-' .
              substr((string)$firstProgramme['start'], 4, 2) . '-' .
              substr((string)$firstProgramme['start'], 6, 2),
    'epg_data' => []
];

foreach ($xml->programme as $programme) {
    $startTimeStr = (string)$programme['start'];
    $stopTimeStr = (string)$programme['stop'];
    
    // 提取 HHMM 并转换为 HH:MM
    $startTime = substr($startTimeStr, 8, 4);
    $stopTime = substr($stopTimeStr, 8, 4);
    $start = substr($startTime, 0, 2) . ':' . substr($startTime, 2, 2);
    $end = substr($stopTime, 0, 2) . ':' . substr($stopTime, 2, 2);
    
    // 提取中文标题
    $title = '';
    foreach ($programme->title as $titleNode) {
        if ((string)$titleNode['lang'] === 'zh') {
            $title = (string)$titleNode;
            break;
        }
    }
    if (empty($title)) {
        continue;  // 跳过无中文标题的节目（按需处理）
    }
    
    $jsonData['epg_data'][] = [
        'title' => $title,
        'start' => $start,
        'end' => $end
    ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($jsonData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
