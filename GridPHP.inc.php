<?php
/**
* GridPHP－通用高效的统一数据模型、分布式计算接口开发框架
* @author ZhuShunqing
* @link http://www.gridphp.com
* @version 1.1
*/
class GRIDPHP{

	var $_CONFIG = array(), 
		$_SERVENV = null, 
		$_REQSRC = null, 
		$_SERVIP = null, 
		$_RPC_TASKS = array(),
		$_MOD_INITED = array();

	/**
	* GRIDPHP 构造函数
	* @param string [[模块名1][,模块名2][,模块名...]]
	* @return void
	*/
	function GRIDPHP(){
		$this->parse_args();

		//default mods
		$this->defmods = $this->getConf('default_modules');
		foreach($this->defmods as $m)
			$this->mod($m);

		foreach($this->defmods as $m)
			foreach($this->defmods as $a)
				if($m != $a) $this->$m->$a = &$this->$a;

		//construct mods
		$args = func_get_args();
		foreach($args as $m) $this->mod($m);

		//register autoload class
		spl_autoload_register('GRIDPHP::autoload');
	}

	/**
	* 动态加载指定模块
	* @param string $m 模块名称
	* @return int 1成功 2已加载 0模块不存在
	*/
	function &mod($m){
		if(!isset($this->$m)){

			//默认加载mod位置
			if(in_array($m, $this->defmods)){
				$path = GRIDPHP_DEF_PATH;
			//前缀分类mod位置
			}else if(count($prefix = explode('_', $m)) > 1){
				$prefix = array_shift($prefix);
				$path = GRIDPHP_ROOT_PATH . $prefix[0] . '_mod/';
			//其它默认位置
			}else{
				$path = GRIDPHP_MOD_PATH;
			}
			$file = $path . $m . '.mod.php';

			$load = file_exists($file);
			if($load){
				//加载主模块类
				require_once($file);
				$c = 'gridphp_' . $m;
				$mod = new $c();// &new $c();

				//attach default mods
				if(!in_array($m, $this->defmods)){
					foreach($this->defmods as $dm)
						$mod->$dm = &$this->$dm;
				}
				
				$mod->name = $m;
				$mod->parent = &$this;

				$this->$m = &$mod;

				//调用默认初始化方法
				// if(method_exists($mod, '_Init_'))
				// if(!isset($this->_MOD_INITED[$c]) && method_exists($mod, '_Init_')){
				// 	$this->_MOD_INITED[$c] = 1;
				// 	$mod->_Init_();
				// }

			}else{
				$this->$m = new gridphp_undefined_class($m);
			}

		}

		return $this->$m;

	}

	/**
	* 创建类 loadClass
	* @param array $args 类名
	* @return class int 0 类文件不存在
	*/
	function &load($m, $c, $args){
		$file = GRIDPHP_INC_PATH . $m . '/' . $c . '.inc.php';
		if(file_exists($file)){
			require_once($file);
			$c = $m . '_' . $c;
			$class = new $c($args); //&new $c($args);
			//引用自身和默认加载模块
			$class->parent = &$this->$m;
			foreach($this->defmods as $dm)
				$class->$dm = &$this->$dm;
		}else{
			$class = new gridphp_undefined_class($m);
		}
		return $class;
	}

	/**
	* 添加HTTP任务队列
	*/
	function addRPC($conf, $data, &$result){
		$this->_RPC_TASKS[] = array(
			'conf'	=> $conf,
			'data'	=> $data,
			'result' => &$result
		);
		return count($this->_RPC_TASKS);
	}

	/**
	* 合并请求接口
	*/
	function callRPC(){
		if(!$this->_RPC_TASKS)
			return;

		//$this->utility->setTimerPoint('_rpc_merge');
		$this->_rpc_merge();
		//$this->debug->dump('_rpc_merge : ' . $this->utility->getTimerDiff('_rpc_merge') . 'ms', 101);

		$this->utility->setTimerPoint('_rpc_getdata');
		$rs = $this->_rpc_getdata();
		$this->debug->dump('_rpc_getdata : ' . $this->utility->getTimerDiff('_rpc_getdata') . 'ms', 101);

		//$this->utility->setTimerPoint('_rpc_process');
		$this->_rpc_process($rs);
		//$this->debug->dump('_rpc_process : ' . $this->utility->getTimerDiff('_rpc_process') . 'ms', 101);

		//END
		$this->_RPC_TASKS = array();
	}

	//合并请求数据
	private function _rpc_merge(){
		$rpc_merge = array();
		foreach($this->_RPC_TASKS as $i => $t){
			$conf = $t['conf'];
			if(GRIDPHP_RPC_MERGE_MODE)
				$key = md5($conf['host'] . $conf['name'] . $conf['port'] . $conf['uri'] . $conf['encode']);
			else
				$key = $i;

			$maxt = 0;
			if(isset($rpc_merge[$key]))
				$maxt = $rpc_merge[$key]['conf']['timeout'];
			$conf['timeout'] = ($maxt > $conf['timeout']) ? $maxt : $conf['timeout'];

			$rpc_merge[$key]['conf'] = $conf;
			$rpc_merge[$key]['data'][] = $t['data'];
			$rpc_merge[$key]['result'][] = &$this->_RPC_TASKS[$i]['result'];
			
		}
		$this->_RPC_TASKS = &$rpc_merge;
	}

