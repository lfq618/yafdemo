<?php
/**
 * 测试
 * @author lifuqiang
 *
 */
class TestController extends Yaf_Controller_Abstract {
	
	public function indexAction() {
		
		$row = dbLib::getInstance('picdb')->querySelectSql("select * from pic_upload_logs201505 where 1 limit 100");
		var_dump($row);
		
		echo "<hr />";
		$config = Yaf_Application::app()->getConfig()->get('application');
		var_dump($config);
		
		
		exit;
	}
	
	public function redisAction() {
		
		redisLib::getRedis('main')->increaseMcRow("lifuqiang", 10);
		var_dump(redisLib::getRedis('main')->getMcRow("lifuqiang"));
		exit;
	}
}