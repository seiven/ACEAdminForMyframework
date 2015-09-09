<?php
/**
 * CMyFrame 数据查询结果类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

class CResult
{
	/**
	 * 结果
	 */
	private $value = array();
	
	/**
	 * 查询SQL
	 */
	private $sql;
	
	/**
	 * 耗时
	 */
	private $castTime = 0;
	
	/**
	 * 是否使用主库
	 */
	private $isMaster;
	
	/**
	 * 是否来自缓存
	 */
	private $isFromCache = false;
	
	
	private $whereValue = array();
	
	/**
	 * 设置where
	 */
	public function setWhereValue($val){
		$this->whereValue = $val;
	}
	
	/**
	 * 返回where
	 */
	public function getWhere(){
		return $this->whereValue;
	}
	
	/**
	 * 转换成数组
	 */
	public function asArray(){
		
		$val = $this->value;
		$result = array();
		
		foreach((array)$val as $key => $val){
			$result[$key] = (array)$val;
		}
		return $result;
	}
	
	/**
	 * 将结果放到cache
	 */
	public function setCache($key,$time = 3600){
		
		Cache::getInstance()->set($key,$this,$time);

		return $this;
	}
	
	/**
	 * 获取用时
	 */
	public function getCastTime(){
		return $this->castTime;
	}
	
	/**
	 * 获取记录数
	 */
	public function count(){
		return count($this->value);
	}
	
	/**
	 * 以指定的key获取
	 */
	public function offsetGet($key){
		
		if(isset($this->value[$key])){
			return (array)$this->value[$key];
		}else{
			return array();
		}
	}
	
	/**
	 * 以指定的key重置数组
	 */
	public function getKey($key,$valKey = ''){
		$value = $this->value;
		$result = array();
		foreach($value as $val){
			$thisVal = (array)$val;
			if(!isset($thisVal[$key])){
				throw new CDbException('[查询错误]获取查询结果时以不存在的key['.$key.']重组数组');
			}
			
			if(isset($thisVal[$valKey])){
				$result[$thisVal[$key]] = $thisVal[$valKey];
			}else{
				$result[$thisVal[$key]] = $thisVal;
			}
		}
		return $result;
	}
	
	/**
	 * 获取第一条记录
	 */
	public function current(){
		
		if(empty($this->value)){
			return array();
		}
		
		return (array)current($this->value);
	}
	
	/**
	 * 设置值
	 */
	public function setValue($val = array()){
		$this->value = $val;
	}
	
	public function setIsMaster($val){
		$this->isMaster = $val;
	}
	
	/**
	 * 返回是否使用主库
	 */
	public function getIsMaster(){
		return $this->isMaster;
	}
	
	public function setIsCache($val){
		$this->isFromCache = $val;
	}
	
	/**
	 * 是否从cache获取数据
	 */
	public function isCache(){
		return $this->isFromCache;
	}
	
	/**
	 * 设置SQL
	 */
	public function setSql($val){
		$this->sql = $val;
	}
	
	/**
	 * 获取SQL
	 */
	public function getSql(){
		return $this->sql;
	}
	
	/**
	 * 设置使用时间
	 */
	public function setCastTime($val){
		$this->castTime = $val;
	}
}