<?php
/**
* GridPHP Log基础类
* @author ZhuShunqing
*/
class gridphp_log extends gridphp_module{

	/**
	* 记录接口调用
	* @param $m 模块
	* @param $f 方法
	* @param $t 类型 c => cache, s => server, h => http
	* @return void
	*/
	public function callog($m, $f, $t = ''){

		//方法调用计数
		$method = $m . '->' . $f;
		//页面来源
		$src = $this->getRequestSRC();
		//Call Type, '' for Local
		if($t) $t = '_' . $t;

		//Server side
		if($this->getServerEnv() == 'server'){
			$src = isset($_POST['src']) ? $_POST['src'] : 'script';
			$t .= '_s';
		}

		//记录接口调用时间
		$timer = $this->utility->getTimerDiff('callog');
		if($timer <= 100){
			$timer = ceil($timer / 10) * 10;
		}else if($timer <= 1000){
			$timer = ceil($timer / 100) * 100;
		}else if($timer > 1000){
			$timer = 1000;
		}
		$timer .= 'ms';

		if(!in_array($m, $this->parent->getConf('default_modules'))){
			// $memc = $this->memcd->loadMemc('callfuncount');
			// $memc->listPush('gridphp_call_list' . $t, $method, 1, GRIDPHP_TODAY_TIMER);
			// $memc->listPush('gridphp_call_time' . $t, $method . '[' . $timer . ']', 1, GRIDPHP_TODAY_TIMER);
			// $memc->listPush('gridphp_call_' . $method . $t, $src, 1, GRIDPHP_TODAY_TIMER);
			// $memc->listPush('gridphp_src_list' . $t, $src, 1, GRIDPHP_TODAY_TIMER);
			$this->debug->dump("callog GRIDPHP->{$m}->{$f}() Memckey: {$method} Type:{$t} -> $src", 99);
		}

	}

	/**
	* 打上统计标签
	* @param string $class 类别
	* @param string $tag 标签
	* @param string $uniq 唯一性标识
	* @return bool, int
	*/
	public function checkpoint($class, $tag, $uniq = null){
		if($class && $tag){
			$key = $class . '-' . $tag;
			$memc = $this->memcd->loadMemc('checkpoint');

			if($uniq){
				$uniq = 'gridphp_checkpoint_uniq_' . md5($key . '-' . $uniq);
				$get = $memc->get($uniq);
				if($get){
					return true;
				}else{
					$memc->set($uniq, 1, GRIDPHP_TODAY_TIMER);
				}
			}

			return $memc->listPush('gridphp_checkpoint', $key, 1, GRIDPHP_TODAY_TIMER);
		}else{
			return false;
		}
	}

	/**
	* 打上统计标签（按配置代码）
	* @param int $code 配置代码
	* @param string $uniq 唯一性标识
	* @param string $form 来源
	* @return bool
	*/
	public function checkpoint_code($code, $uniq = false, $from = false){
		$conf = $this->getConf('CHECKPOINT', $code);
		$from = strtoupper($from);
		if($conf){
			if($from) $conf[1] .= '-' . $from;
			$uniq = $conf[2] ? $uniq : false; //根据配置是否按传值排重
			return $this->checkpoint($code . '-' . $conf[0], $conf[1], $uniq);
		}else{
			return false;
		}
	}

	/**
	* 取出checkpoint的计数值
	*/
	public function getpoint($class, $tag){
		if($class && $tag){
			$key = $class . '-' . $tag;
			$memc = $this->memcd->loadMemc('checkpoint');
			return $memc->listValueCount('gridphp_checkpoint', $key);
		}else{
			return false;
		}
	}

	/**
	* 取出checkpoint_code的计数值
	* @param int $code 配置代码
	* @param string $form 来源
	*/
	public function getpoint_code($code, $from = ''){
		$conf = $this->getConf('CHECKPOINT', $code);
		$from = strtoupper($from);
		if($conf){
			if($from) $conf[1] .= '-' . $from;
			return $this->getpoint($code . '-' . $conf[0], $conf[1]);
		}else{
			return false;
		}
	}
	
	/**
	* 直接设置统计标签数值
	* @param string $class 类别
	* @param string $tag 标签
	* @return int 数值
	*/
	public function countpoint($class, $tag, $numb){
		if($class && $tag){
			$numb = intval($numb);
			$key = $class . '-' . $tag;
			$memc = $this->memcd->loadMemc('checkpoint');
			$key = 'gridphp_checkpoint_' . md5($key);
			return $memc->set($key, $numb);
		}else{
			return false;
		}
	}
	
	/**
	 * 添加一条tracelog
	 * @param array $tlog
	 * @return boolean
	 */
	public function tracelog($tlog){
		if(is_array($tlog)){
			$tlog['timer'] = time();
			$tlog = json_encode($tlog);
		}
		$memc = $this->memcd->loadMemc('tracelog');
		return $memc->listPush('tracelog', $tlog, 0, 3600);
	}

	/**
	* 取出1条tracelog
	*/
	public function get_tracelog(){
		$memc = $this->memcd->loadMemc('tracelog');
		$tlog = $memc->listShift('tracelog');
		if($tlog){
			$json = @json_decode($tlog, 1);
			if($json) $tlog = $json;
		}
		return $tlog;
	}

	/**
	* 写入syslog
	*/
	public function syslog($info = '', $level = 1){
		$trace = debug_backtrace();
		$trace = $trace[$level];
		$log = $_SERVER['SERVER_ADDR'] . "\t" . $_SERVER['REQUEST_URI'] . "\t" . $trace['class'] . "\t" . $trace['function'] . "\t" . $info . "\t" . var_export($trace['args'], 1);
		openlog('JIAYUAN', LOG_PID, LOG_LOCAL5);
        syslog(LOG_NOTICE, $log);
        closelog();
	}

	/**
	* 写入日志文件
	*/
	public function writelog($file, $info){
		if(is_array($info)){
			$string = '';
			foreach ($info as $k => $v)
				$string .= $k . ':' . $v . "\t";
			$info = $string;
		}
		if(substr($info, -1) != "\n") $info .= "\n";
		if(@is_dir(GRIDPHP_ERROR_PATH)){
			return file_put_contents(GRIDPHP_ERROR_PATH . $file, $info, FILE_APPEND);
		//在SAE上记录日志
		}else if(function_exists('sae_debug')){
			sae_debug($info);
		}else{
			return false;
		}
	}

	/*
	 * 调用报警接口
	 * $id 后台得到的id
	 * $config["content"] 报警内容
	 * $config["value"]=array("v1"=>11,"v2"=>22); 传递给服务器的数值
	 */
	public function alarm_interface($id, $config=array()){
		$id = intval($id);
		if ($id <= 0) return false;
		$para = serialize($config);
		$url = sprintf($this->getConf('WARNING_API'), $id, $para, 3);
		$this->http->getUrl($url);
		return true;
	}

}

?>