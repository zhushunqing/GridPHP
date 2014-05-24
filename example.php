<?php

	require_once('GridPHP.inc.php');
	
	$GP = &$GLOBALS['GRIDPHP'];
	$GP->mod('modexample');

	$rs = array();
	$rs[] = $GP->modexample->hello1('Jack');
	$rs[] = $GP->modexample->hello2('Rose');
	$rs[] = $GP->modexample->hello3();

	print_r($rs);

?>
