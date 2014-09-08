<?php
/**
* GRIDPHP 错误信息类
* @author ZhuShunqing
* @package def
*/
class gridphp_errmsg extends GRIDPHP_Module{

	/**
	* 显示出错页面/信息
	*/
	public function show($info, $tmpl = 'default'){
		if(is_array($info)){
			$tmpl = $this->getConf('TMPL', $tmpl);
			if($tmpl)
				$info = $this->replaceLable($tmpl, $info);
			else
				$info = var_export($info, 1);
		}
		print $info . "\n";
		exit;
	}

	//替换文本里的{xxx}标签
	public function replaceLable($text, $lables){
		preg_match_all("/{(.+?)}/", $text, $matches);
		list($lable, $name) = $matches;
		for($i = 0; $i < count($lable); $i ++)
			if(array_key_exists($name[$i], $lables))
				$text = str_replace($lable[$i], $lables[$name[$i]], $text);
		return $text;
	}

}

?>
