<?php
/**
 * CMyFrame 数据库执行结果类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

class CExec
{
	/**
	 * 执行状态
	 */
	private $status = false;
	
	/**
	 * lastInsertId
	 */
	private $lastInsertId;
	
	/**
	 * 影响函数
	 */
	private $rows = 0;
	
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
	 * 是否成功
	 */
	public function isSuccess(){
		return $this->status;
	}
	
	/**
	 * 设置影响行数
	 */
	public function setRow($val){
		$this->rows = $val;
	}
	
	/**
	 * 获取影响行数
	 */
	public function rows(){
		return $this->rows;
	}
	
	/**
	 * 设置执行状态
	 */
	public function setStatus($val){
		$this->status = $val;
	}
	
	/**
	 * 设置使用SQL
	 */
	public function setSql($val){
		$this->sql = $val;
	}
	
	/**
	 * 获取本次SQL
	 */
	public function getSql(){
		return $this->sql;
	}
	
	/**
	 * 获取lastInsertId
	 */
	public function lastInsertId(){
		return $this->lastInsertId;
	}
	
	/**
	 * 不存在cache
	 */
	public function isCache(){
		return false;
	}
	
	/**
	 * 使用主库
	 */
	public function getIsMaster(){
		return true;
	}
	
	/**
	 * 不统计插入耗时
	 */
	public function getCastTime(){
		return 0;
	}
	
	/**
	 * 设置lastInsertId
	 */
	public function setLastInsertId($val){
		$this->lastInsertId = $val;
	}
}