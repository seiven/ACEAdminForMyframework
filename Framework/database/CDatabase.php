<?php
/**
 * CMyFrame 数据库操作类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

class CDatabase
{
	/**
	 * 单列
	 */
	public static $instance = null;

	/**
	 * PDO对象
	 */
	public static $objectPdo = null;
	
	/**
	 * 数据对象
	 */
	private $dataObject = null;
	
	/**
	 * SQL创建对象
	 */
	private $cBuilder = null;
	
	/**
	 * 配置数据
	 */
	public static $configData = null;

	/**
	 * 获取单列
	 */
	public static function getInstance($isMaster = false,$dbConf = 'main'){

		if(!isset(self::$instance[intval($isMaster).$dbConf])){

			//实例自身
			self::$instance[intval($isMaster).$dbConf] = new self($isMaster,$dbConf);

			return self::$instance[intval($isMaster).$dbConf]->cBuilder;
		}
		
		return self::$instance[intval($isMaster).$dbConf]->cBuilder;
	}
	
	/**
	 * 获取PDO对象
	 */
	public static function getDatabase($configName = 'main',$isMaster = true){

		if(empty($configName)){
			$configName = 'main';
		}
		
		if( isset(self::$objectPdo[intval($isMaster).$configName]) ){
			return self::$objectPdo[intval($isMaster).$configName];
		}
		
		$dbConfig = CConfig::getInstance()->load('DB.'.$configName);

		if(!isset($dbConfig['master'])){
			throw new CDbException('[配置错误]getDataBase方法尝试获取不存在的配置项:[Config->DB->master]');
		}
		
		if(true == $isMaster){
			$thisConfigData = $dbConfig['master'];
			self::$configData[intval($isMaster).$configName] = $thisConfigData;
			self::$configData[intval(!$isMaster).$configName] = $dbConfig['slaves'];
		}
		
		if(false == $isMaster){
			$thisConfigData = (!isset($dbConfig['slaves'])) ? $dbConfig['master'] : $dbConfig['slaves'];
			self::$configData[intval($isMaster).$configName] = $thisConfigData;
			self::$configData[intval(!$isMaster).$configName] = $dbConfig['master'];
		}
		
		try{
			$pdoObject = new PDO($thisConfigData['connectionString'],$thisConfigData['username'],$thisConfigData['password']);
			$pdoObject->query('set names '.$thisConfigData['charset']);

		}catch (PDOException $pdoException){
			
			//尝试启用备用库
			if(true == $isMaster){
				
				//是否允许启用备库
				$writeConf = self::$configData[intval($isMaster).$configName];
				if(isset($writeConf['slavesWrite']) && true == $writeConf['slavesWrite'] ){
					
					//尝试连接备库
					try{
						
						//从库配置
						$slaverConf = self::$configData[intval(!$isMaster).$configName];
		
						$pdoObject = new PDO($slaverConf['connectionString'],$slaverConf['username'],$slaverConf['password']);
						$pdoObject->query('set names '.$slaverConf['charset']);
						
					}catch(PDOException $pdoExceptionAgain){
						
						$dbError = new CDBError();
						$dbError->setSQLErrorCode('CMyFrame');
						$dbError->setDriverErrorCode('CMyFrame');
						$dbError->setErrorMessage('主库连接失败后,尝试连接从库,连接从库依旧失败');
						CHooks::callHooks(HOOKS_EXECUTE_ERROR,$dbError);
						
						//切换数据依旧无法连接
						throw new CDbException('[数据库错误]主库连接失败后,尝试连接从库依旧失败:'.$pdoExceptionAgain->getMessage());	
					}
					
				}else{
					
					//不允许使用备库
					$dbError = new CDBError();
					$dbError->setSQLErrorCode('CMyFrame');
					$dbError->setDriverErrorCode('CMyFrame');
					$dbError->setErrorMessage('主库连接失败后,不允许尝试连接从库');
					CHooks::callHooks(HOOKS_EXECUTE_ERROR,$dbError);
					
					throw new CDbException('[数据库错误]连接主数据库失败,且不允许尝试使用从库:'.$pdoException->getMessage());
				}
				
			}else{
				
				//是否使用主库进行读取
				$readConf = self::$configData[intval($isMaster).$configName];
				if(isset($readConf['masterRead']) && true == $readConf['masterRead'] ){
					
					$errorMessage = '从库连接失败后,尝试连接主库,主库连接成功';
					
					try{	
						//主库配置
						$masterConf = self::$configData[intval(!$isMaster).$configName];
		
						$pdoObject = new PDO($masterConf['connectionString'],$masterConf['username'],$masterConf['password']);
						$pdoObject->query('set names '.$masterConf['charset']);

					}catch(PDOException $pdoExceptionAgain){
						
						$dbError = new CDBError();
						$dbError->setSQLErrorCode('CMyFrame');
						$dbError->setDriverErrorCode('CMyFrame');
						$dbError->setErrorMessage('从库连接失败后,尝试连接主库,连接主库依旧失败');
						CHooks::callHooks(HOOKS_EXECUTE_ERROR,$dbError);
						
						//切换数据依旧无法连接
						throw new CDbException('[数据库错误]从库连接失败后,尝试连接主库依旧失败:'.$pdoExceptionAgain->getMessage());	
					}
					
				}else{	

					//发生SQL错误时 触发钩子
					$dbError = new CDBError();
					$dbError->setSQLErrorCode('CMyFrame');
					$dbError->setDriverErrorCode('CMyFrame');
					$dbError->setErrorMessage('从库连接失败后,不允许尝试连接主库');
					CHooks::callHooks(HOOKS_EXECUTE_ERROR,$dbError);
					
					throw new CDbException('[数据库错误]连接从数据库失败,且不允许尝试使用主库:'.$pdoException->getMessage());
				}
				
				//发生SQL错误时 触发钩子
				$dbError = new CDBError();
				$dbError->setSQLErrorCode('CMyFrame');
				$dbError->setDriverErrorCode('CMyFrame');
				$dbError->setErrorMessage($errorMessage);
				CHooks::callHooks(HOOKS_EXECUTE_ERROR,$dbError);
			}
		}
		
		self::$objectPdo[intval($isMaster).$configName] = $pdoObject;
		
		return $pdoObject;
	}
	
	/**
	 * 构造函数
	 */
	public function __construct($isMaster,$dbConf){
		$this->cBuilder = new CBuilder();
		$this->cBuilder->isMaster = intval($isMaster);
		$this->cBuilder->configName = $dbConf;
	}
}