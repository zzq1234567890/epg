<?php
// 读取XML内容
$xmlFiles = [
    './epgziyong.xml'
    // './epgkai.xml'
];

// 最终合并后的EPG数据
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

    // 初始化节目数据存储数组
    $epgData = [];

    // 遍历所有节目
    foreach ($xml->programme as $program) {
        // 获取频道ID和名称
        $channelId = (string)$program['channel'];
        $channelName = $channelId;

        // 解析停止时间并提取日期
        $stopTime = (string)$program['stop'];
        $dateTime = DateTime::createFromFormat('YmdHis O', $stopTime);
        $date = $dateTime->format('Y-m-d');

        // 格式化开始和结束时间
        $startTime = DateTime::createFromFormat('YmdHis O', (string)$program['start']);
        $endTime = $dateTime;

        // 构建节目条目
        $entry = [
            'title' => (string)$program->title,
            'start' => $startTime->format('H:i'),
            'end' => $endTime->format('H:i')
        ];

        // 按日期和频道分组存储
        if (!isset($epgData[$date])) {
            $epgData[$date] = [];
        }
        if (!isset($epgData[$date][$channelName])) {
            $epgData[$date][$channelName] = [];
        }

        array_push($epgData[$date][$channelName], $entry);
    }

    // 将当前文件的数据合并到总数据
    foreach ($epgData as $date => $channels) {
        foreach ($channels as $channelName => $programs) {
            // 检查是否已存在相同日期和频道
            $found = false;
            foreach ($allEpgData as &$item) {
                if ($item['date'] == $date && $item['channel_name'] == $channelName) {
                    $item['epg_data'] = array_merge($item['epg_data'], $programs);
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $allEpgData[] = [
                    'channel_name' => $channelName,
                    'date' => $date,
                    'epg_data' => $programs
                ];
            }
        }
    }
}

// 输出最终合并后的JSON
file_put_contents('epgdiyp.json', json_encode($allEpgData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
?>
