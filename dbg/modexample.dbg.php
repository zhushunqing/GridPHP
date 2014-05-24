<?php
	//modexample模块方法测试代码
	print_r($args); //获取到的GET参数在$args数组变量里
	
	//$rs[] = &$this->hello1('Jack'); //通过$this->引用模块自身方法测试
	
	$this->setHTTP('hello1', 2);
	$this->setHTTP('hello2', 2);
	$this->setHTTP('hello3', 2);

	$rs[] = &$this->hello1('Jack'); //通过$this->引用模块自身方法测试
	$rs[] = &$this->hello2('Rose');
	$rs[] = &$this->hello3();

	$this->getHTTP();

	print_r($rs);

?>