	//批量取得数据
	private function _rpc_getdata(){
		$rs = array();
		$maxt = 0;
		foreach($this->_RPC_TASKS as $k => $t){
			$conf = $t['conf'];
			$maxt = ($maxt > $conf['timeout']) ? $maxt : $conf['timeout'];

			$data = array();
			$data['module'] = $t['data'][0]['module'];
			$data['function'] = $t['data'][0]['function'];
			$data['args'] = $t['data'][0]['args'];
			$data['encode'] = ($conf['encode']) ? $conf['encode'] : 'json';

			if($conf['encode'] == 'serialize'){
				$data['multidata'] = serialize($t['data']);
			}else{
				//识别参数对象类型
				$data['types'] = $this->utility->json->objtypes($t['data'][0]['args']);
				$data['types'] = $this->utility->json->encode($data['types']);
				$data['multidata'] = $this->utility->json->encode($t['data']);
			}

			//有效性校验
			$data['sign'] = $this->rpcsign($data);

			//请求来源
			$data['src'] = $this->getRequestSRC();

			//设置headers
			if($conf['name'])
				$this->http->setHeader('Host', $conf['name']);

			if(isset($conf['headers'])){
				if(isset($conf['headers']['Cookie']) && $conf['headers']['Cookie'] == 1 && isset($_SERVER['HTTP_COOKIE']))
					$conf['headers']['Cookie'] = $_SERVER['HTTP_COOKIE'];
				foreach($conf['headers'] as $h => $v)
					$this->http->setHeader($h, $v);
			}

			$modules = $functions = '';
			foreach($t['data'] as $i => $v){
				if($i > 0){
					$modules .= ',';
					$functions .= ',';
				}
				$modules .= $v['module'];
				$functions .= $v['function'];
			}
			$conf['uri'] .= '?module=' . $modules
						 . '&function=' . $functions
						 . '&encode=' . $data['encode']
						 . '&gz=' . $conf['gz']
						 . '&timeout=' . ($maxt / 1000 + 1);

			if($conf['gz']){
				if($conf['encode'] == 'serialize'){
					$data = serialize($data);
				}else{
					$data = json_encode($data);
				}
				$data = gzcompress($data);
			}

			$rs[$k] = &$this->http->post($conf['host'], $conf['port'], $conf['uri'], $data, GRIDPHP_RPC_NONBLOCK);
		}
		$this->http->sendRequest($maxt);
		if($maxt) $this->debug->dump($rs, 100);
		return $rs;
	}

	//处理返回数据
	private function _rpc_process(&$rs){

		foreach($this->_RPC_TASKS as $k => $t){

			if($rs[$k]['status'] == 200){
				$encode = $rs[$k]['headers']['DATA-ENCODEING'];
				$rs[$k]['response'] = trim($rs[$k]['response']);
				$this->utility->setTimerPoint('datadecode');
				if($encode == 'serialize'){
					$rs[$k] = unserialize($rs[$k]['response']);
				}else if($encode == 'json'){
					$rs[$k] = $this->utility->json->decode($rs[$k]['response']);
				}else{
					$rs[$k] = null;
				}
				$this->debug->dump('datadecode : ' . $this->utility->getTimerDiff('datadecode') . 'ms', 102);
				$this->debug->dump($rs[$k], 102);

				if($rs[$k]){

					$this->utility->loadC('array');
					if(is_object($rs[$k]) && array_key_exists('types', $rs[$k]) && array_key_exists('data', $rs[$k])){

						$data = $rs[$k]->data;
						//还原节点类型
						if(is_object($rs[$k]->types)){
							$types = (array) $rs[$k]->types;
							//$this->utility->json->recover_array(&$data, $types);
							$this->utility->json->recover_array($data, $types);
						}else if($encode == 'json'){
							//转成数据结构
							$data = $this->utility->array->object2array($data);
						}

						if(count($t['result']) > 1){
							for($i = 0; $i < count($this->_RPC_TASKS[$k]['result']); $i ++)
								$this->_RPC_TASKS[$k]['result'][$i] = $data[$i];
						}else{
							$this->_RPC_TASKS[$k]['result'][0] = $data;
						}

					//直接返回数据
					}else{
						if($encode == 'json'){
							//转成数据结构
							$data = $this->utility->array->object2array($rs[$k]);
						}else{
							//原结构返回
							$data = $rs[$k];
						}
						for($i = 0; $i < count($this->_RPC_TASKS[$k]['result']); $i ++)
							$this->_RPC_TASKS[$k]['result'][$i] = $data;
					}

				}else{
					//-1005服务端返回数据不合法
					for($i = 0; $i < count($this->_RPC_TASKS[$k]['result']); $i ++)
						$this->_RPC_TASKS[$k]['result'][$i] = array('status' => GRIDPHP_RPC_ERR_NO_PARSEDATA);
				}

			}else{
				//其它错误代码同http 403 404 500等
				for($i = 0; $i < count($this->_RPC_TASKS[$k]['result']); $i ++)
					$this->_RPC_TASKS[$k]['result'][$i] = array('status' => $rs[$k]['status']);
			}
		}

	}

