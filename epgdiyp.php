<?php
/**
 * EPG XML 转 DIYP JSON 格式工具
 * 修复时间解析错误和数据结构问题
 */

// 配置参数
$xmlFiles = [
    './epgziyong.xml',
    // './epgkai.xml'
];
$outputFile = 'epgdiyp.json';
$timezone = new DateTimeZone('Asia/Shanghai'); // 设置默认时区

// 初始化数据结构
$allEpgData = [];
$errorLog = [];

// 主处理循环
foreach ($xmlFiles as $file) {
    // 文件检查
    if (!file_exists($file)) {
        logError("文件不存在: $file");
        continue;
    }

    // 加载XML
    if (($xml = simplexml_load_file($file)) === false) {
        logError("XML解析失败: $file");
        continue;
    }

    processXml($xml, $allEpgData);
}

// 生成最终JSON
file_put_contents($outputFile, json_encode(array_values($allEpgData), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// 如果有错误则记录
if (!empty($errorLog)) {
    file_put_contents('epg_errors.log', implode(PHP_EOL, $errorLog));
    echo "完成，但有错误。详见 epg_errors.log".PHP_EOL;
} else {
    echo "成功生成 $outputFile".PHP_EOL;
}

/********************
 * 工具函数
 ********************/

/**
 * 处理单个XML文件
 */
function processXml(SimpleXMLElement $xml, array &$allEpgData) {
    foreach ($xml->programme as $program) {
        try {
            // 基础数据校验
            if (!isset($program['channel']) || !isset($program['start']) || !isset($program['stop'])) {
                throw new Exception("缺少必要属性");
            }

            // 获取频道信息
            $channelId = (string)$program['channel'];
            
            // 解析时间
            $startTime = parseTime((string)$program['start']);
            $endTime = parseTime((string)$program['stop']);
            
            // 时间有效性检查
            if ($startTime >= $endTime) {
                throw new Exception("开始时间晚于结束时间");
            }

            // 确定节目日期（基于结束时间）
            $dateKey = $endTime->format('Y-m-d');

            // 构建节目条目
            $entry = [
                'title' => isset($program->title) ? (string)$program->title : '无标题',
                'start' => $startTime->format('H:i'),
                'end' => $endTime->format('H:i')
            ];

            // 存储到数据结构
            $key = "$channelId|$dateKey";
            if (!isset($allEpgData[$key])) {
                $allEpgData[$key] = [
                    'channel_name' => $channelId,
                    'date' => $dateKey,
                    'epg_data' => []
                ];
            }
            array_push($allEpgData[$key]['epg_data'], $entry);

        } catch (Exception $e) {
            logError("节目解析失败: ".$e->getMessage()." | 频道:$channelId");
            continue;
        }
    }
}

/**
 * 时间解析器（加强版）
 */
function parseTime(string $timeStr): DateTime {
    global $timezone;

    // 格式1: 20250413023000 +0800 (带时区)
    if (preg_match('/^(\d{14})\s+([+-]\d{4})$/', $timeStr, $matches)) {
        $datetime = DateTime::createFromFormat(
            'YmdHis P',
            $matches[1].' '.$matches[2],
            $timezone
        );
        if ($datetime) return $datetime;
    }

    // 格式2: 20250413023000 (无时区)
    if (strlen($timeStr) >= 14) {
        $datetime = DateTime::createFromFormat(
            'YmdHis',
            substr($timeStr, 0, 14),
            $timezone
        );
        if ($datetime) return $datetime;
    }

    // 格式3: Unix时间戳
    if (is_numeric($timeStr)) {
        $datetime = new DateTime('@'.((int)$timeStr));
        $datetime->setTimezone($timezone);
        return $datetime;
    }

    throw new Exception("无法解析时间格式: $timeStr");
}

/**
 * 错误记录器
 */
function logError(string $message) {
    global $errorLog;
    $errorLog[] = date('[Y-m-d H:i:s] ') . $message;
}
?>
