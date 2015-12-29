<?php
/**
 * 文章分类Mod
 * @author lifuqiang
 * 	
 *   | category | CREATE TABLE `category` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `title` varchar(128) DEFAULT NULL,
		  `icon` varchar(255) DEFAULT NULL,
		  `parentId` int(11) DEFAULT '0',
		  `sort` smallint(6) DEFAULT '0',
		  `url_token` varchar(32) DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  KEY `sort` (`sort`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 |
 */
class CategoryModel extends BaseModel
{
	public $_table    = 'category';
	public $_dbString = 'myblogdb';
	public $_mcPrefix = "CategoryModel::";
	public $_mcString = "main";
	
	public $table_status = array(
			'id'		=> 'i',
			'title'		=> 's',
			'icon'		=> 's',
			'parentId'	=> 'i',
			'sort'		=> 'i',
			'url_token'	=> 's',
		);
	
	public function addCategory($row)
	{
		if (! isset($row['title']) || ! $row['title'])
		{
			$this->setErrorNo(ERROR_PARAM);
			return false;
		}
		
		return $this->addCategory($row);
	}
	
	public function getCategoryList()
	{
		$retAry = array();
		$sql = "select * from `{$this->_table}` where 1 order by `sort` DESC";
		return $this->getRows($sql); 
	}
}