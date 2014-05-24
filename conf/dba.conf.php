<?php
/**
 * 数据库配置
 */
return array(

	//default cache单元 注释后无默认cache
	// 'DEFAULT_CACHE' => 'dbcache',

	//服务单元设置
	'DATABASE_UNIT' => array(

		//爬虫DB
		'my_mdb' => array(
			'host' => 'localhost',
			'port' => '3306',
			'user' => 'mysqluser',
			'pwd' => 'mysqlpassword',
			'db' => 'test',
		),

		//从库
		'my_sdb' =>'&my_mdb',

		//自递ID
		'increment' =>'&my_mdb',

	),
);
?>
