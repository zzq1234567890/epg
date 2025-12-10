
# 中文繁體版：EPG服務說明
## 一、概述
本電子節目指南（EPG）服務僅面向個人娛樂使用，嚴禁用於商業用途。服務覆蓋大陸、香港、澳門、台灣、韓國、新加坡、馬來西亞、印度尼西亞等地區的電視台節目預告，針對性推出簡體中文與繁體中文兩個版本，适配不同區域用戶需求。

## 二、使用規範
### （一）使用限制
1. 僅限個人非商業用途，任何商業性質的開發、傳播或獲利行為均嚴格禁止；
2. 本服務不承諾提供常規維護及技術支援服務。

### （二）內容與資源說明
1. 不提供節目源地址，用戶需通過訂閱官方正版渠道獲取合法節目資源；
2. YouTube相關鏈接僅提供原始地址，不包含任何解析、解碼服務；
3. 推薦播放應用：Ok影視、Tivimate、OTT Navigator、極致播放器。

## 三、EPG服務規格
### （一）推薦格式
優先選擇**xml.gz格式**，相比XML格式，具有文件體積更小、加載速度更快的優勢。



### （二）區域适配版本
| EPG版本 | 文件名 | 適用區域 | 下載鏈接 |
| ---- | ---- | ---- | ---- |
| 簡體中文 | swepg.xml.gz | 大陸、新加坡、馬來西亞 | https://github.com/zzq1234567890/epg/raw/refs/heads/main/swepg.xml.gz |
| 繁體中文 | twepg.xml.gz | 香港、澳門、台灣 | https://github.com/zzq1234567890/epg/raw/refs/heads/main/twepg.xml.gz |

### （三）大陸用戶特別說明
1. 建議選用**epgnew.xml.gz**文件，該版本已剔除少量未滿十八歲不宜及區域限制類電視台的節目預告；
2. 境外用戶可根據需求自由選.

##( 四)、覆蓋頻道範圍
### （1）大陸地區
央視頻、1905電影網、浙江衛視、江蘇衛視、河北衛視、北京衛視、上海衛視、重慶衛視、福建衛視、廣東衛視、廣西衛視、江西衛視、四川衛視、內蒙古衛視、新疆衛視等。

### （2）香港地區
MyTV Super、Anywhere、TVB Anywhere USA、Now寬頻、HOY電視台、香港電台等。

### （3）澳門地區
澳門電視台、澳門有線電視台。

### （4）台灣地區
MOD、BB寬頻、TBC有線、4GTV、Hami、Ofiii等。

### （5）其他國家及地區
1. 韓國：KBS、SBS、MBC、EBC；
2. 新加坡：MeWatch；
3. 馬來西亞：Astro；
4. 印度尼西亞：EPG MNC Vision+。

## 五、近期更新內容
1. 新增廣西電視台官網部分地方頻道的EPG數據；
2. 新增河北省、廣東省地方電視台的EPG數據；
3. 新增HOY電視台多個全新頻道的EPG數據；
4. 新增TVB Anywhere USA旗下6個頻道的EPG數據；
5. 上線簡體中文版本EPG（swepg.xml.gz）及繁體中文版本EPG（twepg.xml.gz），優化區域适配性。

## 六、參考資源
1. 繁體中文電視台目錄：https://raw.githubusercontent.com/zzq1234567890/epg/refs/heads/main/繁體電視台目錄.txt  ；
2. 簡體中文電視台目錄：https://raw.githubusercontent.com/zzq1234567890/epg/refs/heads/main/简体电视台目录.txt 。

## 七、M3U格式規範
### （一）繁體中文版本（適用於香港、澳門、台灣地區）
```
#EXTM3U url-tvg="https://github.com/zzq1234567890/epg/raw/refs/heads/main/swepg.xml.gz" catchup="append" catchup-source="?playseek=${(b)yyyyMMddHHmmss}-${(e)yyyyMMddHHmmss}"
#EXTINF:-1 tvg-id="電視台名稱1" tvg-name="電視台名稱1" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="你的分組",電視台名稱1
節目播放地址1
#EXTINF:-1 tvg-id="電視台名稱2" tvg-name="電視台名稱2" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="你的分組",電視台名稱2
節目播放地址2
```

### （二）簡體中文版本（適用於大陸及東南亞地區）
```
#EXTM3U url-tvg="https://github.com/zzq1234567890/epg/raw/refs/heads/main/swepg.xml.gz" catchup="append" catchup-source="?playseek=${(b)yyyyMMddHHmmss}-${(e)yyyyMMddHHmmss}"
#EXTINF:-1 tvg-id="電視台名稱1" tvg-name="電視台名稱1" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="你的分組",電視台名稱1
節目源地址1
#EXTINF:-1 tvg-id="電視台名稱2" tvg-name="電視台名稱2" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="你的分組",電視台名稱2
節目源地址2
```

---

