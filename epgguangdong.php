<?php
/**
 * 广东广电 EPG → XMLTV
 * 兼容 PHP 7.4 及以下（无箭头函数）
 *
 * 频道 ID / programme 的 channel 均使用「电视台名称」
 * 用法：
 *   epgguangdong1.php                       输出全部频道 今+明 两天，并落地 epgguangdong.xml
 *   epgguangdong1.php?days=3                输出今天起 3 天
 *   epgguangdong1.php?channels=广东卫视,广东珠江   按名称过滤
 *   epgguangdong1.php?channels=gdws,gdzj    按拼音过滤（仍兼容）
 *   epgguangdong1.php?nosave=1              只输出不写盘
 *
 * 输出时间格式：20261113000000 +0800
 */
date_default_timezone_set('Asia/Shanghai');

$gdtv_sign_key = 'dfkcY1c3sfuw1Cii9DWj8UO3iQy2hqlDxyvDXd1oVMxwYVDSgeB6phO9eW1dfuwX';
$gdtv_ca_key   = '89541943007407288657755311868534';
$gdtv_device   = 'WEB_9b6aa720-df28-11f1-b7ce-354b08786002';

// 频道表：[拼音别名, 官方数字ID, 电视台名称]
// 别名为保留的兼容标识；XMLTV 输出一律用「电视台名称」作为 channel id
$channel_map = array(
    'gdws'   => array('43',  '广东卫视'),
    'gdzj'   => array('44',  '广东珠江'),
    'gdxw'   => array('45',  '广东新闻'),
    'gdms'   => array('48',  '广东民生'),
    'gdty'   => array('47',  '广东体育'),
    'nfws'   => array('51',  '广东大湾区卫视'),
    'dwqws2' => array('46',  '广东大湾区卫视海外版'),
    //'jjkj'   => array('49',  '广东经济科教'),
    'gdys'   => array('53',  '广东影视'),
    'gd4k'   => array('16',  '广东4K超高清'),
    'gdse'   => array('54',  '广东少儿'),
    'jjkt'   => array('66',  '广东嘉佳卡通'),
    'nfgw'   => array('42',  '南方购物'),
    'lnxq'   => array('15',  '广东岭南戏曲'),
    'gdyd'   => array('74',  '广东移动'),
    //'xdjy'   => array('111', '广东现代教育'),
   // 'gdjk'   => array('99',  'GRTN健康频道'),
   // 'gdwh'   => array('75',  'GRTN文化频道'),
    'gdsh'   => array('102', 'GRTN生活频道'),
   // 'gdjy'   => array('104', 'GRTN教育频道'),
);

/* -------------------- HTTP 请求 -------------------- */
function gdtv_request($url)
{
    global $gdtv_sign_key, $gdtv_ca_key, $gdtv_device;

    $t    = round(microtime(true) * 1000);
    $sign = base64_encode(hash_hmac("SHA256", "GET\n$url\n$t\n", $gdtv_sign_key, true));

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_ENCODING       => '',
        CURLOPT_HTTPHEADER     => array(
            'Referer: https://www.gdtv.cn/',
            'Origin: https://www.gdtv.cn',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:160.0) Gecko/20100101 Firefox/160.0',
            'Accept: application/json, text/plain, */*',
            'Content-Type: application/json',
            'X-ITOUCHTV-Ca-Key: '      . $gdtv_ca_key,
            'X-ITOUCHTV-Ca-Signature: '. $sign,
            'X-ITOUCHTV-Ca-Timestamp: '. $t,
            'X-ITOUCHTV-CLIENT: WEB_PC',
            'X-ITOUCHTV-DEVICE-ID: '   . $gdtv_device,
        ),
    ));
    $data = curl_exec($ch);
    $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($data === false || $http >= 400) {
        error_log("gdtv epg http=$http err=$err url=$url");
        return null;
    }
    return $data;
}

/* -------------------- 缓存（30 分钟） -------------------- */
function cache_get($key, $ttl = 1800)
{
    $f = __DIR__ . '/epg_cache/' . md5($key) . '.json';
    if (!file_exists($f) || time() - filemtime($f) >= $ttl) return null;
    $d = @file_get_contents($f);
    if ($d === false) return null;
    $a = json_decode($d, true);
    return is_array($a) ? $a : null;
}
function cache_set($key, $val)
{
    $dir = __DIR__ . '/epg_cache';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    @file_put_contents($dir . '/' . md5($key) . '.json', json_encode($val, JSON_UNESCAPED_UNICODE));
}

