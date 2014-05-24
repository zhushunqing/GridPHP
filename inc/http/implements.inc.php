<?php
/**
* GridPHP http接口调用基础类
* @author ZhuShunqing
*/
class http_implements extends gridphp_implements{

	var $_sockets, $_response, $_buffers, $_checks, $_headers, $_files, $_writetimer, $_proxy;

	function _Init_(){
		$this->loadC('func');
		$this->_reset();
	}

	function _reset(){
		$this->_sockets = array();
		$this->_response = array();
		$this->_buffers = array('');
		$this->_checks = array(0);
		$this->_writetimer = 0;
		$this->_clearFiles();
		$this->clearHeaders();
	}

	/**
	* 设置HTTP代理服务器
	* @param string $host 代理服务器
	* @param int $port 端口
	* @return void
	*/
	function setProxy($host, $port){
		if($host && $port){
			$this->_proxy = array(
				'host' => $host,
				'port' => $port
			);
		}else{
			$this->_proxy = false;
		}
	}

	/**
	* 清除上传文件
	* @return void
	*/
	function _clearFiles(){
		$this->_files = array();
	}

	/**
	* Clear all headers
	*/
	function clearHeaders(){
		$this->_headers = array();
       	$this->setHeader('User-Agent', 'GRIDPHP HTTP Class');
       	$this->setHeader('Accept-Encoding', 'gzip');
	}

	/**
	* 设置请求头信息
	* @param string $k 头标识
	* @param string $v 对应值
	* @return void
	*/
	function setHeader($k, $v){
		$this->_headers[$k] = $v;
	}

	/**
	* 返回请求头信息
	* @param string $k 头标识
	* @return void
	*/
	function getHeader($k){
		return isset($this->_headers[$k]) ? $this->_headers[$k] : null;
	}

	/**
	* 清除请求头信息
	* @param string $k 头标识
	* @return void
	*/
	function delHeader($k){
		unset($this->_headers[$k]);
	}

	/**
	* 增加上传文件
	* @param string $inputName 表单名
	* @param string $fileName 上传文件及路径
	* @param string $contentType mime类型
	* @return void
	*/
	function addFile($input, $file, $contentType = null){
		if(!$contentType){
			if(function_exists('mime_content_type'))
				$contentType = mime_content_type($file);
			else
				$contentType = 'application/octet-stream';
		}

		$this->_files[] = array(
			'input'=> $input,
			'name' => $file,
			'type' => $contentType
		);
	}

	/**
	* 移除上传文件
	* @param string $file 文件名
	* @return void
	*/
	function remFile($file){
		foreach($this->files as $i => $files){
			if($file == $files['name'])
				unset($this->_files[$i]);
		}
	}

	/**
	* 发起http head请求
	* @param string $url
	* @param int $timeout 超时ms
	* @return array ('headers' => array('head' => 'value'), 'response' => 'string', 'status' => int(-1001读取超时 -1002连接/发送超时 其它同http 404 200等)
	*/
	function &headUrl($url, $timeout = GRIDPHP_HTTP_DEFAULT_TIMEOUT){
		$server = $this->func->_parse_url($url);
		return $this->head($server['host'], $server['port'], $server['query'], $timeout);
	}

	/**
	* 发起http head请求
	* @param string $host 主机IP
	* @param int $port 端口
	* @param string $request 请求文件
	* @param int $timeout 超时设置
	* @return array ('headers' => array('head' => 'value'), 'response' => 'string', 'status' => int(-1001读取超时 -1002连接/发送超时 -1003不支持socket方法 其它同http 404 200等)
	*/
	function &head($host, $port, $request, $timeout){

		//不支持socket方法
		if(!function_exists('socket_create')) return array('status' => GRIDPHP_HTTP_ERR_NO_SOCKET);

		$this->setHeader('Host', $this->getHeader('Host') ? $this->getHeader('Host') : $host);
		$this->setHeader('Connection', 'Close');
		$this->setHeader('Cache-Control', 'no-cache');
		$header = $this->func->_buildHeader($this->_headers);
		$data = 'HEAD ' . $request . " HTTP/1.0\r\n" . $header . "\r\n\r\n";
		$rs = &$this->_requestData($host, $port, $data);
		if($timeout !== GRIDPHP_HTTP_NONBLOCK) $this->sendRequest($timeout);
		return $rs;
	}

