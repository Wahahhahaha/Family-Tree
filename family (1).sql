-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 16, 2026 at 04:54 AM
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
  `pending_email` varchar(255) DEFAULT NULL,
  `email_verification_token` varchar(255) DEFAULT NULL,
  `email_verification_token_expires_at` timestamp NULL DEFAULT NULL,
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

INSERT INTO `family_member` (`memberid`, `name`, `email`, `pending_email`, `email_verification_token`, `email_verification_token_expires_at`, `phonenumber`, `gender`, `birthdate`, `birthplace`, `address`, `job`, `education_status`, `life_status`, `marital_status`, `deaddate`, `picture`, `userid`) VALUES
(1, 'Hahahaha', 'hahahaha@gmail.com', 'hahahaha@gmail.com', '0JOisu2f6pPWfR65upmCV6rxKjc2qsjtKT7hYTA7NCbUi0EJRwy1KqhyqIqAKZeS', '2026-04-15 19:40:39', '12345678901', 'male', '1950-04-11', 'Australia', 'HHHHHH', 'Software Engineerr', 'Vocational Highschool', 'alive', 'single', NULL, '/images/avatar-male.svg', 3),
(2, 'Hehehehe', 'hehehehe@gmail.com', NULL, NULL, NULL, '098765432', 'female', '1950-05-31', 'Aussi', 'Earth', NULL, NULL, 'alive', 'married', NULL, '/images/avatar-female.svg', 4),
(4, 'Hahahehe', 'hahahehe@gmail.com', NULL, NULL, NULL, '09865356', 'female', '1975-09-09', 'Earth', 'Earth', NULL, NULL, 'alive', 'married', NULL, '/images/avatar-female.svg', 7),
(6, 'Hihihihi', 'hihihihi@gmail.com', NULL, NULL, NULL, '08587856535', 'female', '2000-08-30', 'Earth', 'Earth', NULL, NULL, 'alive', 'single', NULL, '/uploads/family/family_member_9_1775708200.jpg', 9),
(7, 'Hehehaha', 'hehehaha@gmail.com', NULL, NULL, NULL, '0839844775686', 'male', '1980-01-18', 'Earth', 'Earth', NULL, NULL, 'alive', 'married', NULL, '/images/avatar-male.svg', 10),
(8, 'Huhuhuhu', 'huhuhuhu@gmail.com', NULL, NULL, NULL, '08676464343', 'female', '1977-12-12', 'Earth', 'Earth', NULL, NULL, 'alive', 'married', NULL, '/uploads/family/family_member_11_1775743270.jpg', 11),
(9, 'Hehahuhu', 'hehahuhu@gmail.com', NULL, NULL, NULL, '08411212', 'male', '2001-11-19', 'Earth', 'Earth', NULL, NULL, 'alive', 'married', NULL, '/images/avatar-male.svg', 12),
(10, 'Hohohoho', 'hohohoho@gmail.com', NULL, NULL, NULL, '0838577465', 'female', '2002-01-01', 'Earth', 'Earth', NULL, NULL, 'alive', 'married', NULL, '/images/avatar-female.svg', 13),
(11, 'Hehahuho', 'hehahuho@gmail.com', NULL, NULL, NULL, '08643145678', 'female', '2020-12-12', 'Earth', 'Earth', NULL, NULL, 'alive', 'married', NULL, '/images/avatar-female.svg', 14),
(13, '1', '1@gmail.com', NULL, NULL, NULL, '09876543', 'male', '2019-02-11', 'Australia', 'Earth', NULL, NULL, 'alive', 'married', NULL, '/images/avatar-male.svg', 16),
(14, 'Hello', 'hello@gmail.com', 'happygolucky123lalala@gmail.com', 'Qh2EoVu6YluJoKq0kYnPxbqL9z3rDbs4Ag92XPNioJvLZjCPgYYT4U7LXMdte92t', '2026-04-15 19:45:50', '054546779067', 'male', '1990-02-22', 'Earth', 'Earth', NULL, NULL, 'alive', 'single', NULL, '/images/avatar-male.svg', 17),
(15, 'hi', 'hi@gmail.com', NULL, NULL, NULL, '098765', 'male', '2003-02-23', 'Earth', 'Earth', NULL, NULL, 'alive', 'single', NULL, '/images/avatar-male.svg', 21);

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
(7, '2026_04_09_180000_add_reset_password_columns_to_user_table', 3),
(8, '2026_04_13_100000_create_settings_table', 4),
(9, '2026_04_15_000000_add_deleted_at_to_user_table', 5),
(10, '2026_04_15_add_child_parenting_mode_to_relationship_table', 6),
(11, '2026_04_16_020626_add_email_verification_fields_to_family_member_table', 7);

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
  `relationtype` varchar(255) NOT NULL,
  `child_parenting_mode` varchar(255) DEFAULT 'with_current_partner'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `relationship`
