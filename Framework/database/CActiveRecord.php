<?php
/**
 * CMyFrame AR 基类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

abstract class CActiveRecord extends CModel
{
	/**
	 * 数据结果
	 */
	private $arResult;
	
	/**
	 * 条件
	 */
	private $condition;
	
	/**
	 * 参数
	 */
	private $params;
	
	/**
	 * where条件
	 */
	private $where;
	
	/**
	 * 最后错误
	 */
	private $_lastError;
	
	/**
	 * 每页记录
	 */
	public $pageRows = 20;
	
	/**
	 * 查询所有
	 */
	public function findAll($condition = null,$params = null,$asArray = true){
				
		$this->condition = $condition;
		$this->params = $params;
		$dbConfig = CDatabase::$configData[1];
		$tablePre = isset($dbConfig['tablePrefix']) ? $dbConfig['tablePrefix'] : '';
		
		if(empty($condition) && empty($params)){
			$tableName = $this->tableName();
			$findObject = CDatabase::getInstance()
				->select()
				->from($tableName)
				->execute();
		}else{	
			$findSql = 'SELECT * FROM '.$tablePre.$this->_getTableName().' WHERE '.$condition;
			$findObject = CDatabase::getInstance()
					->prepare($findSql)
					->execute($params);
		}

		$this->arResult = $findObject->asArray();	
		return $this;
	}
	
	/**
	 * 清空该对象
	 */
	public function flush(){
		$this->_clearAttribute();
		return true;
	}
	
	/**
	 * 条件查询
	 */
	public function findByAttributes($where,$asArray = true){
		
		$this->where = $where;
		$findObject = CDatabase::getInstance()
				->select()
				->from($this->_getTableName())
				->where($where)
				->limit(1)
				->execute();
				
		$this->arResult = $findObject->asArray();	
		return $this;
	}
	
	/**
	 * 查询单条
	 */
	public function find($condition,$params,$asArray = true){
		
		$this->condition = $condition;
		$this->params = $params;
		$dbConfig = CConfig::getInstance()->load('DB.main.slaves');
		$tablePre = isset($dbConfig['tablePrefix']) ? $dbConfig['tablePrefix'] : '';
		$findSql = 'SELECT * FROM '.$tablePre.$this->_getTableName().' WHERE '.$condition.' LIMIT 1 ';
		$findObject = CDatabase::getInstance()
				->prepare($findSql)
				->execute($params);

		$this->arResult = $findObject->asArray();
		return $this;
	}

	/**
	 * 转成数组
	 */
	public function asArray(){
		return $this->arResult;
	}
	
	/**
	 * 以key返回
	 */
	public function getKey($key,$valKey = ''){
		$value = $this->arResult;
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
	 * 获取第一条
	 */
	public function current(){
		return current((array)$this->arResult);	
	}
	
	/**
	 * 保存对象
	 */
	public function save(){

		$publicAttributeList = $this->_getAllSetAttribute();

		$tableName = $this->_getTableName();
		
		$condition =  $this->condition;
		$params = $this->params;
		$where = $this->where;
		
		if(!empty($condition)){
			
			$updateStr = '';
			foreach($publicAttributeList as $key => $val){
				$updateStr .= '`'.$key.'` = \''.$val.'\' ,';
			}
			$updateStr = substr($updateStr,0,-1);
			
			$dbConfig = CDatabase::$configData[1];
			$tablePre = isset($dbConfig['tablePrefix']) ? $dbConfig['tablePrefix'] : '';

			$updateSql = 'UPDATE `'.$tablePre.$tableName.'` SET '.$updateStr.' WHERE '.$condition;

			$result = CDatabase::getDatabase()
					->prepare($updateSql)
					->execute($params);	

			if(!$result){
				throw new CDbException('[查询错误]调用ActiveRecord->save()方法时,执行SQL错误['.$updateSql.']');
			}
					
		}else if(!empty($where)){
			$result = CDatabase::getInstance()
					->update()
					->from($tableName)
					->value($publicAttributeList)
					->where($where)
					->execute();
		}else{
		
			$result = CDatabase::getInstance()
					->insert()
					->from($tableName)
					->value($publicAttributeList)
					->execute()
					;
		}
		
		//$this->_clearAttribute();
		
		return $result;
	}
	
	/**
	 * 添加记录
	 */
	public function add($addData = array()){
		try{
			$tableName = $this->tableName();
			
			$data = CDatabase::getInstance()
								->insert()
								->from($tableName)
								->value($addData)
								->execute();
			return $data->isSuccess();
			
		}catch(CDbException $e){
			$this->_lastError =  $e->getMessage();
			return false;
		}
	}
	
	/**
	 * 总记录数
	 */
	public function count(){
		
		$count = 0;
		
		$tableName = $this->tableName();
		$getCount = CDatabase::getInstance()
								->select('COUNT(*)')
								->from($tableName)
								->execute()
								->current();
								
		return isset($getCount['COUNT(*)']) ? $getCount['COUNT(*)'] : $count;	
	}
	
	/**
	 * 更新记录
	 */
	public function update($updateData,$where){
		
		try{
			$tableName = $this->tableName();
			$data = CDatabase::getInstance()
								->update()
								->from($tableName)
								->value($updateData)
								->where($where)
								->execute();
			return $data->isSuccess();
		}catch(CDbException $e){
			$this->_lastError =  $e->getMessage();
			return false;
		}
	}
	
	/**
	 * 删除记录
	 */
	public function delete($where){
		try{
			$tableName = $this->tableName();
			$data = CDatabase::getInstance()
								->delete()
								->from($tableName)
								->where($where)
								->execute();				
			return $data->isSuccess();
		}catch(CDbException $e){
			$this->_lastError =  $e->getMessage();
			return false;
		}
		
	}
	
	/**
	 * 获取最后发生的错误
	 */
	public function getLastError(){
		return $this->_lastError;
	}
	
	/**
	 * 获取被设置的属性
	 */
	private function _getAllSetAttribute(){
		$allAttribute = (array)$this;
		$reflect = new ReflectionClass($this);
		$defaultAttributeKv = $reflect->getDefaultProperties();
		foreach($defaultAttributeKv as $key => $val){
			$defaultAttribute[] = $key;
		}
		foreach($allAttribute as $key => $val){
			
			if(in_array($key,$defaultAttribute)){
				unset($allAttribute[$key]);
			}

			if(strpos($key,$reflect->name) || strpos($key,'CActiveRecord') || strpos($key,'*') ){
				unset($allAttribute[$key]);
			}
			
			if(empty($val)){
				unset($allAttribute[$key]);
			}
		}
	
		return $allAttribute;
	}
	
	/**
	 * 清理被设置的属性
	 */
	private function _clearAttribute(){
		$allAttr = $this->_getAllSetAttribute();
		foreach($allAttr as $key => $val){
			unset($this->$key);
		}
		$this->condition = $this->params = $this->where = null;
	}
	
	/**
	 * 获取表名
	 */
	private function _getTableName(){
		$tableName = $this->tableName();
		if(empty($tableName)){
			$reflect = new ReflectionClass($this);
			throw new CModelException('[模型异常]模型['.$reflect->name.']未设置映射的数据库表名');
		}
		return $tableName;
	}
	
	/**
	 * 抽象方法 设置表名
	 */
	abstract public function tableName();
	
}