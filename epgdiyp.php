<?php
/**
 * EPG XML 转 DIYP JSON 格式工具
 * 按照频道和日期分组节目数据
 */

// 输入XML文件列表
$xmlFiles = [
    './epgziyong.xml'
    // './epgkai.xml'
];

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

    // 設定時區（依需求調整）
    date_default_timezone_set('Asia/Taipei');

    // 建立空陣列儲存每一個頻道與其節目資料
    $channels = [];

    // 遍歷每個 <programme> 節目節點
    foreach ($xml->programme as $prog) {
        // 取得頻道 ID、節目標題、開始時間與結束時間
        $channelId = (string)$prog['channel'];
        $title = trim((string)$prog->title);
        $startStr = (string)$prog['start'];
        $stopStr = (string)$prog['stop'];
        
        // 用 DateTime::createFromFormat 處理時間字串，格式為 YmdHis 後面接空白與時區，例如 "20250413023000 +0800"
        $startTimeObj = DateTime::createFromFormat('YmdHis O', $startStr);
        $stopTimeObj = DateTime::createFromFormat('YmdHis O', $stopStr);
        
        if (!$startTimeObj || !$stopTimeObj) {
            continue; // 若時間格式有誤則略過此節目
        }
        
        // 格式化節目開始與結束時間為 HH:MM 格式
        $startFormatted = $startTimeObj->format("H:i");
        $endFormatted = $stopTimeObj->format("H:i");
        
        // 根據節目結束時間取得分組依據，格式定義為 "Y-m-d-H"（例如 "2025-04-13-02"）
        $groupDate = $stopTimeObj->format("Y-m-d-H");
        
        // 若該頻道尚未建立資料則先建立，這裡 channel_name 以頻道 ID 填入（若 XML 有 display-name 可進一步處理）
        if (!isset($channels[$channelId])) {
            $channels[$channelId] = [
                "channel_name" => $channelId
            ];
        }
        
        // 建立該日期分組下的節目陣列，如尚未存在則先建立
        if (!isset($channels[$channelId][$groupDate])) {
            $channels[$channelId][$groupDate] = [
                "epg_data" => []
            ];
        }
        
        // 將該節目資料加入對應的日期分組中
        $channels[$channelId][$groupDate]["epg_data"][] = [
            "title" => $title,
            "start" => $startFormatted,
            "end"   => $endFormatted
        ];
    }

    // 最後將結果轉換為 JSON 格式，這裡以 array_values() 轉為數字索引陣列，方便閱讀與後續處理
    $jsonOutput = json_encode(array_values($channels), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    // 將 JSON 輸出到 epgdiyp.json 檔案
    file_put_contents("epgdiyp.json", $jsonOutput);

    echo "epgdiyp.json 檔案已產生。";
} // ← 加上這個結束大括號以正確關閉 foreach ($xmlFiles as $file)
?>
