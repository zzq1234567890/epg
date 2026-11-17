<?php
/**
 * PHP 7.4 河南电视台节目表抓取 → 生成 epghenan.xml (XMLTV)
 *
 * 用法：
 *   php epghenan.php                    # 抓取今天，写入 epghenan.xml
 *   php epghenan.php 2026-11-01         # 抓取指定日期，写入 epghenan.xml
 *
 * Web 调用：
 *   /epghenan.php?date=2026-11-01       # 写文件并返回 XMLTV
 */

date_default_timezone_set('Asia/Shanghai');

$date = isset($_GET['date']) ? $_GET['date'] : (isset($argv[1]) ? $argv[1] : date('Y-m-d'));

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    fail('date 必须是 YYYY-MM-DD，例如 2026-11-01');
}
$dt = DateTime::createFromFormat('!Y-m-d', $date, new DateTimeZone('Asia/Shanghai'));
if (!$dt || $dt->format('Y-m-d') !== $date) {
    fail('date 不是有效日期');
}

$result = array(
    'sources' => array(
        'hntv' => fetchSource('河南电视台全部频道', function () use ($date) {
            return fetchHntvAll($date);
        })
    )
);

$output = buildXmltv($result);

// ============ 原子写文件：临时文件 + rename，不用 LOCK_EX ============
$file     = __DIR__ . '/epghenan.xml';
$writeOk  = false;
$writeErr = '';

if (!is_dir(__DIR__)) {
    $writeErr = '目录不存在：' . __DIR__;
} elseif (!is_writable(__DIR__)) {
    $writeErr = '目录不可写：' . __DIR__ . '（请执行 chmod 775 或调整属主）';
} else {
    $tmp = $file . '.tmp.' . getmypid();
    $bytes = @file_put_contents($tmp, $output);
    if ($bytes === false) {
        $last = error_get_last();
        $writeErr = 'file_put_contents 失败' . ($last ? '：' . $last['message'] : '');
        @unlink($tmp);
    } elseif (!@rename($tmp, $file)) {
        $last = error_get_last();
        $writeErr = 'rename 失败' . ($last ? '：' . $last['message'] : '');
        @unlink($tmp);
    } else {
        $writeOk = true;
    }
}

// ============ 输出 ============
if (PHP_SAPI === 'cli') {
    if ($writeOk) {
        fwrite(STDERR, "已生成 {$file}（{$bytes} bytes）\n");
    } else {
        fwrite(STDERR, "写入失败：{$writeErr}\n");
        fwrite(STDERR, "改输出到标准输出：\n" . $output);
        exit(1);
    }
} else {
    header('Content-Type: application/xml; charset=utf-8');
    if (!$writeOk) {
        $safeErr = str_replace('--', '- -', $writeErr);
        echo str_replace(
            '<tv ',
            "<!-- 写文件失败：{$safeErr} -->\n<tv ",
            $output
        );
    } else {
        echo $output;
    }
}

