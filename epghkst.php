<?php
/**
* 香港電台 TV31-TV35 EPG 抓取器
* 直接解析 RTHK timetable 頁面的 slideBlock/shdBlock，輸出 XMLTV。
*/
declare(strict_types = 1);

date_default_timezone_set('Asia/Hong_Kong');
ini_set('memory_limit', '256M');
set_time_limit(300);
ini_set('display_errors', '0');
error_reporting(E_ALL);

$channels = [
    'tv31' => '香港電台31',
    'tv32' => '香港電台32',
    'tv33' => '香港電台33',
    'tv34' => '香港電台34',
    'tv35' => '香港電台35',
];
$timezone = '+0800';
$timezoneObject = new DateTimeZone('Asia/Hong_Kong');
$userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/124 Safari/537.36';

function cleanText(string $text): string
{
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', $text) ?? preg_replace('/\s+/', ' ', $text);
    return trim((string)$text);
}

function xmlText(string $text): string
{
    return htmlspecialchars(trim($text), ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function fetchPage(string $channel, string $userAgent) {
    $channelUrls = [
        'https://www.rthk.hk/timetable/' . rawurlencode($channel) . '?lang=zh-hant',
    ];
    // RTHK 對 TV31/TV32 同時保留 dtt31/dtt32 別名，主頁失敗時使用備用網址。
    if (preg_match('/^tv(3[12])$/', $channel, $match)) {
        $channelUrls[] = 'https://www.rthk.hk/timetable/dtt' . $match[1] . '?lang=zh-hant';
    }
    $lastError = '';

    foreach ($channelUrls as $url) {
        // RTHK 偶爾會短暫回傳 403/5xx，最多重試 3 次。
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_ENCODING => '',
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_USERAGENT => $userAgent,
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_HTTPHEADER => [
                    'Accept: text/html,application/xhtml+xml',
                    'Accept-Language: zh-Hant,zh-TW;q=0.9,en;q=0.8',
                    'Cache-Control: no-cache',
                ],
            ]);
            $html = curl_exec($ch);
            $lastError = curl_error($ch);
            $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($html !== false && $status >= 200 && $status < 400 && strlen($html) > 1000) {
                return $html;
            }
            usleep(300000 * $attempt);
        }
    }

    error_log("RTHK fetch failed: {$url}; HTTP={$status}; cURL={$lastError}");
    return false;
}

function extractTagText(string $html, string $tag): string
{
    if (!preg_match('~<' . preg_quote($tag, '~') . '\\b[^>]*>(.*?)</' . preg_quote($tag, '~') . '>~is', $html, $m)) {
        return '';
    }
    return cleanText(strip_tags($m[1]));
}

