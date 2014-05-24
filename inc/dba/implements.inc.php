<?php
/**
* GridPHP database agent模块实现类
* @author ZhuShunqing
*/
class dba_implements extends gridphp_implements{

	/**
	* 加载指定单元数据库对象
	* @param string $unit 数据库单元
	* @return DB 引用
	*/
	function &loadDB($unit){
		$conf = $this->getConf('DATABASE_UNIT', $unit);
		if(is_string($conf))
			$conf = $this->getConf('DATABASE_UNIT', $conf);
		$conf['unit'] = $unit;
		if(!isset($conf['cache'])) $conf['cache'] = $this->getConf('DEFAULT_CACHE');
		return $this->loadC(array('conn' => $unit), $conf);
	}

	/**
	* 设置指定单元数据库配置
	* @param string $unit 数据库单元
	* @return DB 引用
	*/
	function &setDB($unit){
		$conf = $this->getConf('DATABASE_UNIT', $unit);
		if(is_string($conf))
			$conf = $this->getConf('MEMCACHE_UNIT', $conf);
		$conf['unit'] = $unit;
		if(!isset($conf['cache'])) $conf['cache'] = $this->getConf('DEFAULT_CACHE');
		$this->close();
		return $this->loadC('conn', $conf);
	}

	/**
	* 选择数据库
	* @param string $dbname 数据库名
	* @return bool
	*/
	function selectdb($dbname){
		return (!$this->conn) ? false : $this->conn->selectdb($dbname);
	}


	/**
	* 关闭连接
	* @return void
	*/
	function close(){
		if($this->conn){
			$this->conn->close();
			$this->conn = null;
		}
	}
	
	/**
	* 数据库开始事务 boolean begin()
	* @return boolean
	*/
	function begin($condition) {
		return (!$this->conn) ? false : $this->conn->begin();
	}
	
	/**
	* 回滚数据库 boolean rollback()
	* @return boolean
	*/
	function rollback($condition) {
		return (!$this->conn) ? false : $this->conn->rollback();
	}
	
	/**
	* 提交数据库事务 boolean commit()
	* @return boolean
	*/
	function commit($condition) {
		return (!$this->conn) ? false : $this->conn->commit();
	}
	
	/**
	* sql查询
	* @param string $sql 查询语句
	* @return resource link
	*/
	function query($sql){
		return (!$this->conn) ? false : $this->conn->query($sql);
	}

	/**
	* 受影响行数
	* @param string $sql 查询语句
	* @return resource link
	*/
	function query_affected_rows(){
		return (!$this->conn) ? false : $this->conn->query_affected_rows($sql);
	}

	/**
	* 高级查询测试
	* @param string $table 表名
	* @param array $fields 选择字段
	* @param array $condition 查询条件 array('uid' => array('>', 1000010)[,...]) 字段 => (运算符, 比较值),
	* @return string sql
	*/
	function query_test($table, $fields, $condition){
		return (!$this->conn) ? false : $this->conn->query_test($table, $fields, $condition);
	}

	/**
	* sql高级查询-多条记录
	* @param string $table 表名
	* @param array $fields 选择字段
	* @param array $condition 查询条件 array('uid' => array('>', 1000010)[,...]) 字段 => (运算符, 比较值),
	* @return resource link
	*/
	function query_all($table, $fields, $condition, $cachetime = null){
		return (!$this->conn) ? false : $this->conn->query_all($table, $fields, $condition, $cachetime);
	}

	/**
	* sql高级查询-单条记录
	* @param string $table 表名
	* @param array $fields 选择字段
	* @param array $condition 查询条件 array('uid' => array('>', 1000010)[,...]) 字段 => (运算符, 比较值),
	* @return resource link
	*/
	function query_one($table, $fields, $condition, $cachetime = null){
		return (!$this->conn) ? false : $this->conn->query_one($table, $fields, $condition, $cachetime);
	}

	/**
	* 查询记录数
	* @param string $table 表名
	* @param array $condition 查询条件
	* @param string $cachetime 缓存时间
	* @return int
	*/
	function query_count($table, $condition = null, $cachetime = null){
		return (!$this->conn) ? false : $this->conn->query_count($table, $condition, $cachetime);
	}

	/**
	* 取出一条结果
	* @param string $sql 查询语句
	* @param string $cachetime 缓存时间
	* @return array
	*/
	function fetch($sql = null, $cachetime = null){
		return (!$this->conn) ? false : $this->conn->fetch($sql, $cachetime);
	}

	/**
	* 取回全部结果
	* @param string $sql 查询语句
	* @param int $limit 最大返回条数
	* @param string $cachetime 缓存时间
	* @return array
	*/
	function fetch_all($sql = null, $cachetime = null){
		return (!$this->conn) ? false : $this->conn->fetch_all($sql, $cachetime);
	}

	/**
	* 插入一条记录
	* @param string $table 表名
	* @param array $row 记录值
	* @param array $duprow 主键重复时覆盖值
	* @return resource link
	*/
	function insert($table, $row, $duprow = null){
		return (!$this->conn) ? false : $this->conn->insert($table, $row, $duprow);
	}

	/**
	* 插入一条记录
	* @param string $table 表名
	* @param array $row 记录值
	* @param array $condition 查询条件
	* @return resource link
	*/
	function update($table, $row, $condition){
		return (!$this->conn) ? false : $this->conn->update($table, $row, $condition);
	}

	/**
	* 替换一条记录replace into方法
	* @param string $table 表名
	* @param array $row 记录值
	* @return resource link
	*/
	function replace($table, $row){
		return (!$this->conn) ? false : $this->conn->replace($table, $row, $condition);
	}

	/**
	* 删除一条记录
	* @param string $table 表名
	* @param array $condition 查询条件
	* @return resource link
	*/
	function delete($table, $condition){
		return (!$this->conn) ? false : $this->conn->delete($table, $condition);
	}

	/**
	* 返回最后插入记录ID
	* @param string $table 表名
	* @param array $row 记录值
	* @param array $condition 查询条件
	* @return resource link
	*/
	function insert_id(){
		return (!$this->conn) ? false : $this->conn->insertid();
	}

	/**
	* return error
	* @return array
	*/
	function get_error(){
		return (!$this->conn) ? false : $this->conn->get_error();
	}
}

?>