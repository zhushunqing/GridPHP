<?php
/**
* GridPHP 远程调用自定义实现服务端通用包含文件
* @author ZhuShunqing
*/
$timer = getMsec();

$encode = $_POST['encode'] ? $_POST['encode'] : 'json';
$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
header('SERVER-ADDR: ' . $_SERVER['SERVER_ADDR']);
header('REMOTE-ADDR: ' . $_SERVER['REMOTE_ADDR']);
header('DATA-ENCODEING: ' . $encode);

//根据client端传递参数设置服务端PHP操时
$timeout = intval($_GET['timeout']);
$timeout = ($timeout > 0) ? $timeout : 10;
set_time_limit($timeout);

//去除转义符
strip_post();

//处理从CMI Client端请求来的数据
$mod = $_POST['module']; //模块名
$fun = $_POST['function']; //调用方法
$args = $_POST['args']; //接收到的参数

if($encode == 'serialize')
	$args = unserialize($args);
else
	$args = json_decode($args, 1);

//------------------------------------------------------------------------------

/**
* 输出返回数据
*/
function gridphp_api_output($rs){
	global $timer;
	$timer = getMsec() - $timer;
	//返回数据格式
	$data = new stdClass();
	$data->data = $rs;
	$data->phptime = $timer;
	$data->types = $encode;
	if($encode == 'serialize'){
		$out = serialize($data);  
	}else{
		$out = json_encode($data);
	}
	print $out;
}

/*
 * 去除转义符
 */
function strip_post(){
	if(get_magic_quotes_gpc())
		foreach($_POST as $k => $v)
				$_POST[$k] = stripslashes($v);
}

/**
 * 得到当前毫秒(自2011-01-01起)
 * @return float MilliSecond
 */
function getMsec(){
	list($usec, $sec) = explode(" ",microtime());
	return ( ((float)$sec - 1293811200) + (float)$usec) * 1000; //1293811200 = strtotime('2011-01-01')
}

?>
