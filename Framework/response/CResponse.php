<?php
/**
 * CMyFrame 响应类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/
class CResponse
{
	/**
	 * 单列对象
	 */
	public static $instance = null;
	
	/**
	 * http状态
	 */
	private $_httpCode = 200;
	
	/**
	 * http头信息
	 */
	private $_header = array();
	
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
	 * 设置默认发送信息
	 */
	public function __construct(){
		
		//设置HTTP状态码
		$this->setHttpCode(200);
	
		//设置HTTP头
		$this->addHeader('Powered : CMyFrame Version 3.0(C Extension)');
	}
	
	/**
	 * 设置HTTP状态码
	 */
	public function setHttpCode($code = 200,$isOver = true){
		
		//不覆盖
		if(false == $isOver && $this->_httpCode != 200){
			return $this;
		}
		
		$this->_httpCode = $code;
		
		return $this;
	}
	
	public function send(){
		
		//发送状态码
		$this->_sendHttpCode();
		
		//发送头
		$this->_sendHeader();
		
		return $this;
	}
	
	/**
	 * 添加HTTP头信息
	 */
	public function addHeader($header){
		
		//字符串
		if(is_string($header)){
			$this->_header[] = $header;
		}else if(is_array($header)){	
			foreach($header as $val){
				$this->_header[] = $val;
			}
		}
		
		return $this;
	}
	
	/**
	 * 发送HTTP状态码
	 */
	private function _sendHttpCode(){
		$protocols = isset($_SERVER['SERVER_PROTOCOL'])?$_SERVER['SERVER_PROTOCOL']:'HTTP/1.1';
		header($protocols.' '.$this->_httpCode);
	}
	
	/**
	 * 定向
	 */
	public function redirect($params,$code = 301){
		
		$url = CRequest::createUrl($params);
		$this->setHttpCode($code);	
		header("Location:".$url);
	}
	
	/**
	 * 发送头
	 */
	private function _sendHeader(){
		
		//发送所有头
		foreach($this->_header as $val){
			header($val);
		}
	}
	
	/**
	 * 结束请求
	 */
	public function end(){
		exit();
	}
	
	/**
  	 * 发送HTTP请求
  	 */
    static public function sendHttpRequest($url,$params=array(),$method='GET',$header=array(),$timeout=0){
        if (function_exists('curl_init')){
            $ch = curl_init();
            if( $method == 'GET'){
                if( strpos($url,'?')) $url .= '&'.http_build_query($params);
                else $url .= '?'.http_build_query($params);

                curl_setopt($ch, CURLOPT_URL, $url);
            }elseif ($method == 'POST'){
                $post_data = is_array($params) ? http_build_query($params) : $params;
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                curl_setopt($ch, CURLOPT_POST, true);
            }
            
            //https不验证证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            if(!empty($header)){
                //curl_setopt($ch, CURLOPT_NOBODY,FALSE);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE );
            }
            if($timeout) curl_setopt ($ch, CURLOPT_TIMEOUT, $timeout); //设置超时
            $content = curl_exec($ch);
            $info = curl_getinfo($ch);
			$errors  = curl_error($ch);
			
            return array('content'=>$content,'info'=>$info,'error'=>$errors);
        }else{
            $data_string = http_build_query($params);
            $context = array(
                'http' =>array('method' => $method,
                    'header' => 'Content-type: application/x-www-form-urlencoded'."\r\n".
                        'Content-length: '.strlen($data_string),
                    'content' => $data_string)
            );
            $contextid = stream_context_create($context);
            $sock=fopen($url, 'r', false, $contextid);
            if ($sock){
                $result='';
                while (!feof($sock)) $result.=fgets($sock, 4096);
                fclose($sock);
            }
            return $result;
        }
    }
}