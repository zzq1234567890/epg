<?php
$xmlFiles = [
    './epgziyong.xml', 
    './epgkai.xml'
];

foreach ($xmlFiles as $file) {  // 外部循环开始
    // 检查文件是否存在
    if (!file_exists($file)) {
        error_log("跳过不存在的文件: $file");
        continue;
    }

    // 加载 XML 文件并检查错误
    $xml = simplexml_load_file($file);
    if ($xml === false) {
        error_log("加载失败: $file");
        continue;
    }

    // 提取第一个节目信息（假设当前文件内节目数据统一）
    $firstProgramme = $xml->programme[0];
    $jsonData = [
        'channel_name' => (string)$firstProgramme['channel'],
        'date' => substr((string)$firstProgramme['start'], 0, 4) . '-' .
                  substr((string)$firstProgramme['start'], 4, 2) . '-' .
                  substr((string)$firstProgramme['start'], 6, 2),
        'epg_data' => []
    ];

    // 遍历当前文件的所有节目（内部循环开始）
    foreach ($xml->programme as $programme) {  // 内部循环开始
        $startTimeStr = (string)$programme['start'];
        $stopTimeStr = (string)$programme['stop'];
        
        // 时间格式转换
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
            continue;
        }
        
        $jsonData['epg_data'][] = [
            'title' => $title,
            'start' => $start,
            'end' => $end
        ];
    }  // 内部循环结束（闭合 foreach ($xml->programme as $programme)）

    // 生成对应文件名的 JSON
    $jsonFileName = basename($file, '.xml') . '.json';
    if (file_put_contents($jsonFileName, json_encode($jsonData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) {
        error_log("成功生成: $jsonFileName");
    } else {
        error_log("写入失败: $jsonFileName");
    }
}  // 外部循环结束（闭合 foreach ($xmlFiles as $file)）
?>  // 闭合 PHP 标签（推荐添加）
