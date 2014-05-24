<?php

	require_once('GridPHP.inc.php');

	$GP = &$GLOBALS['GRIDPHP'];

	//def下默认加载直接使用
	
	// $memc = $GP->memcd->loadMemc('server');
	// $memc->set('xxx', 1);
	// $memc->get('xxx');

	// $mdb = $this->loadDB('user');
	// $row = array(
	// 	'user_id'	=> 12345,
	// 	'name'		=> 'jack',
	// );
	// $mdb->insert('user', $row);
	// $mdb->close();


	//mod下调用前加载
	$GP->mod('modexample');

	$rs = array();
	$rs[] = $GP->modexample->hello1('Jack');
	$rs[] = $GP->modexample->hello2('Rose');
	$rs[] = $GP->modexample->hello3();

	print_r($rs);

?>
