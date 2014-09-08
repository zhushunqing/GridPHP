<?php
/**
* GridPHP database route功能实现类
* @author ZhuShunqing
*/
class dbr_router{

	var $error, $conf, $conn;

	/**
	* 初始化构造
	* @param array $conf DB配置
	*/
	function _Init_($conf){
		$this->conf = $conf;
		$this->error = array();
		$this->conn = array();
	}
	
	/**
	* 数据库开始事务 boolean begin()
	* @return boolean
	*/
	function begin($condition) {
		list($dbunit, $mapid, $tabid) = $this->_getMapDB($condition, 'w');
		if($dbunit){
			$this->conn[$dbunit] = $this->dba->loadDB($dbunit);
			return $this->dba->$dbunit->begin();
		}else{
			$this->_put_error('get db route error');
			return false;
		}
	}
	
	/**
	* 回滚数据库 boolean rollback()
	* @return boolean
	*/
	function rollback($condition) {
		list($dbunit, $mapid, $tabid) = $this->_getMapDB($condition, 'w');
		if($dbunit){
			$this->conn[$dbunit] = $this->dba->loadDB($dbunit);
			return $this->dba->$dbunit->rollback();
		}else{
			$this->_put_error('get db route error');
			return false;
		}
	}

	/**
	* 提交数据库事务 boolean commit()
	* @return boolean
	*/
	function commit($condition) {
		list($dbunit, $mapid, $tabid) = $this->_getMapDB($condition, 'w');
		if($dbunit){
			$this->conn[$dbunit] = $this->dba->loadDB($dbunit);
			return $this->dba->$dbunit->commit();
		}else{
			$this->_put_error('get db route error');
			return false;
		}
	}
	
	/**
	* sql查询
	* @param string $sql 查询语句
	* @param array $condition 路由条件值
	* @param string $rw 'r'从库 'w'主库
	* @return resource link
	*/
	function query($sql, $condition, $rw = 'r'){
		list($dbunit, $mapid, $tabid) = $this->_getMapDB($condition, $rw);
		if($dbunit){
			$this->conn[$dbunit] = $this->dba->loadDB($dbunit);
			$dbname = $this->_getMapDBName($mapid, $tabid);
			$this->dba->$dbunit->selectdb($dbname);
			return $this->dba->$dbunit->query($sql);
		}else{
			$this->_put_error('get db route error');
			return false;
		}
	}

	/**
	* 受影响行数
	* @param string $sql 查询语句
	* @param array $condition 路由条件值
	* @param string $rw 'r'从库 'w'主库
	* @return int
	*/
	function query_affected_rows($condition, $rw = 'w'){
		list($dbunit, $mapid, $tabid) = $this->_getMapDB($condition, $rw);
		if($dbunit){
			$this->conn[$dbunit] = $this->dba->loadDB($dbunit);
			return $this->dba->$dbunit->query_affected_rows();
		}else{
			$this->_put_error('get db route error');
			return false;
		}
	}

	/**
	* 取出一条结果
	* @param string $sql 查询语句
	* @param string $cachetime 缓存时间
	* @return array
	*/
	function fetch($sql, $condition, $cachetime = null){
		list($dbunit, $mapid, $tabid) = $this->_getMapDB($condition, 'r');
		if($dbunit){
			$this->conn[$dbunit] = $this->dba->loadDB($dbunit);
			$dbname = $this->_getMapDBName($mapid, $tabid);
			$this->dba->$dbunit->selectdb($dbname);
			return $this->dba->$dbunit->fetch($sql, $cachetime);
		}else{
			$this->_put_error('get db route error');
			return false;
		}
	}

	/**
	* 取回全部结果
	* @param string $sql 查询语句
	* @param string $cachetime 缓存时间
	* @return array
	*/
	function fetch_all($sql, $condition, $cachetime = null){
		list($dbunit, $mapid, $tabid) = $this->_getMapDB($condition, 'r');
		if($dbunit){
			$this->conn[$dbunit] = $this->dba->loadDB($dbunit);
			$dbname = $this->_getMapDBName($mapid, $tabid);
			$this->dba->$dbunit->selectdb($dbname);
			return $this->dba->$dbunit->fetch_all($sql, $cachetime);
		}else{
			$this->_put_error('get db route error');
			return false;
		}
	}

