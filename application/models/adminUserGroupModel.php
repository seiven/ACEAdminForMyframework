<?php

/**
 * 用户组模型
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 ChenChao
 */
class adminUserGroupModel extends CActiveRecord
{

    /**
     * 设置表名
     */
    public function tableName()
    {
        return 'admin_group';
    }

    /**
     * 获取所有用户名 以gid排序
     */
    public function getGroupList()
    {
        return CDatabase::getInstance()->from($this->tableName())
            ->select()
            ->execute()
            ->getKey('gid');
    }

    public function change($parameter_a)
    {
        $product_list = $parameter_a['product_list'] ? $parameter_a['product_list'] : "a:0:{}";
        /*
         * $db = CDatabase::getInstance();
         * $db->query("update sk_admin_group set gname='{$parameter_a['roleName']}',rightList='{$parameter_a['rights']}',product_list='{$product_list}',parent_id='{$parameter_a['parent_id']}' where gid='{$parameter_a['id']}'");
         */
        try {
            $pdo = CDatabase::getDatabase();
            $pdo_runObject = $pdo->prepare("update sk_admin_group set gname=?,rightList=?,product_list=?,parent_id=? where gid=?");
            $pdo_runObject->bindParam(1, $parameter_a['roleName'], PDO::PARAM_STR);
            $pdo_runObject->bindParam(2, $parameter_a['rights'], PDO::PARAM_STR);
            $pdo_runObject->bindParam(3, $product_list, PDO::PARAM_STR);
            $pdo_runObject->bindParam(4, $parameter_a['parent_id'], PDO::PARAM_STR);
            $pdo_runObject->bindParam(5, $parameter_a['id'], PDO::PARAM_INT);
            $result_status = $pdo_runObject->execute();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        return $result_status;
    }
}