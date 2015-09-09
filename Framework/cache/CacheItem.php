<?php
/**
 * CMyFrame 缓存数据对象
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

class CacheItem
{
	/**
	 * 缓存的key
	 */
	private $_key;
	
	/**
	 * 缓存的值
	 */
	private $_value;
	
	/**
	 * 缓存的时间
	 */
	private $_timeout;
	
	/**
	 * 设置key
	 */
	public function setKey($val){
		$this->_key = $val;
	}
	
	/**
	 * 返回key
	 */
	public function getKey(){
		return $this->_key;	
	}
	
	/**
	 * 设置值
	 */
	public function setValue($val){
		$this->_value = $val;
	}
	
	/**
	 * 返回值
	 */
	public function getValue(){
		return $this->_value;
	}
	
	/**
	 * 设置timeout
	 */
	public function setTimeout($val){
		$this->_timeout = $val;
	}
	
	/**
	 * 返回timeout
	 */
	public function getTimeout(){
		return $this->_timeout;	
	}
}