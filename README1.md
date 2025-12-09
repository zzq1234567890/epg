# EPG服务说明（中文商务版）
## 一、服务概述
本电子节目指南（EPG）服务仅面向个人娱乐使用，严禁用于商业用途。服务覆盖中国大陆、中国香港、中国澳门、中国台湾、韩国、新加坡、马来西亚、印度尼西亚等地区的电视台节目预告，针对性推出简体中文与繁体中文两个版本，适配不同区域用户需求。

## 二、核心条款与使用规范
### （一）使用限制
1. 仅限个人非商业用途，任何商业性质的开发、传播或盈利行为均严格禁止；
2. 本服务不承诺提供常规维护及技术支持服务。

### （二）内容与资源说明
1. 不提供节目源地址，用户需通过订阅官方正版渠道获取合法节目资源；
2. YouTube相关链接仅提供原始地址，不包含任何解析、解码服务，建议使用“影视”应用播放（注：影视直播对XML格式兼容性较差）；
3. 推荐播放应用：Ok影视、Tivimate、OTT Navigator、极致播放器。

## 三、EPG服务规格
### （一）推荐格式
优先选择**xml.gz格式**，相比XML格式，具有文件体积更小、加载速度更快的优势（原文“xm.gz”为笔误，已修正）。

### （二）区域适配版本
| EPG版本 | 文件名 | 适用区域 | 下载链接 |
| ---- | ---- | ---- | ---- |
| 简体中文 | swepg.xml.gz | 中国大陆、新加坡、马来西亚 | https://github.com/zzq1234567890/epg/raw/refs/heads/main/swepg.xml.gz |
| 繁体中文 | twepg.xml.gz | 中国香港、中国澳门、中国台湾 | https://github.com/zzq1234567890/epg/raw/refs/heads/main/twepg.xml.gz |

### （三）中国大陆用户特别说明
1. 建议选用**epgnew.xml**文件，该版本已剔除少量少儿不宜及区域限制类电视台的节目预告；
2. 境外用户可根据需求自由选择epgziyong.xml或epgnew.xml。

## 四、覆盖频道范围
### （一）中国大陆地区
央视频、1905电影网、浙江卫视、江苏卫视、河北卫视、北京卫视、上海卫视、重庆卫视、福建卫视、广东卫视、广西卫视、江西卫视、四川卫视、内蒙古卫视、新疆卫视等。

### （二）中国香港地区
MyTV Super、Anywhere、TVB Anywhere USA、Now宽频、HOY电视台、香港电台等。

### （三）中国澳门地区
澳门电视台、澳门有线电视台。

### （四）中国台湾地区
MOD、BB宽频、TBC有线、4GTV、Hami、Ofiii等。

### （五）其他国家及地区
1. 韩国：KBS、SBS、MBC、EBC；
2. 新加坡：MeWatch；
3. 马来西亚：Astro；
4. 印度尼西亚：EPG MNC Vision。

## 五、近期更新内容
1. 新增广西电视台官网部分地方频道的EPG数据；
2. 新增河北省、广东省地方电视台的EPG数据；
3. 新增HOY电视台多个全新频道的EPG数据；
4. 新增TVB Anywhere USA旗下6个频道的EPG数据；
5. 上线简体中文版本EPG（swepg.xml.gz）及繁体中文版本EPG（twepg.xml.gz），优化区域适配性。

## 六、参考资源
1. 繁体中文电视台目录：https://raw.githubusercontent.com/zzq1234567890/epg/refs/heads/main/繁體電視台目錄.txt；
2. 简体中文电视台目录：https://raw.githubusercontent.com/zzq1234567890/epg/refs/heads/main/简体电视台目录.txt。

## 七、M3U格式规范
### （一）繁体中文版本（适用于中国香港、中国澳门、中国台湾地区）
```
#EXTM3U url-tvg="https://github.com/zzq1234567890/epg/raw/refs/heads/main/swepg.xml.gz" catchup="append" catchup-source="?playseek=${(b)yyyyMMddHHmmss}-${(e)yyyyMMddHHmmss}"
#EXTINF:-1 tvg-id="電視台名稱1" tvg-name="電視台名稱1" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="你的分组",電視台名稱1
節目播放地址1
#EXTINF:-1 tvg-id="電視台名稱2" tvg-name="電視台名稱2" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="你的分组",電視台名稱2
節目播放地址2
```

### （二）简体中文版本（适用于中国大陆及东南亚地区）
```
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





