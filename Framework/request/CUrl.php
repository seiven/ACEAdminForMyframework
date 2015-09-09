<?php
/**
 * CMyFrame URL类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

Class CUrl
{
	/**
	 * 创建的url
	 */
	private $_url;
	
	/**
	 * 路由参数
	 */
	private $_params;
	
	/**
	 * 设置URL
	 */
	public function setUrl($val){
		
		$this->_url = $val;
	}
	
	/**
	 * 返回URL
	 */
	public function getUrl(){
		return $this->_url;
	}
	
	/**
	 * 设置参数
	 */
	public function setParam($val){
		
		$this->_params = $val;
	}
	
	/**
	 * 返回路由参数
	 */
	public function getParam(){
		
		return $this->_params;
	}
}