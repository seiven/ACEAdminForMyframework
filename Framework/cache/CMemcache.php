<?php
/**
 * CMyFrame Memcache类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

class CMemcache{
	
	/**
	 * memcache对象
	 */
	private $_cacheObject;
	
	/**
	 * 默认端口
	 */
	private $_defaultPort = 11211;
	
	/**
	 * 构造函数
	 */
	public function __construct(){
		
		//判断是否存在Memcache扩展
		if(!extension_loaded('memcache') ){
			throw new CacheException('[缓存错误]检测到服务器尚未启用Memcache扩展');
		}
		
		//判断有无memcache配置项
		$cacheItem = CConfig::getInstance()->load('CACHE');
		
		//不存在memcache服务器地址
		if(!isset($cacheItem['MEMORY_LIST'])){
			throw new CacheException('[缓存错误]配置项[CACHE->MEMORY_LIST]不存在,无法获取Memcache服务器地址');
		}
		
		//实例化
		$memCacheObject = $this->_cacheObject = new Memcache();
	
		//添加服务器
		if(is_object($memCacheObject)){
			if(is_array($cacheItem['MEMORY_LIST'])){
				foreach($cacheItem['MEMORY_LIST'] as $val){
					$this->_addServer($val);	
				}
			}else if(is_string($cacheItem['MEMORY_LIST'])){
				$this->_addServer($cacheItem['MEMORY_LIST']);
			}
		}else{
			throw new CacheException('[缓存错误]获取Memcache对象时发生错误,请确定memecache扩展是否正确');
		}
	}
	
	/**
	 * 向memcahe添加服务器
	 */
	private function _addServer($address){
		
		$addressArray = explode(':',$address);
		$host         = $addressArray[0];
		$port         = isset($addressArray[1]) ? $addressArray[1] : $this->_defaultPort;
		return $this->_cacheObject->addServer($host,$port);
	}
		
	/**
	 *  写入缓存
	 */
	public function set($key,$data,$expire = ''){
		return $this->_cacheObject->set($key,$data,MEMCACHE_COMPRESSED,$expire);
	}

	/**
	 * 读取缓存
	 */
	public function get($key){
		return $this->_cacheObject->get($key);
	}

	/**
	 * 删除缓存
	 */
	public function del($key,$timeout = ''){
		return $this->_cacheObject->delete($key,$timeout);
	}

	/**
	 * 删除全部缓存
	 */
	public function clear(){
		return $this->_cacheObject->flush();
	}
	
	/**
	 * 查看缓存区状态
	 */
	public function showStatus(){
		
		$list = CConfig::getInstance()->load('CACHE.MEMORY_LIST');
		
		if(is_array($list)){
			return $this->_cacheObject->getExtendedStats();
		}else{
			return $this->_cacheObject->getStats();
		}
	}
}