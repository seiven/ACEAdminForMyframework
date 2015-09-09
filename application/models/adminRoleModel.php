<?php

/**
 * 常见问题
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 ChenChao
 */
class adminRoleModel extends CActiveRecord
{

    /**
     * 设置表名
     */
    public function tableName()
    {
        return 'admin_group';
    }

    /**
     * 设置成树形结构
     */
    public function getCategoryTreeList()
    {
        $list = CDatabase::getInstance()->select()
            ->from($this->tableName())
            ->execute()
            ->asArray();
        
        // 重组数据
        $toTreeArr = array();
        foreach ($list as $val) {
            $val['id'] = $val['gid'];
            $val['name'] = $val['gname'];
            $val['hasNum'] = $this->getRoleHasNum($val['gid']);
            $toTreeArr[] = $val;
        }
        
        // 组装成树形
        $treeData = TreeClass::getTree($toTreeArr, 0, '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
        
        return $treeData;
    }

    /**
     * 获取每个角色组有的人数
     */
    public function getRoleHasNum($gid = 0)
    {
        $num = CDatabase::getInstance()->from('admin_user')
            ->select(array(
            'COUNT(`id`)' => 'num'
        ))
            ->where('groupId', '=', $gid)
            ->execute()
            ->current();
        return isset($num['num']) ? $num['num'] : 0;
    }

    /**
     * 过滤 可以查看管理的角色
     */
    public function filterUserRole($list)
    {
        $userData = CSession::get('user');
        
        $groupId = $userData['groupData']['gid'];
        
        // 超级管理员给全部资源
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
        
        // 获取其子类
        $childData = $childDataVal = array();
        TreeClass::getCatTree($groupId, $category, $childData, $childDataVal);
        
        foreach ($list as $key => $val) {
            if (! in_array($val['gid'], $childData) && $val['gid'] != $groupId) {
                unset($list[$key]);
            }
        }
        
        return $list;
    }
    public function getUserRole($id)
    {
        return CDatabase::getInstance()->select()->from($this->tableName())->where('gid','=',$id)->limit(1)->execute()->current();
    }
    /**
     * 可添加的角色组
     */
    public function filterCanAddRole($list)
    {
        $userData = CSession::get('user');
        
        $groupId = $userData['groupData']['gid'];
        
        // 超级管理员给全部资源
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
        
        // 获取其子类
        $childData = $childDataVal = array();
        TreeClass::getCatTree($groupId, $category, $childData, $childDataVal);
        
        foreach ($list as $key => $val) {
            if (! in_array($val['gid'], $childData)) {
                unset($list[$key]);
            }
        }
        
        return $list;
    }
}