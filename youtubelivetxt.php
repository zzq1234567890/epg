<?php
//$id=$_GET['id'];
header( 'Content-Type: text/plain; charset=UTF-8');
$fp="youtubelive.txt";//压缩版本的扩展名后加.gz
$chn="\n";
$chn.=  "遊戲直播,#genre#\r\n";
$url2='https://www.youtube.com/playlist?list=PLiCvVJzBupKlQ50jZqLas7SAztTMEYv1f';//遊戲
$ch2=curl_init();
curl_setopt($ch2,CURLOPT_URL,$url2);                  
curl_setopt($ch2,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, FALSE);
//curl_setopt($ch2, CURLOPT_HTTPHEADER, $headers);
//curl_setopt($ch2, CURLOPT_COOKIE,$cookie);
//curl_setopt($ch2,CURLOPT_ENCODING,'Vary: Accept-Encoding');
$re2=curl_exec($ch2);
curl_close($ch2);
$re2 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re2);// 適合php7

preg_match_all('|\{"playlistVideoRenderer":\{"videoId":"(.*?)",|i',$re2,$piec2,PREG_SET_ORDER);//vid

preg_match_all('|"shortBylineText":\{"runs":\[\{"text":"(.*?)",|i',$re2,$piek2,PREG_SET_ORDER);//標題

$tru2=count($piec2);
  for ($k2 = 0; $k2 <=$tru2-1; $k2++) {

  $chn.= "".str_replace('【好劇LIVE24h爆肝直播】','',str_replace('Live!','',str_replace('LIVE:','',str_replace('正在直播:','',str_replace('【LIVE】','',str_replace('【ON AIR】','', $piek2[$k2][1])))))).",https://www.youtube.com/watch?v=".$piec2[$k2][1]."\r\n";
}

$chn.= "運動直播,#genre#\r\n";
$url3='https://www.youtube.com/playlist?list=PL8fVUTBmJhHJrxHg_uNTMyRmsWbFltuQV';//運動

$ch3=curl_init();
curl_setopt($ch3,CURLOPT_URL,$url3);                  
curl_setopt($ch3,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch3, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch3, CURLOPT_SSL_VERIFYHOST, FALSE);
//curl_setopt($ch3, CURLOPT_HTTPHEADER, $headers);
//curl_setopt($ch3, CURLOPT_COOKIE,$cookie);
//curl_setopt($ch3,CURLOPT_ENCODING,'Vary: Accept-Encoding');
$re3=curl_exec($ch3);
curl_close($ch3);
$re3 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re3);// 適合php7

preg_match_all('|\{"playlistVideoRenderer":\{"videoId":"(.*?)",|i',$re3,$piec3,PREG_SET_ORDER);//vid

preg_match_all('|"shortBylineText":\{"runs":\[\{"text":"(.*?)",|i',$re3,$piek3,PREG_SET_ORDER);//標題
$tru3=count($piec3);
  for ($k3 = 0; $k3 <=$tru3-1; $k3++) {

  $chn.= "".str_replace('【好劇LIVE24h爆肝直播】','',str_replace('Live!','',str_replace('LIVE:','',str_replace('正在直播:','',str_replace('【LIVE】','',str_replace('【ON AIR】','', $piek3[$k3][1])))))).",https://www.youtube.com/watch?v=".$piec3[$k3][1]."\r\n";
}
$chn.= "正在直播,#genre#\r\n";
//$url4='https://www.youtube.com/playlist?list=PLrLHrWhAPwBkfF03f4bfUZOyicUpYGJJz';//直播綜合
//$url4='https://www.youtube.com/playlist?list=PLU12uITxBEPHb1IgzEPHZpje-903Qkfne';//即將播出
//https://www.youtube.com/playlist?list=PLU12uITxBEPHb1IgzEPHZpje-903Qkfne即將播出
//https://www.youtube.com/playlist?list=PLU12uITxBEPHho8JAl4QvWgmiYw7CiNCC近期直播
$url4='https://www.youtube.com/playlist?list=PLU12uITxBEPFJGVb2zSgCaWvMBe7vHonB';//正在直播
$ch4=curl_init();
curl_setopt($ch4,CURLOPT_URL,$url4);                  
curl_setopt($ch4,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch4, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch4, CURLOPT_SSL_VERIFYHOST, FALSE);
//curl_setopt($ch4, CURLOPT_HTTPHEADER, $headers);
//curl_setopt($ch4, CURLOPT_COOKIE,$cookie);
//curl_setopt($ch4,CURLOPT_ENCODING,'Vary: Accept-Encoding');
$re4=curl_exec($ch4);
curl_close($ch4);
$re4 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re4);// 適合php7

