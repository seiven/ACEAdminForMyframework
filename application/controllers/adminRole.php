<?php

/**
 * CMyFrame 角色管理
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 ChenChao
 */
class adminRole extends AdminController
{

    /**
     * 角色列表
     */
    public function Action_index()
    {
        $list = CModel::factory('adminRoleModel')->getCategoryTreeList();
        
        // 过滤起可控的管理角色
        $list = CModel::factory('adminRoleModel')->filterUserRole($list);
        $this->assign('list', $list);
        $staticUrl = CConfig::getInstance('site')->load('staticUrl');
        $this->assign('staticUrl', $staticUrl);
        $this->display();
    }
    /**
     * 修改自己的密码
     */
    public function Action_changePassword()
    {
    	$this->layout = null;
    	$user = CSession::get('user');
    	if ($_POST){
    		$addData['salt'] = rand(100000, 999999);
    		$addData['password'] = md5(md5($this->Args('password')) . $addData['salt']);
    		$status = CModel::factory('adminUserModel')->update($addData, array(
    				'id' => $user['id']
    		));
    		$errorMessage = '密码修改成,请重新登录!';
    		if (false == $status) {
    			$errorMessage = CDatabase::getDatabase()->errorInfo();
    			$errorMessage = isset($errorMessage[2]) ? $errorMessage[2] : '';
    		}
    		$this->assignAjax('redirect_url', $this->createUrl('logout', 'base'));
    		$this->displayAjax($status, $errorMessage);
    	}
    	$this->assign('user',$user);
    	$this->display();
    }

    /**
     * 添加角色
     */
    public function Action_addRole()
    {
        if ($_POST) {
            // 新增参数
            $addData['gname'] = $this->Args('roleName');
            $addData['rightList'] = implode(',', (array) $this->Args('rights', 'array', 'post', true));
            // 父分类
            $addData['parent_id'] = $this->Args('parent_id', 'int');
            if ($addData['parent_id'] == 0) {
                // 判断是否是超级管理员
                $userData = CSession::get('user');
                if ($userData['groupData']['gid'] != 1) {
                    $this->displayAjax(false, '您不是超级管理员账户,您无法创建顶级角色分组');
                }
            }
            
            try {
                $status = CModel::factory('adminRoleModel')->add($addData);
            } catch (CDbException $e) {
                $errorMessage = $e->getMessage();
            }
            
            if (false == $status) {
                $errorMessage = CDatabase::getDatabase()->errorInfo();
                $errorMessage = isset($errorMessage[2]) ? $errorMessage[2] : '';
            }
            $this->assignAjax('redirect_url', $this->createUrl('index', CRequest::getController()));
            $this->displayAjax(true, '添加成功');
        } else {
            $list = CModel::factory('adminRoleModel')->getCategoryTreeList();
            // 过滤起可控的管理角色
            $list = CModel::factory('adminRoleModel')->filterUserRole($list);
            $rightData = CModel::factory('adminRightsModel')->findAll()->asArray();
            $rightArray = array();
            $rightUndefined = array();
            foreach ($rightData as $key => $item) {
                preg_match('/\[.*?\]/', $item['name'], $localPre);
                if (isset($localPre[0])) {
                    $arrayKey = trim($localPre[0], '[]');
                    $rightArray[$arrayKey][] = $item;
                } else {
                    $rightUndefined[] = $item;
                }
            }
            
            // 按照权限过滤资源表
            $rightArray = CModel::factory('adminRightsModel')->filterRight($rightArray);
            
            $this->assign('list', $list);
            $this->assign('rightArray', $rightArray); // []中匹配正确的权限资源
            $this->assign('rightUndefined', $rightUndefined); // 未被定义的权限资源
            $this->display();
        }
    }

