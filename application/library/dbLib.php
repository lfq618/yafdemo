<?php 
/**
 * db 操作类
 *    - 所有select类函数返回数组或false
 *    - 所有update、delete、insert 类函数返回mysqli_affected_rows结果或false
 * 
 * @package lib
 * @author lifuqiang
 */

class dbLib
{
    public static $dbPool    = null;
    
    private $_db             = null;
    private $_dbKey		 	 = '';
    private static $_dbPool	 = array();
    
    /**
     * 单例模式
     *
     * @param string $mcString
     * @return dbLib
     */
    static function getInstance($dbString)
    {
        if (ENV != DEF_ENV_OUTER) {
            $dbString = "dbdev";
        }
        
        if (! isset(self::$dbPool)){
            $instance = new dbLib($dbString);
            self::$dbPool = $instance;
        }
        else{
        	self::$dbPool->_getDb($dbString);
        }
        
        return self::$dbPool;
    }
    
    private function __construct($dbString = 'dbdev')
    {
        $this->_getDb($dbString);
    }
    
    function closeDb()
    {
    	unset(self::$_dbPool[$this->_dbKey]);
        $this->_dbKey = '';
//         mysqli_close($this->_db);
        mysqli_close($this->_db);
        $this->_db = null;
    }
    
	/**
     * 指定 mcString 得到对应的 memcache 对象
     *
     * @param string $mcString
     * @return memcache
     */
    private function _getDb($dbString)
    {
		$dbConfig = new Yaf_Config_Ini('/home/wwwroot/myblog/conf/database.ini');
    	if (! ($dbConfig instanceof  Yaf_Config_Ini)){
            exit("db system err");
        }
        
        if (! $dbConfig->database->get($dbString))
        {
        	exit("no dbstring: " . $dbString);
        }
        
        if (! isset(self::$_dbPool[$dbString])){
        	$link = mysqli_connect($dbConfig->database->get($dbString)->host, 
        				$dbConfig->database->get($dbString)->username, 
        				$dbConfig->database->get($dbString)->password, 
        				'', 
        				$dbConfig->database->get($dbString)->port);
	        if (!$link) {
	            logLib::errorModuleLog($dbString);
	            return false;
	        }
	        $this->_db		= $link;
	        $this->_dbKey	= '';
	        self::$_dbPool[$dbString] = $link;
        }
        else{
        	$this->_db = self::$_dbPool[$dbString];
        }
        
        if ($this->_dbKey != $dbString)
        {
//         	mysql_select_db($dbconfig['dbname'], $this->_db);
			mysqli_select_db($this->_db, $dbConfig->database->get($dbString)->dbname);
        	$this->_dbKey	= $dbString;
        }
        
        return true;
    }
    
	/**
     * 执行SELECT语句
     *
     * @param string $sql
     * @param bool $isOne	是否为单条
     * @param string $cmKey 获取的列值
     * @return array|false - 系统错误
     */
    function querySelect($row, $table, $whereStr, $isOne = true, $cmKey='')
    {
        $res    = array();
        
        $sql    = $this->makeSqlAllSelect($row, $table, $whereStr);
        if (! $sql){
            logLib::errorDbLog(var_export($row, true) . $table . $whereStr);
            return false;
        }
        
        $result = $this->queryDb($sql);
        if (! $result){
            return false;
        }
        if (mysqli_num_rows($result)){
	        if ($isOne){
// 	            $row = mysqli_fetch_assoc($result); 
	            $row = mysqli_fetch_assoc($result);
	            if ($cmKey)
	            {
	                $res = $row[$cmKey];
	            }
	            else 
	            {
	                $res = $row; 
	            }
	        }
	        else{
	            if ($cmKey)
	            {
		            while($row = mysqli_fetch_assoc($result)){
		                $res[] = $row[$cmKey];
		            }
	            }
	            else 
	            {
		            while($row = mysqli_fetch_assoc($result)){
		                $res[] = $row;
		            }
	            }
	        }
        }
        mysqli_free_result($result);
        
        return $res;
    }
    
