<?php
/**
 * CMyFrame 空模型类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

class CEmptyModel extends CActiveRecord
{
	/**
	 * 表名
	 */
	private $_tableName;
	
	/**
	 * 虚函数
	 */
	public function tableName(){
		return $this->_tableName;
	}
	
	/**
	 * 设置表名
	 */
	public function from($tableName){
		
		$this->_tableName = $tableName;
		return $this;
	}
}