    /**
     * 编辑角色权限
     */
    public function Action_editRole()
    {
        $id = $this->Args('id', 'int');
        
        if ($_POST) {
            // 变动参数
            $addData['rightList'] = implode(',', (array) $this->Args('rights', 'array', 'post', true));
            
            // 产品 渠道
            $addData['parent_id'] = $this->Args('parent_id', 'int');
            if ($addData['parent_id'] == 0) {
                // 判断是否是超级管理员
                $userData = CSession::get('user');
                if ($userData['groupData']['gid'] != 1) {
                    $this->displayAjax(false, '您不是超级管理员账户,您无法创建顶级角色分组');
                }
            }
            
            $status = CModel::factory('adminRoleModel')->update($addData, array(
                'gid' => $id
            ));
            
            if (false == $status) {
                $errorMessage = CDatabase::getDatabase()->errorInfo();
                $errorMessage = isset($errorMessage[2]) ? $errorMessage[2] : '';
            }
            
            $this->assignAjax('redirect_url', $this->createUrl('index', CRequest::getController()));
            $this->displayAjax(true, '更新成功');
        }
        $list = CModel::factory('adminRoleModel')->getCategoryTreeList();
        // 过滤起可控的管理角色
        $list = CModel::factory('adminRoleModel')->filterUserRole($list);
        $rightData = CModel::factory('adminRightsModel')->getAllRights();
        // var_dump($rightData);
        $rightArray = array();
        $rightUndefined = array();
        foreach ($rightData as $key => $item) {
            preg_match('/\[.*?\]/', $item['name'], $localPre);
            if (isset($localPre[0])) {
                $arrayKey = trim($localPre[0], '[]');
                $rightArray[$arrayKey][] = $item;
            } else {
                $rightUndefined[] = $item;
            }
        }
        
        // 获取该角色信息
        $data = CModel::factory('adminRoleModel')->getUserRole($id);
        if ($data) {
            $data['rightList'] = explode(',', $data['rightList']);
        }
        // 按照权限过滤资源表
        $rightArray = CModel::factory('adminRightsModel')->filterRight($rightArray);
        $this->assign('list', $list);
        $this->assign('id', $id);
        $this->assign('data', $data);
        $this->assign('rightArray', $rightArray); // []中匹配正确的权限资源
        $this->assign('rightUndefined', $rightUndefined); // 未被定义的权限资源
        $this->display();
    }

    /**
     * 删除角色
     */
    public function Action_delRole()
    {
        $id = $this->Args('id', 'int');
        
        if ('1' == $id || $id == 1) {
            $this->displayAjax(false, '对不起,您不能删除该组');
        }
        
        // 判断组里是否还有成员
        $hasUser = CDatabase::getInstance()->from('admin_user')
            ->select()
            ->where('groupId', '=', $id)
            ->execute()
            ->current();
        if (! empty($hasUser)) {
            $this->displayAjax(false, '该组之下尚有管理员故无法删除');
        }
        
        $status = CModel::factory('adminRoleModel')->delete(array(
            'gid' => $id
        ));
        
        if (false == $status) {
            $errorMessage = CDatabase::getDatabase()->errorInfo();
            $errorMessage = isset($errorMessage[2]) ? $errorMessage[2] : '';
        }
        $this->displayAjax($status, $errorMessage);
    }

    /**
     * 用户管理
     */
    public function Action_userList()
    {
        $where['gid'] = $this->Args('gid', 'int');
        $where['username'] = $this->Args('username');
        
        // 用户
        $list = CDatabase::getInstance()->from('admin_user')
            ->select()
            ->limit(50000);
        
        if (! empty($where['gid']) && $where['gid'] != '-1') {
            $list = $list->where('groupId', '=', $where['gid']);
        }
        
        if (! empty($where['username'])) {
            $list = $list->where('username', 'LIKE', '%' . $where['username'] . '%');
        }
        
        $list = $list->execute()->asArray();
        
        // 过滤用户
        $list = CModel::factory('adminUserModel')->filterUser($list);
        
        // 组
        $group = CModel::factory('adminRoleModel')->findAll()->asArray();
        $groupKeyArr = array();
        foreach ($group as $val) {
            $groupKeyArr[$val['gid']] = $val;
        }
        
        $canGroup = CModel::factory('adminRoleModel')->findAll()->asArray();
        $canGroup = CModel::factory('adminRoleModel')->filterCanAddRole($group);
        
        $this->assign('where', $where);
        $this->assign('canGroup', $canGroup);
        $this->assign('list', $list);
        $this->assign('group', $groupKeyArr);
        $this->display();
    }

