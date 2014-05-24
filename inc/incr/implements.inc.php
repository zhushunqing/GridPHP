<?php
/**
* GridPHP increment自增ID模块
* @author ZhuShunqing
*/
class incr_implements extends gridphp_implements{
	var $db, $table;
	var $zone_space, $zone_id;

	function _Init_(){
		$this->table = $this->getConf('INCR_TB');
		$this->zone_space = $this->getConf('ZONE_SP');
		$this->zone_id = $this->getConf('ZONE_ID');
		$unit = $this->getConf('INCR_DB');
		$this->db = $this->dba->loadDB($unit);
	}

	/**
	* 生成一个key的普通自增ID
	* @param string $key 标识
	* @param int $step 步长
	* @return bigint
	*/
	function get_incr_id($key, $step = 1){
		$sql = "INSERT INTO `{$this->table}` (`name`) VALUES('{$key}') ON DUPLICATE KEY UPDATE `incr` = LAST_INSERT_ID(`incr` + {$step})";
		$this->db->query($sql);
		return $this->db->insert_id();
	}

	/**
	* 生成一个key的区域唯一ID
	* @param string $key 标识
	* @return bigint
	*/
	function get_zone_id($key){
		$incr = $this->get_incr_id($key);
		return $incr * $this->zone_space + $this->zone_id;
	}

	/**
	* 反算id所属地区zone
	* @param int $id
	* @return int
	*/
	function get_id_zone($id){
		return $id % $this->zone_space;
	}
	
	/**
	* 通过name取incr数值
	* @param string $name
	* @return int
	*/
	function get_incr_value($name) {
		$sql = "SELECT incr FROM ".$this->table. " WHERE name='".$name."'";
		$result = $this->db->fetch($sql);
		$incr = $result['incr'];
		return $incr;
	}

}

?>
