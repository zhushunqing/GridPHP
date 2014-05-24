<?php

	switch($args['action']){

		case 'alias':
			$alias = 'abc@jiayuan.com';
			//$uid = 100 * rand(10000, 100000);
			$uid = '1020900';
			$dbr = $this->loadDB('useralias');

			$row = array(
				'alias'	=> $alias,
				'uid'	=> $uid
			);

			$rs['事务开始'] = $dbr->begin($row);
			$rs['插入记录'] = $dbr->insert('user_alias', $row);
			$rs['事务回滚'] = $dbr->rollback($alias);

			$fields = array('uid');
			$condition = array(
					'where' => array(
						'alias' => array('=', $alias),
					)
				);

			$rs['查询别名对应uid测试SQL'] = $dbr->query_test('user_alias', $fields, $condition);
			$rs['查询别名对应uid'] = $dbr->query_one('user_alias', $fields, $condition);

			break;

		case 'insert':

			$uid = 100 * rand(10000, 100000);
			$dbr = $this->loadDB('userinfo');
			$row = array(
				'uid'		=> $uid,
				'sex'	=> 1,
				'nick'	=> "dqm",
				'score'	=> 0,
				'birthday'	=> date("Y-m-d"),
				'birthpet'	=> 5,
				'star'	=> 6,
				'height'	=> 170,
				'weight'	=> 60,
				'body'	=> 2,
				//'city'	=> 11,
				'household'	=> 12,
				'hometown'	=> 13,
				'marriage'	=> 1,
				'education'	=> 4,
				'salary'	=> 6,
				'house'	=> 2,
				'car'	=> 3
			);

			//$rs['数据'] = $row;
			$rs['事务开始'] = $dbr->begin($row); //可以用含'uid'键值的数据
			$rs['插入记录'] = $dbr->insert('user_info', $row);
			$rs['事务回滚'] = $dbr->rollback($uid); //也可以直接传数值uid

			break;

		case 'query':

			$dbr = $this->loadDB('userinfo');
			$fields = array('uid', 'sex', 'nick', 'birthday');
			$condition = array(
					'where' => array(
						'uid' => array('=', 9169100)
					)
				);
			$cache = 10;

			$rs['结构化查询范例生成SQL'] = $dbr->query_test('user_info', $fields, $condition);
			$rs['结构化取单条记录，缓存'] = $dbr->query_one('user_info', $fields, $condition, $cache);

			break;

		case 'inquery':

			$dbr = $this->loadDB('userinfo');
			$fields = array('uid', 'sex', 'nick', 'birthday');
			$condition = array(
					'where' => array(
						//'uid' => array('in', array(6672300, 3870300, 1020300))
						'uid' => array('in', array(6815100, 1021100, 7349100, 9169100))
						              //in查询以第1个值计算分库
					),
					'limit' => array(10),
				);
			$rs['结构化in查询范例生成SQL'] = $dbr->query_test('user_info', $fields, $condition);
			$rs['结构化in查询'] = $dbr->query_all('user_info', $fields, $condition);

			break;

		case 'ingroup':

			$dbr = $this->loadDB('userinfo');
			$fields = array('uid', 'sex', 'nick', 'birthday');

			$multi_uid = array(4943600, 6672300, 1021100, 8495600, 6815100, 7349100, 1020300, 9169100, 3870300, 1017600);
			//对未知组的uid进行分组
			$multi_uid = $dbr->get_key_group($multi_uid);
			$rs['分好库(组)的uid'] = $multi_uid;

			foreach($multi_uid as $mapid => $uids){
				$condition = array(
						'where' => array(
							'uid' => array('in', $uids)
						),
						'limit' => array(10),
					);
				$rs["对{$mapid}组的查询SQL"] = $dbr->query_test('user_info', $fields, $condition);
				$rs["对{$mapid}组的查询结果"] = $dbr->query_all('user_info', $fields, $condition);
			}

			break;

			case 'test':
				$dbr = $this->loadDB('msg_index');
				for($i = 0; $i < 368956; $i ++){
					$row = array('id' => $i);
					$condition = array(
							'where' => array(
								'id' => array('=', 0)
							),
							'limit' => 1,
							'uid' => 300,
					);
					$ret = $dbr->update('msg_index', $row, $condition);
					print $i . ' ' . var_export($ret, 1) . "\n";
				}

			break;

			case 'fetch':

				$dbr = $this->loadDB('userinfo');
				$uid = 1000100;
				//对未知组的uid进行分组
				$group = $multi_uid = $dbr->get_key_group($uid);
				$sql = "SELECT re.`fuid` , re.`time` FROM `user_msg_sender_{$group}` AS msg RIGHT JOIN `user_click_{$group}` AS re ON msg.uid = re.uid WHERE re.uid =1000100 GROUP BY re.`fuid` limit 1";
				$rs['直接SQL查询'] = $dbr->fetch_all($sql, $uid);

			break;

			case 'looptest':
				//查询全部分库分表数据
				$dbr = $this->loadDB('userinfo');
				for($db = 0; $db < 10; $db ++){

					//分库规则因子
					$ruleid = $db ? $db * 100 : 1000;

					//方法1，自己处理sql表名
					// $sql = "select uid from user_info_{$db} where gid=1 limit 3";
					// $rs['DB' . $db . '查询'] = $dbr->fetch_all($sql, $ruleid);

					//方法2，利用dbr规则
					$fields = array('uid');
					$condition = array(
						'where'	=> array(
								'gid'	=> array('=', 1),
							),
						'uid' => $ruleid, //分库规则因子
						'limit' => array(3),
					);
					$rs['DB' . $db . '查询'] = $dbr->query_all('user_info', $fields, $condition);
				}
			break;
	}


	print_r($rs);
	$err = $dbr->get_error();
	if($err)
		print_r($err);

	$dbr->close();

?>
