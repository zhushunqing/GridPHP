<?php
/**
* GridPHP 通用工具加载类
* @author ZhuShunqing
*/
class gridphp_utility extends gridphp_module{

	var $timerCounter = array();
	
	/**
	* 设置计时器记录点（记录当前系统毫秒）
	* @param int $p 记录点
	* @return void
	*/
	public function setTimerPoint($p){
		$this->timerCounter[$p] = $this->getMsec();
	}

	/**
	* 取距上一次记录点时间差值
	* @param int $p 记录点
	* @return int 毫秒
	*/
	public function getTimerDiff($p){
		return isset($this->timerCounter[$p]) ? number_format($this->getMsec() - $this->timerCounter[$p], 2, '.', '') : 0;
	}
	
	/**
	* 得到当前毫秒(自2011-01-01起)
	* @return float MilliSecond
	*/
	public function getMsec(){
		list($usec, $sec) = explode(" ",microtime()); 
		return ( ((float)$sec - 1293811200) + (float)$usec) * 1000; //1293811200 = strtotime('2011-01-01')
	}

}