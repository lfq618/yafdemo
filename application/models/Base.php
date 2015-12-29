<?php

/** 
 * mod文件基础类
 * 	 - 负责数据库读写
 *   - 负责MC读写
 *   - 所有select类函数返回数组或false
 *   - 所有update、delete、insert 类函数返回mysqli_affected_rows结果或false
 *   - update, insert　天然支持array数据，但在读取的时候，如果未设置 $table_status　的话将返回字符串，存入ｍｃ中的数据也将为字符串，如果设置了table_status则将返回数组，ｍｃ中存储的也将是数组
 *   - 返回false表示有错误发生，调用getErrorNo可以知道发生的错误类型为：ERROR_PARAM 表示参数错误，ERROR_SYS 表示系统硬件错误如数据库操作错误 可到/da0/logs/live_module_error.log查看错误
 *   - 所有继承本基类的函数函数的输出统一返回false、函数需要的正确值 类如：
 *     所有select操作返回 false 以及相关数组 可以为空数组，如果是空数组表示没有该查询关键字记录
 *     所有insert update delete 操作返回false 以及行为所影响的行数，如果为0也表示正确只是该记录没有发生变化
 *   - 基类提供自动格式化数据库字段的功能，例如大文本字段，需要配置$table_status变量（如不配置则将不启用），例如：
 *     public $table_status    = array(
        'id'	=> 'i',            // 数字型
        'max'	=> 'i',
        'ltime'	=> 'i',
        'notice'	=> 's',        // 字符串型
        'wealth'	=> 'i',
        'coin6'		=> 'i',
        'utype'		=> 'i',
        'backpic'	=> 's',
        'acttime'	=> 'i',
        'utypetm'	=> 'i',
        'status'	=> 'i',
        'coin6all'	=> 'i',
        'wealthall'	=> 'i',
        'uoption'	=> 'x',        // 虚拟字段，大的
        'alias'		=> 's',
        'rid'		=> 'i',
        'integral'	=> 'i',
        'fid'		=> 'i',
        'userfrom'	=> 'i',        // 用户来源
    );
    
   　接口明明规范：如果函数写了ForMc 表示只操作ｍｃ　如果写了ForDb则表示只操作数据库　　　　如果未写For则表示两者都处理
 * @abstract
 * @package models
 * @author lifuqiang
 */
if (! defined("ERROR_PARAM")){
    define("ERROR_PARAM", 1001);        //　参数错误
	define("ERROR_SYS", 1002);          //  系统错误
	define("ERROR_IOGIC", 1003);        //  业务逻辑错误
}

class BaseModel
{
    public $_dbString    = '';
    public $_mcString    = '';
    public $_mcPrefix    = '';
    public $_table       = '';
    
    public $_errorNo     = 0;

    public $_errorMsg    = '';
    
    public $resErrorSys = false;
    
    public $table_status    = array();

    public $errorMsgConfig = array(
        ERROR_PARAM => "参数错误", 
        ERROR_SYS   => "系统错误", 
        ERROR_IOGIC => "数据逻辑错误"
    );
    
    function __construct()
    {
        
    }
    
    function regainError()
    {
        $this->_errorNo  = 0;
        $this->_errorMsg = '';
    }
    function setErrorNo($error_no, $errorMsg = "")
    {
        $this->_errorNo = $error_no;
        $this->_errorMsg = $errorMsg;
    }

    function getErrorNo()
    {
        return $this->_errorNo;
    }
    
    function getErrorMsg()
    {
        $msg = "";
        if (array_key_exists($this->_errorNo, $this->errorMsgConfig)) {
            $msg = $this->errorMsgConfig[$this->_errorNo];
        }
        
        if ($this->_errorMsg) {
            $msg .= ":" . $this->_errorMsg;
        }
        
        return $msg;
    }
    
    /**
     * 增加数据
     *   - 支持单条SQL语句，$row 需要为字符串型,$row必须是完整的sql语句，后台验证insert
     *   - 如果是sql
     *   - 只返回成功以否，不返回插入的自增ID
     *
     * @param string|array() $row  需要增加的表字段 		
     * @param string $table
     * @param string $dbString
     * @return int - 更新影响的行数 | false
     */
    protected function add($row, $table='', $dbString='', $getInsertId=false)
    {
        if (is_array($row)){
	        if (! $this->encodeTableRow($row)){
	            $this->setErrorNo(ERROR_PARAM);
	            return $this->resErrorSys;
	        }
        }
        return $this->queryInsert($row, $getInsertId, $table, $dbString);
    }
    
