<?php

//______________________________________________________________________________
//GRIDPHP_BASE DEFIND
define('GRIDPHP_ROOT_PATH', dirname(dirname(__FILE__)) . '/');
define('GRIDPHP_CONF_PATH', GRIDPHP_ROOT_PATH . 'conf/');
define('GRIDPHP_CONFINC_PATH', GRIDPHP_ROOT_PATH . 'conf/@conf/');
define('GRIDPHP_DEF_PATH', GRIDPHP_ROOT_PATH . 'def/');
define('GRIDPHP_MOD_PATH', GRIDPHP_ROOT_PATH . 'mod/');
define('GRIDPHP_INC_PATH', GRIDPHP_ROOT_PATH . 'inc/');
define('GRIDPHP_DBG_PATH', GRIDPHP_ROOT_PATH . 'dbg/');
define('GRIDPHP_AJAX_PATH', GRIDPHP_ROOT_PATH . 'ajax/');

//AJAX接口返回代码
define('GRIDPHP_AJAX_RET_CODE_SUCC', 1);		//请求成功
define('GRIDPHP_AJAX_ERR_NOT_MOD', -2000);		//接口模块不存在
define('GRIDPHP_AJAX_ERR_NOT_FOUND', -2001);	//接口模块不存在
define('GRIDPHP_AJAX_ERR_NOT_LOGIN', -2002);	//用户未登录
define('GRIDPHP_AJAX_ERR_NOT_FUNC', -2003);		//方法不存在
define('GRIDPHP_AJAX_ERR_BAD_REQUEST', -2004);	//参数不对或请求错误

define('GRIDPHP_WWW_PATH', '/var/www/gridphp/');
define('GRIDPHP_ERROR_PATH', '/var/log/gridphp/');

//方法调用cache前缀
define('GRIDPHP_FUNCALL_CACHE', 'gridphp_cache_');
//cache更新计数器
define('GRIDPHP_REKEY_CACHE', 'gridphp_rekey_');
//cache延迟更新队列
define('GRIDPHP_REKEY_DELAY', 'gridphp_recache_delay');
//cache延迟更新默认时间秒，为0不开启
define('GRIDPHP_REKEY_DELAY_DEF', 0);
//cache延迟更新队列有效保留时间
define('GRIDPHP_REKEY_DELAY_TIMER', 600);

//______________________________________________________________________________
//GRIDPHP_HTTP DEFIND

//默认开启HTTP功能
if(!defined('GRIDPHP_HTTP_SWITCH')) define('GRIDPHP_HTTP_SWITCH', 1);

//常规配置
define('GRIDPHP_HTTP_NONBLOCK', 0);				//异步开启
define('GRIDPHP_HTTP_READ_BUFF_LEN', 4096);		//读缓存大小k
define('GRIDPHP_HTTP_WRITE_DATA_LEN', 4096);	//写缓存大小k
define('GRIDPHP_HTTP_DEFAULT_TIMEOUT', 10000);	//默认超时ms

//错误代码
define('GRIDPHP_HTTP_ERR_READ_TIMEOUT', -1001);	//读取数据超时
define('GRIDPHP_HTTP_ERR_WRITE_TIMEOUT', -1002);//发送数据超时
define('GRIDPHP_HTTP_ERR_NO_SOCKET', -1003);	//不支持socket方法
define('GRIDPHP_HTTP_ERR_NO_CONNECT', -1004);	//接口未请求完成
define('GRIDPHP_HTTP_ERR_NO_PARSEDATA', -1005);	//返回数据未能正常解析
define('GRIDPHP_HTTP_ERR_BAD_REQUEST', -1006);//服务器请求错误
define('GRIDPHP_HTTP_ERR_BAD_SERVICE', -1007);//服务器请求错误

//并发进程模式 0关闭 1自动 2全部
define('GRIDPHP_HTTP_THREAD_MODE', 0);

//发送数据超过指定长度自动开启多进程
define('GRIDPHP_HTTP_THREAD_LENGTH', 4096);

//______________________________________________________________________________
//GRIDPHP_COMMON DEFIND
define('GRIDPHP_TIME_NOW', time());
define('GRIDPHP_DATE_NOW', date('Y-m-d H:i:s'));
define('GRIDPHP_TODAY_DATE', date('Y-m-d'));
define('GRIDPHP_TODAY_TIMER', strtotime('tomorrow') - time()); //距当日24点还剩秒数，可用于cache当日过期

//______________________________________________________________________________
//return conf

return array(

	//默认加载模块
	'default_modules' => array('utility', 'debug', 'log', 'http', 'dba', 'dbr', 'incr', 'memcd', 'errmsg'),

	//HTTP请求校验key
	'sign_key'	=> 'loigVulvavyadjilkicmokWofvedtog3',

	//服务器环境
	'server_env' => array(
		''					=> 'null',	//本地
		// '192.168.1.100'		=> 'server',//本地
		'192.168.0.100'		=> 'local',	//本地
		'192.168.1.20'		=> 'local',	//本地
	),

);

?>