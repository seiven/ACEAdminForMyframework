<?php

/**
 * CMyFrame SDK后台管理 需要校验权限
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 ChenChao
 */
class AdminMenu
{

    /**
     * 所有菜单
     */
    private static function _getAllMenu()
    {
        return array(
            
            '系统用户' => array(
                'name' => 'adminRole',
                'icon'=>'icon-desktop',
                'list' => array(
                    '角色管理' => array(
                        'c' => 'adminRole',
                        'a' => 'index',
                        'icon'=>''
                    ),
                    '用户管理' => array(
                        'c' => 'adminRole',
                        'a' => 'userList',
                        'icon'=>''
                    ),
                    '权限资源' => array(
                        'c' => 'adminRole',
                        'a' => 'rightList',
                        'icon'=>''
                    )
                )
            ), 
        );
    }

    /**
     * 注入公共菜单
     */
    private static function _addCommondMenu($menu)
    {}

    /**
     * 返回符合用户权限的菜单
     */
    public static function getUserMenu($userData)
    {
        
        // 超级管理员不校验权限
        if (isset($userData['groupId']) && 1 == $userData['groupId']) {
            return array(
                'menu' => self::_getAllMenu(),
                'allRight' => array()
            );
        }
        
        // 若不存在权限资源则丢弃
        if (! isset($userData['groupData']['rightList'])) {
            return array();
        }
        
        // 所有权限列表
        $rights = CModel::factory('adminRightsModel')->listKey();
        
        // 用户资源ID
        $userRightIDList = (isset($userData['groupData']['rightList'])) ? explode(',', $userData['groupData']['rightList']) : array();
        
        // 将用户的资源ID 换成资源
        $userRightList = array();
        foreach ($userRightIDList as $val) {
            if (isset($rights[$val])) {
                $rightString = $rights[$val]['content'];
                $rightArr = explode(',', $rightString);
                $userRightList = array_merge($rightArr, $userRightList);
            }
        }
        
        // 全部菜单
        $menuList = self::_getAllMenu();
        
        // 移除不被允许的菜单
        foreach ((array) $menuList as $firstKey => $firstMenu) {
            foreach ((array) $firstMenu['list'] as $secKey => $secMenu) {
                if (! isset($secMenu['c']) || ! isset($secMenu['a'])) {
                    unset($menuList[$firstKey]['list'][$secKey]);
                    continue;
                }
                $thisRightStr = $secMenu['c'] . '@' . $secMenu['a'];
                if (! in_array($thisRightStr, $userRightList)) {
                    unset($menuList[$firstKey]['list'][$secKey]);
                }
            }
        }
        
        // 去掉空选项
        foreach ((array) $menuList as $lvKey => $val) {
            if (empty($val['list'])) {
                unset($menuList[$lvKey]);
            }
        }
        
        // 得到合法菜单
        return array(
            'menu' => $menuList,
            'allRight' => $userRightList
        );
    }
}