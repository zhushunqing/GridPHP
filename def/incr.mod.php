<?php
/**
* GridPHP increment自增ID模块
* @author ZhuShunqing
* @package def
*/
class gridphp_incr extends gridphp_module{

	/**
	* 生成一个key的普通自增ID
	* @param string $key 标识
	* @param int $step 步长
	* @return bigint
	*/
	public function &get_incr_id($key, $step = 1){ return $this->_callImplements(); }

	/**
	* 生成一个key的区域唯一ID
	* @param string $key 标识
	* @return bigint
	*/
	public function &get_zone_id($key){ return $this->_callImplements(); }

	
	/**
	* 反算id所属地区zone
	* @param int $id
	* @return int
	*/
	public function &get_id_zone($id){ return $this->_callImplements(); }

	/**
	* 通过name取incr数值
	* @param string $name
	* @return int
	*/
	public function &get_incr_value($name) { return $this->_callImplements(); }
}

?>
