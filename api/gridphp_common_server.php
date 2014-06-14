<?php
/**
* GRIDPHP 远程调用接口
* @author ZhuShunqing
*/

$_SERVER['REMOTE_ADDR'] = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
header('SERVER-ADDR: ' . $_SERVER['SERVER_ADDR']);
header('REMOTE-ADDR: ' . $_SERVER['REMOTE_ADDR']);

$timer = getMsec();

//防止外部调用
//check_request();

//注册退出调用
register_shutdown_function('shutdown_function_slowlog');

define('GRIDPHP_HTTP_SWITCH', 0); //Server模式关闭HTTP方式
require_once('../GridPHP.inc.php');
$GP = &$GLOBALS['GRIDPHP'];

//get_magic_quotes_gpc自动转义
$request = $GP->utility->loadC('request');
$request->strip_request();

//根据client端传递参数设置服务端PHP操时
$timeout = $request->getQuery('timeout', 'intval', 10);
@set_time_limit($timeout);

//使用serialize或json编码数据 php4下json效率极差
$encode = $request->getPost('encode', 'string', 'json');
header('DATA-ENCODEING: ' . $encode);
$iserialize = ($encode == 'serialize');

//请求数据校验
check_sign();

if($iserialize){
	$multi = unserialize($request->getPost('multidata'));
}else{
	$GP->utility->loadC('json');
	$multi = $GP->utility->json->decode($request->getPost('multidata'), 1);
}

if(!$multi)
	$multi = array(
		array(
			'module'	=> $request->getPost('module'),
			'function'	=> $request->getPost('function'),
			'args'		=> $request->getPost('args'),
			'types'		=> $request->getPost('types'),
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
	//$out = $GP->utility->json->encode($data);
	$out = json_encode($data);
}

print $out;

exit(1);


///////////////////////////////////////////////////////////////////////////////////////////////

//请求数据校验
function check_sign(){
	global $GP, $request;
	if($request->getPost('sign') != $GP->httpsign($_POST)){
		die('Invaild request!');
	}
}

/**
 * 防止外部调用
 */
function check_request(){
	if(
		substr($_SERVER['REMOTE_ADDR'], 0, 3) != '10.'
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
		$log .= strlen($request->getPost('multidata')) . "\t";
		$log .= $num . "\t";
		$log .= $timer . "\t";
		$log .= "\n";
		$GP->log->writelog('gridphp_http_slow.log', $log);
	}

}

?>
