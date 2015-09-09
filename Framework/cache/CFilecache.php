<?php
/**
 * CMyFrame File类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

class CFilecache{
	
	private $_path = '__runtime/cache';			//存放目录	
	private $_lastName = '.data';				//文件后缀名
	private $__directoryLevel = 1;        		//目录层级
	public static $except = array('.','..','.svn'); //无效文件或目录名
	
	/**
	 * 构造函数，实列化存放路径
	 */
	function __construct(){
		
		$configPath = CConfig::getInstance()->load('CACHE.FILE_PATH');
		
		$this->_path = !empty($configPath) ? $configPath : $this->_path;
		
		if(!file_exists($this->_path)){
			mkdir($this->_path,0755,true);
		}
	}
	
	/**
	 * 获得缓存文件路径
	 * @param $key 缓存键名
	 */
	public function getSaveFileName($key){
		
		$key      = str_replace(' ','',$key);				//去掉空格
		$cacheDir = rtrim($this->_path,'\\/').'/';			//移除路径末尾的/\并加上/ 用/。。不然LINUX要出问题啊
		if($this->_directoryLevel > 0){
			
			$hash      = abs(crc32($key));
			$cacheDir .= $hash % 1024;
			for($i = 1;$i < $this->directoryLevel;++$i){
				if(($prefix = substr($hash,$i,2)) !== false){
					$cacheDir .= '/'.$prefix;
				}
			}
		}
		return $cacheDir.'/'.md5($key).$this->_lastName;
	}
	
	/**
	 * 写入缓存
	 * @param  $key		缓存键名
	 * @param  $content	缓存内容
	 * @param  $time 失效时间
	 */
	public function set($key,$content,$time = ''){
		$fileName = $this->getSaveFileName($key);
		if(!file_exists($dirname=dirname($fileName))){
			mkdir($dirname,0775,true);
		}
		$writeLen = file_put_contents($fileName,$content);	//写入文件

		if($writeLen == 0){
			throw new CacheException('[缓存错误]缓存内容已过期或意外丢失:'.$key);
		}else{
			chmod($fileName,0777);
			$expire = time() + $time;
			touch($fileName,$expire);
			return true;
		}
	}
	
	/**
	 * 读取缓存
	 * @param  string $key 缓存的唯一key值,当要返回多个值时可以写成数组
	 * @return mixed  读取出的缓存数据;null:没有取到数据或者缓存已经过期了;
	 */
	public function get($key){
		$fileName = $this->getSaveFileName($key);
		if(file_exists($fileName)){
			if(time() > filemtime($fileName)){
				$this->del($key,0);
				return null;
			}else{
				$GLOBALS['cache_log']['read']  = $GLOBALS['cache_log']['read']  + 1;
				return file_get_contents($fileName);
			}
		}else{
			return null;
		}
	}
	
	/**
	 * 删除缓存
	 * @param   $key     缓存的唯一key值
	 * @param	$timeout 在间隔单位时间内自动删除,单位：秒
	 */
	public function del($key,$timeout = ''){
		$fileName = $this->getSaveFileName($key);
		if(file_exists($fileName)){
			if($timeout > 0){
				$timeout = time() + $timeout;
				return touch($fileName,$timeout);
			}else{
				return unlink($fileName);
			}
		}else{
			return true;
		}
	}
	
	/**
	 * 删除全部缓存
	 * @return bool true:成功；false:失败;
	 */
	public function clear(){
		
		return self::_clearDir($this->_path);
	}
	
	/**
	 * 清理目录
	 */
	private static function _clearDir($dir){
		if(!in_array($dir,self::$except) && is_dir($dir) && is_writable($dir)){
			$dirRes = opendir($dir);
			while($fileName = @readdir($dirRes)){
				if(!in_array($fileName,self::$except)){
					$fullpath = $dir.'/'.$fileName;
					if(is_file($fullpath)){
						unlink($fullpath);
					}else{
						self::_clearDir($fullpath);
						rmdir($fullpath);
					}
				}
			}
			closedir($dirRes);
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * 查看缓存状态
	 */
	public function showStatus()
	{
		return null;
	}
}