<?php
/** 产品密钥ID，产品标识 */
define("SECRETID", "your_secret_id");
/** 产品私有密钥，服务端生成签名信息使用，请严格保管，避免泄露 */
define("SECRETKEY", "your_secret_key");
/** 业务ID，易盾根据产品业务特点分配 */
define("BUSINESSID", "your_business_id");
/** 易盾反垃圾云服务文本批量在线检测接口地址 */
define("API_URL", "http://as.dun.163.com/v3/text/batch-check");
/** api version */
define("VERSION", "v3");
/** API timeout*/
define("API_TIMEOUT", 5);
require("../util.php");

/**
 * 反垃圾请求接口简单封装
 * $params 请求参数
 */
function check($params){
	$params["secretId"] = SECRETID;
	$params["businessId"] = BUSINESSID;
	$params["version"] = VERSION;
	$params["timestamp"] = time() * 1000;// time in milliseconds
	$params["nonce"] = sprintf("%d", rand()); // random int

	$params["signatureMethod"] = SIGNATURE_METHOD;
	$params = toUtf8($params);
	$params["signature"] = gen_signature(SECRETKEY, $params);
	// var_dump($params);

	$result = curl_post($params, API_URL, API_TIMEOUT);
	if($result === FALSE){
		return array("code"=>500, "msg"=>"file_get_contents failed.");
	}else{
		return json_decode($result, true);	
	}
}

// 简单测试
function main(){
    echo "mb_internal_encoding=".mb_internal_encoding()."\n";
    $texts = array();
    array_push($texts, array(
        "dataId"=>"dataId1",
		"content"=>"易盾测试内容！v3接口！"
		// "dataType"=>"1",
		// "ip"=>"123.115.77.137",
		// "account"=>"php@163.com",
		// "deviceType"=>"4",
		// "deviceId"=>"92B1E5AA-4C3D-4565-A8C2-86E297055088",
		// "callback"=>"ebfcad1c-dba1-490c-b4de-e784c2691768",
		// "publishTime"=>round(microtime(true)*1000),
		// "callbackUrl"=>"主动回调url地址"
    ));
    array_push($texts, array(
        "dataId"=>"dataId2",
        "content"=>"易盾测试内容！v3接口！"
    ));

	$params = array(
		"texts"=>json_encode($texts)
	);

	$ret = check($params);
	var_dump($ret);
	if ($ret["code"] == 200) {
        foreach($ret["result"] as $index => $value){
            $action = $value["action"];
            $taskId = $value["taskId"];
            $labelArray = $value["labels"];
            if ($action == 0) {
                echo "taskId={$taskId}，文本机器检测结果：通过\n";
            } else if ($action == 1) {
                echo "taskId={$taskId}，文本机器检测结果：嫌疑，需人工复审，分类信息如下：".json_encode($labelArray)."\n";
            } else if ($action == 2) {
                echo "taskId={$taskId}，文本机器检测结果：不通过，分类信息如下：".json_encode($labelArray)."\n";
            }
        }
    }
}

main();
?>
