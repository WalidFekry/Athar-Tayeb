-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 08, 2025 at 02:05 PM
-- Server version: 8.0.39
-- PHP Version: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `athartayeb_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'admin',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$hW01uEGzKOScPnyNeYUjBOvrN47HQ/n0nNNdbNaLUKeKeI3t8QkvW', 'admin', '2025-11-07 15:52:58');

-- --------------------------------------------------------

--
-- Table structure for table `memorials`
--

CREATE TABLE `memorials` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_status` tinyint(1) DEFAULT '0' COMMENT '0=pending, 1=approved',
  `quote` text COLLATE utf8mb4_unicode_ci,
  `quote_status` tinyint(1) DEFAULT '0' COMMENT '0=pending, 1=approved',
  `death_date` date DEFAULT NULL,
  `gender` enum('male','female') COLLATE utf8mb4_unicode_ci DEFAULT 'male',
  `whatsapp` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visits` int DEFAULT '0',
  `last_visit` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tasbeeh_subhan` int DEFAULT '0',
  `tasbeeh_alham` int DEFAULT '0',
  `tasbeeh_lailaha` int DEFAULT '0',
  `tasbeeh_allahu` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` tinyint(1) DEFAULT '0' COMMENT '0=pending, 1=approved'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `memorials`
--

INSERT INTO `memorials` (`id`, `name`, `from_name`, `image`, `image_status`, `quote`, `quote_status`, `death_date`, `gender`, `whatsapp`, `visits`, `last_visit`, `tasbeeh_subhan`, `tasbeeh_alham`, `tasbeeh_lailaha`, `tasbeeh_allahu`, `created_at`, `status`) VALUES
(1, 'محمد أحمد السيد', 'عائلة السيد', NULL, 1, 'كان رجلاً صالحاً محباً للخير، اللهم ارحمه واغفر له وأسكنه فسيح جناتك', 1, '2024-01-15', 'male', NULL, 257, '2025-11-07 22:08:58', 1296, 920, 708, 1106, '2025-11-07 07:54:44', 1),
(2, 'فاطمة محمود علي', 'أبناء المرحومة', NULL, 1, 'أم حنونة وقلب طيب، رحمها الله وجعل الجنة مثواها', 1, '2023-12-20', 'female', NULL, 191, '2025-11-07 22:08:58', 980, 769, 520, 890, '2025-11-07 07:54:44', 1),
(3, 'عبدالله خالد', NULL, NULL, 0, 'في انتظار المراجعة', 1, '2024-02-01', 'male', NULL, 14, '2025-11-07 22:08:58', 45, 30, 25, 40, '2025-11-07 07:54:44', 1),
(6, 'fdsa', 'وليد فكري', NULL, 0, 'fdsaffs', 1, '1999-01-01', 'male', '01094674881', 0, '2025-11-07 22:08:58', 0, 0, 0, 0, '2025-11-07 10:25:53', 1),
(7, 'بيسش', 'fds', NULL, 1, 'eafs', 1, NULL, 'male', '4dfsa', 3, '2025-11-07 22:08:58', 0, 0, 0, 0, '2025-11-07 10:28:46', 1),
(11, 'fds', 'وليد فكري', NULL, 1, 'asdfdfs', 2, '1999-12-08', 'female', '43r', 6, '2025-11-07 22:08:58', 31, 6, 0, 0, '2025-11-07 11:02:07', 1),
(12, 'بيسش', 'وليد', NULL, 1, 'بشيسب', 2, '0019-12-19', 'male', 'يبسش', 0, '2025-11-07 22:08:58', 0, 0, 0, 0, '2025-11-07 12:07:54', 1),
(13, 'بيسش', 'وليد', NULL, 1, 'بشيسببيسش', 2, '0019-12-19', 'male', 'يبسش', 0, '2025-11-07 22:08:58', 0, 0, 0, 0, '2025-11-07 12:08:11', 1),
(14, 'fs', 'fds', NULL, 1, 'fadsfas', 0, '1999-12-19', 'male', 'fdsafads', 1, '2025-11-07 22:08:58', 0, 0, 0, 0, '2025-11-07 12:41:41', 1),
(15, '432', 'fds', NULL, 1, 'بشيس', 0, '1999-12-11', 'male', 'بيشس', 0, '2025-11-07 22:08:58', 0, 0, 0, 0, '2025-11-07 12:48:54', 1),
(16, 'وليد', 'وليد', NULL, 1, 'لبيل', 0, '1999-01-05', 'male', '01094674881', 0, '2025-11-07 22:08:58', 0, 0, 0, 0, '2025-11-07 13:39:25', 1),
(17, 'ببشس', 'بيس', '1762522805_740b816114b4.jpg', 0, 'بشسب', 0, '1111-12-04', 'male', '01094674881', 9, '2025-11-07 22:27:03', 0, 0, 5, 0, '2025-11-07 13:40:05', 1),
(18, 'محمد', NULL, NULL, 1, 'بيشسبشسي', 0, '1999-12-02', 'male', '4dfsa', 9, '2025-11-08 13:23:03', 0, 0, 0, 0, '2025-11-07 16:02:24', 1),
(19, 'ليس', 'لبي', NULL, 0, 'سيبليسليس', 0, '1999-12-18', 'male', 'لبيس', 0, '2025-11-07 22:08:58', 0, 0, 0, 0, '2025-11-07 16:50:23', 1),
(20, 'بسيش', 'بيس', '1762554582_f4d7edac23b5.jpg', 1, NULL, 0, NULL, 'male', NULL, 32, '2025-11-08 14:01:57', 14, 15, 18, 5, '2025-11-07 22:29:42', 1),
(21, 'fads', 'fdas', '1762555258_abf998fac25b.jpg', 1, NULL, 0, NULL, 'male', NULL, 15, '2025-11-08 13:51:48', 3, 3, 3, 4, '2025-11-07 22:40:58', 1),
(22, 'fads', 'fdas', '1762555449_01bf7070d482.jpg', 0, NULL, 0, NULL, 'male', NULL, 2, '2025-11-07 22:52:22', 0, 0, 0, 0, '2025-11-07 22:44:09', 1),
(23, 'وليد فكري  حسني', 'وليد', NULL, 0, NULL, 0, NULL, 'male', NULL, 6, '2025-11-08 12:01:00', 0, 0, 0, 0, '2025-11-07 23:16:32', 1);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(22, 'auto_approval', '1', '2025-11-07 12:01:14', '2025-11-07 12:12:08'),
(47, 'maintenance_mode', '0', '2025-11-07 13:21:27', '2025-11-07 13:35:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `memorials`
--
ALTER TABLE `memorials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_image_status` (`image_status`),
  ADD KEY `idx_quote_status` (`quote_status`),
  ADD KEY `idx_created_at` (`created_at`);
ALTER TABLE `memorials` ADD FULLTEXT KEY `idx_name` (`name`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_key` (`setting_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `memorials`
--
ALTER TABLE `memorials`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
