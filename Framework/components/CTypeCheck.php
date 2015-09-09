<?php
/**
 * CMyFrame 类型校验
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

class CTypeCheck
{
	/*对变量类型的过滤*/
	static function type($str,$type = 'string')
	{
		
		//统一小写
		$type = strtolower($type);
		
		if(is_array($str)){
			foreach($str as $key => $val){
				$result[$key] = self::type($val,$type); 
			}
			return $result;
		}else{
			switch($type)
			{
				case "int":
					return intval($str);
				break;
				
				case "float":
					return floatval($str);
				break;
				
				case "bool":
					return (bool)$str;
				break;
				
				default:
					return self::checkString($str);
				break;
			}
		}
	}
	
	/**
	 * 对字符串过滤
	 */
	public static function checkString($str){
		$str = self::daddslashes($str);
		return $str;	
	}
		
	/**
	 * 引号转义函数
	 */
	static function daddslashes($string, $force = 0){ 
		if(!get_magic_quotes_gpc() || $force) { 
			if(is_array($string)) { 
				foreach($string as $key => $val) { 
					$string[$key] = self::daddslashes($val, $force); 
				} 
			} 
			else { 
				$string = addslashes($string); 
			} 
		} 
		return $string; 
	} 
	
	
	/**
	 * @brief 字符串截取
	 * @param string $str 被截取的字符串
	 * @param int $length 截取的长度 值: 0:不对字符串进行截取(默认)
	 * @param bool $append 是否追加省略号 值: true:追加; false:不追加;
	 * @param string $charset $str的编码格式 值: utf8:默认;
	 * @return string 截取后的字符串
	 */
	public static function substr($str, $length = 0, $append = true, $isUTF8=true){
		$byte   = 0;
		$amount = 0;
		$str    = trim($str);
		$length = intval($length);

		//获取字符串总字节数
		$strlength = strlen($str);

		//无截取个数 或 总字节数小于截取个数
		if($length==0 || $strlength <= $length){
			return $str;
		}

		//utf8编码
		if($isUTF8 == true){
			while($byte < $strlength){
				if(ord($str{$byte}) >= 224){
					$byte += 3;
					$amount++;
				}else if(ord($str{$byte}) >= 192){
					$byte += 2;
					$amount++;
				}else{
					$byte += 1;
					$amount++;
				}

				if($amount >= $length){
					$resultStr = substr($str, 0, $byte);
					break;
				}
			}
		}

		//非utf8编码
		else{
			while($byte < $strlength){
				if(ord($str{$byte}) > 160){
					$byte += 2;
					$amount++;
				}
				else{
					$byte++;
					$amount++;
				}

				if($amount >= $length){
					$resultStr = substr($str, 0, $byte);
					break;
				}
			}
		}

		//实际字符个数小于要截取的字符个数
		if($amount < $length){
			return $str;
		}

		//追加省略号
		if($append){
			$resultStr .= '...';
		}
		return $resultStr;
	}

	/**
	 * @brief 编码转换
	 * @param string &$str 被转换编码的字符串
	 * @param string $outCode 输出的编码
	 * @return string 被编码后的字符串
	 */
	public static function setCode($str,$outCode='UTF-8'){
		if(self::isUTF8($str)==false){
			return iconv('GBK',$outCode,$str);
		}
		return $str;
	}

	/**
	 * @brief 检测编码是否为utf-8格式
	 * @param string 被检测的字符串
	 * @return bool 检测结果 值: true:是utf8编码格式; false:不是utf8编码格式;
	 */
	public static function isUTF8($str){
		$result=preg_match('%^(?:[\x09\x0A\x0D\x20-\x7E] # ASCII
		| [\xC2-\xDF][\x80-\xBF] # non-overlong 2-byte
		| \xE0[\xA0-\xBF][\x80-\xBF] # excluding overlongs
		| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
		| \xED[\x80-\x9F][\x80-\xBF] # excluding surrogates
		| \xF0[\x90-\xBF][\x80-\xBF]{2} # planes 1-3
		| [\xF1-\xF3][\x80-\xBF]{3} # planes 4-15
		| \xF4[\x80-\x8F][\x80-\xBF]{2} # plane 16
		)*$%xs', $str);
		return $result ? true : false;
	}

	/**
	 * @brief 获取字符个数
	 * @param string 被计算个数的字符串
	 * @return int 字符个数
	 */
	public static function getStrLen($str){
		$byte   = 0;
		$amount = 0;
		$str    = trim($str);

		//获取字符串总字节数
		$strlength = strlen($str);

		//检测是否为utf8编码
		$isUTF8=self::isUTF8($str);

		//utf8编码
		if($isUTF8 == true){
			while($byte < $strlength){
				if(ord($str{$byte}) >= 224){
					$byte += 3;
					$amount++;
				}
				else if(ord($str{$byte}) >= 192){
					$byte += 2;
					$amount++;
				}else{
					$byte += 1;
					$amount++;
				}
			}
		}

		//非utf8编码
		else
		{
			while($byte < $strlength){
				if(ord($str{$byte}) > 160){
					$byte += 2;
					$amount++;
				}else{
					$byte++;
					$amount++;
				}
			}
		}
		return $amount;
	}
	
	/**
	 * 数据过滤器
	 */
	static function dataFilter(){
		
		//清理路由参数
		unset($_GET[0],$_GET[1],$_GET['m'],$_GET['a']);
		
		$_GET = self::daddslashes($_GET);
		$_POST = self::daddslashes($_POST);
		$_COOKIE = self::daddslashes($_COOKIE);
		
		self::injectionChecker();
	}
	
	/**
	 * 注入检测
	 */
	static function injectionChecker()
	{
		if(true == CConfig::getInstance()->load('INJECTION_CHECK')){
			
			$getfilter="'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
			$postfilter="\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
			$cookiefilter="\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
			
			foreach($_GET as $key=>$value){ 
				self::StopAttack($key,$value,$getfilter);
			}
			
			/*foreach($_POST as $key=>$value){ 
				self::StopAttack($key,$value,$postfilter);
			}
			
			foreach($_COOKIE as $key=>$value){ 
				self::StopAttack($key,$value,$cookiefilter);
			}*/
		}
	}
	
	/**
	 * 阻止敏感URL函数
	 */
	static function StopAttack($StrFiltKey,$StrFiltValue,$ArrFiltReq)
	{  
		if(is_array($StrFiltValue)){
			$StrFiltValue = implode($StrFiltValue);
		}  
		if (preg_match("/".$ArrFiltReq."/is",$StrFiltValue)==1){
			
			if(true != CConfig::getInstance()->load('ACCEPT_INJECTION_URI')){
				throw new CRouteException('该请求地址可能存在安全隐含,已被管理员拒绝!');
			}
		}      
	}  
}