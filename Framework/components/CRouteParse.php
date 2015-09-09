<?php
/**
 * CMyFrame 路由解析类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

Class CRouteParse
{
	/**
	 * 默认控制器方法名称
	 */
	static private  $UrlCtrlName	= 'c';
	static private  $UrlActionName	= 'a';
	static public $requestUrl = '';
	
	/**
	 * 分层目录
	 */
	public static $buPath = '';
	
	
	/**
	 * 创建URL
	 */
	static function url($params){
		
		$params['a'] = !isset($params['a']) ? 'index' : $params['a'];
		
		if(empty($params['c']) || empty($params['a'])){
			trigger_error('[路由错误]创建URL时需指定控制器、方法名',E_WARNING);	
			return false;
		}

		if(CConfig::getInstance()->load('URLRewrite.OPEN') == 'on'){
			return self::urlWriteResult($params);
		}else{
			return self::NoWriteUrl($params);
		}
	}
	
	/**
	 * 不使用重写
	 */
	private function NoWriteUrl($params){
		
		$url = 'http://'.$_SERVER['HTTP_HOST'].'/index.php?';	
		foreach($params as $key=>$val){
			$url = $url.$key.'='.$val.'&';
		}
		$url = substr($url,0,-1);
		return $url;
	}
	
	/**
	 * 重写方式1
	 * /a/1/b/2
	 */
	private static function URLrewriteType_1($params){
		
		$url = '';$m = '';
		if(isset($params['m'])){
			$m = $params['m'];
			unset($params['m']);
		}
		
		$urlPre = self::getSubdomainUrl($m);
			
		$url .= $params['c'].'/'.$params['a'].'/';
		unset($params['a']);unset($params['c']);
		
		foreach($params as $key => $val){
			if($val == ''){
				$val = 'NULL';
			}
			$url .= $key.'/'.$val.'/';
		}
		return $urlPre.'/'.substr($url,0,-1).'';
	}
	
	/**
	 * 重写方式二
	 * a-1-b-2.html
	 */
	private function URLrewriteType_2($params){
		foreach ($params as $key => $val){
			$url .= $key.'-'.$val.'-';
		}
		return str_replace(array('a-','c-'),array('',''),trim($url,'-')).'.html';
	}
	
	/**
	 * 重写结果
	 */
	private static function urlWriteResult($params,$reWriteRule = ''){
		$paramsList = $params;
		$c = $paramsList['c']; unset($paramsList['c']);
		$a = $paramsList['a']; unset($paramsList['a']);
		if(isset($params['m'])){	
			$m = $paramsList['m']; unset($paramsList['m']);
		}else{
			$m = '';
		}
	
		if($c == CConfig::getInstance()->load('DEFAULT_CONTROLLER') && $a == CConfig::getInstance()->load('DEFAULT_ACTION')){
			$otherParam = self::getOtherParams($paramsList);
			$urlAppend = '?'.$otherParam;
			$urlPre = self::getSubdomainUrl($m);
			return $urlPre.rtrim('/'.CConfig::getInstance()->load('DEFALUT_INDEX').$urlAppend,'?&/');
		}
		

		$rewriteArr = CConfig::getInstance()->load('URLRewrite.LIST');
		$rewriteRules = array_search($c.'@'.$a,$rewriteArr);
		
		if($rewriteRules){
			if(preg_match_all("%<\w+?:.*?>%",$rewriteRules,$customRegMatch)){
				$regInfo = array();
				foreach($customRegMatch[0] as $val){
					$val     = trim($val,'<>');
					$regTemp = explode(':',$val,2);
					$regInfo[$regTemp[0]] = $regTemp[1];
				}
		
				$replaceArray = array();
				foreach($regInfo as $key => $val){
					if(strpos($val,'%') !== false){
						$val = str_replace('%','\%',$val);
					}
		
					if(isset($paramsList[$key]) && preg_match("%$val%",$paramsList[$key])){
						$replaceArray[] = $paramsList[$key];
					}else{
						$replaceArray[] = 0;
					}
					unset($paramsList[$key]);
				}
				$url = str_replace($customRegMatch[0],$replaceArray,$rewriteRules);
			}
			else{
				$url = $rewriteRules;	
			}

			if(!empty($paramsList)){
				$urlAppend = self::getOtherParams($paramsList);
				$url .= '?'.$urlAppend;
			}
			$urlPre = self::getSubdomainUrl($m);
			return $urlPre.rtrim('/'.$url,'/&?');
		}else{

			$specRewriteRules = array_search($c."@<a>",$rewriteArr);
			
			if($specRewriteRules){
				
				if(preg_match_all("%<\w+?:.*?>%",$specRewriteRules,$customRegMatch)){
					$regInfo = array();
					foreach($customRegMatch[0] as $val){
						$val     = trim($val,'<>');
						$regTemp = explode(':',$val,2);
						$regInfo[$regTemp[0]] = $regTemp[1];
					}
		
					$replaceArray = array();
					foreach($regInfo as $key => $val){
						if(strpos($val,'%') !== false){
							$val = str_replace('%','\%',$val);
						}
			
						if(isset($params[$key]) && preg_match("%$val%",$params[$key])){
							$replaceArray[] = $params[$key];
						}else{
							$replaceArray[] = 0;
						}
						unset($params[$key]);
					}
					$url = str_replace($customRegMatch[0],$replaceArray,$specRewriteRules);
					
					if(!empty($paramsList)){
						$urlAppend = self::getOtherParams($paramsList);
						$url .= '?'.$urlAppend;
					}
					$urlPre = self::getSubdomainUrl($m);
					return $urlPre.rtrim('/'.$url,'/&?');
				}	
				
			}else{
				$type = CConfig::getInstance()->load('URLRewrite.TYPE');
				$type = empty($type) ? 1: $type;
				if($type == 1){
					return self::URLrewriteType_1($params);
				}else{
					return self::NoWriteUrl($params);
				}
			}
		}
	}
	
	/**
	 * 获取动态参数
	 */
	private function getOtherParams($paramsList){
		$otherParams = '';
		foreach($paramsList as $key => $val){
			$otherParams .= $key.'='.$val.'&';
		}
		return substr($otherParams,0,-1);
	}
	
	
	/**
	 * 获取子域名
	 */
	private static function getSubdomain()
	{
		$host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
		$domain = explode('.',$host);
		return isset($domain[0])?array('m'=>$domain[0]):array();
	}
	
	/**
	 * 缓存路由表
	 */
	private static function getCacheRoute(){
		$urlArr = parse_url($_SERVER['REQUEST_URI']);
		$urlArr['path'] = trim($urlArr['path'],'\/');
		if(empty($urlArr['path'])) {$urlArr['path'] = 'index';}
		$memCacheObj = new MemoryCache();
		$urlRes = @$memCacheObj->get($urlArr['path']);
		return $urlRes;
	}

	public static function stripslashes_deep($value) 
	{ 
		$value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value); 
		return $value; 
	}
	
	/**
	 * 注入检测
	 */
	static function SQLInjectionCheck()
	{
		
		return false;
		
		if (get_magic_quotes_gpc()) { 
			//$_POST = array_map('CRouteParse::stripslashes_deep', $_POST); 
			$_GET = array_map('CRouteParse::stripslashes_deep', $_GET); 
			$_COOKIE = array_map('CRouteParse::stripslashes_deep', $_COOKIE); 
			//$_REQUEST = array_map('CRouteParse::stripslashes_deep', $_REQUEST); 
		}
		
		$check = CConfig::getInstance()->load('INJECTION_CHECK');
		if($check){
			
			$getfilter="'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
			$postfilter="\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
			$cookiefilter="\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
			
			foreach($_GET as $key=>$value){ 
				self::StopAttack($key,$value,$getfilter);
			}
			foreach($_POST as $key=>$value){ 
				self::StopAttack($key,$value,$postfilter);
			}
			foreach($_COOKIE as $key=>$value){ 
				self::StopAttack($key,$value,$cookiefilter);
			}
		}
	}
	
	/**
	 * 阻止敏感URL函数
	 */
	static function StopAttack($StrFiltKey,$StrFiltValue,$ArrFiltReq)
	{  
		if(is_array($StrFiltValue)){
			$StrFiltValue=implode($StrFiltValue);
		}  
		
		if (preg_match("/".$ArrFiltReq."/is",$StrFiltValue)==1){
			trigger_error('[安全错误]服务器拒绝执行该请求',E_ERROR);
			
			//触发控制器实例化完成的钩子函数
			CHooks::callHooks(HOOKS_SAFE_URL,$ArrFiltReq,$StrFiltValue);
			exit;
		}      
	} 
	
	/**
	 * 解析路由
	 */
	public static function getRoute(){

		if(isset($_GET['c']) && isset($_GET['a'])){
			if( !isset($_GET['m']) || empty($_GET['m'])){
				$subdomain = self::getSubdomain();
				$_GET['m'] = isset($subdomain['m'])?$subdomain['m']:$GLOBALS['CONF']['DEFAULT_MODLUE'];
			}
			$_GET[0] = $_GET['c'];
			$_GET[1] = $_GET['a'];
			return $_GET;
		}

		$URL = str_replace(array(':'),array('%3A'),self::requestURI());
		$params = array();
		
		//过滤检测
		self::SQLInjectionCheck();

		$urlArr = parse_url($URL);
		if(false == $urlArr){
			throw new CException('[路由错误]服务器无法解析该次请求');
		}

		if(isset($urlArr['query'])){
			$paramsCut = explode('&',$urlArr['query']);
			foreach ($paramsCut as $val){	
				$paramMap = explode('=',$val);
				if(isset($paramMap[0]) && count($paramMap) == 2 ){
					$params[$paramMap[0]] = isset($paramMap[1])?$paramMap[1]:null;
				}else if(isset($paramMap[0]) && count($paramMap) > 2){
					//当一个参数序列中含有多个=
					$firstEq = strpos($val,'=');
					
					$thisKey = substr($val,0,$firstEq);
					$thisVal = substr($val,$firstEq + 1,strlen($val));
					$params[$thisKey] = $thisVal;
				}
			}
		}

		$urlPath = trim($urlArr['path'],'\/');
		$urlKey = $urlPath;
		if($urlKey == '') $urlKey = 'index';
		
		if($urlPath == '/' || $urlPath == '' || in_array($urlPath,CConfig::getInstance()->load('ALLOW_INDEX'))){
			$subdomain = self::getSubdomain();
			$base = array(
				'0'=> CConfig::getInstance()->load('DEFAULT_CONTROLLER'),
				'1'=> CConfig::getInstance()->load('DEFAULT_ACTION'),
				'm'=>isset($subdomain['m'])?$subdomain['m']:CConfig::getInstance()->load('DEFAULT_MODLUE')
			);
			$_GET = array_merge($base,$params);
			return array_merge($base,$params);
		}


		$rewriteArr = CConfig::getInstance()->load('URLRewrite.LIST');
		foreach($rewriteArr as $regPattern=>$val){		
			$ControllerAction = explode('@',$val);		
			$regPatternReplace = preg_replace("%<\w+?:(.*?)>%","($1)",$regPattern);

			if(strpos($regPatternReplace,'%') !== false){
				$regPatternReplace = str_replace('%','\%',$regPatternReplace);
			}
			
			///echo "%$regPatternReplace%".'-'.$urlPath.'<br>';
			if(preg_match("%$regPatternReplace%",$urlPath,$matchValue)){	
		
				$matchAll = array_shift($matchValue);
				
				if($matchAll != $urlPath){
					continue;
				}
				
				if($matchValue){
					preg_match_all("%<\w+?:.*?>%",$regPattern,$matchReg);
					foreach($matchReg[0] as $key => $val){
						$val                     = trim($val,'<>');
						$tempArray               = explode(':',$val,2);
						$urlArray[$tempArray[0]] = isset($matchValue[$key]) ? $matchValue[$key] : '';
					}
				}
	
				if( (isset($urlArray[ self::$UrlCtrlName ]) && !preg_match("%^\w+$%",$urlArray[ self::$UrlCtrlName ]) ) || (isset($urlArray[ self::$UrlActionName ]) && !preg_match("%^\w+$%",$urlArray[ self::$UrlActionName ]) ) ){
					$urlArray  = array();
					continue;
				}
				
				foreach($ControllerAction as $key => $val){
					$paramName = trim($val,'<>');
					if( ($val != $paramName) && isset($urlArray[$paramName]) ){
						$ControllerAction[$key] = $urlArray[$paramName];
					}
				}
		
				if(isset($urlArray) && is_array($urlArray)) $urlResArr   = array_merge($ControllerAction,$urlArray);
				if(isset($urlResArr) && isset($params)) $urlResArr	 = array_merge($urlResArr,$params);
				
				$subdomain = self::getSubdomain();
					
				if(!empty($urlResArr)){
					$urlResArr = array_merge($urlResArr,$subdomain);
					self::_setCacheRoute($urlKey,$urlResArr);
					$_GET = $urlResArr;
					return isset($urlResArr)?$urlResArr:array();
				}else{
					if(!empty($ControllerAction)){
						$urlResArr	 = array_merge($ControllerAction,$params);
						$urlResArr	 = array_merge($urlResArr,$subdomain);
						$_GET = $urlResArr;
						return isset($urlResArr)?$urlResArr:array();
					}
				}
			}
		}

		/**
		 * 无路由规则
		 */
		if(empty($urlResArr)){
			$urlPathTemp = explode('.',$urlPath);
			$urlPath = isset($urlPathTemp[0])?$urlPathTemp[0]:$urlPath;	
			$tempArr = explode('/',$urlPath);

			if(isset($tempArr[0]) && isset($tempArr[1])){
				$urlResArr[0] = $tempArr[0];
				$urlResArr[1] = $tempArr[1];
				unset($tempArr[0]);unset($tempArr[1]);
	
				for($i=2;$i<(count($tempArr)+2);$i++){
					$paramVal = $i+1;
					if(isset($tempArr[$paramVal]) && ($i % 2) == 0){
						if('null' == strtolower($tempArr[$paramVal])){
							$tempArr[$paramVal] = '';
						}
						$urlResArr[$tempArr[$i]] = $tempArr[$paramVal];
					}
				}
			}else if(isset($tempArr[0]) && !isset($tempArr[1])){
				$urlResArr[0] = $tempArr[0];
				$urlResArr[1] = CConfig::getInstance()->load('DEFAULT_ACTION');
			}

			$subdomain = self::getSubdomain();
			$urlResArr['m'] = $subdomain['m'];

			if(is_array($params)) $urlResArr = array_merge($urlResArr,$params);
			
			if(!empty($urlResArr)){
				self::_setCacheRoute($urlKey,$urlResArr);
			}
			$_GET = $urlResArr;
			return isset($urlResArr)?$urlResArr:array();
		}
		
		return array('0'=>'','1'=>'','m'=>'');
	}
	
	/**
	 * 获取子域名
	 * @param $m
	 */
	private static function getSubdomainUrl($m)
	{
		$host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
		$domain = explode('.',$host);
		if(!$domain[0] || $m == ''){
			return '';
		}
		$domain[0] = $m;
		return 'http://'.implode('.',$domain);
	}
	
	/**
	 * 使用路由表
	 */
	private static function _setCacheRoute($key,$val){
		if(extension_loaded('memcache')){
			/*$memCacheObj = new MemoryCache();
			$timeOut = isset($GLOBALS['CONF']['url_write']['routingcache'])?$GLOBALS['CONF']['url_write']['routingcache']:180;
			return @$memCacheObj->set($key,$val,$timeOut);*/
		}
	}
	
	/**
	 * 获取未经处理的URL
	 */
	public static function getRequestUri(){
		$uri = '';  
        if(PHP_SAPI === 'cli'){
        	
            if (isset($_SERVER['argv'][1])) {
                $uri = ltrim($_SERVER['argv'][1], '/');
            }
            return $uri;
        }else{
       
            if (isset($_SERVER['REQUEST_URI'])) {
             
                $uri = rawurldecode($_SERVER['REQUEST_URI']);
            }else if (isset($_SERVER['PHP_SELF'])) {
            	
                $uri = $_SERVER['PHP_SELF'];
            }else if (isset($_SERVER['REDIRECT_URL'])) {
            	
                $uri = $_SERVER['REDIRECT_URL'];
            }else {   
            	
                throw new CException('[路由错误]服务器无法解析该次请求');
            }
    
            return $uri;
        }
	}
	
	/**
	 * 获取请求URI
	 */
	public static function requestURI(){
		$uri = '';  
        if(PHP_SAPI === 'cli'){
        	
            if (isset($_SERVER['argv'][1])) {
                $uri = ltrim($_SERVER['argv'][1], '/');
            }
            return $uri;
        }else{
       
            if (isset($_SERVER['REQUEST_URI'])) {

                $uri = ($_SERVER['REQUEST_URI']);
            }else if (isset($_SERVER['PHP_SELF'])) {
            	
                $uri = $_SERVER['PHP_SELF'];
            }else if (isset($_SERVER['REDIRECT_URL'])) {
            	
                $uri = $_SERVER['REDIRECT_URL'];
            }else {   
            	
                throw new CException('[路由错误]服务器无法解析该次请求');
            }
            
            //过滤分层目录
			$pathFile = trim(str_replace('/index.php','',$_SERVER['PHP_SELF']),'/');
			$uri = str_replace(array($pathFile,'//','\\\\'),array('','/','\\'),$uri);

            return $uri;
        }
	}
}