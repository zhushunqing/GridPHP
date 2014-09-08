<?php
/**
* GridPHP 数据库连接类
* @author ZhuShunqing
* @package inc\dba
*/
class dba_conn{
	var $conn, $qid, $unit, $cache, $error;
	var $conf, $host, $port, $user, $pwd, $dbname;

	/**
	* 初始化构造
	* @param array $conf DB配置
	*/
	function _Init_($conf){
		$this->conf = $conf;
		$this->unit = $conf['unit'];
		$this->host = $conf['host'];
		$this->port = $conf['port'];
		$this->user = $conf['user'];
		$this->pwd = $conf['pwd'];
		$this->dbname = isset($conf['db']) ? $conf['db'] : null;
		$this->cache = isset($conf['cache']) ? $this->memcd->loadMemc($conf['cache']) : null;
		$this->error = array();
		$this->lastquery = null;
	}

	/**
	* 连接DB
	* @return void
	*/
	function connect(){
		if (!is_resource($this->conn) || !mysql_ping($this->conn)){
			$this->close();
			$this->conn = @mysql_connect($this->host.':'.$this->port, $this->user, $this->pwd);
			if(is_resource($this->conn))
				mysql_query("SET NAMES 'UTF8'", $this->conn);
		}
		if(is_resource($this->conn)){
			if($this->dbname){
				$selectdb = mysql_select_db($this->dbname, $this->conn);
				if(!$selectdb)
					$this->put_error('Select db error');
			}
		}else{
			$this->put_error('Connect error: ' . $this->host.':'.$this->port . ' ' . $this->user);
		}
	}

	/**
	* 选择数据库
	* @param string $dbname 数据库名 为空恢复默认数据库
	* @return bool
	*/
	function selectdb($dbname = null){
		if($dbname)
			$this->dbname = $dbname;
		else
			$this->dbname = $this->conf['db'];
	}
	
	/**
	* 数据库开始事务 boolean begin()
	* @return boolean
	*/
	function begin() {
		$sql = "begin";
        if($this->lastquery != $sql)
        	return $this->query($sql);
		return true;
	}
	
	/**
	* 回滚数据库 boolean rollback()
	* @return boolean
	*/
	function rollback() {
		$sql = "rollback";
        if($this->lastquery != $sql)
        	return $this->query($sql);
		return true;
	}
	
	/**
	* 提交数据库事务 boolean commit()
	* @return boolean
	*/
	function commit() {
		$sql = "commit";
        if($this->lastquery != $sql)
        	return $this->query($sql);
		return true;
	}
	
	/**
	* sql查询
	* @param string $sql 查询语句
	* @return resource link
	*/
	function query($sql){
		$this->connect();
		if(!$this->conn)
			return false;
		$sql = trim($sql);

		$info = array(
			'unit'	=> $this->unit,
			'port'	=> $this->port,
			'host'	=> $this->host,
			'dbname'=> $this->dbname
		);
		$debug = http_build_query($info) . "\n";

		$debug .= "query sql: {$sql}\n";
		$this->utility->setTimerPoint('db_query_' . $this->unit);

		$this->qid = mysql_query($sql, $this->conn) ;
		if(!$this->qid)
			$this->put_error($sql);

		$debug .= 'query timer: ' . $this->utility->getTimerDiff('db_query_' . $this->unit) . "ms\n";
		$this->debug->dump($debug, 88);
		$this->lastquery = $sql;
		return $this->qid ;
	}

	/**
	* 受影响行数
	* @param string $sql 查询语句
	* @return resource link
	*/
	function query_affected_rows(){
		if(!$this->conn)
			return false;
		return mysql_affected_rows($this->conn);
	}

	/**
	* 取出一条结果
	* @param string $sql 查询语句
	* @param string $cachetime 缓存时间
	* @return array
	*/
	function fetch($sql = null, $cachetime = null){
		if($sql){

			$sql = $this->parse_limit($sql);
			$rs = false;

			//取cache
			if($cachetime && $this->cache){
				$cachekey = $this->cache_key($sql);
				$rs = $this->cache->get($cachekey);
				$this->debug->dump("Cache key => {$cachekey}\n Cache Val => " . var_export($rs, 1), 88);
			}

			if($rs === false){
				$this->query($sql);
				if(is_resource($this->qid))
					$rs = mysql_fetch_assoc($this->qid);
				//写cache
				if($rs && $cachetime && $this->cache)
					$this->cache->set($cachekey, $rs, $cachetime);
			}

		}else if(is_resource($this->qid)){
			$rs = mysql_fetch_assoc($this->qid);
		}else{
			$rs = null;
		}
		return $rs;
	}

