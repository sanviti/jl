/*
Navicat MySQL Data Transfer

Source Server         : 本地
Source Server Version : 50532
Source Host           : localhost:3306
Source Database       : byjl

Target Server Type    : MYSQL
Target Server Version : 50532
File Encoding         : 65001

Date: 2018-08-08 18:23:00
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for jl_access
-- ----------------------------
DROP TABLE IF EXISTS `jl_access`;
CREATE TABLE `jl_access` (
  `role_id` smallint(6) unsigned NOT NULL,
  `node_id` smallint(6) unsigned NOT NULL,
  `level` tinyint(1) NOT NULL DEFAULT '0',
  `module` varchar(50) DEFAULT NULL,
  KEY `groupId` (`role_id`),
  KEY `nodeId` (`node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of jl_access
-- ----------------------------
INSERT INTO `jl_access` VALUES ('41', '1', '0', null);
INSERT INTO `jl_access` VALUES ('41', '2', '0', null);
INSERT INTO `jl_access` VALUES ('41', '3', '0', null);
INSERT INTO `jl_access` VALUES ('41', '0', '0', null);
INSERT INTO `jl_access` VALUES ('42', '2', '0', null);
INSERT INTO `jl_access` VALUES ('43', '2', '0', null);

-- ----------------------------
-- Table structure for jl_admin
-- ----------------------------
DROP TABLE IF EXISTS `jl_admin`;
CREATE TABLE `jl_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(10) DEFAULT NULL COMMENT '显示名称',
  `user` varchar(255) NOT NULL COMMENT '账号',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `userimg` varchar(64) DEFAULT NULL COMMENT '头像',
  `role` int(11) DEFAULT '0',
  `note` varchar(255) DEFAULT NULL,
  `add_time` varchar(255) NOT NULL,
  `update_time` varchar(255) NOT NULL,
  `state` tinyint(4) NOT NULL DEFAULT '1' COMMENT '0:停用 1 正常',
  `last_loginip` varchar(255) DEFAULT NULL,
  `last_logintime` varchar(255) DEFAULT NULL,
  `salt` char(6) NOT NULL COMMENT '盐',
  `login_token` char(32) NOT NULL COMMENT '登录令牌',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of jl_admin
-- ----------------------------
INSERT INTO `jl_admin` VALUES ('53', 'admin', 'admin', '045dca0a7701ce639e07c4c4c55ea335', '', '0', '', '1501236801', '1501236801', '1', '::1', '1524797184', 'BC1998', 'e215d40de29817fde2fee3949bbf2098');
INSERT INTO `jl_admin` VALUES ('73', 'weihua', 'weihua', '1859b5e00013d62eaf065ea211a2e5b5', '', '43', 'ww', '1523959334', '1524018382', '1', '127.0.0.1', '1524019503', '3C204A', '24a96904c9311d00e6992ba9ebd06520');

-- ----------------------------
-- Table structure for jl_admin_act_logs
-- ----------------------------
DROP TABLE IF EXISTS `jl_admin_act_logs`;
CREATE TABLE `jl_admin_act_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `logtype` tinyint(2) DEFAULT NULL COMMENT '操作类型 1登录 2后台操作',
  `subtype` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '操作子类型 login_fail login_success',
  `memo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '信息描述',
  `adminid` tinyint(4) DEFAULT NULL,
  `adminname` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '后台用户名',
  `ip` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ctime` int(10) DEFAULT NULL COMMENT '操作时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10569 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='后台操作日志';

-- ----------------------------
-- Records of jl_admin_act_logs
-- ----------------------------
INSERT INTO `jl_admin_act_logs` VALUES ('10531', '1', 'login_success', '登录成功', '53', 'admin', '127.0.0.1', '1523868596');
INSERT INTO `jl_admin_act_logs` VALUES ('10532', '1', 'login_success', '登录成功', '53', 'admin', '127.0.0.1', '1523931710');
INSERT INTO `jl_admin_act_logs` VALUES ('10533', '1', 'login_success', '登录成功', '53', 'admin', '127.0.0.1', '1523934420');
INSERT INTO `jl_admin_act_logs` VALUES ('10534', '1', 'login_fail', '密码输入错误：尝试密码 *123456*', null, 'weihua', '127.0.0.1', '1523947682');
INSERT INTO `jl_admin_act_logs` VALUES ('10535', '1', 'login_fail', '密码输入错误：尝试密码 *123456*', null, 'weihua', '127.0.0.1', '1523947688');
INSERT INTO `jl_admin_act_logs` VALUES ('10536', '1', 'login_fail', '密码输入错误：尝试密码 *123456*', null, 'weihua', '127.0.0.1', '1523947760');
INSERT INTO `jl_admin_act_logs` VALUES ('10537', '1', 'login_fail', '密码输入错误：尝试密码 *123456*', null, 'weihua', '127.0.0.1', '1523947938');
INSERT INTO `jl_admin_act_logs` VALUES ('10538', '1', 'login_success', '登录成功', '53', 'admin', '127.0.0.1', '1523948728');
INSERT INTO `jl_admin_act_logs` VALUES ('10539', '1', 'login_success', '登录成功', '53', 'admin', '127.0.0.1', '1523949525');
INSERT INTO `jl_admin_act_logs` VALUES ('10540', '1', 'login_success', '登录成功', '53', 'admin', '127.0.0.1', '1523951230');
INSERT INTO `jl_admin_act_logs` VALUES ('10541', '1', 'login_fail', '密码输入错误：尝试密码 *123456*', null, 'weihua', '127.0.0.1', '1523955213');
INSERT INTO `jl_admin_act_logs` VALUES ('10542', '1', 'login_fail', '密码输入错误：尝试密码 *123456*', null, 'weihua', '127.0.0.1', '1523955288');
INSERT INTO `jl_admin_act_logs` VALUES ('10543', '1', 'login_fail', '密码输入错误：尝试密码 *xiaoyu19911201.*', null, 'weihua', '127.0.0.1', '1523955400');
INSERT INTO `jl_admin_act_logs` VALUES ('10544', '1', 'login_success', '登录成功', '53', 'admin', '127.0.0.1', '1523956405');
INSERT INTO `jl_admin_act_logs` VALUES ('10545', '1', 'login_fail', '密码输入错误：尝试密码 *123456*', null, 'weihua', '127.0.0.1', '1523957524');
INSERT INTO `jl_admin_act_logs` VALUES ('10546', '1', 'login_success', '登录成功', '53', 'admin', '127.0.0.1', '1523957541');
INSERT INTO `jl_admin_act_logs` VALUES ('10547', '1', 'login_success', '登录成功', '53', 'admin', '127.0.0.1', '1523957849');
INSERT INTO `jl_admin_act_logs` VALUES ('10548', '1', 'login_success', '登录成功', '53', 'admin', '127.0.0.1', '1523958656');
INSERT INTO `jl_admin_act_logs` VALUES ('10549', '1', 'login_success', '登录成功', '72', 'ww', '127.0.0.1', '1523958751');
INSERT INTO `jl_admin_act_logs` VALUES ('10550', '1', 'login_success', '登录成功', '53', 'admin', '127.0.0.1', '1523958804');
INSERT INTO `jl_admin_act_logs` VALUES ('10551', '1', 'login_success', '登录成功', '53', 'admin', '127.0.0.1', '1523959232');
INSERT INTO `jl_admin_act_logs` VALUES ('10552', '1', 'login_success', '登录成功', '73', 'weihua', '127.0.0.1', '1523959348');
INSERT INTO `jl_admin_act_logs` VALUES ('10553', '1', 'login_success', '登录成功', '73', 'weihua', '127.0.0.1', '1523959524');
INSERT INTO `jl_admin_act_logs` VALUES ('10554', '1', 'login_success', '登录成功', '73', 'weihua', '127.0.0.1', '1523959865');
INSERT INTO `jl_admin_act_logs` VALUES ('10555', '1', 'login_fail', '密码输入错误：尝试密码 *123456*', null, 'weihua', '127.0.0.1', '1524015348');
INSERT INTO `jl_admin_act_logs` VALUES ('10556', '1', 'login_fail', '密码输入错误：尝试密码 *weihua*', null, 'weihua', '127.0.0.1', '1524015361');
INSERT INTO `jl_admin_act_logs` VALUES ('10557', '1', 'login_success', '登录成功', '53', 'admin', '127.0.0.1', '1524015380');
INSERT INTO `jl_admin_act_logs` VALUES ('10558', '1', 'login_success', '登录成功', '73', 'weihua', '127.0.0.1', '1524015516');
INSERT INTO `jl_admin_act_logs` VALUES ('10559', '1', 'login_success', '登录成功', '73', 'weihua', '127.0.0.1', '1524016818');
INSERT INTO `jl_admin_act_logs` VALUES ('10560', '1', 'login_success', '登录成功', '53', 'admin', '127.0.0.1', '1524019044');
INSERT INTO `jl_admin_act_logs` VALUES ('10561', '1', 'login_success', '登录成功', '73', 'weihua', '127.0.0.1', '1524019118');
INSERT INTO `jl_admin_act_logs` VALUES ('10562', '1', 'login_success', '登录成功', '73', 'weihua', '127.0.0.1', '1524019353');
INSERT INTO `jl_admin_act_logs` VALUES ('10563', '1', 'login_success', '登录成功', '53', 'admin', '127.0.0.1', '1524019459');
INSERT INTO `jl_admin_act_logs` VALUES ('10564', '1', 'login_success', '登录成功', '73', 'weihua', '127.0.0.1', '1524019503');
INSERT INTO `jl_admin_act_logs` VALUES ('10565', '1', 'login_success', '登录成功', '53', 'admin', '127.0.0.1', '1524019880');
INSERT INTO `jl_admin_act_logs` VALUES ('10566', '1', 'login_success', '登录成功', '53', 'admin', '127.0.0.1', '1524101048');
INSERT INTO `jl_admin_act_logs` VALUES ('10567', '1', 'login_success', '登录成功', '53', 'admin', '127.0.0.1', '1524108398');
INSERT INTO `jl_admin_act_logs` VALUES ('10568', '1', 'login_success', '登录成功', '53', 'admin', '::1', '1524797182');

-- ----------------------------
-- Table structure for jl_applycash
-- ----------------------------
DROP TABLE IF EXISTS `jl_applycash`;
CREATE TABLE `jl_applycash` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `money` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '提现金额',
  `account` decimal(10,4) DEFAULT NULL COMMENT '手续费',
  `disburse` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '支给金额',
  `role` tinyint(1) DEFAULT NULL COMMENT '用户身份 1普通 2社区',
  `cardid` varchar(200) DEFAULT NULL COMMENT '提现卡号',
  `cid` int(11) DEFAULT NULL COMMENT '支给卡号',
  `rname` varchar(200) DEFAULT NULL COMMENT '开户人',
  `bank` varchar(200) DEFAULT NULL COMMENT '银行名称',
  `subbranch` varchar(200) DEFAULT NULL COMMENT '开户支行',
  `ctime` int(11) DEFAULT NULL COMMENT '申请时间',
  `remark` varchar(120) DEFAULT NULL COMMENT '备注',
  `state` tinyint(1) DEFAULT '0' COMMENT '状态：0未处理 1已打款 -1已打款',
  `ptime` int(11) DEFAULT NULL COMMENT '处理时间',
  `mgrid` smallint(4) DEFAULT NULL COMMENT '处理人id',
  `pname` varchar(50) DEFAULT NULL COMMENT '处理人',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of jl_applycash
-- ----------------------------
INSERT INTO `jl_applycash` VALUES ('1', '1', '200.0000', null, '110.0000', '2', '621799777777777', '0', '高高', '招商银行', '西客站', '1524472255', '规划局', '-1', '1524565622', '53', '平台处理');
INSERT INTO `jl_applycash` VALUES ('2', '1', '100.0000', null, '100.0000', '1', '621799777777777', '1', '高高', '招商银行', '西客站', '1524472578', 'dfgfdg ', '1', '1524564346', '53', '平台处理');
INSERT INTO `jl_applycash` VALUES ('3', '18', '100.0000', null, '100.0000', '1', '621799777777777', '1', '高高', '招商银行', '西客站', '1524472578', 'dfgfdg ', '1', '1524564346', '53', '平台处理');
INSERT INTO `jl_applycash` VALUES ('4', '18', '100.0000', null, '100.0000', '1', '621799777777777', '1', '高高', '招商银行', '西客站', '1524472578', 'dfgfdg ', '1', '1524564346', '53', '平台处理');
INSERT INTO `jl_applycash` VALUES ('5', '18', '100.0000', null, '100.0000', '1', '621799777777777', '1', '高高', '招商银行', '西客站', '1524472578', 'dfgfdg ', '1', '1524564346', '53', '平台处理');

-- ----------------------------
-- Table structure for jl_bankcard
-- ----------------------------
DROP TABLE IF EXISTS `jl_bankcard`;
CREATE TABLE `jl_bankcard` (
  `cardid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned NOT NULL COMMENT '用户ID ',
  `truename` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '姓名',
  `bankname` varchar(120) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '银行名称',
  `card` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '卡号',
  `mobile` char(11) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '手机号',
  `dateline` int(10) unsigned DEFAULT NULL COMMENT '时间线',
  `def` tinyint(1) DEFAULT '0' COMMENT '默认 1是',
  `branchname` varchar(120) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '  ',
  PRIMARY KEY (`cardid`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of jl_bankcard
-- ----------------------------
INSERT INTO `jl_bankcard` VALUES ('3', '31980', '卫华', '招商银行', '621799777777777', null, '1524034957', '0', '西客站');
INSERT INTO `jl_bankcard` VALUES ('4', '31980', '卫华', '招商银行', '621799777777777', null, '1524034989', '0', '西客站');
INSERT INTO `jl_bankcard` VALUES ('5', '31980', '卫华', '招商银行', '621799777777777', null, '1524035075', '0', '西客站');
INSERT INTO `jl_bankcard` VALUES ('6', '31980', '卫华', '招商银行', '621799777777777', null, '1524035133', '0', '西客站');
INSERT INTO `jl_bankcard` VALUES ('7', '31980', '卫华', '招商银行', '621799777777777', null, '1524035142', '0', '西客站');
INSERT INTO `jl_bankcard` VALUES ('9', '31980', '卫华', '招商银行', '621799777777777', null, '1524035545', '1', '西客站');
INSERT INTO `jl_bankcard` VALUES ('10', '31980', '卫华卫华卫华', '招商银行', '621799777777777', null, '1524035712', '0', '西客站');

-- ----------------------------
-- Table structure for jl_members
-- ----------------------------
DROP TABLE IF EXISTS `jl_members`;
CREATE TABLE `jl_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '会员ID',
  `userid` varchar(256) DEFAULT NULL COMMENT '金链ID',
  `name` varchar(256) DEFAULT NULL COMMENT '姓名',
  `phone` varchar(50) DEFAULT NULL COMMENT '手机号',
  `auth_type` enum('0','1','2') DEFAULT '0' COMMENT '认证类型0未认证,1身份证,2护照',
  `idno` varchar(50) DEFAULT NULL COMMENT '身份证号',
  `idno_lock` enum('0','1') NOT NULL DEFAULT '0' COMMENT '是否可编辑 1不可以  0可以',
  `idno_front_img` varchar(256) DEFAULT NULL COMMENT '身份证正面照',
  `idno_back_img` varchar(256) DEFAULT NULL COMMENT '身份证反面照',
  `idno_hand_img` varchar(256) DEFAULT NULL COMMENT '手持身份证照',
  `password` varchar(256) DEFAULT NULL COMMENT '密码',
  `salt` varchar(50) DEFAULT NULL COMMENT '加密串',
  `paypwd` varchar(256) DEFAULT NULL COMMENT '交易密码',
  `paysalt` varchar(256) DEFAULT NULL COMMENT '支付加密串',
  `deposit` decimal(11,3) DEFAULT '0.000' COMMENT '充值',
  `buy_back` decimal(11,3) DEFAULT '0.000' COMMENT '回购',
  `balance` decimal(12,3) DEFAULT NULL COMMENT '会员账户余额',
  `balance_lock` decimal(12,3) DEFAULT NULL,
  `leadid` int(11) DEFAULT NULL COMMENT '推荐人ID',
  `teamid` int(11) NOT NULL DEFAULT '0' COMMENT '社区领导人id',
  `userlevel` enum('1','2') DEFAULT NULL COMMENT '等级,1,普通会员2,社区会员',
  `reg_time` int(11) DEFAULT NULL COMMENT '注册时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `headimg` varchar(256) NOT NULL COMMENT '头像',
  `invite_qrcode` varchar(256) NOT NULL COMMENT '邀请二维码',
  `is_lock` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1禁用 0启用',
  `isfreeze` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否冻结 1冻结  0正常',
  `token` varchar(256) NOT NULL COMMENT 'token',
  `remark` varchar(256) NOT NULL COMMENT '会员备注',
  `login_time` int(11) DEFAULT NULL COMMENT '最后登录IP',
  `login_ip` varchar(255) DEFAULT NULL COMMENT '最后登录IP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COMMENT='会员表';

-- ----------------------------
-- Records of jl_members
-- ----------------------------
INSERT INTO `jl_members` VALUES ('1', 'JL000001', '高高', '120', '0', null, '0', null, null, null, null, null, null, null, '0.000', '0.000', '200.000', null, '2', '0', '2', null, null, '11', '/Uploads/qrcode/2018-04-25/JL000001.png', '0', '0', 'hfkfjhffhkffaffjkahfu3whfhkgjgh', '1', null, null);
INSERT INTO `jl_members` VALUES ('2', 'JL000002', '低低', '110', '0', null, '0', null, null, null, null, null, null, null, '0.000', '0.000', '100.000', null, '1', '1', '1', null, null, '11', '/Uploads/qrcode/2018-04-20/1.png', '0', '0', '2bxgczfK774CgNAoDcN66SxX', '1', null, null);
INSERT INTO `jl_members` VALUES ('9', 'JL000009', '柴光谱', '18514410936', '0', '411023199206140010', '0', null, null, null, 'e855e1218fe871976214a8aac11345a3', 'BB7946E7', 'fdb3f6f20f667007c484f8743b779f3a', 'B19AA25F', '0.000', '0.000', null, null, '0', '0', null, '1524620163', null, '', '', '0', '0', 'KJhDU6SaL79H39ziWZGnOJO6', '', null, null);
INSERT INTO `jl_members` VALUES ('11', 'JL000011', '李欢欢', '13611273164', '0', '41062119911110151X', '0', null, null, null, '7c5ce445ae3bed50431c35e4c18ee168', '7612936D', 'd661e6839afc04f4fc948daec92dcff3', 'A0B20207', '0.000', '0.000', null, null, '0', '0', '2', '1524627931', null, '', '', '0', '0', 'M9gG7W18YmHbiSvI2XcJiRC3', '', null, null);
INSERT INTO `jl_members` VALUES ('15', 'JL000011', '李欢欢', '13611273164', '0', '41062119911110151X', '0', '', '', '', 'af484768e3fa90a81da8f68456790f30', '1D7C2AAE', 'd661e6839afc04f4fc948daec92dcff3', 'A0B20207', '0.000', '0.000', null, null, '0', '0', '2', '1524627931', null, '', '/Uploads/qrcode/2018-04-27/JL000011.png', '0', '0', 'M9gG7W18YmHbiSvI2XcJiRC3', '', null, '');
INSERT INTO `jl_members` VALUES ('18', 'JL000011', '熊', '15901259081', '0', '41062119911110151X', '0', '', '', '', 'd23bbd78a32c278d0f8d4d334618f686', '7612936D', 'd661e6839afc04f4fc948daec92dcff3', 'A0B20207', '1000.000', '2000.000', '3000.000', null, '0', '0', '2', '1524627931', null, '', '', '0', '0', '1234567890123467545', '', null, '');

-- ----------------------------
-- Table structure for jl_members_sign
-- ----------------------------
DROP TABLE IF EXISTS `jl_members_sign`;
CREATE TABLE `jl_members_sign` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `last_sign` varchar(255) NOT NULL COMMENT 'sign',
  `user_token` varchar(255) NOT NULL COMMENT 'token',
  `upd_time` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of jl_members_sign
-- ----------------------------

-- ----------------------------
-- Table structure for jl_notice
-- ----------------------------
DROP TABLE IF EXISTS `jl_notice`;
CREATE TABLE `jl_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `info` text,
  `add_time` varchar(15) DEFAULT NULL,
  `update_time` varchar(15) DEFAULT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否弹出 0不展示  1展示一次  2展示多次',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='公告通知';

-- ----------------------------
-- Records of jl_notice
-- ----------------------------
INSERT INTO `jl_notice` VALUES ('1', '因受春节过后市场观望态势较浓影响，总部做出以下宏观调整', '<p style=\"white-space: normal; text-align: center;\"><span style=\"font-size: 18px;\">通&nbsp; &nbsp;知</span><br/></p><p style=\"white-space: normal;\">尊敬的人人百壹家人：</p><p style=\"white-space: normal; text-indent: 2em;\"><span style=\"text-indent: 2em;\">大家好！因受春节过后市场观望态势较浓影响，总部对市场开发经验不足等一系列问题的影响下，市场整体营业额波动与平台目标激励营业额差距过大，导致激励周期拉长，为避免对市场造成更加恶劣的影响，充分发挥人人百壹模式的优越性，号召全民积极消费，总部做出以下宏观调整：</span></p><p style=\"text-indent: 0em; white-space: normal;\"><span style=\"text-indent: 2em;\">㈠银星、银豆部分</span></p><p style=\"text-indent: 0em; white-space: normal;\"><span style=\"text-indent: 2em;\"></span></p><p style=\"white-space: normal; text-indent: 2em;\">银星：所有功能暂停使用（总部不定期开通释放银星功能）；</p><p style=\"white-space: normal; text-indent: 2em;\">银豆：继续维持20%配比打让利款政策（商城近期将开放银豆+金豆兑换功能）且消费者暂停银豆配比回购功能；</p><p style=\"text-indent: 0em; white-space: normal;\"><span style=\"text-indent: 2em;\">㈡ 金星、金豆部分</span><br/></p><p style=\"white-space: normal; text-indent: 2em;\">⑴消费者金星值自公告之日起调整为0.2元，商家按原金豆配比银豆回购，星值为0.6元，保持不变；</p><p style=\"white-space: normal; text-indent: 2em;\">⑵消费者须在2月25日-3月20日期间累计消费账户所有消费额总和（金星数*500元）的25%；</p><p style=\"white-space: normal; text-indent: 2em;\">⑶2月25日-3月20日期间消费额满足第（2）条件后，该消费者（新老消费者）账户金星，星值立调整为0.8元（银豆暂停配比）；</p><p style=\"white-space: normal; text-indent: 2em;\">⑷如在2月25日-3月20日期间消费者未达到第（2）条件要求，该消费者账户所有金星，星值永久调整为0.02元；</p><p style=\"white-space: normal; text-indent: 2em;\">⑸3月20日后所有消费者需在人人百壹联盟商家，每十天进行一笔消费（商城或线下），金额不限；如未完成要求将暂缓所有金星激励，待完成后立刻恢复激励，目的在于增加消费者与联盟商家黏性；</p><p style=\"white-space: normal; text-indent: 2em;\">⑹人人百壹联盟商家目标要求调整为：如有一个周期未达到目标要求将暂停账户所有金星激励，待达到目标要求后立刻恢复激励；</p><p style=\"white-space: normal; text-indent: 2em;\"><span style=\"text-indent: 2em;\">此次人人百壹全体成员及各级管理中心的全力付出，必将带给您更美好、更精彩的明天。人人百壹的辉煌与全体成员的努力密不可分，再次感谢全国各级管理中心、联盟商家以及全体消费者的信任与支持，谢谢！</span><br/></p><p style=\"text-indent: 0em; white-space: normal; text-align: right;\"><span style=\"text-indent: 2em;\">人人百壹（北京）网络科技有限公司</span></p><p style=\"text-indent: 0em; white-space: normal; text-align: right;\"><span style=\"text-indent: 2em;\">二〇一八年三月四日</span></p><p><br/></p>', '1523962074', '1524020543', '1');
INSERT INTO `jl_notice` VALUES ('4', 'gdf g', '<p>fdg&nbsp;</p>', '1524021002', null, '0');

-- ----------------------------
-- Table structure for jl_role
-- ----------------------------
DROP TABLE IF EXISTS `jl_role`;
CREATE TABLE `jl_role` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `pid` smallint(6) DEFAULT NULL,
  `status` tinyint(1) unsigned DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of jl_role
-- ----------------------------
INSERT INTO `jl_role` VALUES ('41', '后台管理', '0', '1', '1');
INSERT INTO `jl_role` VALUES ('43', '管理', '0', '1', '管理');

-- ----------------------------
-- Table structure for jl_role_user
-- ----------------------------
DROP TABLE IF EXISTS `jl_role_user`;
CREATE TABLE `jl_role_user` (
  `role_id` mediumint(9) unsigned DEFAULT NULL,
  `user_id` char(32) DEFAULT NULL,
  KEY `group_id` (`role_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of jl_role_user
-- ----------------------------
INSERT INTO `jl_role_user` VALUES ('43', '73');
INSERT INTO `jl_role_user` VALUES ('0', '74');
INSERT INTO `jl_role_user` VALUES ('0', '71');
INSERT INTO `jl_role_user` VALUES ('0', '72');
INSERT INTO `jl_role_user` VALUES ('43', '73');

-- ----------------------------
-- Table structure for jl_sms_validcode
-- ----------------------------
DROP TABLE IF EXISTS `jl_sms_validcode`;
CREATE TABLE `jl_sms_validcode` (
  `codeid` int(11) NOT NULL AUTO_INCREMENT,
  `mobile` varchar(11) COLLATE utf8_unicode_ci NOT NULL COMMENT '手机号',
  `code` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '验证码',
  `expires_in` int(10) DEFAULT NULL COMMENT '过期时间',
  `range` tinyint(6) unsigned DEFAULT '0' COMMENT '验证范围',
  PRIMARY KEY (`codeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of jl_sms_validcode
-- ----------------------------

-- ----------------------------
-- Table structure for jl_trading_buy
-- ----------------------------
DROP TABLE IF EXISTS `jl_trading_buy`;
CREATE TABLE `jl_trading_buy` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `num` int(10) DEFAULT NULL,
  `price` decimal(10,4) DEFAULT '0.0000' COMMENT '单价',
  `ctime` int(10) DEFAULT NULL COMMENT '创建时间',
  `status` tinyint(1) DEFAULT '1' COMMENT '1交易中 2交易成功 3已撤单',
  `isclose` tinyint(1) DEFAULT '0' COMMENT '是否结算 0否 1是',
  `transno` varchar(40) DEFAULT '' COMMENT '交易流水号',
  `uid` int(10) DEFAULT NULL COMMENT '用户ID ',
  `ptime` int(10) unsigned DEFAULT NULL COMMENT '成交时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COMMENT='交易中心 买入';

-- ----------------------------
-- Records of jl_trading_buy
-- ----------------------------
INSERT INTO `jl_trading_buy` VALUES ('16', '1', '1.0000', '1513480394', '3', '1', 'BA7DC88F99D06000865AF42078A96BECB', '18', null);
INSERT INTO `jl_trading_buy` VALUES ('17', '2', '1.0000', '1513480476', '2', '1', 'B825266D2467D40907C033249484A9076', '18', '1513481401');
INSERT INTO `jl_trading_buy` VALUES ('18', '3', '1.0000', '1513482548', '3', '1', 'BA3E12ED17EB2C37287FD46EDAA8E3DCA', '18', null);
INSERT INTO `jl_trading_buy` VALUES ('19', '5', '1.0000', '1513482548', '3', '1', 'BA3E12ED17EB2C37287FD46EDAA8E3DCA', '19', null);

-- ----------------------------
-- Table structure for jl_trading_price
-- ----------------------------
DROP TABLE IF EXISTS `jl_trading_price`;
CREATE TABLE `jl_trading_price` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `price` decimal(18,8) DEFAULT '0.00000000',
  `ctime` int(11) DEFAULT NULL COMMENT '交易时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of jl_trading_price
-- ----------------------------
INSERT INTO `jl_trading_price` VALUES ('1', '1.00000000', '1524799349');
INSERT INTO `jl_trading_price` VALUES ('2', '2.00000000', '1524799349');
INSERT INTO `jl_trading_price` VALUES ('3', '2.00000000', '1524799670');
INSERT INTO `jl_trading_price` VALUES ('4', '1.00000000', '1524799728');
INSERT INTO `jl_trading_price` VALUES ('5', '1.00000000', '1524799748');
INSERT INTO `jl_trading_price` VALUES ('6', '11.00000000', '1524799750');
INSERT INTO `jl_trading_price` VALUES ('7', '1.00000000', '1524799755');
INSERT INTO `jl_trading_price` VALUES ('8', '1.00000000', '1524799759');
INSERT INTO `jl_trading_price` VALUES ('9', '2.00000000', '1524799765');
INSERT INTO `jl_trading_price` VALUES ('10', '1.00000000', '1524799769');
INSERT INTO `jl_trading_price` VALUES ('11', '1.00000000', '1524799774');
INSERT INTO `jl_trading_price` VALUES ('12', '2.00000000', '1524800649');

-- ----------------------------
-- Table structure for jl_trading_sell
-- ----------------------------
DROP TABLE IF EXISTS `jl_trading_sell`;
CREATE TABLE `jl_trading_sell` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `num` int(10) DEFAULT NULL,
  `price` decimal(10,4) DEFAULT '0.0000' COMMENT '单价',
  `ctime` int(10) DEFAULT NULL COMMENT '创建时间',
  `status` tinyint(1) DEFAULT '1' COMMENT '1交易中 2交易成功 3已撤单',
  `isclose` tinyint(1) DEFAULT '0' COMMENT '是否结算 0否 1是',
  `transno` varchar(40) DEFAULT '' COMMENT '交易流水号',
  `uid` int(10) DEFAULT NULL COMMENT '用户ID ',
  `ptime` int(10) unsigned DEFAULT NULL COMMENT '成交时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='交易中心 卖出';

-- ----------------------------
-- Records of jl_trading_sell
-- ----------------------------
INSERT INTO `jl_trading_sell` VALUES ('13', '13', '1.0000', '1513341695', '2', '1', 'BE111F524B10523412D9DB408DD2AB65A', '18', '1513341993');
INSERT INTO `jl_trading_sell` VALUES ('14', '14', '1.1100', '1511478842', '2', '1', 'B6CDCBDAE51B15E7023575E8F367F70C8', '18', '1513479001');

-- ----------------------------
-- Table structure for jl_trading_succ
-- ----------------------------
DROP TABLE IF EXISTS `jl_trading_succ`;
CREATE TABLE `jl_trading_succ` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `num` int(10) DEFAULT NULL,
  `price` decimal(10,4) unsigned DEFAULT '0.0000' COMMENT '单价',
  `ctime` int(10) DEFAULT NULL COMMENT '创建时间',
  `transno_sell` varchar(40) DEFAULT NULL COMMENT '卖出流水号',
  `transno_buy` varchar(40) DEFAULT '' COMMENT '买入流水号',
  `buy_uid` int(10) DEFAULT NULL COMMENT '用户ID ',
  `sell_uid` int(10) DEFAULT NULL,
  `fee` decimal(10,4) DEFAULT NULL COMMENT '手续费',
  `transno` varchar(40) DEFAULT NULL COMMENT '成交订单号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='交易中心 成交订单';

-- ----------------------------
-- Records of jl_trading_succ
-- ----------------------------

-- ----------------------------
-- Table structure for jl_treenode
-- ----------------------------
DROP TABLE IF EXISTS `jl_treenode`;
CREATE TABLE `jl_treenode` (
  `id` int(11) NOT NULL,
  `title` varchar(50) DEFAULT NULL COMMENT '名称',
  `g` varchar(50) DEFAULT NULL COMMENT '分组名称',
  `m` varchar(50) DEFAULT NULL COMMENT '模块名称',
  `a` varchar(50) DEFAULT NULL COMMENT 'action 名称',
  `ico` varchar(50) DEFAULT NULL COMMENT '图标',
  `pid` int(11) DEFAULT NULL COMMENT '0',
  `level` tinyint(4) DEFAULT '1' COMMENT '层级 1,2,3',
  `menuflag` tinyint(4) DEFAULT '1' COMMENT '0: 否 1 是',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  `single` tinyint(4) DEFAULT '1' COMMENT '是否单节点 0:否 1 是',
  `remark` varchar(255) DEFAULT NULL,
  `status` tinyint(4) DEFAULT '1' COMMENT '0 停用 1 启用',
  `is_hide` tinyint(1) unsigned DEFAULT '0' COMMENT '是否隐藏',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='后台菜单';

-- ----------------------------
-- Records of jl_treenode
-- ----------------------------
INSERT INTO `jl_treenode` VALUES ('0', '角色管理', 'admin', 'Auth', 'role', null, '3', '2', '1', '0', '1', null, '1', '0');
INSERT INTO `jl_treenode` VALUES ('1', '全选', 'admin', '', '', null, '0', '1', '0', '0', '1', null, '1', '0');
INSERT INTO `jl_treenode` VALUES ('2', '首页', 'admin', 'index', 'index', 'fa fa-home', '1', '2', '1', '999', '1', null, '1', '0');
INSERT INTO `jl_treenode` VALUES ('3', '系统管理', 'admin', 'auth', '', 'fa fa-gears', '1', '2', '1', '980', '0', null, '1', '0');
INSERT INTO `jl_treenode` VALUES ('4', '用户管理', 'admin', 'auth', 'userlist', 'fa fa-gears', '3', '2', '1', '0', '1', null, '1', '0');
INSERT INTO `jl_treenode` VALUES ('5', '价格管理', 'admin', 'notice', 'lists', '', '1', '2', '1', '0', '1', '', '1', '0');
INSERT INTO `jl_treenode` VALUES ('102', '添加角色', 'admin', 'auth', 'addrole', null, '3', '2', '0', '0', '1', null, '1', '0');
INSERT INTO `jl_treenode` VALUES ('103', '编辑角色', 'admin', 'auth', 'editrole', null, '3', '3', '0', '0', '1', null, '1', '0');
INSERT INTO `jl_treenode` VALUES ('106', '添加用户', 'admin', 'auth', 'adduser', null, '3', '3', '0', '0', '1', null, '1', '0');
INSERT INTO `jl_treenode` VALUES ('108', '删除用户', 'admin', 'auth', 'deluser', null, '3', '3', '0', '0', '1', null, '1', '0');
INSERT INTO `jl_treenode` VALUES ('109', '删除角色', 'admin', 'auth', 'delrole', null, '3', '3', '0', '0', '1', null, '1', '0');
INSERT INTO `jl_treenode` VALUES ('110', '资讯管理', 'admin', 'notice', 'lists', null, '1', '2', '1', '0', '1', null, '1', '0');
INSERT INTO `jl_treenode` VALUES ('111', '资讯列表', 'admin', 'notice', 'lists', null, '110', '2', '1', '0', '1', null, '1', '0');
INSERT INTO `jl_treenode` VALUES ('112', '添加资讯', 'admin', 'notice', 'add_notice', null, '110', '3', '0', '0', '1', null, '1', '0');
INSERT INTO `jl_treenode` VALUES ('113', '添加价格', 'admin', 'KeySet', 'addCoinPrice', '', '5', '3', '1', '0', '1', '', '1', '0');
INSERT INTO `jl_treenode` VALUES ('114', '价格走势图', 'admin', 'KeySet', 'showPrice', '', '5', '3', '1', '0', '1', '', '1', '0');

-- ----------------------------
-- Table structure for jl_user_log
-- ----------------------------
DROP TABLE IF EXISTS `jl_user_log`;
CREATE TABLE `jl_user_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '用户ID',
  `changeform` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '变动类型 in 收入 out 支出',
  `subtype` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '操作子类型 ',
  `money` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '余额变动',
  `ctime` int(10) NOT NULL,
  `describes` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '说明',
  `balance` float(10,2) DEFAULT '0.00' COMMENT '操作后余额',
  `extends` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '扩展字段',
  `money_type` tinyint(1) DEFAULT NULL COMMENT '类型 1金链 2余额',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `sub_money_ctime` (`subtype`,`money_type`,`ctime`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of jl_user_log
-- ----------------------------
INSERT INTO `jl_user_log` VALUES ('1', '1', 'out', '1', '100.00', '1524472255', null, '0.00', null, '2');
INSERT INTO `jl_user_log` VALUES ('2', '1', 'out', '1', '100.00', '1524472578', null, '0.00', null, '2');
INSERT INTO `jl_user_log` VALUES ('3', '1', 'in', '2', '200.00', '1524565621', '提现失败,已退回到您的账户,申请回购数量110.0000手续费', '200.00', '', null);
INSERT INTO `jl_user_log` VALUES ('4', '1', 'out', '3', '200.00', '1524565621', '提现失败,已退回到您的账户,申请回购数量110.0000手续费', '200.00', '', null);
INSERT INTO `jl_user_log` VALUES ('5', '1', 'in', '4', '200.00', '1524565621', '提现失败,已退回到您的账户,申请回购数量110.0000手续费', '200.00', '', null);
INSERT INTO `jl_user_log` VALUES ('15', '1', 'in', '2', '200.00', '1524565621', '提现失败,已退回到您的账户,申请回购数量110.0000手续费', '200.00', '', null);

-- ----------------------------
-- Table structure for jl_validcode
-- ----------------------------
DROP TABLE IF EXISTS `jl_validcode`;
CREATE TABLE `jl_validcode` (
  `codeid` int(11) NOT NULL AUTO_INCREMENT,
  `mobile` varchar(11) COLLATE utf8_unicode_ci NOT NULL COMMENT '手机号',
  `code` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '验证码',
  `expires_in` int(10) DEFAULT NULL COMMENT '过期时间',
  `range` tinyint(6) unsigned DEFAULT '0' COMMENT '验证范围',
  PRIMARY KEY (`codeid`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of jl_validcode
-- ----------------------------
INSERT INTO `jl_validcode` VALUES ('1', '15901259081', '7585', '1524209641', '1');
INSERT INTO `jl_validcode` VALUES ('2', '15901259081', '9233', '1524209892', '1');
INSERT INTO `jl_validcode` VALUES ('3', '15901259081', '2914', '1524564217', '2');
INSERT INTO `jl_validcode` VALUES ('4', '15901259081', '2658', '1524634487', '1');
INSERT INTO `jl_validcode` VALUES ('5', '15901259081', '3168', '1524634623', '1');
INSERT INTO `jl_validcode` VALUES ('6', '15901259081', '6042', '1524642976', '1');
INSERT INTO `jl_validcode` VALUES ('7', '13439466199', '5469', '1524643041', '1');
INSERT INTO `jl_validcode` VALUES ('8', '13439466199', '6279', '1524643449', '1');
INSERT INTO `jl_validcode` VALUES ('9', '13439466199', '7048', '1524824425', '1');

-- ----------------------------
-- Table structure for jl_validcode_ip
-- ----------------------------
DROP TABLE IF EXISTS `jl_validcode_ip`;
CREATE TABLE `jl_validcode_ip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(25) COLLATE utf8_unicode_ci NOT NULL COMMENT 'ip',
  `num` int(10) DEFAULT '0' COMMENT '基数',
  `ctime` int(10) DEFAULT NULL COMMENT '过期时间',
  `updatetime` int(10) unsigned DEFAULT NULL COMMENT '最后时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of jl_validcode_ip
-- ----------------------------

-- ----------------------------
-- Table structure for jl_wallet
-- ----------------------------
DROP TABLE IF EXISTS `jl_wallet`;
CREATE TABLE `jl_wallet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL COMMENT 'members.id',
  `wallet_address` varchar(256) NOT NULL COMMENT '钱包地址',
  `wallet_type` tinyint(1) DEFAULT '1' COMMENT '1：金链',
  `wallet_number` decimal(14,4) DEFAULT '0.0000' COMMENT '金链数量',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='钱包地址表';

-- ----------------------------
-- Records of jl_wallet
-- ----------------------------
INSERT INTO `jl_wallet` VALUES ('1', '2', 'jlbTMkjvnfuYI8ggwRYDneZ4Rb5LTnbNHGl26S50Z8', '1', '0.0000');
INSERT INTO `jl_wallet` VALUES ('2', '3', 'jle4K1uVFKU80QkQKSpIGOqpLakg7nM4RQ2CS3tQ4G', '1', '0.0000');
INSERT INTO `jl_wallet` VALUES ('4', '5', 'jlbfS7rGMj0kl0tOdG0qTt9QGr5wX2QmWXfSNYSBET', '1', '0.0000');
INSERT INTO `jl_wallet` VALUES ('5', '7', 'jl3oBUTUtxngO9xNhV2bf9E9Za9VNsZnafVn3sgBGF', '1', '0.0000');
INSERT INTO `jl_wallet` VALUES ('6', '9', 'jlWL5QRxnJGidh9sXuHXx5Ic7iHEAUpEFn9SDQ7lnC', '1', '0.0000');
INSERT INTO `jl_wallet` VALUES ('7', '10', 'jl0p9SR1hLUFz2kPyXzskOpxVzfuXACCFC3zgC1Zi5', '1', '0.0000');
INSERT INTO `jl_wallet` VALUES ('8', '11', 'jlKt30Dxoze4PlMeUZ1b7KVHu8AjHZcVXAHySIFxfr', '1', '0.0000');
INSERT INTO `jl_wallet` VALUES ('9', '12', 'jlG5BcG7iJIbhcHC3bANJKMomXuitrko1yfAKHfwZO', '1', '0.0000');
INSERT INTO `jl_wallet` VALUES ('10', '18', '1234567890123467545', '1', '30000.0000');