// ============ 工具函数 ============
function xmlEscape($value)
{
    return htmlspecialchars((string)$value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
}

function xmlTime($unix)
{
    if (!$unix) {
        return '';
    }
    return date('YmdHis O', (int)$unix);
}

function buildXmltv(array $result)
{
    $xml   = array();
    $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml[] = '<tv generator-info-name="PHP 7.4 EPG Fetcher" generator-info-url="https://static.hntv.tv/kds/">';

    $sources = $result['sources'];

    foreach ($sources as $sourceKey => $source) {
        if (!isset($source['ok']) || !$source['ok'] || !isset($source['data']['channels'])) {
            continue;
        }
        foreach ($source['data']['channels'] as $channel) {
            $channelName = isset($channel['channel_name']) ? $channel['channel_name'] : $sourceKey;
            $xml[] = '  <channel id="' . xmlEscape($channelName) . '">';
            $xml[] = '    <display-name lang="zh">' . xmlEscape($channelName) . '</display-name>';
            $xml[] = '  </channel>';
        }
    }

    foreach ($sources as $sourceKey => $source) {
        if (!isset($source['ok']) || !$source['ok'] || !isset($source['data']['channels'])) {
            continue;
        }
        foreach ($source['data']['channels'] as $channel) {
            $channelName = isset($channel['channel_name']) ? $channel['channel_name'] : $sourceKey;
            foreach ($channel['programs'] as $program) {
                if (empty($program['start_unix']) || empty($program['end_unix'])) {
                    continue;
                }
                $xml[] = '  <programme start="' . xmlEscape(xmlTime($program['start_unix'])) .
                    '" stop="' . xmlEscape(xmlTime($program['end_unix'])) .
                    '" channel="' . xmlEscape($channelName) . '">';
                $xml[] = '    <title lang="zh">' . xmlEscape($program['title']) . '</title>';
                $xml[] = '  </programme>';
            }
        }
    }

    foreach ($sources as $source) {
        if (!isset($source['ok']) || !$source['ok']) {
            $message = isset($source['error']) ? $source['error'] : '未知错误';
            $message = str_replace('--', '- -', $message);
            $xml[]   = '  <!-- 数据源失败：' . xmlEscape($message) . ' -->';
            continue;
        }
        if (isset($source['data']['errors']) && is_array($source['data']['errors'])) {
            foreach ($source['data']['errors'] as $error) {
                $message  = isset($error['channel_name']) ? $error['channel_name'] . ': ' : '';
                $message .= isset($error['error']) ? $error['error'] : '未知错误';
                $message  = str_replace('--', '- -', $message);
                $xml[]    = '  <!-- 频道抓取失败：' . xmlEscape($message) . ' -->';
            }
        }
    }

    $xml[] = '</tv>';
    return implode("\n", $xml) . "\n";
}

function fetchSource($name, callable $callback)
{
    try {
        return array('ok' => true, 'data' => $callback());
    } catch (Exception $e) {
        return array('ok' => false, 'source' => $name, 'error' => $e->getMessage());
    }
}

function httpGet($url, array $headers = array())
{
    if (!function_exists('curl_init')) {
        throw new Exception('PHP 未安装 curl 扩展');
    }
    $lastError = '';
    for ($try = 1; $try <= 3; $try++) {
        $ch = curl_init($url);
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT        => 45,
            CURLOPT_ENCODING       => '',
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36',
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0
        );
        if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
            $options[CURLOPT_IPRESOLVE] = CURL_IPRESOLVE_V4;
        }
        curl_setopt_array($ch, $options);
        $body   = curl_exec($ch);
        $errno  = curl_errno($ch);
        $error  = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body !== false && !$errno && $status >= 200 && $status < 300) {
            return $body;
        }
        $lastError = 'HTTP ' . $status . ', cURL ' . $errno . ': ' . $error;
        if ($try < 3) {
            usleep(300000 * $try);
        }
    }
    throw new Exception('请求失败（已重试3次）：' . $lastError . '；URL：' . $url);
}

function fetchHntvAll($date)
{
    $seed = '6ca114a836ac7d73';
    $now  = time();
    $sign = hash('sha256', $seed . $now);
    $headers = array(
        'Accept: application/json',
        'Referer: https://static.hntv.tv/kds/',
        'sign: ' . $sign,
        'timestamp: ' . $now
    );

    $dateObj  = DateTime::createFromFormat('!Y-m-d', $date, new DateTimeZone('Asia/Shanghai'));
    $midnight = $dateObj->getTimestamp();
    $listUrl  = 'https://pubmod.hntv.tv/program/getAuth/live/class/program/11/';
    $listRaw  = httpGet($listUrl, $headers);
    $list     = json_decode($listRaw, true);
    if (!is_array($list)) {
        throw new Exception('河南频道列表返回格式异常：' . substr($listRaw, 0, 200));
    }

    $channels = array();
    $errors   = array();
    foreach ($list as $item) {
        if (!isset($item['cid'])) {
            continue;
        }
        $cid         = (int)$item['cid'];
        $rawName     = isset($item['name']) ? trim($item['name']) : ('频道' . $cid);
        $channelName = (strpos($rawName, '河南') === 0) ? $rawName : '河南' . $rawName;
        $programUrl  = 'https://pubmod.hntv.tv/program/getAuth/vod/originStream/program/' .
            $cid . '/' . $midnight;
        try {
            $programRaw = httpGet($programUrl, $headers);
            $json       = json_decode($programRaw, true);
            if (!is_array($json) || !isset($json['programs'])) {
                throw new Exception('节目接口返回格式异常');
            }
            $programs = array();
            foreach ($json['programs'] as $program) {
                $programs[] = normalizeProgram(
                    isset($program['beginTime']) ? $program['beginTime'] : null,
                    isset($program['endTime'])   ? $program['endTime']   : null,
                    isset($program['title'])     ? $program['title']     : ''
                );
            }
            $channels[] = array(
                'channel_id'   => $cid,
                'channel_name' => $channelName,
                'programs'     => $programs
            );
        } catch (Exception $e) {
            $errors[] = array('channel_id' => $cid, 'channel_name' => $channelName, 'error' => $e->getMessage());
        }
    }

    return array('channels' => $channels, 'errors' => $errors);
}

function normalizeProgram($begin, $end, $title)
{
    return array(
        'start_unix' => is_numeric($begin) ? (int)$begin : null,
        'end_unix'   => is_numeric($end)   ? (int)$end   : null,
        'title'      => (string)$title
    );
}

function fail($message)
{
    http_response_code(400);
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, '错误：' . $message . "\n");
    } else {
        header('Content-Type: text/plain; charset=utf-8');
        echo '错误：' . $message . "\n";
    }
    exit(1);
}