function parseSchedule(string $html, string $dateYmd, DateTimeZone $timezoneObject): array
{
    // 找出指定日期的整個 slideBlock；允許 class/date 屬性任意排列。
    $datePattern = preg_quote($dateYmd, '~');
    $slidePattern = '~<div\b(?=[^>]*\bclass=["\'][^"\']*\bslideBlock\b[^"\']*["\'])(?=[^>]*\bdate=["\']' . $datePattern . '["\'])[^>]*>~is';
    if (!preg_match($slidePattern, $html, $startMatch, PREG_OFFSET_CAPTURE)) {
        error_log("RTHK date block not found: {$dateYmd}");
        return [];
    }

    $blockStart = (int)$startMatch[0][1];
    $nextBlock = preg_match('~<div\\b[^>]*class=["\'][^"\']*\\bslideBlock\\b~is', $html, $nextMatch, PREG_OFFSET_CAPTURE, $blockStart + strlen($startMatch[0][0]))
    ? (int)$nextMatch[0][1] : strlen($html);
    $block = substr($html, $blockStart, $nextBlock - $blockStart);

    preg_match_all('~<div\\b[^>]*class=["\'][^"\']*\\bshdBlock\\b[^"\']*["\'][^>]*>~is', $block, $itemStarts, PREG_OFFSET_CAPTURE);
    $programs = [];
    $itemCount = count($itemStarts[0]);

    for ($i = 0; $i < $itemCount; $i++) {
        $itemStart = (int)$itemStarts[0][$i][1];
        $itemEnd = ($i + 1 < $itemCount) ? (int)$itemStarts[0][$i + 1][1] : strlen($block);
        $item = substr($block, $itemStart, $itemEnd - $itemStart);

        preg_match_all('~<p\\b[^>]*class=["\'][^"\']*\\btimeDis\\b(?![^"\']*\\bvertDash\\b)[^"\']*["\'][^>]*>(.*?)</p>~is', $item, $timeMatches);
        $times = [];
        foreach ($timeMatches[1] as $time) {
            $time = cleanText(strip_tags($time));
            if (preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $time)) {
                $times[] = $time;
            }
        }
        if (count($times) < 1) continue;

        $title = '';
        if (preg_match('~<[^>]*class=["\'][^"\']*\\bshTitle\\b[^"\']*["\'][^>]*>(.*?)</[^>]+>~is', $item, $titleMatch)) {
            $title = cleanText(strip_tags($titleMatch[1]));
        }
        if ($title === '') continue;

        $subtitle = '';
        if (preg_match('~<[^>]*class=["\'][^"\']*\\bshSubTitle\\b[^"\']*["\'][^>]*>(.*?)</[^>]+>~is', $item, $subMatch)) {
            $subtitle = cleanText(strip_tags($subMatch[1]));
        }

        $start = new DateTimeImmutable($dateYmd . ' ' . $times[0] . ':00', $timezoneObject);
        if (isset($times[1])) {
            $stop = new DateTimeImmutable($dateYmd . ' ' . $times[1] . ':00', $timezoneObject);
            if ($stop <= $start) $stop = $stop->modify('+1 day');
        } else {
            $stop = null;
        }

        $programs[] = [
            'start' => $start,
            'stop' => $stop,
            'title' => $title,
            'desc' => $subtitle,
        ];
    }

    // 沒有明確結束時間時，以下一節目開始時間或翌日 00:00 補足。
    foreach ($programs as $i => &$program) {
        if ($program['stop'] instanceof DateTimeImmutable) continue;
        $program['stop'] = isset($programs[$i + 1])
        ? $programs[$i + 1]['start']
        : (new DateTimeImmutable($dateYmd . ' 00:00:00', $timezoneObject))->modify('+1 day');
    }
    unset($program);
    return $programs;
}

function xmlTime(DateTimeImmutable $dateTime, string $timezone): string
{
    return $dateTime->format('YmdHis') . ' ' . $timezone;
}

$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$xml .= "<!DOCTYPE tv SYSTEM \"https://raw.githubusercontent.com/XMLTV/xmltv/master/xmltv.dtd\">\n";
$xml .= "<tv generator-info-name=\"RTHK TV EPG PHP\" generator-info-url=\"https://www.rthk.hk/timetable/tv33\">\n";

foreach ($channels as $channelId => $channelName) {
    $xml .= '<channel id="' . xmlText($channelName) . '"><display-name lang="zh">' . xmlText($channelName) . "</display-name></channel>\n";
}

$today = new DateTimeImmutable('today', $timezoneObject);
$dates = [$today, $today->modify('+1 day')];
foreach ($channels as $channelId => $channelName) {
    $html = fetchPage($channelId, $userAgent);
    if ($html === false) continue;

    foreach ($dates as $dateObject) {
        $dateYmd = $dateObject->format('Ymd');
        $programs = parseSchedule($html, $dateYmd, $timezoneObject);
        error_log("RTHK {$channelId} {$dateYmd}: " . count($programs) . ' programmes');

        foreach ($programs as $program) {
            $xml .= '<programme channel="' . xmlText($channelName) . '" start="' . xmlTime($program['start'], $timezone) . '" stop="' . xmlTime($program['stop'], $timezone) . '">' . "\n";
            $xml .= '<title lang="zh">' . xmlText($program['title']) . "</title>\n";
            if ($program['desc'] !== '') {
                $xml .= '<desc lang="zh">' . xmlText($program['desc']) . "</desc>\n";
            }
            $xml .= "</programme>\n";
        }
    }
}
$xml .= "</tv>\n";

$outputPath = __DIR__ . '/epghkst.xml';
if (file_put_contents($outputPath, $xml) === false) {
    http_response_code(500);
    exit("無法寫入 EPG 檔案：{$outputPath}\n");
}
echo $xml;
?>