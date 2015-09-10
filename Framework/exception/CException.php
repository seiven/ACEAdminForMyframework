<?php
/**
 * CMyFrame 错误异常处理基类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

class CException extends Exception
{
	/**
	 * CMyFrame 收集到的未被捕获的异常
	 */
	public static $unCatchException = null;
	
	/**
	 * CMyFrame 收集到的错误
	 */
	public static $errorList = array();
	
	/**
	 * CMyFrame 默认错误呈现控制开关
	 */
	private static $_showErrors = true;
	
	/**
	 * 关闭CMyFrame 默认错误呈现
	 */
	public static function closeErrorShow(){
		self::$_showErrors = false;
	}
	
	/**
	 * 是否使用 CMyFrame 默认错误呈现 
	 */
	public static function getErrorShow(){
		return self::$_showErrors;
	}
	
	/**
	 * CMyFrame 异常处理基类
	 */
	public function __construct($message,$code = 1){
		parent::__construct($message,$code);
	}
	
	/**
	 * 顶层异常处理方法
	 */
	public static function getTopException($e){

		self::$unCatchException = $e;
		
		//触发异常发生时钩子函数
		CHooks::callHooks(HOOKS_EXCEPTION_HAPPEN,$e);
		
		//将异常转成错误
		$code = $e->getCode();
		$message = $e->getMessage();
		$file = $e->getFile();
		$line = $e->getLine();
		$name = get_class($e);
		
		trigger_error('未被捕获的异常['.$name.']:'.$message.' - File:'.self::filterFileTruePath($file).' - Line:'.$line,E_USER_ERROR);
	}
	
	/**
	 * 顶层错误处理
	 */
	public static function getTopErrors($code,$content = '',$file = '',$line = ''){

		if(empty($content)){
			return false;
		}

		//运行错误不报告
		if(stripos($content,'__runtime') || stripos($file,'__runtime') ){
			return false;
		}
	
		self::$errorList[] = array($code,$content,$file,$line);

		//触发错误发生是钩子函数
		CHooks::callHooks(HOOKS_ERROR_HAPPEN,$code,$content,$file,$line);
	}
	
	/**
	 * 过滤敏感信息
	 */
	public static function filterFileTruePath($path){
		return str_replace(array(APP_PATH,'\\'),array('APP_PATH','/'),$path);
	}
	
	/**
	 * CMyFrame 默认错误呈现
	 */
	public static function showErrorsView(){
		
		//调试配置项
		$debug = CConfig::getInstance()->load('DEBUG');
		
		//调试模式
		if(true == $debug){
			
			//获取视图对象
			$view = CView::factory('smarty');
			
			//克隆视图对象
			$pluginView = clone $view;
			$pluginView->template_dir = FRAME_PATH.'/exception/';
			
			//错误记录
			$categoryError = self::_categoryError();
			
			if(empty($categoryError)){
				return false;
			}
			
			//加载文件
			$includeFiles = get_included_files();

			$pluginView->assign('errorloadfile',$includeFiles);	
			$pluginView->assign('error',$categoryError);
			$pluginView->display('CErrorView.html');
		}
	}
	
	/**
	 * 确定脚本是否发生致命错误
	 */
	public static function hasFatalErrors(){
		
		//致命错误的ID
		$fatalErrorId = array(E_USER_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR,E_USER_ERROR,E_ERROR);
		
		foreach(self::$errorList as $val){
			if(in_array($val[0],$fatalErrorId)){
				return true;
			}
		}
		
		$lastErrors = error_get_last();
		
		if($lastErrors['type'] == 1){
			return true;
		}
		
		return false;
	}
	
	/**
	 * 得到出错时的文件代码
	 * @param $errorFilePath 出错文件
	 * @param $errorLine 出错行
	 */
	private static function getErrorText($errorFilePath,$errorLine)
	{
		$errorFilefp = new SplFileObject($errorFilePath, 'r');
		$beginLine = ($errorLine > 11)?$errorLine-11:1;
		$endLine = $errorLine+9;
		$text = array();
		for($i = $beginLine;$i <= $endLine; $i++){
			$errorFilefp->seek($i);
			$line = $errorFilefp->current();
			if(!empty($line)) $text[$i+1] = strip_tags($line);
		}		
		return $text;
	}
	
	/**
	 * 对错误进行分解
	 */
	private static function _categoryError(){
		
		//分级
		$categoryErrors = array();
		
		self::$errorList = array_reverse(self::$errorList);
		
		foreach(self::$errorList as $val){
			$tips = 'ErrorException';
			switch($val[0])
			{
				case 1:
					$lv = '致命错误';
					$tips = 'Fatal Error';
					break;
				case 2:
					$lv = '运行警告';
					$tips = 'Warning';
					break;
				case 4:
					$lv = '解析错误';
					break;
				case 8:
					$lv = '运行提示';
					$tips = 'Notice';
					break;
				case 16:
					$lv = 'PHP内核错误';
					$tips = 'Core Error';
					break;
				case 32:
					$lv = 'PHP内核警告';
					$tips = 'Core Warning';
					break;
				case 64:
					$lv = 'Zend致命错误';
					break;
				case 128:
					$lv = 'Zend运行警告';
					$tips = 'Zend Warning';
					break;
				case 256:
					$lv = '自定义错误';
					$tips = 'USER Error';
					break;
				case 512:
					$lv = '自定义警告';
					$tips = 'USER Warning';
					break;
				case 1024:
					$lv = '自定义通知';
					$tips = 'USER Notice';
					break;
				case 2048:
					$lv = '代码通知';
					return false;	
				case 10000:
					$lv = '安全警告';
					return false;	
				default:
					$lv = '致命错误';
			}	
			//错误代码
			$errorText = self::getErrorText($val[2],$val[3]);

			$categoryErrors[] = array(
				'code' => $val[0],
				'content' => $val[1],
				'file' => self::filterFileTruePath($val[2]),
				'line' => $val[3],
				'level' => $lv,
				't_tips' => $tips,
				'detail' => $errorText,
			);
		}
		
		//返回分级后的错误
		return $categoryErrors;
	}
}