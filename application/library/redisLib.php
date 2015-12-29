<?php
class redisLib
{
	public static $_redisObjPool	= array();
	
	private $_redisString			= '';
	private $_redisConfig			= '';
	private $_linkPool				= '';
	private $_isDebug				= false;
	private $_isTranscation			= false; //是否是事物操作，如果是事物操作，则不允许重试
	private $_errorNo				= 0;	
	private $_hashMcKey				= '';  //用来做HASH的key
	private $_hashKey				= 0;   //通过hashMcKey生成的hash值
	private $_hashObj				= null;  //(MegatronHashLib)	
	
	public static function getRedis($redisString, $mcKey = '', $isTransaction = false)
	{
		$mcKey = trim($mcKey);
		
		$redisConfig = new Yaf_Config_Ini(APPLICATION_PATH . '/conf/database.ini', YAF_ENVIRON);
		if (! ($redisConfig instanceof Yaf_Config_Ini))
		{
			exit("redis system err.");
		}
		
		if (! $redisConfig->redis->get($redisString))
		{
			$str = "300000001, redis config {$redisString} is not exist.";
			redis_real_error_log($str);
			return false;
		}
		
		$config = $redisConfig->redis->get($redisString);
		if ($config->hash && ! $mcKey)
		{
			$str = "300000002, redis config {$redisString} is hash, but not have a mcKey";
			redis_real_error_log($str);
			return false;
		}
		if ($config->hash && $mcKey)
		{
			$this->makeRedisHash($mcKey);
		}		
		
		if (! isset(self::$_redisObjPool[$redisString]))
		{
			$redisObj = new redisLib($redisString);
			
			self::$_redisObjPool[$redisString] = $redisObj;
			return $redisObj;
		}
		else 
		{
			return self::$_redisObjPool[$redisString];
		}		
	}
	
	public function __construct($t)
	{
		$redisConfig = new Yaf_Config_Ini(APPLICATION_PATH . '/conf/database.ini', YAF_ENVIRON);
		$this->_redisString = $t;
		$this->_redisConfig = $redisConfig->redis->get($t);
	}
	
	private function setHashMcKey($mcKey)
	{
		if ($this->_hashMcKey == $mcKey)
		{
			return true;
		}
		
		$this->_hashMcKey = $mcKey;
		if ($this->_redisConfig['hash'])
		{
			//生成hash key
			$this->_hashKey = $this->makeRedisHash($mcKey);
		}
		else 
		{
			$this->_hashKey = 0;
		}
		
	}
	
	private function makeRedisHash($mcKey)
	{
		if (is_null($this->_hashObj))
		{
			$this->_hashObj = new livehash();
			$keyAry 		= array_keys($this->_redisConfig['param']);
			foreach ($keyAry as $key)
			{
				$this->_hashObj->add($key);
			}
		}
		
		$res = $this->_hashObj->get($mcKey);
		return $res;		
	}
	
	private function setTransaction($isTransaction = false)
	{
		$this->_isTranscation = $isTransaction;
		return true;
	}
	
	private function connect()
	{
		$this->_errorNo = 0;
		$t 		= $this->_redisString;
		$hash	= $this->_hashKey;
		if (! $this->_redisConfig['hash'])
		{
			$ip		= $this->_redisConfig['ip'];
			$port	= $this->_redisConfig['port'];
		}
		else 
		{
			$config = $this->_redisConfig['param'];
			$ip 	= $config[$hash]['ip'];
			$port	= $config[$hash]['port'];
		}
		
		if (isset($this->_linkPool[$t][$hash]))
		{
			return $this->_linkPool[$t][$hash];
		}
		
		$loopNum = 0;
		do {
			try {
				$redisObj = new Redis();
				$redisObj->connect($ip, $port, 0.1);
				
				$this->_linkPool[$t][$hash] = $redisObj;
				return $redisObj;
			} catch (RedisException $e) {
				if ($loopNum >= 1)
				{
					$str = "100000001, real redis connect failure {$ip} {$port} {$t} {$hash}, " . $e->getMessage();
					redis_real_error_log($str);
				}
				else
				{
					$str = "1001, real redis connnect failure {$ip} {$port} {$t} {$hash},".$e->getMessage();
					redis_real_error_log($str);
				}
			}
			$loopNum++;
		} while ($loopNum < 2);
		
		$this->_errorNo = -1001;
		return false;
	}
	
	public function _close()	
	{
		foreach ($this->_linkPool as $item)
		{
			foreach ($item as $obj)
			{
				$obj->close();
			}
		}
		if ($this->_linkPool)
		{
			$this->_linkPool = array();
		}
	}
	
	public static function __closeAllConnect()
	{
		foreach (self::$_redisObjPool as $redis)
		{
			$redis->_close();
		}
		
		if (self::$_redisObjPool)
		{
			self::$_redisObjPool = array();
		}
	}
	
	public function __call($function, $arguments)
	{
		$this->_errorNo = 0;
		$loopNum = 0;
		
		$redis = $this->connect();
		if (! $redis)
		{
			return false;
		}
		
		do {
			try {
				return call_user_func_array(array($redis, $function), $arguments);
			} catch (RedisException $e) {
				if ($loopNum >= 1)
				{
					$str = "200000001, real redis {$function} failure ,".$e->getMessage();
					redis_real_error_log($str);
				}
				else
				{
					$str = "2001, real redis {$function} failure ,".$e->getMessage();
					redis_real_error_log($str);
				}
				 
				$this->_close();
			}
			
			$loopNum++;
			
		} while ($loopNum < 2 && ! $this->_isTranscation);
	}
	
