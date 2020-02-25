<?php
/**
* @name 抖音解析API
* @time 2019年9月1日20:17:10
* @author 教书先生
* @blog blog.oioweb.cn
*/
header('Access-Control-Allow-Origin:*');
header('Content-type:application/json; charset=utf-8');
error_reporting(0);
if(!array_key_exists('url',$_REQUEST))exit(error("缺少参数"));
$url =@$_REQUEST;
preg_match("/http:\/\/v.douyin.com\/\S+/",$url['url'],$res);
if (!$res)exit(error("请检查你输入的链接"));

function error($str){
    return json_encode([
        "code"=>-1,
        "msg"=>$str
        ],JSON_UNESCAPED_UNICODE);
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
preg_match_all("/itemId: \"([0-9]+)\"|dytk: \"(.*)\"/", curl($res[0]), $res, PREG_SET_ORDER);
if(!@$res[0][1] || !@$res[1][2])exit(error("数据异常"));
$arr = json_decode(curl("https://www.iesdouyin.com/web/api/v2/aweme/iteminfo/?item_ids={$res[0][1]}&dytk={$res[1][2]}"));
exit(json_encode([
    "code"=>1,
    "msg"=>"获取成功",
    "data"=>[
        'title' => $arr->item_list[0]->desc,
        'img' => $arr->item_list[0]->video->cover->url_list[0],
        'videourl' => curl($arr->item_list[0]->video->play_addr->url_list[0], true)
    ]
],JSON_UNESCAPED_UNICODE));

?>