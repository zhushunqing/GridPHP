<?php
/*
 * MemCache服务器设置
*/
return array(

	//服务单元设置
	'MEMCACHE_UNIT' => array(

		//主缓存
		'servers' => array(
			array('host' => '127.0.0.1', 'port' => 11211, 'pconnect' => 1),
			array('host' => '127.0.0.1', 'port' => 11311, 'pconnect' => 1),
		),

		//sql查询缓存
		'dbcache' => array(
			array('host' => '127.0.0.1', 'port' => 12211, 'pconnect' => 1),
			array('host' => '127.0.0.1', 'port' => 12311, 'pconnect' => 1),
		),

		//方法调用缓存
		'callfuncache'	=> array(
			array('host' => '127.0.0.1', 'port' => 12211, 'pconnect' => 1),
			array('host' => '127.0.0.1', 'port' => 12311, 'pconnect' => 1),
		),

		//方法调用计数
		'callfuncount'	=> array(
			array('host' => '127.0.0.1', 'port' => 12211, 'pconnect' => 1),
			array('host' => '127.0.0.1', 'port' => 12311, 'pconnect' => 1),
		),

		//测试
		'test'	=> array(
			array('host' => '127.0.0.1', 'port' => 12211, 'pconnect' => 1),
			array('host' => '127.0.0.1', 'port' => 12311, 'pconnect' => 1),
		),

	),

);
?>
