<?php
/**
* GridPHP http接口调用基础类
* @author ZhuShunqing
*/
class gridphp_http extends gridphp_module{

	/**
	* 设置HTTP代理服务器
	* @param string $host 代理服务器
	* @param int $port 端口
	* @return void
	*/
	public function &setProxy($host, $port){ return $this->_callImplements(); }

	/**
	* 设置请求头信息
	* @param string $k 头标识
	* @param string $v 对应值
	* @return void
	*/
	public function &setHeader($k, $v){ return $this->_callImplements(); }
	/**
	* 返回请求头信息
	* @param string $k 头标识
	* @return void
	*/
	public function &getHeader($k){ return $this->_callImplements(); }

	/**
	* 清除请求头信息
	* @param string $k 头标识
	* @return void
	*/
	public function &delHeader($k){ return $this->_callImplements(); }

	/**
	* Clear all headers
	*/
	public function &clearHeaders(){ return $this->_callImplements(); }

	/**
	* 增加上传文件
	* @param string $inputName 表单名
	* @param string $fileName 上传文件及路径
	* @param string $contentType mime类型
	* @return void
	*/
	public function &addFile($input, $file, $contentType = null){ return $this->_callImplements(); }

	/**
	* 移除上传文件
	* @param string $file 文件名
	* @return void
	*/
	public function &remFile($file){ return $this->_callImplements(); }

	/**
	* 发起http head请求
	* @param string $url
	* @param int $timeout 超时ms
	* @return array ('headers' => array('head' => 'value'), 'response' => 'string', 'status' => int(-1001读取超时 -1002连接/发送超时 其它同http 404 200等)
	*/
	public function &headUrl($url, $timeout = GRIDPHP_HTTP_DEFAULT_TIMEOUT){
		$this->_lazyInit();
		return $this->implements->headUrl($url, $timeout);
	}

	/**
	* 发起http head请求
	* @param string $host 主机IP
	* @param int $port 端口
	* @param string $request 请求文件
	* @param int $timeout 超时设置
	* @return array ('headers' => array('head' => 'value'), 'response' => 'string', 'status' => int(-1001读取超时 -1002连接/发送超时 -1003不支持socket方法 其它同http 404 200等)
	*/
	public function &head($host, $port, $request, $timeout){
		$this->_lazyInit();
		return $this->implements->head($host, $port, $request, $timeout);
	}

	/**
	* 发起http get url返回
	* @param string $url
	* @param int $timeout 超时ms
	* @return array ('headers' => array('head' => 'value'), 'response' => 'string', 'status' => int(-1001读取超时 -1002连接/发送超时 其它同http 404 200等)
	*/
	public function &getUrl($url, $timeout = GRIDPHP_HTTP_DEFAULT_TIMEOUT){
		$this->_lazyInit();
		return $this->implements->getUrl($url, $timeout);
	}

	/**
	* 发起http post url返回
	* @param string $url
	* @param string or array $form 表单值
	* @return string
	* @return array ('headers' => array('head' => 'value'), 'response' => 'string', 'status' => int(-1001读取超时 -1002连接/发送超时 其它同http 404 200等)
	*/
	public function &postUrl($url, $form = '', $timeout = GRIDPHP_HTTP_DEFAULT_TIMEOUT){
		$this->_lazyInit();
		return $this->implements->postUrl($url, $form, $timeout);
	}
	/**
	* 发起http get请求
	* @param string $host 主机IP
	* @param int $port 端口
	* @param string $request 请求文件
	* @param int $timeout 超时设置
	* @return array ('headers' => array('head' => 'value'), 'response' => 'string', 'status' => int(-1001读取超时 -1002连接/发送超时 -1003不支持socket方法 其它同http 404 200等)
	*/
	public function &get($host, $port, $request, $timeout){
		$this->_lazyInit();
		return $this->implements->get($host, $port, $request, $timeout);
	}
	/**
	* 发起http post请求
	* @param string $host 主机IP
	* @param int $port 端口
	* @param string $request 请求文件
	* @param string or array $form 提交表单值
	* @param int $timeout 超时设置
	* @return array ('headers' => array('head' => 'value'), 'response' => 'string', 'status' => int(-1001读取超时 -1002连接/发送超时 -1003不支持socket方法 其它同http 404 200等)
	*/
	public function &post($host, $port, $request, $form, $timeout){
		$this->_lazyInit();
		return $this->implements->post($host, $port, $request, $form, $timeout);
	}

	/**
	* 等待接收数据
	* @param int $timeout 超时ms
	*/
	public function &sendRequest($timeout = GRIDPHP_HTTP_DEFAULT_TIMEOUT){ return $this->_callImplements(); }

}

?>