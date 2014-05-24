<?php
/**
 * database route数据库配置
 */
return array(

	//服务单元设置
	'ROUTER_UNIT' => array(

		//数据库映射规则
		'userdb_group' => array(

			//按uid分库/表规则
			'field'	=> 'uid', //分库/表计算字段
			'mapid'	=> 'intval(fmod({field}, 10))',

			//分表规则 100尾数 / 10000单元条数
			'tabid'	=>	'intval(floatval({field}/10000))',

			//map table表映射
			'maptb'	=> '{table}_{tabid}', //{table}_{mapid}_{tabid} 具体表名

			//数据库名映射
			'mapdb'	=> 'user_{mapid}_{tabid}', //{mapid} 具体库名

			//map database对应映射到dba配置单元
			'mapconn'	=> array(

				//从库
				'r'	=> array(
					'my_mdb', #0
					'my_mdb', #1
					'my_mdb', #2
					'my_mdb', #3
					'my_mdb', #4
					'my_mdb', #5
					'my_mdb', #6
					'my_mdb', #7
					'my_mdb', #8
					'my_mdb', #9
				),

				//写库
				'w'	=> array(
					'my_sdb', #0
					'my_sdb', #1
					'my_sdb', #2
					'my_sdb', #3
					'my_sdb', #4
					'my_sdb', #5
					'my_sdb', #6
					'my_sdb', #7
					'my_sdb', #8
					'my_sdb', #9
				)

			),
		),

		//支持映射别名
		// 'userinfo'	=> 'userdb_group',

	),

);
?>