	/**
	* 高级查询测试
	* @param string $table 表名
	* @param array $fields 选择字段
	* @param array $condition 查询条件 array('uid' => array('>', 1000010)[,...]) 字段 => (运算符, 比较值),
	* @return string sql
	*/
	function query_test($table, $fields, $condition){
		list($dbunit, $mapid, $tabid) = $this->_getMapDB($condition, 'r');
		if($dbunit){
			$this->conn[$dbunit] = $this->dba->loadDB($dbunit);
			$table = $this->_getMapTable($table, $mapid, $tabid);
			$dbname = $this->_getMapDBName($mapid, $tabid);
			$this->dba->$dbunit->selectdb($dbname);
			return $this->dba->$dbunit->query_test($table, $fields, $condition);
		}else{
			$this->_put_error('get db route error for' . $table);
			return false;
		}
	}
	
	/**
	* 高级查询-单条记录
	* @param string $table 表名
	* @param array $fields 选择字段
	* @param array $condition 查询条件 array('uid' => array('>', 1000010)[,...]) 字段 => (运算符, 比较值),
	* @return resource link
	*/
	function query_one($table, $fields, $condition, $cachetime = null){
		list($dbunit, $mapid, $tabid) = $this->_getMapDB($condition, 'r');
		if($dbunit){
			$this->conn[$dbunit] = $this->dba->loadDB($dbunit);
			$table = $this->_getMapTable($table, $mapid, $tabid);
			$dbname = $this->_getMapDBName($mapid, $tabid);
			$this->dba->$dbunit->selectdb($dbname);
			return $this->dba->$dbunit->query_one($table, $fields, $condition, $cachetime);
		}else{
			$this->_put_error('get db route error for ' . $table);
			return false;
		}
	}

	/**
	* 高级查询-多条记录
	* @param string $table 表名
	* @param array $fields 选择字段
	* @param array $condition 查询条件 array('uid' => array('>', 1000010)[,...]) 字段 => (运算符, 比较值),
	* @return resource link
	*/
	function query_all($table, $fields, $condition, $cachetime = null){
		list($dbunit, $mapid, $tabid) = $this->_getMapDB($condition, 'r');
		if($dbunit){
			$this->conn[$dbunit] = $this->dba->loadDB($dbunit);
			$table = $this->_getMapTable($table, $mapid, $tabid);
			$dbname = $this->_getMapDBName($mapid, $tabid);
			$this->dba->$dbunit->selectdb($dbname);
			return $this->dba->$dbunit->query_all($table, $fields, $condition, $cachetime);
		}else{
			$this->_put_error('get db route error for' . $table);
			return false;
		}
	}

	/**
	* 查询记录数
	* @param string $table 表名
	* @param array $condition 查询条件 array('uid' => array('>', 1000010)[,...]) 字段 => (运算符, 比较值),
	* @param string $cachetime 缓存时间
	* @return int
	*/
	function query_count($table, $condition = null, $cachetime = null){
		list($dbunit, $mapid, $tabid) = $this->_getMapDB($condition, 'r');
		if($dbunit){
			$this->conn[$dbunit] = $this->dba->loadDB($dbunit);
			$table = $this->_getMapTable($table, $mapid, $tabid);
			$dbname = $this->_getMapDBName($mapid, $tabid);
			$this->dba->$dbunit->selectdb($dbname);
			return $this->dba->$dbunit->query_count($table, $condition, $cachetime);
		}else{
			$this->_put_error('get db route error for ' . $table);
			return false;
		}
	}

