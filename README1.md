说明，本节目预告是两天预告，个别如4gtv是三天！

注意提醒，请严格按照电视台名称对应,不要任意改动！否则不能正常获取节目预告！

由于台湾电视部分频道在有线电视中，mod和网络会播不同节目，请根据节目源来源，自己对应名称！

初步规划中文电视台名称一样的，
Mod和hami一样，在mod电视台后面加mod(仅仅限于中文台)，
龙华电视台几个增加ott节目预告，也就是litv那些节目预告！
有线电视后面加有线
国外台节目是一样的


 美亞電影台          美亞電影台台灣
 
 龍華偶像MOD         龍華偶像台ott
 
 天映經典台香港(mytvsuper)       天映經典台馬來西亞
 天映經典台菲律賓   天映經典台印尼
 
   天映電視台馬來西亞   天映電視台印尼
   天映電視台新加坡

 公視mod          公視

 中視mod          中視

节目预告采用xml格式，适合m3u文件的节目预告，适合绝大多数播放器。推荐ott navigator  Tivimate，极致播放器(极致播放器缺陷电视台名称不能带()  )。 目前包含了1905电影网, 央视频官网包括央视，cgtn几个外语台，中数传媒收费节目，卫视，广东官网，福建电视台官网，江苏电视台官网，浙江电视台官网，陕西电视台，山东电视台官网，河北电视台官网， 新疆電視台，澳门电视台官网，莲花卫视，台湾BB宽频官网,台湾中华电信官网,台湾4gtv官网，hami视频官网部分，香港now宽频官网，香港mytvsuper官网,香港anywhere官网，香港电台官网，香港hoy电视台，韩国KBS电视台官网，韩国MBC电视台官网，韩国SBS电视台官网，韩国ebs电视台官网， 新加坡mewatch官网 
删除了马来西亚astro unifi 印尼的k+

后来也综合了老张epg，增加内地例如北京，四川，辽宁等地方台，以及部分收费台节目预告！

增加了上海地方台，以及老张的epg.
目前每4小时自动更新数据,同时更新到github上。

具体电视台名称参见 https://raw.githubusercontent.com/zzq1234567890/epg/main/電視台總目錄.txt

主要是填写 tvg-id="电视台名称" tvg-name="电视台名称"

具体格式

#EXTM3U url-tvg="https://raw.githubusercontent.com/zzq1234567890/epg/refs/heads/main/epgziyong.xml.gz" catchup="append" catchup-source="?playseek=${(b)yyyyMMddHHmmss}-${(e)yyyyMMddHHmmss}"

#EXTINF:-1 tvg-id="电视台名称" tvg-name="电视台名称" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="你的分组",电视台名称

节目源地址


样例例如
#EXTINF:-1 tvg-id="cctv1" tvg-name="cctv1" tvg-logo="https://epg.51/tb1/CCTV/CCTV1.png" group-title="黑龙江移动",cctv-1 

http://ottrrs.hl.chinamobile.com/PLTV/88888888/224/3221226016/index.m3u8





本不提供diyp之类的epg,请用肥羊
diyp 影视等播放器，适合json格式。 推荐https://epg.v1.mk/json?ch=频道名&date=日期

https://epg.112114.xyz/?ch={name}&date={date}

https://epg.v1.mk/json?ch={name}&date={date}

影视模式

"epg": "https://epg.112114.xyz/?ch={name}&date={date}", "logo": "https://epg.112114.xyz/logo/{name}.png"
