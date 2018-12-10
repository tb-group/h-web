<?php
/**
 * Created by IntelliJ IDEA.
 * User: pi
 * Date: 12/10/18
 * Time: 12:38 PM
 */

$port=$_POST['port'];
//print_r  $port;
//var_dump($port);die();


//"e.g: php curl.php start or stop";
$username='admin';
$password='Chiji2018';
$URL="http://tbgw.shenhuan-tech.com:$port/ISAPI/PTZCtrl/channels/1/homeposition/goto";
//var_dump($URL);die();

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $URL);
curl_setopt($curl, CURLOPT_HEADER, 0);//定义是否显示状态头 1：显示 ； 0：不显示
//$header[] = "Content-type:image/jpeg";//定义header，可以加多个
//curl_setopt($curl, CURLOPT_HTTPHEADER, $header);//定义header
//curl_setopt($curl, CURLOPT_POST, 1);   //定义提交类型 1：POST ；0：GET
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT"); //定义请求类型，此处必须为大写
//$postdata = "";
//curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata); //定义提交的数据
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。定义是否直接输出返回流
curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
curl_setopt($curl, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds

$data = curl_exec($curl);
$status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);   //get status code
curl_close($curl);
//var_dump($data);
//$array = json_decode(json_encode(simplexml_load_string($data)),TRUE);
$array = (array) simplexml_load_string( $data );
//print_r($array);
