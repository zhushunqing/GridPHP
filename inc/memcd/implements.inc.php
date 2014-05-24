<?php
/**
* GridPHP Memcache基础类
* @author ZhuShunqing
*/
class memcd_implements extends gridphp_implements{

	/**
	* 加载指定单元数据库对象
	* @param string $unit 数据库单元
	* @return DB 引用
	*/
	function loadMemc($unit){
		$conf = $this->getConf('MEMCACHE_UNIT', $unit);
		if(is_string($conf))
			$conf = $this->getConf('MEMCACHE_UNIT', $conf);
		return $this->loadC(array('cache' => $unit), $conf, $unit);
	}

	/**
	* 设置指定单元数据库配置
	* @param string $unit 数据库单元
	* @return DB 引用
	*/
	function setMemc($unit){
		$conf = $this->getConf('MEMCACHE_UNIT', $unit);
		if(is_string($conf))
			$conf = $this->getConf('MEMCACHE_UNIT', $conf);
		if($this->cache){
			$this->cache->close();
			$this->cache = null;
		}
		return $this->loadC('cache', $conf, $unit);
	}

	/**
	* 关闭连接
	* @return void
	*/
	function close(){
		if($this->cache)
			return $this->cache->close();
		else
			return false;
	}

	/**
	* 不管key存不存在都保存
	* @param string $k key
	* @param mixed $v value
	* @param string $e expire 过期时间(默认最大30天)
	* @return bool
	*/
	function set($k, $v, $e = 2592000){
		if($this->cache)
			return $this->cache->set($k, $v, $e);
		else
			return false;
	}

	function setByKey($s, $k, $v, $e = 2592000){
		if($this->cache)
			return $this->cache->setByKey($s, $k, $v, $e);
		else
			return false;
	}

	function setMulti($kv, $e = 2592000){
		if($this->cache)
			return $this->cache->setMulti($kv, $e);
		else
			return false;
	}

	function setMultiByKey($s, $kv, $e = 2592000){
		if($this->cache)
			return $this->cache->setMultiByKey($s, $kv, $e);
		else
			return false;
	}

	/**
	* 取值
	* @param string $k key
	* @return mixed
	*/
	function get($k){
		if($this->cache)
			return $this->cache->get($k);
		else
			return false;
	}

	function getMulti($ks){
		if($this->cache)
			return $this->cache->getMulti($ks);
		else
			return false;
	}

	function getByKey($s, $k){
		if($this->cache)
			return $this->cache->getByKey($s, $k);
		else
			return false;
	}

	function getMultiByKey($s, $ks){
		if($this->cache)
			return $this->cache->getMultiByKey($s, $ks);
		else
			return false;
	}

	/**
	* 当key不存在时才保存
	* @param string $k key
	* @param mixed $v value
	* @param string $e expire 过期时间(默认最大30天)
	* @return bool
	*/
	function add($k, $v, $e = 2592000){
		if($this->cache)
			return $this->cache->add($k, $v, $e);
		else
			return false;
	}

	/**
	* 当key存在时才更新保存
	* @param string $k key
	* @param mixed $v value
	* @param string $e expire 过期时间(默认最大30天)
	* @return bool
	*/
	function replace($k, $v, $e = 2592000){
		if($this->cache)
			return $this->cache->replace($k, $v, $e);
		else
			return false;
	}

	/**
	* 删除key值
	* @param string $k key
	* @param int $t timeout 延时删除
	* @return bool
	*/
	function delete($k, $t = 0){
		if($this->cache)
			return $this->cache->delete($k, $t);
		else
			return false;
	}

	/**
	* 增量
	* @param string $k key
	* @param int $v 增量值
	* @param string $e expire 过期时间(默认最大30天)
	* @return int
	*/
	function increment($k, $v = 1, $e = 2592000){
		if($this->cache)
			return $this->cache->increment($k, $v, $e);
		else
			return false;
	}

	/**
	* 减量
	* @param string $k key
	* @param int $v 减量值
	* @return int
	*/
	function decrement($k, $v = 1){
		if($this->cache)
			return $this->cache->decrement($k, $v);
		else
			return false;
	}

	/**
	* 推进队列
	* @param int $k key
	* @param int $v value
	* @param bool $uniq value唯一
	* @param string $e expire 过期时间(默认最大30天)
	* @return int
	*/
	function listPush($k, $v, $uniq = 0, $e = 2592000){
		if($this->cache)
			return $this->cache->listPush($k, $v, $uniq, $e);
		else
			return false;
	}

	/**
	* 删除指定位置
	* @param int $k key
	* @param int $idx 位置
	* @return int
	*/
	function listDel($k, $idx){
		if($this->cache)
			return $this->cache->listDel($k, $idx);
		else
			return false;
	}

	/**
	* Value Count
	*/
	function listValueCount($k, $v){
		if($this->cache)
			return $this->cache->listValueCount($k, $v);
		else
			return false;
	}

	/**
	* 队列长度
	* @param int $k key
	* @return int
	*/
	function listCount($k){
		if($this->cache)
			return $this->cache->get($k);
		else
			return false;
	}

	/**
	* 弹出1条队列
	* @param $k
	* @return
	*/
	function listPop($k){
		if($this->cache)
			return $this->cache->listPop($k);
		else
			return false;
	}

	/**
	* 取出底部队列
	* @param $k
	* @return
	*/
	function listShift($k){
		if($this->cache)
			return $this->cache->listShift($k);
		else
			return false;
	}

	/**
	* 取出队列
	* @param int $k key
	* @param int $offs 起始位置
	* @param int $length 条数
	* @return array 结果集
	*/
	function listGet($k, $offs = 0, $length = 100){
		if($this->cache)
			return $this->cache->listGet($k, $offs, $length);
		else
			return false;
	}

	/**
	* 重整理队列序列号
	* @param int $k key
	* @param string $e expire 过期时间(默认最大30天)
	* @return int
	*/
	function listOptimize($k, $e = 2592000){
		if($this->cache)
			return $this->cache->listOptimize($k, $e);
		else
			return false;
	}

	/**
	* 销毁队列
	* @param int $k key
	* @return bool
	*/
	function listDestroy($k){
		if($this->cache)
			return $this->cache->listDestroy($k);
		else
			return false;
	}

}

?>