    /**
     * 创建管理员
     */
    public function Action_addUser()
    {
        if ($_POST) {
            $addData['username'] = $this->Args('username');
            $addData['salt'] = rand(100000, 999999);
            $addData['password'] = md5(md5($this->Args('password')) . $addData['salt']);
            $addData['email'] = $this->Args('email');
            $addData['phone'] = $this->Args('phone', 'int');
            $addData['createTime'] = time();
            $addData['status'] = $this->Args('status', 'int');
            $addData['groupId'] = $this->Args('groupId', 'int');
            $addData['truename'] = $this->Args('truename');
            
            if (empty($addData['groupId'])) {
                $this->displayAjax(false, '请填写用户所属角色');
            }
            
            try {
                $status = CModel::factory('adminUserModel')->add($addData);
                
                if (false == $status) {
                    $errorMessage = CDatabase::getDatabase()->errorInfo();
                    $errorMessage = isset($errorMessage[2]) ? $errorMessage[2] : '';
                }
            } catch (CDbException $e) {
                $errorMessage = $e->getMessage();
            }
            $this->displayAjax($status, $errorMessage);
        }
        $group = CModel::factory('adminRoleModel')->findAll()->asArray();
        $group = CModel::factory('adminRoleModel')->filterCanAddRole($group);
        
        $this->assign('group', $group);
        $this->layout = null;
        $this->display();
    }

    /**
     * 编辑管理员
     */
    public function Action_editUser()
    {
        $id = $this->Args('id', 'int');
        if ($_POST) {
            $addData['username'] = $this->Args('username');
            $addData['email'] = $this->Args('email');
            $addData['phone'] = $this->Args('phone', 'int');
            $addData['createTime'] = time();
            $addData['status'] = $this->Args('status', 'int');
            $addData['groupId'] = $this->Args('groupId', 'int');
            $addData['truename'] = $this->Args('truename');
            
            if (empty($addData['groupId'])) {
                $this->displayAjax(false, '请选择用户所属用户角色分组');
            }
            
            $password = $this->Args('password');
            if (! empty($password)) {
                $addData['salt'] = rand(100000, 999999);
                $addData['password'] = md5(md5($this->Args('password')) . $addData['salt']);
            }
            
            // 判断邮箱是否存在
            $emailExist = CModel::factory('adminUserModel')->find(' email = :email', array(
                ':email' => $addData['email']
            ))->current();
            if (! empty($emailExist) && $emailExist['id'] != $id) {
                $this->displayAjax(false, '该邮箱已被使用');
            }
            // 账户名不能修改
            unset($addData['username']);
            $status = CModel::factory('adminUserModel')->update($addData, array(
                'id' => $id
            ));
            
            if (false == $status) {
                $errorMessage = CDatabase::getDatabase()->errorInfo();
                $errorMessage = isset($errorMessage[2]) ? $errorMessage[2] : '';
            }
            $this->displayAjax($status, $errorMessage);
        }
        $data = CModel::factory('adminUserModel')->getUserById($id);
        $group = CModel::factory('adminRoleModel')->findAll()->asArray();
        
        $group = CModel::factory('adminRoleModel')->filterCanAddRole($group);
        $this->assign('id', $id);
        $this->assign('group', $group);
        $this->assign('data', $data);
        $this->layout = null;
        $this->display();
    }

    /**
     * 删除用户
     */
    public function Action_delUser()
    {
        $id = $this->Args('id', 'int');
        $user = CSession::get('user');
        if ($id == $user['id'])
            $this->displayAjax(false, '您不能删除自己啊~');
        $status = CModel::factory('adminUserModel')->delete(array(
            'id' => $id
        ));
        
        if (false == $status) {
            $errorMessage = CDatabase::getDatabase()->errorInfo();
            $errorMessage = isset($errorMessage[2]) ? $errorMessage[2] : '';
        }
        $this->displayAjax($status, $errorMessage);
    }

