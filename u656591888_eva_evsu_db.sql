-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 26, 2026 at 02:23 PM
-- Server version: 11.8.6-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u656591888_eva_evsu_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email_type` varchar(50) NOT NULL,
  `recipient_email` varchar(100) NOT NULL,
  `status` enum('sent','failed','pending') DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_tokens`
--

CREATE TABLE `email_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `token_type` enum('verification','password_reset','account_activation') NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offices`
--

CREATE TABLE `offices` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive','archived') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offices`
--

INSERT INTO `offices` (`id`, `name`, `description`, `created_at`, `status`) VALUES
(20, 'Registrar\'s Office', 'Office of the University Registrar - Student Records, TOR, Certifications, and Enrollment', '2026-03-11 05:12:36', 'active'),
(20, 'Registrar\'s Office', 'Office of the University Registrar - Student Records, TOR, Certifications, and Enrollment', '2026-03-11 05:12:36', 'active'),
(20, 'Registrar\'s Office', 'Office of the University Registrar - Student Records, TOR, Certifications, and Enrollment', '2026-05-16 16:44:45', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `offices_backup`
--

CREATE TABLE `offices_backup` (
  `id` int(11) NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive','archived') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `offices_backup`
--

INSERT INTO `offices_backup` (`id`, `name`, `description`, `created_at`, `status`) VALUES
(12, 'IT Office', 'IT', '2025-12-17 11:45:28', 'inactive'),
(13, 'Library', 'books', '2025-12-17 12:04:54', 'inactive'),
(15, 'Supply Office', 'wara', '2025-12-21 16:45:39', 'inactive'),
(18, 'Registrar\'s Office', 'Office of the University Registrar - Student Records, TOR, Certifications, and Enrollment', '2026-03-11 03:57:53', 'inactive'),
(20, 'Registrar\'s Office', 'Office of the University Registrar - Student Records, TOR, Certifications, and Enrollment', '2026-03-11 05:12:36', 'active'),
(12, 'IT Office', 'IT', '2025-12-17 11:45:28', 'inactive'),
(13, 'Library', 'books', '2025-12-17 12:04:54', 'inactive'),
(15, 'Supply Office', 'wara', '2025-12-21 16:45:39', 'inactive'),
(18, 'Registrar\'s Office', 'Office of the University Registrar - Student Records, TOR, Certifications, and Enrollment', '2026-03-11 03:57:53', 'inactive'),
(20, 'Registrar\'s Office', 'Office of the University Registrar - Student Records, TOR, Certifications, and Enrollment', '2026-03-11 05:12:36', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_requests`
--

CREATE TABLE `password_reset_requests` (
  `id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `state_token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_requests`
--

INSERT INTO `password_reset_requests` (`id`, `token`, `email`, `user_id`, `state_token`, `expires_at`, `used`, `created_at`) VALUES
(1, '3415f425f1d720cde6f111bd0abac1f23662f8d4be1cf3d3160c517c88aaeab6', 'kenrepollo30@gmail.com', 4, '01f9871398350b3cfc2afbbafcc9d58d', '2025-12-17 22:04:47', 1, '2025-12-17 13:54:47'),
(2, '538c999c0db1f9dff73642b505b7a4c05989dd3699f453dec1f6caaa2d90cf13', 'kenrepollo30@gmail.com', 4, '1a66b43f0b55523186389f0c40b695ac', '2025-12-17 22:11:07', 1, '2025-12-17 14:01:07'),
(3, 'bed63996f8334207610241c6572224bda826b9a3166527a0184d232c63b90ba5', 'kenrepollo30@gmail.com', 4, 'b006cd2edf84b3468301bea4a5fe2eaf', '2025-12-17 22:16:51', 1, '2025-12-17 14:06:51'),
(4, '0721f39d032493cb717f873ccaf0cff1ca90b8758c6b09f00408a0768c44063d', 'kenrepollo30@gmail.com', 4, 'b0296acb0d65c3ba7debca363fe56963', '2025-12-17 22:25:40', 1, '2025-12-17 14:15:40'),
(5, '7df3d7f6233d32e5138386ec20d25852175854fccfe34306db24441e32d15aff', 'kenrepollo30@gmail.com', 4, '5ba7c6ea0f6a9a8b221ae5c8e3902797', '2025-12-17 22:26:32', 1, '2025-12-17 14:16:32'),
(6, '59a83d8ce787d42e29b0d16bc3c8eb9c19ca3ab67e6bd192f3cac0798d7a171c', 'kenrepollo30@gmail.com', 4, '78269100d3e3fba322f9b0aaedb3dd2d', '2025-12-17 22:33:20', 1, '2025-12-17 14:23:20'),
(7, 'b16b310c41ab23eb13a560ae16df392b908bc4a69b4ebd1df3e7dba5d4111438', 'ken@gmail.com', 19, '2da752e7968abd2cb87fc110d0de8aec', '2026-03-11 10:59:49', 0, '2026-03-11 02:49:49'),
(1, '3415f425f1d720cde6f111bd0abac1f23662f8d4be1cf3d3160c517c88aaeab6', 'kenrepollo30@gmail.com', 4, '01f9871398350b3cfc2afbbafcc9d58d', '2025-12-17 22:04:47', 1, '2025-12-17 13:54:47'),
(2, '538c999c0db1f9dff73642b505b7a4c05989dd3699f453dec1f6caaa2d90cf13', 'kenrepollo30@gmail.com', 4, '1a66b43f0b55523186389f0c40b695ac', '2025-12-17 22:11:07', 1, '2025-12-17 14:01:07'),
(3, 'bed63996f8334207610241c6572224bda826b9a3166527a0184d232c63b90ba5', 'kenrepollo30@gmail.com', 4, 'b006cd2edf84b3468301bea4a5fe2eaf', '2025-12-17 22:16:51', 1, '2025-12-17 14:06:51'),
(4, '0721f39d032493cb717f873ccaf0cff1ca90b8758c6b09f00408a0768c44063d', 'kenrepollo30@gmail.com', 4, 'b0296acb0d65c3ba7debca363fe56963', '2025-12-17 22:25:40', 1, '2025-12-17 14:15:40'),
(5, '7df3d7f6233d32e5138386ec20d25852175854fccfe34306db24441e32d15aff', 'kenrepollo30@gmail.com', 4, '5ba7c6ea0f6a9a8b221ae5c8e3902797', '2025-12-17 22:26:32', 1, '2025-12-17 14:16:32'),
(6, '59a83d8ce787d42e29b0d16bc3c8eb9c19ca3ab67e6bd192f3cac0798d7a171c', 'kenrepollo30@gmail.com', 4, '78269100d3e3fba322f9b0aaedb3dd2d', '2025-12-17 22:33:20', 1, '2025-12-17 14:23:20'),
(7, 'b16b310c41ab23eb13a560ae16df392b908bc4a69b4ebd1df3e7dba5d4111438', 'ken@gmail.com', 19, '2da752e7968abd2cb87fc110d0de8aec', '2026-03-11 10:59:49', 0, '2026-03-11 02:49:49');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `report_text` text NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `office_id`, `user_id`, `report_text`, `submitted_at`, `created_at`) VALUES
(2, 13, 18, 'ohh', '2025-12-23 07:21:14', '2025-12-23 07:21:14'),
(3, 13, 19, 'wewewewew', '2025-12-29 14:59:48', '2025-12-29 14:59:48'),
(5, 15, 19, 'wqwqw', '2025-12-29 16:56:41', '2025-12-29 16:56:41'),
(6, 20, 20, 'nice', '2026-04-29 17:12:15', '2026-04-29 17:12:15'),
(2, 13, 18, 'ohh', '2025-12-23 07:21:14', '2025-12-23 07:21:14'),
(3, 13, 19, 'wewewewew', '2025-12-29 14:59:48', '2025-12-29 14:59:48'),
(5, 15, 19, 'wqwqw', '2025-12-29 16:56:41', '2025-12-29 16:56:41'),
(6, 20, 20, 'nice', '2026-04-29 17:12:15', '2026-04-29 17:12:15'),
(0, 20, 0, 'xfdsfsfa f', '2026-05-14 17:51:30', '2026-05-14 17:51:30'),
(0, 20, 8, 'gjfk', '2026-05-14 18:21:20', '2026-05-14 18:21:20'),
(0, 20, 7, 'sdvrf', '2026-05-14 18:33:30', '2026-05-14 18:33:30'),
(0, 20, 6, 'wawww', '2026-05-15 18:38:13', '2026-05-15 18:38:13'),
(0, 20, 12, 'd daw mo tan aw si aira sa graduation ser masakitan daw sya', '2026-05-26 02:27:32', '2026-05-26 02:27:32'),
(0, 20, 14, 'Okay', '2026-05-26 02:43:45', '2026-05-26 02:43:45');

-- --------------------------------------------------------

--
-- Table structure for table `responses`
--

CREATE TABLE `responses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `service_type_id` int(11) DEFAULT NULL,
  `question_text` text NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `answer` text DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `submission_date` date DEFAULT curdate(),
  `service_type` enum('TOR','Certification','Enrollment','Diploma','Authentication') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `responses`
--

INSERT INTO `responses` (`id`, `user_id`, `office_id`, `service_type_id`, `question_text`, `rating`, `submitted_at`, `answer`, `comments`, `submission_date`, `service_type`) VALUES
(0, 10, 20, 5, 'How satisfied are you with the processing time?', 5, '2026-05-26 10:00:00', 'Very satisfied!', NULL, '2026-05-26', NULL),
(0, 10, 20, 5, 'How helpful and courteous were the staff?', 4, '2026-05-26 10:00:01', '', NULL, '2026-05-26', NULL),
(0, 10, 20, 5, 'How clear were the instructions?', 5, '2026-05-26 10:00:02', '', NULL, '2026-05-26', NULL),
(0, 10, 20, 5, 'How accurate was your document?', 5, '2026-05-26 10:00:03', '', NULL, '2026-05-26', NULL),
(0, 10, 20, 5, 'How convenient was the process?', 4, '2026-05-26 10:00:04', '', NULL, '2026-05-26', NULL),
(0, 10, 20, 5, 'How long did you wait?', 3, '2026-05-26 10:00:05', '', NULL, '2026-05-26', NULL),
(0, 10, 20, 5, 'How organized was the service?', 5, '2026-05-26 10:00:06', '', NULL, '2026-05-26', NULL),
(0, 10, 20, 5, 'How likely to recommend?', 5, '2026-05-26 10:00:07', '', NULL, '2026-05-26', NULL),
(0, 10, 20, 5, 'How would you rate cleanliness?', 4, '2026-05-26 10:00:08', '', NULL, '2026-05-26', NULL),
(0, 10, 20, 5, 'Overall satisfaction?', 5, '2026-05-26 10:00:09', '', NULL, '2026-05-26', NULL),
(0, 14, 20, 4, 'How satisfied are you with the processing time?', 4, '2026-05-26 11:00:00', 'Okay naman', NULL, '2026-05-26', NULL),
(0, 14, 20, 4, 'How helpful and courteous were the staff?', 5, '2026-05-26 11:00:01', '', NULL, '2026-05-26', NULL),
(0, 14, 20, 4, 'How clear were the instructions?', 4, '2026-05-26 11:00:02', '', NULL, '2026-05-26', NULL),
(0, 14, 20, 4, 'How accurate was your document?', 5, '2026-05-26 11:00:03', '', NULL, '2026-05-26', NULL),
(0, 14, 20, 4, 'How convenient was the process?', 4, '2026-05-26 11:00:04', '', NULL, '2026-05-26', NULL),
(0, 14, 20, 4, 'How long did you wait?', 3, '2026-05-26 11:00:05', '', NULL, '2026-05-26', NULL),
(0, 14, 20, 4, 'How organized was the service?', 4, '2026-05-26 11:00:06', '', NULL, '2026-05-26', NULL),
(0, 14, 20, 4, 'How likely to recommend?', 5, '2026-05-26 11:00:07', '', NULL, '2026-05-26', NULL),
(0, 14, 20, 4, 'How would you rate cleanliness?', 4, '2026-05-26 11:00:08', '', NULL, '2026-05-26', NULL),
(0, 14, 20, 4, 'Overall satisfaction?', 4, '2026-05-26 11:00:09', '', NULL, '2026-05-26', NULL),
(0, 13, 20, 1, 'How satisfied are you with the processing time?', 5, '2026-05-26 12:00:00', 'Mabilis ang service', NULL, '2026-05-26', NULL),
(0, 13, 20, 1, 'How helpful and courteous were the staff?', 5, '2026-05-26 12:00:01', '', NULL, '2026-05-26', NULL),
(0, 13, 20, 1, 'How clear were the instructions?', 4, '2026-05-26 12:00:02', '', NULL, '2026-05-26', NULL),
(0, 13, 20, 1, 'How accurate was your document?', 5, '2026-05-26 12:00:03', '', NULL, '2026-05-26', NULL),
(0, 13, 20, 1, 'How convenient was the process?', 4, '2026-05-26 12:00:04', '', NULL, '2026-05-26', NULL),
(0, 13, 20, 1, 'How long did you wait?', 3, '2026-05-26 12:00:05', '', NULL, '2026-05-26', NULL),
(0, 13, 20, 1, 'How organized was the service?', 5, '2026-05-26 12:00:06', '', NULL, '2026-05-26', NULL),
(0, 13, 20, 1, 'How likely to recommend?', 5, '2026-05-26 12:00:07', '', NULL, '2026-05-26', NULL),
(0, 13, 20, 1, 'How would you rate cleanliness?', 4, '2026-05-26 12:00:08', '', NULL, '2026-05-26', NULL),
(0, 13, 20, 1, 'Overall satisfaction?', 5, '2026-05-26 12:00:09', '', NULL, '2026-05-26', NULL),
(0, 12, 20, 3, 'How satisfied are you with the processing time?', 4, '2026-05-26 13:00:00', 'Smooth process', NULL, '2026-05-26', NULL),
(0, 12, 20, 3, 'How helpful and courteous were the staff?', 4, '2026-05-26 13:00:01', '', NULL, '2026-05-26', NULL),
(0, 12, 20, 3, 'How clear were the instructions?', 5, '2026-05-26 13:00:02', '', NULL, '2026-05-26', NULL),
(0, 12, 20, 3, 'Were your subjects encoded correctly?', 5, '2026-05-26 13:00:03', '', NULL, '2026-05-26', NULL),
(0, 12, 20, 3, 'How convenient was the process?', 4, '2026-05-26 13:00:04', '', NULL, '2026-05-26', NULL),
(0, 12, 20, 3, 'How long did the enrollment take?', 3, '2026-05-26 13:00:05', '', NULL, '2026-05-26', NULL),
(0, 12, 20, 3, 'How organized was the service?', 4, '2026-05-26 13:00:06', '', NULL, '2026-05-26', NULL),
(0, 12, 20, 3, 'How likely to recommend?', 4, '2026-05-26 13:00:07', '', NULL, '2026-05-26', NULL),
(0, 12, 20, 3, 'How would you rate cleanliness?', 4, '2026-05-26 13:00:08', '', NULL, '2026-05-26', NULL),
(0, 12, 20, 3, 'Overall satisfaction?', 4, '2026-05-26 13:00:09', '', NULL, '2026-05-26', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `responses_backup`
--

CREATE TABLE `responses_backup` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `service_type_id` int(11) DEFAULT NULL,
  `question_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `rating` int(11) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `answer` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `submission_date` date DEFAULT curdate(),
  `service_type` enum('TOR','Certification','Enrollment','Diploma','Authentication') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `responses_backup`
--

INSERT INTO `responses_backup` (`id`, `user_id`, `office_id`, `service_type_id`, `question_text`, `rating`, `submitted_at`, `answer`, `submission_date`, `service_type`) VALUES
(32, 18, 13, NULL, 'approve baka sa patakaran?', 5, '2025-12-23 07:21:14', NULL, '2025-12-23', NULL),
(33, 19, 13, NULL, 'approve baka sa patakaran?', 4, '2025-12-29 14:59:48', NULL, '2025-12-29', NULL),
(34, 19, 12, NULL, '\"Tech support speed\"', 5, '2025-12-29 16:55:09', NULL, '2025-12-30', NULL),
(36, 19, 15, NULL, 'asasa', 5, '2025-12-29 16:56:41', NULL, '2025-12-30', NULL),
(37, 20, 20, NULL, 'How satisfied are you with the processing time for your request?', 4, '2026-03-11 06:50:57', '', '2026-03-11', NULL),
(38, 20, 20, NULL, 'How helpful and courteous were the Registrar staff?', 4, '2026-03-11 06:50:57', '', '2026-03-11', NULL),
(39, 20, 20, NULL, 'How clear were the instructions and requirements provided?', 4, '2026-03-11 06:50:57', '', '2026-03-11', NULL),
(40, 20, 20, NULL, 'How would you rate the accuracy of your document/transaction?', 4, '2026-03-11 06:50:57', '', '2026-03-11', NULL),
(41, 20, 20, NULL, 'How convenient was the overall process?', 4, '2026-03-11 06:50:57', '', '2026-03-11', NULL),
(42, 20, 20, NULL, 'How would you rate the waiting time?', 3, '2026-03-11 06:50:57', '', '2026-03-11', NULL),
(43, 20, 20, NULL, 'How organized is the Registrar\'s Office?', 5, '2026-03-11 06:50:57', '', '2026-03-11', NULL),
(44, 20, 20, NULL, 'How likely are you to recommend our services to others?', 5, '2026-03-11 06:50:57', '', '2026-03-11', NULL),
(45, 20, 20, NULL, 'How would you rate the cleanliness of the office?', 5, '2026-03-11 06:50:57', '', '2026-03-11', NULL),
(46, 20, 20, NULL, 'Overall, how satisfied are you with the Registrar\'s Office?', 5, '2026-03-11 06:50:57', '', '2026-03-11', NULL),
(47, 21, 20, NULL, 'How satisfied are you with the processing time for your request?', 5, '2026-04-29 02:58:34', '', '2026-04-29', NULL),
(48, 21, 20, NULL, 'How helpful and courteous were the Registrar staff?', 5, '2026-04-29 02:58:34', '', '2026-04-29', NULL),
(49, 21, 20, NULL, 'How clear were the instructions and requirements provided?', 5, '2026-04-29 02:58:34', '', '2026-04-29', NULL),
(50, 21, 20, NULL, 'How would you rate the accuracy of your document/transaction?', 5, '2026-04-29 02:58:34', '', '2026-04-29', NULL),
(51, 21, 20, NULL, 'How convenient was the overall process?', 4, '2026-04-29 02:58:34', '', '2026-04-29', NULL),
(52, 21, 20, NULL, 'How would you rate the waiting time?', 4, '2026-04-29 02:58:34', '', '2026-04-29', NULL),
(53, 21, 20, NULL, 'How organized is the Registrar\'s Office?', 4, '2026-04-29 02:58:34', '', '2026-04-29', NULL),
(54, 21, 20, NULL, 'How likely are you to recommend our services to others?', 4, '2026-04-29 02:58:34', '', '2026-04-29', NULL),
(55, 21, 20, NULL, 'How would you rate the cleanliness of the office?', 4, '2026-04-29 02:58:34', '', '2026-04-29', NULL),
(56, 21, 20, NULL, 'Overall, how satisfied are you with the Registrar\'s Office?', 5, '2026-04-29 02:58:34', '', '2026-04-29', NULL),
(57, 20, 20, 5, 'How satisfied are you with the processing time for your request?', 4, '2026-04-29 17:12:14', '', '2026-04-30', NULL),
(58, 20, 20, 5, 'How helpful and courteous were the Registrar staff?', 4, '2026-04-29 17:12:14', '', '2026-04-30', NULL),
(59, 20, 20, 5, 'How clear were the instructions and requirements provided?', 4, '2026-04-29 17:12:14', '', '2026-04-30', NULL),
(60, 20, 20, 5, 'How would you rate the accuracy of your document/transaction?', 4, '2026-04-29 17:12:14', '', '2026-04-30', NULL),
(61, 20, 20, 5, 'How convenient was the overall process?', 4, '2026-04-29 17:12:14', '', '2026-04-30', NULL),
(62, 20, 20, 5, 'How would you rate the waiting time?', 4, '2026-04-29 17:12:14', '', '2026-04-30', NULL),
(63, 20, 20, 5, 'How organized is the Registrar\'s Office?', 4, '2026-04-29 17:12:14', '', '2026-04-30', NULL),
(64, 20, 20, 5, 'How likely are you to recommend our services to others?', 4, '2026-04-29 17:12:14', '', '2026-04-30', NULL),
(65, 20, 20, 5, 'How would you rate the cleanliness of the office?', 4, '2026-04-29 17:12:15', '', '2026-04-30', NULL),
(66, 20, 20, 5, 'Overall, how satisfied are you with the Registrar\'s Office?', 4, '2026-04-29 17:12:15', '', '2026-04-30', NULL),
(32, 18, 13, NULL, 'approve baka sa patakaran?', 5, '2025-12-23 07:21:14', NULL, '2025-12-23', NULL),
(33, 19, 13, NULL, 'approve baka sa patakaran?', 4, '2025-12-29 14:59:48', NULL, '2025-12-29', NULL),
(34, 19, 12, NULL, '\"Tech support speed\"', 5, '2025-12-29 16:55:09', NULL, '2025-12-30', NULL),
(36, 19, 15, NULL, 'asasa', 5, '2025-12-29 16:56:41', NULL, '2025-12-30', NULL),
(37, 20, 20, NULL, 'How satisfied are you with the processing time for your request?', 4, '2026-03-11 06:50:57', '', '2026-03-11', NULL),
(38, 20, 20, NULL, 'How helpful and courteous were the Registrar staff?', 4, '2026-03-11 06:50:57', '', '2026-03-11', NULL),
(39, 20, 20, NULL, 'How clear were the instructions and requirements provided?', 4, '2026-03-11 06:50:57', '', '2026-03-11', NULL),
(40, 20, 20, NULL, 'How would you rate the accuracy of your document/transaction?', 4, '2026-03-11 06:50:57', '', '2026-03-11', NULL),
(41, 20, 20, NULL, 'How convenient was the overall process?', 4, '2026-03-11 06:50:57', '', '2026-03-11', NULL),
(42, 20, 20, NULL, 'How would you rate the waiting time?', 3, '2026-03-11 06:50:57', '', '2026-03-11', NULL),
(43, 20, 20, NULL, 'How organized is the Registrar\'s Office?', 5, '2026-03-11 06:50:57', '', '2026-03-11', NULL),
(44, 20, 20, NULL, 'How likely are you to recommend our services to others?', 5, '2026-03-11 06:50:57', '', '2026-03-11', NULL),
(45, 20, 20, NULL, 'How would you rate the cleanliness of the office?', 5, '2026-03-11 06:50:57', '', '2026-03-11', NULL),
(46, 20, 20, NULL, 'Overall, how satisfied are you with the Registrar\'s Office?', 5, '2026-03-11 06:50:57', '', '2026-03-11', NULL),
(47, 21, 20, NULL, 'How satisfied are you with the processing time for your request?', 5, '2026-04-29 02:58:34', '', '2026-04-29', NULL),
(48, 21, 20, NULL, 'How helpful and courteous were the Registrar staff?', 5, '2026-04-29 02:58:34', '', '2026-04-29', NULL),
(49, 21, 20, NULL, 'How clear were the instructions and requirements provided?', 5, '2026-04-29 02:58:34', '', '2026-04-29', NULL),
(50, 21, 20, NULL, 'How would you rate the accuracy of your document/transaction?', 5, '2026-04-29 02:58:34', '', '2026-04-29', NULL),
(51, 21, 20, NULL, 'How convenient was the overall process?', 4, '2026-04-29 02:58:34', '', '2026-04-29', NULL),
(52, 21, 20, NULL, 'How would you rate the waiting time?', 4, '2026-04-29 02:58:34', '', '2026-04-29', NULL),
(53, 21, 20, NULL, 'How organized is the Registrar\'s Office?', 4, '2026-04-29 02:58:34', '', '2026-04-29', NULL),
(54, 21, 20, NULL, 'How likely are you to recommend our services to others?', 4, '2026-04-29 02:58:34', '', '2026-04-29', NULL),
(55, 21, 20, NULL, 'How would you rate the cleanliness of the office?', 4, '2026-04-29 02:58:34', '', '2026-04-29', NULL),
(56, 21, 20, NULL, 'Overall, how satisfied are you with the Registrar\'s Office?', 5, '2026-04-29 02:58:34', '', '2026-04-29', NULL),
(57, 20, 20, 5, 'How satisfied are you with the processing time for your request?', 4, '2026-04-29 17:12:14', '', '2026-04-30', NULL),
(58, 20, 20, 5, 'How helpful and courteous were the Registrar staff?', 4, '2026-04-29 17:12:14', '', '2026-04-30', NULL),
(59, 20, 20, 5, 'How clear were the instructions and requirements provided?', 4, '2026-04-29 17:12:14', '', '2026-04-30', NULL),
(60, 20, 20, 5, 'How would you rate the accuracy of your document/transaction?', 4, '2026-04-29 17:12:14', '', '2026-04-30', NULL),
(61, 20, 20, 5, 'How convenient was the overall process?', 4, '2026-04-29 17:12:14', '', '2026-04-30', NULL),
(62, 20, 20, 5, 'How would you rate the waiting time?', 4, '2026-04-29 17:12:14', '', '2026-04-30', NULL),
(63, 20, 20, 5, 'How organized is the Registrar\'s Office?', 4, '2026-04-29 17:12:14', '', '2026-04-30', NULL),
(64, 20, 20, 5, 'How likely are you to recommend our services to others?', 4, '2026-04-29 17:12:14', '', '2026-04-30', NULL),
(65, 20, 20, 5, 'How would you rate the cleanliness of the office?', 4, '2026-04-29 17:12:15', '', '2026-04-30', NULL),
(66, 20, 20, 5, 'Overall, how satisfied are you with the Registrar\'s Office?', 4, '2026-04-29 17:12:15', '', '2026-04-30', NULL),
(0, 0, 20, 1, 'How satisfied are you with the processing time for your request?', 5, '2026-05-14 16:58:19', '', '2026-05-14', NULL),
(0, 0, 20, 1, 'How helpful and courteous were the Registrar staff?', 4, '2026-05-14 16:58:19', '', '2026-05-14', NULL),
(0, 0, 20, 1, 'How clear were the instructions and requirements provided?', 5, '2026-05-14 16:58:19', '', '2026-05-14', NULL),
(0, 0, 20, 1, 'How would you rate the accuracy of your document/transaction?', 5, '2026-05-14 16:58:19', '', '2026-05-14', NULL),
(0, 0, 20, 1, 'How convenient was the overall process?', 4, '2026-05-14 16:58:19', '', '2026-05-14', NULL),
(0, 0, 20, 1, 'How would you rate the waiting time?', 5, '2026-05-14 16:58:19', '', '2026-05-14', NULL),
(0, 0, 20, 1, 'How organized is the Registrar\'s Office?', 4, '2026-05-14 16:58:19', '', '2026-05-14', NULL),
(0, 0, 20, 1, 'How likely are you to recommend our services to others?', 4, '2026-05-14 16:58:19', '', '2026-05-14', NULL),
(0, 0, 20, 1, 'How would you rate the cleanliness of the office?', 2, '2026-05-14 16:58:19', '', '2026-05-14', NULL),
(0, 0, 20, 1, 'Overall, how satisfied are you with the Registrar\'s Office?', 3, '2026-05-14 16:58:19', '', '2026-05-14', NULL),
(0, 0, 20, 5, 'How satisfied are you with the processing time for your request?', 4, '2026-05-14 17:51:30', '', '2026-05-14', NULL),
(0, 0, 20, 5, 'How helpful and courteous were the Registrar staff?', 3, '2026-05-14 17:51:30', 'sdfsdfa cfsd', '2026-05-14', NULL),
(0, 0, 20, 5, 'How clear were the instructions and requirements provided?', 4, '2026-05-14 17:51:30', '', '2026-05-14', NULL),
(0, 0, 20, 5, 'How would you rate the accuracy of your document/transaction?', 3, '2026-05-14 17:51:30', '', '2026-05-14', NULL),
(0, 0, 20, 5, 'How convenient was the overall process?', 3, '2026-05-14 17:51:30', '', '2026-05-14', NULL),
(0, 0, 20, 5, 'How would you rate the waiting time?', 3, '2026-05-14 17:51:30', '', '2026-05-14', NULL),
(0, 0, 20, 5, 'How organized is the Registrar\'s Office?', 4, '2026-05-14 17:51:30', '', '2026-05-14', NULL),
(0, 0, 20, 5, 'How likely are you to recommend our services to others?', 5, '2026-05-14 17:51:30', '', '2026-05-14', NULL),
(0, 0, 20, 5, 'How would you rate the cleanliness of the office?', 4, '2026-05-14 17:51:30', '', '2026-05-14', NULL),
(0, 0, 20, 5, 'Overall, how satisfied are you with the Registrar\'s Office?', 4, '2026-05-14 17:51:30', 'sdfsdfds', '2026-05-14', NULL),
(0, 8, 20, 4, 'How satisfied are you with the processing time for your request?', 4, '2026-05-14 18:17:02', 'uguiyf', '2026-05-14', NULL),
(0, 8, 20, 4, 'How helpful and courteous were the Registrar staff?', 5, '2026-05-14 18:17:02', '', '2026-05-14', NULL),
(0, 8, 20, 4, 'How clear were the instructions and requirements provided?', 4, '2026-05-14 18:17:02', '', '2026-05-14', NULL),
(0, 8, 20, 4, 'How would you rate the accuracy of your document/transaction?', 3, '2026-05-14 18:17:02', '', '2026-05-14', NULL),
(0, 8, 20, 4, 'How convenient was the overall process?', 4, '2026-05-14 18:17:02', '', '2026-05-14', NULL),
(0, 8, 20, 4, 'How would you rate the waiting time?', 3, '2026-05-14 18:17:02', '', '2026-05-14', NULL),
(0, 8, 20, 4, 'How organized is the Registrar\'s Office?', 4, '2026-05-14 18:17:02', '', '2026-05-14', NULL),
(0, 8, 20, 4, 'How likely are you to recommend our services to others?', 3, '2026-05-14 18:17:02', '', '2026-05-14', NULL),
(0, 8, 20, 4, 'How would you rate the cleanliness of the office?', 4, '2026-05-14 18:17:02', '', '2026-05-14', NULL),
(0, 8, 20, 4, 'Overall, how satisfied are you with the Registrar\'s Office?', 1, '2026-05-14 18:17:02', '', '2026-05-14', NULL),
(0, 8, 20, 3, 'How satisfied are you with the processing time for your request?', 3, '2026-05-14 18:21:20', '', '2026-05-14', NULL),
(0, 8, 20, 3, 'How helpful and courteous were the Registrar staff?', 5, '2026-05-14 18:21:20', '', '2026-05-14', NULL),
(0, 8, 20, 3, 'How clear were the instructions and requirements provided?', 4, '2026-05-14 18:21:20', '', '2026-05-14', NULL),
(0, 8, 20, 3, 'How would you rate the accuracy of your document/transaction?', 4, '2026-05-14 18:21:20', '', '2026-05-14', NULL),
(0, 8, 20, 3, 'How convenient was the overall process?', 4, '2026-05-14 18:21:20', '', '2026-05-14', NULL),
(0, 8, 20, 3, 'How would you rate the waiting time?', 4, '2026-05-14 18:21:20', '', '2026-05-14', NULL),
(0, 8, 20, 3, 'How organized is the Registrar\'s Office?', 5, '2026-05-14 18:21:20', '', '2026-05-14', NULL),
(0, 8, 20, 3, 'How likely are you to recommend our services to others?', 4, '2026-05-14 18:21:20', '', '2026-05-14', NULL),
(0, 8, 20, 3, 'How would you rate the cleanliness of the office?', 4, '2026-05-14 18:21:20', '', '2026-05-14', NULL),
(0, 8, 20, 3, 'Overall, how satisfied are you with the Registrar\'s Office?', 4, '2026-05-14 18:21:20', '', '2026-05-14', NULL),
(0, 7, 20, 4, 'How satisfied are you with the processing time for your request?', 4, '2026-05-14 18:33:30', '', '2026-05-14', NULL),
(0, 7, 20, 4, 'How helpful and courteous were the Registrar staff?', 4, '2026-05-14 18:33:30', '', '2026-05-14', NULL),
(0, 7, 20, 4, 'How clear were the instructions and requirements provided?', 4, '2026-05-14 18:33:30', '', '2026-05-14', NULL),
(0, 7, 20, 4, 'How would you rate the accuracy of your document/transaction?', 4, '2026-05-14 18:33:30', '', '2026-05-14', NULL),
(0, 7, 20, 4, 'How convenient was the overall process?', 4, '2026-05-14 18:33:30', 'rtgrvd', '2026-05-14', NULL),
(0, 7, 20, 4, 'How would you rate the waiting time?', 4, '2026-05-14 18:33:30', 'wdwed', '2026-05-14', NULL),
(0, 7, 20, 4, 'How organized is the Registrar\'s Office?', 4, '2026-05-14 18:33:30', '', '2026-05-14', NULL),
(0, 7, 20, 4, 'How likely are you to recommend our services to others?', 4, '2026-05-14 18:33:30', '', '2026-05-14', NULL),
(0, 7, 20, 4, 'How would you rate the cleanliness of the office?', 4, '2026-05-14 18:33:30', '', '2026-05-14', NULL),
(0, 7, 20, 4, 'Overall, how satisfied are you with the Registrar\'s Office?', 4, '2026-05-14 18:33:30', '', '2026-05-14', NULL),
(0, 6, 20, 2, 'How satisfied are you with the processing time for your request?', 4, '2026-05-15 18:37:25', '', '2026-05-15', NULL),
(0, 6, 20, 2, 'How helpful and courteous were the Registrar staff?', 4, '2026-05-15 18:37:25', '', '2026-05-15', NULL),
(0, 6, 20, 2, 'How clear were the instructions and requirements provided?', 4, '2026-05-15 18:37:25', '', '2026-05-15', NULL),
(0, 6, 20, 2, 'How would you rate the accuracy of your document/transaction?', 3, '2026-05-15 18:37:25', '', '2026-05-15', NULL),
(0, 6, 20, 2, 'How convenient was the overall process?', 4, '2026-05-15 18:37:25', '', '2026-05-15', NULL),
(0, 6, 20, 2, 'How would you rate the waiting time?', 4, '2026-05-15 18:37:25', '', '2026-05-15', NULL),
(0, 6, 20, 2, 'How organized is the Registrar\'s Office?', 4, '2026-05-15 18:37:25', '', '2026-05-15', NULL),
(0, 6, 20, 2, 'How likely are you to recommend our services to others?', 4, '2026-05-15 18:37:25', '', '2026-05-15', NULL),
(0, 6, 20, 2, 'How would you rate the cleanliness of the office?', 4, '2026-05-15 18:37:25', '', '2026-05-15', NULL),
(0, 6, 20, 2, 'Overall, how satisfied are you with the Registrar\'s Office?', 4, '2026-05-15 18:37:25', '', '2026-05-15', NULL),
(0, 6, 20, 5, 'How satisfied are you with the processing time for your request?', 4, '2026-05-15 18:38:13', '', '2026-05-15', NULL),
(0, 6, 20, 5, 'How helpful and courteous were the Registrar staff?', 4, '2026-05-15 18:38:13', '', '2026-05-15', NULL),
(0, 6, 20, 5, 'How clear were the instructions and requirements provided?', 3, '2026-05-15 18:38:13', '', '2026-05-15', NULL),
(0, 6, 20, 5, 'How would you rate the accuracy of your document/transaction?', 4, '2026-05-15 18:38:13', '', '2026-05-15', NULL),
(0, 6, 20, 5, 'How convenient was the overall process?', 4, '2026-05-15 18:38:13', '', '2026-05-15', NULL),
(0, 6, 20, 5, 'How would you rate the waiting time?', 3, '2026-05-15 18:38:13', '', '2026-05-15', NULL),
(0, 6, 20, 5, 'How organized is the Registrar\'s Office?', 4, '2026-05-15 18:38:13', '', '2026-05-15', NULL),
(0, 6, 20, 5, 'How likely are you to recommend our services to others?', 4, '2026-05-15 18:38:13', '', '2026-05-15', NULL),
(0, 6, 20, 5, 'How would you rate the cleanliness of the office?', 4, '2026-05-15 18:38:13', '', '2026-05-15', NULL),
(0, 6, 20, 5, 'Overall, how satisfied are you with the Registrar\'s Office?', 4, '2026-05-15 18:38:13', '', '2026-05-15', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `schema_migrations`
--

CREATE TABLE `schema_migrations` (
  `migration` varchar(255) NOT NULL,
  `migrated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schema_migrations`
--

INSERT INTO `schema_migrations` (`migration`, `migrated_at`) VALUES
('evsu_evaluation.sql', '2026-05-14 16:14:00');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `office_id`, `name`, `description`, `is_active`, `created_at`) VALUES
(1, 1, 'Authentication', 'Authentication services', 1, '2026-05-14 17:34:30'),
(2, 1, 'Certification', 'Certification services', 1, '2026-05-14 17:34:30'),
(3, 1, 'Diploma', 'Diploma services', 1, '2026-05-14 17:34:30'),
(4, 1, 'Enrollment', 'Enrollment services', 1, '2026-05-14 17:34:30'),
(5, 1, 'TOR', 'Transcript of Records services', 1, '2026-05-14 17:34:30');

-- --------------------------------------------------------

--
-- Table structure for table `service_types`
--

CREATE TABLE `service_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_types`
--

INSERT INTO `service_types` (`id`, `name`, `description`, `is_active`) VALUES
(1, 'TOR', 'Transcript of Records services', 1),
(2, 'Certification', 'Certification services', 1),
(3, 'Enrollment', 'Enrollment services', 1),
(4, 'Diploma', 'Diploma services', 1),
(5, 'Authentication', 'Authentication services', 1);

-- --------------------------------------------------------

--
-- Table structure for table `survey_availability`
--

CREATE TABLE `survey_availability` (
  `id` int(11) NOT NULL,
  `service_type_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `survey_availability`
--

INSERT INTO `survey_availability` (`id`, `service_type_id`, `is_active`, `start_date`, `end_date`, `updated_by`, `updated_at`) VALUES
(1, 1, 1, '2026-05-15', '2026-05-30', 4, '2026-05-26 03:32:32'),
(2, 2, 1, '2026-05-15', '2026-05-29', 4, '2026-05-26 03:32:19'),
(3, 3, 1, '2026-05-15', '2027-05-29', 4, '2026-05-26 03:32:27'),
(4, 4, 1, '2026-05-15', '2026-05-29', 4, '2026-05-26 03:32:11'),
(5, 5, 0, '2026-05-14', '2026-05-27', 4, '2026-05-26 03:32:36');

-- --------------------------------------------------------

--
-- Table structure for table `survey_availability_backup`
--

CREATE TABLE `survey_availability_backup` (
  `id` int(11) NOT NULL,
  `service_type_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `survey_availability_backup`
--

INSERT INTO `survey_availability_backup` (`id`, `service_type_id`, `is_active`, `start_date`, `end_date`, `updated_by`, `updated_at`) VALUES
(0, NULL, 1, '2026-03-11', '2027-03-11', 4, '2026-05-14 18:30:20'),
(0, 1, 1, '2026-05-15', '2027-05-15', NULL, '2026-05-15 18:24:07'),
(0, 2, 1, '2026-05-15', '2027-05-15', NULL, '2026-05-15 18:24:07'),
(0, 3, 1, '2026-05-15', '2027-05-15', NULL, '2026-05-15 18:24:07'),
(0, 4, 1, '2026-05-15', '2027-05-15', NULL, '2026-05-15 18:24:07'),
(0, 5, 1, '2026-05-15', '2027-05-15', NULL, '2026-05-15 18:24:07');

-- --------------------------------------------------------

--
-- Table structure for table `survey_questions`
--

CREATE TABLE `survey_questions` (
  `id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `service_type_id` int(11) DEFAULT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('rating','text','yesno','multiple') DEFAULT 'rating',
  `category` varchar(50) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `survey_questions`
--

INSERT INTO `survey_questions` (`id`, `office_id`, `service_type_id`, `question_text`, `question_type`, `category`, `display_order`, `is_active`, `created_at`) VALUES
(161, 20, 1, 'How satisfied are you with the processing time for TOR request?', 'rating', 'service', 1, 0, '2026-05-17 15:08:08'),
(162, 20, 1, 'How helpful and courteous were the staff for TOR?', 'rating', 'staff', 2, 0, '2026-05-17 15:08:08'),
(163, 20, 1, 'How clear were the instructions for TOR requirements?', 'rating', 'information', 3, 0, '2026-05-17 15:08:08'),
(164, 20, 1, 'How accurate was your TOR document?', 'rating', 'quality', 4, 0, '2026-05-17 15:08:08'),
(165, 20, 1, 'How convenient was the TOR request process?', 'rating', 'convenience', 5, 1, '2026-05-17 15:08:08'),
(166, 20, 1, 'How long did you wait for your TOR?', 'rating', 'waiting', 6, 0, '2026-05-17 15:08:08'),
(167, 20, 1, 'How organized is the TOR processing?', 'rating', 'organization', 7, 1, '2026-05-17 15:08:08'),
(168, 20, 1, 'How likely are you to recommend TOR service?', 'rating', 'recommendation', 8, 1, '2026-05-17 15:08:08'),
(169, 20, 1, 'How would you rate the office cleanliness?', 'rating', 'facility', 9, 1, '2026-05-17 15:08:08'),
(170, 20, 1, 'Overall satisfaction with TOR service?', 'rating', 'overall', 10, 1, '2026-05-17 15:08:08'),
(171, 20, 2, 'How satisfied are you with the processing time for Certification?', 'rating', 'service', 1, 1, '2026-05-17 15:08:08'),
(172, 20, 2, 'How helpful and courteous were the staff for Certification?', 'rating', 'staff', 2, 1, '2026-05-17 15:08:08'),
(173, 20, 2, 'How clear were the instructions for Certification requirements?', 'rating', 'information', 3, 1, '2026-05-17 15:08:08'),
(174, 20, 2, 'How accurate was your Certification document?', 'rating', 'quality', 4, 1, '2026-05-17 15:08:08'),
(175, 20, 2, 'How convenient was the Certification request process?', 'rating', 'convenience', 5, 1, '2026-05-17 15:08:08'),
(176, 20, 2, 'How long did you wait for your Certification?', 'rating', 'waiting', 6, 1, '2026-05-17 15:08:08'),
(177, 20, 2, 'How organized is the Certification processing?', 'rating', 'organization', 7, 1, '2026-05-17 15:08:08'),
(178, 20, 2, 'How likely are you to recommend Certification service?', 'rating', 'recommendation', 8, 1, '2026-05-17 15:08:08'),
(179, 20, 2, 'How would you rate the office cleanliness?', 'rating', 'facility', 9, 1, '2026-05-17 15:08:08'),
(180, 20, 2, 'Overall satisfaction with Certification service?', 'rating', 'overall', 10, 1, '2026-05-17 15:08:08'),
(181, 20, 3, 'How satisfied are you with the processing time for Enrollment?', 'rating', 'service', 1, 1, '2026-05-17 15:08:08'),
(182, 20, 3, 'How helpful and courteous were the staff for Enrollment?', 'rating', 'staff', 2, 1, '2026-05-17 15:08:08'),
(183, 20, 3, 'How clear were the instructions for Enrollment requirements?', 'rating', 'information', 3, 1, '2026-05-17 15:08:08'),
(184, 20, 3, 'Were your subjects encoded correctly?', 'rating', 'quality', 4, 1, '2026-05-17 15:08:08'),
(185, 20, 3, 'How convenient was the Enrollment process?', 'rating', 'convenience', 5, 1, '2026-05-17 15:08:08'),
(186, 20, 3, 'How long did the enrollment take?', 'rating', 'waiting', 6, 1, '2026-05-17 15:08:08'),
(187, 20, 3, 'How organized is the Enrollment processing?', 'rating', 'organization', 7, 1, '2026-05-17 15:08:08'),
(188, 20, 3, 'How likely are you to recommend Enrollment service?', 'rating', 'recommendation', 8, 1, '2026-05-17 15:08:08'),
(189, 20, 3, 'How would you rate the office cleanliness?', 'rating', 'facility', 9, 1, '2026-05-17 15:08:08'),
(190, 20, 3, 'Overall satisfaction with Enrollment service?', 'rating', 'overall', 10, 1, '2026-05-17 15:08:08'),
(191, 20, 4, 'How satisfied are you with the processing time for Diploma?', 'rating', 'service', 1, 1, '2026-05-17 15:08:08'),
(192, 20, 4, 'How helpful and courteous were the staff for Diploma?', 'rating', 'staff', 2, 1, '2026-05-17 15:08:08'),
(193, 20, 4, 'How clear were the instructions for Diploma requirements?', 'rating', 'information', 3, 1, '2026-05-17 15:08:08'),
(194, 20, 4, 'How accurate was your Diploma document?', 'rating', 'quality', 4, 1, '2026-05-17 15:08:08'),
(195, 20, 4, 'How convenient was the Diploma request process?', 'rating', 'convenience', 5, 1, '2026-05-17 15:08:08'),
(196, 20, 4, 'How long did you wait for your Diploma?', 'rating', 'waiting', 6, 1, '2026-05-17 15:08:08'),
(197, 20, 4, 'How organized is the Diploma processing?', 'rating', 'organization', 7, 1, '2026-05-17 15:08:08'),
(198, 20, 4, 'How likely are you to recommend Diploma service?', 'rating', 'recommendation', 8, 1, '2026-05-17 15:08:08'),
(199, 20, 4, 'How would you rate the office cleanliness?', 'rating', 'facility', 9, 1, '2026-05-17 15:08:08'),
(200, 20, 4, 'Overall satisfaction with Diploma service?', 'rating', 'overall', 10, 1, '2026-05-17 15:08:08'),
(201, 20, 5, 'How satisfied are you with the processing time for Authentication?', 'rating', 'service', 1, 1, '2026-05-17 15:08:08'),
(202, 20, 5, 'How helpful and courteous were the staff for Authentication?', 'rating', 'staff', 2, 1, '2026-05-17 15:08:08'),
(203, 20, 5, 'How clear were the instructions for Authentication requirements?', 'rating', 'information', 3, 1, '2026-05-17 15:08:08'),
(204, 20, 5, 'How accurate was your Authentication document?', 'rating', 'quality', 4, 1, '2026-05-17 15:08:08'),
(205, 20, 5, 'How convenient was the Authentication process?', 'rating', 'convenience', 5, 1, '2026-05-17 15:08:08'),
(206, 20, 5, 'How long did you wait for Authentication?', 'rating', 'waiting', 6, 1, '2026-05-17 15:08:08'),
(207, 20, 5, 'How organized is the Authentication processing?', 'rating', 'organization', 7, 1, '2026-05-17 15:08:08'),
(208, 20, 5, 'How likely are you to recommend Authentication service?', 'rating', 'recommendation', 8, 1, '2026-05-17 15:08:08'),
(209, 20, 5, 'How would you rate the office cleanliness?', 'rating', 'facility', 9, 1, '2026-05-17 15:08:08'),
(210, 20, 5, 'Overall satisfaction with Authentication service?', 'rating', 'overall', 10, 1, '2026-05-17 15:08:08');

-- --------------------------------------------------------

--
-- Table structure for table `survey_questions_backup`
--

CREATE TABLE `survey_questions_backup` (
  `id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `service_type_id` int(11) DEFAULT NULL,
  `question_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `question_type` enum('rating','text','yesno','multiple') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'rating',
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `survey_questions_backup`
--

INSERT INTO `survey_questions_backup` (`id`, `office_id`, `service_type_id`, `question_text`, `question_type`, `category`, `display_order`, `is_active`, `created_at`) VALUES
(0, 20, NULL, 'How satisfied are you with the processing time for your request?', 'rating', 'service', 1, 1, '2026-05-16 16:44:45'),
(0, 20, NULL, 'How helpful and courteous were the Registrar staff?', 'rating', 'staff', 2, 1, '2026-05-16 16:44:45'),
(0, 20, NULL, 'How clear were the instructions and requirements provided?', 'rating', 'information', 3, 1, '2026-05-16 16:44:45'),
(0, 20, NULL, 'How would you rate the accuracy of your document/transaction?', 'rating', 'quality', 4, 1, '2026-05-16 16:44:45'),
(0, 20, NULL, 'How convenient was the overall process?', 'rating', 'convenience', 5, 1, '2026-05-16 16:44:45'),
(0, 20, NULL, 'How would you rate the waiting time?', 'rating', 'waiting', 6, 1, '2026-05-16 16:44:45'),
(0, 20, NULL, 'How organized is the Registrar\'s Office?', 'rating', 'organization', 7, 1, '2026-05-16 16:44:45'),
(0, 20, NULL, 'How likely are you to recommend our services to others?', 'rating', 'recommendation', 8, 1, '2026-05-16 16:44:45'),
(0, 20, NULL, 'How would you rate the cleanliness of the office?', 'rating', 'facility', 9, 1, '2026-05-16 16:44:45'),
(0, 20, NULL, 'Overall, how satisfied are you with the Registrar\'s Office?', 'rating', 'overall', 10, 1, '2026-05-16 16:44:45');

-- --------------------------------------------------------

--
-- Table structure for table `survey_settings`
--

CREATE TABLE `survey_settings` (
  `id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL,
  `log_type` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `details` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
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
  `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
  `description` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_by`, `updated_at`) VALUES
(1, 'system_name', 'EVSU Registrar Evaluation System', 'text', 'System name displayed in header', NULL, '2026-03-11 06:45:31'),
(2, 'system_version', '2.0.0', 'text', 'Current system version', NULL, '2026-03-11 06:45:31'),
(3, 'items_per_page', '20', 'number', 'Number of items to display per page', NULL, '2026-03-11 06:45:31'),
(4, 'allow_registration', '1', 'boolean', 'Allow new student registration', NULL, '2026-03-11 06:45:31'),
(5, 'require_email_verification', '0', 'boolean', 'Require email verification for new accounts', NULL, '2026-03-11 06:45:31'),
(6, 'session_timeout', '3600', 'number', 'Session timeout in seconds', NULL, '2026-03-11 06:45:31'),
(7, 'max_login_attempts', '5', 'number', 'Maximum failed login attempts before lockout', NULL, '2026-03-11 06:45:31'),
(8, 'lockout_time', '900', 'number', 'Account lockout time in seconds', NULL, '2026-03-11 06:45:31'),
(9, 'backup_retention_days', '30', 'number', 'Number of days to keep backups', NULL, '2026-03-11 06:45:31'),
(10, 'maintenance_mode', '0', 'boolean', 'Put system in maintenance mode', NULL, '2026-03-11 06:45:31'),
(11, 'maintenance_message', 'System under maintenance. Please check back later.', 'text', 'Message to display during maintenance', NULL, '2026-03-11 06:45:31'),
(12, 'contact_email', 'admin@evsu.edu.ph', 'text', 'System administrator contact email', NULL, '2026-03-11 06:45:31'),
(13, 'school_year', '2026-2027', 'text', 'Current school year', NULL, '2026-03-11 06:45:31'),
(14, 'semester', '2nd Semester', 'text', 'Current semester', NULL, '2026-03-11 06:45:31'),
(15, 'evaluation_reminder_days', '7', 'number', 'Days before evaluation deadline to send reminders', NULL, '2026-03-11 06:45:31'),
(1, 'system_name', 'EVSU Registrar Evaluation System', 'text', 'System name displayed in header', NULL, '2026-03-11 06:45:31'),
(2, 'system_version', '2.0.0', 'text', 'Current system version', NULL, '2026-03-11 06:45:31'),
(3, 'items_per_page', '20', 'number', 'Number of items to display per page', NULL, '2026-03-11 06:45:31'),
(4, 'allow_registration', '1', 'boolean', 'Allow new student registration', NULL, '2026-03-11 06:45:31'),
(5, 'require_email_verification', '0', 'boolean', 'Require email verification for new accounts', NULL, '2026-03-11 06:45:31'),
(6, 'session_timeout', '3600', 'number', 'Session timeout in seconds', NULL, '2026-03-11 06:45:31'),
(7, 'max_login_attempts', '5', 'number', 'Maximum failed login attempts before lockout', NULL, '2026-03-11 06:45:31'),
(8, 'lockout_time', '900', 'number', 'Account lockout time in seconds', NULL, '2026-03-11 06:45:31'),
(9, 'backup_retention_days', '30', 'number', 'Number of days to keep backups', NULL, '2026-03-11 06:45:31'),
(10, 'maintenance_mode', '0', 'boolean', 'Put system in maintenance mode', NULL, '2026-03-11 06:45:31'),
(11, 'maintenance_message', 'System under maintenance. Please check back later.', 'text', 'Message to display during maintenance', NULL, '2026-03-11 06:45:31'),
(12, 'contact_email', 'admin@evsu.edu.ph', 'text', 'System administrator contact email', NULL, '2026-03-11 06:45:31'),
(13, 'school_year', '2026-2027', 'text', 'Current school year', NULL, '2026-03-11 06:45:31'),
(14, 'semester', '2nd Semester', 'text', 'Current semester', NULL, '2026-03-11 06:45:31'),
(15, 'evaluation_reminder_days', '7', 'number', 'Days before evaluation deadline to send reminders', NULL, '2026-03-11 06:45:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','staff','student') DEFAULT 'student',
  `student_id` varchar(20) DEFAULT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `email_verified` tinyint(4) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `verification_sent_at` timestamp NULL DEFAULT NULL,
  `verification_expires_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `mojoauth_id` varchar(100) DEFAULT NULL,
  `enrollment_status` enum('enrolled','graduated','inactive') DEFAULT 'enrolled',
  `last_enrolled` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `student_id`, `fullname`, `profile_picture`, `is_active`, `created_at`, `last_login`, `email_verified`, `verification_token`, `verification_sent_at`, `verification_expires_at`, `updated_at`, `mojoauth_id`, `enrollment_status`, `last_enrolled`) VALUES
(4, 'admin', '$2y$10$TZ1PQSroGCBxJZLg7QTSxOpqbL9WL3qhh.E3HN/xcSTvwBw7sWoq2', 'admin@evsu.edu.ph', 'admin', NULL, 'System Administrator', NULL, 1, '2026-05-14 18:25:00', NULL, 0, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL),
(10, 'Ken', '$2y$10$7DKjL90qkZ4/3NQJsATckeSh3lJTe8pmyyVIMBYO1iloFMpZhVuoC', 'ken.repollo@evsu.edu.ph', 'student', '2022-32626', 'Ken Repollo', NULL, 1, '2026-05-25 14:50:15', '2026-05-26 14:22:36', 0, NULL, NULL, NULL, NULL, NULL, 'enrolled', '2026-05-25'),
(11, 'evaluation', '$2y$10$ZBcMZLAMSKpsjjRo6PiSt.k7Nz5X1RwN.ZHGzfaluPuzcwnnFSUCO', 'system.evaluation@evsu.edu.ph', 'student', '2022-66666', 'sytem', NULL, 1, '2026-05-26 02:14:59', '2026-05-26 03:18:37', 0, NULL, NULL, NULL, NULL, NULL, 'enrolled', '2026-05-26'),
(13, 'Nova', '$2y$10$ORrD0iQ313HggGzM08TpWe6D8DmobglPrHChT9glhIuo1323nJA/y', 'nova.redulla@evsu.edu.ph', 'student', '2022-30774', 'Nova Redulla', NULL, 1, '2026-05-26 02:40:23', '2026-05-26 02:40:42', 0, NULL, NULL, NULL, NULL, NULL, 'enrolled', '2026-05-26'),
(14, 'aira', '$2y$10$vUPsyNgt/0eyGwYx6bkUTupkkEQbUlkpS8.Tmw5aiCh.nX2EvfzGa', 'aira.cabase@evsu.edu.ph', 'student', '2022-23249', 'AIRA CABASE', NULL, 1, '2026-05-26 02:42:26', '2026-05-26 02:42:38', 0, NULL, NULL, NULL, NULL, NULL, 'enrolled', '2026-05-26'),
(20, 'kenpublio', '', 'kenpublio12@gmail.com', 'student', '2022-32626', 'Ken Repollo', NULL, 1, '2026-05-26 04:25:57', NULL, 0, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL),
(24, 'marklindon', '', 'mark.serato@evsu.edu.ph', 'student', '2022-31409', 'Mark Lindon Serato', NULL, 1, '2026-05-26 04:25:57', NULL, 0, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_logs`
--

INSERT INTO `user_logs` (`id`, `user_id`, `action`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 0, 'registration', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 16:57:12'),
(2, 0, 'registration', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 16:57:12'),
(3, 0, 'evaluation_submitted_registrar', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 16:58:19'),
(4, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:13:26'),
(5, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:13:29'),
(6, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:13:32'),
(7, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:13:40'),
(8, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:14:58'),
(9, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:15:02'),
(10, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:15:17'),
(11, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:15:40'),
(12, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:15:46'),
(13, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:18:56'),
(14, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:18:58'),
(15, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:19:02'),
(16, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:19:10'),
(17, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:19:37'),
(18, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:20:41'),
(19, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:20:52'),
(20, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:21:16'),
(21, 0, 'registration', '2001:4454:18e:f000:9d21:1b00:5f70:eef3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:32:40'),
(22, 0, 'registration', '2001:4454:18e:f000:9d21:1b00:5f70:eef3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:32:40'),
(23, 0, 'registration', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:43:27'),
(24, 0, 'registration', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:43:27'),
(25, NULL, 'login_failed', '2001:4454:18e:f000:9d21:1b00:5f70:eef3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:46:47'),
(26, NULL, 'login_failed', '2001:4454:18e:f000:9d21:1b00:5f70:eef3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:46:54'),
(27, NULL, 'login_failed', '2001:4454:18e:f000:9d21:1b00:5f70:eef3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:47:11'),
(28, 0, 'registration', '2001:4454:18e:f000:9d21:1b00:5f70:eef3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:47:52'),
(29, 0, 'registration', '2001:4454:18e:f000:9d21:1b00:5f70:eef3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:47:52'),
(30, NULL, 'login_failed', '2001:4454:18e:f000:9d21:1b00:5f70:eef3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:48:12'),
(31, 0, 'evaluation_submitted_registrar', '2001:4454:18e:f000:9d21:1b00:5f70:eef3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:51:30'),
(32, 0, 'deactivate_student', '2001:4454:18e:f000:9d21:1b00:5f70:eef3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:04:11'),
(33, 0, 'activate_student', '2001:4454:18e:f000:9d21:1b00:5f70:eef3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:04:29'),
(34, 0, 'deactivate_student', '2001:4454:18e:f000:9d21:1b00:5f70:eef3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:04:35'),
(35, 0, 'delete_student', '2001:4454:18e:f000:9d21:1b00:5f70:eef3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:04:54'),
(36, 0, 'deactivate_student', '2001:4454:18e:f000:9d21:1b00:5f70:eef3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:05:04'),
(37, 0, 'activate_student', '2001:4454:18e:f000:9d21:1b00:5f70:eef3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:05:43'),
(38, 0, 'delete_student', '2001:4454:18e:f000:9d21:1b00:5f70:eef3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:06:49'),
(39, 8, 'registration', '2001:4454:18e:f000:9d21:1b00:5f70:eef3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:08:17'),
(40, 8, 'registration', '2001:4454:18e:f000:9d21:1b00:5f70:eef3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:08:17'),
(41, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:14:40'),
(42, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:14:59'),
(43, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:15:51'),
(44, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:15:56'),
(45, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:15:59'),
(46, 8, 'evaluation_submitted_registrar', '2001:4454:18e:f000:9d21:1b00:5f70:eef3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:17:02'),
(47, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:18:29'),
(48, NULL, 'login_failed', '2001:4454:18e:f000:d473:78b5:761:9f9a', 'Mozilla/5.0 (Linux; Android 16; V2434 Build/BP2A.250605.031.A3_NN_V000L1; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/147.0.7727.138 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/560.0.0.55.69;]', '2026-05-14 18:18:29'),
(49, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:18:33'),
(50, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:18:46'),
(51, 8, 'evaluation_submitted_registrar', '2001:4454:18e:f000:9d21:1b00:5f70:eef3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:21:20'),
(52, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:23:34'),
(53, 6, 'registration', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:28:46'),
(54, 6, 'registration', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:28:46'),
(55, 4, 'update_survey_settings', '2001:4454:18e:f000:9d21:1b00:5f70:eef3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:30:20'),
(56, 7, 'registration', '2001:4454:18e:f000:9d21:1b00:5f70:eef3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:32:45'),
(57, 7, 'registration', '2001:4454:18e:f000:9d21:1b00:5f70:eef3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:32:45'),
(58, 7, 'evaluation_submitted_registrar', '2001:4454:18e:f000:9d21:1b00:5f70:eef3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 18:33:30'),
(59, 6, 'login_success', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 18:36:53'),
(60, 6, 'evaluation_submitted_registrar', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 18:37:25'),
(61, 6, 'evaluation_submitted_registrar', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 18:38:13'),
(62, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 15:31:38'),
(63, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 15:32:21'),
(64, 6, 'login_success', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 15:32:49'),
(65, 8, 'registration', '2001:4454:18e:f000:563:c5b8:b873:d3e1', 'Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/537.36 (KHTML, like Gecko)  VivoBrowser/15.8.2.0 Chrome/131.0.6778.200 Safari/537.36', '2026-05-16 15:39:13'),
(66, 8, 'registration', '2001:4454:18e:f000:563:c5b8:b873:d3e1', 'Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/537.36 (KHTML, like Gecko)  VivoBrowser/15.8.2.0 Chrome/131.0.6778.200 Safari/537.36', '2026-05-16 15:39:13'),
(67, 8, 'login_success', '2001:4454:18e:f000:563:c5b8:b873:d3e1', 'Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/537.36 (KHTML, like Gecko)  VivoBrowser/15.8.2.0 Chrome/131.0.6778.200 Safari/537.36', '2026-05-16 15:39:28'),
(68, 6, 'login_success', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 16:23:47'),
(69, 4, 'add_default_questions', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:06:27'),
(70, 4, 'add_default_questions', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:06:30'),
(71, 4, 'add_default_questions', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:06:32'),
(72, 4, 'add_default_questions', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:06:34'),
(73, 4, 'add_default_questions', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 17:06:37'),
(74, 4, 'clear_logs', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 18:44:04'),
(75, 9, 'registration', '175.176.69.241', 'Mozilla/5.0 (Linux; Android 13; V2038 Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/147.0.7727.138 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/561.0.0.55.76;]', '2026-05-17 13:18:05'),
(76, 9, 'registration', '175.176.69.241', 'Mozilla/5.0 (Linux; Android 13; V2038 Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/147.0.7727.138 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/561.0.0.55.76;]', '2026-05-17 13:18:05'),
(77, 9, 'login_success', '175.176.69.241', 'Mozilla/5.0 (Linux; Android 13; V2038 Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/147.0.7727.138 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/561.0.0.55.76;]', '2026-05-17 13:18:30'),
(78, 9, 'login_success', '175.176.69.241', 'Mozilla/5.0 (Linux; Android 13; V2038 Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/147.0.7727.138 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/561.0.0.55.76;]', '2026-05-17 13:22:01'),
(79, 6, 'login_success', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 14:54:14'),
(80, 6, 'login_success', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 14:59:49'),
(81, 6, 'login_success', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 06:47:20'),
(82, 7, 'login_success', '2001:4454:1fc:5200:50e6:7e57:2d92:de0c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 11:44:52'),
(83, 7, 'evaluation_submitted_registrar', '2001:4454:1fc:5200:50e6:7e57:2d92:de0c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 12:38:01'),
(84, NULL, 'login_failed', '143.44.165.2', 'Mozilla/5.0 (Linux; Android 16; 2409BRN2CA Build/BP2A.250605.031.A3; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/148.0.7778.178 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/562.0.0.53.83;]', '2026-05-25 12:45:05'),
(85, NULL, 'login_failed', '143.44.165.2', 'Mozilla/5.0 (Linux; Android 16; 2409BRN2CA Build/BP2A.250605.031.A3; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/148.0.7778.178 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/562.0.0.53.83;]', '2026-05-25 12:45:22'),
(86, 7, 'login_success', '49.145.33.229', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 12:50:19'),
(87, 4, 'deactivate_student', '49.145.33.229', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 12:53:41'),
(88, 4, 'activate_student', '49.145.33.229', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 12:53:49'),
(89, 4, 'deactivate_student', '49.145.33.229', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 12:53:55'),
(90, 4, 'activate_student', '49.145.33.229', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 12:54:02'),
(91, 4, 'reset_student_password', '49.145.33.229', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 12:54:19'),
(92, 4, 'reset_student_password', '49.145.33.229', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 12:54:24'),
(93, 4, 'deactivate_student', '49.145.33.229', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 12:54:40'),
(94, 4, 'activate_student', '49.145.33.229', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 12:54:48'),
(95, 4, 'deactivate_student', '49.145.33.229', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 12:55:01'),
(96, 4, 'activate_student', '49.145.33.229', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 12:55:11'),
(97, 4, 'reset_student_password', '2001:4454:1fc:5200:50e6:7e57:2d92:de0c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 12:56:32'),
(98, 4, 'deactivate_student', '2001:4454:1fc:5200:50e6:7e57:2d92:de0c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 12:56:35'),
(99, 4, 'activate_student', '2001:4454:1fc:5200:50e6:7e57:2d92:de0c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 12:57:12'),
(100, 4, 'deactivate_student', '2001:4454:1fc:5200:50e6:7e57:2d92:de0c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 12:57:16'),
(101, 4, 'update_survey_settings', '2001:4454:1fc:5200:50e6:7e57:2d92:de0c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 13:37:40'),
(102, 4, 'update_survey_settings', '2001:4454:1fc:5200:50e6:7e57:2d92:de0c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 13:37:54'),
(103, 6, 'login_success', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 14:49:25'),
(104, 10, 'registration', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 14:50:15'),
(105, 10, 'registration', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 14:50:15'),
(106, 10, 'login_success', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 14:50:28'),
(107, 10, 'evaluation_submitted_registrar', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 14:51:06'),
(108, 4, 'delete_student', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 15:13:59'),
(109, 4, 'delete_student', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 15:15:45'),
(110, 4, 'delete_student', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 15:15:48'),
(111, 4, 'delete_student', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 15:17:33'),
(112, 4, 'delete_student', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 15:18:01'),
(113, 4, 'delete_student', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 15:18:13'),
(114, 4, 'delete_student', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 15:18:42'),
(115, 11, 'registration', '58.69.60.2', 'Mozilla/5.0 (Linux; Android 16; V2434 Build/BP2A.250605.031.A3_NN_V000L1; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/148.0.7778.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/562.0.0.53.83;]', '2026-05-26 02:14:59'),
(116, 11, 'registration', '58.69.60.2', 'Mozilla/5.0 (Linux; Android 16; V2434 Build/BP2A.250605.031.A3_NN_V000L1; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/148.0.7778.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/562.0.0.53.83;]', '2026-05-26 02:14:59'),
(117, 11, 'login_success', '58.69.60.2', 'Mozilla/5.0 (Linux; Android 16; V2434 Build/BP2A.250605.031.A3_NN_V000L1; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/148.0.7778.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/562.0.0.53.83;]', '2026-05-26 02:15:11'),
(118, 12, 'registration', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 02:25:43'),
(119, 12, 'registration', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 02:25:43'),
(120, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 02:25:57'),
(121, 12, 'login_success', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 02:26:02'),
(122, 12, 'evaluation_submitted_registrar', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 02:27:32'),
(123, 4, 'update_survey_settings', '58.69.60.2', 'Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/537.36 (KHTML, like Gecko)  VivoBrowser/16.0.0.2 Chrome/131.0.6778.200 Safari/537.36', '2026-05-26 02:38:32'),
(124, 4, 'update_survey_settings', '58.69.60.2', 'Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/537.36 (KHTML, like Gecko)  VivoBrowser/16.0.0.2 Chrome/131.0.6778.200 Safari/537.36', '2026-05-26 02:38:37'),
(125, 4, 'update_survey_settings', '58.69.60.2', 'Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/537.36 (KHTML, like Gecko)  VivoBrowser/16.0.0.2 Chrome/131.0.6778.200 Safari/537.36', '2026-05-26 02:39:15'),
(126, 13, 'registration', '58.69.60.2', 'Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/537.36 (KHTML, like Gecko)  VivoBrowser/16.0.0.2 Chrome/131.0.6778.200 Safari/537.36', '2026-05-26 02:40:23'),
(127, 13, 'registration', '58.69.60.2', 'Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/537.36 (KHTML, like Gecko)  VivoBrowser/16.0.0.2 Chrome/131.0.6778.200 Safari/537.36', '2026-05-26 02:40:23'),
(128, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/537.36 (KHTML, like Gecko)  VivoBrowser/16.0.0.2 Chrome/131.0.6778.200 Safari/537.36', '2026-05-26 02:40:31'),
(129, 13, 'login_success', '58.69.60.2', 'Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/537.36 (KHTML, like Gecko)  VivoBrowser/16.0.0.2 Chrome/131.0.6778.200 Safari/537.36', '2026-05-26 02:40:42'),
(130, 13, 'evaluation_submitted_registrar', '58.69.60.2', 'Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/537.36 (KHTML, like Gecko)  VivoBrowser/16.0.0.2 Chrome/131.0.6778.200 Safari/537.36', '2026-05-26 02:41:15'),
(131, 14, 'registration', '58.69.60.2', 'Mozilla/5.0 (Linux; Android 13; V2038 Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/148.0.7778.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/562.0.0.53.83;]', '2026-05-26 02:42:26'),
(132, 14, 'registration', '58.69.60.2', 'Mozilla/5.0 (Linux; Android 13; V2038 Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/148.0.7778.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/562.0.0.53.83;]', '2026-05-26 02:42:26'),
(133, 14, 'login_success', '58.69.60.2', 'Mozilla/5.0 (Linux; Android 13; V2038 Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/148.0.7778.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/562.0.0.53.83;]', '2026-05-26 02:42:38'),
(134, 14, 'evaluation_submitted_registrar', '58.69.60.2', 'Mozilla/5.0 (Linux; Android 13; V2038 Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/148.0.7778.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/562.0.0.53.83;]', '2026-05-26 02:43:45'),
(135, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 02:46:36'),
(136, NULL, 'login_failed', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 02:46:41'),
(137, 10, 'login_success', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 02:46:45'),
(138, 4, 'deactivate_question', '58.69.60.2', 'Mozilla/5.0 (Linux; Android 16; V2434 Build/BP2A.250605.031.A3_NN_V000L1; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/148.0.7778.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/562.0.0.53.83;]', '2026-05-26 02:49:35'),
(139, 4, 'deactivate_question', '58.69.60.2', 'Mozilla/5.0 (Linux; Android 16; V2434 Build/BP2A.250605.031.A3_NN_V000L1; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/148.0.7778.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/562.0.0.53.83;]', '2026-05-26 02:49:43'),
(140, 4, 'deactivate_question', '58.69.60.2', 'Mozilla/5.0 (Linux; Android 16; V2434 Build/BP2A.250605.031.A3_NN_V000L1; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/148.0.7778.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/562.0.0.53.83;]', '2026-05-26 02:50:11'),
(141, 4, 'deactivate_question', '58.69.60.2', 'Mozilla/5.0 (Linux; Android 16; V2434 Build/BP2A.250605.031.A3_NN_V000L1; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/148.0.7778.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/562.0.0.53.83;]', '2026-05-26 02:50:33'),
(142, 4, 'deactivate_question', '58.69.60.2', 'Mozilla/5.0 (Linux; Android 16; V2434 Build/BP2A.250605.031.A3_NN_V000L1; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/148.0.7778.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/562.0.0.53.83;]', '2026-05-26 02:50:46'),
(143, 10, 'login_success', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 02:51:55'),
(144, 11, 'login_success', '58.69.60.2', 'Mozilla/5.0 (Linux; Android 16; V2434 Build/BP2A.250605.031.A3_NN_V000L1; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/148.0.7778.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/562.0.0.53.83;]', '2026-05-26 02:52:11'),
(145, 4, 'update_survey_settings', '58.69.60.2', 'Mozilla/5.0 (Linux; Android 16; V2434 Build/BP2A.250605.031.A3_NN_V000L1; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/148.0.7778.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/562.0.0.53.83;]', '2026-05-26 02:53:38'),
(146, 4, 'update_survey_settings', '58.69.60.2', 'Mozilla/5.0 (Linux; Android 16; V2434 Build/BP2A.250605.031.A3_NN_V000L1; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/148.0.7778.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/562.0.0.53.83;]', '2026-05-26 02:53:56'),
(147, 4, 'update_survey_settings', '58.69.60.2', 'Mozilla/5.0 (Linux; Android 16; V2434 Build/BP2A.250605.031.A3_NN_V000L1; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/148.0.7778.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/562.0.0.53.83;]', '2026-05-26 02:54:08'),
(148, 4, 'update_survey_settings', '58.69.60.2', 'Mozilla/5.0 (Linux; Android 16; V2434 Build/BP2A.250605.031.A3_NN_V000L1; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/148.0.7778.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/562.0.0.53.83;]', '2026-05-26 02:54:19'),
(149, 4, 'update_survey_settings', '58.69.60.2', 'Mozilla/5.0 (Linux; Android 16; V2434 Build/BP2A.250605.031.A3_NN_V000L1; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/148.0.7778.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/562.0.0.53.83;]', '2026-05-26 02:54:38'),
(150, 11, 'login_success', '58.69.60.2', 'Mozilla/5.0 (Linux; Android 16; V2434 Build/BP2A.250605.031.A3_NN_V000L1; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/148.0.7778.120 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/562.0.0.53.83;]', '2026-05-26 02:55:22'),
(151, 10, 'login_success', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 02:58:17'),
(152, 11, 'login_success', '58.69.60.2', 'Mozilla/5.0 (Linux; Android 15; V2434) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/123.0.6312.118 Mobile Safari/537.36 VivoBrowser/15.0.2.4', '2026-05-26 03:18:37'),
(153, 4, 'update_survey_settings', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 03:21:01'),
(154, 4, 'update_survey_settings', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 03:24:49'),
(155, 4, 'update_survey_settings', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 03:26:15'),
(156, 4, 'update_survey_settings', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 03:26:20'),
(157, 4, 'update_survey_settings', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 03:26:24'),
(158, 4, 'update_survey_settings', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 03:27:03'),
(159, 4, 'update_survey_settings', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 03:28:19'),
(160, 4, 'update_survey_settings', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 03:28:59'),
(161, 4, 'update_survey_settings', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 03:29:01'),
(162, 4, 'update_survey_settings', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 03:29:03'),
(163, 4, 'update_survey_settings', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 03:29:06'),
(164, 10, 'login_success', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 03:29:40'),
(165, 4, 'update_survey_settings', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 03:32:00'),
(166, 4, 'update_survey_settings', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 03:32:11'),
(167, 4, 'update_survey_settings', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 03:32:19'),
(168, 4, 'update_survey_settings', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 03:32:27'),
(169, 4, 'update_survey_settings', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 03:32:32'),
(170, 4, 'update_survey_settings', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 03:32:36'),
(171, 10, 'login_success', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 12:50:20'),
(172, 10, 'login_success', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 13:36:03'),
(173, 10, 'login_success', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 13:51:56'),
(174, 10, 'login_success', '58.69.60.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 14:22:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `action` (`action`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_email_type` (`email_type`);

--
-- Indexes for table `email_tokens`
--
ALTER TABLE `email_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_user_type` (`user_id`,`token_type`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_password_resets_token` (`token`),
  ADD KEY `idx_password_resets_expiry` (`expires_at`);

--
-- Indexes for table `schema_migrations`
--
ALTER TABLE `schema_migrations`
  ADD PRIMARY KEY (`migration`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `office_id` (`office_id`);

--
-- Indexes for table `survey_availability`
--
ALTER TABLE `survey_availability`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `survey_questions`
--
ALTER TABLE `survey_questions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `survey_settings`
--
ALTER TABLE `survey_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_log_type` (`log_type`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_tokens`
--
ALTER TABLE `email_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `survey_availability`
--
ALTER TABLE `survey_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `survey_questions`
--
ALTER TABLE `survey_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=211;

--
-- AUTO_INCREMENT for table `survey_settings`
--
ALTER TABLE `survey_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=175;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
