<?php

	$rs = array();

	$rs['普通自增ID'] = $this->get_incr_id('key1');

	$rs['区域唯一ID'] = $this->get_zone_id('key2');

	$rs['反算id所属zone'] = $this->get_id_zone(1234567803);

	var_dump($rs);

?>