	/**
	* 插入一条记录
	* @param string $table 表名
	* @param array $row 记录值
	* @param array $duprow 主键重复时覆盖值
	* @return resource link
	*/
	function insert($table, $row, $duprow = null){
		list($dbunit, $mapid, $tabid) = $this->_getMapDB($row, 'w');
		if($dbunit){
			$this->conn[$dbunit] = $this->dba->loadDB($dbunit);
			$table = $this->_getMapTable($table, $mapid, $tabid);
			$dbname = $this->_getMapDBName($mapid, $tabid);
			$this->dba->$dbunit->selectdb($dbname);
			return $this->dba->$dbunit->insert($table, $row, $duprow);
		}else{
			$this->_put_error('get db route error for ' . $table);
			return false;
		}
	}

	/**
	* 替换一条记录replace into方法
	* @param string $table 表名
	* @param array $row 记录值
	* @return resource link
	*/
	function replace($table, $row){
		list($dbunit, $mapid, $tabid) = $this->_getMapDB($row, 'w');
		if($dbunit){
			$this->conn[$dbunit] = $this->dba->loadDB($dbunit);
			$table = $this->_getMapTable($table, $mapid, $tabid);
			$dbname = $this->_getMapDBName($mapid, $tabid);
			$this->dba->$dbunit->selectdb($dbname);
			return $this->dba->$dbunit->replace($table, $row);
		}else{
			$this->_put_error('get db route error for ' . $table);
			return false;
		}
	}

	/**
	* 插入一条记录
	* @param string $table 表名
	* @param array $row 记录值
	* @param array $condition 查询条件 array('uid' => array('>', 1000010)[,...]) 字段 => (运算符, 比较值),
	* @return resource link
	*/
	function update($table, $row, $condition){
		list($dbunit, $mapid, $tabid) = $this->_getMapDB($condition, 'w');
		if($dbunit){
			$this->conn[$dbunit] = $this->dba->loadDB($dbunit);
			$table = $this->_getMapTable($table, $mapid, $tabid);
			$dbname = $this->_getMapDBName($mapid, $tabid);
			$this->dba->$dbunit->selectdb($dbname);
			return $this->dba->$dbunit->update($table, $row, $condition);
		}else{
			$this->_put_error('get db route error for' . $table);
			return false;
		}
	}

	/**
	* 删除一条记录
	* @param string $table 表名
	* @param array $condition 查询条件 array('uid' => array('>', 1000010)[,...]) 字段 => (运算符, 比较值),
	* @return resource link
	*/
	function delete($table, $condition){
		list($dbunit, $mapid, $tabid) = $this->_getMapDB($condition, 'w');
		if($dbunit){
			$this->conn[$dbunit] = $this->dba->loadDB($dbunit);
			$table = $this->_getMapTable($table, $mapid, $tabid);
			$dbname = $this->_getMapDBName($mapid, $tabid);
			$this->dba->$dbunit->selectdb($dbname);
			return $this->dba->$dbunit->delete($table, $condition);
		}else{
			$this->_put_error('get db route error for' .$table);
			return false;
		}
	}

	/**
	* 返回最后插入记录ID
	* @return int
	*/
	function insert_id($condition){
		list($dbunit, $mapid, $tabid) = $this->_getMapDB($condition, 'w');
		if($dbunit){
			$this->conn[$dbunit] = $this->dba->loadDB($dbunit);
			return $this->dba->$dbunit->insert_id();
		}else{
			$this->_put_error('get db route error');
			return false;
		}
	}

	/**
	* 按分库字段分组，一般用于in查询
	* @param array or int $ary 传入的1个或多个in字段值
	* @return array 返回分好库的数组
	*/
	function get_key_group($ary){
		if(!is_array($ary)){
			$rtype = 'int';
			$ary = array($ary);
		}else{
			$rtype = 'array';
		}
		$group = array();
		for($i = 0; $i < count($ary); $i ++){
			$mapid = false;
			$field = $ary[$i];
			if($field && isset($this->conf['mapid'])){
				$mapid = str_replace('{field}', $field, $this->conf['mapid']);
				$mapid = '$mapid=' . $mapid . ';';
				eval($mapid);
			}
			if($mapid !== false){
				$group[$mapid][] = $field;
			}
		}
		if($rtype == 'int')
			$group = array_shift(array_keys($group));
		return $group;
	}

