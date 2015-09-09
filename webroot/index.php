<?php
/**
 * APP 入口文件
 */
// 启动时间
$GLOBALS['SYSTEM_INIT']['frameBegin'] = microtime(true);

// 定义系统变量
if (! defined('APP_PATH'))
    define('APP_PATH', dirname(__FILE__).'/../');
if (! defined('FRAME_PATH'))
    define('FRAME_PATH', APP_PATH . '/Framework');
if (! defined('CODE_PATH'))
    define('CODE_PATH', APP_PATH . '/application');
    
require (FRAME_PATH . '/boot/CWebApp.php');
    
    // 启动框架
CWebApp::createApp()->GetRequest();

