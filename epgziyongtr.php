<?php
/**
 * 修改说明：
 * 1. 增加了 is_chinese 函数用于检测文本是否包含中文。
 * 2. 增加了 translate_to_chinese 函数，利用 Google Translate 免费接口进行翻译。
 * 3. 在合并节目（programme）时，对非中文的标题（title）和描述（desc）进行翻译。
 */

// 定义要合并的XML文件列表
$xmlFiles = [
    './epgmbcae.xml',
    './epgdubaione.xml',
    './epgkbsworld.xml',
    './epgzhongtianam.xml',
    //'./epgdiyicaijing.xml',
   // './epgnewshanghai.xml',
   // './epgkai1.xml',
   // './epglifetv.xml',
   // './epgmytvsuper.xml',
    //'./epganywhere.xml',
   // './epgmacocable.xml',
   // './epg4gtv2.xml', 
    //'./epgtvsou.xml',
   // './epgofiii.xml', 
   // './epgnewhebei.xml',
    //'./epgnewguangdong.xml',
    './epgastro.xml',
    './epgmncvision.xml',
    './epgunifi.xml'
];

/**
 * 检测字符串是否包含中文字符
 */
function is_chinese($string) {
    return preg_match('/[\x{4e00}-\x{9fa5}]/u', $string);
}

/**
 * 将文本翻译为中文（使用 Google Translate 免费接口）
 */
function translate_to_chinese($text) {
    if (empty($text) || is_chinese($text)) {
        return $text;
    }

    $url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=auto&tl=zh-CN&dt=t&q=" . urlencode($text);
    
    // 设置超时，避免接口响应慢导致脚本卡死
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        ]
    ]);

    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        return $text; // 翻译失败则返回原文
    }

    $result = json_decode($response, true);
    if (isset($result[0])) {
        $translatedText = '';
        foreach ($result[0] as $sentence) {
            $translatedText .= $sentence[0];
        }
        return $translatedText;
    }

    return $text;
}

// 创建新的SimpleXMLElement对象作为根元素
$mergedXml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><tv></tv>');

// 第一步：合并 <channel> 元素
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

    if (isset($xml->channel)) {
        foreach ($xml->channel as $channel) {
            $mergedChannel = $mergedXml->addChild('channel');
            $mergedChannel->addAttribute('id', (string)$channel['id']);
            
            $displayName = htmlspecialchars((string)$channel->{'display-name'}, ENT_XML1);
            $mergedChannel->addChild('display-name', $displayName)->addAttribute('lang', (string)$channel->{'display-name'}['lang']);
        }
    }
}

// 第二步：合并 <programme> 元素并进行翻译
foreach ($xmlFiles as $file) {
    if (!file_exists($file)) {
        continue;
    }

    $xml = simplexml_load_file($file);
    if ($xml === false) {
        continue;
    }

    if (isset($xml->programme)) {
        foreach ($xml->programme as $programme) {
            $mergedProgramme = $mergedXml->addChild('programme');
            $mergedProgramme->addAttribute('start', (string)$programme['start']);
            $mergedProgramme->addAttribute('stop', (string)$programme['stop']);
            $mergedProgramme->addAttribute('channel', (string)$programme['channel']);
            
            // 处理标题
            $titleRaw = (string)$programme->title;
            if (!is_chinese($titleRaw)) {
                $titleRaw = translate_to_chinese($titleRaw);
            }
            $title = htmlspecialchars($titleRaw, ENT_XML1);
            $mergedProgramme->addChild('title', $title)->addAttribute('lang', 'zh');
            
            // 处理描述
            if (isset($programme->desc)) {
                $descRaw = (string)$programme->desc;
                if (!empty($descRaw) && !is_chinese($descRaw)) {
                    $descRaw = translate_to_chinese($descRaw);
                }
                $desc = htmlspecialchars($descRaw, ENT_XML1);
                $mergedProgramme->addChild('desc', $desc)->addAttribute('lang', 'zh');
            }
        }
    }
}

// 使用DOMDocument进行格式化输出
$dom = new DOMDocument('1.0');
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($mergedXml->asXML());
$dom->save('epgziyongtr.xml');

$xmlContent = file_get_contents('epgziyongtr.xml');
$gz = gzopen('epgziyongtr.xml.gz', 'w9');
if ($gz !== false) {
    gzwrite($gz, $xmlContent);
    gzclose($gz);
    echo "XML文件合并并翻译完成，已保存为 epgziyongtr.xml 和 epgziyong.xml.gz\n";
} else {
    echo "创建 gz 文件失败\n";
}
?>
