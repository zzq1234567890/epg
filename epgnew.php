<?php
// 要刪除的頻道 ID 列表
$channelsToRemove = ['三立新聞台', '新唐人亞太台', '民視新聞台']; // 替換為你要刪除的頻道 ID

// 讀取 XML 文件
$xmlFile = ['./epgziyong.xml'];


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
?>
