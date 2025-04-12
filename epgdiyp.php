<?php
$xmlFiles = [
    './epgziyong.xml'
   // './epgkai.xml'
];

// 合并所有文件的 EPG 数据（假设需要合并为一个 JSON）
$allEpgData = [];

foreach ($xmlFiles as $file) {
    if (!file_exists($file)) {
        error_log("跳过不存在的文件: $file");
        continue;
    }

    $xml = simplexml_load_file($file);
    if ($xml === false) {
        error_log("加载失败: $file");
        continue;
    }

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
        $startTime = substr($startTimeStr, 8, 4);
        $stopTime = substr($stopTimeStr, 8, 4);
        $start = substr($startTime, 0, 2) . ':' . substr($startTime, 2, 2);
        $end = substr($stopTime, 0, 2) . ':' . substr($stopTime, 2, 2);

        $title = '';
        foreach ($programme->title as $titleNode) {
            if ((string)$titleNode['lang'] === 'zh') {
                $title = (string)$titleNode;
                break;
            }
        }
        if (empty($title)) {
            continue;
        }

        $jsonData['epg_data'][] = [
            'title' => $title,
            'start' => $start,
            'end' => $end
        ];
    }

    // 合并多个文件数据（若需要）
    $allEpgData = array_merge($allEpgData, $jsonData['epg_data']);
}

// 生成最终的 epgdiyp.json（关键：写入文件而非输出）
$finalJson = [
      'date' => $jsonData['date'] ?? date('Y-m-d'),
    'channel_name' => $jsonData['channel_name'] ?? '未知频道',  // 处理可能的空数据
  
    'epg_data' => $allEpgData
];

file_put_contents('epgdiyp.json', json_encode($finalJson, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
?>
