<?php
/**
 * GridPHP http模块内部方法
 * @author ZhuShunqing
 */
class http_func {

	/**
	* 生成header请求串
	* @return string
	*/
	function _buildHeader($headers){
		$h = '';
		foreach($headers as $k => $v)
			$h .= $k . ': ' . $v . "\r\n";
		return $h;
	}

	/**
	* 生成表单数据
	* @param array $data 表单数组
	* @param string $boundary 
	* @return string
	*/
	function _buildForm($data, $boundary){
		$form = '';
		foreach($data as $k => $v){
			$form .= '--' . $boundary . "\r\n";
			$form .= 'Content-Disposition: form-data; name="' . $k . "\"\r\n\r\n";
			$form .= $v . "\r\n";
		}
		return $form;
	}

	/**
	* 生成文件上传数据
	* @param array $files
	* @return string;
	*/
	function _buildFile($boundary, $files){
		$data = '';
		foreach($files as $i => $file){
			$getfile = file_get_contents($file['name']);
			$basename = basename($file['name']);
			$data .= '--' . $boundary . "\r\n";
			$data .= 'Content-Disposition: form-data; name="' . $file['input'] . '"; filename="' . $basename . "\"\r\n";
			$data .= 'Content-Type: ' . $file['type'] . "\r\n\r\n";
			$data .=  $getfile . "\r\n";
		}
		return $data;
	}

	/**
	* 解析http结果
	* @param string $text http返回信息
	* @return array 'headers' => array('head' => 'value'), 'response' => 'string', 'status' => int
	*/
	function _parse_response($text){
		$p = strpos($text, "\r\n\r\n");
		$head = substr($text, 0, $p);		//响应头
		if($p)
			$response = substr($text, $p + 4);	//正文
		else
			$response = $text; //非HTTP协议
		$line = explode("\r\n", $head);
		list(,$status,) = @explode(' ', $line[0]);	//状态码
		if(!$status) $status = GRIDPHP_RPC_ERR_BAD_REQUEST;

		$headers = array();			//header信息
		for($i = 1; $i < count($line); $i ++){
			list($k, $v) = explode(': ', $line[$i]);
			$headers[$k] = $v;
		}

		if($headers['Transfer-Encoding'] == 'chunked')
			$response = $this->http_chunked_decode($response);
		if($headers['Content-Encoding'] == 'gzip')
			$response = $this->decodeGzip($response);

		return array(
			'status'	=> $status,
			'headers'	=> $headers,
			'response'	=> $response,
		);
	}

	/**
	* 分析url链接
	* @param string $url
	* @return array
	*/
	function _parse_url($url){
		$s = parse_url($url);
		$s['scheme'] = empty($s['scheme']) ? 'http' : $s['scheme'];
		$s['port'] = empty($s['port']) ? '80' : $s['port'];
		$s['path'] = empty($s['path']) ? '/' : $s['path'];
		$s['query'] = empty($s['query']) ? $s['path'] : $s['path'] . '?' . $s['query'];
		return $s;
	}

	/**
	* http_build_query兼容
	*/
	function http_build_query($formdata){
		$data = '';
		if(function_exists('http_build_query')){
			$data = http_build_query($formdata);
		}else{
			foreach($formdata as $k => $v)
				$data .= $k . '=' . urlencode($v) . '&';
			$data = substr($data, 0, -1);
		}
		return $data;	
	}

