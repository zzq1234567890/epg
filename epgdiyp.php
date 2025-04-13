<?php
/**
 * EPG XML 转 DIYP JSON 格式工具
 * 按照频道和日期分组节目数据
 */

// 输入XML内容
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

  

// 输出文件
$outputFile = 'epgdiyp.json';
$timezone = new DateTimeZone('Asia/Shanghai'); // 设置默认时区

// 解析XML
//$xml = simplexml_load_string($xmlString);
if ($xml === false) {
    die("XML解析失败");
}

// 初始化数据结构
$allEpgData = [];

// 处理所有节目
foreach ($xml->programme as $program) {
    try {
        // 获取频道ID
        $channelId = (string)$program['channel'];
        
        // 解析时间
        $startTime = DateTime::createFromFormat('YmdHis O', (string)$program['start']);
        $endTime = DateTime::createFromFormat('YmdHis O', (string)$program['stop']);
        
        if (!$startTime || !$endTime) {
            throw new Exception("时间格式错误");
        }
        
        // 确定节目日期（基于结束时间）
        $dateKey = $endTime->format('Y-m-d');
        
        // 构建节目条目
        $entry = [
            'title' => (string)$program->title,
            'start' => $startTime->format('H:i'),
            'end' => $endTime->format('H:i')
        ];
        
        // 按频道和日期分组存储
        if (!isset($allEpgData[$channelId])) {
            $allEpgData[$channelId] = [];
        }
        
        if (!isset($allEpgData[$channelId][$dateKey])) {
            $allEpgData[$channelId][$dateKey] = [];
        }
        
        $allEpgData[$channelId][$dateKey][] = $entry;
        
    } catch (Exception $e) {
        // 跳过错误条目
        continue;
    }
}

// 生成最终JSON（按照要求的格式）
$outputJson = '';
foreach ($allEpgData as $channelName => $dates) {
    foreach ($dates as $date => $programs) {
        $outputJson .= "{\n    \"channel_name\": \"$channelName\",\n";
        $outputJson .= " \"date\": \"$date\",{\n\n    \"epg_data\": [\n";
        
        foreach ($programs as $index => $program) {
            $outputJson .= "        {\n";
            $outputJson .= "            \"title\": \"{$program['title']}\",\n";
            $outputJson .= "            \"start\": \"{$program['start']}\",\n";
            $outputJson .= "            \"end\": \"{$program['end']}\"\n";
            $outputJson .= "        }" . ($index < count($programs) - 1 ? "," : "") . "\n";
        }
        
        $outputJson .= "    ]\n}},\n";
    }
}

// 移除最后一个逗号和换行
$outputJson = rtrim($outputJson, ",\n");

// 写入文件
file_put_contents($outputFile, $outputJson);

echo "成功生成 $outputFile".PHP_EOL;
?>
