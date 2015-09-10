<?php
/**
 * CMyFrame 视图类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

class CView
{
	/**
	 * 视图工厂列表
	 */
	private static $viewList = array();
	
	/**
	 * 视图名称
	 */
	private $_viewName;
	
	/**
	 * 视图对象
	 */
	private $_viewObject;
	
	/**
	 * 视图工厂
	 */
	public static function factory($viewName = null){
	
		if(empty($viewName)){
			throw new CViewException('[视图错误]获取视图对象时需设置使用的模块引擎名称');
		}

		//统一小写
		$viewName = strtolower($viewName);

		if(!isset(self::$viewList[$viewName])){

			self::$viewList[$viewName] = new self($viewName);

			return self::$viewList[$viewName]->getViews();
		}
		
		return self::$viewList[$viewName]->getViews();
	}
	
	/**
	 * 构造
	 */
	public function __construct($viewName){
		
		$this->_viewName = $viewName;
		
		$viewConfigs = CConfig::getInstance()->load('TEMPLATE');

		if(!isset($viewConfigs[$viewName])){
			trigger_error('[视图错误]使用配置中不存在的模板引擎:'.$viewName,E_USER_ERROR);
		}
		
		$thisViewConfig = $viewConfigs[$viewName];
		$templatePath = isset($thisViewConfig['TEMPLATE_PATH'])?$thisViewConfig['TEMPLATE_PATH']:'';
		$viewConfItem = isset($thisViewConfig['CONF_INFO'])?$thisViewConfig['CONF_INFO']:array();
		
		if(!file_exists($templatePath)){
			trigger_error('[视图错误]未能找到指定模板引擎['.$viewName.']的主文件:'.$templatePath,E_USER_ERROR);
		}

		CLoader::importFile($templatePath);

		$viewObject = new $viewName();

		if('smarty' == $viewName){
			
			$viewObject->template_dir = $viewConfItem['template_dir'];
				
			//编译目录
			$compile_dir = $viewConfItem['compile_dir'];
			if(!is_dir($compile_dir)){
				if(false  == mkdir($compile_dir,true,0755)){
					echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>[部署错误]CMyFrame无法创建缓存目录,请确定服务器权限';
					exit;
				}
				chmod($compile_dir,0777);
			}
			$viewObject->compile_dir = $compile_dir;
			
			//缓存目录
			$cache_dir = $viewConfItem['cache_dir'];
			if(!is_dir($cache_dir)){
				if(false == mkdir($cache_dir,true,0755)){
					echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>[部署错误]CMyFrame无法创建缓存目录,请确定服务器权限';
					exit;
				}
				chmod($cache_dir,0777);
			}
			
			$viewObject->cache_dir = $cache_dir;
			
			//分隔符
			$viewObject->left_delimiter = $viewConfItem['left_delimiter'];
			$viewObject->right_delimiter = $viewConfItem['right_delimiter'];
			
			//使用PHP语法
			$viewObject->allow_php_tag = $viewConfItem['allow_php_tag'];
			
			//缓冲
			$viewObject->caching = $viewConfItem['caching'];
			$viewObject->cache_lifetime = $viewConfItem['cache_lifetime'];
			
			//注册函数
			$viewObject->register_function('url',array('CRequest','createUrl'));
			$viewObject->register_function('PageInfo',array('CSmarty','showPageData'));
			$viewObject->register_function('substr',array('CSmarty','cn_substr'));
			$viewObject->register_function('sayTime',array('CSmarty','sayTime'));
			
			//注册块函数
			$viewObject->register_block('checkRight',array('CSmarty','checkRight'));
			
			//设置默认数据
			CSmarty::setInitData($viewObject);
			
		}else{
			
			//设置所有配置项
			foreach((array)$viewConfItem as $key => $val){
				$viewObject->$key = $val;
			}
		}
		
		//第一次获取视图时
		CHooks::callHooks(HOOKS_VIEW_GET,$viewObject);
			
		$this->_viewObject = $viewObject;
	}
	
	
	
	
	/**
	 * 获取视图对象
	 */
	public function getViews(){
		return $this->_viewObject;
	}
}