本EPG說明及詳細教程:

注意:

1.自娛自樂！自用，請勿用作商業用途！一般不進行維護和技術支持！

推薦播放軟體 ok影視， Tivimate, ott navigator,極致播放器！epg選擇xml.gz格式。速度快，體積小。

2.不提供節目源地址！請支持訂閱正版節目源地址！

3.Youtube僅僅提供地址，不帶任何解析。建議用影視(影視直播對xml支持較差)。

4.墻內建議選epgnew.xml(去掉幾個少兒不宜及對岸禁止的幾個電視台預告)，墻外無所謂了，epgziyong.xml還是epgnew.xml隨便了

5.本節目預告包括

中國大陸

央視頻
1905
浙江
江蘇
河北
北京
上海
重慶
福建
廣東
廣西
江蘇
江西
四川
內蒙古
新疆



香港  
mytvsuper 
anywhere
tvb anywhere usa 
now寬頻
hoy電視台
香港電台

澳門
澳門電視台
澳門有線

台灣
mod 
bb寬頻
tbc有線
4gtv
hami 
ofiii

韓國
kbs
sbs
mbc
ebc

新加坡
mewatch

馬來西亞
astro
印尼
epgmncvision
 
6.增加
簡體版本的epg，適合中國大陸，新加坡，马来西亚，swepg.xml.gz，(網址: https://github.com/zzq1234567890/epg/raw/refs/heads/main/swepg.xml.gz);  

繁體版本epg,適合香港澳門台灣，twepg.xml.gz. （網址:
https://github.com/zzq1234567890/epg/raw/refs/heads/main/twepg.xml.gz);

7.強烈推薦xm.gz,而不是xml格式，檔案小很多

 具体电视台名称参见 
 
 繁體[https://raw.githubusercontent.com/zzq1234567890/epg/refs/heads/main/繁體電視台目錄.txt];

简体电视台目录[
https://raw.githubusercontent.com/zzq1234567890/epg/refs/heads/main/简体电视台目录.txt]

教程

m3u格式

 繁體，適合港澳台
 
#EXTM3U url-tvg="https://github.com/zzq1234567890/epg/raw/refs/heads/main/swepg.xml.gz" catchup="append" catchup-source="?playseek=${(b)yyyyMMddHHmmss}-${(e)yyyyMMddHHmmss}"

#EXTINF:-1 tvg-id="電視台名稱1" tvg-name="電視台名稱1" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="你的分组",電視台名稱1

節目播放地址1

#EXTINF:-1 tvg-id="電視台名稱2" tvg-name="電視台名稱2" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="你的分组",電視台名稱2

節目播放地址2


 m3u格式
 
  简体，适合东南亚及中国大陆用
具体格式

#EXTM3U url-tvg="https://github.com/zzq1234567890/epg/raw/refs/heads/main/swepg.xml.gz" catchup="append" catchup-source="?playseek=${(b)yyyyMMddHHmmss}-${(e)yyyyMMddHHmmss}"

#EXTINF:-1 tvg-id="电视台名称1" tvg-name="电视台名称1" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="你的分组",电视台名称1

节目源地址1

#EXTINF:-1 tvg-id="电视台名称2" tvg-name="电视台名称2" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="你的分组",电视台名称2

节目源地址2
# EPG Service Instructions (Revised English Version)
## 1. Service Overview
This EPG (Electronic Program Guide) service is provided exclusively for personal entertainment and non-commercial use. It covers program previews for TV channels across Mainland China, Hong Kong, Macau, Taiwan, South Korea, Singapore, Malaysia, and Indonesia, with dedicated Simplified and Traditional Chinese versions tailored to different regional needs.

## 2. Key Terms & Conditions
### 2.1 Usage Restrictions
- This service is for **personal non-commercial use only**; commercial exploitation is strictly prohibited.
- Routine maintenance and technical support are not guaranteed.

### 2.2 Content & Resource Notes
- Program source URLs are not provided. Users must subscribe to official and legitimate program source services.
- YouTube links are shared in their original form without any parsing or decoding. Yingshi is recommended for use, though Yingshi Live has poor compatibility with XML formats.
- Recommended playback applications: Ok Video, Tivimate, OTT Navigator, Jizhi Player.

## 3. EPG Specifications
### 3.1 Recommended Format
- **xml.gz format** is preferred over XML, as it offers smaller file size and faster loading speed.

### 3.2 Regional Adaptations
| EPG Version       | File Name       | Applicable Regions          | Download URL                                                                 |
|-------------------|-----------------|-----------------------------|-----------------------------------------------------------------------------|
| Simplified Chinese | swepg.xml.gz    | Mainland China, Singapore, Malaysia | https://github.com/zzq1234567890/epg/raw/refs/heads/main/swepg.xml.gz       |
| Traditional Chinese | twepg.xml.gz   | Hong Kong, Macau, Taiwan    | https://github.com/zzq1234567890/epg/raw/refs/heads/main/twepg.xml.gz       |

### 3.3 Special Notes for Mainland China Users
- It is recommended to use **epgnew.xml** (excludes program previews for a few adult-oriented and regionally restricted TV channels).
- Users outside Mainland China may choose either epgziyong.xml or epgnew.xml.

## 4. Recent Updates
1. Added EPG data for selected local channels from Guangxi TV's official website.
2. Added EPG data for local TV channels in Hebei and Guangdong provinces.
3. Added EPG data for several new HOY TV channels.
4. Added EPG data for six channels from TVB Anywhere USA.
5. Launched Simplified Chinese EPG (swepg.xml.gz) and Traditional Chinese EPG (twepg.xml.gz) for regional adaptation.

## 5. Reference Resources
- Traditional Chinese TV Channel List: https://raw.githubusercontent.com/zzq1234567890/epg/refs/heads/main/繁體電視台目錄.txt
- Simplified Chinese TV Channel List: https://raw.githubusercontent.com/zzq1234567890/epg/refs/heads/main/简体电视台目录.txt

## 6. M3U Format Specifications
### 6.1 Traditional Chinese (For Hong Kong, Macau, Taiwan)
```
#EXTM3U url-tvg="https://github.com/zzq1234567890/epg/raw/refs/heads/main/swepg.xml.gz" catchup="append" catchup-source="?playseek=${(b)yyyyMMddHHmmss}-${(e)yyyyMMddHHmmss}"
#EXTINF:-1 tvg-id="電視台名稱1" tvg-name="電視台名稱1" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="你的分组",電視台名稱1
節目播放地址1
#EXTINF:-1 tvg-id="電視台名稱2" tvg-name="電視台名稱2" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="你的分组",電視台名稱2
節目播放地址2
```

### 6.2 Simplified Chinese (For Mainland China, Southeast Asia)
```
#EXTM3U url-tvg="https://github.com/zzq1234567890/epg/raw/refs/heads/main/swepg.xml.gz" catchup="append" catchup-source="?playseek=${(b)yyyyMMddHHmmss}-${(e)yyyyMMddHHmmss}"
#EXTINF:-1 tvg-id="电视台名称1" tvg-name="电视台名称1" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="你的分组",电视台名称1
节目源地址1
#EXTINF:-1 tvg-id="电视台名称2" tvg-name="电视台名称2" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="你的分组",电视台名称2
节目源地址2