    /**
     * 资源管理
     */
    public function Action_rightList()
    {
        $where['page'] = $this->Args('page','int');
        $this->displayList('adminRightsModel', $where);
    }

    /**
     * 添加资源
     */
    public function Action_addRights()
    {
        if ($_POST) {
            // 权限名称
            $where['name'] = $this->Args('rightName');
            // 动作集合
            $actionList = $this->Args('actionList', 'array', 'post', true);
            $actionList = array_unique($actionList);
            
            // 转成字符串
            $where['content'] = implode(',', $actionList);
            $status = CModel::factory('adminRightsModel')->add($where);
            
            $errorMessage = '权限资源添加成功';
            if (false == $status) {
                $errorMessage = CDatabase::getDatabase()->errorInfo();
                $errorMessage = isset($errorMessage[2]) ? $errorMessage[2] : '';
            }
            $this->displayAjax($status, $errorMessage);
        }
        
        // 控制器列表
        $controllerList = $this->_getControllerName();
        
        $this->assign('controllerList', $controllerList);
        $this->layout = null;
        $this->display();
    }

    /**
     * 编辑资源
     */
    public function Action_editRights()
    {
        // ID
        $id = $this->Args('id', 'int');
        $this->layout = null;
        if ($_POST) {
            // 权限名称
            $data['name'] = $this->Args('rightName');
            // 动作集合
            $actionList = $this->Args('actionList', 'array', 'post', true);
            $actionList = array_unique($actionList);
            
            // 转成字符串
            $data['content'] = implode(',', $actionList);
            
            $status = CModel::factory('adminRightsModel')->update($data, array(
                'id' => $id
            ));
            $errorMessage = '权限资源编辑成功';
            if (false == $status) {
                $errorMessage = CDatabase::getDatabase()->errorInfo();
                $errorMessage = isset($errorMessage[2]) ? $errorMessage[2] : '';
            }
            $this->displayAjax($status, $errorMessage);
        }
        // 控制器列表
        $controllerList = $this->_getControllerName();
        $data = CModel::factory('adminRightsModel')->getRightByid($id);
        // 将权限转成数据
        $data['content'] = explode(',', $data['content']);
        $this->assign('data', $data);
        $this->assign('controllerList', $controllerList);
        $this->display();
    }

    /**
     * 删除资源
     */
    public function Action_delRights()
    {
        $id = $this->Args('id', 'int');
        $status = CModel::factory('adminRightsModel')->delete(array(
            'id' => $id
        ));
        
        if (false == $status) {
            $errorMessage = CDatabase::getDatabase()->errorInfo();
            $errorMessage = isset($errorMessage[2]) ? $errorMessage[2] : '';
        }
        $this->displayAjax($status, $errorMessage);
    }

    /**
     * 获取方法列表
     */
    public function Action_getActionList() {
    	$controllerName = $this->Args ( 'controller' );
    
    	$list = $this->_getActionList ( $controllerName );
    
    	echo json_encode ( array (
    			'status' => true,
    			'list' => $list
    	) );
    }
    /**
     * 获取控制器列表
     */
    private function _getControllerName()
    {
        $list = array();
        $controllerList = CFile::getPathFile(CODE_PATH . '/controllers');
        foreach ((array) $controllerList as $val) {
            
            // 分割名字
            $fileArr = explode('.', $val);
            if (! empty($fileArr[0])) {
                $list[] = $fileArr[0];
            }
        }
        return $list;
    }

    /**
     * 获取方法列表
     */
    private function _getActionList($controllerName)
    {
        
        // 控制器前缀
        $actionPre = CConfig::getInstance()->load('ACTION_PREFIX');
        
        // 获取该对象下的方法
        $action = array();
        $list = get_class_methods($controllerName);
        
        if (empty($actionPre)) {
            return $list;
        }
        
        foreach ((array) $list as $val) {
            if (false !== strpos($val, $actionPre)) {
                $action[] = str_replace($actionPre, '', $val);
            }
        }
        
        return $action;
    }
}