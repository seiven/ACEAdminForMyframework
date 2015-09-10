<?php
/**
 * CMyFrame 主模型基类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

class CModel
{
	/**
	 * 工厂集合
	 */
	private static $modelList = array();
	
	/**
	 * 空单列
	 */
	private static $emptyModel = null;
	
	/**
	 * 工厂方法
	 */
	public static function factory($modelName = null){
		
		if(empty($modelName)){
			return CModel::getInstance();
		}
		
		if(!isset(self::$modelList[$modelName])){
			
			self::$modelList[$modelName] = new $modelName();

			return self::$modelList[$modelName];
		}
		
		return self::$modelList[$modelName];
	}
	
	/**
	 * 返回一个空模型的案列
	 */
	public static function getInstance(){
		
		if(!isset(self::$emptyModel) || !is_object(self::$emptyModel) ){
			return new CEmptyModel();
		}
		
		return self::$emptyModel;
	}
	
	/**
	 * 获取页面偏移
	 */
	public function getPageLimit($page = 1){
		$rows = isset($this->pageRows) ? $this->pageRows : 20;
		$page = $page <= 0 ? 1 : $page;
		$limit = ($page - 1) * $rows . ' , ' . $rows;
		return $limit;
	}
}