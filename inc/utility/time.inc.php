<?php
/**
* GridPHP 时间工具类
* @author ZhuShunqing
*/
class utility_time{

	var $timerCounter = array();
	
	/**
	* 设置计时器记录点（记录当前系统毫秒）
	* @param int $p 记录点
	* @return void
	*/
	function setTimerPoint($p){
		$this->timerCounter[$p] = $this->getMsec();
	}

	/**
	* 取距上一次记录点时间差值
	* @param int $p 记录点
	* @return int 毫秒
	*/
	function getTimerDiff($p){
		return intval($this->getMsec() - $this->timerCounter[$p]);
	}
	
	/**
	* 得到当前毫秒(自2011-01-01起)
	* @return float MilliSecond
	*/
	function getMsec(){
		list($usec, $sec) = explode(" ",microtime()); 
		return ( ((float)$sec - 1293811200) + (float)$usec) * 1000; //1293811200 = strtotime('2011-01-01')
	}

	/**
	 * 计算时间轴显示
	 * @param string $time 要显示的时间戳
	 * @return string 显示的时间轴
	 */
	function tranTime($time) { 
		$str='';  //返回值
		$time=intval($time);
		if($time <= 0){
			return $str;
		}
		$diff = time() - $time; 
		if($diff < 5){
			$str = '刚刚';
		}elseif ($diff < 60) { 
		    $str = $diff.'秒前'; 
		}elseif ($diff < 3600) { 
		    $min = floor($diff/60); 
		    $str = $min.'分钟前'; 
		}elseif (date('Y-m-d') == date('Y-m-d',$time)) {
			$str='今天 '.date("G:i",$time);
		}else { 
		    $str = date("Y") == date("Y",$time) ? date("n月j日 G:i",$time):date("Y-n-j G:i",$time); 
		} 
		return $str; 
	} 

	/**
	 * 判断平年闰年
	 * @param int $year 年份
	 * @return bool 闰年返回true，否者返回false
	 */
	function leap_year($year){
	    if ($year%4==0 && ($year%100!=0 || $year%400==0)){
	        return true;
	    }else{
	        return false;
	    }
	} 

	/**
	 * 计算某年某月天数
	 * @param int $year 年份
	 * @param int $month 月份
	 * @return int 天数
	 */
	function get_month_days($year,$month){
		$days = 0;
		$big_arr = array(1,3,5,7,8,10,12);
		$month = intval($month);
		if($month == 2){
			$days = leap_year($year) ? 29 : 28;
		}else if(in_array($month, $big_arr)){
			$days = 31;
		}else{
			$days = 30;
		}
		return $days;
	}

	/**
	 * 获取上月的起止时间戳
	 * @param void
	 * @return array 
	 */
	function last_month_timestamp(){

    	$result = array();
    	$last_month = date("Y-m",strtotime("-1 month"));
    	$year = substr($last_month, 0,4);
    	$month = substr($last_month,5,2);
   		$month_begin = $last_month.'-01';	
   		$month_days = $this->get_month_days($year,$month);
    	$result['begin'] = strtotime($month_begin);
    	$result['end'] = $result['begin'] + $month_days * 86400;
   		$result['days'] = $month_days;
   		return $result;
	}
}

?>