/* -------------------- 抓单频道某日期段节目 -------------------- */
function fetch_menus($pk, $from, $to)
{
    $ck = "menu|$pk|$from|$to";
    $cached = cache_get($ck);
    if ($cached !== null) return $cached;

    $url = "https://gdtv-api.gdtv.cn/api/tv/v2/tvMenu?tvChannelPk={$pk}&beginAt={$from}&endAt={$to}";
    $raw = gdtv_request($url);
    if ($raw === null) return array();

    $json = json_decode($raw, true);
    if (!isset($json['resultList']) || !is_array($json['resultList'])) return array();

    $menus = array();
    foreach ($json['resultList'] as $day) {
        if (empty($day['tvMenus'])) continue;
        foreach ($day['tvMenus'] as $m) {
            if (empty($m['beginAt']) || empty($m['endAt'])) continue;
            $menus[] = array(
                'beginAt' => (int) $m['beginAt'],
                'endAt'   => (int) $m['endAt'],
                'name'    => isset($m['name']) ? $m['name'] : '',
            );
        }
    }

    usort($menus, function ($a, $b) {
        if ($a['beginAt'] === $b['beginAt']) return 0;
        return $a['beginAt'] < $b['beginAt'] ? -1 : 1;
    });

    $seen = array();
    $unique = array();
    foreach ($menus as $m) {
        $k = $m['beginAt'];
        if (isset($seen[$k])) continue;
        $seen[$k] = true;
        $unique[] = $m;
    }

    cache_set($ck, $unique);
    return $unique;
}

/* -------------------- XMLTV 时间格式 -------------------- */
function xmltv_time($ms)
{
    return date('YmdHis', intdiv($ms, 1000)) . ' +0800';
}

/* -------------------- XML 转义 -------------------- */
function xml_esc($s)
{
    return htmlspecialchars((string)$s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

/* -------------------- 频道过滤（名称 或 别名均可） -------------------- */
$days = isset($_GET['days']) ? max(1, min(7, (int)$_GET['days'])) : 2;

$selected = null;
if (isset($_GET['channels']) && $_GET['channels'] !== '') {
    $alias2name = array();
    $name2alias = array();
    foreach ($channel_map as $alias => $info) {
        $alias2name[$alias] = $info[1];
        $name2alias[$info[1]] = $alias;
    }

    $selected = array();
    foreach (explode(',', $_GET['channels']) as $raw) {
        $raw = trim($raw);
        if ($raw === '') continue;
        if (isset($name2alias[$raw])) {
            $selected[] = $name2alias[$raw];
        } elseif (isset($alias2name[$raw])) {
            $selected[] = $raw;
        } else {
            foreach ($name2alias as $name => $alias) {
                if (mb_strtolower($name) === mb_strtolower($raw)) {
                    $selected[] = $alias;
                    break;
                }
            }
        }
    }
    if (empty($selected)) {
        header('HTTP/1.1 400 Bad Request');
        header('Content-Type: text/plain; charset=utf-8');
        exit('400 - channels 参数未匹配任何频道，可用名称：' . implode('、', array_values($alias2name)));
    }
}

/* -------------------- 主流程 -------------------- */
$tz    = new DateTimeZone('Asia/Shanghai');
$today = new DateTime('today', $tz);
$from  = $today->format('Y-m-d');
$to    = (clone $today)->modify('+' . ($days - 1) . ' days')->format('Y-m-d');

$programmes = array();
foreach ($channel_map as $alias => $info) {
    if ($selected !== null && !in_array($alias, $selected, true)) continue;

    $pk   = $info[0];
    $name = $info[1];
    $menus = fetch_menus($pk, $from, $to);
    foreach ($menus as $m) {
        $programmes[] = array(
            'channel' => $name,
            'start'   => $m['beginAt'],
            'stop'    => $m['endAt'],
            'title'   => $m['name'],
        );
    }
}

/* -------------------- 拼装 XMLTV（先存字符串，再输出+写盘） -------------------- */
$xmlOut  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xmlOut .= '<tv generator-info-name="gdtv-epg" generator-info-url="https://www.gdtv.cn/">' . "\n";

// channel 节点：id = 电视台名称
foreach ($channel_map as $alias => $info) {
    if ($selected !== null && !in_array($alias, $selected, true)) continue;
    $name = $info[1];
    $xmlOut .= '  <channel id="' . xml_esc($name) . '">' . "\n";
    $xmlOut .= '    <display-name lang="zh">' . xml_esc($name) . '</display-name>' . "\n";
    $xmlOut .= '  </channel>' . "\n";
}

// programme 节点：channel = 电视台名称
foreach ($programmes as $p) {
    if ($p['title'] === '') continue;
    $xmlOut .= '  <programme start="' . xmltv_time($p['start'])
             . '" stop="' . xmltv_time($p['stop'])
             . '" channel="' . xml_esc($p['channel']) . '">' . "\n";
    $xmlOut .= '    <title lang="zh">' . xml_esc($p['title']) . '</title>' . "\n";
    $xmlOut .= '  </programme>' . "\n";
}

$xmlOut .= '</tv>' . "\n";

/* -------------------- 落地 epgguangdong.xml -------------------- */
// 默认写盘；?nosave=1 关闭
$doSave = !isset($_GET['nosave']) || $_GET['nosave'] === '' || $_GET['nosave'] === '0';
if ($doSave && $xmlOut !== '') {
    $target = __DIR__ . '/epgguangdong.xml';
    $written = @file_put_contents($target, $xmlOut, LOCK_EX);
    if ($written === false) {
        error_log("gdtv epg: 写入 $target 失败，请检查目录写权限");
    }
}

/* -------------------- 输出 XMLTV -------------------- */
header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: no-store');
header('Access-Control-Allow-Origin: *');
echo $xmlOut;
