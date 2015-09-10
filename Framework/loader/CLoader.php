<?php
/**
 * CMyFrame 自动加载器
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

Class CLoader
{
	/**
	 * 单列
	 */
	public static $instance = null;
	
	/**
	 * 类映射
	 */
	private static $_loadMapp = array();
	
	/**
	 * 获取单列
	 */
	public static function getInstance(){
		
		if(null == self::$instance){
			
			self::$instance = new self();
			
			self::$instance->_setDefaultMapp();
			
			return self::$instance;
		}
		
		return self::$instance;
	}
	
	/**
	 * 自动加载
	 */
	public function load($className){

		//优先使用映射集合
		if(isset(self::$_loadMapp[$className])){
			return CLoader::import($className,self::$_loadMapp[$className]);
		}else if(file_exists($path = APP_PATH.'/modules/'.CRoute::getInstance()->getModule().'/controllers/'.$className.'.php') ){
			return CLoader::importFile($path);
		}else if(file_exists($path = APP_PATH.'/modules/'.CRoute::getInstance()->getModule().'/classes/'.$className.'.php') ){
			return CLoader::importFile($path);
		}else if(file_exists($path = CODE_PATH.'/controllers/'.$className.'.php') ){
			return CLoader::importFile($path);
		}else if(file_exists($path = FRAME_PATH.'/components/'.$className.'.php')){
			return CLoader::importFile($path);
		}else{
			$list = array();
			$importList = CConfig::getInstance()->load('IMPORT');
			if(!empty($importList)){
				foreach((array)$importList as $thisPath){
					$list[] = APP_PATH.'/'.str_replace(array('.','*'),array('/',''),$thisPath);
				}
			}
			
			//查询指定的加载目录
			foreach($list as $val){
				if(file_exists($path = $val.$className.'.php')){
					return CLoader::importFile($path);
				}else if(false !== strpos($className,'_')){
					
					//处理类名中的路径
					$path = str_replace('_','/',$className);
					if(file_exists($path = trim($val,'/\\').'/'.$path.'.php')){
						return CLoader::importFile($path);
					}
				}
			}
		}
	}
	
	/**
	 * 导入文件
	 */
	public static function importFile($filePath){
		return require($filePath);
	}
	
	/**
	 * 引入文件 仅供自身使用
	 */
	public static function import($className,$path){

		//文件不存在
		if(!file_exists($path)){
			trigger_error('[类寻址错误]路由映射中类['.$className.']所设置的文件路径不存在:'.CException::filterFileTruePath($path),E_USER_ERROR);
		}
		
		//引入文件
		$importStatus = include_once($path);
		if(!$importStatus){
			throw new CClassNotFoundException($className);
		}
		
		return $importStatus;
	}
	
	/**
	 * 注册类映射
	 */
	public static function registerClassMap(){
		
		$paramNum = func_num_args();
		
		if($paramNum == 1 && is_array(func_get_arg(0))){
			
			$maps = func_get_arg(0);
			foreach ($maps as $key => $val){
				self::$_loadMapp[$key] = $val;
			}
			
		}else if($paramNum == 2){
			
			$className = func_get_arg(0);
			$classPath = func_get_arg(1);
			self::$_loadMapp[$className] = $classPath;
		}
		
		return true;
	}
	
	/**
	 * 获取类映射
	 */
	public static function getClassMap($key = null){
		
		if(null == $key){
			return self::$_loadMapp;
		}else if(isset(self::$_loadMapp[$key])){
			return self::$_loadMapp[$key];
		}else{
			return null;
		}
	}
	
	/**
	 * 类映射
	 */
	private function _setDefaultMapp(){
		

		self::$_loadMapp = array(
		
			'CConfig' 					=> FRAME_PATH.'/boot/CConfig.php',								
			'CException'				=> FRAME_PATH.'/exception/CException.php',						
			'CRouteException'			=> FRAME_PATH.'/exception/CRouteException.php',
			'CDbException'				=> FRAME_PATH.'/exception/CDbException.php',
			'CClassNotFoundException'	=> FRAME_PATH.'/exception/CClassNotFoundException.php',			
			'CModelExcetpion'			=> FRAME_PATH.'/exception/CModelExcetpion.php',
			'CPluginException'			=> FRAME_PATH.'/exception/CPluginException.php',
			'CViewException'			=> FRAME_PATH.'/exception/CViewException.php',
			'CacheException'			=> FRAME_PATH.'/exception/CacheException.php',
			'CSessionCookieException'	=> FRAME_PATH.'/exception/CSessionCookieException.php',
		
			'CRequest'					=> FRAME_PATH.'/request/CRequest.php',
			'CUrl'						=> FRAME_PATH.'/request/CUrl.php',
			'CRoute'					=> FRAME_PATH.'/request/CRoute.php',
			'CRouteParse'				=> FRAME_PATH.'/components/CRouteParse.php',
			'CController'				=> FRAME_PATH.'/components/CController.php',
			'CTypeCheck'				=> FRAME_PATH.'/components/CTypeCheck.php',
			'Cache'						=> FRAME_PATH.'/cache/Cache.php',
			'CMemcache'					=> FRAME_PATH.'/cache/CMemcache.php',
			'CFilecache'				=> FRAME_PATH.'/cache/CFilecache.php',
			'CacheItem'					=> FRAME_PATH.'/cache/CacheItem.php',
			'CDBError'					=> FRAME_PATH.'/database/CDbError.php',
			'CSession'					=> FRAME_PATH.'/components/CSession.php',
			'CCookie'					=> FRAME_PATH.'/components/CCookie.php',
			'CEncrypt'					=> FRAME_PATH.'/components/CEncrypt.php',
			'CHash'						=> FRAME_PATH.'/components/CHash.php',
			'CRedis'					=> FRAME_PATH.'/components/CRedis.php',
			'CHttp'						=> FRAME_PATH.'/components/CHttp.php',
			'CDiContainer'				=> FRAME_PATH.'/components/CDiContainer.php',
			
			'CDatabase'					=> FRAME_PATH.'/database/CDatabase.php',
			'CBuilder'					=> FRAME_PATH.'/database/CBuilder.php',
			'CResult'					=> FRAME_PATH.'/database/CResult.php',
			'CExec'						=> FRAME_PATH.'/database/CExec.php',
			'CActiveRecord'				=> FRAME_PATH.'/database/CActiveRecord.php',
			'CModel'					=> FRAME_PATH.'/database/CModel.php',
			'CEmptyModel'				=> FRAME_PATH.'/database/CEmptyModel.php',
		
			'CHooks'					=> FRAME_PATH.'/plugin/CHooks.php',
			'CPlugin'					=> FRAME_PATH.'/plugin/CPlugin.php',
			'CArraySort'				=> FRAME_PATH.'/components/CArraySort.php',
		
			'CView'						=> FRAME_PATH.'/view/CView.php',
			'CResponse'					=> FRAME_PATH.'/response/CResponse.php',
			'CLog'						=> FRAME_PATH.'/components/CLog.php',
		);
	}
}