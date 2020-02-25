<?php
/**
 * Created by PhpStorm
 * User: iqiqiya
 * Date: 2019/11/20
 * Time: 16:40
 * Blog: 77sec.cn
 */

error_reporting(0);
//获取302跳转后的url
function get_redirect_url($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    // 不需要页面内容
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    // 不直接输出
    curl_setopt($ch, CURLOPT_USERAGENT,"Mozilla/5.0 (iPhone; CPU iPhone OS 10_3 like Mac OS X) AppleWebKit/602.1.50 (KHTML, like Gecko) CriOS/56.0.2924.75 Mobile/14E5239e Safari/602.1");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // 返回最后的Location
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_exec($ch);
    $info = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    return $info;
}
function get_down_url($url){
    ini_set('user_agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3 like Mac OS X) AppleWebKit/602.1.50 (KHTML, like Gecko) CriOS/56.0.2924.75 Mobile/14E5239e Safari/602.1');
    return file_get_contents($url);
}
function zz_get_dytk($content){
    preg_match("~dytk(.*?)}~", $content, $matches);
    $Dytk = $matches[1];
    preg_match("~\"(.*?)\"~",$Dytk,$matches2);
    $dytk = $matches2[1];
    return $dytk;
}
function zz_get_item_ids($content){
    preg_match("~video/(.*?)/~", $content, $matches);
    $item_ids = $matches[1];
    return $item_ids;
}
function get_url($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    $output = json_decode($output,true);
    return $output;
}
function zz_video_url($content){
    preg_match("~https(.*?)\"~", $content, $matches);
    $item_ids = $matches[0];
    return $item_ids;
}
function curl($url, $getinfo=false)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent:Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1'));
    if($getinfo){
    curl_exec($ch);
    $data = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
    }else{
    $data = curl_exec($ch);
    }
    curl_close($ch);
    return $data;
}

function error($str){
    return json_encode([
        "code"=>-1,
        "msg"=>$str
        ],JSON_UNESCAPED_UNICODE);
}

$url = $_POST['url'];
$str_r= '/(http:\/\/|https:\/\/)((\w|=|\?|\.|\/|&|-)+)/';
preg_match_all($str_r,$url,$arr);

if (!$arr[0])exit(error("请检查你输入的链接"));

$share_url=$arr[0][0];

//$share_url = "http://v.douyin.com/xGSE7P/";
$url_302 =  get_redirect_url($share_url);//打印跳转后的链接

$mid = zz_get_item_ids($url_302);//匹配mid

$content1 =  get_down_url($url_302);//获得源吗
$dytk = zz_get_dytk($content1);//匹配dytk

$url_2 = "https://www.iesdouyin.com/web/api/v2/aweme/iteminfo/?item_ids=".$mid."&dytk=".$dytk;//拼接最后的url
$result = file_get_contents($url_2);
header('Content-Type:application/json; charset=utf-8');
//print_r($result);

$result = json_decode($result);

exit(json_encode([
    "code"=>1,
    "msg"=>"获取成功",
    "data"=>[
        'title' => $result->item_list[0]->desc,
        'img' => $result->item_list[0]->video->cover->url_list[0],
        'videourl' => curl($result->item_list[0]->video->play_addr->url_list[0], true)
    ]
    ],JSON_UNESCAPED_UNICODE));
?>