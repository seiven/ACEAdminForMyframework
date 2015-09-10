<?php
/**
 * CMyFrame 服务管理
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/
class CDiContainer
{
	
	/**
	 * 对象保存
	 */
	static private $instance;
	
	/**
	 * 服务映射集合
	 */
	private $serviceList = array();
	
	/**
	 * 获取单例对象
	 */
	public static function getInstance(){
		
		if(!empty(self::$instance) && is_object(self::$instance) ){
			return self::$instance;
		}
		
		self::$instance = new self();
		return self::$instance;
	}
	
	/**
	 * 注册对象
	 */
	public function set($name,$defined,$params = null){
		$this->serviceList[$name] = array(
			'defined' => $defined,
			'params' => (!empty($params)) ? $params : (is_array($defined) && isset($defined['params']) ? $defined['params'] : null),
			'object' => (is_object($defined)) ? $defined : null,
		);
		return true;
	}
	
	/**
	 * 移除组件
	 */
	public function del($name){
		if(isset($this->serviceList[$name])){
			unset($this->serviceList[$name]);
			return true;
		}
		return false;
	}
	
	/**
	 * 判断组件存在
	 */
	public function has($name){
		return isset($this->serviceList[$name]) ? true : false;
	}
	
	/**
	 * 获取对象
	 */
	public function get($name,$params = null){
		
		if(!isset($this->serviceList[$name])){
			throw new CClassNotFoundException('[类寻址错误]尝试获取尚未注册的组件:'.$name);
		}
		
		$serviceData = $this->serviceList[$name];
		if(isset($serviceData['defined']) && is_object($$serviceData['defined']) ){
			return $serviceData['defined'];
		}else if(is_string($serviceData['defined'])){
			
			if(strpos($serviceData['defined'],'::')){
				$setParams = !empty($params) ? $params : $serviceData['params'];
				$this->serviceList[$name]['object'] = $thisObject = call_user_func_array($serviceData['defined'],$setParams);
				return $thisObject;
			}
			
			require_once($serviceData['defined']);
			$setParams = !empty($params) ? $params : $serviceData['params'];
			$this->serviceList[$name]['object'] = $thisObject = new $name($setParams);
			return $thisObject;
		}else if(is_array($serviceData['defined']) && isset($serviceData['defined']['path']) ){
			require_once($serviceData['defined']['path']);
			$setParams = !empty($params) ? $params : $serviceData['params'];
			$this->serviceList[$name]['object'] = $thisObject = new $name($setParams);
			return $thisObject;
		}else if($serviceData['defined'] == null){
			$setParams = !empty($params) ? $params : $serviceData['params'];
			$this->serviceList[$name]['object'] = $thisObject = new $name($setParams);
			return $thisObject;
		}
	
		throw new CClassNotFoundException('[类寻址错误]尝试获取尚未注册的组件:'.$name);
	}
	
	/**
	 * 获取单例对象
	 */
	public function singleton($name,$params = null){
		
		if(!isset($this->serviceList[$name])){
			throw new CClassNotFoundException('[类寻址错误]尝试获取尚未注册的组件:'.$name);
		}
		
		$serviceData = $this->serviceList[$name];
		if(isset($serviceData['defined']) && is_object($$serviceData['defined']) ){
			return $serviceData['defined'];
		}else if(is_string($serviceData['defined'])){
			
			if(is_object($serviceData['object'])){
				return $serviceData['object'];
			}
			
			if(strpos($serviceData['defined'],'::')){
				$setParams = !empty($params) ? $params : $serviceData['params'];
				$this->serviceList[$name]['object'] = $thisObject = call_user_func_array($serviceData['defined'],$setParams);
				return $thisObject;
			}
			
			require_once($serviceData['defined']);
			$setParams = !empty($params) ? $params : $serviceData['params'];
			$this->serviceList[$name]['object'] = $thisObject = new $name($setParams);
			return $thisObject;
			
		}else if(is_array($serviceData['defined']) && isset($serviceData['defined']['path']) ){
			
			if(is_object($serviceData['object'])){
				return $serviceData['object'];
			}
			
			require_once($serviceData['defined']['path']);
			$setParams = !empty($params) ? $params : $serviceData['params'];
			$this->serviceList[$name]['object'] = $thisObject = new $name($setParams);
			return $thisObject;
		}else if($serviceData['defined'] == null){
			
			if(is_object($serviceData['object'])){
				return $serviceData['object'];
			}
			
			$setParams = !empty($params) ? $params : $serviceData['params'];
			$this->serviceList[$name]['object'] = $thisObject = new $name($setParams);
			return $thisObject;
		}
	
		throw new CClassNotFoundException('[类寻址错误]尝试获取尚未注册的组件:'.$name);
	}
}