# English Version: EPG Service Instructions
## 1. Service Overview
This EPG (Electronic Program Guide) service is provided exclusively for personal entertainment and non-commercial use. It covers program previews for TV channels across Mainland China, Hong Kong, Macau, Taiwan, South Korea, Singapore, Malaysia, and Indonesia, with dedicated Simplified and Traditional Chinese versions tailored to different regional needs.

## 2. Key Terms & Conditions
### 2.1 Usage Restrictions
1. This service is for **personal non-commercial use only**; commercial exploitation is strictly prohibited.
2. Routine maintenance and technical support are not guaranteed.

### 2.2 Content & Resource Notes
1. Program source URLs are not provided. Users must subscribe to official and legitimate program source services.
2. YouTube links are shared in their original form without any parsing or decoding. 
3. Recommended playback applications: Ok Video, Tivimate, OTT Navigator, Jizhi Player.

## 3. EPG Specifications
### 3.1 Recommended Format
**xml.gz format** is preferred over XML, as it offers smaller file size and faster loading speed.

### 3.2 File Structure
1. twepg.xml: Traditional Chinese version EPG, suitable for Hong Kong, Macau, and Taiwan.
2. swepg.xml: Simplified Chinese version EPG, suitable for Mainland China users.

### 3.3 Regional Adaptations
| EPG Version | File Name | Applicable Regions | Download URL |
|-------------|-----------|--------------------|--------------|
| Simplified Chinese | swepg.xml.gz | Mainland China, Singapore, Malaysia | https://github.com/zzq1234567890/epg/raw/refs/heads/main/swepg.xml.gz |
| Traditional Chinese | twepg.xml.gz | Hong Kong, Macau, Taiwan | https://github.com/zzq1234567890/epg/raw/refs/heads/main/twepg.xml.gz |

### 3.4 Special Notes for Mainland China Users
1. It is recommended to use **epgnew.xml** (excludes program previews for a few adult-oriented and regionally restricted TV channels).
2. Users outside Mainland China may choose either epgziyong.xml or epgnew.xml.

## 4. Covered Channel Scope
### 4.1 Mainland China
CCTV Video, 1905 Movie Network, Zhejiang TV, Jiangsu TV, Hebei TV, Beijing TV, Shanghai TV, Chongqing TV, Fujian TV, Guangdong TV, Guangxi TV, Jiangxi TV, Sichuan TV, Inner Mongolia TV, Xinjiang TV, etc.

### 4.2 Hong Kong
MyTV Super, Anywhere, TVB Anywhere USA, Now TV, HOY TV, Radio Television Hong Kong (RTHK), etc.

### 4.3 Macau
TDM - Teledifusão de Macau, Macau Cable TV.

### 4.4 Taiwan
MOD, BB Broadband, TBC Cable, 4GTV, Hami, Ofiii, etc.

### 4.5 Other Countries & Regions
1. South Korea: KBS, SBS, MBC, EBC.
2. Singapore: MeWatch.
3. Malaysia: Astro.
4. Indonesia: EPG MNC Vision.

## 5. Recent Updates
1. Added EPG data for selected local channels from Guangxi TV's official website.
2. Added EPG data for local TV channels in Hebei and Guangdong provinces.
3. Added EPG data for several new HOY TV channels.
4. Added EPG data for six channels from TVB Anywhere USA.
5. Launched Simplified Chinese EPG (swepg.xml.gz) and Traditional Chinese EPG (twepg.xml.gz) for improved regional adaptation.

## 6. Reference Resources
1. Traditional Chinese TV Channel List: https://raw.githubusercontent.com/zzq12345/epgtest/refs/heads/main/繁體電視台目錄.txt
2. Simplified Chinese TV Channel List: https://raw.githubusercontent.com/zzq12345/epgtest/refs/heads/main/简体电视台目录.txt

## 7. M3U Format Specifications
### 7.1 Traditional Chinese (For Hong Kong, Macau, Taiwan)
```
#EXTM3U url-tvg="https://github.com/zzq1234567890/epg/raw/refs/heads/main/swepg.xml.gz" catchup="append" catchup-source="?playseek=${(b)yyyyMMddHHmmss}-${(e)yyyyMMddHHmmss}"
#EXTINF:-1 tvg-id="電視台名稱1" tvg-name="電視台名稱1" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="你的分組",電視台名稱1
節目播放地址1
#EXTINF:-1 tvg-id="電視台名稱2" tvg-name="電視台名稱2" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="你的分組",電視台名稱2
節目播放地址2
```

### 7.2 Simplified Chinese (For Mainland China, Southeast Asia)
```
#EXTM3U url-tvg="https://github.com/zzq1234567890/epg/raw/refs/heads/main/swepg.xml.gz" catchup="append" catchup-source="?playseek=${(b)yyyyMMddHHmmss}-${(e)yyyyMMddHHmmss}"
#EXTINF:-1 tvg-id="电视台名称1" tvg-name="电视台名称1" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="你的分组",电视台名称1
节目源地址1
#EXTINF:-1 tvg-id="电视台名称2" tvg-name="电视台名称2" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="你的分组",电视台名称2
节目源地址2
```