    /**
     * 根据sql语句查询
     *   $sql 必须包含where
     *   正常返回array()  系统错误返回false
     *
     * @param string $sql
     * @param bool $isOne
     * @param string $cmKey 获取的列值
     * @return array | false - 系统错误
     */
    function querySelectSql($sql, $isOne = true, $cmKey = '')
    {
        $res    = array();
        
        if (! $sql || false === stripos($sql, 'where')){
            logLib::errorDbLog('no where item ' . $sql);
            return false;
        }
        
        $result = $this->queryDb($sql);
        if (! $result){
            return false;
        }
        
        if (mysqli_num_rows($result)){
            if ($isOne){
	            $row = mysqli_fetch_assoc($result); 
	            if ($cmKey)
	            {
	                $res = $row[$cmKey];
	            }
	            else 
	            {
	                $res = $row; 
	            }
	        }
	        else{
	            if ($cmKey)
	            {
		            while($row = mysqli_fetch_assoc($result)){
		                $res[] = $row[$cmKey];
		            }
	            }
	            else 
	            {
		            while($row = mysqli_fetch_assoc($result)){
		                $res[] = $row;
		            }
	            }
	        }
        }
        mysqli_free_result($result);
        
        return $res;
    }
    
    /**
     * 执行update语句
     *
     * @param string $sql
     * @return int | false - 系统错误
     */
    function queryUpdate($row, $table, $whereStr)
    {
        $sql    = $this->makeSqlAllUpdate($row, $table, $whereStr);
        if (! $sql){
            logLib::errorDbLog(var_export($row, true) . $table . $whereStr);
            return false;
        }
     
        $result = $this->queryDb($sql);
        if (! $result){
            return false;
        }
        $res = mysqli_affected_rows($this->_db);
        
        return $res;
    }
    
    /**
     * 执行insert语句 返回插入的自增ID
     *
     * @param string $sql
     * @return int | false - 系统错误
     */
    function queryInsert($row, $table, $getInsertId=false)
    {
        $sql    = $this->makeSqlAllInsert($row, $table);
        if (! $sql){
            logLib::errorDbLog(var_export($row, true) . $table);
            return false;
        }
        
        $result = $this->queryDb($sql);
        if (! $result){
            return false;
        }
        
        $res = mysqli_affected_rows($this->_db);
        if ($getInsertId){
            $res    = mysqli_insert_id($this->_db);
        }
        
        return $res;
    }
    
    /**
     * 执行insert语句
     *   未更新成功返回0，更新成功返回1，系统错误返回false, 
     *
     * @param string $sql
     * @return int | false - 系统错误
     */
    function queryInsertSql($sql, $getInsertId=false)
    {
        if (! $sql || false === stripos($sql, 'insert')){
            return false;
        }
        
        /// 测试是否使用了引用
        $result = $this->queryDb($sql);
        if (! $result){
            return false;
        }
        
        $res = mysqli_affected_rows($this->_db);
        if ($getInsertId){
            $res    = mysqli_insert_id($this->_db);
        }
        
        return $res;
    }
    
	/**
     * 执行delete语句
     *   未更新成功返回0，更新成功返回1，系统错误返回false, 
     *
     * @param string $sql
     * @return int | false - 系统错误  0：数据逻辑错误，更新失败，条件不符合  1:成功
     */
    function queryDelete($table, $whereStr)
    {
        $sql    = $this->makeSqlAllDelete($table, $whereStr);
        if (! $sql){
            logLib::errorDbLog($whereStr . $table);
            return false;
        }
        
        $result = $this->queryDb($sql);
        if (! $result){
            return false;
        }
        $res = mysqli_affected_rows($this->_db);
        
        return $res;
    }
    
