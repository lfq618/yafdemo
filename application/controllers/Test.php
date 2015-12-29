<?php
/**
 * 测试
 * @author lifuqiang
 *
 */
class TestController extends Yaf_Controller_Abstract {
	
	public function indexAction() {
		
		$model = new BaseModel();
		$str = $model->getDataById(1);
		echo $str; exit;
	}
}