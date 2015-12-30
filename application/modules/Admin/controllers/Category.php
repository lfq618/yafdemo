<?php
/**
 * 文章分类管理
 * @author lifuqiang
 *
 */
class CategoryController extends Yaf_Controller_Abstract
{
	
	public function indexAction()
	{
		$categoryObj = new CategoryModel();
		$list = $categoryObj->getCategoryList();
		var_dump($list);
// 		exit;
	}
	
	public function addAction()
	{
		$categoryObj = new CategoryModel();
		
		if ($this->getRequest()->isPost())
		{
			//添加操作处理
			var_dump($this->getRequest()->getParams());
			exit;
		}
		
// 		$this->getView()->assign('list', $list);
	}
	
	
}