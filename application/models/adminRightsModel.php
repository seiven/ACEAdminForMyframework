<?php

/**
 * 常见问题
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 ChenChao
 */
class adminRightsModel extends CActiveRecord
{

    /**
     * 设置表名
     */
    public function tableName()
    {
        return 'admin_rights';
    }

    /**
     * 得到所有权限列表
     */
    public function listKey()
    {
        return CDatabase::getInstance()->select()
            ->from($this->tableName())
            ->
        // ->cache('rightsList',86400)
        execute()
            ->getKey('id');
    }

    /**
     * 过滤资源
     */
    public function filterRight($right)
    {
        $userData = CSession::get('user');
        $groupId = $userData['groupData']['gid'];
        
        if ($groupId == 1) {
            return $right;
        }
        
        // 自身权限
        $selfRight = $userData['groupData']['rightList'];
        $selfRightArr = explode(',', $selfRight);
        
        foreach ($right as $name => $list) {
            foreach ($list as $key => $val) {
                if (! in_array($val['id'], $selfRightArr)) {
                    unset($right[$name][$key]);
                }
            }
        }
        return $right;
    }
    public function getAllRights()
    {
        return $this->findAll()->asArray();
    }
    
    public function getRightByid($id)
    {
        if (empty($id)) {
            return array();
        }
        $data = CDatabase::getInstance()->select()
        ->from($this->tableName())
        ->where('id', '=', $id)
        ->limit(1)
        ->execute()
        ->current();
        return $data;
    }
}