	/**
	* 取回全部结果
	* @param string $sql 查询语句
	* @param string $cachetime 缓存时间
	* @return array
	*/
	function fetch_all($sql = null, $cachetime = null){
		$rs = false;
		if($sql){
			$sql = $this->parse_limit($sql);

			//取cache
			if($cachetime && $this->cache){
				$cachekey = $this->cache_key($sql);
				$rs = $this->cache->get($cachekey);
				$this->debug->dump("Cache key => {$cachekey}\n Cache Val => " . var_export($rs, 1), 88);
			}

			if($rs === false)
				$this->query($sql);
		}

		if($rs === false){
			$rs = array();
			while($r = $this->fetch())
				$rs[] = $r;
			//写cache
			if($cachetime && $this->cache)
				$this->cache->set($cachekey, $rs, $cachetime);
		}
		return $rs;
	}

	/**
	* 高级查询测试
	* @param string $table 表名
	* @param array $fields 选择字段
	* @param array $condition 查询条件 array('uid' => array('>', 1000010)[,...]) 字段 => (运算符, 比较值),
	* @return string sql
	*/
	function query_test($table, $fields, $condition){
		$sql = $this->parse_condition_sql($table, $fields, $condition);
		$sql = $this->parse_limit($sql);
		return $sql;
	}

	/**
	* 高级查询-单条记录
	* @param string $table 表名
	* @param array $fields 选择字段
	* @param array $condition 查询条件 array('uid' => array('>', 1000010)[,...]) 字段 => (运算符, 比较值),
	* @param string $cachetime 缓存时间
	* @return resource link
	*/
	function query_one($table, $fields, $condition, $cachetime = null){
		$sql = $this->parse_condition_sql($table, $fields, $condition);
		return $this->fetch($sql,$cachetime);
	}

	/**
	* 高级查询-多条记录
	* @param string $table 表名
	* @param array $fields 选择字段
	* @param array $condition 查询条件 array('uid' => array('>', 1000010)[,...]) 字段 => (运算符, 比较值),
	* @param string $cachetime 缓存时间
	* @return resource link
	*/
	function query_all($table, $fields, $condition, $cachetime = null){
		$sql = $this->parse_condition_sql($table, $fields, $condition);
		return $this->fetch_all($sql, $cachetime);
	}

	/**
	* 查询记录数
	* @param string $table 表名
	* @param array $condition 查询条件 array('uid' => array('>', 1000010)[,...]) 字段 => (运算符, 比较值),
	* @param string $cachetime 缓存时间
	* @return int
	*/
	function query_count($table, $condition = null, $cachetime = null){
		$table = mysql_escape_string($table);
		$cond = $this->parse_condition($condition);
		$sql = "select count(*) as `count` from `{$this->dbname}`.`{$table}` {$cond}";
		$rs = $this->fetch($sql, $cachetime);
		return intval($rs['count']);
	}

	/**
	* 插入一条记录
	* @param string $table 表名
	* @param array $row 记录值
	* @param array $duprow 主键重复时覆盖值
	* @return resource link
	*/
	function insert($table, $row, $duprow = null){
		$this->connect();
		if(!$this->conn)
			return false;

		$table = mysql_escape_string($table);

		$parse = $this->parseInsertValueStr($row);
		$fields = $parse['fields'];
		$values = $parse['values'];
		$sql = "insert into $table (`$fields`) values ($values)";

		if($duprow && is_array($duprow)){
			$values = $this->parseReplaceValueStr($duprow);
			$sql .= ' on duplicate key update ' . $values;
		}

		return $this->query($sql);
	}

	/**
	* 替换一条记录replace into方法
	* @param string $table 表名
	* @param array $row 记录值
	* @return resource link
	*/
	function replace($table, $row){
		$this->connect();
		if(!$this->conn)
			return false;

		$table = mysql_escape_string($table);

		$parse = $this->parseInsertValueStr($row);
		$fields = $parse['fields'];
		$values = $parse['values'];
		$sql = "replace into $table (`$fields`) values ($values)";

		return $this->query($sql);
	}

