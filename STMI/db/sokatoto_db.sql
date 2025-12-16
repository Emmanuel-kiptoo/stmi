-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 16, 2025 at 09:52 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sokatoto_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `log_type` varchar(20) DEFAULT 'info',
  `description` text NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_contacts`
--

CREATE TABLE `admin_contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied','archived') DEFAULT 'unread',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `replied_at` datetime DEFAULT NULL,
  `replied_by` int(11) DEFAULT NULL,
  `reply_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_contacts`
--

INSERT INTO `admin_contacts` (`id`, `name`, `email`, `subject`, `message`, `status`, `ip_address`, `user_agent`, `replied_at`, `replied_by`, `reply_message`, `created_at`) VALUES
(1, 'Michael Johnson', 'michael@example.com', 'Volunteer Inquiry', 'I would like to volunteer with your organization. Please let me know how I can help.', 'read', NULL, NULL, NULL, NULL, NULL, '2025-12-16 07:47:01'),
(2, 'Sarah Williams', 'sarah@example.com', 'Partnership Opportunity', 'Our company is interested in partnering with STMI Trust for CSR activities.', 'unread', NULL, NULL, NULL, NULL, NULL, '2025-12-16 07:47:01');

-- --------------------------------------------------------

--
-- Table structure for table `admin_donations`
--

