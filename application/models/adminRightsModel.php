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

    public function pkName()
    {
        return 'id';
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
            ->getKey($this->pkName());
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
                if (! in_array($val[$this->pkName()], $selfRightArr)) {
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
            ->where($this->pkName(), '=', $id)
            ->limit(1)
            ->execute()
            ->current();
        return $data;
    }

    public function getList($setWhere)
    {
        $list = CDatabase::getInstance()->from($this->tableName())
            ->select();
        if (! isset($setWhere['_order']))
            $setWhere['_order'] = $this->pkName();
        if (! isset($setWhere['_sort']))
            $setWhere['_sort'] = 'DESC';
        if (! in_array(strtoupper($setWhere['_sort']), array(
            'ASC',
            'DESC'
        ))) {
            $setWhere['_sort'] = 'DESC';
        }
        $list->orderBy($setWhere['_order'], $setWhere['_sort']);
        // 筛选条件
        if (! intval($setWhere['page']))
            $setWhere['page'] = 1;
        $list = $this->setWhere($list, $setWhere);
        $limit = $this->getPageLimit($setWhere['page']);
        $list->limit($limit);
        $list = $list->execute()->asArray();
        return $list;
    }

    protected function setWhere(&$list, $where)
    {
        // if (! empty($where['name']))
        // $list->where('name', '=', $where['name']);
        return $list;
    }

    public function getCount($setWhere)
    {
        $list = CDatabase::getInstance()->from($this->tableName())
            ->select(array(
            "count(`{$this->pkName()}`)" => 'num'
        ));
        
        // 筛选条件
        $list = $this->setWhere($list, $setWhere);
        
        $list = $list->execute()->current();
        
        return isset($list['num']) ? $list['num'] : 0;
    }
}