<?php
/**
 * CMyFrame Cache基类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

class Cache
{
	/**
	 * 单列对象
	 */
	private static $instance = array();
	
	/**
	 * 缓存对象
	 */
	private $_cacheObject;
	
	/**
	 * 缓存默认时间
	 */
	private $_cacheTimeout = 86400;
	
	/**
	 * 请求单列
	 */
	public static function getInstance($type = null){
		
		if(null == $type){
			
			//默认使用memcahe
			$type = 'memcache';
			
			//获取默认设置
			$defaultCacheType = CConfig::getInstance()->load('CACHE.DEFAULT_CACHE');
			
			if(!empty($defaultCacheType)){
				$type = $defaultCacheType;
			}
		}

		if( !isset(self::$instance[$type]) || null == self::$instance[$type]){
			
			//初始自身
			self::$instance[$type] = new self($type);
			
			return self::$instance[$type];
		}
		
		return self::$instance[$type];
	}
	
	/**
	 * 构造函数
	 */
	public function __construct($type){
		
		//统一小写
		$type = strtolower($type);
		
		switch ($type){
			
			case 'memcache':
				$this->_cacheObject = new CMemcache();
				break;
				
			case 'filecache':
				$this->_cacheObject = new CFilecache();
				break;
				
			default:
				throw new CacheException('[缓存错误]CMyFrame目前暂不支持['.$type.']缓存方式');
				break;
		}
		
	}
	
	/**
	 * 缓存对象
	 */
	public function _getCacheObject(){
		return $this->_cacheObject;
	}
	
	/**
	 * 设置缓存
	 */
	public function set($key,$content,$time = null){
		
		if(empty($time)){
			$time = $this->_cacheTimeout;
		}
		
		//组装cacheItem对象传递给Hooks函数
		$cacheItemObject = new CacheItem();
		$cacheItemObject->setKey($key);
		$cacheItemObject->setValue($content);
		$cacheItemObject->setTimeout($time);
		
		//触发钩子函数
		CHooks::callHooks(HOOKS_CACHE_SET,$cacheItemObject);
		
		$hookEndContent = $cacheItemObject->getValue();
		$endContent = serialize($hookEndContent);
		
		return $this->_cacheObject->set($cacheItemObject->getKey(),$endContent,$cacheItemObject->getTimeout());
		
	}
	
	/**
	 * 读取缓存
	 */
	public function get($key){
		
		if(empty($key)){
			throw new CacheException('[缓存错误]请指定读取缓存的key');
		}
		
		//直接返回的数据
		$data = $this->_cacheObject->get($key);
		$content = unserialize($data);
		
		//结果对象
		$cacheItemObject = new CacheItem();
		$cacheItemObject->setKey($key);
		$cacheItemObject->setValue($content);
		
		//触发获取缓存时的Hooks
		CHooks::callHooks(HOOKS_CACHE_GET,$cacheItemObject);
		
		//经过HOOKS的数据
		$endContent = $cacheItemObject->getValue();
		
		return !empty($endContent) ? $endContent : null;
	}
	
	/**
	 * 删除缓存
	 */
	public function del($key,$timeout = 1){
		
		return $this->_cacheObject->del($key,$timeout);
	}
		
	/**
	 * 清空缓存
	 */
	public function clear(){
		return $this->_cacheObject->clear();
	}
	
	/**
	 * 查看状态
	 */
	public function showStatus(){
		return $this->_cacheObject->showStatus();
	}
}