	/**
     * 增加数据(获取自增ID)
     *   - 支持单条SQL语句，$row 需要为字符串型,$row必须是完整的sql语句，后台验证insert
     *   - 如果是sql
     *   - 返回自增ID,如果表未设置自增ID会报错
     *
     * @param string|array() 
     * @param string $table
     * @param string $dbString
     * @return int - 插入的自增ID | false - 系统错误 | int
     */
    protected function addGetInsertId($row, $table='', $dbString='')
    {
        if (is_array($row)){
	        if (! $this->encodeTableRow($row)){
	            $this->setErrorNo(ERROR_PARAM);
	            return $this->resErrorSys;
	        }
        }
        return $this->queryInsert($row, true, $table, $dbString);
    }
    
    /**
     * 修改数据
     *   - 支持单条SQL语句，$row 需要为字符串型,$row必须是完整的sql语句，后台验证where
     *   - $row 是数组，$where 必须有值，如果是针对全部，值为1
     *
     * @param string|array() $row
     * @param string $where
     * @param string $table
     * @param string $dbString
     * @return int - 更新影响的行数 | false - 系统错误
     */
    protected function update($row, $where='', $table='', $dbString='')
    {
        if (is_array($row)){
	        if (! $this->encodeTableRow($row)){
	            $this->setErrorNo(ERROR_PARAM);
	            return $this->resErrorSys;
	        }
        }
        
        return $this->queryUpdate($row, $where, $table, $dbString);
    }
    
    /**
     * 删除数据
     *
     * @param string $whereStr     条件语句
     * @param string $table        表名
     * @param string $dbString 	        数据库
     * @return int - 更新影响的行数 | false - 系统错误
     */
    protected function delete($whereStr, $table='', $dbString='')
    {
    	return $this->queryDelete($whereStr, $table, $dbString);
    }
    
    /**
     * 获取单行
     *   获取的结果如: array('id'=>xxx, 'v'=>xxx)
     *   支持单条SQL语句，$sqlItem 需要为字符串型数据
     *   当不需要保存mc时，请将mcKey设置为空
     *   当real为true $mcKey不为空时，将从数据库中取出值，并存入mc，当取的值为空，则保存空数组，如果系统错误，将不保存值
     *
     * @param array()|string $sqlItem
     * @param string $where   条件语句
     * @param string $mcKey   mc的key
     * @param bool $real	     是否读取MC，
     * @param int $mcTime	  mc保留时间0为永久
     * @param string $table
     * @param string $dbString 
     * @return array() | false - 系统错误
     */
    protected function getRow($sqlItem, $where='', $mcKey='', $real=false, $mcTime=0, $table='', $dbString='')
    {
        $result    = $this->get($sqlItem, $where, $mcKey, $real, true, '', $mcTime, $table, $dbString);
        
        return $result;
    }
    
    /**
     * 获取多行值
     *   获取的结果如: array(0=>array('id'=>xxx, 'v'=>xxx), 1=>array('id'=>xx, 'v'=>xxx))
     *   支持单条SQL语句，$sqlItem 需要为字符串型数据
     *   当不需要保存mc时，请将mcKey设置为空
     *   当real为true $mcKey不为空时，将从数据库中取出值，并存入mc，当取的值为空，则保存空数组，如果系统错误，将不保存值
     *
     * @param array()|string $sqlItem
     * @param string $where    条件语句
     * @param string $mcKey    mc的key
     * @param bool $real       是否读取MC，
     * @param int $mcTime      mc保留时间0为永久
     * @param string $table
     * @param string $dbString
     * @return array() | false - 系统错误
     */
    protected function getRows($sqlItem, $where='', $mcKey='', $real=false, $mcTime=0, $table='', $dbString='')
    {
        $result    = $this->get($sqlItem, $where, $mcKey, $real, false, '', $mcTime, $table, $dbString);
        
        return $result;
    }
    
	/**
     * 获取单一列单行
     *   获取的结果如: array(value)
     *   支持单条SQL语句，$sqlItem 需要为字符串型数据
     *   当不需要保存mc时，请将mcKey设置为空
     *   当real为true $mcKey不为空时，将从数据库中取出值，并存入mc，当取的值为空，则保存空数组，如果系统错误，将不保存值
     *
     * @param array()|string $sqlItem
     * @param string $cmKey   表列
     * @param string $where   条件语句
     * @param string $mcKey   mc的key
     * @param bool $real	     是否读取MC，
     * @param int $mcTime	  mc保留时间0为永久
     * @param string $table
     * @param string $dbString 
     * @return array() | false - 系统错误
     */
    protected function getColumnRow($sqlItem, $columnKey, $where='', $mcKey='', $real=false, $mcTime=0, $table='', $dbString='')
    {
        $result    = $this->get($sqlItem, $where, $mcKey, $real, true, $columnKey, $mcTime, $table, $dbString);
        
        return $result;
    }
    
