<?php
/**
 * log接口
 * @author ZhuShunqing 
 */
class modexample_ajax extends gridphp_modexample{ //ajax接口类命名规则: <mod>_ajax 继承所属mod gridphp_<mod>

	function _Init_($args){ //处理接收到的参数
		$this->uid = $args['gridphp_ajax_uid']; //当前登录用户uid
	}

    /**
    * 定义一个方法接口1
    * @param string $s
    * @return void
    */ 
    function hello1(){
    	$name = $this->getParam('name');
    	if($name){
			$data = parent::hello1($name);
			//返回正常请求状态和结果
			return $this->ajaxData(GRIDPHP_AJAX_RET_CODE_SUCC, 'GRIDPHP_AJAX_RET_CODE_SUCC', $data);
		}else{
			//参数错误
			return $this->ajaxData(GRIDPHP_AJAX_ERR_BAD_REQUEST, 'GRIDPHP_AJAX_ERR_BAD_REQUEST');
		}
    }

    /**
    * 定义一个方法接口2
    * @param string $s
    * @return void
    */ 
    function hello2(){
    	$name = $this->getParam('name');
    	if($name){
			$data = parent::hello2($name);
			//返回正常请求状态和结果
			return $this->ajaxData(GRIDPHP_AJAX_RET_CODE_SUCC, 'GRIDPHP_AJAX_RET_CODE_SUCC', $data);
		}else{
			//参数错误
			return $this->ajaxData(GRIDPHP_AJAX_ERR_BAD_REQUEST, 'GRIDPHP_AJAX_ERR_BAD_REQUEST');
		}
    }

    /**
    * 定义一个方法接口3
    * @return void
    */ 
    function hello3(){
		$data = parent::hello3($name);
		return $this->ajaxData(GRIDPHP_AJAX_RET_CODE_SUCC, 'GRIDPHP_AJAX_RET_CODE_SUCC', $data);
    }

	/**
	* checkpoint code标识计数
	* @param int $c code
	*/
	function cpc(){
		$codes = $this->getParam('c', 'intary'); //类型
		if($codes){
			$data = array();
			foreach($codes as $i => $code) {
				$data[$code] = $this->checkpoint_code($code, $this->uid);
			}
			//返回正常请求状态和结果
			return $this->ajaxData(GRIDPHP_AJAX_RET_CODE_SUCC, 'GRIDPHP_AJAX_RET_CODE_SUCC', $data);
		}else{
			//参数错误
			return $this->ajaxData(GRIDPHP_AJAX_ERR_BAD_REQUEST, 'GRIDPHP_AJAX_ERR_BAD_REQUEST');
		}
	}

}
?>
