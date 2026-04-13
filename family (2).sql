-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 12, 2026 at 04:58 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `family`
--

-- --------------------------------------------------------

--
-- Table structure for table `employer`
--

CREATE TABLE `employer` (
  `employerid` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phonenumber` varchar(255) NOT NULL,
  `roleid` int(11) NOT NULL,
  `userid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employer`
--

INSERT INTO `employer` (`employerid`, `name`, `email`, `phonenumber`, `roleid`, `userid`) VALUES
(1, 'Superadmin', 'superadmin@gmail.com', '0987654321', 1, 1),
(2, 'admin', 'admin@gmail.com', '13231', 2, 2),
(3, 'Yenatrice Sn', 'yenatricesn@gmail.com', '082170976500', 2, 6);

-- --------------------------------------------------------

--
-- Table structure for table `family`
--

CREATE TABLE `family` (
  `familyid` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `family`
--

INSERT INTO `family` (`familyid`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Hehe Family', 'Everything is about hahahehehuhuhihihoho', '2026-04-06 15:49:56', '2026-04-06 15:49:56');

-- --------------------------------------------------------

--
-- Table structure for table `family_member`
--

CREATE TABLE `family_member` (
  `memberid` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phonenumber` varchar(255) NOT NULL,
  `gender` varchar(255) NOT NULL,
  `birthdate` date NOT NULL,
  `birthplace` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `job` varchar(255) DEFAULT NULL,
  `education_status` varchar(255) DEFAULT NULL,
  `life_status` varchar(255) NOT NULL,
  `marital_status` varchar(255) NOT NULL,
  `deaddate` date DEFAULT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `userid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `family_member`
--

INSERT INTO `family_member` (`memberid`, `name`, `email`, `phonenumber`, `gender`, `birthdate`, `birthplace`, `address`, `job`, `education_status`, `life_status`, `marital_status`, `deaddate`, `picture`, `userid`) VALUES
(1, 'Hahahaha', 'hahahaha@gmail.com', '1234567890', 'male', '1950-04-11', 'Australia', 'HHHHHH', 'Software Engineer', 'Vocational Highschool', 'alive', 'single', NULL, '/images/avatar-male.svg', 3),
(2, 'Hehehehe', 'hehehehe@gmail.com', '098765432', 'female', '1950-05-31', 'Aussi', 'Earth', NULL, NULL, 'alive', 'married', NULL, '/images/avatar-female.svg', 4),
(4, 'Hahahehe', 'hahahehe@gmail.com', '09865356', 'female', '1975-09-09', 'Earth', 'Earth', NULL, NULL, 'alive', 'married', NULL, '/images/avatar-female.svg', 7),
(6, 'Hihihihi', 'hihihihi@gmail.com', '08587856535', 'female', '2000-08-30', 'Earth', 'Earth', NULL, NULL, 'alive', 'single', NULL, '/uploads/family/family_member_9_1775708200.jpg', 9),
(7, 'Hehehaha', 'hehehaha@gmail.com', '0839844775686', 'male', '1980-01-18', 'Earth', 'Earth', NULL, NULL, 'alive', 'married', NULL, '/images/avatar-male.svg', 10),
(8, 'Huhuhuhu', 'huhuhuhu@gmail.com', '08676464343', 'female', '1977-12-12', 'Earth', 'Earth', NULL, NULL, 'alive', 'married', NULL, '/uploads/family/family_member_11_1775743270.jpg', 11),
(9, 'Hehahuhu', 'hehahuhu@gmail.com', '08411212', 'male', '2001-11-19', 'Earth', 'Earth', NULL, NULL, 'alive', 'married', NULL, '/images/avatar-male.svg', 12),
(10, 'Hohohoho', 'hohohoho@gmail.com', '0838577465', 'female', '2002-01-01', 'Earth', 'Earth', NULL, NULL, 'alive', 'married', NULL, '/images/avatar-female.svg', 13),
(11, 'Hehahuho', 'hehahuho@gmail.com', '08643145678', 'female', '2020-12-12', 'Earth', 'Earth', NULL, NULL, 'alive', 'single', NULL, '/images/avatar-female.svg', 14);

-- --------------------------------------------------------

--
-- Table structure for table `level`
--

CREATE TABLE `level` (
  `levelid` int(11) NOT NULL,
  `levelname` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `level`
--

INSERT INTO `level` (`levelid`, `levelname`) VALUES
(1, 'Employer'),
(2, 'Family Member');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_04_07_210500_make_family_member_age_nullable', 2),
(5, '2026_04_07_213000_change_family_member_education_status_to_string', 3),
(6, '2026_04_09_110000_create_password_reset_tokens_if_not_exists', 3),
(7, '2026_04_09_180000_add_reset_password_columns_to_user_table', 3);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `relationship`
--

CREATE TABLE `relationship` (
  `relationid` int(11) NOT NULL,
  `memberid` int(11) NOT NULL,
  `relatedmemberid` int(11) NOT NULL,
  `relationtype` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `relationship`
--

INSERT INTO `relationship` (`relationid`, `memberid`, `relatedmemberid`, `relationtype`) VALUES
(1, 1, 2, 'partner'),
(2, 2, 1, 'partner'),
(4, 1, 4, 'child'),
(5, 2, 4, 'child'),
(8, 4, 6, 'child'),
(9, 2, 7, 'child'),
(10, 1, 7, 'child'),
(11, 7, 8, 'partner'),
(12, 8, 7, 'partner'),
(13, 7, 9, 'child'),
(14, 8, 9, 'child'),
(15, 9, 10, 'partner'),
(16, 10, 9, 'partner'),
(17, 9, 11, 'child'),
(18, 10, 11, 'child');

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `roleid` int(11) NOT NULL,
  `rolename` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`roleid`, `rolename`) VALUES
(1, 'Superadmin'),
(2, 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('4urgMUNon2LrNSzQhL3uD9O5Ob2Nfak8ECdwsFPL', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoia3ZlcjFkd1RReHZXN0tETXJYbE1lR21UTnBXbGE3TmdLMlhaM0ZsTCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9fQ==', 1776004846),
('dzPTqaplKKyIZwTPSBb1ZIGYgKMnwCwYJzHoxl6c', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidUxzNzdPdTd3TlA3dDZKMFdyTnlhNDNESXdObmhsRzgxcjBjZFdFaiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9fQ==', 1776005654),
('fizrY7bptynHCAYkiL1AbtoeZh0bvO7ujcnNNBWq', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoibmI4RUloWW1PS2hpa1pLTWRVaHZKRjlwWG5QU3NhZ2g5ejBOQmY4ayI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czoxODoiYXV0aGVudGljYXRlZF91c2VyIjthOjg6e3M6NjoidXNlcmlkIjtpOjM7czo4OiJ1c2VybmFtZSI7czo4OiJoYWhhaGFoYSI7czo3OiJsZXZlbGlkIjtpOjI7czo5OiJsZXZlbG5hbWUiO3M6MTM6IkZhbWlseSBNZW1iZXIiO3M6Njoicm9sZWlkIjtOO3M6ODoicm9sZW5hbWUiO047czo4OiJlbXBsb3llciI7TjtzOjEyOiJmYW1pbHlNZW1iZXIiO086ODoic3RkQ2xhc3MiOjE1OntzOjg6Im1lbWJlcmlkIjtpOjE7czo0OiJuYW1lIjtzOjg6IkhhaGFoYWhhIjtzOjU6ImVtYWlsIjtzOjE4OiJoYWhhaGFoYUBnbWFpbC5jb20iO3M6MTE6InBob25lbnVtYmVyIjtzOjEwOiIxMjM0NTY3ODkwIjtzOjY6ImdlbmRlciI7czo0OiJtYWxlIjtzOjk6ImJpcnRoZGF0ZSI7czoxMDoiMTk1MC0wNC0xMSI7czoxMDoiYmlydGhwbGFjZSI7czo5OiJBdXN0cmFsaWEiO3M6NzoiYWRkcmVzcyI7czo2OiJISEhISEgiO3M6Mzoiam9iIjtzOjE3OiJTb2Z0d2FyZSBFbmdpbmVlciI7czoxNjoiZWR1Y2F0aW9uX3N0YXR1cyI7czoyMToiVm9jYXRpb25hbCBIaWdoc2Nob29sIjtzOjExOiJsaWZlX3N0YXR1cyI7czo1OiJhbGl2ZSI7czoxNDoibWFyaXRhbF9zdGF0dXMiO3M6Njoic2luZ2xlIjtzOjg6ImRlYWRkYXRlIjtOO3M6NzoicGljdHVyZSI7czoyMzoiL2ltYWdlcy9hdmF0YXItbWFsZS5zdmciO3M6NjoidXNlcmlkIjtpOjM7fX19', 1776005824),
('FuFzJGvkkzTV8GyszvlmMNygf2ru30Jzs27IUsIv', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidkpvS2NySUMyektCQzB3R0F2RXg0Q0gxVFUxTEY5MGZuRVlGNUtKTyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1776004269),
('IOBcky6GnG5HW2pQJo6Cu38cKX3Myp9lPILvkZ6X', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSDBEUWc4U2xoNzg5ZENNRlZCQ2lTVHdvWmtZR2s2cmtEbHRwVU16cCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czoxODoiYXV0aGVudGljYXRlZF91c2VyIjthOjg6e3M6NjoidXNlcmlkIjtpOjE7czo4OiJ1c2VybmFtZSI7czoxMDoic3VwZXJhZG1pbiI7czo3OiJsZXZlbGlkIjtpOjE7czo5OiJsZXZlbG5hbWUiO3M6ODoiRW1wbG95ZXIiO3M6Njoicm9sZWlkIjtpOjE7czo4OiJyb2xlbmFtZSI7czoxMDoiU3VwZXJhZG1pbiI7czo4OiJlbXBsb3llciI7Tzo4OiJzdGRDbGFzcyI6Njp7czoxMDoiZW1wbG95ZXJpZCI7aToxO3M6NDoibmFtZSI7czoxMDoiU3VwZXJhZG1pbiI7czo1OiJlbWFpbCI7czoyMDoic3VwZXJhZG1pbkBnbWFpbC5jb20iO3M6MTE6InBob25lbnVtYmVyIjtzOjEwOiIwOTg3NjU0MzIxIjtzOjY6InJvbGVpZCI7aToxO3M6ODoicm9sZW5hbWUiO3M6MTA6IlN1cGVyYWRtaW4iO31zOjEyOiJmYW1pbHlNZW1iZXIiO047fX0=', 1775898558),
('wNEsx8jCsdZ7osOYOl4IBrlH19KYGXODiQQXfCsK', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiOWdjQUNuR1pNeGZjQ3B4ZlZjT1pwcjhGektjZ20wV2hjNzFpMFlORSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1775896453);

-- --------------------------------------------------------

--
-- Table structure for table `system`
--

CREATE TABLE `system` (
  `systemid` int(11) NOT NULL,
  `systemname` varchar(255) NOT NULL,
  `systemlogo` varchar(255) NOT NULL,
  `systemcontact` varchar(255) NOT NULL,
  `systemmanager` varchar(255) NOT NULL,
  `systemaddress` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system`
--

INSERT INTO `system` (`systemid`, `systemname`, `systemlogo`, `systemcontact`, `systemmanager`, `systemaddress`) VALUES
(1, 'Family Tree', 'asd', 'Familytree@gmail.com', 'Superadmin', 'ASD');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `userid` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `levelid` int(11) NOT NULL,
  `reset_password_token` varchar(255) DEFAULT NULL,
  `reset_password_token_expired` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`userid`, `username`, `password`, `levelid`, `reset_password_token`, `reset_password_token_expired`) VALUES
