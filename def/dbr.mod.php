<?php
/**
* GridPHP database route基础类
* @author ZhuShunqing
*/
class gridphp_dbr extends gridphp_module{

	/**
	* 加载指定单元数据库对象
	* @param string $unit 数据库单元
	* @return DB 引用
	*/
	function &loadDB($unit){ return $this->_callImplements(); }

	/**
	* 设置指定单元数据库配置
	* @param string $unit 数据库单元
	* @return DB 引用
	*/
	function &setDB($unit){ return $this->_callImplements(); }

	/**
	* 数据库开始事务 boolean begin()
	* @return boolean
	*/
	function &begin($condition){ return $this->_callImplements(); }
	
	/**
	* 回滚数据库 boolean rollback()
	* @return boolean
	*/
	function &rollback($condition){ return $this->_callImplements(); }
	
	/**
	* 提交数据库事务 boolean commit()
	* @return boolean
	*/
	function &commit($condition) { return $this->_callImplements(); }

	/**
	* sql查询
	* @param string $sql 查询语句
	* @param array $condition 路由条件值
	* @param string $rw 'r'从库 'w'主库
	* @return resource link
	*/
	function &query($sql, $condition, $rw = 'r'){ return $this->_callImplements(); }

	/**
	* 受影响行数
	* @param string $sql 查询语句
	* @param array $condition 路由条件值
	* @param string $rw 'r'从库 'w'主库
	* @return int
	*/
	function &query_affected_rows($condition, $rw = 'w'){ return $this->_callImplements(); }

	/**
	* 高级查询测试
	* @param string $table 表名
	* @param array $fields 选择字段
	* @param array $condition 查询条件 array('uid' => array('>', 1000010)[,...]) 字段 => (运算符, 比较值),
	* @return string sql
	*/
	function &query_test($table, $fields, $condition){ return $this->_callImplements(); }

	/**
	* sql高级查询-多条记录
	* @param string $table 表名
	* @param array $fields 选择字段
	* @param array $condition 查询条件 array('uid' => array('>', 1000010)[,...]) 字段 => (运算符, 比较值),
	* @return resource link
	*/
	function &query_all($table, $fields, $condition, $cachetime = null){ return $this->_callImplements(); }

	/**
	* sql高级查询-单条记录
	* @param string $table 表名
	* @param array $fields 选择字段
	* @param array $condition 查询条件 array('uid' => array('>', 1000010)[,...]) 字段 => (运算符, 比较值),
	* @return resource link
	*/
	function &query_one($table, $fields, $condition, $cachetime = null){ return $this->_callImplements(); }

	/**
	* 查询记录数
	* @param string $table 表名
	* @param array $condition 查询条件
	* @param string $cachetime 缓存时间
	* @return int
	*/
	function &query_count($table, $condition, $cachetime = null){ return $this->_callImplements(); }

	/**
	* 取出一条结果
	* @param string $sql 查询语句
	* @param array condition 路由条件值
	* @param string $cachetime 缓存时间
	* @return array
	*/
	function &fetch($sql = null, $condition, $cachetime = null){ return $this->_callImplements(); }

	/**
	* 取回全部结果
	* @param string $sql 查询语句
	* @param array condition 路由条件值
	* @param string $cachetime 缓存时间
	* @return array
	*/
	function &fetch_all($sql = null, $condition, $cachetime = null){ return $this->_callImplements(); }

	/**
	* 插入一条记录
	* @param string $table 表名
	* @param array $row 记录值
	* @param array $duprow 主键重复时覆盖值
	* @return resource link
	*/
	function &insert($table, $row, $duprow = null){ return $this->_callImplements(); }

	/**
	* 插入一条记录
	* @param string $table 表名
	* @param array $row 记录值
	* @param array $condition 查询条件
	* @return resource link
	*/
	function &update($table, $row, $condition){ return $this->_callImplements(); }

	/**
	* 替换一条记录replace into方法
	* @param string $table 表名
	* @param array $row 记录值
	* @return resource link
	*/
	function replace($table, $row){ return $this->_callImplements(); }

	/**
	* 删除一条记录
	* @param string $table 表名
	* @param array $condition 查询条件
	* @return resource link
	*/
	function &delete($table, $condition){ return $this->_callImplements(); }

	/**
	* 返回最后插入记录ID
	* @return int
	*/
	function &insert_id($condition){ return $this->_callImplements(); }

	/**
	* get errors info
	* @return array
	*/
	function &get_error(){ return $this->_callImplements(); }

	/**
	* get errors info
	* @return array
	*/
	function &close(){ return $this->_callImplements(); }

}

?>
