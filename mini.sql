/*
 Navicat Premium Data Transfer

 Source Server         : DEV
 Source Server Type    : MySQL
 Source Server Version : 80011
 Source Host           : 192.168.33.10:3306
 Source Schema         : mall

 Target Server Type    : MySQL
 Target Server Version : 80011
 File Encoding         : 65001

 Date: 04/08/2020 20:42:14
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for oc_products
-- ----------------------------
DROP TABLE IF EXISTS `oc_products`;
CREATE TABLE `oc_products`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '名称',
  `brand_id` int(11) NOT NULL COMMENT '品牌ID',
  `brand` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '品牌',
  `brand_py` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '品牌拼音缩写',
  `brand_pinyin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '品牌拼音',
  `category_id` int(11) NOT NULL COMMENT '分类ID',
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '分类',
  `category_py` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '分类拼音缩写',
  `category_pinyin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '分类拼音',
  `status` tinyint(1) UNSIGNED NOT NULL COMMENT '状态 1 上架 0 下架',
  `price` decimal(10, 2) NOT NULL COMMENT '价格',
  `date_added` datetime(0) NOT NULL COMMENT '创建时间',
  `sales` smallint(5) DEFAULT NULL COMMENT '销量',
  `tags` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '标签',
  `sort_order` smallint(3) DEFAULT NULL COMMENT '排序',
  `es_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of oc_products
-- ----------------------------
INSERT INTO `oc_products` VALUES (1, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meibang', 1, '上衣', 'sy', 'shangyi', 1, 88.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子,夏季', 10, 'h4Z7uXMBlQr4oNUEmMqZ');
INSERT INTO `oc_products` VALUES (2, '美邦男版春季时尚上衣', 1, '美邦', 'mb', 'meibang', 1, '上衣', 'sy', 'shangyi', 1, 100.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子,春季', 9, 'iIZ7uXMBlQr4oNUEnMrf');
INSERT INTO `oc_products` VALUES (3, '美邦男版秋季时尚上衣', 1, '美邦', 'mb', 'meibang', 1, '上衣', 'sy', 'shangyi', 1, 99.00, '2020-08-04 18:23:58', 155, '时尚,花色,秋季', 8, 'iYZ7uXMBlQr4oNUEocoM');
INSERT INTO `oc_products` VALUES (4, '美邦男版冬季时尚上衣', 1, '美邦', 'mb', 'meibang', 1, '上衣', 'sy', 'shangyi', 1, 88.00, '2020-08-04 18:23:58', 155, '时尚,清爽,冬季', 10, 'ioZ7uXMBlQr4oNUEpco_');
INSERT INTO `oc_products` VALUES (5, '优衣库男版春季时尚裤子', 2, '优衣库', 'yyk', 'youyiku', 2, '裤子', 'kz', 'kuzi', 1, 88.00, '2020-08-04 18:23:58', 155, '时尚,清爽,轻奢', 10, 'jIZ7uXMBlQr4oNUEqcpt');
INSERT INTO `oc_products` VALUES (6, '优衣库男版夏季时尚裤子', 2, '优衣库', 'yyk', 'youyiku', 2, '裤子', 'kz', 'kuzi', 1, 77.00, '2020-08-04 18:23:58', 155, '时尚,清爽,轻奢', 10, 'jYZ7uXMBlQr4oNUErcqf');
INSERT INTO `oc_products` VALUES (7, '优衣库男版秋季时尚裤子', 2, '优衣库', 'yyk', 'youyiku', 2, '裤子', 'kz', 'kuzi', 1, 88.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 10, 'joZ7uXMBlQr4oNUEscrN');
INSERT INTO `oc_products` VALUES (8, '优衣库男版冬季时尚裤子', 2, '优衣库', 'yyk', 'youyiku', 2, '裤子', 'kz', 'kuzi', 1, 66.00, '2020-08-04 18:23:58', 155, '时尚,清爽', 108, 'j4Z7uXMBlQr4oNUEtcr9');
INSERT INTO `oc_products` VALUES (9, '以纯女版春季时尚裙子', 3, '以纯', 'yc', 'yichun', 3, '裙子', 'qz', 'qunzi', 1, 88.00, '2020-08-04 18:23:58', 155, '淡黄,花色,格子', 10, 'kIZ7uXMBlQr4oNUEusoz');
INSERT INTO `oc_products` VALUES (10, '以纯女版夏季时尚裙子', 3, '以纯', 'yc', 'yichun', 3, '裙子', 'qz', 'qunzi', 1, 260.00, '2020-08-04 18:23:58', 155, '时尚,花色,夏季', 10, 'kYZ7uXMBlQr4oNUEvspi');
INSERT INTO `oc_products` VALUES (11, '以纯女版秋季时尚裙子', 3, '以纯', 'yc', 'yichun', 3, '裙子', 'qz', 'qunzi', 1, 88.00, '2020-08-04 18:23:58', 155, '时尚,秋季,清纯', 7, 'koZ7uXMBlQr4oNUEwsqW');
INSERT INTO `oc_products` VALUES (12, '以纯女版冬季时尚裙子', 3, '以纯', 'yc', 'yichun', 3, '裙子', 'qz', 'qunzi', 1, 102.00, '2020-08-04 18:23:58', 155, '冬季,花色,保暖', 11, 'k4Z7uXMBlQr4oNUExsrI');
INSERT INTO `oc_products` VALUES (13, 'ZARA男版夏季时尚卫衣', 1, 'ZARA', '', '', 4, '卫衣', 'wy', 'weiyi', 1, 88.00, '2020-08-04 18:23:58', 155, '时尚,花色,卫衣', 10, 'lIZ7uXMBlQr4oNUEysr3');
INSERT INTO `oc_products` VALUES (14, 'ZARA男版夏季时尚卫衣', 1, 'ZARA', '', '', 4, '卫衣', 'wy', 'weiyi', 1, 88.00, '2020-08-04 18:23:58', 155, '时尚,简约,格子', 10, 'loZ7uXMBlQr4oNUEz8oq');
INSERT INTO `oc_products` VALUES (15, 'ZARA男版夏季时尚卫衣', 1, 'ZARA', '', '', 4, '卫衣', 'wy', 'weiyi', 1, 55.00, '2020-08-04 18:23:58', 155, '大气,花色,格子', 12, 'l4Z7uXMBlQr4oNUE08pc');
INSERT INTO `oc_products` VALUES (16, 'ZARA男版夏季时尚卫衣', 1, 'ZARA', '', '', 4, '卫衣', 'wy', 'weiyi', 1, 88.00, '2020-08-04 18:23:58', 155, '时尚,大码,格子', 10, 'mIZ7uXMBlQr4oNUE18qF');
INSERT INTO `oc_products` VALUES (17, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meiban', 1, '上衣', 'sy', 'shangyi', 1, 88.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 10, 'mYZ7uXMBlQr4oNUE28q4');
INSERT INTO `oc_products` VALUES (18, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meiban', 1, '上衣', 'sy', 'shangyi', 1, 88.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 13, 'moZ7uXMBlQr4oNUE38rn');
INSERT INTO `oc_products` VALUES (19, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meiban', 1, '上衣', 'sy', 'shangyi', 1, 77.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 10, 'm4Z7uXMBlQr4oNUE5Mob');
INSERT INTO `oc_products` VALUES (20, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meiban', 1, '上衣', 'sy', 'shangyi', 1, 88.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 114, 'nIZ7uXMBlQr4oNUE6MpH');
INSERT INTO `oc_products` VALUES (21, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meiban', 1, '上衣', 'sy', 'shangyi', 1, 88.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 10, 'nYZ7uXMBlQr4oNUE7Mp5');
INSERT INTO `oc_products` VALUES (22, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meiban', 1, '上衣', 'sy', 'shangyi', 1, 120.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 15, 'noZ7uXMBlQr4oNUE8Mqm');
INSERT INTO `oc_products` VALUES (23, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meiban', 1, '上衣', 'sy', 'shangyi', 1, 88.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 10, 'oIZ7uXMBlQr4oNUE9MrY');
INSERT INTO `oc_products` VALUES (24, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meiban', 1, '上衣', 'sy', 'shangyi', 1, 88.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 16, 'oYZ7uXMBlQr4oNUE-cpy');
INSERT INTO `oc_products` VALUES (25, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meiban', 1, '上衣', 'sy', 'shangyi', 1, 88111.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 10, 'ooZ7uXMBlQr4oNUE_cqo');
INSERT INTO `oc_products` VALUES (26, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meiban', 1, '上衣', 'sy', 'shangyi', 1, 525.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 10, 'o4Z8uXMBlQr4oNUEAcrV');
INSERT INTO `oc_products` VALUES (27, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meiban', 1, '上衣', 'sy', 'shangyi', 1, 88.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 1018, 'pIZ8uXMBlQr4oNUEBsoJ');
INSERT INTO `oc_products` VALUES (28, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meiban', 1, '上衣', 'sy', 'shangyi', 1, 55.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 10, 'pYZ8uXMBlQr4oNUECso5');
INSERT INTO `oc_products` VALUES (29, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meiban', 1, '上衣', 'sy', 'shangyi', 1, 88.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 10, 'poZ8uXMBlQr4oNUEDspu');
INSERT INTO `oc_products` VALUES (30, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meiban', 1, '上衣', 'sy', 'shangyi', 1, 656.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 2, 'p4Z8uXMBlQr4oNUEEsqa');
INSERT INTO `oc_products` VALUES (31, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meiban', 1, '上衣', 'sy', 'shangyi', 1, 88.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 10, 'qIZ8uXMBlQr4oNUEFsrL');
INSERT INTO `oc_products` VALUES (32, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meiban', 1, '上衣', 'sy', 'shangyi', 1, 88.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 10, 'qYZ8uXMBlQr4oNUEGsr7');
INSERT INTO `oc_products` VALUES (33, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meiban', 1, '上衣', 'sy', 'shangyi', 1, 111.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 103, 'q4Z8uXMBlQr4oNUEH8or');
INSERT INTO `oc_products` VALUES (34, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meiban', 1, '上衣', 'sy', 'shangyi', 1, 88.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 10, 'rIZ8uXMBlQr4oNUEI8pa');
INSERT INTO `oc_products` VALUES (35, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meiban', 1, '上衣', 'sy', 'shangyi', 1, 110.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 10, 'rYZ8uXMBlQr4oNUEJ8qJ');
INSERT INTO `oc_products` VALUES (36, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meiban', 1, '上衣', 'sy', 'shangyi', 1, 88.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 3, 'roZ8uXMBlQr4oNUEK8q4');
INSERT INTO `oc_products` VALUES (37, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meiban', 1, '上衣', 'sy', 'shangyi', 1, 88.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 10, 'r4Z8uXMBlQr4oNUEL8ry');
INSERT INTO `oc_products` VALUES (38, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meiban', 1, '上衣', 'sy', 'shangyi', 1, 88.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 10, 'sIZ8uXMBlQr4oNUENMok');
INSERT INTO `oc_products` VALUES (39, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meiban', 1, '上衣', 'sy', 'shangyi', 1, 88.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 10, 'sYZ8uXMBlQr4oNUEOMpR');
INSERT INTO `oc_products` VALUES (40, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meiban', 1, '上衣', 'sy', 'shangyi', 1, 88.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 10, 'soZ8uXMBlQr4oNUEPMqB');
INSERT INTO `oc_products` VALUES (41, '美邦男版夏季时尚上衣', 1, '美邦', 'mb', 'meiban', 1, '上衣', 'sy', 'shangyi', 1, 88.00, '2020-08-04 18:23:58', 155, '时尚,花色,格子', 10, 's4Z8uXMBlQr4oNUEQMqx');

SET FOREIGN_KEY_CHECKS = 1;
