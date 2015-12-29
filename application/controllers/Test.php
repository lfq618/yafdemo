<?php
/**
 * 测试
 * @author lifuqiang
 *
 */
class TestController extends Yaf_Controller_Abstract {
	
	public function indexAction() {
		
		$app = new Yaf_Application();
		var_dump($app->getConfig('application'));
		$model = new BaseModel();
		$str = $model->getDataById(1);
		echo $str; exit;
	}
}