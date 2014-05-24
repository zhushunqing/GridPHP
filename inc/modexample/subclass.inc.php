<?php

/**
* 类命名为 <模块名>_<子类名> 
*/
class modexample_subclass{
	
	/**
	* 初始化方法
	* 请注意：不要使用默认构造函数 __construct() 或类同名函数modexample_implements()初始化
	* _Init_()方法遵从Lazy Initialization延迟初始化原则，在初次调用时才初始化所需资源1次，之后调用不重复执行。
	*/	
	function _Init_() {
		//"Subclass _Init_ Once\n";
	}

	function do_something(){
		return "subclass do something";
	}

}

?>