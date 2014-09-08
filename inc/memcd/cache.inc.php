<?php
/**
* GridPHP Memcache工具类
* @author ZhuShunqing
* @package inc\memcd
*/
class memcd_cache{
	var $conf, $cache, $memcached, $connection, $unit;

	/**
	* 初始化构造
	* @param array $conf Memcache连接配置
	*/
	function _Init_($conf, $unit){
		$this->conf = $conf;
		$this->unit = $unit;
		$this->memcached = class_exists("Memcached"); //memcached(2.0.1+libmemcached 0.53)发现过致命bug导致php异常申请大内存溢出崩溃，如果发现问题可设成false试试
		if($this->memcached)
			$this->cache = new Memcached();
		else
			$this->cache = new Memcache();
	}

	/**
	* 连接
	* @return void
	*/
	function connect(){
		if(!$this->connection){
			if($this->conf){
				foreach($this->conf as $c)
					if($this->memcached){
						$this->cache->addServer($c['host'], $c['port']);
					}else{
						$this->cache->addServer($c['host'], $c['port'], $c['pconnect']);
					}

				if($this->memcached){
					$this->cache->setOption(Memcached::OPT_COMPRESSION, true);
				}else{
					$this->cache->setCompressThreshold(4096, 0.2);
					if(!$this->cache->connection)
						$this->errmsg->show($c, 'memcd_connect');
				}

				$this->connection = 1;
			}else{
				$this->errmsg->show('memcache conf "' . $this->unit . '" is null');
			}
		}
	}

	/**
	* 不管key存不存在都保存
	* @param string $k key
	* @param string $e expire 过期时间(默认最大30天)
	* @return bool
	*/
	function set($k, $v, $e = 2592000){
		$this->connect();
		if($this->memcached)
			return $this->cache->set($k, $v, $e);
		else
			return $this->cache->set($k, $v, MEMCACHE_COMPRESSED, $e);
	}

	/**
	* 不管key存不存在都保存
	* @param string $k key
	* @param mixed $v value
	* @param string $e expire 过期时间(默认最大30天)
	* @return bool
	*/
	function setMulti($kv, $e = 2592000){
		$this->connect();
		if($this->memcached){
			return $this->cache->setMulti($kv, $e);
		}else{
			foreach ($kv as $k => $v)
				$this->set($k, $v, $e);
			return 1;
		}
	}

	function setByKey($s, $k, $v, $e = 2592000){
		$this->connect();
		if($this->memcached)
			return $this->cache->setByKey($s, $k, $v, $e);
		else{
			return false;
		}
	}

	function setMultiByKey($s, $kv, $e = 2592000){
		$this->connect();
		if($this->memcached){
			return $this->cache->setMultiByKey($s, $kv, $e);
		}else{
			return false;
		}
	}

	/**
	* 取值
	* @param string $k key
	* @return mixed
	*/
	function get($k){
		$this->connect();
		return $this->cache->get($k);
	}

	function getByKey($s, $k){
		$this->connect();
		if($this->memcached){
			return $this->cache->getByKey($s, $k);
		}else{
			return false;
		}
	}

	/**
	* 取值
	* @param string, array $k key
	* @return mixed
	*/
	function getMulti($ks){
		$this->connect();
		if($this->memcached){
			return $this->cache->getMulti($ks);
		}else{
			return $this->cache->get($ks);
		}
	}

	function getMultiByKey($s, $ks){
		$this->connect();
		if($this->memcached){
			return $this->cache->getMultiByKey($s, $ks);
		}else{
			return false;
		}
	}

	/**
	* 当key不存在时才保存
	* @param string $k key
	* @param mixed $v value
	* @param string $e expire 过期时间(默认最大30天)
	* @return bool
	*/
	function add($k, $v, $e = 2592000){
		$this->connect();
		if($this->memcached)
			return $this->cache->add($k, $v, $e);
		else
			return $this->cache->add($k, $v, MEMCACHE_COMPRESSED, $e);
	}

	function addByKey($k, $v, $e = 2592000){
		$this->connect();
		if($this->memcached){
			return $this->cache->addByKey($s, $k, $v, $e);
		}else{
			return false;
		}
	}

	/**
	* 当key存在时才更新保存
	* @param string $k key
	* @param mixed $v value
	* @param string $e expire 过期时间(默认最大30天)
	* @return bool
	*/
	function replace($k, $v, $e = 2592000){
		$this->connect();
		if($this->memcached)
			return $this->cache->replace($k, $v, $e);
		else
			return $this->cache->replace($k, $v, MEMCACHE_COMPRESSED, $e);
	}

	function replaceByKey($s, $k, $v, $e = 2592000){
		$this->connect();
		if($this->memcached){
			return $this->cache->replaceByKey($s, $k, $v, $e);
		}else{
			return false;
		}
	}

	/**
	* 删除key值
	* @param string $k key
	* @param int $t timeout 延时删除
	* @return bool
	*/
	function delete($k, $t = 0){
		$this->connect();
		return $this->cache->delete($k, $t);
	}

	function deleteByKey($k, $t = 0){
		$this->connect();
		if($this->memcached){
			return $this->cache->deleteByKey($k, $t);
		}else{
			return false;
		}
	}

	/**
	* 增量
	* @param string $k key
	* @param int $v 增量值
	* @param string $e expire 过期时间(默认最大30天)
	* @return int
	*/
	function increment($k, $v = 1, $e = 2592000){
		$this->connect();
		$incr = $this->cache->increment($k, $v);
		if(!$incr){
			$this->add($k, $v, $e);
			$incr = 1;
		}
		return $incr;
	}

