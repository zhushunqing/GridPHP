<?php

		//$this->loadC('ip');
		//print_r($this->ip->get_client_ip());

		$this->loadC('json');

		//$obj = array('a' => 1234, 'b' => 'defg', 'c' => '中文');
		$ary1 = array('bbb'=>'abc', 'ccc'=>'def');
		$std2 = new stdclass();
		$std2->ee = 'cd';
		$std2->ff = array(1,2,'aaa'=>array(1,2,3,4));

		$std1 = new stdclass();
		$std1->aa = 1;
		$std1->bb = 2;
		$std1->cc = 3;
		$std1->dd = $ary1;
		$std1->ee = $std2;

		$obj = new stdclass();
		$obj->data = array('a'=>1, 'b'=>2, 'c'=>3, 'd'=>$std1);
		print_r($obj);

		//原数据节点类型
		$types = $this->json->objtypes($obj);
		print_r($types);

		$data = $this->json->encode($obj);
		$obj = $this->json->decode($data);

		//恢复原数据节点类型
		$this->json->recover_array(&$obj, $types);
		print_r($obj);

/*
		$obj = $this->json->decode(file_get_contents('tmp.txt'));
		$data = $obj->data;
		$types = $obj->types;

		//print_r($data);
		//print_r($types);
		
		//恢复原数据节点类型
		$this->json->recover_array(&$data, $types);
		print_r($data);
*/

?>