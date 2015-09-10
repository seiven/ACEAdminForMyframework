<?php
/**
 * CMyFrame 路由类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

Class CRoute
{
	/**
	 * 控制器
	 */
	private $controller;
	
	/**
	 * 方法
	 */
	private $action;
	
	/**
	 * 模块
	 */
	private $module;
	
	/**
	 * 单列
	 */
	public static $instance = null;
	
	/**
	 * 获取单列
	 */
	public static function getInstance(){
		
		if(null == self::$instance){
			
			self::$instance = new self();
			
			return self::$instance;
		}
		
		return self::$instance;
	}
	
	/**
	 * 设置默认控制器方法
	 */
	public function __construct(){
		
		$this->setController(CConfig::getInstance()->load('DEFAULT_CONTROLLER'));
		
		$this->setAction(CConfig::getInstance()->load('DEFAULT_ACTION'));
		
		$this->setModule(CConfig::getInstance()->load('DEFAULT_MODLUE'));
	}
	
	/**
	 * 设置控制器
	 */
	public function setController($controller){
		$this->controller = $controller;
	}
	
	/**
	 * 设置方法
	 */
	public function setAction($action){
		$this->action = $action;
	}
	
	/**
	 * 设置模块
	 */
	public function setModule($module){
		$this->module = $module;
	}
	
	/**
	 * 获取控制器
	 */
	public function getController(){
		return $this->controller;
	}
	
	/**
	 * 获取方法
	 */
	public function getAction(){
		return $this->action;
	}
	
	/**
	 * 获取模块
	 */
	public function getModule(){
		return $this->module;
	}
}