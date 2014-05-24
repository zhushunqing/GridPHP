<?php
/**
* 数据工具类
*/
class utility_array{

	/**
	* 递归将array转换成object
	* @param array $ary
	* @return object
	*/
	function array2object($ary){
		//to be contiune...
	}

	/**
	* 递归将object转换成array
	* @param object $obj
	* @return array
	*/
	function object2array($obj){
		$array = is_object($obj) ? get_object_vars($obj) : $obj;
		if(is_array($array) && sizeof($array) > 0) {
		       	foreach ($array as $key => $value) {
			       $value = (is_array($value) || is_object($value)) ? 
			       			utility_array::object2array($value) : $value;
			       $arr[$key] = $value;
		       	}
		       	return $arr;
	    	} else {
	    		return $array;
	    	}
	}

	/**
	* 合并数组
	* @param array $ary1 数组1
	* @param array $ary2 数组2
	* @param bool $over 1 用ary2合并覆盖ary1中的同名键值 0 只合并ary1中没有的键值 
	* @author ZhuShunqing
	*/
	function merge(&$ary1, &$ary2, $over = 1){
		if(is_array($ary1))
			while (list($key, ) = each($ary2))
				$this->merge($ary1[$key], $ary2[$key], $over);
		else if(is_null($ary1) || $over)
			$ary1 = $ary2;
	}

	/**
	 * 解决 php 5.2.6 以上版本 array_diff() 函数在处理大数组时的效率问题
	 * 根据 ChinaUnix 论坛版主 hightman 思路写的方法
	 * @author ChenYi
	 */
	function array_diff_fast($firstArray, $secondArray) 
	{
	 
	    // 转换第二个数组的键值关系
	    $secondArray = array_flip($secondArray);
	 
	    // 循环第一个数组
	    foreach($firstArray as $key => $value) {
	 
	        // 如果第二个数组中存在第一个数组的值
	        if (isset($secondArray[$value])) {
	 
	            // 移除第一个数组中对应的元素
	            unset($firstArray[$key]);
	        }
	    }
	 
	    return $firstArray;
	}
	
}

?>