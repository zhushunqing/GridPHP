<?php
/**
* modexample配置文件示例
*/
return array(

	//远程调用接口
	'HTTP_CONFIG' => array(
		
		//默认接口
		'default' => array(
			'host'	=> 'gridphp.sinaapp.com',
			'name'	=> 'gridphp.sinaapp.com',
			'port'	=> 80,
			'uri'	=> '/api/gridphp_common_server.php',
			'timeout'=> 3000,	//超时毫秒设置
			//'encode'	=> 'serialize', //使用serialize编码数据
		),

		'hello2' => array(
			// 'host'	=> '127.0.0.1',
			// 'name'	=> 'api.gridphp.com',
			// 'port'	=> 80,
			// 'uri'	=> '/gridphp_common_server.php',
			// 'timeout'=> 3000,	//超时毫秒设置
			'use'	=> 1,
		),

	),

	'AJAX_CONFIG' => array(

		//允许未登录请求的方法
		'anonymous'	=> array(
			'hello1' => 1,
			'hello2' => 1,
		),

	),

	/**
	 * shape 体型 表项描述
	 */
	'shape' => array (
		1 => '苗条',
		2 => '苗条',
		4 => '苗条',
		3 => '匀称',
		5 => '高挑',
		6 => '丰满',
		7 => '健壮',
		8 => '魁梧',
		9 => '丰满',
		10 => '丰满'
	),

	/**
	 * zodiac 星座 表项描述
	 */
	'zodiac' => array(
		1 => '白羊座',
		2 => '金牛座',
		3 => '双子座',
		4 => '巨蟹座',
		5 => '狮子座',
		6 => '处女座',
		7 => '天秤座',
		8 => '天蝎座',
		9 => '射手座',
		10 => '魔羯座',
		11 => '水瓶座',
		12 => '双鱼座'
	),

	/**
	 * character 个性 表项描述
	 */
	'characters' => array(
		1 => '浪漫迷人',
		2 => '成熟稳重',
		3 => '风趣幽默',
		4 => '乐天达观',
		5 => '活泼可爱',
		6 => '忠厚老实',
		7 => '淳朴害羞',
		8 => '温柔体贴',
		9 => '多愁善感',
		10 => '新潮时尚',
		11 => '热辣动感',
		12 => '豪放不羁'
	),

	/**
	 * house 购房情况 表项描述
	 */
	'house' => array(
		1 => '暂未购房',
		8 => '需要时购置',
		2 => '已购住房',
		3 => '与人合租',
		4 => '独自租房',
		5 => '与父母同住',
		6 => '住亲朋家',
		7 => '住单位房'
	),

	/**
	 * bloodtype 血型 表项描述
	 */
	'bloodtype' => array(
		1 => 'A型',
		2 => 'B型',
		3 => 'O型',
		4 => 'AB型',
		5 => '其它',
		6 => '保密'
	),

	/**
	 * animal 生肖 表项描述
	 */
	'animal' => array(
		1 => '鼠',
		2 => '牛',
		3 => '虎',
		4 => '兔',
		5 => '龙',
		6 => '蛇',
		7 => '马',
		8 => '羊',
		9 => '猴',
		10 => '鸡',
		11 => '狗',
		12 => '猪'
	),

	//学校列表
	'university' => '@university',

);

?>