	/**
	* Decodes the message-body encoded by gzip
	*
	* The real decoding work is done by gzinflate() built-in function, this
	* method only parses the header and checks data for compliance with
	* RFC 1952  
	*
	* @access   private
	* @param    string  gzip-encoded data
	* @return   string  decoded data
	*/
	function decodeGzip($data)
	{
		$length = strlen($data);
		// If it doesn't look like gzip-encoded data, don't bother
		if (18 > $length || strcmp(substr($data, 0, 2), "\x1f\x8b")) {
			return $data;
		}
		$method = ord(substr($data, 2, 1));
		if (8 != $method) {
			return ('_decodeGzip(): unknown compression method');
		}
		$flags = ord(substr($data, 3, 1));
		if ($flags & 224) {
			return ('_decodeGzip(): reserved bits are set');
		}

		// header is 10 bytes minimum. may be longer, though.
		$headerLength = 10;
		// extra fields, need to skip 'em
		if ($flags & 4) {
			if ($length - $headerLength - 2 < 8) {
				return ('_decodeGzip(): data too short');
			}
			$extraLength = unpack('v', substr($data, 10, 2));
			if ($length - $headerLength - 2 - $extraLength[1] < 8) {
				return ('_decodeGzip(): data too short');
			}
			$headerLength += $extraLength[1] + 2;
		}
		// file name, need to skip that
		if ($flags & 8) {
			if ($length - $headerLength - 1 < 8) {
				return ('_decodeGzip(): data too short');
			}
			$filenameLength = strpos(substr($data, $headerLength), chr(0));
			if (false === $filenameLength || $length - $headerLength - $filenameLength - 1 < 8) {
				return ('_decodeGzip(): data too short');
			}
			$headerLength += $filenameLength + 1;
		}
		// comment, need to skip that also
		if ($flags & 16) {
			if ($length - $headerLength - 1 < 8) {
				return ('_decodeGzip(): data too short');
			}
			$commentLength = strpos(substr($data, $headerLength), chr(0));
			if (false === $commentLength || $length - $headerLength - $commentLength - 1 < 8) {
				return ('_decodeGzip(): data too short');
			}
			$headerLength += $commentLength + 1;
		}
		// have a CRC for header. let's check
		if ($flags & 1) {
			if ($length - $headerLength - 2 < 8) {
				return ('_decodeGzip(): data too short');
			}
			$crcReal   = 0xffff & crc32(substr($data, 0, $headerLength));
			$crcStored = unpack('v', substr($data, $headerLength, 2));
			if ($crcReal != $crcStored[1]) {
				return ('_decodeGzip(): header CRC check failed');
			}
			$headerLength += 2;
		}
		// unpacked data CRC and size at the end of encoded data
		$tmp = unpack('V2', substr($data, -8));
		$dataCrc  = $tmp[1];
		$dataSize = $tmp[2];

		// finally, call the gzinflate() function
		$unpacked = @gzinflate(substr($data, $headerLength, -8), $dataSize);
		if (false === $unpacked) {
			return ('_decodeGzip(): gzinflate() call failed');
		} elseif ($dataSize != strlen($unpacked)) {
			return ('_decodeGzip(): data size check failed');
		//} elseif ($dataCrc != crc32($unpacked)) {
		//	return ('_decodeGzip(): data CRC check failed');
		}
		return $unpacked;
	}

	function http_chunked_decode($chunk) { 
		$pos = 0; 
		$len = strlen($chunk); 
		$dechunk = null; 

		while(($pos < $len) 
			&& ($chunkLenHex = substr($chunk,$pos, ($newlineAt = strpos($chunk,"\n",$pos+1))-$pos))) 
		{ 
			if (!$this->is_hex($chunkLenHex)) { 
				trigger_error('Value is not properly chunk encoded', E_USER_WARNING); 
				return $chunk; 
			} 

			$pos = $newlineAt + 1; 
			$chunkLen = hexdec(rtrim($chunkLenHex,"\r\n")); 
			$dechunk .= substr($chunk, $pos, $chunkLen); 
			$pos = strpos($chunk, "\n", $pos + $chunkLen) + 1; 
		} 
		return $dechunk; 
	} 

	function is_hex($hex) { 
		// regex is for weenies 
		$hex = strtolower(trim(ltrim($hex,"0"))); 
		if (empty($hex)) { $hex = 0; }; 
		$dec = hexdec($hex); 
		return ($hex == dechex($dec)); 
	}
	
	/**
	* 得到当前毫秒(自2011-01-01起)
	* @return float MilliSecond
	*/
	function _getMsec(){
		list($usec, $sec) = explode(" ",microtime()); 
		return ( ((float)$sec - 1293811200) + (float)$usec) * 1000; //1293811200 = strtotime('2011-01-01')
	}

}