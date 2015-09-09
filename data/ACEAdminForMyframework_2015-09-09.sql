-- --------------------------------------------------------
-- 主机:                           127.0.0.1
-- 服务器版本:                        5.1.41-log - Source distribution
-- 服务器操作系统:                      Win32
-- HeidiSQL 版本:                  8.2.0.4675
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- 导出  表 weixin_learn.wl_admin_group 结构
CREATE TABLE IF NOT EXISTS `wl_admin_group` (
  `gid` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `gname` varchar(30) DEFAULT NULL COMMENT '用户组名称',
  `rightList` text COMMENT '权限列表',
  `product_list` text COMMENT '产品权限列表',
  PRIMARY KEY (`gid`)
) ENGINE=MyISAM AUTO_INCREMENT=44 DEFAULT CHARSET=utf8 COMMENT='管理员分组';

-- 正在导出表  weixin_learn.wl_admin_group 的数据：2 rows
/*!40000 ALTER TABLE `wl_admin_group` DISABLE KEYS */;
REPLACE INTO `wl_admin_group` (`gid`, `parent_id`, `gname`, `rightList`, `product_list`) VALUES
	(1, 0, '超级管理员', NULL, NULL),
	(43, 0, 'dadsadas', '1,3,5,7,9,11,13,15,17,19,21,23,25,27,29,31,33', NULL);
/*!40000 ALTER TABLE `wl_admin_group` ENABLE KEYS */;


-- 导出  表 weixin_learn.wl_admin_rights 结构
CREATE TABLE IF NOT EXISTS `wl_admin_rights` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) DEFAULT NULL COMMENT '权限名称',
  `content` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=66 DEFAULT CHARSET=utf8 COMMENT='权限资源';

-- 正在导出表  weixin_learn.wl_admin_rights 的数据：33 rows
/*!40000 ALTER TABLE `wl_admin_rights` DISABLE KEYS */;
REPLACE INTO `wl_admin_rights` (`id`, `name`, `content`) VALUES
	(1, '[游戏]查看分组列表d1', 'game@groupList,game@ajaxGetGameByGid,game@ajaxGetChannelByGameId'),
	(3, '[游戏]变更游戏分组', 'game@addGroup,game@addGroupHandle,game@editGroup,game@editGroupHandle'),
	(5, '[游戏]查看游戏列表', 'game@gameList,game@ajaxGetGameByGid,game@ajaxGetChannelByGameId'),
	(7, '[游戏]变更游戏列表', 'game@addGame,game@addGameHandle,game@editGame,game@editGameHandle'),
	(9, '[游戏]查看渠道列表', 'game@channelList,game@ajaxGetGameByGid,game@ajaxGetChannelByGameId'),
	(11, '[游戏]设置游戏渠道', 'game@setProductConfigs,game@setProductConfigsHandle,game@addChannel,game@addGameChannelHandle,game@getChannelKey'),
	(13, '[基础数据]查看用户列表', 'baseData@playerList'),
	(15, '[基础数据]查看用户详情', 'baseData@playDetail'),
	(17, '[基础数据]查看用户角色', 'baseData@roleList'),
	(19, '[基础数据]查看角色详情', 'baseData@roleDetail'),
	(21, '[基础数据]查看登录日志', 'baseData@loginlogList'),
	(23, '[基础数据]登录日志详情', 'baseData@logindetail'),
	(25, '[基础数据]查看订单列表', 'baseData@orderList,baseData@getOrderTongji'),
	(27, '[基础数据]手动同步订单', 'baseData@orderAsy'),
	(29, '[基础数据]强制同步订单', 'baseData@sendNotice'),
	(31, '[基础报表]查看汇总报表', 'report@allReportData'),
	(33, '[基础报表]查看留存报表', 'report@userLive'),
	(35, '[终端属性]查看设备列表', 'device@list'),
	(37, '[终端属性]查看设备统计', 'device@deviceList'),
	(39, '[终端属性]查看机型分布', 'device@deviceType'),
	(43, '[数据分析]查看访问量', 'dataAdmin@pageView'),
	(45, '[日志]通知失败记录', 'log@callGameError'),
	(47, '[系统用户]查看角色列表', 'adminRole@roleList'),
	(49, '[系统用户]变更管理角色', 'adminRole@addRole,adminRole@addRoleHandle,adminRole@editRole,adminRole@editRoleHandle'),
	(51, '[系统用户]删除管理角色', 'adminRole@delRole'),
	(53, '[系统用户]查看用户列表', 'adminRole@userList'),
	(55, '[系统用户]变更管理用户', 'adminRole@addUser,adminRole@addUserHandle,adminRole@editUser,adminRole@editUserHandle'),
	(57, '[系统用户]删除系统用户', 'adminRole@delUser'),
	(61, '[系统用户]变更权限资源', 'adminRole@addRights,adminRole@addRightsHandle,adminRole@editRights,adminRole@editRightsHandle,adminRole@getActionList'),
	(63, '[系统用户]删除权限资源', 'adminRole@delRights'),
	(64, '[社交行为分析]通话记录', 'analysis@callList');
/*!40000 ALTER TABLE `wl_admin_rights` ENABLE KEYS */;


-- 导出  表 weixin_learn.wl_admin_user 结构
CREATE TABLE IF NOT EXISTS `wl_admin_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(60) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(32) NOT NULL DEFAULT '' COMMENT '密码 ',
  `salt` char(6) NOT NULL DEFAULT '' COMMENT '密码随机值',
  `email` varchar(255) DEFAULT NULL COMMENT '邮件地址',
  `createTime` bigint(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `lastTime` bigint(11) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `lastIp` varchar(80) DEFAULT NULL COMMENT '最后登录IP',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否禁用   0禁用  1正常',
  `groupId` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '分组编号',
  `phone` bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT '电话',
  `saveLog` text,
  `truename` varchar(10) DEFAULT NULL COMMENT '真实姓名',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像图标',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `email` (`email`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=630 DEFAULT CHARSET=utf8 COMMENT='管理员账户';

-- 正在导出表  weixin_learn.wl_admin_user 的数据：1 rows
/*!40000 ALTER TABLE `wl_admin_user` DISABLE KEYS */;
REPLACE INTO `wl_admin_user` (`id`, `username`, `password`, `salt`, `email`, `createTime`, `lastTime`, `lastIp`, `status`, `groupId`, `phone`, `saveLog`, `truename`, `avatar`) VALUES
	(628, 'seiven', '6b9813b247814e2d5c43b5f3c5aac633', '293185', 'itvvc@qq.com', 1441466156, 0, NULL, 1, 1, 0, NULL, '海均', NULL),
	(629, 'dasdsada', 'b172468b390ab8f9db00e07ff3d4a1cf', '533740', 'dasd', 1441776179, 0, NULL, 1, 1, 11111, NULL, 'seiven', NULL);
/*!40000 ALTER TABLE `wl_admin_user` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