    /**
     * 执行update语句
     *   未更新成功返回0，更新成功返回1，系统错误返回false,
     *   sql 语句必须有where 
     *
     * @param string $sql
     * @return int | false - 系统错误
     */
    function queryUpdateSql($sql)
    {
        if (! $sql || false === stripos($sql, 'where')){
            logLib::errorDbLog($sql);
            return false;
        }
        
        /// 测试是否使用了引用
        $result = $this->queryDb($sql);
        if (! $result){
            return false;
        }
        $res = mysqli_affected_rows($this->_db);
        
        return $res;
    }
    
    function querySql($sql)
    {
        $result = $this->queryDb($sql);
        if (! $result){
            return false;
        }
        
        return $result;
    }
    
    /**
     * 执行SQL语句
     *
     * @param unknown_type $sql
     * @param unknown_type $isSelect
     * @return unknown
     */
    private function queryDb($sql)
    {
        if (is_null($this->_db)) {
            exit('get db error null');
        }
        
        $result = mysqli_query($this->_db, $sql);
        if (! $result) {
            logLib::errorModuleLog(mysqli_errno($this->_db) . mysqli_error($this->_db) . $sql . var_export($result, true) . " \n");
            return false;
        }
        
        return $result;
    }
    
    function makeSqlAllDelete($table, $whereStr)
    {
        if (! $whereStr){
            return "";
        }
        
        $where    = 'where ' . $whereStr;
        $sql = "delete from `{$table}` {$where}";
        
        return $sql;
    }
    
    function makeSqlAllSelect($row, $table, $whereStr)
    {
        if (! $whereStr){
            return "";
        }
        
        $sql = "";
        foreach ($row as $v){
            $v    = addslashes($v);
            $sql .= "$v, ";
        }
        
        if ($sql){
            $sql = substr($sql, 0, -2);
        }
        
        $where    = 'where ' . $whereStr;
        $sql = "select {$sql} from `{$table}` {$where}";
        
        return $sql;
    }
    
	/**
     * 生成update sql语句 - 支持array
     *
     * @param array $row  支持类型 string
     * @param string $table
     * @param string $whereStr
     * 
     * @return string
     */
    function makeSqlAllUpdate($row, $table, $whereStr)
    {
        if (! $whereStr){
            return "";
        }
        
        $sql = "";
        foreach ($row as $k=>$v){
            if (is_array($v)){
                $v = addslashes(json_encode($v));
            }
            else{
                $v = addslashes($v);
            }
            $sql .= "{$k}='{$v}', ";
        }
        
        if ($sql){
            $sql = substr($sql, 0, -2);
        }
        
        $where    = 'where ' . $whereStr;
        $sql = "update `{$table}` set {$sql} {$where}";
        
        return $sql;
    }
    
	/**
     * 生成插入sql语句 - 支持array
     *
     * @param array $row
     * @param string $table
     * @return string
     */
    function makeSqlAllInsert($row, $table)
    {
        $sql = "";
        $sqlItem = "";
        $sqlValue = "";        
        foreach ($row as $k=>$v){        	
            if (is_array($v)){
                $v = addslashes(json_encode($v));
            }
            else{
                $v = addslashes($v);
            }
            $sqlItem .= $k . ", ";
            $sqlValue .= "'" . $v . "', ";
        }
        
        if ($sqlItem){
            $sqlItem = substr($sqlItem, 0, -2);
            $sqlValue = substr($sqlValue, 0, -2);
            
            $sql = "({$sqlItem}) values({$sqlValue})";
        }
        
        $sql    = "insert into {$table}{$sql}";
        return $sql;
    }
    
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
    
    static function __closeAllConnect()
    {
    	foreach (self::$_dbPool as $link)
    	{
    		mysqli_close($link);
    	}
    	
    	self::$_dbPool	= array();
    }
    
    static function __printConnect()
    {
    	return self::$_dbPool;
    }
}
?>