CREATE TABLE `admin_donations` (
  `id` int(11) NOT NULL,
  `donor_name` varchar(100) NOT NULL,
  `donor_email` varchar(100) DEFAULT NULL,
  `donor_phone` varchar(20) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'KES',
  `payment_method` enum('mpesa','bank','cash','online') NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `purpose` enum('general','sports','arts','teen_mothers','education','specific') DEFAULT 'general',
  `status` enum('pending','confirmed','receipt_sent','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `receipt_sent_at` datetime DEFAULT NULL,
  `confirmed_by` int(11) DEFAULT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_events`
--

CREATE TABLE `admin_events` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `event_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(200) NOT NULL,
  `category` enum('upcoming','ongoing','past') DEFAULT 'upcoming',
  `status` enum('draft','published','cancelled') DEFAULT 'draft',
  `registration_link` varchar(500) DEFAULT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `featured_image` varchar(500) DEFAULT NULL,
  `gallery_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gallery_images`)),
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_events`
--

INSERT INTO `admin_events` (`id`, `title`, `description`, `event_date`, `start_time`, `end_time`, `location`, `category`, `status`, `registration_link`, `banner_image`, `featured_image`, `gallery_images`, `created_by`, `created_at`, `updated_at`) VALUES
(7, 'ki[', 'gg', '2026-01-02', '09:00:00', '17:00:00', 't', 'upcoming', 'published', '', 'uploads/events/temp/banner_1765914657_c0180ad82c1bd942.png', NULL, NULL, 1, '2025-12-16 19:50:57', '2025-12-16 19:51:02'),
(8, 'h', 'h', '2025-12-26', '09:00:00', '17:00:00', 'y', 'upcoming', 'published', '', 'uploads/events/temp/banner_1765916147_ecf2ffff5c457a93.jpeg', NULL, NULL, 1, '2025-12-16 20:15:47', '2025-12-16 20:15:51'),
(9, 'j', 'j', '2026-01-03', '09:00:00', '17:00:00', 't', 'ongoing', 'published', '', 'uploads/events/temp/banner_1765916341_17466827582f4a79.png', NULL, NULL, 1, '2025-12-16 20:19:01', '2025-12-16 20:19:04'),
(10, 'h', 'r', '2025-12-18', '09:00:00', '17:00:00', 'v', 'past', 'published', '', 'uploads/events/temp/banner_1765916406_c4b6776860b99c75.png', NULL, NULL, 1, '2025-12-16 20:20:06', '2025-12-16 20:20:11');

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 07:47:26'),
(2, 1, 'delete', 'admin_events', 1, '{\"title\":\"Annual Sports Day 2024\",\"category\":\"upcoming\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 08:05:57'),
(3, 1, 'update', 'admin_events', 2, '{\"title\":\"Teen Mothers Empowerment Workshop\",\"description\":\"A 2-day workshop focusing on life skills, financial literacy, and parenting skills for teen mothers. Certificates will be awarded to participants.\",\"category\":\"upcoming\",\"status\":\"published\"}', '{\"title\":\"Teen Mothers Empowerment Workshop true\",\"description\":\"A 2-day workshop focusing on life skills, financial literacy, and parenting skills for teen mothers. Certificates will be awarded to participants.\",\"category\":\"past\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 08:06:12'),
(4, 1, 'update', 'admin_events', 2, '{\"title\":\"Teen Mothers Empowerment Workshop true\",\"description\":\"A 2-day workshop focusing on life skills, financial literacy, and parenting skills for teen mothers. Certificates will be awarded to participants.\",\"category\":\"past\",\"status\":\"published\"}', '{\"title\":\"Teen Mothers Empowerment Workshop true\",\"description\":\"A 2-day workshop focusing on life skills, financial literacy, and parenting skills for teen mothers. Certificates will be awarded to participants.\",\"category\":\"ongoing\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 08:06:22'),
(5, 1, 'delete_donation', 'admin_donations', 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 08:06:59'),
(6, 1, 'delete_donation', 'admin_donations', 2, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 08:07:06'),
(7, 1, 'delete_donation', 'admin_donations', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 08:07:09'),
(8, 1, 'create', 'admin_team', 9, NULL, '{\"name\":\"Sharon Jelimo\",\"position\":\"ICT\",\"department\":\"programs\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 08:30:33'),
(9, 1, 'create', 'admin_team', 10, NULL, '{\"name\":\"kiptoo emmanuel\",\"position\":\"ICT\",\"department\":\"leadership\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 08:31:03'),
(10, 1, 'delete', 'admin_events', 2, '{\"title\":\"Teen Mothers Empowerment Workshop true\",\"category\":\"ongoing\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 08:31:37'),
(11, 1, 'delete', 'admin_events', 3, '{\"title\":\"Digital Literacy Program Launch\",\"category\":\"past\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 08:31:42'),
(12, 1, 'delete', 'admin_events', 4, '{\"title\":\"Christmas Outreach Program 2023\",\"category\":\"past\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 08:31:45'),
(13, 1, 'delete', 'admin_media', 1, '{\"title\":\"Children Sports Day 2023\",\"file_name\":\"sports-day-2023.jpg\",\"file_type\":\"image\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 08:47:50'),
(14, 1, 'create', 'admin_events', 5, NULL, '{\"title\":\"trial\",\"category\":\"upcoming\",\"status\":\"draft\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 09:16:12'),
(15, 1, 'publish', 'admin_events', 5, '{\"status\":\"draft\"}', '{\"status\":\"published\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 09:16:58'),
(16, 1, 'create', 'admin_events', 6, NULL, '{\"title\":\"und\",\"category\":\"upcoming\",\"status\":\"draft\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 09:19:11'),
(17, 1, 'publish', 'admin_events', 6, '{\"status\":\"draft\"}', '{\"status\":\"published\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 09:19:16'),
(18, 1, 'create', 'admin_events', 7, NULL, '{\"title\":\"ki[\",\"category\":\"upcoming\",\"status\":\"draft\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 19:50:57'),
(19, 1, 'publish', 'admin_events', 7, '{\"status\":\"draft\"}', '{\"status\":\"published\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 19:51:02'),
(20, 1, 'delete', 'admin_events', 6, '{\"title\":\"und\",\"category\":\"upcoming\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 20:14:57'),
(21, 1, 'delete', 'admin_events', 5, '{\"title\":\"trial\",\"category\":\"upcoming\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 20:15:00'),
(22, 1, 'create', 'admin_events', 8, NULL, '{\"title\":\"h\",\"category\":\"upcoming\",\"status\":\"draft\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 20:15:47'),
(23, 1, 'publish', 'admin_events', 8, '{\"status\":\"draft\"}', '{\"status\":\"published\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 20:15:51'),
(24, 1, 'create', 'admin_events', 9, NULL, '{\"title\":\"j\",\"category\":\"ongoing\",\"status\":\"draft\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 20:19:01'),
(25, 1, 'publish', 'admin_events', 9, '{\"status\":\"draft\"}', '{\"status\":\"published\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 20:19:04'),
(26, 1, 'create', 'admin_events', 10, NULL, '{\"title\":\"h\",\"category\":\"past\",\"status\":\"draft\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 20:20:06'),
(27, 1, 'publish', 'admin_events', 10, '{\"status\":\"draft\"}', '{\"status\":\"published\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 20:20:11'),
(28, 1, 'logout', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 20:32:24'),
(29, 1, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 20:32:36');

-- --------------------------------------------------------

--
-- Table structure for table `admin_media`
--

CREATE TABLE `admin_media` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` bigint(20) NOT NULL COMMENT 'Size in bytes',
  `file_type` enum('image','video','pdf','document','audio','archive','other') NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `category` enum('general','gallery','articles','newsletters','resources','reports','events','team') NOT NULL DEFAULT 'general',
  `sub_category` varchar(100) DEFAULT NULL,
  `report_type` enum('annual','financial','mel','general') DEFAULT 'general',
  `report_year` year(4) DEFAULT NULL,
  `report_audit_date` date DEFAULT NULL,
  `report_pages` int(11) DEFAULT NULL,
  `report_summary` text DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `status` enum('published','draft','archived') NOT NULL DEFAULT 'published',
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `views` int(11) DEFAULT 0,
  `downloads` int(11) DEFAULT 0,
  `last_accessed` timestamp NULL DEFAULT NULL,
  `width` int(11) DEFAULT NULL COMMENT 'Image width in pixels',
  `height` int(11) DEFAULT NULL COMMENT 'Image height in pixels',
  `keywords` text DEFAULT NULL,
  `author` varchar(100) DEFAULT NULL,
  `language` varchar(10) DEFAULT 'en'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_media`
--

INSERT INTO `admin_media` (`id`, `title`, `description`, `file_path`, `file_name`, `file_size`, `file_type`, `mime_type`, `category`, `sub_category`, `report_type`, `report_year`, `report_audit_date`, `report_pages`, `report_summary`, `alt_text`, `caption`, `status`, `uploaded_by`, `uploaded_at`, `updated_at`, `views`, `downloads`, `last_accessed`, `width`, `height`, `keywords`, `author`, `language`) VALUES
(1, 'Annual Report 2023', 'Complete annual report for the year 2023', 'uploads/media/2023/12/annual_report_2023.pdf', 'annual_report_2023.pdf', 5242880, 'pdf', 'application/pdf', 'reports', NULL, 'annual', '2023', NULL, 45, NULL, 'Annual Report 2023 Cover', 'Our most comprehensive annual report yet', 'published', 1, '2025-12-16 16:58:50', '2025-12-16 16:58:50', 0, 0, NULL, NULL, NULL, NULL, NULL, 'en'),
(2, 'Sports Day Gallery Photo', 'Children playing football during sports day', 'uploads/media/2023/12/sports_day_1.jpg', 'sports_day_1.jpg', 2048576, 'image', 'image/jpeg', 'gallery', 'sports', NULL, NULL, NULL, NULL, NULL, 'Children playing football', 'Annual Sports Day 2023', 'published', 1, '2025-12-16 16:58:50', '2025-12-16 16:58:50', 0, 0, NULL, NULL, NULL, NULL, NULL, 'en'),
(3, 'Financial Audit 2023', 'Audited financial statements for 2023', 'uploads/media/2024/03/financial_audit_2023.pdf', 'financial_audit_2023.pdf', 2936012, 'pdf', 'application/pdf', 'reports', NULL, 'financial', '2023', NULL, 28, NULL, 'Financial Audit 2023 Cover', 'Complete audited financial statements', 'published', 1, '2025-12-16 16:58:50', '2025-12-16 16:58:50', 0, 0, NULL, NULL, NULL, NULL, NULL, 'en'),
(4, 'Team Building Event', 'Team building activities photo', 'uploads/media/2023/11/team_building.jpg', 'team_building.jpg', 1857432, 'image', 'image/jpeg', 'team', NULL, NULL, NULL, NULL, NULL, NULL, 'Team members during building activities', 'Annual Team Building 2023', 'published', 1, '2025-12-16 16:58:50', '2025-12-16 16:58:50', 0, 0, NULL, NULL, NULL, NULL, NULL, 'en'),
(5, 'Digital Literacy Program', 'Video overview of digital literacy program', 'uploads/media/2023/10/digital_literacy.mp4', 'digital_literacy.mp4', 52428800, 'video', 'video/mp4', 'gallery', 'training', NULL, NULL, NULL, NULL, NULL, 'Digital literacy training session', 'Empowering children with digital skills', 'published', 1, '2025-12-16 16:58:50', '2025-12-16 16:58:50', 0, 0, NULL, NULL, NULL, NULL, NULL, 'en'),
(6, 'M&E Report Q4 2023', 'Monitoring and Evaluation report for Q4 2023', 'uploads/media/2024/01/me_report_q4_2023.pdf', 'me_report_q4_2023.pdf', 2202009, 'pdf', 'application/pdf', 'reports', NULL, 'mel', '2023', NULL, 32, NULL, 'M&E Report Q4 2023 Cover', 'Quarterly monitoring and evaluation findings', 'published', 1, '2025-12-16 16:58:50', '2025-12-16 16:58:50', 0, 0, NULL, NULL, NULL, NULL, NULL, 'en');

-- --------------------------------------------------------

--
-- Table structure for table `admin_reports`
--

CREATE TABLE `admin_reports` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `report_type` enum('annual','financial','monitoring','other') NOT NULL,
  `report_year` year(4) NOT NULL,
  `report_period` varchar(50) DEFAULT NULL,
  `report_number` varchar(50) DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `downloads` int(11) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `thumbnail_path` varchar(500) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `published_date` date DEFAULT NULL,
  `last_accessed` datetime DEFAULT NULL,
  `meta_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta_data`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_reports`
