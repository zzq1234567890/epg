<?php
header( 'Content-Type: text/plain; charset=UTF-8');
//if(date_default_timezone_get() != "Asia/Shanghai") date_default_timezone_set("Asia/Shanghai");
ini_set("max_execution_time", "3000000");
ini_set('date.timezone','Asia/Shanghai');
$fp="epghenan.xml";//压缩版本的扩展名后加.gz
function compress_html($string) {
    $string = str_replace("\r", '', $string); //清除换行符
    $string = str_replace("\n", '', $string); //清除换行符
    $string = str_replace("\t", '', $string); //清除制表符
    return $string;
}

function escape($str) 
{ 
preg_match_all("/[\x80-\xff].|[\x01-\x7f]+/",$str,$r); 
$ar = $r[0]; 
foreach($ar as $k=>$v) 
{ 
if(ord($v[0]) < 128) 
$ar[$k] = rawurlencode($v); 
else 
$ar[$k] = "%u".bin2hex(iconv("UTF-8","UCS-2",$v)); 
} 
return join("",$ar); 
} 

header( 'Content-Type: text/plain;charset=UTF-8');
$chn="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<!DOCTYPE tv SYSTEM \"http://api.torrent-tv.ru/xmltv.dtd\">\n<tv generator-info-name=\"秋哥綜合\" generator-info-url=\"https://www.tdm.com.mo/c_tv/?ch=Satellite\">\n";
$dt1=date('Ymd');//獲取當前日期
$dt2=date('Ymd',time()+24*3600);//第二天日期
$dt3=date('Ymd',time()+6*24*3600);
$w1=date("w");//當前第幾周
if ($w1<'1') {$w1=7;}
$w2=$w1+1;
$dt11=date('Y-m-d');
$time=time();
$id22=100935;//起始节目编号
$cid22=array(
array('145','河南卫视'),
array('141','河南都市'),
array('146','河南民生'),
array('147','河南法制'),
array('148','河南电视剧'),
array('149','河南新闻'),
array('150','河南欢腾购物'),
array('151','河南公共'),
array('152','河南乡村'),
array('153','河南国际'),
array('154','河南梨园戏曲'),
array('155','河南文物宝库'),
array('156','河南武术'),
array('157','河南晴彩中原'),
array('163','河南移动戏曲'),
array('183','河南象世界'),

);
$nid22=sizeof($cid22);
for ($idm22 = 1; $idm22 <= $nid22; $idm22++){
 $idd22=$id22+$idm22;
   $chn.="<channel id=\"".$cid22[$idm22-1][1]."\"><display-name lang=\"zh\">".$cid22[$idm22-1][1]."</display-name></channel>\n";       
}

for ($idm22 = 1; $idm22 <= $nid22; $idm22++){

$ts = time();

      $url22='https://pubmod.hntv.tv/program/getAuth/vod/originStream/program/'.$cid22[$idm22-1][0].'/'.$time;
 
$ch311 = curl_init();
curl_setopt_array($ch311, array(
CURLOPT_URL => 'https://api.cmc.hebtv.com/appapi/api/content/get-live-info',
CURLOPT_CUSTOMREQUEST => 'OPTIONS',
CURLOPT_RETURNTRANSFER => true,
CURLOPT_HEADER => true,
CURLOPT_NOBODY => true,
CURLOPT_VERBOSE => true,
CURLOPT_SSL_VERIFYPEER=>false,
CURLOPT_SSL_VERIFYHOST =>false,
));

$r = curl_exec($ch311);


curl_close($ch311);







$sign = hash('sha256', '6ca114a836ac7d73'.$ts);

 $idd22=$id22+$idm22;
    $ch22= curl_init();
    curl_setopt($ch22, CURLOPT_URL,$url22);
    curl_setopt($ch22, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch22, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch22, CURLOPT_SSL_VERIFYHOST, FALSE);
$headers22=[
'Host: pubmod.hntv.tv',
'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:101.0) Gecko/20100101 Firefox/101.0',
'sign: '.$sign,
'timestamp: '.$ts,
'Origin: https://static.hntv.tv',
'DNT: 1',
'Connection: keep-alive',
'Referer: https://static.hntv.tv/',
];

//curl_setopt($ch, CURLOPT_HTTPHEADER, array('timestamp: '.$ts,'sign: '.$sign));
//curl_setopt($ch, CURLOPT_USERAGENT, 'okhttp/3.12.0');
curl_setopt($ch22, CURLOPT_HTTPHEADER, $headers22);


curl_setopt($ch22,CURLOPT_ENCODING,'Vary: Accept-Encoding');
$re22 = curl_exec($ch22);
curl_close($ch22);
$data22 = json_decode($re22);
if (!isset($data22->programs) || !is_array($data22->programs) && !is_object($data22->programs)) {
    // 这个频道没节目数据，直接跳过
    continue;
}

$programs22 = $data22->programs;
$tum22 = count((array)$programs22);

for ($i22 = 0; $i22 < $tum22; $i22++) {
    $p = is_array($programs22) ? $programs22[$i22] : $programs22[$i22];
    
    if (empty($p->title) || empty($p->beginTime) || empty($p->endTime)) {
        continue;
    }

    $title22     = $p->title;
    $beginTime22 = $p->beginTime;
    $endTime22   = $p->endTime;

    $chn .= "<programme start=\"" . 
            str_replace([' ','-',':'], '', date('Y-m-d H:i:s', $beginTime22)) . " +0800\" stop=\"" . 
            str_replace([' ','-',':'], '', date('Y-m-d H:i:s', $endTime22)) . " +0800\" channel=\"" . 
            $cid22[$idm22-1][1] . "\">\n" .
            "<title lang=\"zh\">" . $title22 . "</title>\n" .
            "<desc lang=\"zh\"> </desc>\n</programme>\n";
}
}
$chn.="</tv>\n";
//写入文件。这里一次性写入，可以自己分次写入操作
file_put_contents($fp, $chn);
