<?php

class base extends BaseController
{

    public $layout = null;

    public function Action_index()
    {
        // 检测用户是否登录
        if (AdminController::isLogin())
            return CResponse::getInstance()->redirect(array(
                'c' => 'admin',
                'a' => 'index'
            ));
        if ($_POST) {
            // 获取参数
            $username = $this->Args('username', 'string');
            $password = $this->Args('password', 'string');
            
            // 检查登陆
            $userCheckStatus = CModel::factory('adminUserModel')->userCheck($username, $password);
            
            // 检查失败
            if (false == $userCheckStatus['status']) {
                // 登录失败
                $this->assign('userLoginStatus', $userCheckStatus);
            } else {
                // 允许登陆
                $userLoginStatus = CModel::factory('adminUserModel')->userLogin($userCheckStatus);
                if ($userLoginStatus['status'] == false) {
                    
                    $this->assign('userLoginStatus', $userLoginStatus);
                } else {
                    // 登录成功
                    CResponse::getInstance()->redirect($userLoginStatus['urlPram']);
                }
            }
        }
        $this->display();
    }

    /**
     * 退出
     */
    public function Action_Logout()
    {
        CSession::del('user');
        CResponse::getInstance()->redirect(array(
            'c' => 'base',
            'a' => 'index'
        ));
    }
}
