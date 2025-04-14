<?php
$url='https://github.com/zzq12345/tvepg/blob/main/epgshanghai.xml';
$ch1 = curl_init ();
curl_setopt ( $ch1, CURLOPT_URL, $url );
//curl_setopt ( $ch1, CURLOPT_HEADER, $hea );
curl_setopt($ch1,CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch1,CURLOPT_SSL_VERIFYHOST,false);
curl_setopt ( $ch1, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt($ch1,CURLOPT_ENCODING,'Vary: Accept-Encoding');
  $xml = curl_exec($ch1);
     curl_close($ch1);
$dom = new DOMDocument();
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($xml);

// 创建频道映射关系 [旧ID => 新名称]
$channelMap = [];
foreach ($dom->getElementsByTagName('channel') as $channel) {
    $oldId = $channel->getAttribute('id');
    $newName = $channel->getElementsByTagName('display-name')->item(0)->nodeValue;
    $channelMap[$oldId] = $newName;
}

// 替换 channel 的 id
foreach ($dom->getElementsByTagName('channel') as $channel) {
    $oldId = $channel->getAttribute('id');
    if (!empty($channelMap[$oldId])) {
        $channel->setAttribute('id', $channelMap[$oldId]);
    }
}

// 替换 programme 的 channel 属性
foreach ($dom->getElementsByTagName('programme') as $programme) {
    $oldChannel = $programme->getAttribute('channel');
    if (!empty($channelMap[$oldChannel])) {
        $programme->setAttribute('channel', $channelMap[$oldChannel]);
    }
}

echo $dom->saveXML();
?>
