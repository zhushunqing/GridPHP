<?php
/**
 * log接口
 * @author ZhuShunqing 
 */
class log_ajax extends gridphp_log{ //ajax接口类命名规则: <mod>_ajax 继承所属mod gridphp_<mod>

	function _Init_($args){ //处理接收到的参数
		$this->uid = $args['gridphp_ajax_uid']; //当前登录用户uid
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
