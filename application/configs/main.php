<?php

/**
 * 主项目配置文件
 */
return array(
	/*主表配置*/
	'DB' => array(
        'main' => array(
            'master' => array(
                'connectionString' => 'mysql:host=localhost;dbname=aceadmin',
                'slavesWrite' => false,
                'username' => 'root',
                'password' => '',
                'charset' => 'utf8',
                'tablePrefix' => 'wl_'
            ),
        )
    ),
	
	/*视图配置*/
	'TEMPLATE' => array(
        
        // smarty模板
        'smarty' => array(
            'TEMPLATE_PATH' => APP_PATH . '/vendors/smarty/libs/Smarty.class.php',
            'CONF_INFO' => array(
                'template_dir' => APP_PATH . '/application/views/default', // 模板目录
                'compile_dir' => '__runtime/script', // 编译目录
                'cache_dir' => '__runtime/html',
                'left_delimiter' => '<{',
                'right_delimiter' => '}>',
                'allow_php_tag' => true,
                'caching' => false,
                'cache_lifetime' => 100
            )
        )
    ),
    
    'URLRewrite' => array(
        'OPEN' => 'on',
        'TYPE' => 1, // 默认的重写规则,即如果重写规则不含有某URL,但框架却又开启重写时的默认规则,0为index.php?a=1&b=2;1为 a/1/b/2 格式;2为 a-1-b-2.html格式;
        'ROUTE_TIME_LIMIT' => 3600, // 路由表更新时间
        'LIST' => array()
    ),
    
    // 控制器映射 通过此配置 可以在主程序 映射子模块的控制.此配置用以解决一些跨域问题
    'CONTROLLER_MAPPING' => array(
        'im' => 'modules/group'
    ), // 表示当在主域名下 访问im控制器时 将会映射到group.xx.com/im/模块下
       
    // 注册到系统类目录 此目录下的类自动载入
    'IMPORT' => array(
        'application.models.*',
        'application.classes.*'
    ),
    
    // 共享配置
    // 'COOKIE_DOMAIN' =>'.mysns.com', #配置此项可以让多子域名session共享
    'AUTO_SESSION' => true, // 自动启用SESSION
    'COOKIE_SECURE_CODE' => 'NDJU293YCSNKAP', // cookie 加密密钥
    'SESSION_MEMCACHE' => false, // 将Session放入memcache
    'SESSION_MEMCAHCE_HOST' => 'ip:port',
    
    // 调试配置
    'DEBUG' => true, // 调式、运营模式
    'DEBUG_IP' => array(),
    'FUZZY_TIPS' => true, // 框架级错误提示(一般为路由错误,命名错误等)
    'LOAD_PLUGIN' => false, // 使用插件机制
                            // 'LOAD_LIST' => array('plugins_dir_name'), #可选配置 以该顺序加载指定插件
    'PLUGIN_PATH' => 'plugins',
    
    // GZip配置
    'GZIP' => true, // GZIP
    'GZIP_LEVEL' => 6, // GZIP级别
                       
    // 路由配置
    'DEFAULT_CONTROLLER' => 'base', // 默认控制器
    'DEFAULT_ACTION' => 'index', // 默认方法
    'ACTION_PREFIX' => 'Action_', // 控制器前缀
    'DEFAULT_MODLUE' => 'test', // 默认模块
    'USE_MODULE' => false, // 是否检测子域名模块
    'DEFALUT_INDEX' => 'index', // 默认的首页,可以填写asp,aspx,php,jsp等,但需要下面的allow_index配置项目中允许该首页通过
    'ALLOW_INDEX' => array(
        'index'
    ), // 允许通过的首页入口文件
    'PAGE_404' => APP_PATH . 'application/views/templates/tips/404.html', // 404模板位置
    'INJECTION_CHECK' => true, // 注入检测
    'ACCEPT_INJECTION_URI' => true, // 是否允许含有高危字符的URL通过
                                    
    // 杂项
    'CHARSET' => 'utf-8', // 字符集
    'DEFAULT_PAGE_NUM' => 25, // 默认每次分页的记录数
    'magic_quotes_gpc' => true, // 魔术引号状态
    'TIME_ZONE' => 'PRC', // 时区
    
    'ERROR_LOG' => true, // 错误记录 默认是开启的
    'ERROR_LOG_PATH' => '__runtime/error_logs/', // 错误日志保存位置
                                                 
    // 缓存配置
    'CACHE' => array(
        'DEFAULT_CACHE' => 'memcache',
        'MEMORY_LIST' => array(),
        
        // 'ip:port'
        'FILE_PATH' => '__runtime/cache'
    ),
    'REDIS_HOST' => 'localhost',
    'REDIS_PORT' => 6379,
    'REDIS_TIMEOUT' => 3
);