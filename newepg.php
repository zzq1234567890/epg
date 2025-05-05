<?php
// 指定要移除的频道 ID 数组（根据实际 XML 结构修改）
$removeChannelIds = ['channel1', 'channel3', 'channel5'];

// 加载 XML
$xml = simplexml_load_file('epgziyong.xml');
if (!$xml) {
    die("无法加载 EPG XML 文件");
}

// 遍历所有频道并移除指定项
foreach ($xml->channel as $channel) {
    $channelId = (string)$channel['id'];
    if (in_array($channelId, $removeChannelIds)) {
        $channel->delete(); // 需 PHP 5.3+
    }
}

// 保存修改后的 XML（可选：格式化输出）
$dom = new DOMDocument('1.0', 'UTF-8');
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($xml->asXML());
$dom->save('clean_epg.xml');
echo "已移除指定频道，结果保存为 clean_epg.xml";
?>