	/**
	* RPC请求数据签名
	*/
	function rpcsign($data){
		$sign_key = md5($this->getConf('sign_key'));
		if(isset($data['multidata']))
			return md5($sign_key . substr($data['multidata'], 0, 512));
		else if(isset($data['module']) && isset($data['function']))
			return md5($sign_key . $data['module'] . $data['function'] . substr($data['args'], 0, 512));
		else
			return false;
	}

	/**
	* 命令行参数转成$_GET参数
	* 例: php test.php uid=1 type=2 将得到 $_GET['uid'] = 1 和 $_GET['type'] = 2
	*/
	private function parse_args(){
		if(isset($_SERVER['argv']))
		for($i = 1; $i < count($_SERVER['argv']); $i ++){
			preg_match('/^-?(\w+?)[:=](.+)$/', $_SERVER['argv'][$i], $a);
			if($a){
				$_GET[$a[1]] = $a[2];
				$_REQUEST[$a[1]] = $a[2];
			}
		}
	}

	/**
	* 获取配置信息
	*/
	function getConf(){
		$args = func_get_args();
		if(!$this->_CONFIG)
			$this->_CONFIG = require('conf/gridphp.conf.php');
		$conf = &$this->_CONFIG;
		foreach($args as $k)
			if(is_array($conf)){
				$conf = &$conf[$k];
			}else{
				$conf = null;
				break;
			}

		return $conf;
	}

	/**
	* 取服务器IP
	* @return string
	*/
	function getServerIP(){
		if(!$this->_SERVIP){
			$ip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : null;
			$file = '/sbin/ifconfig';
			if(!$ip && file_exists($file)){
				$ifcfg = `$file`;
				preg_match('/inet addr:(.*?) /', $ifcfg, $match);
				if($match) $ip = $match[1];
			}
			$file = '/etc/sysconfig/network-scripts/ifcfg-eth0';
			if(!$ip && file_exists($file)){
				//尝试读系统配置
				$ifcfg = file_get_contents($file);
				preg_match('/IPADDR=(.*)/', $ifcfg, $match);
				if($match) $ip = $match[1];
			}
			$this->_SERVIP = $ip;
		}
		return $this->_SERVIP;
	}

	/**
	* 取客户端IP
	* @return string
	*/
	function getClientIP(){
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if(isset($_SERVER['REMOTE_ADDR']))
			return $_SERVER['REMOTE_ADDR'];
		else
			return '';
	}

	/**
	* 获取当前服务器所处环境
	* @return string
	*/
	function getServerEnv(){
		if(!$this->_SERVENV)
			$this->_SERVENV = defined('GRIDPHP_SERVER_ENV') ? GRIDPHP_SERVER_ENV : $this->getConf('server_env', $this->getServerIP());
		return $this->_SERVENV;
	}

	/**
	* 获取当前请求URL
	* @return string
	*/
	function getRequestSRC(){
		if(!$this->_REQSRC){
			if(isset($_SERVER['HTTP_HOST'])){
				$this->_REQSRC = $_SERVER['HTTP_HOST'] . preg_replace('/[?#].*/', '', $_SERVER['REQUEST_URI']);
			}else{
				$this->_REQSRC = $this->getServerIP() . ':' . $_SERVER['PWD'] . '/' . $_SERVER['SCRIPT_NAME'];
			}
		}		
		return $this->_REQSRC;
	}

	static function autoload($classname){
		$GP = $GLOBALS['GRIDPHP'];
		foreach($GP->getConf('autoload_default') as $file) {
			require_once($file);
		}
		foreach($GP->getConf('autoload_libpath') as $path) {
			$file = $path . $classname . '.class.php';
			if(file_exists($file)){
				require($file);
				break;
			}
		}
	}
}

/**
* GridPHP模块基础类
* @package GRIDPHP
*/
class gridphp_module{
	var $_CONFIG = array();
	var $lazyInited = 0;
	var $name = '';

	/**
	* Lazy Initialization延迟初始化，只执行一次
	* @return void
	*/
	function lazyInit(){
		$backtrace = debug_backtrace();
		$trace = $backtrace[1];
		$fun = $trace['function']; //strtolower($trace['function']); //转小写与php4保持一致
		if($fun == '_callImplements'){
			$trace = $backtrace[2];
			$fun = $trace['function']; //strtolower($trace['function']); //转小写与php4保持一致
		}
		// $class = get_class($this); //$trace['object'] ? get_class($trace['object']) : $trace['class'];
		// $mod = substr($class, 4); //strtolower(substr($class, 4));
		$isrpc = $this->isRPC($fun);
		if(!$this->lazyInited){
			if(!$isrpc || !GRIDPHP_RPC_SWITCH){
				$this->lazyInited = 1;
				$this->_Init_();
				$this->loadC('implements');
			}
		}
	}

