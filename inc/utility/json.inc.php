<?php
/**
* GridPHP json工具类
* @author ZhuShunqing
*/
class utility_json{

	var $json;

	function _Init_(){
		if(!function_exists('json_encode') || !function_exists('json_decode')){
			require_once('json.class.php');
			$this->json = new Services_JSON_GRIDPHP();
		}
	}

	/**
	* json转码
	*/
	function encode($obj){
		if(function_exists('json_encode'))
			return json_encode($obj);
		else
			return $this->json->encode($obj);
	}

	/**
	* json中文不转成unicode
	*/
	function encode_cn($obj){
		$json = $this->encode($obj);
		$json = preg_replace("#\\\u([0-9a-f]+)#ie", "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))", $json);
		return $json;
	}

	/**
	* json解码
	* $is_array  true 强制返回数组
	*/
	function decode($data, $is_array = false){
		if(function_exists('json_decode')) {
			return json_decode($data, $is_array);
		} else {
			$jsonObj = $this->json->decode($data);
			if(false === $is_array)	{
				return $jsonObj;
			} else {
				return $this->getArrayFromObj($jsonObj);
			}
		}
	}
	
	/**
	* 递归将对象转化成数组
	*/
	function getArrayFromObj($obj) {
		$array = is_object($obj) ? get_object_vars($obj) : $obj;
		if(is_array($array) && sizeof($array) > 0) {
		       	foreach ($array as $key => $value) {
			       $value = (is_array($value) || is_object($value)) ? 
			       			utility_json::getArrayFromObj($value) : $value;
			       $arr[$key] = $value;
		       	}
		       	return $arr;
	    	} else {
	    		return $array;
	    	}
	}

	/*
	* 返回对象全部节点类型
	* @param $o obj或array
	* @param $p 递归调用传递的当前路径
	* @return array
	*/
	function objtypes($o, $p = 'obj'){
		$t = gettype($o);
		$path = '';
		if($t == 'array' || $t == 'object'){
			$path = $p . '=' . $t . "\n";
			foreach($o as $k => $v){
				$path .= $this->objtypes($v, $p . '.' . $k);
			}
		}
		if($p == 'obj'){
			$path = explode("\n", $path);
			$ary = array();
			for($i = count($path) - 2 ; $i >= 0; $i --){
				list($k, $v) = explode('=', $path[$i]);
				$ary[$k] = $v;
			}
			return $ary;
		}else{
			return $path;
		}
	}

	/*
	* 根据路径转换obj为array对象
	* @param $obj 传入的obj引用
	* @param $path 节点路径
	*/
	function array_in_obj(&$obj, &$path){
		$p = array_shift($path);
		if(count($path) > 0){
			if(gettype($obj) == 'array')
				$this->array_in_obj($obj[$p], $path);
			else
				$this->array_in_obj($obj->$p, $path);
		}
		else if(is_null($p))
			$obj = (array) $obj;
		else if(gettype($obj) == 'object')
			$obj->$p = (array) $obj->$p;
		else if(gettype($obj) == 'array')
			$obj[$p] = (array) $obj[$p];
	}

	/*
	* 恢复obj中的array类型节点
	* @param $obj 传入的obj引用
	* @param $types 节点类型列表
	*/
	function recover_array(&$obj, &$types){
		foreach($types as $k => $v){
			$p = explode('.', $k);
			array_shift($p);
			if($v == 'array')
				$this->array_in_obj($obj, $p);
		}
		return $obj;
	}

}
