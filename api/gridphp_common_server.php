<?php
/**
* GRIDPHP 远程调用接口
* @author ZhuShunqing
*/

//使用serialize或json编码数据
$encode = isset($_GET['encode']) ? $_GET['encode'] : 'json';
$iserialize = ($encode == 'serialize');

if(isset($_GET['gz']) && $_GET['gz']){
	$data = gzuncompress(file_get_contents("php://input"));
	if($iserialize)
		$_POST = unserialize($data);
	else
		$_POST = json_decode($data);
}

$_SERVER['REMOTE_ADDR'] = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
header('SERVER-ADDR: ' . $_SERVER['SERVER_ADDR']);
header('REMOTE-ADDR: ' . $_SERVER['REMOTE_ADDR']);
header('DATA-ENCODEING: ' . $encode);

$timer = getMsec();

//防止外部调用
// check_request();

//注册退出调用
register_shutdown_function('shutdown_function_slowlog');

//去除转义符
strip_post();

//根据client端传递参数设置服务端PHP操时
$timeout = intval($_GET['timeout']);
$timeout = ($timeout > 0) ? $timeout : 10;
@set_time_limit($timeout);

if(defined('SAE_APPNAME'))
	define('GRIDPHP_SERVER_ENV', 'sae');
else
	define('GRIDPHP_SERVER_ENV', 'server');
require_once('../GridPHP.inc.php');
$GP = $GLOBALS['GRIDPHP'];

//请求数据校验
check_sign();

$multi = null;
if($iserialize){
	$multi = unserialize($_POST['multidata']);
}else{
	$GP->utility->loadC('json');
	if(isset($_POST['multidata']))
		$multi = $GP->utility->json->decode($_POST['multidata'], 1);
}

if(!$multi)
	$multi = array(
		array(
			'module'	=> $_POST['module'],
			'function'	=> $_POST['function'],
			'args'		=> $_POST['args'],
			'types'		=> $_POST['types'],
		)
	);

$rs = array();
foreach($multi as $t){

	$data = null;
	$mod = $t['module'];
	$fun = $t['function'];

	if($iserialize){
		$args = unserialize($t['args']);
	}else{
		$args = $GP->utility->json->decode($t['args']);
		//还原参数对象类型
		$types = $GP->utility->json->decode($t['types'], 1);
		if($types)
			$GP->utility->json->recover_array(&$args, $types);
	}

	if($mod && $GP->mod($mod)){
		$GP->$mod->setHTTP($fun, 0); //保证HTTP模式已关闭
		$data = call_user_func_array(array(&$GP->$mod, $fun), $args);
	}else{
		//模块不存在
		$data = array('status' => -1);
	}

	$rs[] = $data;

}

$rs = (count($rs) == 1) ? $rs[0] : $rs;

$timer = getMsec() - $timer;

//返回数据格式
$data = new stdClass();
$data->data = $rs;
$data->phptime = $timer;

//原数据节点类型
if($iserialize){
	$data->types = 'serialize';
	$out = serialize($data);
}else{
	$types = $GP->utility->json->objtypes($rs);
	$data->types = $types;
	$out = $GP->utility->json->encode($data);;
}

print $out;

exit(1);


///////////////////////////////////////////////////////////////////////////////////////////////

//请求数据校验
function check_sign(){
	global $GP;
	if($_POST['sign'] !== $GP->httpsign($_POST)){
		die('Invaild request!');
	}
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
 * 防止外部调用
 */
function check_request(){
	if(
		substr($_SERVER['REMOTE_ADDR'], 0, 3) != '10.'
		&&
		substr($_SERVER['REMOTE_ADDR'], 0, 12) != '124.' . '193.' . '193.' //办公环境测试
	){
		header("HTTP/1.0 403 Forbidden");
		die('Forbidden!');
	}
}

/**
 * 得到当前毫秒(自2011-01-01起)
 * @return float MilliSecond
 */
function getMsec(){
	list($usec, $sec) = explode(" ", microtime());
	return ( ((float)$sec - 1293811200) + (float)$usec) * 1000; //1293811200 = strtotime('2011-01-01')
}

/**
 * 退出处理
 */
function shutdown_function_slowlog(){
	global $GP, $timer, $multi;
	$num = count($multi);

	//慢日志
	if(false && $timer > 50){
		$log = $_SERVER['REMOTE_ADDR'] . "\t";
		$log .= date('Y-m-d H:i:s') . "\t";
		$log .= strlen($_POST['multidata']) . "\t";
		$log .= $num . "\t";
		$log .= $timer . "\t";
		$log .= "\n";
		$GP->log->writelog('gridphp_http_slow.log', $log);
	}

}

?>