	/**
	* 发起http get url返回
	* @param string $url
	* @param int $timeout 超时ms
	* @return array ('headers' => array('head' => 'value'), 'response' => 'string', 'status' => int(-1001读取超时 -1002连接/发送超时 其它同http 404 200等)
	*/
	function &getUrl($url, $timeout = GRIDPHP_HTTP_DEFAULT_TIMEOUT){
		$server = $this->func->_parse_url($url);
		return $this->get($server['host'], $server['port'], $server['query'], $timeout);
	}

	/**
	* 发起http post url返回
	* @param string $url
	* @param string or array $form 表单值
	* @return string
	* @return array ('headers' => array('head' => 'value'), 'response' => 'string', 'status' => int(-1001读取超时 -1002连接/发送超时 其它同http 404 200等)
	*/
	function &postUrl($url, $form = '', $timeout = GRIDPHP_HTTP_DEFAULT_TIMEOUT){
		$server = $this->func->_parse_url($url);
		return $this->post($server['host'], $server['port'], $server['query'], $form, $timeout);
	}

	/**
	* 发起http get请求
	* @param string $host 主机IP
	* @param int $port 端口
	* @param string $request 请求文件
	* @param int $timeout 超时设置
	* @return array ('headers' => array('head' => 'value'), 'response' => 'string', 'status' => int(-1001读取超时 -1002连接/发送超时 -1003不支持socket方法 其它同http 404 200等)
	*/
	function &get($host, $port, $request, $timeout){

		//不支持socket方法
		if(!function_exists('socket_create')) return array('status' => GRIDPHP_HTTP_ERR_NO_SOCKET);

		$this->setHeader('Host', $this->getHeader('Host') ? $this->getHeader('Host') : $host);
		$this->setHeader('Connection', 'Close');
		$this->setHeader('Cache-Control', 'no-cache');
		$header = $this->func->_buildHeader($this->_headers);
		if($this->_proxy){
			$this->setHeader('Proxy-Connection', 'keep-alive');
			$host = $this->_proxy['host'];
			$port = $this->_proxy['port'];
			$request = 'http://' . $this->getHeader('Host') . '/' . $request;
		}
		$data = 'GET ' . $request . " HTTP/1.1\r\n" . $header . "\r\n\r\n";
		$rs = &$this->_requestData($host, $port, $data);
		if($timeout !== GRIDPHP_HTTP_NONBLOCK) $this->sendRequest($timeout);
		return $rs;
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
	function &post($host, $port, $request, $form, $timeout){

		//不支持socket方法
		if(!function_exists('socket_create')) return array('status' => GRIDPHP_HTTP_ERR_NO_SOCKET);

		$data = '';
		if(count($this->_files) > 0){
			//文件上传post

			$boundary = '-----GRIDPHP_HTTP-' . md5(uniqid('GRIDPHP_HTTP') . microtime());
			$this->setHeader('Content-Type', 'multipart/form-data; boundary=' . $boundary);

			//表单数据
			if(!is_array($form)) parse_str($form, $form);
			$data .= $this->func->_buildForm($form, $boundary);

			//上传文件
			$data .= $this->func->_buildFile($boundary, $this->_files);

			$data .= '--' . $boundary . "--";
		}else{
			//普通post
			$data = is_array($form) ? $this->func->http_build_query($form) : $form;
		}

		$len = strlen($data);

		if(!$this->getHeader('Content-Type')) $this->setHeader('Content-Type', 'application/x-www-form-urlencoded');
		if(!$this->getHeader('Host')) $this->setHeader('Host', $host);
		//if(!$this->getHeader('Accept')) $this->setHeader('Accept', '*/*');
		//if(!$this->getHeader('Accept-Language')) $this->setHeader('Accept-Language', 'zh-cn');
		//$this->setHeader('Cache-Control', 'no-cache');
		$this->setHeader('Content-Length', $len);
		$this->setHeader('Connection', 'Close');
		$header = $this->func->_buildHeader($this->_headers);
		$this->_clearFiles();

		$data = 'POST ' . $request . " HTTP/1.1\r\n" . $header . "\r\n" . $data;

		$rs = &$this->_requestData($host, $port, $data);
		if($timeout !== GRIDPHP_HTTP_NONBLOCK) $this->sendRequest($timeout);
		return $rs;
	}

	/**
	* 准备发送数据
	*/
	function &_requestData($host, $port, $data){
		$idx = count($this->_sockets);
		$this->_sockets[$idx]['host'] = $host;
		$this->_sockets[$idx]['port'] = $port;
		$this->_sockets[$idx]['data'] = $data;
		return $this->_response[$idx];
	}

	/**
	* 等待接收数据
	* @param int $timeout 超时ms
	*/
	function sendRequest($timeout = GRIDPHP_HTTP_DEFAULT_TIMEOUT){

		//不支持socket方法
		if(!function_exists('socket_create')) return false;

		$timerstart = $this->func->_getMsec();

		//发送数据请求
		for($i = 0; $i < count($this->_sockets); $i ++){

			$host = $this->_sockets[$i]['host'];
			$port = $this->_sockets[$i]['port'];
			$data = $this->_sockets[$i]['data'];

			//是否启动多进程并发
			$this->_sockets[$i]['thread'] = 0;
			if(
				GRIDPHP_HTTP_THREAD_MODE == 1
				&& count($this->_sockets) > 1
				&& strlen($data) > GRIDPHP_HTTP_THREAD_LENGTH
			){
				$this->_sockets[$i]['thread'] = 1;

			}else if(
				GRIDPHP_HTTP_THREAD_MODE == 2
				&& count($this->_sockets) > 1
			){
				$this->_sockets[$i]['thread'] = 1;
			}

			//多线程代理
			if($this->_sockets[$i]['thread']){
				$data = base64_encode($data);
				$data = array('h' => $host, 'p' => $port, 'd' =>  $data);
				$data = $this->func->http_build_query($data);

				$conf = $this->getConf('THREAD_CONFIG');

				$len = strlen($data);
				$this->setHeader('Host', $conf['name']);
				$this->setHeader('Content-Type', 'application/x-www-form-urlencoded');
				$this->setHeader('Content-Length', $len);
				$header = $this->func->_buildHeader($this->_headers);

				$data = 'POST ' . $conf['uri'] . " HTTP/1.1\r\n" . $header . "\r\n" . $data;
				$this->_sockets[$i]['data'] = $data;
				$host = $conf['host'];
				$port = $conf['port'];
			}

			$this->_sockets[$i]['startdate'] = date('Y-m-d H:i:s');
			$socket = $this->socketConnect($host, $port);
			$this->_sockets[$i]['socket'] = $socket;

		}

		//get data
		$this->_socketGetData($timeout);

		//解析http结果
		$this->_parse_reponse();

		//DEBUG=101打印调试信息
		//$this->debug->dump($this->_sockets, 101);
		$this->debug->dump($this->_response, 101);

		//记录错误日志
		$this->_put_error();

		$this->close();
	}

	/**
	* 发起socket连接
	* @param string $host 主机IP
	* @param int $port 端口
	* @return socket reference
	*/
	function socketConnect($host, $port){
		$timerstart = $this->func->_getMsec();

		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if(socket_set_nonblock($socket)){ //mac+php5.4.17 socket_last_error() = 36
			if(@socket_connect($socket, $host, $port) || in_array(socket_last_error(), array(115, 36))){
				$this->_writetimer += intval($this->func->_getMsec() - $timerstart);
				return $socket;
			}
		}
		$errno = socket_last_error($socket);
		$errstr = socket_strerror($errno);
		if(is_resource($socket)) socket_close($socket);
		$socket = array('error' => $errno, 'errinfo' => $errstr);

		return $socket;
	}

	/**
	* Get Data
	*/
	function _socketGetData($timeout){
		$timerstart = $this->func->_getMsec();
		$timeout -= $this->_writetimer;

		//resource id => socket id
		$resid = array();
		$pos = array();
		for($i = 0; $i < count($this->_sockets); $i ++){
			$resid[(int) $this->_sockets[$i]['socket']] = $i;
			$pos[$i] = 0;
			$this->_sockets[$i]['readtimer'] = 0;
			$this->_sockets[$i]['writetimer'] = 0;
		}
		$write_flag = array(0);
		$read_flag = array(0);

		while(($timer = ($this->func->_getMsec() - $timerstart)) < $timeout){

			$write = array();
			$read = array();
			for($i = 0; $i < count($this->_sockets); $i ++){
				if(is_resource($this->_sockets[$i]['socket'])){
					$sockid = (int) $this->_sockets[$i]['socket'];
					if(!$write_flag[$i])
						$write[] = $this->_sockets[$i]['socket'];					
					if(!$read_flag[$i])
						$read[] = $this->_sockets[$i]['socket'];
				}else{
					$write_flag[$i] = $read_flag[$i] = 1;
				}
			}

			if($write || $read){

				//Wait for socket write
				@socket_select($read, $write, $except = null, 0, ($timeout - $timer) * 1000);
				
				//write data
				foreach($write as $i => $socket){
					$sockid = $resid[(int) $socket];
					$data = $this->_sockets[$sockid]['data'];

					$buff = substr($data, $pos[$sockid], GRIDPHP_HTTP_WRITE_DATA_LEN);
					$len = strlen($buff);
					$p = @socket_write($socket, $buff, $len);
					$pos[$sockid] += $p;

					if($p === false){
						//$this->_sockets[$sockid]['socket'] = array("error" => GRIDPHP_HTTP_ERR_BAD_SERVICE, 'errinfo' => 'ERR_BAD_SERVICE');
					//write done
					}else if($pos[$sockid] >= strlen($data)){
						$write_flag[$sockid] = 1;
						$timerend = intval($this->func->_getMsec() - $timerstart + $this->_writetimer);
						$this->_sockets[$sockid]['writetimer'] = $timerend;
					}
				}

				//读取数据
				foreach($read as $i => $socket){
					$sockid = $resid[(int) $socket];
					if(!is_resource($this->_sockets[$sockid]['socket']))
						continue;
					
					$buff = @socket_read($socket, GRIDPHP_HTTP_READ_BUFF_LEN);
					if($buff)
						$this->_buffers[$sockid] .= $buff;
					else if($buff === ''){
						$this->_checks[$sockid] = true;
						$timerend = intval($this->func->_getMsec() - $timerstart);
						$this->_sockets[$sockid]['readtimer'] = $timerend;
						$this->_sockets[$sockid]['enddate'] = date('Y-m-d H:i:s');
						$read_flag[$sockid] = 1;
					}
				}

			}else{
				break;
			}

		}

		for($i = 0; $i < count($this->_sockets); $i ++){

			if(!$read_flag[$i]){
				$this->_response[$i]['status'] = GRIDPHP_HTTP_ERR_READ_TIMEOUT;
				$this->_response[$i]['error'] = 'ERR_READ_TIMEOUT';
				$this->_response[$i]['writetimer'] = $this->_sockets[$i]['writetimer'];
				$this->_response[$i]['readtimer'] = $timeout - $this->_sockets[$i]['writetimer'];
			}

			if(!$write_flag[$i]){
				$this->_response[$i]['status'] = GRIDPHP_HTTP_ERR_WRITE_TIMEOUT;
				$this->_response[$i]['error'] = 'ERR_WRITE_TIMEOUT';
				$this->_response[$i]['writetimer'] = $timeout;
			}
		}

	}

	//解析http结果
	function _parse_reponse(){

		for($i = 0; $i < count($this->_sockets); $i ++){

			if($this->_checks[$i]){
				$this->_response[$i] = $this->func->_parse_response($this->_buffers[$i]);
				if($this->_sockets[$i]['thread'])
					$this->_response[$i] = $this->func->_parse_response($this->_response[$i]['response']);

				$this->_response[$i]['headers']['START-DATE'] = $this->_sockets[$i]['startdate'];
				$this->_response[$i]['headers']['END-DATE'] = $this->_sockets[$i]['enddate'];

				// $this->_response[$i]['headers']['WRITE-TIMER'] = intval($this->_response[$i]['headers']['WRITE-TIMER']);
				// $this->_response[$i]['headers']['READ-TIMER'] = intval($this->_response[$i]['headers']['READ-TIMER']);

				// if(!$this->_response[$i]['headers']['WRITE-TIMER'])
					$this->_response[$i]['headers']['WRITE-TIMER'] = $this->_sockets[$i]['writetimer'];
				// if(!$this->_response[$i]['headers']['READ-TIMER'])
					$this->_response[$i]['headers']['READ-TIMER'] = $this->_sockets[$i]['readtimer'];

				$this->_response[$i]['headers']['ALL-TIMER'] = 
					$this->_response[$i]['headers']['WRITE-TIMER'] + 
					$this->_response[$i]['headers']['READ-TIMER'];

			}

			if(is_array($this->_sockets[$i]['socket'])){
				$this->_response[$i]['status'] = $this->_sockets[$i]['socket']['error'];
				$this->_response[$i]['error'] = $this->_sockets[$i]['socket']['errinfo'];

			}

			$this->_response[$i]['host'] = $this->_sockets[$i]['host'];
			$this->_response[$i]['port'] = $this->_sockets[$i]['port'];
			$this->_response[$i]['request'] = $this->_sockets[$i]['data'];

			//$this->_response[$i]['startdate'] = $this->_sockets[$i]['startdate'];
			//$this->_response[$i]['enddate'] = ($this->_sockets[$i]['enddate']) ?
			//		($this->_sockets[$i]['enddate']) : date('Y-m-d H:i:s');
			//$this->_response[$i]['writetimer'] = $this->_sockets[$i]['writetimer'];

		}
	}

	/**
	* error log
	* @return void
	*/
	function _put_error(){
		for($i = 0; $i < count($this->_sockets); $i ++){
			if($this->_response[$i]['status'] != '200'){
				list($request) = explode("\r\n", $this->_sockets[$i]['data']);
				list($method, $uri) = explode(' ', $request);
				$mixed = array(
					'time' => GRIDPHP_DATE_NOW,
					'server'=> $this->parent->parent->getServerIP(),
					'remote'=> $this->parent->parent->getClientIP(),
					'host'	=> $this->_sockets[$i]['host'],
					'port'	=> $this->_sockets[$i]['port'],
					'method'=> $method,
					'uri'=> $uri,
					'status'=> $this->_response[$i]['status']
				);
				//$this->debug->dump($mixed, 100);
				$this->log->writelog('err_gridphp_http.txt', $mixed);
			}
		}

	}

	/**
	* 关闭socket连接
	* @return void
	*/
	function close(){
		for($i = 0; $i < count($this->_sockets); $i ++)
			if(is_resource($this->_sockets[$i]['socket']))
				socket_close($this->_sockets[$i]['socket']);
			else
				unset($this->_sockets[$i]['socket']);
		$this->_reset();
	}

}

?>