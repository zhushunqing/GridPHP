<?php
/**
* GRIDPHP公共模块客户端接口
* @author ZhuShunqing
*/
require_once('../GridPHP.inc.php');
$GP = $GLOBALS['GRIDPHP'];

//get_magic_quotes_gpc自动转义
$request = $GP->utility->loadC('request');
$request->strip_request();

$mod = $request->getParam('mod');
$func = $request->getParam('func');
$device = $request->getParam('device');
$checkcode = $request->getParam('cc', 'intary');

if($mod == 'msg' && $func == 'list') $func = 'listsys'; //临时兼容接口

if($GP->mod($mod)){

	//是否已登录
	// $GP->mod('login');
	// $GP->mod('permission');
	// $uid = $GP->login->getSession('uid'); //从登录session中取uid
	// $is_intercept = $GP->permission->is_can_post($uid); //是否被拦截了...

	//是否强制登录
	if(
		($uid && !empty($is_intercept))
		|| $GP->$mod->getConf('AJAX_CONFIG', 'anonymous', $func)
	){
		
		$_REQUEST['cmiajax_uid'] = floatval($uid);
		$_REQUEST['func'] = $func;
		$ret = $GP->$mod->AJAX($_REQUEST);
		
		//Cehckpoint统计
		foreach($checkcode as $i => $code) {
			$GP->log->checkpoint_code($code, $uid);
		}

	}else{
		//返回错误代码
		$ret = array(
			'retcode'	=> GRIDPHP_AJAX_ERR_NOT_LOGIN,
			'retmean'	=> 'GRIDPHP_AJAX_ERR_NOT_LOGIN'
		);
	}

}else{
	$ret = array(
		//返回代码
		'retcode'	=> GRIDPHP_AJAX_ERR_NOT_MOD,
		//代码含义
		'retmean'	=> 'GRIDPHP_AJAX_ERR_NOT_MOD'
	);
}

if($device){

	foreach($_POST as $k => &$v)
		if(strlen($v) > 512)
			$v = substr($v, 0, 512) . '...'; //截取一部分避免日志过长

	$log = array(
		'time'		=> GRIDPHP_DATE_NOW,
		'uid'		=> $uid,
		'device'	=> $device,
		'agent'		=> $_SERVER['HTTP_USER_AGENT'],
		'clientinfo' => $GP->$mod->getClientInfo(),
		'get'		=> $_GET,
		'post'		=> $_POST,
		'args'		=> $args,
		'return'	=> $ret,
	);

	$memc = $GP->memcd->loadMemc('test');
	$key = 'cmiajax_' . md5($device);
	$memc->listPush($key, $log, 0, 600);
}

print @json_encode($ret);

?>