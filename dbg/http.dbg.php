<?php
/**
* article模块调试代码
*/

	print "<pre>";
	$this->utility->loadC('time');
	$this->utility->time->setTimerPoint('test');

	$h = $this;
	$rs = array();

	$args['type'] = $args[0] ? $args[0] : $args['type'];

	//并发请求
	switch($args['type']){
		//1 并发get请求
		//2 并发post请求
		//3 顺序get请求
		//4 顺序post请求
		//5 并发post上传文件
		//6 顺序post上传文件

		case 1:	
			print "并发get请求\n";

			$rs[] = &$h->getUrl('http://zhushunqing.sinaapp.com/sleep.php?s=1', GRIDPHP_RPC_NONBLOCK);
			$rs[] = &$h->getUrl('http://zhushunqing.sinaapp.com/sleep.php?s=2&a=1&b=2', GRIDPHP_RPC_NONBLOCK);
			$rs[] = &$h->getUrl('http://zhushunqing.sinaapp.com/sleep.php?s=3&ccc', GRIDPHP_RPC_NONBLOCK);
			$h->sendRequest(3000); //全局超时

		break;
		case 2: 
			print "并发post请求\n";

			$rs[] = &$h->postUrl('http://zhushunqing.sinaapp.com/sleep.php?s=1', 'aaa=1&bbb=2&ccc=3', GRIDPHP_RPC_NONBLOCK);
			$rs[] = &$h->postUrl('http://zhushunqing.sinaapp.com/sleep.php?s=3', 'aaa=1&bbb=2&ccc=3', GRIDPHP_RPC_NONBLOCK);
			$rs[] = &$h->postUrl('http://zhushunqing.sinaapp.com/sleep.php?s=4', 'aaa=1&bbb=2&ccc=3', GRIDPHP_RPC_NONBLOCK);
			$h->sendRequest(5000);

		break;
		case 3:
			print "顺序get请求\n";

			$rs[] = $h->getUrl('http://zhushunqing.sinaapp.com/sleep.php?s=1', 4000);
			$rs[] = $h->getUrl('http://zhushunqing.sinaapp.com/sleep.php?s=2', 4000);
			//$h->setHeader('Host', 'flashman.com.cn');
			$rs[] = $h->getUrl('http://zhushunqing.sinaapp.com/sleep.php?s=3', 4000);

		break;
		case 4: 
			print "顺序post请求\n";

			$rs[] = $h->postUrl('http://zhushunqing.sinaapp.com/sleep.php?s=1', 'aaa=1&bbb=2&ccc=3');
			$rs[] = $h->postUrl('http://zhushunqing.sinaapp.com/sleep.php?s=3', 'aaa=1&bbb=2&ccc=3');
			$rs[] = $h->postUrl('http://zhushunqing.sinaapp.com/sleep.php?s=4', 'aaa=1&bbb=2&ccc=3');

		break;
		case 5: 
			print "并发post上传文件\n";

			$h->addFile('upload1', './tmp/screenshot1.png'); //支持多文件
			//$h->addFile('upload2', './tmp/screenshot2.png'); 
			$rs[] = &$h->postUrl('http://zhushunqing.sinaapp.com/sleep.php', 'aaa=1&bbb=2&ccc=3', GRIDPHP_RPC_NONBLOCK);

			$h->addFile('upload2', './tmp/screenshot2.png'); //支持多文件
			//$h->addFile('upload2', './tmp/screenshot3.png'); 
			$rs[] = &$h->postUrl('http://zhushunqing.sinaapp.com/sleep.php', 'aaa=1&bbb=2&ccc=3', GRIDPHP_RPC_NONBLOCK);

			$h->addFile('upload3', './tmp/screenshot3.png');
			//$h->addFile('upload4', './tmp/screenshot4.png');
			$rs[] = &$h->postUrl('http://zhushunqing.sinaapp.com/sleep.php', 
					array('ddd' => 4, 'eee' => 5, 'fff' => 6), //post数据支持数组和query串
					GRIDPHP_RPC_NONBLOCK);

			$h->addFile('upload4', './tmp/screenshot4.png'); 
			$rs[] = &$h->postUrl('http://zhushunqing.sinaapp.com/sleep.php', 'aaa=1&bbb=2&ccc=3', GRIDPHP_RPC_NONBLOCK);
			$h->sendRequest(30000);

		break;
		case 6:
			print "顺序post上传文件\n";

			$h->addFile('upload1', './tmp/screenshot1.png'); //支持多文件
			//$h->addFile('upload2', './tmp/screenshot2.png'); 
			$rs[] = $h->postUrl('http://zhushunqing.sinaapp.com/sleep.php', 'aaa=1&bbb=2&ccc=3');

			$h->addFile('upload2', './tmp/screenshot2.png'); //支持多文件
			//$h->addFile('upload3', './tmp/screenshot3.png'); 
			$rs[] = $h->postUrl('http://zhushunqing.sinaapp.com/sleep.php', 'aaa=1&bbb=2&ccc=3');

			$h->addFile('upload3', './tmp/screenshot3.png');
			//$h->addFile('upload4', './tmp/screenshot4.png');
			$rs[] = $h->postUrl('http://zhushunqing.sinaapp.com/sleep.php',
				array('ddd' => 4, 'eee' => 5, 'fff' => 6));//post数据支持数组和query串

			$h->addFile('upload4', './tmp/screenshot4.png');
			$rs[] = $h->postUrl('http://zhushunqing.sinaapp.com/sleep.php', 'aaa=1&bbb=2&ccc=3');

			$h->addFile('upload[]', './tmp/screenshot1.png'); //支持多文件
			$h->addFile('upload[]', './tmp/screenshot2.png'); 
			//$h->addFile('upload3', './tmp/screenshot3.png'); 
			$rs[] = $h->postUrl('http://zhushunqing.sinaapp.com/sleep.php', 'aaa=1&bbb=2&ccc=3', 15000);

		break;
		case 10:
			print "其它测试\n";
			//...................................
$this->setHeader('Host', 'reg.taobao.com');
$this->setHeader('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:25.0) Gecko/20100101 Firefox/25.0');
$this->setHeader('Accept', 'application/json, text/javascript, */*; q=0.01');
$this->setHeader('Accept-Language', 'en-US,en;q=0.5');
$this->setHeader('Accept-Encoding', 'gzip, deflate');
$this->setHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
$this->setHeader('X-Requested-With', 'XMLHttpRequest');
$this->setHeader('Referer', 'http://reg.taobao.com/member/new_email_reg_two.jhtml?spm=a1z08.5765629.1000508.2.siKLEi&paras=MTk0NTUyNTQ0Nw%3D%3D&redirectUrl=&redirectName=');
$this->setHeader('Content-Length', '182');
$this->setHeader('Cookie', 'mt=ci=0_0; _umdata=2354076B2AADD970F322562EBF1F1D623F36459B6DC2B683B2AB4E24AAC265F1126BA2101D7569739D5690C1AEE1B8D3E046E2C7C62BC43D73AD8A38EC4A7E71E2FEB7AB4B57F75A; cookie2=d3e8c5dff91fa0434bc362ef75d954a9; t=61959472b748d0e7ace190f8fc2a8708; uc1=cookie14=UoLU47zH%2FA8goA%3D%3D; v=0; cna=y+ELC1JRUzMCAW/B0hmxzLOP; _tb_token_=Igk8L3mSsm; rg2=UoLU47zH%2FA8h5QeX6A%3D%3D; _lang=zh_CN%3AGBK; rg3=en-US%3Afirefox%7C25%3Amacos%7C10.9%3A2560*1440');
$this->setHeader('Connection', 'keep-alive');
$this->setHeader('Pragma', 'no-cache');
$this->setHeader('Cache-Control', 'no-cache');

		case 11:
			print "通过HTTP代理请求\n";
			$this->setProxy('127.0.0.1', 8081);
			$rs[] = $h->getUrl('http://zhushunqing.sinaapp.com/ip.php');
		break;

		case 12:
			print "通过Socket请求\n";
			// $rs[] = $h->sockget('127.0.0.1', 8000, "Hello\n", 3000);

			$data = "GET /sleep.php?s=3 HTTP/1.1
User-Agent: GRIDPHP HTTP Class
Host: zhushunqing.sinaapp.com
Connection: Close
Cache-Control: no-cache

";
			$rs[] = $h->sockget('zhushunqing.sinaapp.com', 80, $data, 3000);
			var_dump($rs);exit;
		break;
	}

	foreach($rs as $i => $r){
		print $i . ' => ';
		$r['request'] = substr($r['request'], 0, 300);
		print_r($r);
		print "\n";
	}

	print $this->utility->time->getTimerDiff('test') . "ms\n";;

?>