	/**
     * 获取单一列多行值
     *   获取的结果如: array(0=>array('id'=>xxx, 'v'=>xxx), 1=>array('id'=>xx, 'v'=>xxx))
     *   支持单条SQL语句，$sqlItem 需要为字符串型数据
     *   当不需要保存mc时，请将mcKey设置为空
     *   当real为true $mcKey不为空时，将从数据库中取出值，并存入mc，当取的值为空，则保存空数组，如果系统错误，将不保存值
     *
     * @param array()|string $sqlItem
     * @param string $cmKey    表列
     * @param string $where    条件语句
     * @param string $mcKey    mc的key
     * @param bool $real       是否读取MC，
     * @param int $mcTime      mc保留时间0为永久
     * @param string $table
     * @param string $dbString
     * @return array() | false - 系统错误
     */
    protected function getColumnRows($sqlItem, $columnKey, $where='', $mcKey='', $real=false, $mcTime=0, $table='', $dbString='')
    {
        $result    = $this->get($sqlItem, $where, $mcKey, $real, false, $columnKey, $mcTime, $table, $dbString);
        
        return $result;
    }
    
    /**
     * 根据主键id，获取一组值
     *   - $dbKey 必须为记录的主键
     *   - $termStr 额外的条件语句
     *   - $idAry 必须为dbKey值的数值
     *   - $mcKeyAry 必须在运用程序中格式好每个id所对应的mcKey, 格式为 array($id=>$mcKey, $id1=>$mcKey1);
     *   - $real  表示是否要取mc值
     *
     * @param array() $sqlItem      要获取的字段
     * @param string  $whereStr		需要额外查询的条件
     * @param string  $idKey		需要被集合查询字段
     * @param array() $idAry		需要被查询的ID组
     * @param array  $mcKeyAry		MCkey组
     * @param bool $real			是否启用mc
     * @param string $table			表
     * @param string $dbString		数据库
     * @return array()
     */
    protected function getRowsByIdAry($sqlItem, $whereStr, $idKey, $idAry, $mcKeyAry, $real=false, $table='', $dbString='')
    {
        $res = array();
        if (!is_array($idAry) || count($idAry) == 0 || ! is_array($mcKeyAry) || count($mcKeyAry) == 0) {
            $this->setErrorNo(ERROR_PARAM);
            return $res;
        }
        
        $ret = array();
        $notHit = array();
        if ($real){
            $notHit    = $idAry;
        }
        else{
            $ret = $this->_getAryByIdMc($idAry, $mcKeyAry, $notHit);
        }
        
        if (is_array($notHit) && count($notHit) > 0) {
            $ret += $this->_getAryByIdRead($sqlItem, $whereStr, $idKey, $notHit, $mcKeyAry, $table, $dbString);
        }
        
        return $ret;
    }
    
	/**
     * 执行非select delete insert update 等常规命令的函数
     * 
     * @param string $sql    SQL语句
     * @param string $dbString  数据库
     * @return resource
     */
    protected function querySql($sql, $dbString='')
    {
        if (! $dbString){
            $dbString = $this->_dbString;
        }
        
        $result = dbLib::getInstance($dbString)->querySql($sql);
        if (false === $result){
            $this->setErrorNo(ERROR_SYS);
            return 0;
        }
        
        return $result;
    }
    
    /**
     * 获取相应数据 
     *   支持单条SQL语句，$sqlItem 需要为字符串型数据
     *   当不需要保存mc时，请将mcKey设置为空
     *   当real为true $mcKey不为空时，将从数据库中取出值，并存入mc，当取的值为空，则保存空数组，如果系统错误，将不保存值
     *   支持取单列操作，设置cmKey则为取单列模式，单列模式的结果如：array(value, value2)  否则为：array('k1'=>value1, 'k2'=>value2)
     *   支持array(*)操作
     *
     * @param string | array() $sqlItem
     * @param string $where
     * @param string $mcKey
     * @param string $cmKey
     * @param bool	$real
     * @param string $table
     * @param string $dbString
     * 
     * @return array() | false - 系统错误
     */
    private function get($sqlItem, $where, $mcKey, $real=false, $isOnce=true, $columnKey='', $mcTime=0, $table='', $dbString='')
    {
        $row    = array();
        if (! $mcKey || $real || false === ($row = $this->getMcRow($mcKey))){
            $row = $this->querySelect($sqlItem, $where, $isOnce, $columnKey, $table, $dbString);
            
            if (false === $row){
                return $row;
            }
            
            if ($isOnce){
                $this->decodeTableRow($row);
            }
            else{
                foreach ($row as &$item){
                    $this->decodeTableRow($item);
                }
                unset($item);
            }
            if ($mcKey){
            	$this->setMcRow($mcKey, $row, $mcTime);
            }
        }
        
        return $row;
    }
    
