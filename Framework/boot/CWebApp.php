<?php
/**
 * CMyFrame 引导类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

//核心文件
require(FRAME_PATH.'/boot/CApplication.php');

Class CWebApp
{
	/**
	 * 创建WebApplication应用
	 */
	public static function createApp(){
		return new CApplication();
	}
} 