<?php 
include("token.php");
//删除菜单
// $appid = "wxe70f44aeeaca52f7";
//$appsecret = "62ace525417accaabe8a67977bd23009";
//$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
//
//$ch = curl_init();
//curl_setopt($ch, CURLOPT_URL, $url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//$output = curl_exec($ch);
//curl_close($ch);
//$jsoninfo = json_decode($output, true);
//$access_token = $jsoninfo["access_token"];

//$access_token="c0V2MZfhSr4nGnxDD_lPI6nFIN0FkOgKJTa9bArDGPtqPFVvZVO-d_gg9Ld6AO0gilUiToaZWgpnW_b5En0at0wZwB-8RGQMtK9cxFT_XSIEv1BipbfyK3XdhqtVYSZHVTReAGADBZ";
$url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=".$access_token;
$result = https_request($url);
var_dump($result);

function https_request($url, $data = null){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}







?>