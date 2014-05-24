<?php

	$rs = array();

	$db = $this->loadDB('test');
	switch($args['func']){

		case 'test':

			//Select 字段
			$fields = array(
				'sid',
				'subject', 
				'count(*)' => 'num', //等同 select count(*) as `num`
			);  
			//结构化查询范例
			$condition = array(
					'where' => array(
						'(', //括号符
							'sid' => array('=', 282286), //运算符支持 '=', '!=', '>', '<', 'like', 'in'
							'or', //或
							'uid' => array('=', 12345),
						')',
						'and', //与
							'tid' => array('in', array(1,3,5)), //in查询值用array
						'or',
							array('uid', '<', 10000), //重复的字段条件不用指定下标，同字段做下标会覆盖前面的值
						'or',
							array('uid', '!=', '4321'),
					),
					'group' => array( //等同于group by f1, f2
						'f1',
						'f2'
					),
					'order' => array( //等同于order by sid desc, uid asc
						'sid' => 'desc',
						'uid' => 'asc'
					),
					'limit'	=> array(10, 20), //等同于limit 10, 20
					// 'limit'	=> array(10), //等同于limit 10
					// 'limit'	=> 10, //等同于limit 10
				);

			$rs['结构化查询范例生成SQL'] = $db->query_test('succeed_story', $fields, $condition);

		break;

		//fetch直接sql查询尽量少用，尤其是在前台
		case 'fetch':

			$rs['asfield'] = $db->fetch('select count(*) as `count` from succeed_story');

			$rs['sql取单条记录'] = $db->fetch('select `sid`, `subject` from `succeed_story` order by `sid` desc limit 1');

			$rs['sql取单条记录，缓存'] = $db->fetch('select `sid`, `subject` from `succeed_story` order by `sid` desc limit 1', 10); //缓存时间10秒

			$rs['sql查询不加limit默认强制limit 1'] = $db->fetch_all('select `sid`, `subject` from `succeed_story` order by `sid` desc');

			$rs['sql取多条记录'] = $db->fetch_all('select `sid`, `subject` from `succeed_story` order by `sid` desc limit 5');

			$rs['sql取多条记录, 缓存'] = $db->fetch_all('select `sid`, `subject` from `succeed_story` order by `sid` desc limit 5', 10); //缓存时间10秒 

		break;

		//规格化查询
		case 'query':

			$fields = array('sid', 'subject');
			$condition = array(
					'where' => array(
						'sid' => array('=', 282286)
					)
				);
			$cache = 10;
			$rs['结构化取单条记录，缓存'] = $db->query_one('succeed_story', $fields, $condition, $cache);

			////////////////////////////////////////////////////////////////////////
			$fields = array('sid', 'subject');
			$condition = array(
					'where' => array(
						'sid' => array('>', 282000)
					),
					'limit' => array(0, 10) //需要传limit参数,不传默认limit 1
				);
			$cache = 10;
			$rs['结构化取多条记录，缓存'] = $db->query_all('succeed_story', $fields, $condition, $cache);

		break;

		case 'count':
			$condition = array(
					'where' => array(
						'sid' => array('>', 282000)
					)
				);
			$rs['查询表记录数'] = $db->query_count('succeed_story', $condition);
			$cache = 10;
			$rs['查询表记录数，缓存'] = $db->query_count('succeed_story', $condition, $cache);

		break;

		case 'insert':

			$mdb = $this->loadDB('test');
			$row = array(
				'user_id'		=> rand(0, 1000),
				'product_id'	=> rand(0, 1000),
				'service_id'	=> rand(0, 1000),
				'expiry'		=> rand(0, 1000)
			);
			$rs['插入记录'] = $mdb->insert('FXBC_EXPIRY', $row);
			$rs['最后插入ID'] = $mdb->insert_id();

			$fields = array('id', 'user_id', 'product_id', 'service_id', 'expiry');
			$condition = array(
					'order' => array(
						'id' => 'desc'
					),
					'limit' => array(10)
				);
			$rs['查询插入记录'] = $mdb->query_all('FXBC_EXPIRY', $fields, $condition);

			$mdb->close();

		break;

		case 'update':
			$mdb = $this->loadDB('test');
			$row = array(
				'user_id'		=> rand(0, 1000),
				'product_id'	=> rand(0, 1000),
				'service_id'	=> rand(0, 1000),
				'expiry'		=> rand(0, 1000),
			);
			$condition = array(
					'where'	=> array(
						'id' => array('<', 10)
					),
					'limit' => array(10) //update需要传limit参数,自己估个数,不传默认limit 1
				);
			$rs['更新记录'] = $mdb->update('FXBC_EXPIRY', $row, $condition);

			$fields = array('id', 'user_id', 'product_id', 'service_id', 'expiry');
			$rs['查询更新记录'] = $mdb->query_all('FXBC_EXPIRY', $fields, $condition);

		break;

		case 'delete':
			$mdb = $this->loadDB('test');
			$condition = array(
					'where'	=> array(
						'id' => array('>', 10)
					),
					'limit' => array(1) //update需要传limit参数,自己估个数,不传默认limit 1
				);

			$rs['删除记录'] = $mdb->delete('FXBC_EXPIRY', $condition);
			$rs['查询删除后记录数'] = $mdb->query_count('FXBC_EXPIRY', $condition);

		break;

		case 'dbproxy':
			$db = $this->loadDB('dbproxy');
			$row = array(
				'uid'		=> 12*rand(1,100),
				'sex'	=> 1,
				'nick'	=> "dqm",
				'score'	=> 0,
				'birthday'	=> date("Y-m-d"),
				'birthpet'	=> 5,
				'star'	=> 6,
				'height'	=> 170,
				'weight'	=> 60,
				'body'	=> 2,
				'city'	=> 11,
				'household'	=> 12,
				'hometown'	=> 13,
				'marriage'	=> 1,
				'education'	=> 4,
				'salary'	=> 6,
				'house'	=> 2,
				'car'	=> 3
			);
			$rs['插入记录'] = $db->insert('user_info', $row);
			$rs['最后插入ID'] = $db->insert_id();
			/*
			$fields = array('uid', 'content', 'ctime');
			$condition = array(
					'limit' => array(0, 100)
				);
			$cache = null;
			$rs['db proxy 查单表'] = $db->query_all('user_news', $fields, $condition, $cache);
			 */
			$fields = array('uid', 'nick');
			$condition = array(
					'where' => array(
						//'uid' => array('in', array(18107132,19107132)) //in查询值用array
					),
					'limit' => array(0, 100)
				);
			$cache = null;
			$rs['db proxy 查多表'] = $db->query_all('user_info', $fields, $condition, $cache);

		break;

		case 'incr':
			$db = $this->loadDB('increment');
			$sql = "INSERT INTO `sys_increment` (`name`) VALUES ('key1') ON DUPLICATE KEY UPDATE `incr` = LAST_INSERT_ID(`incr` + 1)";
			$db->query($sql);
			//$db->query("SET NAMES 'UTF8'");	
			$rs['全局id'] = $db->insert_id();

		break;
	}

	var_dump($rs);

	$err = $db->get_error();
	if($err)
		var_dump($err);

	$db->close();

?>