	/**
     * 从MC中获取数据
     *
     * @param array $idAry		总的id
     * @param array $hitAry		未命中的
     */
    private function _getAryByIdMc($idAry, $mcKeyAry=array(), &$noHit)
    {
        $res = array();

        if (!is_array($idAry) || count($idAry) <= 0) {
            $this->setErrorNo(ERROR_PARAM);
            return $res;
        }
        
        if (count($mcKeyAry) > mcLib::$MC_KEYS_LIMIT) {
            $mcKeyAryC = array_chunk($mcKeyAry, mcLib::$MC_KEYS_LIMIT);
        }
        else {
            $mcKeyAryC[] = $mcKeyAry;
        }
        
        $ret1 = array();
        foreach ( $mcKeyAryC as $item ) {
            $ret1 += $this->getMcRow($item);
        }
        
        foreach ( $mcKeyAry as $id => $mcategory ) {
            if (!isset($ret1[$mcategory]) || !$ret1[$mcategory]) {
                $noHit[] = $id;
            }
            else {
                $res[$id] = $ret1[$mcategory];
            }
        }
        return $res;
    }

    private function _getAryByIdRead($row, $termStr, $idKey, $idAry, $mcKeyAry=array(), $table, $dbString)
    {
        $res = array();
        
        if (!is_array($idAry) || count($idAry) <= 0) {
            $this->setErrorNo(ERROR_PARAM);
            return $res;
        }
        
        if (count($idAry) > mcLib::$MC_KEYS_LIMIT) {
            $idAryC = array_chunk($idAry, mcLib::$MC_KEYS_LIMIT);
        }
        else {
            $idAryC[] = $idAry;
        }
        if (trim($termStr)){
            $termStr = $termStr . 'and ';
        }
        
        foreach ( $idAryC as $item ) {
        	if (! $item)
        	{
        		continue;
        	}
            $whereIn = implode(",", $item);
            $where   = "$termStr {$idKey} in ({$whereIn})";
            $result  = $this->querySelect($row, $where, false, $table, $dbString);
            
            if (! $result){
                continue;
            }
            foreach($result as $item) {
                $this->decodeTableRow($item);
                $res[$item[$idKey]] = $item;
            }
        }
        
        $mc = mcLib::getInstance($this->_mcString);
        foreach ( $res as $id => $item ) {
            $mcKey    = $mcKeyAry[$id];
            $mc->setMcRow($mcKey, $item);
        }
        
        return $res;
    }

/////////////////////// 内部函数 /////////////////////////////////////////
    /**
     * 解析数据库语句
     *
     * @param array() $row
     * @return bool
     */
    private function encodeTableRow(&$row)
    {
        if (! $this->table_status || ! $row){
            return true;
        }
        foreach ($row as $k=>$v){
            if (! isset($this->table_status[$k])){
                return false;
            }
            
            if ($this->table_status[$k] == 'i'){
                $row[$k] = $v+0;
            }
            elseif ($this->table_status[$k] == 's'){
                $row[$k] = $v . '';
            }
            elseif ($this->table_status[$k] == 'x'){
                if (! is_array($v)){
                    return false;
                }
                $row[$k] = json_encode($v);
            }
        }
        
        return true;
    }
    
    /**
     * 解析从数据库中获取的数据
     *     - 需要配置$table_status 变量，该变量定义了
     *
     * @param array() $row
     * @return array()
     */
    private function decodeTableRow(&$row)
    {
        if (! $this->table_status || ! is_array($row) || ! $row){
            return true;
        }
        foreach ($row as $k=>$v){
            $type = isset($this->table_status[$k]) ? $this->table_status[$k] : '';
            if ($type)
            {
	            if ($this->table_status[$k] == 'x'){
	                if (! $v){
	                    $row[$k] = array();
	                }
	                else{
	                    $row[$k] = json_decode($v, true);
	                }
	            }
            }
        }
        
        return true;
    }
    
    /**
     * 获取mc值
     *
     * @param string $mcKey
     * @param string $mcString
     * @return value
     */
    protected function getMcRow($mcKey, $mcString='')
    {
        if (! $mcString){
            $mcString    = $this->_mcString;
        }
        //mcLib::getInstance('mcTest')->getMcRow($mcKey);
        return redisLib::getRedis($mcString)->getMcRow($mcKey);
    }
    
