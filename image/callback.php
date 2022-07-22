<?php
/** 产品密钥ID，产品标识 */
define("SECRETID", "your_secret_id");
/** 产品私有密钥，服务端生成签名信息使用，请严格保管，避免泄露 */
define("SECRETKEY", "your_secret_key");
/** 业务ID，易盾根据产品业务特点分配 */
define("BUSINESSID", "your_business_id");
/** 易盾反垃圾云服务图片离线检测结果获取接口地址 */
define("API_URL", "http://as.dun.163.com/v4/image/callback/results");
/** api version */
define("VERSION", "v4");
/** API timeout*/
define("API_TIMEOUT", 10);
require("../util.php");

/**
 * 反垃圾请求接口简单封装
 * $params 请求参数
 */
function check(){
    $params = array();
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
	// var_dump($result);
	if($result === FALSE){
		return array("code"=>500, "msg"=>"file_get_contents failed.");
	}else{
		return json_decode($result, true);	
	}
}

// 简单测试
function main(){
    echo "mb_internal_encoding=".mb_internal_encoding()."\n";
	$ret = check();
	var_dump($ret);
	
	if ($ret["code"] == 200) {
		$antispamArray = $ret["antispam"];
		foreach($antispamArray as $index => $image_ret){
		    $name = $image_ret["name"];
		    $taskId = $image_ret["taskId"];
		    $action = $image_ret["action"];
		    $labelArray = $image_ret["labels"];
		    echo "taskId={$taskId}，name={$name}，action={$action}\n";
		    foreach($image_ret["labels"] as $index=>$label){
		        echo "label:{$label["label"]}, level={$label["level"]}, rate={$label["rate"]}\n";
		    }
		    if($action==0){
			echo "#图片人工复审结果：最高等级为：正常\n";
		    }else if($action==2){
			echo "#图片人工复审结果：最高等级为：确定\n";
		    }
		}
    }else{
    	var_dump($ret);
    }
}

main();
?>