	/**
	 * 设置值-有重试机制
	 * @param unknown $mcKey
	 * @param unknown $row
	 * @param number $expire
	 * @return boolean
	 */
	public function setMcRow($mcKey, $row, $expire = 0)
	{
		if (! is_numeric($row))
		{
			$row = serialize($row);
		}
		
		$expire = intval($expire);
		if (! $expire)
		{
			$expire = 86400*30;
		}
		
		$this->setHashMcKey($mcKey);
		
		$loopNum = 1;
		do {
			$redisObj = $this->connect();
			if (! $redisObj)
			{
				return false;
			}
			try {
				if ($expire > 0) {
					$redisObj->setex($mcKey, $expire, $row);
				} else {
					$redisObj->set($mcKey, $row);
				}
				return true;
			} catch (Exception $e) {
				if ($loopNum >= 2)
				{
					$str = "100000002, redis set value faild. mckey: {$mcKey} errorMsg:".$e->getMessage();
					redis_real_error_log($str);
				}
				else 
				{
					$str = "1002, redis set value faild. mckey: {$mcKey} errorMsg:".$e->getMessage();
					redis_real_error_log($str);
				}
				
				$this->_close();
			}
			
			$loopNum++;
		} while ($loopNum <= 2);
		
		return false;
	}
	
	/**
	 * 根据mcKey获取值
	 * @param unknown $mcKey
	 * @param string $errNum
	 * @return boolean|unknown
	 */
	public function getMcRow($mcKey, &$errNum = null)
	{
		$retVal = null;
		$this->setHashMcKey($mcKey);
		$loopNum = 1;
		
		do {
			$redisObj = $this->connect();
			if (! $redisObj)
			{
				return false;
			}
			try {
				$retVal = $redisObj->get($mcKey);
				if (! is_numeric($retVal))
				{
					$retVal = unserialize($retVal);
				}
			} catch (RedisException $e) {
				if ($loopNum >= 2)
				{
					$str = "100000003, redis get value faild. mckey: {$mcKey} errorMsg:".$e->getMessage();
					redis_real_error_log($str);
				}
				else
				{
					$str = "1003, redis get value faild. mckey: {$mcKey} errorMsg:".$e->getMessage();
					redis_real_error_log($str);
				}
				
				$this->_close();
			}
			
			$loopNum++;
		} while ($loopNum <= 2);
		
		return $retVal;
	}
	
	/**
	 * 设置增量
	 * @param unknown $mcKey
	 * @param unknown $num
	 * @param number $expire
	 * @return boolean|unknown
	 */
	public function increaseMcRow($mcKey, $num, $expire=0)
	{
		$retVal = false;
		$this->setHashMcKey($mcKey);
		$num = intval($num);
		$expire = ! $expire ? 86400*30 : $expire;
		
		$redisObj = $this->connect();
		if (! $redisObj)
		{
			return $retVal;
		}
		
		try {
			$retVal = $redisObj->incrBy($mcKey, $num);
			$redisObj->expire($mcKey, $expire);
			return $retVal;
		} catch (RedisException $e) {
			$str = "1005, redis incrby value faild. mckey: ".$mcKey . " errorMsg:".$e->getMessage();
			redis_real_error_log($str);
			$this->_close();
			return $retVal;
		}
		
		return $retVal;
		
	}
	
	/**
	 * 设置减量
	 * @param unknown $mcKey
	 * @param unknown $num
	 * @param number $expire
	 * @return boolean|unknown
	 */
	public function decreaseMcRow($mcKey, $num, $expire=0)
	{
		$retVal = false;
		$this->setHashMcKey($mcKey);
		$num = intval($num);
		$expire = ! $expire ? 86400*30 : $expire;
		
		$redisObj = $this->connect();
		if (! $redisObj)
		{
			return $retVal;
		}
		
		try {
			$retVal = $redisObj->decrBy($mcKey, $num);
			$redisObj->expire($mcKey, $expire);
			return $retVal;
		} catch (RedisException $e) {
			$str = "1006, redis decrby value faild. mckey: ".$mcKey . " errorMsg:".$e->getMessage();
			redis_real_error_log($str);
			$this->_close();
			return $retVal;
		}
		
		return $retVal;
		
	}
	
	/**
	 * 删除mcKey
	 * @param unknown $mcKey
	 * @return boolean|unknown
	 */
	public function delMcRow($mcKey)
	{
		$this->setHashMcKey($mcKey);
		$loopNum = 1;
		do {
			$redisObj = $this->connect();
			if (! $redisObj)
			{
				return false;
			}
			try {
				$delNum = $redisObj->del($mcKey);
				return $delNum;
			} catch (RedisException $e) {
				if ($loopNum >= 2)
				{
					$str = "100000004, redis del value faild. mckey: {$mcKey} errorMsg:".$e->getMessage();
					redis_real_error_log($str);
				}
				else
				{
					$str = "1004, redis del value faild. mckey: {$mcKey} errorMsg:".$e->getMessage();
					redis_real_error_log($str);
				}
				
			}
			$loopNum++;
		} while ($loopNum <= 2);
		
		return false;
	}
}

function redis_real_error_log($str)
{
	$date = date('Y-m-d H:i:s');
	$err  = '';
	file_put_contents("/da0/logs/redis_error.log", "{$date}, {$str} {$err}\n", FILE_APPEND);
}