preg_match_all('|\{"playlistVideoRenderer":\{"videoId":"(.*?)",|i',$re4,$piec4,PREG_SET_ORDER);//vid

preg_match_all('|"shortBylineText":\{"runs":\[\{"text":"(.*?)",|i',$re4,$piek4,PREG_SET_ORDER);//標題
$tru4=count($piec4);
  for ($k4 = 0; $k4 <=$tru4-1; $k4++) {

  $chn.= "".str_replace('【好劇LIVE24h爆肝直播】','',str_replace('Live!','',str_replace('LIVE:','',str_replace('正在直播:','',str_replace('【LIVE】','',str_replace('【ON AIR】','', $piek4[$k4][1])))))).",https://www.youtube.com/watch?v=".$piec4[$k4][1]."\r\n";
}

$chn.= "少兒,#genre#\r\n";
$url5='https://www.youtube.com/playlist?list=PLd8qbe5zE33ufMlSpRWhDEUplpYlVsyaS';//少兒

$ch5=curl_init();
curl_setopt($ch5,CURLOPT_URL,$url5);                  
curl_setopt($ch5,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch5, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch5, CURLOPT_SSL_VERIFYHOST, FALSE);

$re5=curl_exec($ch5);
curl_close($ch5);
$re5= preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re5);// 適合php7

preg_match_all('|\{"playlistVideoRenderer":\{"videoId":"(.*?)",|i',$re5,$piec5,PREG_SET_ORDER);//vid
preg_match_all('|"shortBylineText":\{"runs":\[\{"text":"(.*?)",|i',$re5,$piek5,PREG_SET_ORDER);//標題

$tru5=count($piec5);
  for ($k5 = 0; $k5 <=$tru5-1; $k5++) {

  $chn.= "".str_replace('【好劇LIVE24h爆肝直播】','',str_replace('Live!','',str_replace('LIVE:','',str_replace('正在直播:','',str_replace('【LIVE】','',str_replace('【ON AIR】','', $piek5[$k5][1])))))).",https://www.youtube.com/watch?v=".$piec5[$k5][1]."\r\n";
}


$chn.= "娛樂,#genre#\r\n";
$url6='https://www.youtube.com/playlist?list=PLd8qbe5zE33t4Q78Q1TxE8K953dMiTC9S';//娛樂
$ch6=curl_init();
curl_setopt($ch6,CURLOPT_URL,$url6);                  
curl_setopt($ch6,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch6, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch6, CURLOPT_SSL_VERIFYHOST, FALSE);
//curl_setopt($ch6, CURLOPT_HTTPHEADER, $headers);
//curl_setopt($ch6, CURLOPT_COOKIE,$cookie);
//curl_setopt($ch6,CURLOPT_ENCODING,'Vary: Accept-Encoding');
$re6=curl_exec($ch6);
curl_close($ch6);
$re6= preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re6);// 適合php7

preg_match_all('|\{"playlistVideoRenderer":\{"videoId":"(.*?)",|i',$re6,$piec6,PREG_SET_ORDER);//vid
preg_match_all('|"shortBylineText":\{"runs":\[\{"text":"(.*?)",|i',$re6,$piek6,PREG_SET_ORDER);//標題
$tru6=count($piec6);
  for ($k6 = 0; $k6 <=$tru6-1; $k6++) {

  $chn.= "".str_replace('【好劇LIVE24h爆肝直播】','',str_replace('Live!','',str_replace('LIVE:','',str_replace('正在直播:','',str_replace('【LIVE】','',str_replace('【ON AIR】','', $piek6[$k6][1])))))).",https://www.youtube.com/watch?v=".$piec6[$k6][1]."\r\n";
}






$chn.= "新聞,#genre#\r\n";
$url8='https://www.youtube.com/playlist?list=PLd8qbe5zE33trmwWLpiCr7DjzsoUb0-Jj';//直播綜合
$ch8=curl_init();
curl_setopt($ch8,CURLOPT_URL,$url8);                  
curl_setopt($ch8,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch8, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch8, CURLOPT_SSL_VERIFYHOST, FALSE);

$re8=curl_exec($ch8);
curl_close($ch8);
$re8= preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re8);// 適合php7

