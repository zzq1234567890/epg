<?php
// 要刪除的頻道 ID 列表
$channelsToRemove = ['VOA美國之音','新唐人亞太台', '成人節目資訊','冰火頻道','成人極品台','彩虹頻道','松視4台','Pandora潘朵啦高畫質玩美台','Pandora潘朵啦高畫質粉紅台','松視1台','松視2台','松視3台','彩虹E台','彩虹Movie台','K頻道','HOT頻道','HAPPY頻道','玩家頻道','驚豔成人電影台','香蕉台','樂活頻道','JStar極限台電影頻道']; // 替換為你要刪除的頻道 ID

// 讀取 XML 文件
$xmlFile = 'epgziyong.xml';


if (!file_exists($xmlFile)) {
    die("文件不存在：$xmlFile");
}

// 載入 XML
$xml = simplexml_load_file($xmlFile);
if (!$xml) {
    die("無法載入 XML 文件。");
}

// 使用 DOM 操作以便於刪除節點
$dom = new DOMDocument();
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->load($xmlFile);

// 刪除 <channel> 節點
$xpath = new DOMXPath($dom);
foreach ($channelsToRemove as $channelId) {
    // 刪除 <channel> 節點
    foreach ($xpath->query("//channel[@id='$channelId']") as $node) {
        $node->parentNode->removeChild($node);
    }

    // 刪除 <programme> 節點
    foreach ($xpath->query("//programme[@channel='$channelId']") as $node) {
        $node->parentNode->removeChild($node);
    }
}

// 保存為新文件
$newFile = 'epgnew.xml';
if ($dom->save($newFile)) {
    echo "處理完成，新文件已保存為：$newFile";
} else {
    echo "保存文件時出錯。";
}
$xmlContent = file_get_contents('epgnew.xml');
$gz = gzopen('epgnew.xml.gz', 'w9');  // 'w9' 表示最高压缩级别（可选，默认为 6）
if ($gz !== false) {
    gzwrite($gz, $xmlContent);
    gzclose($gz);
    echo "XML文件合并完成，已保存为 epgnew.xml 和 epgnee.xml.gz";
} else {
    echo "创建 gz 文件失败";
}
?>
