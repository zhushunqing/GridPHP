<?php
/**
* GRIDPHP HTTP并发线程
* @author ZhuShunqing
*/

//防止外部调用
if(
	substr($_SERVER['REMOTE_ADDR'], 0, 3) != '10.'
&&
	substr($_SERVER['REMOTE_ADDR'], 0, 12) != '124.' . '193.' . '193.' //办公环境测试
) die('Forbidden!');

$add_header = '';
_addHeader('START-DATE', date('Y-m-d H:i:s'));

//并发post请求子线程
$host = $_POST['h'];
$port = intval($_POST['p']);
$data = base64_decode($_POST['d']);

if($host && $port && $data){

	$timer = _getMsec();
	$fp = fsockopen($host, $port);
	fwrite($fp, $data );
	_addHeader('WRITE-TIMER', intval(_getMsec() - $timer));

	$timer = _getMsec();
	$buff = '';
	while(!feof($fp)){
		$buff .= fread($fp, 4096);
	}
	fclose( $fp );
	_addHeader('READ-TIMER', intval(_getMsec() - $timer));

	$p = strpos($buff, "\r\n\r\n");
	$head = substr($buff, 0, $p);		//响应头
	$response = substr($buff, $p);	//正文

	_addHeader('END-DATE', date('Y-m-d H:i:s'));
	print $head . $add_header . $response;
}else{
	print 'null';
}

/**
* 得到当前毫秒(自2011-01-01起)
* @return float MilliSecond
*/
function _getMsec(){
	list($usec, $sec) = explode(" ",microtime()); 
	return ( ((float)$sec - 1293811200) + (float)$usec) * 1000; //1293811200 = strtotime('2011-01-01')
}

function _addHeader($h, $v){
	global $add_header;
	$header = ($h . ': ' .$v);
	$add_header .= "\r\n$header";
	header($header);
}


?>