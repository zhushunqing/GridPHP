<?php
/**
* GridPHP cookie工具类
* @author ZhuShunqing
*/
class gridphp_cookie{

	/**
	* 设置单个cookie
	*/
	function setCookie($key, $value, $expire = NULL, $path = NULL, $domain = NULL){
		return @setcookie($key, $value, $expires, $path, $domain);
	}

	/**
	* 设置多个cookie
	*/
	function setCookies($cookies, $expire = NULL, $path = NULL, $domain = NULL){
		foreach ($cookies as $key => $value)
			$this->setCookie($key, $value, $expire, $path, $domain);
	}

	function getCookie($key){
		return isset($_COOKIE[$key]) ? $_COOKIE[$key] : NULL;
	}

	function getCookies($keys){
		$cookies = array();
		foreach($keys as $i => $k)
			$cookies[$k] = $this->getCookie($k);
		return $cookies;
	}
	/**
	* 删除cookie
	*/
	function delCookie($key, $path = NULL, $domain = NULL){
		$time = time() - 1;
		return $this->setCookie($key, '', $time, $path, $useDomain);
	}

}

?>