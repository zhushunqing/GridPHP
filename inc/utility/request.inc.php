<?php
/*
* 安全输入
*/
class utility_request{

	/**
	* 过滤取get参数值
	* @param $name 参数名
	* @param $type 数据类型 intval floatval string email ...
	* @param $default 默认值
	* @param $min 最小值/长度
	* @param $max 最大值/长度
	* @return value
	*/
    function getQuery($name, $type = null, $default = null, $min = null, $max = null){
    	return $this->getParam($name, $type, $default, $min, $max, 'get');
    }
	
	/**
	* 过滤取post参数值
	* @param $name 参数名
	* @param $type 数据类型 intval floatval string email ...
	* @param $default 默认值
	* @param $min 最小值/长度
	* @param $max 最大值/长度
	* @return value
	*/
	function getPost($name, $type = null, $default = null, $min = null, $max = null){
    	return $this->getParam($name, $type, $default, $min, $max, 'post');
	}

	/**
	* 规范化取参数值
	* @param $name 参数名
	* @param $type 数据类型 intval floatval string email ...
	* @param $default 默认值
	* @param $min 最小值/长度
	* @param $max 最大值/长度
	* @param $method get/post/request
	* @return value
	*/
	function getParam($name, $type = null, $default = null, $min = null, $max = null, $method = null){
		$name = trim($name);
		switch ($method){
			case 'get':
				$value = @$_GET[$name];	
				break;
			case 'post':
				$value = @$_POST[$name];
				break;
			default:
				$value = @$_REQUEST[$name];
				break;
		}
		$value = is_null($value) ? $default : $value;
		switch($type){
			case 'int':
			case 'intval':
				$value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
				$value = intval($value);
				break;
			case 'intary':
			case 'floatary':
				$value = preg_replace('/[^0-9,|\s]/', '', $value);
			case 'stringary':
				if($value)
					$value = preg_split('/[,|\s]/', $value);
				else
					$value = array();
				break;
			case 'string':
				$value = htmlspecialchars($value);
				break;
			case 'email':
				$value = filter_var($value, FILTER_SANITIZE_EMAIL);
				break;
			case 'quoted':
				$value = filter_var($value, FILTER_SANITIZE_MAGIC_QUOTES);
				break;
			case 'float':
			case 'floatval':
				$value = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT);
				$value = floatval($value);
				break;
			case 'url':
				$value = filter_var($value, FILTER_SANITIZE_URL);
				break;
			case 'boolean':
				$value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
				break;
			case 'ip':
				$value = filter_var($value, FILTER_VALIDATE_IP);
				break;
		}

		switch($type){
			case 'int':
			case 'intval':
			case 'float':
			case 'floatval':
				if($min != null && $value < $min) $value = $mix;
				if($max != null && $value > $max) $value = $max;
				break;

			case 'intary':
				foreach($value as $i => &$v)
					$v = intval($v);
				break;
			case 'floatary':
				foreach($value as $i => &$v)
					$v = floatval($v);
				break;
			case 'stringary':
				foreach($value as $i => &$v)
					$v = htmlspecialchars($v);
				break;

			case 'string':
			case 'url':
				$len = strlen($value);
				if($min != null && $len < $min) $value = 'too short';
				if($max != null && $len > $max) $value = substr($value, 0, $max);
				break;
		}

		return $value;
	}

	/**
	* 自动去除转义符
	*/
	function strip_request(){
		if(get_magic_quotes_gpc()){
			$this->strip_array($_GET);	// reset($_GET);
			$this->strip_array($_POST);	// reset($_POST);
			$this->strip_array($_REQUEST);// reset($_REQUEST);
		}
	}

	/*
	 * 递归去除对象中的转义符
	 */
	function strip_array(&$ary){
		foreach($ary as $i => &$v){
			if(is_array($v) || is_object($v)){
				$this->strip_array($v);
			}else{
				$v = stripslashes($v);
			}
		}
	}

}

?>