<?php
/**
 * CMyFrame 配置工具类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

Class CConfig
{
	/**
	 * 单列
	 */
	public static $instance = null;
	
	/**
	 * 实例中的配置数据
	 */
	private $thisConfigData = array();
	
	/**
	 * 配置名
	 */
	private $configName = null;
	
	/**
	 * 获取对象
	 */
	public static function getInstance($configName = 'main'){
		
		if(!isset(self::$instance[$configName])){
			self::$instance[$configName] = new self($configName);
			return self::$instance[$configName];
		}
		
		return self::$instance[$configName];
	}
	
	/**
	 * 构造函数
	 */
	public function __construct($configName){
		$configPath = CODE_PATH.'/configs/'.$configName.'.php';
		if(!file_exists($configPath)){
			echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>[部署错误]主配置文件不存在!';
			exit;
		}
		$this->configName = $configName;
		$this->thisConfigData = CLoader::import($configName,$configPath);
	}
	
	/**
	 * 返回配置项
	 */
	public function load($key){	

		if(isset($this->thisConfigData[$key])){
			return $this->thisConfigData[$key];
		}

		$keyList = explode('.',$key);
		if($keyList > 1){
			$thisCurrentVal = $this->thisConfigData;
			foreach($keyList as $val){
			
				if(!isset($thisCurrentVal[$val])){
					return null;
					break;
				}
				$thisCurrentVal = $thisCurrentVal[$val];
			}
			return $thisCurrentVal;
		}
		return null;
	}
} 