<?php
/**
 * CMyFrame Redis辅助类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

class CRedis
{
	/**
	 * 单列对象
	 */
	private static $instance = null;
	
	/**
	 * Redis连接对象
	 */
	private $_redisConn;
	
	/**
	 * 返回单列对象
	 */
	public static function getInstance(){
		
		if(!extension_loaded('Redis')){
			throw new CacheException('[缓存错误]请安装或者启用Redis扩展');
		}
		
		if(empty(self::$instance)){
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * 构造函数
	 */
	public function __construct(){
		
		//创建Redis连接
		$this->_redisConn = $connection = $this->_getRedisConnection();
	}
	
	/**
	 * 创建Redis连接
	 */
	private function _getRedisConnection(){
		
		//默认参数
		$redisHost = '127.0.0.1';
		$redisPort = 6379;
		$timeout = 3;
		
		//配置参数
		$setRedisHost = CConfig::getInstance()->load('REDIS_HOST');
		$setRedisPort = CConfig::getInstance()->load('REDIS_PORT');
		$setTimeout = CConfig::getInstance()->load('REDIS_TIMEOUT');
		
		
		if(!empty($setRedisHost)){
			$redisHost = $setRedisHost;
		}
		
		if(!empty($setRedisPort)){
			$redisPort = $setRedisPort;
		}
		
		if(!empty($setTimeout)){
			$timeout = $setTimeout;
		}

		//连接Redis
		$redisObject = new Redis();
		$redisObject->connect($redisHost,$redisPort,$timeout);

		return $redisObject;
	}
	
	/**
	 * 返回redis对象
	 */
	public function getRedis(){
		return $this->_redisConn;
	}
	
	/**
	 * 返回所有子键键名
	 */
	public function keys($key){
		return $this->getRedis()->keys($key);
	}
	
	/**
	 * 设置值
	 */
	public function set($key,$value,$expire = 0){

		$value = json_encode($value);
		
		//不超时
	 	if($expire == 0){
            $ret = $this->getRedis()->set($key, $value);
        }else{
            $ret = $this->getRedis()->setex($key, $expire, $value);
        }
        
        return $ret;
	}
	
	/**
	 * 取值
	 */
	public function get($key){
		$val = $this->getRedis()->get($key);
		return json_decode($val,true);
	}
	
	/**
	 * 链表长度
	 */
	public function llen($key){
		$val = $this->getRedis()->llen($key);
		return $val;
	}
	
	/**
	 * 链表的偏移值
	 */
	public function lindex($list,$index){
		$val = $this->getRedis()->lindex($list,$index);
		return json_decode($val,true);
	}
	
	/**
	 * 删除key
	 */
	public function del($key){
		return $this->getRedis()->del($key);
	}
	
	public function rPush($key,$val){
		return $this->getRedis()->rPush($key,$val);
	}
	
	public function lPush($key,$val){
		return $this->getRedis()->lPush($key,$val);
	}
	
	public function lPop($key){
		return $this->getRedis()->lPop($key);
	}
	
	public function rPop($key){
		return $this->getRedis()->rPop($key);
	}
	
	public function lSize($key){
		return $this->getRedis()->lSize($key);
	}
	
	public function lGet($key){
		return $this->getRedis()->lGet($key);
	}
	
	public function lGetRange($key,$b,$l){
		return $this->getRedis()->lGetRange($key,$b,$l);
	}
	
	public function lRemove($key,$b,$l){
		return $this->getRedis()->lRemove($key,$b,$l);
	}
	
	
	public function sAdd($key,$b){
		return $this->getRedis()->sAdd($key,$b);
	}
	
	public function sSize($key){
		return $this->getRedis()->sSize($key);
	}
	
	public function __call($method,$arg){
		return call_user_func_array(array($this->getRedis(),$method),$arg);
	}
	
}