preg_match_all('|\{"playlistVideoRenderer":\{"videoId":"(.*?)",|i',$re8,$piec8,PREG_SET_ORDER);//vid
preg_match_all('|"shortBylineText":\{"runs":\[\{"text":"(.*?)",|i',$re8,$piek8,PREG_SET_ORDER);//標題
$tru8=count($piec8);
  for ($k8 = 0; $k8 <=$tru8-1; $k8++) {

  $chn.= "".str_replace('【好劇LIVE24h爆肝直播】','',str_replace('Live!','',str_replace('LIVE:','',str_replace('正在直播:','',str_replace('【LIVE】','',str_replace('【ON AIR】','', $piek8[$k8][1])))))).",https://www.youtube.com/watch?v=".$piec8[$k8][1]."\r\n";
}
$chn.= "國外新聞,#genre#\r\n";
$url4='https://www.youtube.com/playlist?list=PLd8qbe5zE33s5OSV4qzMMkCWoYItL7otl';//直播綜合
//$url4='https://www.youtube.com/playlist?list=PLU12uITxBEPHb1IgzEPHZpje-903Qkfne';//即將播出

$ch4=curl_init();
curl_setopt($ch4,CURLOPT_URL,$url4);                  
curl_setopt($ch4,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch4, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch4, CURLOPT_SSL_VERIFYHOST, FALSE);
//curl_setopt($ch4, CURLOPT_HTTPHEADER, $headers);
//curl_setopt($ch4, CURLOPT_COOKIE,$cookie);
//curl_setopt($ch4,CURLOPT_ENCODING,'Vary: Accept-Encoding');
$re4=curl_exec($ch4);
curl_close($ch4);
$re4 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re4);// 適合php7

preg_match_all('|\{"playlistVideoRenderer":\{"videoId":"(.*?)",|i',$re4,$piec4,PREG_SET_ORDER);//vid
preg_match_all('|"shortBylineText":\{"runs":\[\{"text":"(.*?)",|i',$re4,$piek4,PREG_SET_ORDER);//標題
$tru4=count($piec4);
  for ($k4 = 0; $k4 <=$tru4-1; $k4++) {

  $chn.= "".str_replace('【好劇LIVE24h爆肝直播】','',str_replace('Live!','',str_replace('LIVE:','',str_replace('正在直播:','',str_replace('【LIVE】','',str_replace('【ON AIR】','', $piek4[$k4][1])))))).",https://www.youtube.com/watch?v=".$piec4[$k4][1]."\r\n";
}

print "英語學習,#genre#\r\n";
$url9='https://www.youtube.com/playlist?list=PLd8qbe5zE33uW6vfsO9ZZGCUzbStqtNxS';//直播綜合
$ch9=curl_init();
curl_setopt($ch9,CURLOPT_URL,$url9);                  
curl_setopt($ch9,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch9, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch9, CURLOPT_SSL_VERIFYHOST, FALSE);

$re9=curl_exec($ch9);
curl_close($ch9);
$re9= preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re9);// 適合php7

preg_match_all('|\{"playlistVideoRenderer":\{"videoId":"(.*?)",|i',$re9,$piec9,PREG_SET_ORDER);//vid
preg_match_all('|"shortBylineText":\{"runs":\[\{"text":"(.*?)",|i',$re9,$piek9,PREG_SET_ORDER);//標題
$tru9=count($piec9);
  for ($k9 = 0; $k9 <=$tru9-1; $k9++) {

  $chn.= "".str_replace('【好劇LIVE24h爆肝直播】','',str_replace('Live!','',str_replace('LIVE:','',str_replace('正在直播:','',str_replace('【LIVE】','',str_replace('【ON AIR】','', $piek9[$k9][1])))))).",https://www.youtube.com/watch?v=".$piec9[$k9][1]."\r\n";
}


$chn.= "街景,#genre#\r\n";
$url7='https://www.youtube.com/playlist?list=PLd8qbe5zE33v2Ip13eAc38OgpPksxQ7mE';//直播綜合
$ch7=curl_init();
curl_setopt($ch7,CURLOPT_URL,$url7);                  
curl_setopt($ch7,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch7, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch7, CURLOPT_SSL_VERIFYHOST, FALSE);

$re7=curl_exec($ch7);
curl_close($ch7);
$re7= preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re7);// 適合php7

preg_match_all('|\{"playlistVideoRenderer":\{"videoId":"(.*?)",|i',$re7,$piec7,PREG_SET_ORDER);//vid
preg_match_all('|"shortBylineText":\{"runs":\[\{"text":"(.*?)",|i',$re7,$piek7,PREG_SET_ORDER);//標題
$tru7=count($piec7);
  for ($k7 = 0; $k7 <=$tru7-1; $k7++) {

  $chn.= "".str_replace('【好劇LIVE24h爆肝直播】','',str_replace('Live!','',str_replace('LIVE:','',str_replace('正在直播:','',str_replace('【LIVE】','',str_replace('【ON AIR】','', $piek7[$k7][1])))))).",https://www.youtube.com/watch?v=".$piec7[$k7][1]."\r\n";
}

