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
        try {
            // 获取频道ID和名称
            $channelId = (string)$program['channel'];
            $channelName = $channelId;

            // 解析时间字段
            $startTimeStr = (string)$program['start'];
            $stopTimeStr = (string)$program['stop'];
            
            // 处理时间格式
            $startTime = DateTime::createFromFormat('YmdHis O', $startTimeStr);
            $endTime = DateTime::createFromFormat('YmdHis O', $stopTimeStr);
            
            // 验证时间解析结果
            if (!$startTime || !$endTime) {
                throw new Exception("时间格式错误: start=$startTimeStr, stop=$stopTimeStr");
            }
            
            // 提取日期（从结束时间获取）
            $date = $endTime->format('Y-m-d');

            // 构建节目条目
            $entry = [
                'title' => (string)$program->title,
                'start' => $startTime->format('H:i'),
                'end' => $endTime->format('H:i')
            ];

            // 按日期和频道分组存储
            if (!isset($epgData[$date][$channelName])) {
                $epgData[$date][$channelName] = [];
            }
            array_push($epgData[$date][$channelName], $entry);
            
        } catch (Exception $e) {
            error_log("节目解析失败: " . $e->getMessage());
            continue;
        }
    }

    // 合并数据到总集合...（保持原有合并逻辑）
}

// 输出最终JSON...（保持原有输出逻辑）
?>