	/**
	* 加载类 loadClass
	* @param array $args 类名及创建参数
	* @return class
	*/
	function &loadC(){
		$args = func_get_args();
		if(!$args){
			$trace = debug_backtrace();
			$args = $trace[1]['args'];
		}
		$c = array_shift($args);
		$a = null; //alias别名
		if(is_array($c)){
			$k = array_keys($c);
			$a = $c[$k[0]];
			$c = $k[0];
		}

		$r = ($a) ? $a : $c;
		if(!isset($this->$r)){
			$this->$r = &$this->parent->load($this->name, $c, $args);
			//调用初始化方法
			if(method_exists($this->$r, '_Init_') ){
				call_user_func_array(array(&$this->$r, '_Init_'), $args);
			}
		}
		return $this->$r;
	}

	/**
	* 动态加载指定模块
	* @param string $m 模块名称
	* @return void
	*/
	function &mod($m){
		return $this->$m = &$this->parent->mod($m);
	}

	/**
	* 设置使用RPC远程调用
	* @param string $fun 函数数
	* @param int $flag 1启用 0禁用
	* @param int $timeout HTTP接口超时设置
	* @return int 1成功 0失败
	*/
	function setRPC($fun, $flag, $timeout = null){
		//$fun = strtolower($fun); //兼容php4中debug_backtrace()获取的function名称为小写
		if(
			$this->getConf('RPC_CONFIG', $fun)
		||
			$this->getConf('RPC_CONFIG', 'default')
		){
			$this->_CONFIG['RPC_CONFIG'][$fun]['use'] = $flag;
			if($timeout)
				$this->_CONFIG['RPC_CONFIG'][$fun]['timeout'] = $timeout;
			return 1;
		}else{
			return 0;
		}
	}

	/**
	* 返回是否使用RPC
	* @return int
	*/
	function isRPC($fun = null){
		if(!$fun){
			$trace = debug_backtrace();
			$trace = $trace[1];
			$fun = $trace['function'];
		}
		//$fun = strtolower($fun); //兼容php4中debug_backtrace()获取的function名称为小写
		$rs = $this->getConf('RPC_CONFIG', $fun, 'use');
		if(is_null($rs))
			$rs = $this->getConf('RPC_CONFIG', 'default', 'use');
		return $rs;
	}

	/**
	* 使用RPC远程调用接口
	* @return void
	*/
	function &doRPC($fun = null, $args = null){

		$this->utility->loadC('json');

		$trace = debug_backtrace();
		$trace = $trace[1];
		$fun = (!$fun) ? $trace['function'] : $fun;
		//$fun = strtolower($fun); //兼容php4中debug_backtrace()获取的function名称为小写
		$args = (!$args) ? $trace['args'] : $args;
		//$class = get_class($this); //$trace['object'] ? get_class($trace['object']) : $trace['class'];
		$mod = $this->name; //substr($class, 4); //strtolower(substr($class, 4));

		$conf = null;
		if($this->getConf('RPC_CONFIG', $fun, 'host'))
			$conf = $this->getConf('RPC_CONFIG', $fun);
		else{
			$conf = $this->getConf('RPC_CONFIG', 'default');
			if($this->getConf('RPC_CONFIG', $fun, 'timeout'))
				$conf['timeout'] = $this->getConf('RPC_CONFIG',$fun, 'timeout');
		}
		$conf['encode'] = isset($conf['encode']) ? $conf['encode'] : 'json';
		$conf['gz'] = isset($conf['gz']) ? $conf['gz'] : 0;

		$data['module'] = $mod;
		$data['function'] = $fun;
		$conf['encode'] = isset($conf['encode']) ? $conf['encode'] : 'json';
		$conf['gz'] = isset($conf['gz']) ? $conf['gz'] : 0;
		$data['query'] = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';

		//远程调用URL来源
		$this->setRPCShared('reffer', $this->parent->getRequestSRC());

		if($conf['encode'] == 'serialize'){
			$data['args'] = serialize($args);
			$data['shared'] = serialize($this->getRPCShared());
		}else{
			$data['args'] = $this->utility->json->encode($args);
			$data['shared'] = $this->utility->json->encode($this->getRPCShared());
			//识别参数对象类型
			$data['types'] = $this->utility->json->objtypes($args);
			$data['types'] = $this->utility->json->encode($data['types']);
		}

		switch($this->isRPC($fun)){
			//合并请求接口
			case 2:
				//-1004返回表示接口未请求完成
				$result = array('status' => GRIDPHP_RPC_ERR_NO_CONNECT);
				$this->addRPC($conf, $data, $result);
				return $result;
			break;

			case 1: //实时请求
			case 3: //已在队列里的请求一起发送
			default:
				$result = array('status' => GRIDPHP_RPC_ERR_NO_CONNECT);
				$this->addRPC($conf, $data, $result);
				$this->callRPC();
				return $result;
			break;
		}

	}

	/**
	* 添加HTTP任务队列
	* @return int
	*/
	function addRPC(&$conf, &$data, &$result){
		return $this->parent->addRPC($conf, $data, $result);
	}

	/**
	* 合并远程调用
	* @return mixed
	*/
	function callRPC(){
		return $this->parent->callRPC();
	}

