<?php
class Admin_CategoryController extends Yaf_Controller_Abstract
{
	
	public function indexAction()
	{
		$categoryObj = new CategoryModel();
		$list = $categoryObj->getCategoryList();
		var_dump($list);
		exit;
	}
}