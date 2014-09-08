<?php
/**
 * GRIDPHP 模块开发示例
 * @author: ZhuShunqing
 * @package mod
 */
class gridphp_modexample extends gridphp_module{

    /**
    * 定义了一个方法接口hello1
    * @param string $s
    * @return void
    */ 
    public function &hello1($s){ return $this->_callImplements(); }

    /**
    * 定义了一个方法接口hello2
    * @param string $s
    * @return void
    */ 
    public function &hello2($s){ return $this->_callImplements(); }

    /**
    * 定义了一个方法接口hello3
    * @return void
    */ 
    public function &hello3(){ return $this->_callImplements(); }
    
}