	/**
	* 获取当前服务器所处环境
	* @return string
	*/
	function getServerEnv(){
		return $this->parent->getServerEnv();
	}

	/**
	* 获取当前请求URL
	* @return string
	*/
	function getRequestSRC(){
		return $this->parent->getRequestSRC();
	}

	/**
	* 获取配置信息同级合只适合数据键值
	* @return mixed
	*/
	function &getConfMerge(){
		$args = func_get_args();
		$keys = array_pop($args);
		$values = array();
		if(is_array($keys)){
			foreach($keys as $k){
				$args[] = $k;
				$conf = call_user_func_array(array($this, 'getConf'), $args);
				if(!is_array($conf)){ $values = array(); break;} //任何一个合并的key值不为数组时返回空
				$values = array_merge($values, $conf);
				array_pop($args);
			}
		}
		return $values;
	}

	/**
	* 获取配置信息
	* @return mixed
	*/
	function &getConf(){
		$args = func_get_args();

		if(!$this->_CONFIG){
			//加载配置文件
			$file = GRIDPHP_CONF_PATH . $this->name . '.conf.php';
			$load = file_exists($file);
			if($load)
				$this->_CONFIG = include($file);

			//加载不同环境配置
			if($this->getServerEnv()){
				$file = GRIDPHP_CONF_PATH . $this->getServerEnv() . '/' . $this->name . '.conf.php';
				$load = file_exists($file);
				if($load){
					$this->parent->utility->loadC('array');
					$config = include($file);
					$this->parent->utility->array->merge($this->_CONFIG, $config);
				}
			}
		}

		$conf = &$this->_CONFIG;
		foreach($args as $k)
			if(is_array($conf)){
				$conf = &$conf[$k];
			}else{
				$conf = null;
				break;
			}

		if($conf && is_string($conf)){
			switch(substr($conf, 0, 1)){
				case '@': //动态包含配置文件
					$file = substr($conf, 1);
					$file = preg_replace('/[^\w]/', '', $file);
					$file = GRIDPHP_CONFINC_PATH . $this->name . '/' . $file . '.conf.php';
					$load = file_exists($file);
					if($load) $conf = include($file);

				break;
				case '&': //同级配置引用
					$ref = substr($conf, 1);
					array_pop($args);
					$args[] = $ref;
					$conf = &$this->_CONFIG;
					foreach($args as $k)
						if(is_array($conf)){
							$conf = &$conf[$k];
						}else{
							$conf = null;
							break;
						}
				break;
			}
		}

		return $conf;
	}

	/**
	* 修改配置
	* @return void
	*/
	function setConf(){
		$args = func_get_args();
		$conf = &$this->getConf();
		$num = count($args) - 1;
		$value = $args[$num];
		for($i = 0; $i < $num; $i ++){
			$k = $args[$i];
			if(!isset($conf[$k]))
				$conf[$k] = array();
			$conf = &$conf[$k];
		}
		$conf = $value;
		return $value;
	}

	/**
	* 设置远程共享数据
	* @return void
	*/
	function setRPCShared(){
		$args = func_get_args();
		array_unshift($args, 'RPC_SHARED');
		return call_user_func_array(array($this, 'setConf'), $args);
	}

	/**
	* 设置远程共享数据
	* @return mixed
	*/
	function getRPCShared(){
		$args = func_get_args();
		array_unshift($args, 'RPC_SHARED');
		return call_user_func_array(array($this, 'getConf'), $args);
	}

	/**
	* 手动设置Cache
	* @return void
	*/
	function setCacheOn($func, $conf = 1){
		return $this->setConf('CACHE_CONFIG', $func, $conf);
	}

	/**
	* 手动关闭Cache
	* @return void
	*/
	function setCacheOff($func){
		return $this->setConf('CACHE_CONFIG', $func, 0);
	}

	/**
	* 数据调用cache
	* @return mixed
	*/
	function getCache($func, $args){
		$rt = null;
		$conf = $this->_get_cache_conf($func);
		if($conf){
			//用于识别cache是否该更新的计数器
			$uniqkey = $this->_get_uniqkey_count($conf, $args);
			$key_sign = $this->name . '|' . $func . '|' . $uniqkey;
			foreach($args as $v){
				if(is_numeric($v)) $v = (string) $v;
				$key_sign .= '|' . var_export($v, 1);
			}
			$key = GRIDPHP_FUNCALL_CACHE . md5($key_sign);
			$this->utility->setTimerPoint('getcache');
			$memc = $this->memcd->loadMemc($conf['cache']);
			$rt = $memc->get($key);
			//返回数据转型
			if($rt !== false && $rt !== null){
				switch($conf['return']){
					case 'int':
						$rt = (int) $rt;
						break;
					case 'float':
						$rt = (float) $rt;
						break;
					case 'bool':
						$rt = (bool) $rt;
						break;
					case 'string':
						$rt = (string) $rt;
						break;
				}
			}
			$ms = $this->utility->getTimerDiff('getcache');
			$this->debug->dump("Call: {$this->name}->{$func} KeySign: {$key_sign} Get Cache({$ms}ms): key => {$key}\n value => " . var_export($rt, 1), 88);
		}
		return $rt;
	}

