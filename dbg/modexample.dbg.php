<?php
	//modexample模块方法测试代码	
	//$rs[] = &$this->hello1('Jack'); //通过$this->引用模块自身方法测试
	
	//设置并发远程调用
	$this->setRPC('hello1', 2);
	$this->setRPC('hello2', 2);
	$this->setRPC('hello3', 2);

	$rs[] = &$this->hello1('Jack'); //通过$this->引用模块自身方法测试
	$rs[] = &$this->hello2('Rose');
	$rs[] = &$this->hello3();

	//并发远程调用
	$this->callRPC();

	print_r($rs);

?>