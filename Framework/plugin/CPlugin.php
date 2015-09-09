<?php
/**
 * CMyFrame CPlugin 插件基类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

class CPlugin
{
	
	/**
	 * 获取一个提供给插件使用的视图对象
	 */
	public static function getView(){
		
		return  CView::factory('smarty');
	}
}