	/**
	* 执行结果写入cache
	* @return void
	*/
	function setCache($func, $args, $data){
		$rt = null;
		$conf = $this->_get_cache_conf($func);
		if($conf){
			//用于识别cache是否该更新的计数器
			$uniqkey = $this->_get_uniqkey_count($conf, $args);
			$memc = $this->memcd->loadMemc($conf['cache']);

			$argstr = '';
			foreach($args as $v){
				if(is_numeric($v)) $v = (string) $v;
				$argstr .= '|' . var_export($v, 1);
			}

			//保存当前Cache
			$key_sign = $this->name . '|' . $func . '|' . $uniqkey . $argstr;
			$key = GRIDPHP_FUNCALL_CACHE . md5($key_sign);
			$rt = $memc->set($key, $data, $conf['timer']);

			//删除之前的Cache
			for($i = $uniqkey - 1; $i >= 0 && ($uniqkey - $i) <= 20; $i --){
				$key_sign_previous = $this->name . '|' . $func . '|' . $i . $argstr;
				$key = GRIDPHP_FUNCALL_CACHE . md5($key_sign_previous);
				$rt = $memc->delete($key);
			}

			$this->debug->dump("Call: {$this->name}->{$func} Rekey: {$uniqkey} KeySign: {$key_sign} Set Cache: key => {$key} timer: {$conf['timer']}\nvalue => " . var_export($data, 1), 88);
		}
		return $rt;
	}

	/**
	* 删除cache
	* @return void
	*/
	function delCache($func, $args){
		$rt = null;
		$conf = $this->_get_cache_conf($func);
		if($conf){
			//用于识别cache是否该更新的计数器
			$uniqkey = $this->_get_uniqkey_count($conf, $args);
			$key_sign = $this->name . '|' . $func . '|' . $uniqkey;
			foreach($args as $v){
				if(is_numeric($v)) $v = (string) $v;
				$key_sign .= '|' . var_export($v, 1);
			}
			$key = GRIDPHP_FUNCALL_CACHE . md5($key_sign);
			$memc = $this->memcd->loadMemc($conf['cache']);
			$rt = $memc->delete($key);
			$this->debug->dump("Call {$func} {$key_sign} Delete Cache: key => {$key}\n ", 88);
		}
		return $rt;
	}

	/**
	* 更新Cache标记
	* @return void
	*/
	function reCache($func, $args){
		$rt = null;
		$conf = $this->_get_cache_conf($func);
		if($conf){
			$key_sign = $this->name . '|' . $conf['func'];
			foreach($conf['uniqkey'] as $i => $v){
				if(is_numeric($args[$v])) $args[$v] = (string) $args[$v];
				$key_sign .= '|' . var_export($args[$v], 1);
			}
			$key = GRIDPHP_UNIQKEY_CACHE . md5($key_sign);
			$memc = $this->memcd->loadMemc($conf['cache']);
			$rt = $memc->increment($key, 1, $conf['timer']);
			$this->debug->dump("Call: {$this->name}->{$func} KeySign: {$key_sign} ReCache: key => {$key}\n value => {$rt}", 88);
			//延迟更新队列
			if(GRIDPHP_UNIQKEY_DELAY_DEF > 0){
				$delay = time() + ($conf['delay'] ? $conf['delay'] : GRIDPHP_UNIQKEY_DELAY_DEF);
				$memc->listPush(GRIDPHP_UNIQKEY_DELAY, array($key, $delay, $conf['timer']), 0, GRIDPHP_UNIQKEY_DELAY_TIMER);
			}
		}
		return $rt;
	}

	/**
	* 返回模块Cache配置
	* return mixed
	*/
	private function _get_cache_conf($func){
		$defc = $this->getConf('CACHE_CONFIG', 'default');
		$conf = $this->getConf('CACHE_CONFIG', $func);
		if($conf){
			if(!is_array($conf)) {
				$conf = $defc;
				$conf['func'] = 'default';
			}
			if(!isset($conf['enalble'])) $conf['enalble'] = $defc['enalble'];
			if($conf['enalble']){
				if(!isset($conf['uniqkey'])) $conf['uniqkey'] = $defc['uniqkey'];
				if(!isset($conf['cache'])) $conf['cache'] = $defc['cache'];
				if(!isset($conf['timer'])) $conf['timer'] = $defc['timer'];
				if(!isset($conf['func'])) $conf['func'] = $func;
			}else{
				$conf = 0;
			}
		}
		if($conf && !is_array($conf['uniqkey']))
			$conf['uniqkey'] = array($conf['uniqkey']);
		return $conf;
	}