--

INSERT INTO `relationship` (`relationid`, `memberid`, `relatedmemberid`, `relationtype`, `child_parenting_mode`) VALUES
(1, 1, 2, 'partner', 'with_current_partner'),
(2, 2, 1, 'partner', 'with_current_partner'),
(4, 1, 4, 'child', 'with_current_partner'),
(5, 2, 4, 'child', 'with_current_partner'),
(8, 4, 6, 'child', 'with_current_partner'),
(9, 2, 7, 'child', 'with_current_partner'),
(10, 1, 7, 'child', 'with_current_partner'),
(11, 7, 8, 'partner', 'with_current_partner'),
(12, 8, 7, 'partner', 'with_current_partner'),
(13, 7, 9, 'child', 'with_current_partner'),
(14, 8, 9, 'child', 'with_current_partner'),
(15, 9, 10, 'partner', 'with_current_partner'),
(16, 10, 9, 'partner', 'with_current_partner'),
(17, 9, 11, 'child', 'with_current_partner'),
(18, 10, 11, 'child', 'with_current_partner'),
(21, 11, 13, 'partner', 'with_current_partner'),
(22, 13, 11, 'partner', 'with_current_partner'),
(23, 1, 14, 'child', 'with_current_partner'),
(24, 14, 15, 'child', 'with_current_partner');

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
('7CDtONnF1Jg8GpdITZAgAFvApE5jJrkZw52HdcOr', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVE5rRUFMYWwzM2czUWNHbEd3SGRNcFQ3Nk9MYWJSNHhUb25KaDFzSyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9fQ==', 1776307111),
('7GA4GUyo9n3lTf8RoTvl4WAUAuINim2XzSOkNelq', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidDlDckRNMEhDWlk3SldHMGptSDJOOGRwRWVtUjF1RlNYeEZCYjRLRiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9fQ==', 1776306802),
('AMqj2eBBcyywL95MdZLwCxo6ar0kTvMrE9ilvnyq', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoia0szMHBYWEhCa0lKcktYUlh1Y2xsUnlFWGFLa21BWFcxREtqd2Z3aSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9fQ==', 1776307801),
('ddPDKoISpThk5xm2St6Rqmkm8NoHq9182UjRAuqM', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoia0FtelZvS1hzV01RdWt4QzhqSWViN3pxbjRpaFNtNWxObWZHaXpSbyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czoxODoiYXV0aGVudGljYXRlZF91c2VyIjthOjk6e3M6NjoidXNlcmlkIjtpOjE2O3M6ODoidXNlcm5hbWUiO3M6MToiMSI7czo0OiJuYW1lIjtzOjE6IjEiO3M6NzoibGV2ZWxpZCI7aToyO3M6OToibGV2ZWxuYW1lIjtzOjEzOiJGYW1pbHkgTWVtYmVyIjtzOjY6InJvbGVpZCI7TjtzOjg6InJvbGVuYW1lIjtOO3M6ODoiZW1wbG95ZXIiO047czoxMjoiZmFtaWx5TWVtYmVyIjtPOjg6InN0ZENsYXNzIjoxODp7czo4OiJtZW1iZXJpZCI7aToxMztzOjQ6Im5hbWUiO3M6MToiMSI7czo1OiJlbWFpbCI7czoxMToiMUBnbWFpbC5jb20iO3M6MTM6InBlbmRpbmdfZW1haWwiO047czoyNDoiZW1haWxfdmVyaWZpY2F0aW9uX3Rva2VuIjtOO3M6MzU6ImVtYWlsX3ZlcmlmaWNhdGlvbl90b2tlbl9leHBpcmVzX2F0IjtOO3M6MTE6InBob25lbnVtYmVyIjtzOjg6IjA5ODc2NTQzIjtzOjY6ImdlbmRlciI7czo0OiJtYWxlIjtzOjk6ImJpcnRoZGF0ZSI7czoxMDoiMjAxOS0wMi0xMSI7czoxMDoiYmlydGhwbGFjZSI7czo5OiJBdXN0cmFsaWEiO3M6NzoiYWRkcmVzcyI7czo1OiJFYXJ0aCI7czozOiJqb2IiO047czoxNjoiZWR1Y2F0aW9uX3N0YXR1cyI7TjtzOjExOiJsaWZlX3N0YXR1cyI7czo1OiJhbGl2ZSI7czoxNDoibWFyaXRhbF9zdGF0dXMiO3M6NzoibWFycmllZCI7czo4OiJkZWFkZGF0ZSI7TjtzOjc6InBpY3R1cmUiO3M6MjM6Ii9pbWFnZXMvYXZhdGFyLW1hbGUuc3ZnIjtzOjY6InVzZXJpZCI7aToxNjt9fX0=', 1776307693),
('fkr4ewF7sclLLcHiSC8l2XmKUl9lLDG4KVznzUud', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiN2lQMFBINGNSSGRZU0RkWXlMU2FLMFdiNGJIbkVpekdpR1NIcmF0QiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czoxODoiYXV0aGVudGljYXRlZF91c2VyIjthOjk6e3M6NjoidXNlcmlkIjtpOjEzO3M6ODoidXNlcm5hbWUiO3M6ODoiaG9ob2hvaG8iO3M6NDoibmFtZSI7czo4OiJIb2hvaG9obyI7czo3OiJsZXZlbGlkIjtpOjI7czo5OiJsZXZlbG5hbWUiO3M6MTM6IkZhbWlseSBNZW1iZXIiO3M6Njoicm9sZWlkIjtOO3M6ODoicm9sZW5hbWUiO047czo4OiJlbXBsb3llciI7TjtzOjEyOiJmYW1pbHlNZW1iZXIiO086ODoic3RkQ2xhc3MiOjE4OntzOjg6Im1lbWJlcmlkIjtpOjEwO3M6NDoibmFtZSI7czo4OiJIb2hvaG9obyI7czo1OiJlbWFpbCI7czoxODoiaG9ob2hvaG9AZ21haWwuY29tIjtzOjEzOiJwZW5kaW5nX2VtYWlsIjtOO3M6MjQ6ImVtYWlsX3ZlcmlmaWNhdGlvbl90b2tlbiI7TjtzOjM1OiJlbWFpbF92ZXJpZmljYXRpb25fdG9rZW5fZXhwaXJlc19hdCI7TjtzOjExOiJwaG9uZW51bWJlciI7czoxMDoiMDgzODU3NzQ2NSI7czo2OiJnZW5kZXIiO3M6NjoiZmVtYWxlIjtzOjk6ImJpcnRoZGF0ZSI7czoxMDoiMjAwMi0wMS0wMSI7czoxMDoiYmlydGhwbGFjZSI7czo1OiJFYXJ0aCI7czo3OiJhZGRyZXNzIjtzOjU6IkVhcnRoIjtzOjM6ImpvYiI7TjtzOjE2OiJlZHVjYXRpb25fc3RhdHVzIjtOO3M6MTE6ImxpZmVfc3RhdHVzIjtzOjU6ImFsaXZlIjtzOjE0OiJtYXJpdGFsX3N0YXR1cyI7czo3OiJtYXJyaWVkIjtzOjg6ImRlYWRkYXRlIjtOO3M6NzoicGljdHVyZSI7czoyNToiL2ltYWdlcy9hdmF0YXItZmVtYWxlLnN2ZyI7czo2OiJ1c2VyaWQiO2k6MTM7fX19', 1776307814),
('sIBvdpEFNi4fWD0pNi5v7OUaJn30AX9qaaecDClJ', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoialk1UXFraWxwbFNRQ0I4WGh6NGY0Z0gwUkszck5sa0xRZ1FwNDJETSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1776307793),
('tOR4U8Zf52gt9NS0jeJo3WiEdLYeSMNwDwp8s7i5', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoib0V0YXQ2MExXYVpGc2VIV1NTTmt1Z3ZCOXdMVUk4N2RPRE90R2dPQSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9fQ==', 1776307015),
('tYJ8bhqTcWyT2CU7riwLI0JEQBGMQJE2kChLGt62', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiM3RlZENpdFdMMU10QThGRXVqVFdjdE9rZ2NtZDVQdTNNcXVsc3Z5RCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9fQ==', 1776305748),
('ULLUZPL79ewgboKKsjjbFWMxnz9ikTqvQi7TtAlf', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieGJiU2paUGthaHBCQkJKbU9XeEx6em13T29zZTkwejBlUktuS2hwRSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9fQ==', 1776307687),
('WkfesjjLR9B59XY3GNSTw3L9cBvANFPSBuKL1uik', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYk1iTVFGSmZYZm9xOEJVV1h3Uk1kN21lMUs5bHdJNm1BQWFXRGI4RiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1776304814);

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
(1, 'Family Tree', '/uploads/system/system_logo_1776049478.png', 'Familytree@gmail.com', 'Superadmin', 'ASD');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `userid` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `levelid` int(11) NOT NULL,
  `reset_password_token` varchar(255) DEFAULT NULL,
  `reset_password_token_expired` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`userid`, `username`, `password`, `deleted_at`, `levelid`, `reset_password_token`, `reset_password_token_expired`) VALUES
(1, 'superadmin', '\\$2y\\$12\\$R4dR2oQyp34A7tbVnzaDrO1yRFEAGobuJfXbcleS2SVzgD9z8ciCG', NULL, 1, '', '2026-04-06 15:46:48'),
(2, 'admin', '$2y$12$1kP3JzHpKRasqpQpvqtk2.gIXw1X8AdrL1iZbPrjeQ3hh8V38ZAau', NULL, 1, NULL, NULL),
(3, 'hahahaha', '$2y$12$cjw0up3w4szwNaIODlgyM.CVjS5YqwK9FR6BbqTCNDlPuqDJbqmFe', NULL, 2, NULL, NULL),
(4, 'hehehehe', '$2y$12$ZFH9awIHbJaWNOGlXOrmougnTjGexGl6EqCYOTNOyEJK.1epccLY2', NULL, 2, NULL, NULL),
(6, 'abcd', '$2y$12$P4ZSpNT2zdk.QqsL2Jjr7.m6TYyZUV/alx9rwdf0Wp6UV/kjQJrb2', NULL, 1, NULL, NULL),
(7, 'hahahehe', '$2y$12$cqJ71uRBRhCBZURy9hiGVu0GbnuliM0tuBJ9ZvU7FH..fK.0qfP/O', NULL, 2, NULL, NULL),
(9, 'hihihihi', '$2y$12$9pfN.GMz9q3l/m5Kgf1Z/.CGNMaZ2/HeSi5rDGzIkVz6VlvU.shWm', NULL, 2, NULL, NULL),
(10, 'hehehaha', '$2y$12$GHieVOd9GEdl1VFCSCeE/u6WN8xvOHQgxMp1UEi1Zo0ZhxdGSHb4S', NULL, 2, NULL, NULL),
(11, 'huhuhuhu', '$2y$12$iPfce.4oFJw6HPC4g8t3ieR9IJkOWrZU/Y3v8wg4wP6mdym4T.C2W', NULL, 2, NULL, NULL),
(12, 'hehahuhu', '$2y$12$B4JXIC.PWhGsUakwQ70BWeV5nvmC4vmIfWk92WQtS0ttF7XUwxH46', NULL, 2, NULL, NULL),
(13, 'hohohoho', '$2y$12$FPtxrnzL6.MQER1ZaqWxV.xoNIUPtZv0SQXGmG8KwstuDi7TWiLvG', NULL, 2, NULL, NULL),
(14, 'hehahuho', '$2y$12$GnTByig/fo3nlIAc2W8e9.e1oQfsIg5e2BPisYk3uCvwJi1O7IbKW', NULL, 2, NULL, NULL),
(16, '1', '$2y$12$uEr6aN77I1JUO2Cdgi9ViuasVKCSY0gfqvGkTMZ8HbmrfDCdC00iW', NULL, 2, NULL, NULL),
(17, 'hello', '$2y$12$liP6z8uvH..O0Z4DNnGhzuBd8V/Qkm4CUdEJO2DKIra3Yjqiy5HWe', NULL, 2, NULL, NULL),
(21, 'hi', '$2y$12$6O9ijvDDMGPeKFxA.UJoROlk43qlfmNikaWrIYkpyEY0TUDCge8bW', NULL, 2, NULL, NULL);

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
  MODIFY `employerid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `family`
--
ALTER TABLE `family`
  MODIFY `familyid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `family_member`
--
ALTER TABLE `family_member`
  MODIFY `memberid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `level`
--
ALTER TABLE `level`
  MODIFY `levelid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `relationship`
--
ALTER TABLE `relationship`
  MODIFY `relationid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

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
  MODIFY `userid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