	/**
	* error log
	* @return void
	*/
	function _put_error($sql = ''){
		$trace = debug_backtrace();
		$debug = array();
		foreach($trace as $i => $t){
			$debug[] = array(
				'file'	=> $t['file'],
				'line'	=> $t['line'],
				'func'	=> $t['function'],
				'args'	=> '[' . implode($t['args'], ',') . ']',
			);
		}
		$mixed = array(
			'time' => GRIDPHP_DATE_NOW,
			'server'=> $this->parent->parent->getServerIP(),
			'remote'=> $_SERVER['REMOTE_ADDR'],
			'sql'	=> $sql,
			'error'	=> mysql_error(),
			'errno'	=> mysql_errno(),
			'unit'	=> $this->unit,
			'port'	=> $this->port,
			'host'	=> $this->host,
			'dbname'=> $this->dbname,
			'uri'	=> $_SERVER["REQUEST_URI"],
			'debug'	=> json_encode($debug),
		);
		$this->debug->dump($mixed, 88);
		$this->error[] = $mixed;
		$this->log->writelog('err_gridphp_dbr.txt', $mixed);
	}

	/**
	* 计算分库(表)映射
	*/
	function _getMapDB($row, $rw = ''){

		$field = $this->conf['field'];
		if(!is_array($row)){
			$field = $row;
		}else if(isset($row[$field])){
			$field = $row[$field];
		}else if(isset($row['where'][$field]) && $row['where'][$field][0] == '='){
			$field = $row['where'][$field][1];
		}else if(isset($row['where'][$field]) && $row['where'][$field][0] == 'in'){
			$field = $row['where'][$field][1][0];
		}else{
			$field = false;
		}

		if($field){

			$mapid = false;
			if(isset($this->conf['mapid'])){
				$mapid = str_replace('{field}', '$field', $this->conf['mapid']);
				$mapid = '$mapid=' . $mapid . ';';
				$this->debug->dump($mapid, 89);
				eval($mapid);
				$this->debug->dump($mapid, 89);
			}

			$tabid = false;
			if(isset($this->conf['tabid'])){
				$tabid = str_replace('{field}', '$field', $this->conf['tabid']);
				$tabid = '$tabid=' . $tabid . ';';
				$this->debug->dump($tabid, 89);
				eval($tabid);
				$this->debug->dump($tabid, 89);
			}

			$dbunit = $this->conf['mapconn'][$rw][$mapid];
			return array($dbunit, $mapid, $tabid);
		}else{
			return array(false, false, false);
		}
	}

	/**
	* 生成表名映射
	*/
	function _getMapTable($table, $mapid, $tabid){
		$maptb = $this->conf['maptb'];
		$maptb = str_replace('{table}', $table, $maptb);
		$maptb = str_replace('{mapid}', $mapid, $maptb);
		$maptb = str_replace('{tabid}', $tabid, $maptb);
		return $maptb;
	}

	/**
	* 生成库名映射
	*/
	function _getMapDBName($mapid, $tabid){
		$mapdb = $this->conf['mapdb'];
		$mapdb = str_replace('{mapid}', $mapid, $mapdb);
		$mapdb = str_replace('{tabid}', $tabid, $mapdb);
		return $mapdb;
	}

	/**
	* return error
	* @return array
	*/
	function get_error(){
		$error = array();
		foreach($this->conn as $unit => $conn){
			if($conn->get_error())
				$error[$unit] = $conn->get_error();
		}
		if($this->error)
			$error['router'] = $this->error;
		return $error;
	}

	/**
	* return error
	* @return array
	*/
	function close(){
		foreach($this->conn as $unit => $conn)
			$conn->close();
		$this->conn = array();
	}

}

?>