	/**
	* 返回cache是否该更新的计数器
	* return int
	*/
	private function _get_uniqkey_count($conf, $args){
		$key_sign = $this->name . '|' . $conf['func'];
		foreach($conf['uniqkey'] as $i => $v){
			if(is_numeric($v)){
				if(is_numeric($args[$v]))
					$args[$v] = (string) $args[$v];
				else
					$args[$v] = $args[$v];
				$key_sign .= '|' . var_export($args[$v], 1);
			}else{
				$key_sign .= '|' . $v;
			}
		}
		$key = GRIDPHP_UNIQKEY_CACHE . md5($key_sign);
		$memc = $this->memcd->loadMemc($conf['cache']);
		$count = intval($memc->get($key));
		$this->debug->dump("Call: {$this->name}->{$conf['func']} KeySign: {$key_sign} uniqkey => {$key} value => {$count}", 88);
		return $count;
	}

	//统一接口返回方法
	function &_callImplements(){
		$trace = debug_backtrace();
		$trace = $trace[1];
		//$class = get_class($this); //$trace['object'] ? get_class($trace['object']) : $trace['class'];
		$mod = $this->name; //substr($class, 4); //strtolower(substr($class, 4));
		$func = $trace['function'];
		//复制传参而不是引用！ 
		$args = array(); foreach($trace['args'] as $i => $v) $args[] = $v;
		//debug_backtrace传递进来的$trace['args']是引用，func内部修改破坏参数后会影响setCache的key值与getCache不一致

		$object = &$this->parent->$mod;
		$object->lazyInit();
		$isrpc = $object->isRPC($func);

		//记录接口调用时间
		$this->utility->setTimerPoint('callog');

		//检查本地缓存
		$cache = $object->getCache($func, $args);
		if($cache !== false && $cache !== null){
			if(isset($this->log))
				$this->log->callog($mod, $func, 'c'); //记录请求日志
			//直接返回本地缓存
			return $cache;

		}else if((GRIDPHP_RPC_SWITCH && $isrpc) || $isrpc == 10){
			//远程调用
			$ret = &$this->doRPC($func, $args);
			if(isset($this->log))
				$this->log->callog($mod, $func, 'h'); //记录请求日志
			//异步请求或远程出错直接返回
			if(is_array($ret) && isset($ret['status']) && $ret['status'])
				return $ret;

		// }else if(method_exists($object->implements, $func)){
		}else{
			//本地调用
			$ret = call_user_func_array(array($object->implements, $func), $args);
			if(isset($this->log))
				$this->log->callog($mod, $func); //记录请求日志

		}
		// else{
		// 	return "method: '{$mod}->{$func}()' not exists";
		// }

		//保存到Cache
		if($cache !== null)
			$object->setCache($func, $args, $ret);

		return $ret;
	}

	/**
	* 规范化取参数值
	* @param $name 参数名
	* @param $type 数据类型 intval floatval string email ...
	* @param $default 默认值
	* @param $min 最小值/长度
	* @param $max 最大值/长度
	* @param $method get/post/request
	* @return mixed
	*/
	function getParam($name, $type = null, $default = null, $min = null, $max = null, $method = null){
		$this->request = isset($this->request) ? $this->request : $this->utility->loadC('request');
		return $this->request->getParam($name, $type, $default, $min, $max, $method);
	}

	/**
	* 客户端信息
	* @return mixed
	*/
	function getClientInfo(){
		$info = array(
			'type'	=> '',
			'ver'	=> ''
		);
		$agent = $_SERVER['HTTP_USER_AGENT'];
		if(strstr($agent, 'android'))
			$info['type'] = 'android';
		preg_match('/ver:(.+?)\s/', $agent, $match);
		if($match)
			$info['ver'] = $match[1];
		return $info;
	}

	/**
	* 组织返回ajax数据格式
	* @param $retcode 状态码
	* @param $retmean 状态描述
	* @param $data 实际数据
	* @return array
	*/
	function ajaxData($retcode, $retmean = '', $data = ''){
		return array(
			'retcode'	=> intval($retcode),
			'retmean'	=> $retmean,
			'data'		=> $data
		);
	}

	/**
	* AJAX调用方法
	* @return mixed
	*/
	function AJAX($args){
		$mod = $this->name;
		$func = $args['func'];
		$this->utility->setTimerPoint('ajax');

		if(file_exists($ajax = GRIDPHP_AJAX_PATH . $mod . '.ajax.class.php')){
			require_once($ajax);
			$ajax = $mod . '_ajax';
			$ajax = new $ajax();
			$ajax->name = $mod;
			$ajax->parent = &$this->parent;
			//attach default mods
			foreach($this->parent->defmods as $dm)
				$ajax->$dm = &$this->parent->$dm;
			//调用默认初始化方法
			if(method_exists($ajax, '_Init_'))
				$ajax->_Init_($args);
			//执行func
			if(method_exists($ajax, $func)){
				$ret = $ajax->$func();
			}else{
				//返回错误代码
				$ret = array (
					'retcode' => GRIDPHP_AJAX_ERR_NOT_FUNC,
					'retmean' => 'GRIDPHP_AJAX_ERR_NOT_FUNC' 
				);
			}

		}else{
			$ret = array(
				'retcode'	=> GRIDPHP_AJAX_ERR_NOT_FOUND,
				'content'	=> 'GRIDPHP_AJAX_ERR_NOT_FOUND'
			);
		}
		$this->debug->dump($ret, 77);

		//记录接口调用时间
		$time = $this->utility->getTimerDiff('ajax');
		$method = $mod . '->' . $func;
		if($time <= 1000){
			$time = ceil($time / 100) * 100;
			$time .= 'ms';
		}else if($time <= 10000){
			$time = ceil($time / 1000);
			$time .= 's';
		}else{
			$time = '10s+';
		}
		// $memc = $this->memcd->loadMemc('callfuncount');
		// $memc->listPush('ajax_request_time', $method . '[' . $time . ']', 1, GRIDPHP_TODAY_TIMER);

		return $ret;
	}

