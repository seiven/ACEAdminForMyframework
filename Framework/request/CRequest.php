<?php
/**
 * CMyFrame 路由请求处理类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

Class CRequest
{
	/**
	 * 单列对象
	 */
	public static $instance = null;
	
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
	 * 错误消息
	 */
	private $errorMessage;

	/**
	 * 控制器对象
	 */
	public $controllerObj;
	
	/**
	 * 控制器反射对象
	 */
	public $controllerReflection;
	
	/**
	 * 是否使用 CMyFrame 路由开关
	 */
	private static $_useRouter = true;
	
	/**
	 * 请求单列
	 */
	public static function getInstance(){
		
		if(null == self::$instance){
			
			//初始自身
			self::$instance = new self();
			
			return self::$instance;
		}
		
		return self::$instance;
	}
	
	/**
	 * 获取控制器
	 */
	public static function getController(){
		return CRoute::getInstance()->getController();
	}
	
	/**
	 * 设置控制器
	 */
	public function setController($val){
		$this->controller = $val;
	}
	
	/**
	 * 获取方法
	 */
	public static function getAction(){
		$actionPreFix = CConfig::getInstance()->load('ACTION_PREFIX');
		$action = CRoute::getInstance()->getAction();
		$actionEnd = str_replace($actionPreFix,'',$action);
		return $actionEnd;
	}
	
	/**
	 * 设置方法
	 */
	public function setAction($val){
		$this->action = $val;
	}
	
	/**
	 * 获取模块
	 */
	public static function getModule(){
		return CRoute::getInstance()->getModule();
	}
	
	/**
	 * 设置模块
	 */
	public function setModule($val){
		$this->module = $val;
	}
	
	/**
	 * 设置默认参数
	 */
	public function __construct(){
		
		//默认控制器
		$this->setController(CConfig::getInstance()->load('DEFAULT_CONTROLLER'));
		
		//默认方法
		$this->setAction(CConfig::getInstance()->load('DEFAULT_ACTION'));
		
		//默认模块
		$this->setModule(CConfig::getInstance()->load('DEFAULT_MODLUE'));
	}
	
	/**
	 * 解析路由
	 */
	public function run(){
		
		try{
			
			$GLOBALS['SYSTEM_INIT']['registerEvent'] = microtime(true);
			
			//触发路由前的钩子函数
			$oldRouteObject = serialize(CRoute::getInstance());
			CHooks::callHooks(HOOKS_ROUTE_START,CRoute::getInstance());
			
			//是否使用 CMyFrame 默认路由器
			if(true == self::$_useRouter){
				
				//解析路由
				$routeData = CRouteParse::getRoute();

				//获取路由对象
				$routeObject = $this->_checkActionPreFix($routeData);
					
			}else if($oldRouteObject != serialize(CRoute::getInstance()) && false == self::$_useRouter){
				
				//路由对象
				$routeObject = CRoute::getInstance();
	
			}else{
				
				//关闭了CMyFrame 路由器 但未重新设置路由参数
				$routeObject = CRoute::getInstance();
			}
			
			//触发路由结束的钩子函数
			CHooks::callHooks(HOOKS_ROUTE_END,$routeObject);
			
			//设置控制器
			$this->setController($routeObject->getController());
			
			//设置方法
			$this->setAction($routeObject->getAction());
		
			//设置模块
			$this->setModule($routeObject->getModule());
	
			//执行路由
			$this->routeDoing($routeObject);
	
			//触发控制器实例化完成的钩子函数
			CHooks::callHooks(HOOKS_CONTROLLER_INIT,$this->controllerObj);
			
			//执行方法
			$this->execAction($routeObject);
			
			//触发执行方法后的钩子函数
			CHooks::callHooks(HOOKS_ACTION_INIT,$this->controllerObj);
		
		}catch (CRouteException $e){

			//捕获到CMyFrame路由异常时,将之转成错误报告
			trigger_error($e->getMessage(),E_USER_ERROR);
			
			//设置404请求头
			CResponse::getInstance()->setHttpCode(404);
		}
		
		return $this;
	}
	
	/**
	 * 检查方法名前缀
	 */
	private function _checkActionPreFix($routeData){
		$prefix = CConfig::getInstance()->load('ACTION_PREFIX');
		if(!empty($prefix) && isset($routeData[1])){
			$routeData[1] = $prefix.$routeData[1];
		}
		
		//设置路由对象
		$routeObject = CRoute::getInstance();
		$routeObject->setController($routeData[0]);
		$routeObject->setAction($routeData[1]);
		$routeObject->setModule($routeData['m']);
		
		//返回对象
		return $routeObject;
	}
	
	/**
	 * 执行路由
	 */
	private function routeDoing($routeObject)
	{	
		//检查模块
		$defaultModule = CConfig::getInstance()->load('DEFAULT_MODLUE');
		$routeModule = $routeObject->getModule();
		$routeController = $routeObject->getController();
		$module = (!empty($routeModule)) ? $routeObject->getModule() :$defaultModule;

		//实例化控制器
		if(!empty($routeController)){
			
			//是否使用子域名模块路由
			$useModule = CConfig::getInstance()->load('USE_MODULE');
		
			//非主模块时
			if($defaultModule == $module || $useModule == false){
				if(file_exists($controllerPath = CODE_PATH.'/controllers/'.$routeController.'.php')){
					$this->createController($controllerPath,$routeController);
				}else if(isset($GLOBALS['CONF']['CONTROLLER_MAPPING'][$routeController])){
					if(file_exists($controllerPath = APP_PATH.'/'.$GLOBALS['CONF']['CONTROLLER_MAPPING'][$routeController].'/controllers/'.$routeController.'.php')){
						$this->createController($controllerPath,$routeController);
					};
				}
				else{
					throw new CRouteException('[路由错误]未被定义的控制器: "'.$routeController.'"');
				}
			}else{
				if(is_dir($modulePath = APP_PATH.'/modules/'.$module)){
					//检查模块内的控制器
					if(file_exists($controllerPath = $modulePath.'/controllers/'.$routeController.'.php')){
						$this->createController($controllerPath,$routeController);
					}else{
						throw new CRouteException('[路由错误]模块 ('.$module.')中未定义的控制器: "'.$routeController.'"');
					}
				}else{
					throw new CRouteException('[路由错误]请求不存在的模块: '.$module);
				}
			}
		}else{
			throw new CRouteException('[路由错误]请求的路径、文件不存在或无访问权限');
		}	
	}

	/**
	 * 创建控制器对象
	 * @params $path 依照传入的控制器位置决定是否调用H层
	 */
	private function createController($path,$name)
	{
		include($path);
		$this->controllerObj = new $name(CDiContainer::getInstance());

		//反射
		$this->controllerReflection = new ReflectionClass($name);
		
		if($this->controllerReflection->isAbstract()){
            throw new CRouteException('[路由错误]控制器不能设计为抽象类:'.$name);}
            
		if($this->controllerReflection->isInterface()){
            throw new CRouteException('[路由错误]控制器不能设计为接口:'.$name);}
            
		if(!$this->controllerReflection->isSubclassOf('CController') ){
            throw new CRouteException('[路由错误]控制器须继承CController'.$name);}
	}
	
	/**
	 * 创建方法执行
	 */
	private function createAction($controllerObj,$action)
	{
		//执行请求方法
		$controllerObj->$action();
	}
	
	/**
	 * 执行请求操作
	 */
	private function execAction($routeObject)
	{
		$controllerObj = $this->controllerObj;

		$routeAction = $routeObject->getAction();
		
		//检查执行__before魔术函数
		if($this->controllerReflection->hasMethod('__before') && $this->controllerReflection->getMethod('__before')->isPublic() ){
			$controllerObj->__before();
		}

		if(is_object($controllerObj) && !empty($routeAction)){
	
			if ( ! $this->controllerReflection->hasMethod($routeAction) ) {
				
				//检查执行__error魔术函数
				if($this->controllerReflection->hasMethod('__error') && $this->controllerReflection->getMethod('__error')->isPublic() ){
					return $controllerObj->__error($routeObject->getAction());
				}else{
					throw new CRouteException('[路由错误]请求的方法不存在:' . $routeAction);
				}
			}
            
            if( ! $this->controllerReflection->getMethod($routeAction)->isPublic()){
                throw new CRouteException('[路由错误]Action '."'".$routeAction."'".' 不具有访问权限');
            }

			//过滤预定义参数

			//执行请求
			$this->createAction($controllerObj,$routeAction);
			
		}else{
			throw new CRouteException('[路由错误]请求的方法不存在: '.(!empty($routeAction) ? $routeAction : ''));
		}
	}
	
	/**
	 * 路由状态
	 */
	public function isSuccess(){
		return ($this->errorMessage) ? true : false;
	}
	
	/**
	 * 关闭CMyFrame 默认路由
	 */
	static public function closeRouter(){
		self::$_useRouter = false;
	}
	
	/**
	 * 启用CMyFrame 默认路由
	 */
	static public function openRouter(){
		self::$_useRouter = true;
	}
	
	/**
	 * 获取是否使用CMyFrame默认路由
	 */
	static public function getUseRouterStatus(){
		return self::$_useRouter;
	}
	
	/**
	 * 获取参数
	 */
	static public function Args($name,$type = 'string',$from = null,$noFilter = null){
		
		if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) {
            throw new CRouteException('[安全错误]可能存在不安全的GET/POST参数,服务器拒绝执行该方法');
        }
        
		if(!isset($name)) {
        	return null;
        }
		   
		//统一小写
		$from = strtolower($from);
		$tempVal = null;
		
		if($from == 'get'){
			if(isset($_GET[$name])){
				$tempVal = $_GET[$name];
			}else{
				//尝试从重写规则中获取
				$list = CRouteParse::getRoute();
				if(isset($list[$name])){
					$tempVal = $list[$name];
				}
			}
		}else if($from == 'post'){
			if(isset($_POST[$name])){
				$tempVal = $_POST[$name];
			}
		}else{
			//未指定get/post时 优先去post数据 在去get数据
			if(isset($_POST[$name])){
				$tempVal = $_POST[$name];
			}else if(isset($_GET[$name])){
				$tempVal = $_GET[$name];
			}
		}
		
		if(is_string($tempVal)){
			$tempVal = urldecode($tempVal);
		}
		
		if(null != $noFilter){
			return $tempVal;
		}
	
		//过滤检测参数
		$tempVal = strip_tags($tempVal);
		$tempVal = CTypeCheck::type($tempVal,$type);
			
		//返回参数
		return $tempVal;
	}
	
	/**
	 * 获取请求的URL
	 */
	static public function getUrl(){
		return 'http://'.$_SERVER['HTTP_HOST'].CRouteParse::getRequestUri();
	}
	
	/**
	 * 获取URI
	 */
	static public function getUri(){
		return CRouteParse::requestURI();
	}
	
	/**
	 * @brief 获取客户端ip地址
	 */
	public static function getIp(){
	    $realip = NULL;
	    if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
	    	$ipArray = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
	    	foreach($ipArray as $rs){
	    		$rs = trim($rs);
	    		if($rs != 'unknown'){
	    			$realip = $rs;
	    			break;
	    		}
	    	}
	    }else if(isset($_SERVER['HTTP_CLIENT_IP'])){
	    	$realip = $_SERVER['HTTP_CLIENT_IP'];
	    }else{
	    	$realip = $_SERVER['REMOTE_ADDR'];
	    }

	    preg_match("/[\d\.]{7,15}/", $realip, $match);
	    $realip = !empty($match[0]) ? $match[0] : '0.0.0.0';
	    return $realip;
	}

	/**
	 * @brief 获取客户端浏览的上一个页面的url地址
	 * @return string 客户端上一个访问的url地址
	 */
	public static function getPreUrl(){
		return isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'Sources can not be detected';
	}
	
	/**
	 * @brief 获取客户端代理名称
	 * @return string 客户端的代理名称
	 */
	public static function getAgent(){
		return isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'';
	}
	
	/**
	 * 获取根路径
	 * @param $protocol
	 */
	public static function getHost($protocol='http'){
		$port    = $_SERVER['SERVER_PORT'] == 80 ? '' : ':'.$_SERVER['SERVER_PORT'];
		$host	 = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
		$baseUrl = $protocol.'://'.$host.$port;
		return $baseUrl;
	}
	
	/**
	 * 返回框架开始时间
	 */
	public static function getStartTime(){
		return isset($GLOBALS['SYSTEM_INIT']['frameBegin']) ? $GLOBALS['SYSTEM_INIT']['frameBegin'] : false;
	}
	
	/**
	 * 注册事件驱动器的时刻
	 */
	public static function getRegisterEventTime(){
		return isset($GLOBALS['SYSTEM_INIT']['registerEvent']) ? $GLOBALS['SYSTEM_INIT']['registerEvent'] : false;
	}
	
	/**
	 * 返回错误消息
	 */
	public function getErrorMessage(){
		return $this->errorMessage;
	}
	
	/**
	 * 分层目录
	 */
	public static function getPath(){

		return (!empty(CRouteParse::$buPath)) ? '/'.CRouteParse::$buPath : '';
	}
	
	/**
	 * 创建符合重写的链接
	 */
	public static function createUrl($params = array()){
		
		//调用路由解析类创建URL
		$baseUrl = CRouteParse::url($params);
		
		//触发创建URL完成的钩子函数
		$urlObject = new CUrl();
		$urlObject->setUrl($baseUrl);
		$urlObject->setParam($params);
		CHooks::callHooks(HOOKS_URL_CREATE,$urlObject);
		
		//返回经过Hooks处理的URL
		return $urlObject->getUrl();
	}
	
	/**
	 * 关闭POST请求
	 */
	public static function disablePOST(){
		if(!isset($_SERVER['REQUEST_METHOD'])){
			trigger_error('服务器拒绝执行该方式的请求',E_ALL);
		}
		$requestType = $_SERVER['REQUEST_METHOD'];
		if('POST' == $requestType){
			CResponse::getInstance()->setHttpCode(405);
			throw new CRouteException('该地址不支持POST请求');
		}
	}
	
	/**
	 * 关闭GET请求
	 */
	public static function disableGET(){
		if(!isset($_SERVER['REQUEST_METHOD'])){
			trigger_error('服务器拒绝执行该方式的请求',E_ALL);
		}
		$requestType = $_SERVER['REQUEST_METHOD'];
		if('GET' == $requestType){
			CResponse::getInstance()->setHttpCode(405,false);
			throw new CRouteException('该地址不支持GET请求');
		}
	}
}