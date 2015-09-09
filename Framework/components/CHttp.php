<?php
/**
 * CMyFrame http操作
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/
class CHttp
{
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