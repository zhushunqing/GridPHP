<pre>
<?php
/**
* GridPHP mod测试
* @author ZhuShunqing
* @example php modtest.php mod=http type=1
*/
// error_reporting(E_ALL ^ E_NOTICE ^E_DEPRECATED);

//可以指定配置环境
if(isset($_GET['ENV'])) define('GRIDPHP_SERVER_ENV', $_GET['ENV']);

require_once('GridPHP.inc.php');
$GP = &$GLOBALS['GRIDPHP'];

$GP->utility->setTimerPoint('START');
$mod = $_GET['mod'];
if($GP->mod($mod)){
	$GP->$mod->_DEBUG($_GET);
	$timer = $GP->utility->getTimerDiff('START');

	print "\n<br/>\nMod {$mod} test done.\n";
}else{
	print "mod=?\n";
}

print "timer: " . $timer . "ms\n";
print "memory_get_usage: " . intval(memory_get_usage() / 1024) . " Kb\n";
print "memory_get_peak_usage: " . intval(memory_get_peak_usage() / 1024) . " Kb\n";

?>
