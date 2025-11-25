<?php
// 错误控制
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

header('Content-Type: text/xml;charset=UTF-8');
ini_set("max_execution_time", "3000000");
ini_set('date.timezone','Asia/Shanghai');
$fp = "epgdiyicaijing.xml";

function compress_html($string) {
    $string = str_replace("\r", '', $string);
    $string = str_replace("\n", '', $string);
    $string = str_replace("\t", '', $string);
    return $string;
}

function escape($str) {
    preg_match_all("/[\x80-\xff].|[\x01-\x7f]+/",$str,$r);
    $ar = $r[0];
    foreach($ar as $k=>$v) {
        if(ord($v[0]) < 128)
            $ar[$k] = rawurlencode($v);
        else
            $ar[$k] = "%u".bin2hex(iconv("UTF-8","UCS-2",$v));
    }
    return join("",$ar);
}

// 时间戳转换函数 - 格式: YYYYMMDDHHMMSS +0800
function formatTimestamp($timestamp) {
    return date('YmdHis', $timestamp) . ' +0800';
}

try {
    $weekday2 = date('N');

    // 创建XML文档
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE tv SYSTEM "http://api.torrent-tv.ru/xmltv.dtd"><tv></tv>');
    $xml->addAttribute('generator-info-name', '秋哥綜合');
    $xml->addAttribute('generator-info-url', 'https://www.tdm.com.mo/c_tv/?ch=Satellite');

    $id161 = 100200;
    $cid161 = array(
        array('105','第一财经'),
        array('104','东方财经'),
    );
    $nid161 = sizeof($cid161);

    // 先添加所有频道
    for ($idm161 = 1; $idm161 <= $nid161; $idm161++) {
        $idd161 = $id161 + $idm161;
        $channel = $xml->addChild('channel');
        $channel->addAttribute('id', $cid161[$idm161-1][1]);
        $display_name = $channel->addChild('display-name', $cid161[$idm161-1][1]);
        $display_name->addAttribute('lang', 'zh');
    }

    // 然后为每个频道添加节目
    for ($idm161 = 1; $idm161 <= $nid161; $idm161++) {
        $url161 = 'https://vmsapi.yicai.com/epg/api/tv_program?channel='.$cid161[$idm161-1][0].'&days='.$weekday2;
        $idd161 = $id161 + $idm161;
        $ch161 = curl_init();
        curl_setopt($ch161, CURLOPT_URL, $url161);
        curl_setopt($ch161, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch161, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch161, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch161, CURLOPT_TIMEOUT, 10);
        $re161 = curl_exec($ch161);
        $httpCode = curl_getinfo($ch161, CURLINFO_HTTP_CODE);
        curl_close($ch161);
        
        if ($httpCode !== 200 || empty($re161)) {
            continue; // 如果请求失败，跳过这个频道
        }
        
        $data = json_decode($re161, true);
        
        if (isset($data['epg_data']) && is_array($data['epg_data'])) {
            $channel_id = $cid161[$idm161-1][1];
            
            // 遍历epg_data数组，为每个节目创建programme节点
            foreach ($data['epg_data'] as $program) {
                if (!isset($program['start_time']) || !isset($program['end_time']) || !isset($program['name'])) {
                    continue; // 跳过数据不完整的节目
                }
                
                $programme = $xml->addChild('programme');
                $programme->addAttribute('start', formatTimestamp($program['start_time']));
                $programme->addAttribute('stop', formatTimestamp($program['end_time']));
                $programme->addAttribute('channel', $channel_id);
                
                $title = $programme->addChild('title', htmlspecialchars($program['name']));
                $title->addAttribute('lang', 'zh');
                
                $desc = $programme->addChild('desc', htmlspecialchars($program['name']));
                $desc->addAttribute('lang', 'zh');
            }
        }
    } // 这里缺少了循环的结束大括号

    // 清除可能的多余输出 - 这个位置才是正确的
    ob_clean();
    
    // 格式化XML输出
    $dom = new DOMDocument('1.0');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    
    // 检查XML是否有效
    if (!$dom->loadXML($xml->asXML())) {
        throw new Exception('Invalid XML generated');
    }

    // 输出XML到浏览器
    echo $dom->saveXML();

    // 保存到文件
    file_put_contents($fp, $dom->saveXML());
    
} catch (Exception $e) {
    // 如果发生错误，输出一个基本的有效XML
    ob_clean();
    echo '<?xml version="1.0" encoding="UTF-8"?><tv><error>XML generation failed: ' . htmlspecialchars($e->getMessage()) . '</error></tv>';
    file_put_contents($fp, '<?xml version="1.0" encoding="UTF-8"?><tv><error>XML generation failed</error></tv>');
}
?>