	/**
	* 模块调试方法
	* @return void
	*/
	function _DEBUG(){
		$trace = debug_backtrace();
		$trace = $trace[0];
		// $class = get_class($this); //$trace['object'] ? get_class($trace['object']) : $trace['class'];
		$mod = $this->name; //substr($class, 4); //strtolower(substr($class, 4));
		$dbg = GRIDPHP_DBG_PATH . $mod . '.dbg.php';
		if(file_exists($dbg))
			include(GRIDPHP_DBG_PATH . $mod . '.dbg.php');
		else
			print "$dbg : file not found!";
	}

	/**
	* 待重载的初始化方法
	* @return void
	*/
	function _Init_(){}

	/**
	* 方法未定义
	* @return void|exception
	*/
    function __call($name, $args) {
    	//自动生成的delCache_func方法
    	if(substr($name, 0, 9) == 'delCache_'){
    		$name = substr($name, 9);
    		return $this->delCache($name, $args);
    	}
    	throw new Exception("Call to undefined method {$this->name}->{$name}()");
    	return false;
	}

}

/**
* 功能实现基础类
*/
class gridphp_implements{

	/**
	* 动态加载指定模块
	* @param string $m 模块名称
	* @return void
	*/
	function &mod($m){
		return $this->$m = $this->parent->mod($m);
	}

	/**
	* 加载类 loadClass
	* @param array $args 类名及创建参数
	* @return class
	*/
	function &loadC(){
		$args = func_get_args();
		$c = array_shift($args);
		$a = null;
		if(is_array($c)){
			$k = array_keys($c);
			$a = $c[$k[0]];
			$c = $k[0];
		}
		$r = ($a) ? $a : $c;
		return $this->$r = &$this->parent->loadC();
	}

	/**
	* 获取配置信息同级合只适合数据键值
	* @return mixed
	*/
	function getConfMerge(){
		$args = func_get_args();
		return call_user_func_array(array(&$this->parent, 'getConfMerge'), $args);
	}

	/**
	* 获取配置信息
	* @return mixed
	*/
	function getConf(){
		$args = func_get_args();
		return call_user_func_array(array(&$this->parent, 'getConf'), $args);
	}

	/**
	* 设置远程共享数据
	* @return void
	*/
	function setRPCShared(){
		$args = func_get_args();
		return call_user_func_array(array(&$this->parent, 'setRPCShared'), $args);
	}

	/**
	* 返回远程共享数据
	* @return mixed
	*/
	function getRPCShared(){
		$args = func_get_args();
		return call_user_func_array(array(&$this->parent, 'getRPCShared'), $args);
	}

	/**
	* 手动设置Cache
	* @return void
	*/
	function setCacheOn($func, $conf){
		return $this->parent->setCacheOn($func, $conf);
	}
	
	/**
	* 手动关闭Cache
	* @return void
	*/
	function setCacheOff($func){
		return $this->parent->setCacheOff($func);
	}
	
	/**
	* 更新Cache标记
	* @return void
	*/
	function reCache($func = null, $args = null){
		if(!$func || !$args){
			$trace = debug_backtrace();
			$trace = $trace[1];
			if(!$func) $func = $trace['function'];
			if(!$args) $args = $trace['args'];
		}
		return $this->parent->reCache($func, $args);
	}

	/**
	* 执行结果写入cache
	* @return void
	*/
	function setCache($func = null, $args = null, $data = null){
		if(!$func || !$args || !$data){
			$trace = debug_backtrace();
			$trace = $trace[1];
			if(!$func) $func = $trace['function'];
			if(!$args) $args = $trace['args'];
			if(!$data) $data = $trace['data'];
		}
		return $this->parent->setCache($func, $args, $data);
	}

	/**
	* delete cache
	* @return void
	*/
	function delCache($func, $args){
		return $this->parent->delCache($func, $args);
	}

	/**
	* 方法未定义
	* @return void|exception
	*/
    function __call($name, $arguments) {
    	throw new Exception("Call to undefined method {$name}()");
    	return false;
	}

}

/**
* 加载到未识别的类
* @return void|exception
*/
class gridphp_undefined_class{
	var $_class;
	function gridphp_undefined_class($class){
		$this->_class = $class;
	}
	/**
	* 方法未定义
	* @return void|exception
	*/
    function __call($name, $args) {
    	throw new Exception("Call to undefined method {$this->_class}->{$name}()");
    	return false;
	}
}

//全局引用
$GLOBALS['GRIDPHP'] = new GRIDPHP(); //&new GRIDPHP(); < php5.3

?>