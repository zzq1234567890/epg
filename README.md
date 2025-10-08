注意:

1.自娛自樂！自用，請勿用要商業用途！一般不進行維護和技術支持！

推薦播放軟體 Tivimate, ott navigator,極致播放器！

2.不提供節目源地址！請支持訂閱正版節目源地址！

3.Youtube僅僅提供地址，不帶任何解析。建議用影視(影視直播對xml支持較差)。

4.墻內建議選epgnew.xml(去掉幾個少兒不宜及對岸禁止的幾個電視台預告)，墻外無所謂了，epgziyong.xml還是epgnew.xml隨便了

5.增加廣西電視台節目預告（兩天）

6.增加河北電信廣東電信的地方台節目

7.增加HOY新增頻道EPG

8.增加tvb anywhere usa 6個頻道EPG

9.增加
簡體版本的epg，適合中國大陸，新加坡，马来西亚，swepg.xml.gz，(網址: https://github.com/zzq1234567890/epg/raw/refs/heads/main/swepg.xml.gz   );  
繁體版本epg,適合香港澳門台灣，twepg.xml.gz. （網址:
https://github.com/zzq1234567890/epg/raw/refs/heads/main/twepg.xml.gz );

10.強烈推薦xm.gz,而不是xml格式，檔案小很多
Important Notices
 具体电视台名称参见 
 繁體[https://raw.githubusercontent.com/zzq1234567890/epg/refs/heads/main/%E7%B9%81%E9%AB%94%E9%9B%BB%E8%A6%96%E5%8F%B0%E7%9B%AE%E9%8C%84.txt]

简体电视台目录[
https://raw.githubusercontent.com/zzq1234567890/epg/refs/heads/main/%E7%AE%80%E4%BD%93%E7%94%B5%E8%A7%86%E5%8F%B0%E6%95%B4%E7%90%86%E6%9C%AA%E5%88%86%E7%B1%BB.txt]

主要是填写 tvg-id="电视台名称" tvg-name="电视台名称"
 繁體，適合港澳台


#EXTM3U url-tvg="https://raw.githubusercontent.com/zzq1234567890/epg/refs/heads/main/twepg.xml.gz" catchup="append" catchup-source="?playseek=${(b)yyyyMMddHHmmss}-${(e)yyyyMMddHHmmss}"

#EXTINF:-1 tvg-id="電視台名稱" tvg-name="電視台名稱" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="你的分组",电视台名称



  简体，适合东南亚及中国大陆用
具体格式

#EXTM3U url-tvg="https://raw.githubusercontent.com/zzq1234567890/epg/refs/heads/main/swepg.xml.gz" catchup="append" catchup-source="?playseek=${(b)yyyyMMddHHmmss}-${(e)yyyyMMddHHmmss}"

#EXTINF:-1 tvg-id="电视台名称" tvg-name="电视台名称" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="你的分组",电视台名称

节目源地址

样例例如 #EXTINF:-1 tvg-id="cctv1" tvg-name="cctv1" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="黑龙江移动",cctv-1

http://ottrrs.hl.chinamobile.com/PLTV/88888888/224/3221226016/index.m3u8
1. For Personal Use Only – Commercial Use Strictly Prohibited
This service is exclusively intended for personal entertainment and non-commercial use. Maintenance and technical support are not provided as a general policy.
Recommended Playback Applications: Tivimate, OTT Navigator, Jizhi Player.
 
2. No Program Source URLs Distributed
We do not provide or share program source URLs. Please support legitimate content by subscribing to official program source URLs.
 
3. YouTube URLs Provided “As-Is” (No Parsing Functionality)
YouTube URLs are shared in their raw format without any parsing, decoding, or technical processing. For optimal compatibility, we recommend using Yingshi (Note: Yingshi Live has limited support for XML formats).

4.Adding the epg of some chinese local  channels from guangxi tv  website.
   
5.Adding the epg of local tv channels of hebei&guangdong province .

6.Adding the epg of some HOY new channels

7.Adding theepg of six channels from TVB anywhere USA .

8.Adding the epg for simplied chinese ,swepg.xml.gz, used in mainland of china;
the epg of  tradional chinese epg,tw.epg.gz, used in hk ,marcol,taiwan  area .
