<?php

	$key = $this->getParam('k');
	$value = $this->getParam('v');
	$uniq = $this->getParam('uniq', 'intval');
	$unit = $this->getParam('unit');
	$offs = $this->getParam('offs', 'intval');
	$size = $this->getParam('size', 'intval');

	$offs = intval($offs);
	$size = ($size) ? $size : 10000;
	$unit = ($unit) ? $unit : 'test';

	$func = $this->getParam('func');

	$this->setMemc($unit);
	switch($func){

		case 'set':
			var_dump($this->set($key, $value, 3600));
			break;

		case 'setmulti':
			$kv = array(
				'aaa' => 111,
				'bbb' => 222,
				'ccc' => 333,
				'ddd' => 444,
				'eee' => 555,
				'fff' => 666,
				'ggg' => 777,
				'hhh' => 888,
				);
			var_dump($this->setMulti($kv), 60);
			break;

		case 'setmultibykey':
			$kv = array(
				'aaa' => 111,
				'bbb' => 222,
				'ccc' => 333,
				'ddd' => 444,
				'eee' => 555,
				'fff' => 666,
				'ggg' => 777,
				'hhh' => 888,
				);
			var_dump($this->setMultiByKey('group1', $kv, 60));
			break;

		case 'get':
			var_dump($this->get($key));
			break;

		case 'getmulti':
			$keys = array('aaa', 'bbb', 'ccc');
			var_dump($this->getMulti($keys));
			break;

		case 'del':
			var_dump($this->delete($key));
			break;

		case 'push':
			for($i = 0; $i < 1000; $i ++)
				var_dump($this->listPush($key, $value . '_' . $i, $uniq));
			break;

		case 'list':
			$numb = $this->get($key);
			$list = $this->listGet($key, $offs, $size);
			if($list){
				foreach($list as $i => $v)
					$list[$i] = array($v, $key.'_'.md5($v), $this->listValueCount($key, $v));
			}
			var_dump($numb, $list);
			break;

		case 'listopti':
			$ret = $this->listOptimize($key);
			var_dump($ret);
			break;

		case 'listdel':
			for($i = 0; $i < 500; $i ++)
				var_dump($i, $this->listDel($key, rand(0, 1000)));
			break;

		case 'pop':
			$pop = $this->listPop($key);
			var_dump($pop);
			break;

		case 'shift':
			$pop = $this->listShift($key);
			var_dump($pop);
			break;

		case 'destroy':
			var_dump($this->listDestroy($key, $unit));
			break;

		default:
			print 'action=?';
	}

?>