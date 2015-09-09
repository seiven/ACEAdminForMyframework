<?php
/**
 * CMyFrame 日志记录类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/
class CLog
{
	static private $instance;
	
	private $params;
	
	private $logList = array();
	
	/**
	 * 单例对象
	 */
	public function getInstance($params = null){

		if(!empty(self::$instance) && is_object(self::$instance)){
			return self::$instance;
		}
		
		self::$instance = new self($params);
		return self::$instance;
	}
	
	public function __construct($params = null){
		$this->params = $params;
	}
	
	/**
	 * 设置一条日志
	 */
	public function set($name,$content){
		if(!isset($this->logList[$name])){
			$this->logList[$name] = array();
		}
		
		array_push($this->logList[$name],$content);
	}
	
	/**
	 * 立即将日志存储到文件系统
	 */
	public function save(){
		
		if(empty($this->logList)){
			return 0;
		}
		
		//日志路径
		$logPath = APP_PATH.'/logs/userlog/'.date('Ym').'/';
		if(isset($this->params['path'])){
			$logPath = $this->params['path'];
		}
		
		if(!is_dir($logPath)){
			
			mkdir($logPath,0755,true);
		}
		
		$logList = $this->logList;
		foreach($logList as $name => $list){
			
			$fopenHandle = fopen(rtrim($logPath,'/\\').'/'.$name.'.log','a+');
			
			//锁定文件
			if (flock($fopenHandle,LOCK_EX)){
				
				foreach($list as $val){
					
					//附加分割
					$content =  '#LogTime:'.date('Y-m-d h:i:s').PHP_EOL.
								'LogContent:'.$val.PHP_EOL.PHP_EOL;
					
	
					//写入文件
					fwrite($fopenHandle,$content);
				}
				
				//解除锁定
				flock($fopenHandle,LOCK_UN);
			}
			
			//关闭流
			fclose($fopenHandle);
			unset($this->logList[$name]);
		}
		
	}
	
	public function __destruct(){
		$this->save();
	}
	
	/**
	 * 记录文本日志
	 */
	static public function write($fileName = 'userLog',$content = null,$cutDate = true){
		
		if(empty($content)){
			return false;
		}
		
		//日志路径
		if(true == $cutDate){
			$logPath = APP_PATH.'/logs/userlog/'.date('Ym').'/';
		}else{
			$logPath = APP_PATH.'/logs/userlog/';
		}

		//创建目录
		if(!is_dir($logPath)){
			mkdir($logPath,0755,true);
		}

		//附加分割
		$content =  '#LogTime:'.date('Y-m-d h:i:s').PHP_EOL.
					'LogContent:'.$content.PHP_EOL.PHP_EOL;
		
		//写入文件
		self::writeFile($logPath.$fileName.'.log',$content);
	}
	
	/**
	 * 写入文件
	 */
	public static function writeFile($logFileName,$logContent){
		
		//打开文件
		$fopenHandle = fopen($logFileName,'a+');
		
		//锁定文件
		if (flock($fopenHandle,LOCK_EX)){
			//日志内容
			fwrite($fopenHandle,$logContent);
			flock($fopenHandle,LOCK_UN);
		}
		
		//关闭流
		fclose($fopenHandle);
	}
}