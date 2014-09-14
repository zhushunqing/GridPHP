<?php

/**
* 类命名为 <模块名>_implements 同时继承 gridphp_implements
*/
class modexample_implements extends gridphp_implements{
	
	/**
	* 初始化方法
	* 请注意：不要使用默认构造函数 __construct() 或类同名函数modexample_implements()初始化
	* _Init_()方法遵从Lazy Initialization延迟初始化原则，在初次调用时才初始化所需资源1次，之后调用不重复执行。
	*/	
	public function _Init_() {
		//"implements _Init_ Once\n";
	}

    /**
    * 方法接口1实现
    * @param string $s
    * @return void
    */ 
    public function hello(){
        return 'hello done';
    }

    /**
    * 方法接口1实现
    * @param string $s
    * @return void
    */ 
    public function hello1($s){
        sleep(1);
		return "1. Hello1, I am {$s}, and I sleeped 1 sec. IP:" . $this->parent->parent->getServerIP();
	}

    /**
    * 方法接口2实现
    * @param string $s
    * @return string
    */ 
    public function hello2($s){
        sleep(2);
		return "2. Hello2, I am {$s}, and I sleeped 2 sec. IP:" . $this->parent->parent->getServerIP();
	}

    /**
    * 方法接口3实现
    * @param string $s
    * @return string
    */ 
    public function hello3(){
        sleep(3);
		//调用subclass方法
        $this->loadC('subclass', 'I am hello3'); //加载subclass子类
		return "3. Hello3, " . $this->subclass->do_something() . ", and I sleeped 3 sec. IP:" . $this->parent->parent->getServerIP();
	}

}

?>