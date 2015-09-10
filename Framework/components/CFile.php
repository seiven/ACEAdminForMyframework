<?php
/**
 * CMyFrame 文件操作类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/
class CFile
{
	public static $except = array('.','..','.svn'); //无效文件或目录名
	
	/**
	 * 创建文件 
	 * @param unknown_type $file 带路径文件名
	 * @param unknown_type $content 文件内容
	 * @return string 返回成功提示
	 */
	static function add($file,$content)
	{
		$path = dirname($file);
		if(!file_exists($path)) mkdir($path,0777,true);
		if(!file_exists($file)) $fopen  = fopen($file, 'wb');	
		fputs($fopen, $content);
		fclose($fopen);
		return APP_PATH.$file;
	}
	
	/**
	 * 读写文件
	 * @param unknown_type $filename 文件路径+名称
	 * @param unknown_type $somecontent 需要写入的内容
	 * @param unknown_type $type 默认写入方式 append表示追加  其他任意参数为重写
	 */
	static function edit($filename,$somecontent = '',$type = 'append')
	{
		if(!file_exists($filename)){
			self::add($filename,'');
		}
		if (is_writable($filename)) 
		{
			if($type != 'append') 
			{
				$handle = fopen($filename, 'w');
			}
			else 
			{
				$handle = $handle = fopen($filename, 'a');
			}
			
			if (!$handle) 
			{
				throw new IException('尝试开打一个没有权限的文件: '.$filename,4001);
		        exit;
	    	} 
		    if(fwrite($handle, $somecontent) === FALSE)
		    {
		    	throw new IException('尝试操作一个不能写入的文件: '.$filename,4001);
		    	exit;
		    }
		    return APP_PATH.$filename;
		} 
		else 
		{
	    	throw new IException('尝试编辑一个没有权限的文件: '.$filename,4001);
		}	
	}
	
	/**
	 * 删除文件
	 * @param unknown_type $filename 带路径文件名
	 */
	static function del($filename)
	{
		if(file_exists($filename)) unlink($filename);
		else throw new IException('尝试删除一个不存在的文件: '.$filename,4003);
	}

	/**
	 * 取得文件后缀名
	 * @param $filename
	 */
	static function getLastname($filename)
	{
		return pathinfo($filename, PATHINFO_EXTENSION);
	}
	
	
	/**
	 * 返回路径下所有文件大小 单位B
	 * @param 目录路径
	 * @return 单位：字节 B
	 */
	static function getDirSize($dir)
    { 
    	$sizeResult = '';
        $handle = opendir($dir);
        while (false!==($FolderOrFile = readdir($handle)))
        { 
            if($FolderOrFile != "." && $FolderOrFile != "..") 
            { 
                if(is_dir("$dir/$FolderOrFile"))
                { 
                    $sizeResult += self::getDirSize("$dir/$FolderOrFile"); 
                }
                else
                { 
                    $sizeResult += filesize("$dir/$FolderOrFile"); 
                }
            }    
        }
        closedir($handle);
        return $sizeResult;
    }
    
    /**
     * 得到路径下的所有文件
     * @param $path
     */
    public static function getPathFile($path = ''){
    	if(!is_dir($path)){
    		return array();
    	}
    	
    	$class = scandir($path);
    	foreach($class as $key=>$val){
    		if(in_array($val,self::$except)){
    			unset($class[$key]);			//清理无效目录
    		}
    	}
		return $class;
    }
    
    /**
     * @brief 以树形分支列出目录下所有的子目录
     * @param $path 开始目录
     */
    static function get_path_files_tree($path = '')
    {
    	if(!is_dir($path)) return array();
    	
    	$arr = self::get_path_files_tree();
    	
    	
    }
    
    
	/**
	 * @brief  创建文件夹
	 * @param String $path  路径
	 * @param int    $chmod 文件夹权限
	 * @note  $chmod 参数不能是字符串(加引号)，否则linux会出现权限问题
	 */
	static function mkdir($path,$chmod=0777)
	{
		return is_dir($path) or (self::mkdir(dirname($path),$chmod,true) and mkdir($path,$chmod,true));
	}
	
	/**
	 * 清空目录下所有文件
	 * @param  $dir 目录名称
	 */
	static function clearDir($dir)
	{
		if(!in_array($dir,self::$except) && is_dir($dir) && is_writable($dir))
		{
			$dirRes = opendir($dir);
			while($fileName = @readdir($dirRes))
			{
				if(!in_array($fileName,self::$except))
				{
					$fullpath = $dir.'/'.$fileName;
					if(is_file($fullpath))
					{
						unlink($fullpath);
					}

					else
					{
						self::clearDir($fullpath);
						rmdir($fullpath);
					}
				}
			}
			closedir($dirRes);
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * 同步从远端下载文件
	 * @param unknown_type $url
	 */
	static public function downFile($url, $file="", $timeout=60){
		
		set_time_limit(0);
		
	    $file = empty($file) ? pathinfo($url,PATHINFO_BASENAME) : $file;
	    $dir = pathinfo($file,PATHINFO_DIRNAME);
	    !is_dir($dir) && @mkdir($dir,0755,true);
	    $url = str_replace(" ","%20",$url);
	
	    if(function_exists('curl_init')) {
	        $ch = curl_init();
	        curl_setopt($ch, CURLOPT_URL, $url);
	        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	        $temp = curl_exec($ch);
	        if(@file_put_contents($file, $temp) && !curl_error($ch)) {
	            return $file;
	        } else {
	            return false;
	        }
	    } else {
	        $opts = array(
	            "http"=>array(
	            "method"=>"GET",
	            "header"=>"")
	        );
	        $context = stream_context_create($opts);
	        if(@copy($url, $file, $context)) {
	            //$http_response_header
	            return $file;
	        } else {
	            return false;
	        }
	    }
	}
}