    /**
     * 设置MC值
     *
     * @param string $mcKey
     * @param value $row
     * @param int $time		过期时间
     * @param string $mcString MC服务器
     * @return value
     */
    protected function setMcRow($mcKey, $row, $time=0, $mcString='')
    {
        if (! $mcString){
            $mcString    = $this->_mcString;
        }
        //mcLib::getInstance('mcTest')->setMcRow($mcKey, $row, $time);
        return redisLib::getRedis($mcString)->setMcRow($mcKey, $row, $time);
    }
    
    protected function increaseMcRow($mcKey, $num, $mcString='')
    {
        if (! $mcString){
            $mcString    = $this->_mcString;
        }
        
        return redisLib::getRedis($mcString)->increaseMcRow($mcKey, $num);
    }
    
    protected function decreaseMcRow($mcKey, $num, $mcString='')
    {
        if (! $mcString){
            $mcString    = $this->_mcString;
        }
        
        return redisLib::getRedis($mcString)->decreaseMcRow($mcKey, $num);
    }
    
    /**
     * 删除mc
     *
     * @param string $mcKey
     * @param value $mcString
     * @return value
     */
    protected function delMcRow($mcKey, $mcString='')
    {
        if (! $mcString){
            $mcString    = $this->_mcString;
        }
        return redisLib::getRedis($mcString)->delMcRow($mcKey);
    }
    
    /**
     * 查询数据
     *
     * @param string|array() $item   如果item为字符串，则需要是完整的sql语句
     * @param string $whereStr  条件语句
     * @param bool $isOne   是否单条获取
     * @param string $cmKey 是否要取单一列值，如果设置后返回的结果为 array(value); 否则为：array('key'=>value);
     * @param string $table
     * @param string $dbString
     * @return array()
     */
    private function querySelect($item, $whereStr='', $isOne=true, $cmKey='', $table='', $dbString='')
    {
        if (! $table){
            $table = $this->_table;
        }
        if (! $dbString){
            $dbString = $this->_dbString;
        }
        
        if (is_string($item)){
            $result = dbLib::getInstance($dbString)->querySelectSql($item, $isOne, $cmKey);
        }
        else{
            $result = dbLib::getInstance($dbString)->querySelect($item, $table, $whereStr, $isOne, $cmKey);
        }
        
        if (false === $result){
            $this->setErrorNo(ERROR_SYS);
        }
        
        return $result;
    }
    
    /**
     * 插入数据
     *
     * @param string|array() $item   如果item为字符串，则需要是完整的sql语句
     * @param bool $getInsertId      是否获取插入ID
     * @return int | false
     */
    public function queryInsert($item, $getInsertId=false, $table='', $dbString='')
    {
        if (! $table){
            $table = $this->_table;
        }
        if (! $dbString){
            $dbString = $this->_dbString;
        }
        
        if (is_string($item)){
            $result = dbLib::getInstance($dbString)->queryInsertSql($item, $getInsertId);
        }
        else{
            $result = dbLib::getInstance($dbString)->queryInsert($item, $table, $getInsertId);
        }

        if (false === $result){
            $this->setErrorNo(ERROR_SYS);
        }
        
        return $result;
    }
    
    /**
     * 更新数据
     *
     * @param string|array() $item   如果item为字符串，则需要是完整的sql语句
     * @param string $whereStr
     * @return 0 | false-系统错误
     */
    protected function queryUpdate($item, $whereStr='', $table='', $dbString='')
    {
        if (! $table){
            $table = $this->_table;
        }
        if (! $dbString){
            $dbString = $this->_dbString;
        }
        
        if (is_string($item)){
            $result = dbLib::getInstance($dbString)->queryUpdateSql($item);
        }
        else{
            $result = dbLib::getInstance($dbString)->queryUpdate($item, $table, $whereStr);
        }
        
        if (false === $result){
            $this->setErrorNo(ERROR_SYS);
        }
        
        return $result;
    }
    
    /**
     * 删除
     *
     * @param string $whereStr
     * @param string $table
     * @param string $dbString
     * @return int | false - 系统错误
     */
    protected function queryDelete($whereStr='', $table='', $dbString='')
    {
        if (! $table){
            $table = $this->_table;
        }
        if (! $dbString){
            $dbString = $this->_dbString;
        }
        
        $result = dbLib::getInstance($dbString)->queryDelete($table, $whereStr);
        if (false === $result){
            $this->setErrorNo(ERROR_SYS);
        }
        
        return $result;
    }
}
?>