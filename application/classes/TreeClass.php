<?php

/**
 * UncleChen
 * 树形结构相关操作
 * @copyright UncleChen 2013
 * @author UncleChen
 * @version UncleChen v 0.0.1 2013/1/17
 */
class TreeClass
{

    /**
     * 得到树的层级
     *
     * @param
     *            $catArray
     */
    static public function getTree($catArray, $id = 0, $prefix = '')
    {
        static $formatCat = array();
        static $floor = 0;
        
        if (! empty($catArray)) {
            foreach ($catArray as $key => $val) {
                if ($val['parent_id'] == $id) {
                    $str = self::nstr($prefix, $floor);
                    $val['name'] = $str . $val['name'];
                    
                    $val['floor'] = $floor;
                    $formatCat[] = $val;
                    
                    unset($catArray[$key]);
                    
                    $floor ++;
                    self::getTree($catArray, $val['id'], $prefix);
                    $floor --;
                }
            }
            return $formatCat;
        } else {
            return false;
        }
    }

    /**
     * 处理缩进
     */
    static function nstr($str, $num = 0)
    {
        $return = '';
        for ($i = 0; $i < $num; $i ++) {
            $return .= $str;
        }
        return $return;
    }

    /**
     * 递归子类
     */
    static public function getCatTree($caList, $caAll, &$childList = array(), &$childArray = array())
    {
        $n = 0; // 跳出递归的计数器
        if (is_array($caList)) {
            foreach ($caAll as $key1 => $val1) {
                if (in_array($val1['parent_id'], $caList)) {
                    array_push($childList, $val1['id']);
                    array_push($childArray, $val1);
                    unset($caAll[$key1]);
                    $n ++;
                }
            }
            if ($n > 0) {
                self::getCatTree($childList, $caAll, $childList, $childArray);
            }
        } else {
            foreach ($caAll as $key1 => $val1) {
                if ($val1['parent_id'] == $caList) {
                    array_push($childList, $val1['id']);
                    array_push($childArray, $val1);
                    unset($caAll[$key1]);
                    $n ++;
                }
            }
            if ($n > 0) {
                self::getCatTree($childList, $caAll, $childList, $childArray);
            }
        }
    }

    /**
     * 生成树
     */
    static public function getTreeArray($ca)
    {
        if (! is_array($ca))
            return false;
        $newTreeTemp = array();
        
        $allFans = 0;
        
        foreach ($ca as $val) {
            $newTreeTemp[$val['floor']][$val['parent_id']][$val['id']] = $val;
            $allFans += isset($val['count']) ? $val['count'] : 0;
        }
        
        return array(
            'data' => $newTreeTemp,
            'count' => $allFans
        );
    }

    /**
     * 生成嵌套格式的树形数组
     * array(..."children"=>array(..."children"=>array(...)))
     */
    function getTreeHaveChild($OriginalList, $root = 0, $pk = "id", $parentKey = "parent_id", $childrenKey = "children")
    {
        // 最终数组
        $tree = array();
        // 存储主键与数组单元的引用关系
        $refer = array();
        // 遍历
        foreach ($OriginalList as $k => $v) {
            if (! isset($v[$pk]) || ! isset($v[$parentKey]) || isset($v[$childrenKey])) {
                unset($OriginalList[$k]);
                continue;
            }
            $refer[$v[$pk]] = &$OriginalList[$k]; // 为每个数组成员建立引用关系
        }
        // 遍历2
        foreach ($OriginalList as $k => $v) {
            if ($v[$parentKey] == $root) { // 根分类直接添加引用到tree中
                $tree[$v[$pk]] = &$OriginalList[$k];
            } else {
                if (isset($refer[$v[$parentKey]])) {
                    $parent = &$refer[$v[$parentKey]]; // 获取父分类的引用
                    $parent[$childrenKey][$v[$pk]] = &$OriginalList[$k]; // 在父分类的children中再添加一个引用成员
                }
            }
        }
        return $tree;
    }
}