(1, 'superadmin', '\\$2y\\$12\\$R4dR2oQyp34A7tbVnzaDrO1yRFEAGobuJfXbcleS2SVzgD9z8ciCG', 1, '', '2026-04-06 15:46:48'),
(2, 'admin', '$2y$12$1kP3JzHpKRasqpQpvqtk2.gIXw1X8AdrL1iZbPrjeQ3hh8V38ZAau', 1, NULL, NULL),
(3, 'hahahaha', '$2y$12$cjw0up3w4szwNaIODlgyM.CVjS5YqwK9FR6BbqTCNDlPuqDJbqmFe', 2, NULL, NULL),
(4, 'hehehehe', '$2y$12$ZFH9awIHbJaWNOGlXOrmougnTjGexGl6EqCYOTNOyEJK.1epccLY2', 2, NULL, NULL),
(6, 'abcd', '$2y$12$P4ZSpNT2zdk.QqsL2Jjr7.m6TYyZUV/alx9rwdf0Wp6UV/kjQJrb2', 1, NULL, NULL),
(7, 'hahahehe', '$2y$12$cqJ71uRBRhCBZURy9hiGVu0GbnuliM0tuBJ9ZvU7FH..fK.0qfP/O', 2, NULL, NULL),
(9, 'hihihihi', '$2y$12$9pfN.GMz9q3l/m5Kgf1Z/.CGNMaZ2/HeSi5rDGzIkVz6VlvU.shWm', 2, NULL, NULL),
(10, 'hehehaha', '$2y$12$GHieVOd9GEdl1VFCSCeE/u6WN8xvOHQgxMp1UEi1Zo0ZhxdGSHb4S', 2, NULL, NULL),
(11, 'huhuhuhu', '$2y$12$iPfce.4oFJw6HPC4g8t3ieR9IJkOWrZU/Y3v8wg4wP6mdym4T.C2W', 2, NULL, NULL),
(12, 'hehahuhu', '$2y$12$B4JXIC.PWhGsUakwQ70BWeV5nvmC4vmIfWk92WQtS0ttF7XUwxH46', 2, NULL, NULL),
(13, 'hohohoho', '$2y$12$8rYO.nDxBuLtyyCNw0St3ePo3HsgR08uhWKRfmZ4Gf4Xk1fbh28pK', 2, NULL, NULL),
(14, 'hehahuho', '$2y$12$GnTByig/fo3nlIAc2W8e9.e1oQfsIg5e2BPisYk3uCvwJi1O7IbKW', 2, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `employer`
--
ALTER TABLE `employer`
  ADD PRIMARY KEY (`employerid`);

--
-- Indexes for table `family`
--
ALTER TABLE `family`
  ADD PRIMARY KEY (`familyid`);

--
-- Indexes for table `family_member`
--
ALTER TABLE `family_member`
  ADD PRIMARY KEY (`memberid`);

--
-- Indexes for table `level`
--
ALTER TABLE `level`
  ADD PRIMARY KEY (`levelid`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `relationship`
--
ALTER TABLE `relationship`
  ADD PRIMARY KEY (`relationid`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`roleid`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `system`
--
ALTER TABLE `system`
  ADD PRIMARY KEY (`systemid`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`userid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `employer`
--
ALTER TABLE `employer`
  MODIFY `employerid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `family`
--
ALTER TABLE `family`
  MODIFY `familyid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `family_member`
--
ALTER TABLE `family_member`
  MODIFY `memberid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `level`
--
ALTER TABLE `level`
  MODIFY `levelid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `relationship`
--
ALTER TABLE `relationship`
  MODIFY `relationid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `roleid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `system`
--
ALTER TABLE `system`
  MODIFY `systemid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `userid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
