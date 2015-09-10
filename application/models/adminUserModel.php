<?php

/**
 * 后台用户模型
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/
class adminUserModel extends CActiveRecord
{

    /**
     * 设置表名
     */
    public function tableName()
    {
        return 'admin_user';
    }

    /**
     * 通过用户名获取用户资料
     */
    public function getUserByUsername($username = null)
    {
        if (empty($username)) {
            return array();
        }
        $user = CDatabase::getInstance()->select()
            ->from($this->tableName())
            ->where('username', '=', $username)
            ->limit(1)
            ->execute()
            ->current();
        return $user;
    }
    public function getUserById($id){
    	if (empty($id)) {
    		return array();
    	}
    	$user = CDatabase::getInstance()->select()
            ->from($this->tableName())
    	->where('id', '=', $id)
    	->limit(1)
    	->execute()
    	->current();
    	return $user;
    }

    /**
     * 用户登陆
     */
    public function userCheck($username, $password)
    {
        $result = array(
            'status' => false,
            'message' => '发生错误,处理失败'
        );
        // 检查长度
        if (empty($username) || strlen($username) < 4 || strlen($username) > 20) {
            $result['message'] = '用户名长度错误';
        } elseif (empty($password)) {
            $result['message'] = '密码输入错误';
        }
        
        // 获取用户资料
        $userData = $this->getUserByUsername($username);
        // 用户不存在
        if (! $userData || ! isset($userData['id'])) {
            $result['message'] = '用户不存在';
        } else {
            // 检查密码
            $thisPassword = md5(md5($password) . $userData['salt']);
            if ($userData['status'] == 0) {
                // 检查是否禁用
                $result['message'] = '用户被禁用';
            } elseif ($thisPassword != $userData['password']) {
                $result['message'] = '密码错误';
            } else {
                // 判断IP是否允许
                // 验证通过
                $result['status'] = true;
                $result['message'] = '登录成功';
                $result['userData'] = $userData;
            }
        }
        return $result;
    }

    /**
     * 用户登陆
     */
    public function userLogin($checkData)
    {
        $result = array(
            'status' => false,
            'message' => '发生错误,处理失败'
        );
        // 检查
        if (! isset($checkData['status']) || false == $checkData['status'] || empty($checkData['userData'])) {
            $result['message'] = '登录失败';
            return $result;
        }
        
        // 用户资料
        $userData = $checkData['userData'];
        
        // 获取权限
        $groupList = CModel::factory('adminUserGroupModel')->getGroupList();
        
        // 不存在管理组
        if (! isset($groupList[$userData['groupId']])) {
            
            // 记录日志
            $result['message'] = '没有权限登录(group)';
            return $result;
        }
        
        // 填充组信息
        $userData['groupData'] = $groupList[$userData['groupId']];
        
        // 获取菜单
        $menuData = AdminMenu::getUserMenu($userData);
        
        // 保存菜单
        $userData['menu'] = $menuData['menu'];
        
        // 保存权限
        $userData['rightAll'] = $menuData['allRight'];
        
        $userData['isInternal'] = intval(IPArea::isInternalIP());
        
        // 获取导航
        // 登陆地
        if ($userData['isInternal'] == 1) {
            $userData['loginArea'] = '公司内部';
        } else {
            $userData['loginArea'] = IPArea::getArea(CRequest::getIp());
        }
        
        // 保存状态
        CSession::set('user', $userData);
        $result = array(
            'status' => true,
            'urlPram' => array(
                'c' => 'admin',
                'a' => 'index'
            )
        );
        // 返回数据
        return $result;
    }

    public function change($parameter_a)
    {
        $db = CDatabase::getDatabase();
        // $prepare_re=$db->prepare("update sk_admin_user set username = :username ,email = :email ,status=:status,groupId=:groupId,phone=:phone,truename=:truename where id=:id");
        if (isset($parameter_a['password']) && $parameter_a['password'] != "") {
            $prepare_re = $db->prepare("update sk_admin_user set username = ?,password = ?,email= ?,status= ?,groupId= ?,phone= ?,truename=? where id=?");
            $prepare_re->bindParam(1, $parameter_a['username'], PDO::PARAM_STR);
            $prepare_re->bindParam(2, md5($parameter_a['password']), PDO::PARAM_STR);
            $prepare_re->bindParam(3, $parameter_a['email'], PDO::PARAM_STR);
            $prepare_re->bindParam(4, $parameter_a['status'], PDO::PARAM_STR);
            $prepare_re->bindParam(5, $parameter_a['groupId'], PDO::PARAM_INT);
            $prepare_re->bindParam(6, $parameter_a['phone'], PDO::PARAM_STR);
            $prepare_re->bindParam(7, $parameter_a['truename'], PDO::PARAM_STR);
            $prepare_re->bindParam(8, $parameter_a['id'], PDO::PARAM_STR);
        } else {
            $prepare_re = $db->prepare("update sk_admin_user set username = :username ,email = :email ,status= :status ,groupId= :groupId,phone= :phone,truename= :truename where id= :id");
            $prepare_re->bindParam(":username", $parameter_a['username'], PDO::PARAM_STR);
            $prepare_re->bindParam(":email", $parameter_a['email'], PDO::PARAM_STR);
            $prepare_re->bindParam(":status", $parameter_a['status'], PDO::PARAM_STR);
            $prepare_re->bindParam(":groupId", $parameter_a['groupId'], PDO::PARAM_INT);
            $prepare_re->bindParam(":phone", $parameter_a['phone'], PDO::PARAM_STR);
            $prepare_re->bindParam(":truename", $parameter_a['truename'], PDO::PARAM_STR);
            $prepare_re->bindParam(":id", $parameter_a['id'], PDO::PARAM_STR);
        }
        $result = $prepare_re->execute();
        return $result;
    }
    /**
     * 过滤权限
     */
    public function filterUser($list)
    {
        $userData = CSession::get('user');
        $groupId = $userData['groupData']['gid'];
    
        if (1 == $groupId) {
            return $list;
        }
    
        $category = CDatabase::getInstance()->from('admin_group')
        ->select()
        ->execute()
        ->asArray();
    
        foreach ($category as $key => $val) {
            $category[$key]['name'] = $val['gname'];
            $category[$key]['id'] = $val['gid'];
        }
    
        $category = TreeClass::getTree($category);
    
        // 获取该组ID阔以查看的所有子组ID
        $childData = $childDataVal = array();
        TreeClass::getCatTree($groupId, $category, $childData, $childDataVal);
    
        // 可以查询的子组ID序列
        $groupList = array();
        foreach ($childDataVal as $key => $val) {
            $groupList[] = $val['gid'];
        }
    
        foreach ($list as $key => $val) {
    
            if (! in_array($val['groupId'], $groupList)) {
                unset($list[$key]);
            }
        }
    
        return $list;
    }
}