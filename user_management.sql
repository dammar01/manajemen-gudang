/*
 Navicat Premium Dump SQL

 Source Server         : db
 Source Server Type    : MySQL
 Source Server Version : 110702 (11.7.2-MariaDB)
 Source Host           : localhost:3306
 Source Schema         : user_management

 Target Server Type    : MySQL
 Target Server Version : 110702 (11.7.2-MariaDB)
 File Encoding         : 65001

 Date: 31/10/2025 20:54:46
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for password_resets
-- ----------------------------
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL,
  `expires` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_uca1400_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of password_resets
-- ----------------------------

-- ----------------------------
-- Table structure for products
-- ----------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NULL,
  `price` decimal(10, 2) NULL DEFAULT NULL,
  `quantity` int NULL DEFAULT NULL,
  `created_by` int NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `created_by`(`created_by` ASC) USING BTREE,
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_uca1400_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of products
-- ----------------------------

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL,
  `is_active` tinyint NULL DEFAULT 0,
  `activation_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `email`(`email` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_uca1400_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (3, 'dammar.s011@gmail.com', '$2y$12$NfnYW8wmIMtSVV/gxo7esuPwOoX8cIC/be2i6a5cEYnvgmdydfSS2', 0, 'ae1e629bd54d940f713809a9571ef13bfe1a729ef9369c3ed7dd87d32d130f0c', '2025-10-31 20:13:47');
INSERT INTO `users` VALUES (4, 'tes@gmail.com', '$2y$12$ophDozoQ1jO.d3XZCC/Ti.jpQLCHDpEwtYsuBvgvu2xpCdDkvXR6O', 1, '', '2025-10-31 20:14:33');
INSERT INTO `users` VALUES (5, 'hendra@gmail.com', '$2y$12$eCprieeR/AwHCyntyJ3iv.prSU5dkfzwkbmqiLm2vwuEkgXSsLIdC', 0, '5b155fdea08f370ae81295412fdf83c7cb90f87c77c190f411aa1500d2c63e09', '2025-10-31 20:15:32');
INSERT INTO `users` VALUES (6, 'test@example.com', '$2y$12$UMkNiAGR8LYXwX.mB4RCZ.KsxinR7L3UnwrLX.du9Dhl2wxkL8jiO', 0, '35141e7fbadb9821484fb53149a00f20e20dcb313e9d61e0fb20322513e660f9', '2025-10-31 20:17:17');
INSERT INTO `users` VALUES (7, 'asda@gmail.com', '$2y$12$XEXglNJQNrnIY3GHpppU1unpDh9l36MFim9HkKtXsiBvamRdLC34m', 1, '', '2025-10-31 20:46:59');

SET FOREIGN_KEY_CHECKS = 1;
