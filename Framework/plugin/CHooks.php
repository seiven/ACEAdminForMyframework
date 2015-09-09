<?php
/**
 * CMyFrame Hooks 类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

define('HOOKS_ROUTE_START','HOOKS_ROUTE_START');
define('HOOKS_ROUTE_END','HOOKS_ROUTE_END');
define('HOOKS_CONTROLLER_INIT','HOOKS_CONTROLLER_INIT');
define('HOOKS_ACTION_INIT','HOOKS_ACTION_INIT');
define('HOOKS_EXECUTE_BEFORE','HOOKS_EXECUTE_BEFORE');
define('HOOKS_EXECUTE_END','HOOKS_EXECUTE_END');
define('HOOKS_EXECUTE_ERROR','HOOKS_EXECUTE_ERROR');
define('HOOKS_ERROR_HAPPEN','HOOKS_ERROR_HAPPEN');
define('HOOKS_EXCEPTION_HAPPEN','HOOKS_EXCEPTION_HAPPEN');
define('HOOKS_SYSTEM_SHUTDOWN','HOOKS_SYSTEM_SHUTDOWN');
define('HOOKS_CACHE_SET','HOOKS_CACHE_SET');
define('HOOKS_CACHE_GET','HOOKS_CACHE_GET');
define('HOOKS_LOADER_START','HOOKS_LOADER_START');
define('HOOKS_VIEW_GET','HOOKS_VIEW_GET');
define('HOOKS_VIEW_SHOW','HOOKS_VIEW_SHOW');
define('HOOKS_URL_CREATE','HOOKS_URL_CREATE');


class CHooks
{		
	/**
	 * 插件列表
	 */
	private static $_pluginList = array();
	
	/**
	 * 钩子列表
	 */
	private static $_hooks = array();
	
	/**
	 * 加载失败的插件
	 */
	private static $_failLoadPluginList = array();
	
	/**
	 * 所有钩子
	 */
	private static $_allHooks = array();
	
	/**
	 * 加载插件
	 */
	static public function loadPlugin(){
		
		$loadPlugin = CConfig::getInstance()->load('LOAD_PLUGIN');
		$loadList = CConfig::getInstance()->load('LOAD_LIST');
		
		if(false === $loadPlugin){
			return false;
		}
		
		$pluginPath = APP_PATH.'/plugins/';
		$confPath = CConfig::getInstance()->load('PLUGIN_PATH');
		if(null != $confPath){
			$pluginPath = APP_PATH.'/'.trim($confPath).'/';
		}
		
		//定义插件宏
		define('CPLUGIN_PATH',$pluginPath);
		
		$hooksFile = self::_getPathFile($pluginPath,$loadList);
			
		foreach($hooksFile as $val){
				
			$pluginRegisterFilePath = $pluginPath.$val.'/'.$val.'.php';	

			if(!file_exists($pluginRegisterFilePath)){
				self::$_failLoadPluginList[] = array('pluginName' => $val);
				continue;
			}
				
			CLoader::importFile($pluginRegisterFilePath);
			$thisPluginObject = new $val();
			$thisPluginReflection = new ReflectionClass($thisPluginObject);
				
			$isSubCPlugin = $thisPluginReflection->isSubclassOf('CPlugin');
			if(false == $isSubCPlugin){
				self::$_failLoadPluginList[] = $val;
				continue;
			}
				
			$thisPluginObject->setHooks();
				
			$thisPluginData['pluginName'] = isset($thisPluginObject->pluginName) ? $thisPluginObject->pluginName : '';
			$thisPluginData['author'] = isset($thisPluginObject->author) ? $thisPluginObject->author : '';
			$thisPluginData['version'] = isset($thisPluginObject->version) ? $thisPluginObject->version : '';
			$thisPluginData['copyright'] = isset($thisPluginObject->copyright) ? $thisPluginObject->copyright : '';
			$thisPluginData['date'] = isset($thisPluginObject->date) ? $thisPluginObject->date : '';
			$thisPluginData['description'] = isset($thisPluginObject->description) ? $thisPluginObject->description : '';
			self::$_pluginList[] = $thisPluginData;
		}
	}
	
	/**
	 * 获取已注册的钩子列表
	 */
	static public function getHooksRegisterList(){
		return self::$_hooks;
	}
	
	/**
	 * 获取加载成功的插件
	 */
	static public function getPluginLoadSuccess(){
		return self::$_pluginList;
	}
	
	/**
	 * 获取记载失败的插件
	 */
	static public function getPluginLoadFail(){
		return self::$_failLoadPluginList;
	}
	
	/**
	 * 调用钩子函数
	 */
	static public function callHooks(){
		
		$paramsNum = func_num_args();

		if($paramsNum < 1){
			throw new CPluginException('[Hooks错误]调用Hooks函数时需指定钩子名称');
		}
		
		$hooksName = func_get_arg(0);
		$paramsList = func_get_args();
		unset($paramsList[0]);
		
		$functionList = isset(self::$_hooks[$hooksName]) ? self::$_hooks[$hooksName] : array();
		
		if(empty($functionList)){
			return false;
		}
		
		$functionList = self::_setHooksFunctionLevel($functionList);
	
		foreach($functionList as $key => $pluginVal){
			$keyArr = explode('|',$pluginVal['key']);
			if(count($keyArr) != 2){
				continue;
			}
			$pluginName = $keyArr[0];
			$functionName = $keyArr[1];

			if(!is_object($pluginVal['callObject'])){
				trigger_error('[Hooks警告]已注册的钩子函数['.$functionName.']传递的调用对象错误',E_USER_WARNING);
				continue;
			}
			
			if(!method_exists($pluginVal['callObject'],$functionName)){
				trigger_error('[Hooks警告]插件['.$pluginName.']中注册到改钩子的调用函数不存在:'.$functionName,E_USER_WARNING);
				continue;
			}
			
			$callStatus = call_user_func_array(array($pluginVal['callObject'], $functionName),$paramsList);
			
			if(isset(self::$_hooks[$hooksName][$pluginName][$functionName]['callNum'])){
				self::$_hooks[$hooksName][$pluginName][$functionName]['callNum']++;
			}
		}
		
		return true;
	}
	
	/**
	 * 获取钩子列表
	 */
	public static function getHooksList(){
		self::$_allHooks = array(
			
		);
	}
	
	/**
	 * 设置钩子函数的调用等级
	 */
	static private function _setHooksFunctionLevel($list){

		$sortArr = array();
		
		foreach((array)$list as $key => $val){
			foreach((array)$val as $f => $fv){
				$fv['key'] = $key.'|'.$f;
				$sortArr[] = $fv;
			}
		}
		
		$sortArr = CArraySort::sortArrayDesc($sortArr,'callLevel');
		return $sortArr;
	}
	
	/**
	 * 注册钩子函数
	 */
	static public function registerHook($hooksName,$runFunction,$runObject,$callLevel = 1){
		
		if(!is_object($runObject)){
			throw new CPluginException('[Hooks错误]注册钩子函数传递的调用对象错误[param 3]');
		}
		
		$className = get_class($runObject);
		
		self::$_hooks[$hooksName][$className][$runFunction]['callNum'] = 0;
		self::$_hooks[$hooksName][$className][$runFunction]['callObject'] = $runObject;
		self::$_hooks[$hooksName][$className][$runFunction]['callLevel'] = $callLevel;
		
		return true;
	}
	
	/**
	 * 读取目录下的文件
	 */
	static private function _getPathFile($path,$canList = null){
		$fileList = scandir($path);
		$result = array();
		$unsetList = array('.','..','.svn');
		foreach((array)$fileList as $val){
			if(!in_array($val,$unsetList)){
				$result[] = $val;
			}
		}
		
		if(null == $canList || empty($canList)){
			return $result;
		}else{
			$canUse = array();
			foreach($result as $val){
				if(in_array($val,$canList)){
					$canUse[] = $val;
				}
			}
			
			return $canUse;
		}
	}
}