	/**
	* 减量
	* @param string $k key
	* @param int $v 减量值
	* @return int
	*/
	function decrement($k, $v = 1){
		$this->connect();
		return $this->cache->decrement($k, $v);
	}

	/**
	* 关闭连接
	* @return void
	*/
	function close(){
		if($this->memcached && $this->cache->connection)
			$this->cache->close();
	}

//Simple list queue function
//-------------------------------------------------------------------------------

	/**
	* 推进队列
	* @param int $k key
	* @param int $v value
	* @param bool $uniq value唯一
	* @param string $e expire 过期时间(默认最大30天)
	* @return int
	*/
	function listPush($k, $v, $uniq = 0, $e = 2592000){
		$ret = false;
		if($uniq){
			$ukey = $k . '_' . $this->_md5_mixed($v);
			$ret = $this->increment($ukey, 1, $e);
			$ret = ($ret == 1) ? false : $ret;
		}
		if(!$ret){
			$idx = $this->increment($k, 1) - 1;
			$ikey = $k . '_' . $idx;
			return $this->set($ikey, $v, $e) ? 1 : 0;
		}else{
			return 2;
		}
	}

	/**
	* 删除指定位置
	* @param int $k key
	* @param int $idx 位置
	* @return int
	*/
	function listDel($k, $idx){
		$ki = $k . '_' . $idx;
		$v = $this->get($ki);
		if($v){
			//删除uniq用key值
			$uk = $k . '_' . $this->_md5_mixed($v);
			$this->delete($uk);
			//删除队列id值
			return $this->delete($ki);
		}else{
			return false;
		}
	}

	/**
	* Value Count
	*/
	function listValueCount($k, $v){
		$k .= '_' . $this->_md5_mixed($v);
		return $this->get($k);
	}

	/**
	* 队列长度
	* @param int $k key
	* @return int
	*/
	function listCount($k){
		return $this->get($k);
	}

	/**
	* 取出队列
	* @param int $k key
	* @param int $offs 起始位置
	* @param int $length 条数
	* @return array 结果集
	*/
	function listGet($k, $offs = 0, $length = 100){
		$numb = $this->get($k);
		if($numb){
			$keys = array();
			for($i = $offs; $i < ($offs + $length) && $i < $numb; $i ++)
				$keys[] = $k . '_' . $i;
			$list = $this->getMulti($keys);
			if($list){
				$kl = strlen($k) + 1;
				foreach($list as $k => $v){
					$idx = substr($k, $kl);
					$list[$idx] = $v;
					unset($list[$k]);
				}
			}
			return $list;
		}else{
			return false;
		}
	}

	/**
	* 弹出1条队列
	* @param $k
	* @return mixed === null表示队列已清空 === false表示队列值为空
	*/
	function listPop($k){
		$front = $this->get($k);
		if($front){
			$ki = $k . '_' . ($front - 1);
			$v = $this->get($ki);
			$this->delete($ki);
			$this->decrement($k);
			return $v;
		}else{
			return null;
		}
	}

	/**
	* 按先后顺序取出队列
	* @param $k
	* @return mixed === null表示队列已清空 === false表示队列值为空
	*/
	function listShift($k){
		$front = floatval($this->get($k));
		$ki = $k . '_rear';
		if($front > 0 && ($rear = floatval($this->increment($ki))) > 0){
			//取完后复位队列
			if($rear >= $front){
				$this->set($k, 0);
				$this->set($ki, 0);
			}
			$ki = $k . '_' . ($rear - 1);
			$v = $this->get($ki);
			$this->delete($ki);
			return $v;
		}else{
			return null;
		}
	}

	/**
	* 重整理队列序列号
	* @param int $k key
	* @param string $e expire 过期时间(默认最大30天)
	* @return int
	*/
	function listOptimize($k, $e = 2592000){
		$new = $numb = $this->get($k);
		if($numb){

			$keys = array();
			for($i = 0; $i < $numb; $i ++){
				$ki = $k . '_' . $i;
				$keys[] = $ki;
			}
			$caches = $this->getMulti($keys);

			//整理列表
			for($i = 0, $l = $numb - 1; $i <= $l; $i ++){
				$ki = $k . '_' . $i;
				$v = $caches[$ki];
				if($v === null){
					for($j = $l; $j > $i; $j --){
						$kj = $k . '_' . $j;
						$v = $caches[$kj];
						if($v !== null){
							$this->set($ki, $v, $e);
							$l = $j - 1;
							break;
						}else{
							$new --;
							$l --;
						}
					}
					$new --;
				}
			}
			//更新列表最后id
			if($new < $numb)
				$this->set($k, $new);
			//清理溢出key
			for($i = $new; $i < $numb; $i ++){
				$ki = $k . '_' . $i;
				$this->delete($ki);
			}
			return $new;
		}else{
			return false;
		}
	}

	/**
	* 销毁队列
	* @param int $k key
	* @return bool
	*/
	function listDestroy($k){
		$numb = $this->get($k);
		if($numb){
			for($i = 0; $i < $numb; $i ++)
				$this->listDel($k, $i);
			//删除主key
			$this->delete($k);
			return true;
		}else{
			return false;
		}

	}

	//md5可用于对象
	function _md5_mixed($v){
		switch(gettype($v)){
			case 'array':
			case 'object':
				$v = var_export($v, 1);
				break;
			default:
		}
		return md5($v);
	}

}

?>