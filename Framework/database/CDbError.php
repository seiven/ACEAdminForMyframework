<?php
/**
 * MyFrame Plugin 扩展MyFrame 使发生错误时保存文本错误日志
 * @copyright UncleChen 2013
 * @author UncleChen
 * @version UncleChen v 0.0.1 2013/12/7
 */

class CDBError{

	/**
	 * sql错误
	 */
	private $_sqlErrorCode;
	
	/**
	 * 驱动错误
	 */
	private $_driverErrorCode;
	
	/**
	 * 错误消息
	 */
	private $_errorMessage;
	
	/**
	 * sql语句
	 */
	private $_sql;
	
	/**
	 * 设置sql错误ID
	 */
	public function setSQLErrorCode($val){
		$this->_sqlErrorCode = $val;
	}
	
	/**
	 * 设置驱动错误
	 */
	public function setDriverErrorCode($val){
		$this->_driverErrorCode = $val;
	}
	
	/**
	 * 错误消息
	 */
	public function setErrorMessage($val){
		$this->_errorMessage = $val;
	}
	
	/**
	 * 返回标准SQLSTATUS
	 */
	public function getSqlstatus(){
		return $this->_sqlErrorCode;
	}
	
	/**
	 * 返回MySQL错误码
	 */
	public function getCode(){
		return $this->_driverErrorCode;
	}
	
	/**
	 * 返回错误消息
	 */
	public function getMessage(){
		return $this->_errorMessage;
	}
	
	/**
	 * 设置SQL
	 */
	public function setSql($val){
		$this->_sql = $val;
	}
	
	/**
	 * 返回SQL
	 */
	public function getSql(){
		return $this->_sql;
	}
	
}