--

INSERT INTO `admin_reports` (`id`, `title`, `description`, `file_path`, `file_name`, `file_size`, `report_type`, `report_year`, `report_period`, `report_number`, `status`, `downloads`, `views`, `thumbnail_path`, `featured`, `uploaded_by`, `uploaded_at`, `published_date`, `last_accessed`, `meta_data`) VALUES
(1, 'Annual Impact Report 2023', 'Comprehensive annual report detailing our achievements, challenges, and financial performance for the year 2023.', 'uploads/reports/annual-report-2023.pdf', 'annual-report-2023.pdf', 5123456, 'annual', '2023', 'Full Year', 'ANNUAL-2023-001', 'published', 0, 0, NULL, 1, NULL, '2025-12-16 08:56:59', '2024-01-15', NULL, NULL),
(2, 'Financial Statements 2023', 'Audited financial statements including balance sheet, income statement, cash flow, and notes.', 'uploads/reports/financial-statements-2023.pdf', 'financial-statements-2023.pdf', 3456789, 'financial', '2023', 'Full Year', 'FIN-2023-001', 'published', 0, 0, NULL, 1, NULL, '2025-12-16 08:56:59', '2024-02-20', NULL, NULL),
(3, 'Quarter 1 Monitoring Report 2024', 'Monitoring and evaluation report for Q1 2024 covering program performance and impact metrics.', 'uploads/reports/q1-me-report-2024.pdf', 'q1-me-report-2024.pdf', 2345678, 'monitoring', '2024', 'Q1', 'ME-Q1-2024-001', 'published', 0, 0, NULL, 0, NULL, '2025-12-16 08:56:59', '2024-04-10', NULL, NULL),
(4, 'Annual Report 2022', 'Previous year annual report showing our growth and development journey.', 'uploads/reports/annual-report-2022.pdf', 'annual-report-2022.pdf', 4987654, 'annual', '2022', 'Full Year', 'ANNUAL-2022-001', 'published', 0, 0, NULL, 0, NULL, '2025-12-16 08:56:59', '2023-01-20', NULL, NULL),
(5, 'Mid-Year Financial Report 2023', 'Financial performance review for the first half of 2023.', 'uploads/reports/mid-year-financial-2023.pdf', 'mid-year-financial-2023.pdf', 2987654, 'financial', '2023', 'Jan-Jun', 'FIN-H1-2023-001', 'published', 0, 0, NULL, 0, NULL, '2025-12-16 08:56:59', '2023-07-15', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `admin_settings`
--

CREATE TABLE `admin_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','email','url','json','boolean') DEFAULT 'text',
  `category` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_settings`
--

INSERT INTO `admin_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_at`) VALUES
(1, 'site_name', 'Soka Toto Muda Initiative Trust', 'text', 'general', 'Website name', '2025-12-16 07:44:11'),
(2, 'site_email', 'stmitrust@gmail.com', 'email', 'general', 'Default contact email', '2025-12-16 07:44:11'),
(3, 'site_phone', '+254 728 274304', 'text', 'general', 'Contact phone number', '2025-12-16 07:44:11'),
(4, 'site_address', 'Alpha Glory Community Educational Center, Nairobi, Kenya', 'text', 'general', 'Physical address', '2025-12-16 07:44:11'),
(5, 'facebook_url', 'https://facebook.com/sokatoto', 'url', 'social', 'Facebook page URL', '2025-12-16 07:44:11'),
(6, 'twitter_url', 'https://twitter.com/sokatoto', 'url', 'social', 'Twitter profile URL', '2025-12-16 07:44:11'),
(7, 'instagram_url', 'https://instagram.com/sokatoto', 'url', 'social', 'Instagram profile URL', '2025-12-16 07:44:11'),
(8, 'linkedin_url', 'https://linkedin.com/company/sokatoto', 'url', 'social', 'LinkedIn page URL', '2025-12-16 07:44:11'),
(9, 'mpesa_paybill', '522522', 'text', 'donations', 'MPESA Paybill number', '2025-12-16 07:44:11'),
(10, 'mpesa_account', '7936016', 'text', 'donations', 'MPESA Account number', '2025-12-16 07:44:11'),
(11, 'bank_name', 'KCB Bank', 'text', 'donations', 'Bank name', '2025-12-16 07:44:11'),
(12, 'bank_account', '1335357998', 'text', 'donations', 'Bank account number', '2025-12-16 07:44:11'),
(13, 'bank_branch', 'Prestige Plaza', 'text', 'donations', 'Bank branch', '2025-12-16 07:44:11'),
(14, 'swift_code', 'KCBLKENX', 'text', 'donations', 'Swift code for international transfers', '2025-12-16 07:44:11'),
(15, 'about_intro', 'We are a Christian founded, non-profit making organization. We reach out to vulnerable and talented children through Sports (SOKA TOTO), Creative Arts (MUDA), Mentorship, Discipleship and Outreaches, Life skills, empowerment and psycho-Social Support to Young Mothers.', 'text', 'content', 'About page introduction', '2025-12-16 07:44:11'),
(16, 'vision_statement', 'To empower children with opportunities to explore their talents, receive support with dignity and grow into confident, independent individuals.', 'text', 'content', 'Vision statement', '2025-12-16 07:44:11'),
(17, 'mission_statement', 'To holistically transform our children through talent exploration so that they are excellent, independent decision-makers and resourceful people in society.', 'text', 'content', 'Mission statement', '2025-12-16 07:44:11');

