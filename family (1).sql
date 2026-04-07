-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 07, 2026 at 05:10 PM
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
(2, 'admin', 'admin@gmail.com', '13231', 1, 2);

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
  `education_status` int(11) DEFAULT NULL,
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
(1, 'Hahahaha', 'hahahaha@gmail.com', '1234567890', 'male', '1990-04-11', 'Australia', 'HHHHHH', NULL, NULL, 'alive', 'single', NULL, 'https://api.dicebear.com/9.x/personas/svg?seed=Hahahaha&backgroundColor=93c5fd', 3),
(2, 'Hehehehe', 'hehehehe@gmail.com', '098765432', 'female', '1993-05-31', 'Aussi', 'Earth', NULL, NULL, 'alive', 'married', NULL, '/images/avatar-female.svg', 4),
(3, 'Hohohoho', 'hohohoho@gmail.com', '0987654', 'male', '2000-03-12', 'America', 'Earth', NULL, NULL, 'alive', 'single', NULL, '/images/avatar-male.svg', 5);

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
(2, 'Family'),
(3, 'Employer'),
(4, 'Family');

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
(4, '2026_04_07_210500_make_family_member_age_nullable', 2);

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
(3, 1, 3, 'child');

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
(2, 'Admin'),
(3, 'Family Head'),
(4, 'Family Member');

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
('8lHeWhviYXGaorf1BM3dKm7Zw4J6NWnJGTcCtHpm', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUkI5UTZoM3ZGdW83azZDMUJYbktCOHl2VTVXREZ5bnZGczFYTVhFWiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9fQ==', 1775528110),
('A741sJwvK9QLT2JuQPIQKfNAoz3TZBGSpNhC9SIq', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMHY2T0g5c2I1VkFlalRjOVkza2plMk9oaXRwVktRdUxHZm1QRHRxNiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9fQ==', 1775527565),
('bjA992ajgij7HJkCYvzqKipYAZVV03R05vAPZx12', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiOHhOdVVPa3lpUktSUVpJTGlJZHhicUQxb0RKR2ZjNlVuQm82NTJBbyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czoxODoiYXV0aGVudGljYXRlZF91c2VyIjthOjg6e3M6NjoidXNlcmlkIjtpOjU7czo4OiJ1c2VybmFtZSI7czo4OiJob2hvaG9obyI7czo3OiJsZXZlbGlkIjtpOjI7czo5OiJsZXZlbG5hbWUiO3M6NjoiRmFtaWx5IjtzOjY6InJvbGVpZCI7TjtzOjg6InJvbGVuYW1lIjtOO3M6ODoiZW1wbG95ZXIiO047czoxMjoiZmFtaWx5TWVtYmVyIjtPOjg6InN0ZENsYXNzIjoxNTp7czo4OiJtZW1iZXJpZCI7aTozO3M6NDoibmFtZSI7czo4OiJIb2hvaG9obyI7czo1OiJlbWFpbCI7czoxODoiaG9ob2hvaG9AZ21haWwuY29tIjtzOjExOiJwaG9uZW51bWJlciI7czo3OiIwOTg3NjU0IjtzOjY6ImdlbmRlciI7czo0OiJtYWxlIjtzOjk6ImJpcnRoZGF0ZSI7czoxMDoiMjAwMC0wMy0xMiI7czoxMDoiYmlydGhwbGFjZSI7czo3OiJBbWVyaWNhIjtzOjc6ImFkZHJlc3MiO3M6NToiRWFydGgiO3M6Mzoiam9iIjtOO3M6MTY6ImVkdWNhdGlvbl9zdGF0dXMiO047czoxMToibGlmZV9zdGF0dXMiO3M6NToiYWxpdmUiO3M6MTQ6Im1hcml0YWxfc3RhdHVzIjtzOjY6InNpbmdsZSI7czo4OiJkZWFkZGF0ZSI7TjtzOjc6InBpY3R1cmUiO3M6MjM6Ii9pbWFnZXMvYXZhdGFyLW1hbGUuc3ZnIjtzOjY6InVzZXJpZCI7aTo1O319fQ==', 1775574565),
('Dq3WKYEhGO1BvLmEDVIdo4Gk5Sb5hX6h7HDJptnf', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVFpGcEswYm56WnlWcEJBelBhNEZsMnJCQTE4WU9tQ3JZSkk2M2NkVCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9fQ==', 1775571306),
('IuinlryHbNPeQpGQqpT8Wkz4jWsdcoM1dzOjVzNG', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUGVuank2em14M3pKM1ZBNnZhakZ5QXh2c2NQclhkNm0xb1JwYU83TCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1775486999),
('jIC4w2HwAjQBJadXlGGWl7w2XRhaisG7ajMQmjC9', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieWI3em5TaWVsc01qcXRremQ3TEpFUEZ4VlpmdFlUR3NkaDhqWmQwRSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9fQ==', 1775574557),
('kVIdWtGpB2CZXW2zZEROQSreApeS8tP5cqyQ6yZq', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMDE3ZFI1MTYzeGlxUzJvMUJMdVp3c0hYNHhYajJtdXppYXV0azZTQSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1775570928),
('LYopgpsyQXFqMEMvWEa0Lscpx93tgWvMCGpiJZbw', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiS2V1MGpZN2lUQkJ6NzJFUzZtQ0RmeVdSeHRKTk5xcjVXSHJXOHZwQyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1775527369),
('NKiDBEng6ziyhO2encXHAmzBdzXVR1RyrasKtkF9', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNWlybW15NlgwbmE4NzRabEhDbjJxUEtmenVsQ2VWdGVSRUFOQkh0bCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hY2NvdW50IjtzOjU6InJvdXRlIjtOO31zOjE4OiJhdXRoZW50aWNhdGVkX3VzZXIiO2E6ODp7czo2OiJ1c2VyaWQiO2k6MTtzOjg6InVzZXJuYW1lIjtzOjEwOiJzdXBlcmFkbWluIjtzOjc6ImxldmVsaWQiO2k6MTtzOjk6ImxldmVsbmFtZSI7czo4OiJFbXBsb3llciI7czo2OiJyb2xlaWQiO2k6MTtzOjg6InJvbGVuYW1lIjtzOjEwOiJTdXBlcmFkbWluIjtzOjg6ImVtcGxveWVyIjtPOjg6InN0ZENsYXNzIjo2OntzOjEwOiJlbXBsb3llcmlkIjtpOjE7czo0OiJuYW1lIjtzOjEwOiJTdXBlcmFkbWluIjtzOjU6ImVtYWlsIjtzOjIwOiJzdXBlcmFkbWluQGdtYWlsLmNvbSI7czoxMToicGhvbmVudW1iZXIiO3M6MTA6IjA5ODc2NTQzMjEiO3M6Njoicm9sZWlkIjtpOjE7czo4OiJyb2xlbmFtZSI7czoxMDoiU3VwZXJhZG1pbiI7fXM6MTI6ImZhbWlseU1lbWJlciI7Tjt9fQ==', 1775529730),
('VFqwW5aLqyQkyb1iPb2MPHNXiBAjl2tCRF1lLoay', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRUprcW5MRFh3V3poeWZ1aHFYV3dOeHlkTHdUU0M2SGpPaXUzTWVidCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zdXBlcmFkbWluL3NldHRpbmdzIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjE4OiJhdXRoZW50aWNhdGVkX3VzZXIiO2E6ODp7czo2OiJ1c2VyaWQiO2k6MTtzOjg6InVzZXJuYW1lIjtzOjEwOiJzdXBlcmFkbWluIjtzOjc6ImxldmVsaWQiO2k6MTtzOjk6ImxldmVsbmFtZSI7czo4OiJFbXBsb3llciI7czo2OiJyb2xlaWQiO2k6MTtzOjg6InJvbGVuYW1lIjtzOjEwOiJTdXBlcmFkbWluIjtzOjg6ImVtcGxveWVyIjtPOjg6InN0ZENsYXNzIjo2OntzOjEwOiJlbXBsb3llcmlkIjtpOjE7czo0OiJuYW1lIjtzOjEwOiJTdXBlcmFkbWluIjtzOjU6ImVtYWlsIjtzOjIwOiJzdXBlcmFkbWluQGdtYWlsLmNvbSI7czoxMToicGhvbmVudW1iZXIiO3M6MTA6IjA5ODc2NTQzMjEiO3M6Njoicm9sZWlkIjtpOjE7czo4OiJyb2xlbmFtZSI7czoxMDoiU3VwZXJhZG1pbiI7fXM6MTI6ImZhbWlseU1lbWJlciI7Tjt9fQ==', 1775494054);

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
(5, 'hohohoho', '$2y$12$apaIiG8kZErfOA/sHuQ0luNIAVCJXWJXzPAA.adTq2DKcgQ63UzGu', 2, NULL, NULL);

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
  MODIFY `employerid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `family`
--
ALTER TABLE `family`
  MODIFY `familyid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `family_member`
--
ALTER TABLE `family_member`
  MODIFY `memberid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `level`
--
ALTER TABLE `level`
  MODIFY `levelid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `relationship`
--
ALTER TABLE `relationship`
  MODIFY `relationid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `userid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
