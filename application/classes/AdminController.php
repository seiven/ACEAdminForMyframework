<?php

class AdminController extends BaseController
{

    public $isAjax = 0;

    /**
     * 构造函数
     */
    public function __construct()
    {
        if ($this->Args('isajax', 'int') == 1)
            $this->isAjax = 1;
        if (! self::isLogin()) {
            if ($this->isAjax)
                $this->displayAjax(false, '您还没有登陆,请先登录');
            return CResponse::getInstance()->redirect(array(
                'c' => 'base',
                'a' => 'index'
            ));
        }
        $status = self::checkRight();
        
        // 用户资源
        if ($this->layout == 'layout_main') {
            $userData = CSession::get('user');
            $this->assign('userdata', $userData);
        }
        // 检查权限
        if (false == $status) {
            
            // 判断请求方式
            if ($this->isAjax)
                $this->displayAjax(false, '您没有权限执行此操作!');
                
                // 分析错误信息
            $data['from'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            // ip归属地
            $data['ip'] = CRequest::getIp();
            $data['ipArea'] = IPArea::getArea(CRequest::getIp());
            $data['agent'] = CRequest::getAgent();
            
            $this->assign('data', $data);
            $this->display('alert/noright');
            exit();
        }
    }

    public static function isLogin()
    {
        // 检查登录
        $userData = CSession::get('user');
        if ($userData && isset($userData['username']) && ! empty($userData['groupId']))
            return true;
        return false;
    }

    /**
     * 是否有权限
     */
    public static function checkRight($thisRoute = null)
    {
        
        // 用户资源
        $userData = CSession::get('user');
        
        if ($userData['groupId'] == 1) {
            return true;
        }
        
        $userRightList = isset($userData['rightAll']) ? $userData['rightAll'] : array();
        
        // 附加公共资源
        array_push($userRightList, 'system@welcome');
        array_push($userRightList, 'system@navList');
        array_push($userRightList, 'system@addNav');
        array_push($userRightList, 'system@addNavHandle');
        array_push($userRightList, 'system@ajaxAddNav');
        array_push($userRightList, 'system@editNav');
        array_push($userRightList, 'system@editNavHandle');
        array_push($userRightList, 'system@delNav');
        array_push($userRightList, 'system@changeMyPassword');
        array_push($userRightList, 'system@changeMyPassHandle');
        array_push($userRightList, 'adminRole@messageCenterForClient');
        array_push($userRightList, 'system@seeHelp');
        
        // 转小写
        foreach ($userRightList as $key => $val) {
            $userRightList[$key] = strtolower($val);
        }
        
        // 获取当前请求的路由
        if ($thisRoute == null) {
            $route = CRequest::getController() . '@' . str_replace(CConfig::getInstance()->load('ACTION_PREFIX'), '', CRequest::getAction());
        } else {
            $route = $thisRoute;
        }
        
        return in_array(strtolower($route), $userRightList);
    }
}