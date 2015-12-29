<?php
/**
 * 测试
 * @author lifuqiang
 *
 */
class TestController extends Yaf_Controller_Abstract {
	
	public function indexAction() {
		
		echo ENV;
		exit;
		
		$row = dbLib::getInstance('picdb')->querySelectSql("select * from pic_upload_logs201505 where 1 limit 100");
		var_dump($row);
		exit;
	}
}