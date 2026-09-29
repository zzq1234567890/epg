<?php
// 定义要合并的XML文件列表
$xmlFiles = [
    './epgkankanlive.xml',
    './epglotus.xml',
    './epgOUTtv.xml',
   // './epgbtime.xml',
    './epgjiangsu.xml',
    './epgshanghai23.xml',
    './epgmyvideo.xml',
    './epgfujian.xml',
    './epgcctv.xml',
    './epgrthk.xml',
    './epgfenghuangxiu.xml', 
    './epgtianying.xml',
    './epgeltatv.xml',
    './epgzhongtianam.xml',
   './epgmbcae.xml',
    './epgdubaione.xml',
    './epgkbsworld.xml',
    './epgasia.xml',
    './epgdiyicaijing.xml',
    './epgnewshanghai.xml',
    './epgkai1.xml',
    './epgcatchplay.xml',
    './epganywhere.xml',  
    './epglifetv.xml',
    './epgmytvsuper.xml',
    './epg4gtv2.xml', 
    './epgmacocable.xml',
    //'./epgtvsou.xml',
    './epgofiii.xml', 
    './epgnewhebei.xml',
    './epgnewguangdong.xml',
     './epgdifang.xml',
     './epgastrogo.xml',
     './epgmncvision.xml'
    //'./epgsingtel.xml'
     //'./epgunifi.xml'
 ];

// 创建新的SimpleXMLElement对象作为根元素
$mergedXml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><tv></tv>');

// 修复XML文件内容的函数
function fixXmlContent($content) {
    // 1. 移除或转义非法字符
    $content = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $content);
    
    // 2. 转义未转义的 & 符号（除了已有的实体）
    $content = preg_replace('/&(?!amp;|lt;|gt;|apos;|quot;|#\d+;)/', '&amp;', $content);
    
    // 3. 移除无效的XML声明（如果有多个）
    $content = preg_replace('/<\?xml[^>]*>\s*/', '', $content, -1, $count);
    if ($count > 1) {
        $content = '<?xml version="1.0" encoding="UTF-8"?>' . $content;
    }
    
    return $content;
}

// 加载并清理XML文件的函数
function loadAndCleanXml($file) {
    if (!file_exists($file)) {
        return false;
    }
    
    // 读取文件内容
    $content = file_get_contents($file);
    if ($content === false) {
        error_log("无法读取文件: $file");
        return false;
    }
    
    // 修复XML内容
    $cleanContent = fixXmlContent($content);
    
    // 尝试加载清理后的XML
    libxml_use_internal_errors(true);
    libxml_clear_errors();
    
    $xml = simplexml_load_string($cleanContent);
    
    if ($xml === false) {
        $errors = libxml_get_errors();
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = sprintf("行 %d, 列 %d: %s", $error->line, $error->column, $error->message);
        }
        error_log("XML解析失败: $file - " . implode("; ", $errorMessages));
        
        // 尝试更激进的清理
        $cleanContent = mb_convert_encoding($cleanContent, 'UTF-8', 'UTF-8');
        $cleanContent = iconv('UTF-8', 'UTF-8//IGNORE', $cleanContent);
        $xml = simplexml_load_string($cleanContent);
        
        if ($xml === false) {
            error_log("重试解析也失败: $file");
            return false;
        }
    }
    
    libxml_clear_errors();
    return $xml;
}

// 处理所有XML文件
foreach ($xmlFiles as $file) {
    // 检查文件是否存在
    if (!file_exists($file)) {
        error_log("跳过不存在的文件: $file");
        continue;
    }

    echo "处理文件: $file\n";
    
    // 加载XML文件（使用清理函数）
    $xml = loadAndCleanXml($file);
    if ($xml === false) {
        error_log("加载失败: $file");
        continue;
    }

    // 合并<channel>元素
    if (isset($xml->channel)) {
        foreach ($xml->channel as $channel) {
            // 检查是否已存在相同id的channel
            $existingChannel = $mergedXml->xpath("//channel[@id='" . (string)$channel['id'] . "']");
            if (empty($existingChannel)) {
                $mergedChannel = $mergedXml->addChild('channel');
                $mergedChannel->addAttribute('id', (string)$channel['id']);
                
                $displayName = htmlspecialchars((string)$channel->{'display-name'}, ENT_XML1, 'UTF-8');
                $mergedChannel->addChild('display-name', $displayName)->addAttribute('lang', (string)$channel->{'display-name'}['lang']);
            }
        }
    }
}

// 再次遍历处理programme元素（分离循环以避免重复添加channel）
foreach ($xmlFiles as $file) {
    if (!file_exists($file)) {
        continue;
    }
    
    echo "处理节目表: $file\n";
    
    $xml = loadAndCleanXml($file);
    if ($xml === false) {
        continue;
    }
    
    // 合并<programme>元素
    if (isset($xml->programme)) {
        foreach ($xml->programme as $programme) {
            $mergedProgramme = $mergedXml->addChild('programme');
            $mergedProgramme->addAttribute('start', (string)$programme['start']);
            $mergedProgramme->addAttribute('stop', (string)$programme['stop']);
            $mergedProgramme->addAttribute('channel', (string)$programme['channel']);
            
            $title = htmlspecialchars((string)$programme->title, ENT_XML1, 'UTF-8');
            $titleElement = $mergedProgramme->addChild('title', $title);
            if (isset($programme->title['lang'])) {
                $titleElement->addAttribute('lang', (string)$programme->title['lang']);
            }
            
            if (isset($programme->desc)) {
                $desc = htmlspecialchars((string)$programme->desc, ENT_XML1, 'UTF-8');
                $descElement = $mergedProgramme->addChild('desc', $desc);
                if (isset($programme->desc['lang'])) {
                    $descElement->addAttribute('lang', (string)$programme->desc['lang']);
                }
            }
        }
    }
}

// 使用DOMDocument进行格式化输出
$dom = new DOMDocument('1.0', 'UTF-8');
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($mergedXml->asXML());

// 保存XML文件
$outputFile = 'epgziyong.xml';
if ($dom->save($outputFile)) {
    echo "XML文件合并完成，已保存为 $outputFile\n";
    
    // 创建压缩版本
    $xmlContent = file_get_contents($outputFile);
    if ($xmlContent !== false) {
        $gz = gzopen($outputFile . '.gz', 'w9');
        if ($gz !== false) {
            gzwrite($gz, $xmlContent);
            gzclose($gz);
            echo "压缩文件已保存为 " . $outputFile . ".gz\n";
        } else {
            echo "创建 gz 文件失败\n";
        }
    } else {
        echo "无法读取生成的XML文件\n";
    }
} else {
    echo "保存XML文件失败\n";
}

// 显示统计信息
$channelCount = count($mergedXml->xpath('//channel'));
$programmeCount = count($mergedXml->xpath('//programme'));
echo "\n合并统计:\n";
echo "- 频道数量: $channelCount\n";
echo "- 节目数量: $programmeCount\n";
?>