$chn.= "廣告,#genre#\r\n";
$url10='https://www.youtube.com/playlist?list=PLd8qbe5zE33tN_4OSmIvc1QM82jCP4BI3';//少兒

$ch10=curl_init();
curl_setopt($ch10,CURLOPT_URL,$url10);                  
curl_setopt($ch10,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch10, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch10, CURLOPT_SSL_VERIFYHOST, FALSE);

$re10=curl_exec($ch10);
curl_close($ch10);
$re10= preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re10);// 適合php7

preg_match_all('|\{"playlistVideoRenderer":\{"videoId":"(.*?)",|i',$re10,$piec10,PREG_SET_ORDER);//vid
preg_match_all('|"shortBylineText":\{"runs":\[\{"text":"(.*?)",|i',$re10,$piek10,PREG_SET_ORDER);//標題
$tru10=count($piec10);
  for ($k10 = 0; $k10 <=$tru10-1; $k10++) {

  $chn.= "".str_replace('【好劇LIVE24h爆肝直播】','',str_replace('Live!','',str_replace('LIVE:','',str_replace('正在直播:','',str_replace('【LIVE】','',str_replace('【ON AIR】','', $piek10[$k10][1])))))).",https://www.youtube.com/watch?v=".$piec10[$k10][1]."\r\n";
}

$chn.= "臨時直播,#genre#\r\n";
$url12='https://www.youtube.com/playlist?list=PLd8qbe5zE33v8XouhXYxUjn954xIPaSEN';//遊戲
$ch12=curl_init();
curl_setopt($ch12,CURLOPT_URL,$url12);                  
curl_setopt($ch12,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch12, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch12, CURLOPT_SSL_VERIFYHOST, FALSE);
//curl_setopt($ch12, CURLOPT_HTTPHEADER, $headers);
//curl_setopt($ch12, CURLOPT_COOKIE,$cookie);
//curl_setopt($ch12,CURLOPT_ENCODING,'Vary: Accept-Encoding');
$re12=curl_exec($ch12);
curl_close($ch12);
$re12 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re12);// 適合php7

preg_match_all('|\{"playlistVideoRenderer":\{"videoId":"(.*?)",|i',$re12,$piec12,PREG_SET_ORDER);//vid
preg_match_all('|"shortBylineText":\{"runs":\[\{"text":"(.*?)",|i',$re12,$piek12,PREG_SET_ORDER);//標題
$tru12=count($piec12);
  for ($k12 = 0; $k12 <=$tru12-1; $k12++) {

  $chn.= "".str_replace('【好劇LIVE24h爆肝直播】','',str_replace('Live!','',str_replace('LIVE:','',str_replace('正在直播:','',str_replace('【LIVE】','',str_replace('【ON AIR】','', $piek12[$k12][1])))))).",https://www.youtube.com/watch?v=".$piec12[$k12][1]."\r\n";
}






$chn.= "鳳凰直播,#genre#\r\n";
$url8='https://console.zhibo.ifeng.com/web/live/channel?page=1&id=68&state=1';


$ch8=curl_init();
curl_setopt($ch8,CURLOPT_URL,$url8);
curl_setopt($ch8,CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch8,CURLOPT_SSL_VERIFYHOST,false);
curl_setopt($ch8,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch8,CURLOPT_USERAGENT,'Mozilla/5.0');
$re8=curl_exec($ch8);
curl_close($ch8);
preg_match_all('|\{"id":"68","liveId":(.*?),"title|i',$re8,$piec8,PREG_SET_ORDER);//節目id
preg_match_all('|\,"title":"(.*?)","state"\:1,"thumb|i',$re8,$piek8,PREG_SET_ORDER);//節目標題
$tru8=count($piec8);
  for ($k8 = 0; $k8 <=$tru8-1; $k8++) {

  $chn.= "".str_replace('【好劇LIVE24h爆肝直播】','',str_replace('Live!','',str_replace('LIVE:','',str_replace('正在直播:','',str_replace('【LIVE】','',str_replace('【ON AIR】','', $piek8[$k8][1])))))).",http://zzqwe.giize.com:12229/fengzhibo.php?id=".$piec8[$k8][1]."\r\n";
}




file_put_contents($fp, $chn);
?>
