<?php
/**
 * GRIDPHP 模块开发示例
 * @author: ZhuShunqing
 */
class gridphp_modexample extends gridphp_module{

    /**
    * 定义一个方法接口1
    * @param string $s
    * @return void
    */ 
    function &hello1($s){ return $this->_callImplements(); }

    /**
    * 定义一个方法接口2
    * @param string $s
    * @return void
    */ 
    function &hello2($s){ return $this->_callImplements(); }

    /**
    * 定义一个方法接口3
    * @return void
    */ 
    function &hello3(){ return $this->_callImplements(); }
    
}
