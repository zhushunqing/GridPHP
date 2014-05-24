<?php
/**
* GridPHP database route模块实现类
* @author ZhuShunqing
*/
class dbr_implements extends gridphp_implements{

	/**
	* 加载指定单元数据库对象
	* @param string $unit 数据库单元
	* @return DB 引用
	*/
	function &loadDB($unit){
		$conf = $this->getConf('ROUTER_UNIT', $unit);
		if(is_string($conf))
			$conf = $this->getConf('ROUTER_UNIT', $conf);
		return $this->loadC(array('router' => $unit), $conf);
	}

	/**
	* 设置指定单元数据库配置
	* @param string $unit 数据库单元
	* @return DB 引用
	*/
	function &setDB($unit){
		$conf = $this->getConf('ROUTER_UNIT', $unit);
		if(is_string($conf))
			$conf = $this->getConf('ROUTER_UNIT', $conf);
		$this->close();
		return $this->loadC('router', $conf);
	}
	
	/**
	* 数据库开始事务 boolean begin()
	* @return boolean
	*/
	function begin($condition) {
		return (!$this->router) ? false : $this->router->begin($condition);
	}
	
	/**
	* 回滚数据库 boolean rollback()
	* @return boolean
	*/
	function rollback($condition) {
		return (!$this->router) ? false : $this->router->rollback($condition);
	}
	
	/**
	* 提交数据库事务 boolean commit()
	* @return boolean
	*/
	function commit($condition) {
		return (!$this->router) ? false : $this->router->commit($condition);
	}
	
	/**
	* sql查询
	* @param string $sql 查询语句
	* @param array $condition 路由条件值
	* @param string $rw 'r'从库 'w'主库
	* @return resource link
	*/
	function query($sql, $condition, $rw = 'r'){
		return (!$this->router) ? false : $this->router->query($sql, $condition, $rw);
	}

	/**
	* 受影响行数
	* @param string $sql 查询语句
	* @param array $condition 路由条件值
	* @param string $rw 'r'从库 'w'主库
	* @return int
	*/
	function query_affected_rows($condition, $rw = 'w'){
		return (!$this->router) ? false : $this->router->query_affected_rows($condition, $rw);
	}

	/**
	* 高级查询测试
	* @param string $table 表名
	* @param array $fields 选择字段
	* @param array $condition 查询条件 array('uid' => array('>', 1000010)[,...]) 字段 => (运算符, 比较值),
	* @return string sql
	*/
	function query_test($table, $fields, $condition){
		return (!$this->router) ? false : $this->router->query_test($table, $fields, $condition);
	}

	/**
	* sql高级查询-多条记录
	* @param string $table 表名
	* @param array $fields 选择字段
	* @param array $condition 查询条件 array('uid' => array('>', 1000010)[,...]) 字段 => (运算符, 比较值),
	* @return resource link
	*/
	function query_all($table, $fields, $condition, $cachetime = null){
		return (!$this->router) ? false : $this->router->query_all($table, $fields, $condition, $cachetime);
	}

	/**
	* sql高级查询-单条记录
	* @param string $table 表名
	* @param array $fields 选择字段
	* @param array $condition 查询条件 array('uid' => array('>', 1000010)[,...]) 字段 => (运算符, 比较值),
	* @return resource link
	*/
	function query_one($table, $fields, $condition, $cachetime = null){
		return (!$this->router) ? false : $this->router->query_one($table, $fields, $condition, $cachetime);
	}

	/**
	* 查询记录数
	* @param string $table 表名
	* @param array $condition 查询条件
	* @param string $cachetime 缓存时间
	* @return int
	*/
	function query_count($table, $condition = null, $cachetime = null){
		return (!$this->router) ? false : $this->router->query_count($table, $condition, $cachetime);
	}

	/**
	* 取出一条结果
	* @param string $sql 查询语句
	* @param string $cachetime 缓存时间
	* @return array
	*/
	function fetch($sql = null, $condition, $cachetime = null){
		return (!$this->router) ? false : $this->router->fetch($sql, $condition, $cachetime);
	}

	/**
	* 取回全部结果
	* @param string $sql 查询语句
	* @param int $limit 最大返回条数
	* @param string $cachetime 缓存时间
	* @return array
	*/
	function fetch_all($sql = null, $condition, $cachetime = null){
		return (!$this->router) ? false : $this->router->fetch_all($sql, $condition, $cachetime);
	}

	/**
	* 插入一条记录
	* @param string $table 表名
	* @param array $row 记录值
	* @param array $duprow 主键重复时覆盖值
	* @return resource link
	*/
	function insert($table, $row, $duprow = null){
		return (!$this->router) ? false : $this->router->insert($table, $row, $duprow);
	}

	/**
	* 插入一条记录
	* @param string $table 表名
	* @param array $row 记录值
	* @param array $condition 查询条件
	* @return resource link
	*/
	function update($table, $row, $condition){
		return (!$this->router) ? false : $this->router->update($table, $row, $condition);
	}

	/**
	* 替换一条记录replace into方法
	* @param string $table 表名
	* @param array $row 记录值
	* @return resource link
	*/
	function replace($table, $row){
		return (!$this->router) ? false : $this->router->replace($table, $row, $condition);
	}

	/**
	* 删除一条记录
	* @param string $table 表名
	* @param array $condition 查询条件
	* @return resource link
	*/
	function delete($table, $condition){
		return (!$this->router) ? false : $this->router->delete($table, $condition);
	}

	/**
	* 返回最后插入记录ID
	* @param string $table 表名
	* @param array $row 记录值
	* @param array $condition 查询条件
	* @return resource link
	*/
	function insert_id($condition){
		return (!$this->router) ? false : $this->router->insertid($condition);
	}

	/**
	* 按分库字段分组，一般用于in查询
	* @param array or int $ary 传入的1个或多个in字段值
	* @return array 返回分好库的数组
	*/
	function get_key_group($ary){
		return (!$this->router) ? false : $this->router->get_key_group($ary);
	}
	/**
	* return error
	* @return array
	*/
	function get_error(){
		return (!$this->router) ? false : $this->router->get_error();
	}

	/**
	* 关闭连接
	* @return void
	*/
	function close(){
		if($this->router){
			$this->router->close();
			$this->router = null;
		}
	}

}

?>