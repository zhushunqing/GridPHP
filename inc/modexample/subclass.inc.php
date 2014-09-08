<?php

/**
* 类命名为 <模块名>_<子类名> 
*/
class modexample_subclass{
	
	/**
	* 初始化方法
	* 请注意：不要使用默认构造函数 __construct() 或类同名函数modexample_subclass()初始化
	* 引用loadC时可先传参 ->loadC('subclass', 'I am  hello3');
	*/	
	public function _Init_($val) {
		//"Subclass _Init_ Once\n";
		$this->val = $val;
	}

	public function do_something(){
		return "subclass do something " . $this->val;
	}

}

?>