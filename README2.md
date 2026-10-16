# 代碼全部遷移到 GitHub 上，目前換新測試中，請知悉！

# EPG 服務說明
## 一、概述
本電子節目指南（EPG）服務僅面向個人娛樂使用，嚴禁用於商業用途。服務覆蓋大陸、香港、澳門、台灣、韓國、新加坡、馬來西亞、印度尼西亞等地區的電視台節目預告，針對性推出簡體中文與繁體中文兩個版本，適配不同區域用戶需求。

## 二、使用規範
### （1）使用限制
1. 僅限個人非商業用途，任何商業性質的開發、傳播或獲利行為均嚴格禁止；
2. 本服務不承諾提供常規維護及技術支援服務。

### （2）內容與資源說明
1. 不提供節目源地址，用戶需通過訂閱官方正版渠道獲取合法節目資源；
2. 因不可抗力因素，已取消 YouTube 相關鏈接；
3. 推薦播放應用：OK影視 v3.6.6、Ok影視 Pro v3.97、Tivimate 4.6以上、OTT Navigator v1.7.3.4、極致播放器。

## 三、EPG 服務規格
### （1）推薦格式
優先選擇 **xml.gz 格式**，相比 XML 格式，具有文件體積更小、加載速度更快的優勢。

### （2）區域適配版本
| EPG 版本 | 文件名 | 適用區域 | 下載鏈接 |
| ---- | ---- | ---- | ---- |
| 簡體中文 | swepg.xml.gz | 大陸、新加坡、馬來西亞 | https://github.com/zzq1234567890/epg/raw/refs/heads/main/swepg.xml.gz |
| 繁體中文 | twepg.xml.gz | 香港、澳門、台灣 | https://github.com/zzq1234567890/epg/raw/refs/heads/main/twepg.xml.gz |

### （3）大陸用戶特別說明
1. 建議選用 **epgnew.xml.gz** 文件，該版本已剔除少量未滿十八歲不宜及區域限制類電視台的節目預告；
2. 境外用戶可根據需求自由選用。

## 四、覆蓋頻道範圍
### （1）大陸地區
央視頻、1905電影網、電視搜部分地方頻道，浙江電視台、江蘇電視台、河北電視台、北京電視台、上海電視台、重慶電視台、福建電視台、廣東電視台、廣西電視台、江西電視台、四川電視台、內蒙古電視台、新疆電視台等。

### （2）香港地區
MyTV Super、TVB Anywhere 東南亞、TVB Anywhere USA、Now 寬頻、HOY 電視台、香港電台等。

### （3）澳門地區
澳門電視台、澳門有線電視台。

### （4）台灣地區
中華電信 MOD、中嘉 BB 寬頻、TBC 有線、Hami、Ofiii、愛爾達 TV、4GTV、Catchplay、龍華電視台、MyVideo 等（部分頻道視路由器恢復情況持續更新）。

### （5）其他國家及地區
1. 韓國：KBS、SBS、MBC、EBC；
2. 新加坡：MeWatch、Singtel；
3. 馬來西亞：Astro、Unifi；
4. 印度尼西亞：Vision+；
5. 阿聯酋：MBC 電視台系列。

## 五、近期更新內容
1. 新增廣西電視台官網部分地方頻道的 EPG 數據；
2. 新增河北省、廣東省地方電視台的 EPG 數據；
3. 新增 HOY 電視台多個全新頻道的 EPG 數據；
4. 新增 TVB Anywhere USA 旗下 6 個頻道的 EPG 數據；
5. 增加天映頻道、天映經典、KIX 頻道多個國家節目 EPG；
6. 增加 4GTV 中的 FAST-live 直播 EPG；
7. 增加新加坡 Singtel EPG；
8. 新增台灣 Catchplay 直播點播 EPG；
9. 上線簡體中文版本 EPG（swepg.xml.gz）及繁體中文版本 EPG（twepg.xml.gz），優化區域適配性。

## 六、參考資源
1. 繁體中文電視台目錄：https://raw.githubusercontent.com/zzq1234567890/epg/refs/heads/main/繁體電視台目錄.txt
2. 簡體中文電視台目錄：https://raw.githubusercontent.com/zzq1234567890/epg/refs/heads/main/简体电视台目录.txt

## 七、M3U 格式規範
### 1. 繁體中文版本（適用於香港、澳門、台灣地區）
```m3u
#EXTM3U url-tvg="[https://github.com/zzq1234567890/epg/raw/refs/heads/main/twepg.xml.gz](https://github.com/zzq1234567890/epg/raw/refs/heads/main/twepg.xml.gz)" catchup="append" catchup-source="?playseek=${(b)yyyyMMddHHmmss}-${(e)yyyyMMddHHmmss}"
#EXTINF:-1 tvg-id="電視台名稱1" tvg-name="電視台名稱1" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="你的分組",電視台名稱1
節目播放地址1
#EXTINF:-1 tvg-id="電視台名稱2" tvg-name="電視台名稱2" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="你的分組",電視台名稱2
節目播放地址2

### 2. 簡體中文版本（適用於大陸及東南亞地區）
#EXTM3U url-tvg="[https://github.com/zzq1234567890/epg/raw/refs/heads/main/swepg.xml.gz](https://github.com/zzq1234567890/epg/raw/refs/heads/main/swepg.xml.gz)" catchup="append" catchup-source="?playseek=${(b)yyyyMMddHHmmss}-${(e)yyyyMMddHHmmss}"
#EXTINF:-1 tvg-id="電視台名稱1" tvg-name="電視台名稱1" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="你的分組",電視台名稱1
節目源地址1
#EXTINF:-1 tvg-id="電視台名稱2" tvg-name="電視台名稱2" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="你的分組",電視台名稱2
節目源地址2