	/**
	* 插入一条记录
	* @param string $table 表名
	* @param array $row 记录值
	* @param array $condition 查询条件 array('uid' => array('>', 1000010)[,...]) 字段 => (运算符, 比较值),
	* @return resource link
	*/
	function update($table, $row, $condition){
		$this->connect();
		if(!$this->conn)
			return false;

		$table = mysql_escape_string($table);
		$values = $this->parseReplaceValueStr($row);
		$cond = $this->parse_condition($condition);
		if(!empty($cond)){ //必须有条件
			$sql = "update {$table} set {$values} {$cond}";
			$sql = $this->parse_limit($sql); //必须有limit
			return $this->query($sql);
		}else{
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
		$table = mysql_escape_string($table);
		$cond = $this->parse_condition($condition);
		if(!empty($cond)){ //必须有条件
			$sql = "delete from {$table} {$cond}";
			$sql = $this->parse_limit($sql); //必须有limit
			return $this->query($sql);
		}else{
			return false;
		}
	}

	/**
	* 返回最后插入记录ID
	* @return int
	*/
	function insert_id(){
		$this->connect();
		if(!$this->conn)
			return false;
		return mysql_insert_id($this->conn);
	}

	/**
	* 解析sql查询条件
	*/
	private function parse_condition_sql($table, $fields, $condition){
		$table = mysql_escape_string($table);
		$select = '';
		foreach($fields as $k => $v){
			if(is_numeric($k)){
				$select .= ",`{$v}`";
			}else{
				$select .= ",{$k} as `{$v}`";
			}
		}
		$select = mysql_escape_string(substr($select, 1));
		$cond = $this->parse_condition($condition);
		$sql = "select {$select} from `{$this->dbname}`.`{$table}` {$cond}";
		return $sql;
	}
	private function parse_condition($condition){
		$cond_array = array(
			'where' => null,
			'group' => null,
			'order' => null,
			'limit' => null,
		);

		if(is_array($condition))
		foreach($condition as $cond => $tion){
			$cond = strtolower($cond);
			switch($cond){

				case 'where':
					$cond_where = array();
					foreach($tion as $field => $where){

						//查询条件
						if(is_array($where)){

							//处理数组下标型字段查询条件
							if(count($where) == 3)
								$field = array_shift($where);

							$op = strtolower($where[0]);
							$val = $where[1];
							switch($op){
								case 'like':
									$op = ' like ';
								case '=':
								case '!=':
								case '>=':
								case '<=':
								case '>':
								case '<':
									if(is_string($val))
										$val = "'" . mysql_escape_string($val) . "'";
									$cond_where[] = "`{$field}`{$op}{$val}";
								break;
								
								case 'in':
								case 'not in':
									if(is_array($val)){
										for($i = 0; $i < count($val); $i ++)
											if(is_string($val[$i]))
												$val[$i] = "'" . mysql_escape_string($val[$i]) . "'";
										$val = implode(',', $val);
										$cond_where[] = "`{$field}` {$op} ({$val})";
									}
								break;
							}

						//逻辑与或非，括号符
						}else if(is_string($where)){
							$where = strtolower($where);
							if(in_array($where, array('and', 'or', 'not', '(', ')')))
								$cond_where[] = $where;
						}

					}

					if($cond_where){
						$where = implode(' ', $cond_where);
						$cond_array['where'] = 'where ' . $where;	
					}

				break;

				case 'group':
					if(is_array($tion)){
						for($i = 0; $i < count($tion); $i ++){
							$tion[$i] = mysql_escape_string($tion[$i]);
							$tion[$i] = "`{$tion[$i]}`";
						}
						$cond_array['group'] = 'group by ' . implode(',', $tion);
					}
				break;

				//order by 排序
				case 'order':
					$cond_order = array();
					foreach($tion as $field => $order){
						$field = mysql_escape_string($field);
						if(!in_array($order, array('asc', 'desc')))
							$order = '';
						$cond_order[] = "`{$field}` {$order}";
					}
					if($cond_order){
						$order = implode(',', $cond_order);
						$cond_array['order'] = 'order by ' . $order;	
					}

				break;

				//limit条数
				case 'limit':
					if(is_array($tion)){
						for($i = 0; $i < 2; $i ++)
							if(isset($tion[$i]))
								//强制转int型
								$tion[$i] = intval($tion[$i]);
						array_splice($tion, 2);
						$cond_array['limit'] = 'limit ' . implode(',', $tion);
					}else if(is_numeric($tion)){
						$cond_array['limit'] = 'limit ' . $tion;
					}
				break;

			}
		}
		
		foreach($cond_array as $k => $v)
			if(!$v)
				unset($cond_array[$k]);
		$cond_sql = null;
		if(count($cond_array) > 0)
			$cond_sql = implode(' ', $cond_array);
		return $cond_sql;
	}

	/**
	* auto parse limit
	* @return string
	*/
	private function parse_limit($sql, $limit = 1){
		$sql = trim($sql);
		$limit = intval($limit);
		$tmp = substr($sql, -20);
		$tmp = strtolower($tmp); //strtolower and strpos function compatible php4, not php5 stripos function case-insensitive.
		if(!strpos($tmp, ' limit '))
			$sql .= ' limit ' . $limit;
		return $sql;
	}

	/**
	* 根据sql生成cachekey
	*/
	private function cache_key($sql){
		return 'db_' . md5(trim($sql));
	}

	/**
	* error log
	* @return void
	*/
	private function put_error($sql = ''){
		$trace = debug_backtrace();
		$debug = array();
		foreach($trace as $i => $t){
			if(is_array($t)){
				$debug[] = array(
					'file'	=> @$t['file'],
					'line'	=> @$t['line'],
					'func'	=> @$t['function'],
					'args'	=> '[' . substr(@implode($t['args'], ','), 0, 300) . ']',
				);
			}else{
				$debug[] = $t;
			}
		}
		$mixed = array(
			'time' => GRIDPHP_DATE_NOW,
			'server'=> $this->parent->parent->getServerIP(),
			'remote'=> $this->parent->parent->getClientIP(), //$_SERVER['REMOTE_ADDR'],
			'sql'	=> $sql,
			'error'	=> is_resource($this->conn) ? mysql_error($this->conn) : mysql_error(),
			'errno'	=> is_resource($this->conn) ? mysql_errno($this->conn) : mysql_errno(),
			'unit'	=> $this->unit,
			'port'	=> $this->port,
			'host'	=> $this->host,
			'dbname'=> $this->dbname,
			'uri'	=> @$_SERVER["REQUEST_URI"],
			'debug'	=> json_encode($debug),
		);
		$this->debug->dump($mixed, 88);
		$this->error[] = $mixed;
		$this->log->writelog('err_gridphp_dba.txt', $mixed);
	}

	/**
	* return error
	* @return array
	*/
	function get_error(){
		return $this->error;
	}
	
	/**
	* 关闭连接
	* @return void
	*/
	function close(){
		if(is_resource($this->conn)){
			@mysql_close($this->conn);
			$this->conn = false;
		}
	}

	//数值类型综合判断
	private function isnumeric($v){
		$type = gettype($v);
		if($type == 'integer' || $type == 'double'){
			return true;
		// }else if($type == 'string'){
		// 	return is_numeric($v) && (strlen($v) <= 80);
		}else{
			return false;
		}
	}

	private function isexpression($v){
		return is_string($v) && preg_match('/^\(.+?\)$/', $v);
	}

	//parse insert str
	private function parseInsertValueStr($row){
		$fields = $values = array();
		foreach($row as $k => $v){
			$fields[] = mysql_escape_string($k);
			if(is_string($v))
				$values[] = mysql_escape_string($v);
			else
				$values[] = $v;
		}
		$fields = implode("`,`", $fields);
		$valuestr = '';
		foreach($values as $i => $v){
			if($this->isnumeric($v) || $this->isexpression($v))
				$valuestr .= ",{$v}";
			else
				$valuestr .= ",'{$v}'";
		}
		$valuestr = substr($valuestr, 1);
		return array(
			'fields' => $fields,
			'values' => $valuestr
		);
	}

	//parse replace str
	private function parseReplaceValueStr($row){
		$values = array();
		foreach($row as $k => $v){
			$k = mysql_escape_string($k);
			if(!$this->isnumeric($v))
				$v = mysql_escape_string($v);
			if($this->isnumeric($v) || preg_match('/^\(.+?\)$/', $v)){
				$v = str_replace("\'", "'", $v);
				$values[] = "`{$k}`={$v}";
			}else{
				$values[] = "`{$k}`='{$v}'";
			}
		}
		$values = implode(',', $values);
		return $values;
	}

}

?>