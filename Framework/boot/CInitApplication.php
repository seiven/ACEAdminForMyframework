<?php
/**
 * CMyFrame 框架初始化抽象类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

require(FRAME_PATH.'/loader/CLoader.php');
abstract Class CInitApplication
{
	/**
	 * 初始化项目结果
	 */
	private $_initData = array();
	
	/**
	 * 执行
	 */
	public function __construct(){
		
		//初始化自动加载
		$this->_initData['autoLoad'] = $this->_initAutoLoad();
		
		//初始化加载目录
		$this->_initData['includeClass'] = $this->_initIncludeClass();

		//初始化事件驱动器
		$this->initData['setHooks'] = $this->_initHooks();
		
		//初始化异常处理
		$this->_initData['catchException'] = $this->_initTopException();
		
		//初始化时区设置
		$this->_initData['timeZone'] = $this->_initTimeZone();
		
		//初始化CookieDomain
		$this->_initData['cookie'] = $this->_initCookie();
		
		//初始化session
		$this->_initData['session'] = $this->_initSession();
		
		//初始化Gzip压缩传输
		$this->_initData['gzip'] = $this->_initGzip();
		
		//注册组件
		$this->_initData['di'] = $this->_initCDiContainer();
	}
	
	/**
	 * 自动加载
	 */
	private function _initAutoLoad(){
		
		return spl_autoload_register(array($this,'classAutoLoad'));
	}
	
	/**
	 * 时区
	 */
	private function _initTimeZone(){
		$timeZone = CConfig::getInstance()->load('TIME_ZONE');
		date_default_timezone_set($timeZone); //时区
	}
	
	/**
	 * 实例化服务容器
	 */
	private function _initCDiContainer(){
		
		$systemDi  = CDiContainer::getInstance();
		
		//注册默认服务
		$systemDi->set('CRequest',CRequest::getInstance());
		$systemDi->set('CResponse',CResponse::getInstance());
		
		$componentList = CConfig::getInstance('main')->load('components');
		if(empty($componentList)){
			return false;
		}
		
		//注册服务
		foreach((array)$componentList as $key => $val){
			$systemDi->set($key,$val);
		}
		return true;
	}
	
	/**
	 * 加载路径
	 */
	private function _initIncludeClass(){
		
		$importPath = CConfig::getInstance()->load('IMPORT');
		
		if(empty($importPath)){
			return true;
		}
		
		$truePathList = array();
		foreach((array)$importPath as $val){
			$truePathList[] = APP_PATH.'/'.str_replace(array('.','*'),array('/',''),$val);
		}
		
		$truePath = implode(PATH_SEPARATOR,$truePathList);
		return (false != set_include_path(get_include_path() . PATH_SEPARATOR . $truePath)) ? true : false;
	}
	
	/**
	 * 异常处理
	 */
	private function _initTopException(){
		
		//set_error_handler('CException::getTopErrors',E_ALL);
    	set_exception_handler('CException::getTopException');
    	register_shutdown_function(array('CInitApplication','webShutdown'));
    	error_reporting(0);
	}
	
	/**
	 * 装载插件
	 */
	private function _initHooks(){
		
		$usePlugin = CConfig::getInstance()->load('LOAD_PLUGIN');
		if(true === $usePlugin){
			return CHooks::loadPlugin();
		}
		return false;
	}
	
	/**
	 * 设置Gzip压缩
	 */
	private function _initGzip(){
		
		$useGzip = CConfig::getInstance()->load('GZIP');
		
		if(true == $useGzip){
			
			//压缩等级
			$gzipLevel = CConfig::getInstance()->load('GZIP_LEVEL');
			
			//设置压缩
			ini_set('zlib.output_compression', 'On');
    		ini_set('zlib.output_compression_level',!empty($gzipLevel) ? $gzipLevel : 6);
		}
	}
	
	/**
	 * 设置Cookie
	 */
	private function _initCookie(){
		
		//cookie域
		$cookieDoamin = CConfig::getInstance()->load('COOKIE_DOMAIN');
		if(!empty($cookieDoamin)){
			ini_set('session.cookie_domain',$cookieDoamin);
			return true;
		}
		
		return false;
	}
	
	/**
	 * 设置Session
	 */
	private function _initSession(){
		
		$sessionMemcache = CConfig::getInstance()->load('SESSION_MEMCACHE');
		
		//使用memcacheSession
		if(extension_loaded('memcache') && $sessionMemcache == true){
			
			$sessionHost = CConfig::getInstance()->load('SESSION_MEMCAHCE_HOST');
			
	    	ini_set("session.save_handler", "memcache");  
			ini_set("session.save_path", "tcp://".$sessionHost); 
    	}
		
		$autoSession = CConfig::getInstance()->load('AUTO_SESSION');
		if(!empty($autoSession) && true == $autoSession ){
			session_start();
			return true;
		}
		
		return false;
	}
	
	/**
	 * 自动加载
	 */
	private function classAutoLoad($className){
	
		$loadStatus = CLoader::getInstance()->load($className);
	}
	
	/**
	 * 请求结束
	 */
	public static function webShutdown(){
		
		//传递引起脚本中断的致命错误
		$lastError = error_get_last();
		if(!empty($lastError) && in_array($lastError['type'],array(E_USER_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR,E_USER_ERROR,E_ERROR))){
			CException::getTopErrors($lastError['type'],$lastError['message'],$lastError['file'],$lastError['line']);
		}
		

		//脚本因错误而中断
		if(true == CException::hasFatalErrors()){
			
			//最后发生的错误
			$lastError = error_get_last();
				
			//设置503请求头
			CResponse::getInstance()->setHttpCode(503,false);
		}
		
		//触发执行结束钩子函数
		CHooks::callHooks(HOOKS_SYSTEM_SHUTDOWN,CException::$errorList);
		
		//是否使用CMyFrame 默认错误呈现
		if(true == CException::getErrorShow()){
			
			//使用CMyFrame 默认进行错误呈现
			CException::showErrorsView();
		}
	}
	
	/**
	 * 抽象方法 执行请求
	 */
	abstract function GetRequest();
} 