<?php
/**
* GridPHP 调试信息工具类
* @author ZhuShunqing
*/
class gridphp_debug extends gridphp_module{

	var $debug = 0, $count = 0, $level = 0;

	function _Construct_Init_(){
		$this->debug = (
			//只在本地和测试机上生效
			in_array($this->getServerEnv(), array('', 'server', 'local', 'dev', 'sae'))
		&&
			isset($_GET['DEBUG'])
		) ? 1 : 0;

		$this->level = isset($_GET['DEBUG']) ? intval($_GET['DEBUG']) : 0;
	}

	//是否测试环境
	function isDebug(){
		return $this->debug;
	}

	function setDebug($l){
		if($l){
			$this->debug = true;
			$this->level = $l;
		}else{
			$this->debug = false;
		}
	}

	/**
	* 打印调试信息，只在URL参数中含有"DEBUG"，并且在测试机环境时才生效
	* @param object $v value 输出调试变量
	* @param int $l level 调试级别
	* @param int $d depth 打印函数调用信息debug_backtrace深度
	* @return void
	*/
	function dump($v, $l = 0, $d = 0){
		if($this->debug && $this->level == $l){
			$trace = debug_backtrace();
			$info = "\n<!--\nDEBUG " . (++ $this->count) . ' ' . $this->utility->getTimerDiff('debug') . "ms\n";
			$info .= 'file: ' . $trace[0]['file']
				. ' line: ' . $trace[0]['line']
				. "\n";
			for($i = 1; $i <= $d && $i < count($trace); $i ++)
				$info .= 'file: ' . $trace[$i]['file']
					. ' line: ' . $trace[$i]['line']
					. "\n"
					. $trace[$i]['class'] . '->' . $trace[$i]['function']
					. '(' . var_export($trace[$i]['args'], 1) . ')'
					. "\n\n";
			$info .= "--------------------------------------------------------------------\n"
				. stripslashes(var_export($v, 1))
				. "\n--------------------------------------------------------------------\n"
				. "-->\n";

			print $info;
			$this->utility->setTimerPoint('debug');
		}
	}

}

?>