-- --------------------------------------------------------

--
-- Table structure for table `admin_team`
--

CREATE TABLE `admin_team` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `department` enum('leadership','programs','sports','arts','mentorship','support') DEFAULT 'programs',
  `bio` text DEFAULT NULL,
  `photo` varchar(500) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `social_links` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`social_links`)),
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive','former') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_team`
--

INSERT INTO `admin_team` (`id`, `name`, `position`, `department`, `bio`, `photo`, `email`, `phone`, `social_links`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(8, 'Sharon Jelimo', 'ICT', 'sports', 'ict', 'uploads/team/694115af1709b_WhatsApp_Image_2025-10-05_at_22.08.15.jpeg', 'chelimosharoon9@gmail.com', '0702386080', '{\"linkedin\":\"\",\"twitter\":\"\",\"facebook\":\"\"}', 2, 'active', '2025-12-16 08:17:51', '2025-12-16 08:17:51'),
(9, 'Sharon Jelimo', 'ICT', 'programs', 'k', 'uploads/team/694118a96bb6f_1765873833.png', '', '', '{\"linkedin\":\"\",\"twitter\":\"\",\"facebook\":\"\"}', 2, 'active', '2025-12-16 08:30:33', '2025-12-16 08:30:33'),
(10, 'kiptoo emmanuel', 'ICT', 'leadership', '', 'uploads/team/694118c7c0c48_1765873863.png', '', '', '{\"linkedin\":\"\",\"twitter\":\"\",\"facebook\":\"\"}', 0, 'active', '2025-12-16 08:31:03', '2025-12-16 08:31:03');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role_id` int(11) NOT NULL DEFAULT 1,
  `role` enum('super_admin','admin','editor') DEFAULT 'editor',
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_picture` varchar(500) NOT NULL,
  `is_super_admin` tinyint(1) NOT NULL DEFAULT 0,
  `verification_token` varchar(100) NOT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `last_password_change` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role_id`, `role`, `status`, `last_login`, `created_at`, `updated_at`, `profile_picture`, `is_super_admin`, `verification_token`, `two_factor_enabled`, `last_password_change`) VALUES
(1, 'admin', 'admin@sokatoto.org', '$2y$10$obaH.e21Lr2WfnOi4vTIluGS643dCK6GAnmXbKLl.bv/Gkj3PLVKm', 'System Administrator', 1, 'super_admin', 'active', '2025-12-16 23:32:36', '2025-12-16 07:43:05', '2025-12-16 20:32:36', '', 0, '', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `collection_media_relationships`
--

CREATE TABLE `collection_media_relationships` (
  `collection_id` int(11) NOT NULL,
  `media_id` int(11) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `added_by` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `collection_media_relationships`
--

INSERT INTO `collection_media_relationships` (`collection_id`, `media_id`, `display_order`, `added_by`, `added_at`) VALUES
(1, 1, 1, 1, '2025-12-16 17:03:37'),
(2, 2, 1, 1, '2025-12-16 17:03:37');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `status` enum('unread','read','replied','archived') DEFAULT 'unread',
  `is_urgent` tinyint(1) DEFAULT 0,
  `attachments` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_access_logs`
--

CREATE TABLE `media_access_logs` (
  `id` int(11) NOT NULL,
  `media_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'NULL for anonymous users',
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `access_type` enum('view','download','preview') NOT NULL,
  `accessed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_collections`
--

CREATE TABLE `media_collections` (
  `id` int(11) NOT NULL,
  `collection_name` varchar(255) NOT NULL,
  `collection_slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `cover_media_id` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media_collections`
--

INSERT INTO `media_collections` (`id`, `collection_name`, `collection_slug`, `description`, `cover_media_id`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Annual Reports Collection', 'annual-reports', 'Collection of all annual reports', 1, 1, '2025-12-16 17:03:04', '2025-12-16 17:03:04'),
(2, 'Sports Gallery', 'sports-gallery', 'Photos and videos from sports events', 2, 1, '2025-12-16 17:03:04', '2025-12-16 17:03:04');

-- --------------------------------------------------------

--
-- Table structure for table `media_statistics_daily`
--

CREATE TABLE `media_statistics_daily` (
  `id` int(11) NOT NULL,
  `media_id` int(11) NOT NULL,
  `stat_date` date NOT NULL,
  `views` int(11) DEFAULT 0,
  `downloads` int(11) DEFAULT 0,
  `unique_visitors` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media_statistics_daily`
--

INSERT INTO `media_statistics_daily` (`id`, `media_id`, `stat_date`, `views`, `downloads`, `unique_visitors`) VALUES
(1, 1, '2024-03-01', 45, 12, 32),
(2, 1, '2024-03-02', 38, 8, 29),
(3, 2, '2024-03-01', 120, 0, 85),
(4, 2, '2024-03-02', 95, 0, 67);

-- --------------------------------------------------------

--
-- Table structure for table `media_tags`
--

CREATE TABLE `media_tags` (
  `id` int(11) NOT NULL,
  `tag_name` varchar(50) NOT NULL,
  `tag_slug` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media_tags`
--

INSERT INTO `media_tags` (`id`, `tag_name`, `tag_slug`, `description`, `created_at`) VALUES
(1, 'Sports', 'sports', 'Sports related media', '2025-12-16 16:59:34'),
(2, 'Education', 'education', 'Educational content', '2025-12-16 16:59:34'),
(3, 'Reports', 'reports', 'Official reports and documents', '2025-12-16 16:59:34'),
(4, 'Events', 'events', 'Event photos and videos', '2025-12-16 16:59:34'),
(5, 'Training', 'training', 'Training materials and sessions', '2025-12-16 16:59:34'),
(6, 'Children', 'children', 'Media featuring children', '2025-12-16 16:59:34'),
(7, 'Community', 'community', 'Community activities', '2025-12-16 16:59:34'),
(8, 'Annual', 'annual', 'Annual reports and reviews', '2025-12-16 16:59:34');

-- --------------------------------------------------------

--
-- Table structure for table `media_tag_relationships`
--

CREATE TABLE `media_tag_relationships` (
  `media_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `added_by` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media_tag_relationships`
--

INSERT INTO `media_tag_relationships` (`media_id`, `tag_id`, `added_by`, `added_at`) VALUES
(1, 3, 1, '2025-12-16 17:01:45'),
(1, 8, 1, '2025-12-16 17:01:45'),
(2, 1, 1, '2025-12-16 17:01:45'),
(2, 6, 1, '2025-12-16 17:01:45'),
(3, 3, 1, '2025-12-16 17:01:45'),
(5, 2, 1, '2025-12-16 17:01:45'),
(5, 5, 1, '2025-12-16 17:01:45'),
(6, 3, 1, '2025-12-16 17:01:45'),
(6, 7, 1, '2025-12-16 17:01:45');

-- --------------------------------------------------------

--
-- Table structure for table `media_usage`
--

CREATE TABLE `media_usage` (
  `id` int(11) NOT NULL,
  `media_id` int(11) NOT NULL,
  `page_type` enum('article','page','gallery','resource','report','other') NOT NULL,
  `page_id` int(11) NOT NULL COMMENT 'ID of the page/article where media is used',
  `usage_type` enum('featured','content','gallery','attachment','other') NOT NULL DEFAULT 'content',
  `added_by` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `removed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media_usage`
--

INSERT INTO `media_usage` (`id`, `media_id`, `page_type`, `page_id`, `usage_type`, `added_by`, `added_at`, `removed_at`) VALUES
(1, 1, 'report', 1, 'featured', 1, '2025-12-16 17:02:26', NULL),
(2, 2, 'gallery', 1, 'gallery', 1, '2025-12-16 17:02:26', NULL),
(3, 3, 'report', 2, 'featured', 1, '2025-12-16 17:02:26', NULL),
(4, 4, 'page', 3, 'content', 1, '2025-12-16 17:02:26', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `media_versions`
--

CREATE TABLE `media_versions` (
  `id` int(11) NOT NULL,
  `media_id` int(11) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `version_number` int(11) NOT NULL DEFAULT 1,
  `changes_description` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message_replies`
--

CREATE TABLE `message_replies` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `sent_by_id` int(11) NOT NULL,
  `sent_by_name` varchar(100) NOT NULL,
  `attachments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Sokatoto Muda Initiative Trust', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(2, 'site_url', 'https://sokatoto.org', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(3, 'site_email', 'admin@sokatoto.org', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(4, 'timezone', 'Africa/Nairobi', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(5, 'default_language', 'en', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(6, 'date_format', 'Y-m-d', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(7, 'time_format', 'H:i', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(8, 'maintenance_mode', '0', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(9, 'registration_enabled', '1', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(10, 'items_per_page', '20', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(11, 'admin_theme', 'light', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(12, 'smtp_host', 'smtp.gmail.com', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(13, 'smtp_port', '587', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(14, 'smtp_encryption', 'tls', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(15, 'smtp_auth', '1', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(16, 'from_email', 'noreply@sokatoto.org', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(17, 'two_factor_enabled', '0', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(18, 'login_attempt_limit', '1', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(19, 'max_login_attempts', '5', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(20, 'lockout_duration', '15', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(21, 'session_timeout_enabled', '1', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(22, 'session_timeout', '30', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(23, 'min_password_length', '8', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(24, 'password_expiry_days', '90', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(25, 'require_uppercase', '1', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(26, 'require_numbers', '1', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(27, 'require_special_chars', '0', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(28, 'backup_frequency', 'weekly', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(29, 'keep_backups_days', '30', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(30, 'compress_backups', '1', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(31, 'backup_location', '../backups/', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(32, 'email_header_color', '#0e0c5e', '2025-12-16 19:16:06', '2025-12-16 19:16:06'),
(33, 'email_footer_text', '© 2024 Sokatoto Muda Initiative Trust. All rights reserved.', '2025-12-16 19:16:06', '2025-12-16 19:16:06');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `permissions` text DEFAULT NULL COMMENT 'JSON array of permissions',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`id`, `role_name`, `description`, `permissions`, `created_at`) VALUES
(1, 'Administrator', 'Full system access', '[\"all\"]', '2025-12-16 17:58:50'),
(2, 'Editor', 'Can create and edit content', '[\"view\", \"create\", \"edit\", \"upload\"]', '2025-12-16 17:58:50'),
(3, 'Viewer', 'Read-only access', '[\"view\"]', '2025-12-16 17:58:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action_type` (`action_type`),
  ADD KEY `idx_log_type` (`log_type`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_ip_address` (`ip_address`);

--
-- Indexes for table `admin_contacts`
--
ALTER TABLE `admin_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `replied_by` (`replied_by`);

--
-- Indexes for table `admin_donations`
--
ALTER TABLE `admin_donations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `confirmed_by` (`confirmed_by`);

--
-- Indexes for table `admin_events`
--
ALTER TABLE `admin_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `admin_media`
--
ALTER TABLE `admin_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_file_type` (`file_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_uploaded_at` (`uploaded_at`),
  ADD KEY `idx_uploaded_by` (`uploaded_by`),
  ADD KEY `idx_report_type` (`report_type`),
  ADD KEY `idx_report_year` (`report_year`);
ALTER TABLE `admin_media` ADD FULLTEXT KEY `idx_search` (`title`,`description`,`alt_text`,`caption`);

--
-- Indexes for table `admin_reports`
--
ALTER TABLE `admin_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `idx_report_type` (`report_type`),
  ADD KEY `idx_report_year` (`report_year`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_featured` (`featured`),
  ADD KEY `idx_published_date` (`published_date`);

--
-- Indexes for table `admin_settings`
--
ALTER TABLE `admin_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `admin_team`
--
ALTER TABLE `admin_team`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `collection_media_relationships`
--
ALTER TABLE `collection_media_relationships`
  ADD PRIMARY KEY (`collection_id`,`media_id`),
  ADD KEY `media_id` (`media_id`),
  ADD KEY `added_by` (`added_by`),
  ADD KEY `idx_display_order` (`display_order`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `media_access_logs`
--
ALTER TABLE `media_access_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_media_id` (`media_id`),
  ADD KEY `idx_accessed_at` (`accessed_at`),
  ADD KEY `idx_access_type` (`access_type`);

--
-- Indexes for table `media_collections`
--
ALTER TABLE `media_collections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `collection_slug` (`collection_slug`),
  ADD KEY `cover_media_id` (`cover_media_id`),
  ADD KEY `idx_collection_slug` (`collection_slug`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `media_statistics_daily`
--
ALTER TABLE `media_statistics_daily`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_media_date` (`media_id`,`stat_date`),
  ADD KEY `idx_stat_date` (`stat_date`);

--
-- Indexes for table `media_tags`
--
ALTER TABLE `media_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tag_name` (`tag_name`),
  ADD UNIQUE KEY `tag_slug` (`tag_slug`),
  ADD KEY `idx_tag_slug` (`tag_slug`);

--
-- Indexes for table `media_tag_relationships`
--
ALTER TABLE `media_tag_relationships`
  ADD PRIMARY KEY (`media_id`,`tag_id`),
  ADD KEY `added_by` (`added_by`),
  ADD KEY `idx_media_id` (`media_id`),
  ADD KEY `idx_tag_id` (`tag_id`);

--
-- Indexes for table `media_usage`
--
ALTER TABLE `media_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `added_by` (`added_by`),
  ADD KEY `idx_media_id` (`media_id`),
  ADD KEY `idx_page` (`page_type`,`page_id`),
  ADD KEY `idx_usage_type` (`usage_type`);

--
-- Indexes for table `media_versions`
--
ALTER TABLE `media_versions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_media_version` (`media_id`,`version_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_media_id` (`media_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `message_replies`
--
ALTER TABLE `message_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_message_id` (`message_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_contacts`
--
ALTER TABLE `admin_contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admin_donations`
--
ALTER TABLE `admin_donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `admin_events`
--
ALTER TABLE `admin_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `admin_media`
--
ALTER TABLE `admin_media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `admin_reports`
--
ALTER TABLE `admin_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `admin_settings`
--
ALTER TABLE `admin_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `admin_team`
--
ALTER TABLE `admin_team`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media_access_logs`
--
ALTER TABLE `media_access_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media_collections`
--
ALTER TABLE `media_collections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `media_statistics_daily`
--
ALTER TABLE `media_statistics_daily`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `media_tags`
--
ALTER TABLE `media_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `media_usage`
--
ALTER TABLE `media_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `media_versions`
--
ALTER TABLE `media_versions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message_replies`
--
ALTER TABLE `message_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_contacts`
--
ALTER TABLE `admin_contacts`
  ADD CONSTRAINT `admin_contacts_ibfk_1` FOREIGN KEY (`replied_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `admin_donations`
--
ALTER TABLE `admin_donations`
  ADD CONSTRAINT `admin_donations_ibfk_1` FOREIGN KEY (`confirmed_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `admin_events`
--
ALTER TABLE `admin_events`
  ADD CONSTRAINT `admin_events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `admin_media`
--
ALTER TABLE `admin_media`
  ADD CONSTRAINT `admin_media_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_reports`
--
ALTER TABLE `admin_reports`
  ADD CONSTRAINT `admin_reports_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `collection_media_relationships`
--
ALTER TABLE `collection_media_relationships`
  ADD CONSTRAINT `collection_media_relationships_ibfk_1` FOREIGN KEY (`collection_id`) REFERENCES `media_collections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `collection_media_relationships_ibfk_2` FOREIGN KEY (`media_id`) REFERENCES `admin_media` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `collection_media_relationships_ibfk_3` FOREIGN KEY (`added_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `media_access_logs`
--
ALTER TABLE `media_access_logs`
  ADD CONSTRAINT `media_access_logs_ibfk_1` FOREIGN KEY (`media_id`) REFERENCES `admin_media` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `media_access_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `media_collections`
--
ALTER TABLE `media_collections`
  ADD CONSTRAINT `media_collections_ibfk_1` FOREIGN KEY (`cover_media_id`) REFERENCES `admin_media` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `media_collections_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `media_statistics_daily`
--
ALTER TABLE `media_statistics_daily`
  ADD CONSTRAINT `media_statistics_daily_ibfk_1` FOREIGN KEY (`media_id`) REFERENCES `admin_media` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `media_tag_relationships`
--
ALTER TABLE `media_tag_relationships`
  ADD CONSTRAINT `media_tag_relationships_ibfk_1` FOREIGN KEY (`media_id`) REFERENCES `admin_media` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `media_tag_relationships_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `media_tags` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `media_tag_relationships_ibfk_3` FOREIGN KEY (`added_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `media_usage`
--
ALTER TABLE `media_usage`
  ADD CONSTRAINT `media_usage_ibfk_1` FOREIGN KEY (`media_id`) REFERENCES `admin_media` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `media_usage_ibfk_2` FOREIGN KEY (`added_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `media_versions`
--
ALTER TABLE `media_versions`
  ADD CONSTRAINT `media_versions_ibfk_1` FOREIGN KEY (`media_id`) REFERENCES `admin_media` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `media_versions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `message_replies`
--
ALTER TABLE `message_replies`
  ADD CONSTRAINT `message_replies_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `contact_messages` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
