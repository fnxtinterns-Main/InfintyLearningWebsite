-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 07, 2026 at 07:06 AM
-- Server version: 11.8.9-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u999011396_inflearning`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_assessments`
--

CREATE TABLE `academic_assessments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `teacher_user_id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `batch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `class_level` varchar(50) DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(220) NOT NULL,
  `assessment_type` enum('homework','unit_test','mid_term','final_term','practice_quiz') NOT NULL DEFAULT 'homework',
  `total_marks` decimal(5,1) NOT NULL DEFAULT 20.0,
  `passing_marks` decimal(5,1) NOT NULL DEFAULT 8.0,
  `duration_minutes` int(10) UNSIGNED DEFAULT 0,
  `instructions` text DEFAULT NULL,
  `questions_json` longtext NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `status` enum('draft','published','closed') NOT NULL DEFAULT 'published',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `achievements`
--

CREATE TABLE `achievements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category` enum('academic','sports','creative','communication','problem_solving','competition','other') DEFAULT 'other',
  `badge_icon` varchar(255) DEFAULT NULL,
  `earned_at` datetime DEFAULT current_timestamp(),
  `awarded_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `category` enum('creative','academic','sports','cultural','technology','communication','competition') NOT NULL,
  `short_description` varchar(300) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `gallery` longtext DEFAULT NULL CHECK (json_valid(`gallery`)),
  `event_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `venue` varchar(200) DEFAULT NULL,
  `organizer` varchar(150) DEFAULT NULL,
  `eligibility` varchar(200) DEFAULT NULL,
  `eligible_classes` varchar(300) DEFAULT NULL,
  `batch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `registration_deadline` date DEFAULT NULL,
  `registration_url` varchar(500) DEFAULT NULL,
  `fee` decimal(10,2) DEFAULT 0.00,
  `max_participants` int(10) UNSIGNED DEFAULT NULL,
  `contact_email` varchar(150) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `tags` varchar(300) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `status` enum('draft','published','closed','archived') DEFAULT 'published',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `uuid`, `title`, `slug`, `category`, `short_description`, `description`, `cover_image`, `gallery`, `event_date`, `start_time`, `end_time`, `venue`, `organizer`, `eligibility`, `eligible_classes`, `batch_id`, `registration_deadline`, `registration_url`, `fee`, `max_participants`, `contact_email`, `contact_phone`, `tags`, `featured`, `status`, `created_by`, `school_id`, `created_at`, `updated_at`) VALUES
(1, '573fee8d-d922-4be2-8f10-449fc111fa81', 'QUIZWIZ', 'quizwiz', 'competition', 'Quiz competition', 'There is a quiz competition for class 6-12', '/uploads/activities/27484c36c25127a1_1790330150.jpg', NULL, '2026-09-27', '08:00:00', '00:00:00', 'SCHOOL AUDITORIUM', 'QUIZWIZ', 'Ages 6-18', NULL, NULL, '2026-09-26', '', 0.00, 100, '', '', '', 1, 'published', 1, NULL, '2026-09-25 15:25:50', '2026-09-25 15:25:50');

-- --------------------------------------------------------

--
-- Table structure for table `activity_registrations`
--

CREATE TABLE `activity_registrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `activity_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('registered','approved','rejected','attended','absent') DEFAULT 'registered',
  `registered_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_registrations`
--

INSERT INTO `activity_registrations` (`id`, `activity_id`, `user_id`, `notes`, `status`, `registered_at`) VALUES
(1, 1, 6, NULL, 'attended', '2026-09-25 19:11:03'),
(2, 1, 1, NULL, 'registered', '2026-09-25 19:12:09');

-- --------------------------------------------------------

--
-- Table structure for table `activity_results`
--

CREATE TABLE `activity_results` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `activity_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `score` decimal(7,2) DEFAULT NULL,
  `position` int(10) UNSIGNED DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `recorded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `recorded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_results`
--

INSERT INTO `activity_results` (`id`, `activity_id`, `student_user_id`, `score`, `position`, `remarks`, `recorded_by`, `recorded_at`) VALUES
(1, 1, 6, 88.00, 1, NULL, 5, '2026-09-29 10:35:49');

-- --------------------------------------------------------

--
-- Table structure for table `ai_answers`
--

CREATE TABLE `ai_answers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ai_question_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `answer_text` text DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `ai_feedback` text DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `answered_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_questions`
--

CREATE TABLE `ai_questions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ai_session_id` bigint(20) UNSIGNED NOT NULL,
  `question_text` text NOT NULL,
  `expected_answer` text DEFAULT NULL,
  `difficulty` enum('easy','medium','hard') DEFAULT 'medium',
  `generated_by` enum('ai','teacher','imported') DEFAULT 'ai',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_sessions`
--

CREATE TABLE `ai_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `topic_id` bigint(20) UNSIGNED DEFAULT NULL,
  `mode` enum('explain','practice','evaluate','recommend','chat') DEFAULT 'practice',
  `context` longtext DEFAULT NULL CHECK (json_valid(`context`)),
  `started_at` datetime DEFAULT current_timestamp(),
  `ended_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `body` text NOT NULL,
  `audience` enum('all','students','teachers','admins') DEFAULT 'all',
  `pinned` tinyint(1) DEFAULT 0,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `body`, `audience`, `pinned`, `created_by`, `school_id`, `created_at`) VALUES
(1, 'Quizwiz', 'There is going to be a quiz competation please participate.', 'all', 0, 1, 3, '2026-09-25 19:13:21');

-- --------------------------------------------------------

--
-- Table structure for table `assessment_submissions`
--

CREATE TABLE `assessment_submissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `assessment_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `answers_json` longtext DEFAULT NULL,
  `score_obtained` decimal(5,1) DEFAULT NULL,
  `is_evaluated` tinyint(1) NOT NULL DEFAULT 0,
  `teacher_feedback` text DEFAULT NULL,
  `status` enum('submitted','evaluated','late') NOT NULL DEFAULT 'submitted',
  `submitted_at` datetime DEFAULT current_timestamp(),
  `evaluated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `batch_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('present','absent','late','excused') NOT NULL,
  `marked_by` bigint(20) UNSIGNED DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `batch_id`, `student_user_id`, `attendance_date`, `status`, `marked_by`, `remarks`, `created_at`) VALUES
(1, 1, 6, '2026-09-25', 'present', 5, NULL, '2026-09-25 15:21:32'),
(2, 1, 6, '2026-09-24', 'absent', 5, NULL, '2026-09-25 19:28:59'),
(3, 1, 6, '2026-09-30', 'present', 5, NULL, '2026-09-30 09:11:50');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_sessions`
--

CREATE TABLE `attendance_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `batch_id` bigint(20) UNSIGNED NOT NULL,
  `session_date` date NOT NULL,
  `session_label` varchar(100) DEFAULT NULL,
  `marked_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `metadata` longtext DEFAULT NULL CHECK (json_valid(`metadata`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `metadata`, `ip_address`, `created_at`) VALUES
(1, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-24 12:28:03'),
(2, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-25 14:58:31'),
(3, 1, 'school.created', 'school', 1, '{\"name\":\"TEST SCHOOL\",\"code\":\"TS-6183\"}', '::1', '2026-09-25 14:59:33'),
(4, 2, 'user.login', 'user', 2, NULL, '::1', '2026-09-25 15:00:01'),
(5, 2, 'user.logout', 'user', 2, NULL, '::1', '2026-09-25 15:05:08'),
(6, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-25 15:05:26'),
(7, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-25 15:06:14'),
(8, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-25 15:07:11'),
(9, 1, 'school.created', 'school', 3, '{\"name\":\"Test SCHOOL\",\"code\":\"TS-F59C\"}', '::1', '2026-09-25 15:10:43'),
(10, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-25 15:11:25'),
(11, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"testteacher@gmail.com\",\"role\":\"teacher\",\"school_id\":3}', '::1', '2026-09-25 15:12:24'),
(12, NULL, 'registration.completed', 'user', 5, '{\"role\":\"teacher\",\"school_id\":3}', '::1', '2026-09-25 15:12:35'),
(13, 4, 'user.login', 'user', 4, NULL, '::1', '2026-09-25 15:12:51'),
(14, 4, 'school.user_approved', 'user', 5, '{\"school_id\":3}', '::1', '2026-09-25 15:12:57'),
(15, 4, 'user.logout', 'user', 4, NULL, '::1', '2026-09-25 15:13:30'),
(16, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-25 15:13:45'),
(17, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-25 15:14:54'),
(18, 4, 'user.login', 'user', 4, NULL, '::1', '2026-09-25 15:15:05'),
(19, 4, 'user.logout', 'user', 4, NULL, '::1', '2026-09-25 15:15:20'),
(20, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"stu@gmail.com\",\"role\":\"student\",\"school_id\":3}', '::1', '2026-09-25 15:16:36'),
(21, NULL, 'registration.completed', 'user', 6, '{\"role\":\"student\",\"school_id\":3}', '::1', '2026-09-25 15:17:04'),
(22, 4, 'user.login', 'user', 4, NULL, '::1', '2026-09-25 15:17:22'),
(23, 4, 'school.user_approved', 'user', 6, '{\"school_id\":3}', '::1', '2026-09-25 15:17:51'),
(24, 4, 'user.logout', 'user', 4, NULL, '::1', '2026-09-25 15:19:32'),
(25, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-25 15:20:18'),
(26, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-25 15:20:28'),
(27, 5, 'user.login', 'user', 5, NULL, '::1', '2026-09-25 15:20:34'),
(28, 5, 'batch.created', 'batch', 1, '{\"name\":\"science class 6\"}', '::1', '2026-09-25 15:20:56'),
(29, 5, 'batch.student_added', 'batch', 1, '{\"student\":6}', '::1', '2026-09-25 15:21:14'),
(30, 5, 'attendance.marked', 'batch', 1, '{\"date\":\"2026-09-25\",\"count\":1}', '::1', '2026-09-25 15:21:32'),
(31, 5, 'user.logout', 'user', 5, NULL, '::1', '2026-09-25 15:23:16'),
(32, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-25 15:23:29'),
(33, 1, 'activity.created', 'activity', 1, '{\"title\":\"QUIZWIZ\"}', '::1', '2026-09-25 15:25:50'),
(34, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-25 15:26:09'),
(35, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-25 15:40:17'),
(36, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-25 17:29:43'),
(37, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-25 17:30:20'),
(38, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-25 17:30:44'),
(39, 6, 'user.login', 'user', 6, NULL, '::1', '2026-09-25 17:30:53'),
(40, 6, 'password.changed', 'user', 6, NULL, '::1', '2026-09-25 18:39:45'),
(41, 6, 'password.change_failed', 'user', 6, '{\"reason\":\"wrong_current\"}', '::1', '2026-09-25 18:42:50'),
(42, 6, 'password.changed', 'user', 6, NULL, '::1', '2026-09-25 18:43:12'),
(43, 6, 'activity.registered', 'activity', 1, NULL, '::1', '2026-09-25 19:11:03'),
(44, 6, 'profile.updated', 'user', 6, NULL, '::1', '2026-09-25 19:11:21'),
(45, 6, 'user.logout', 'user', 6, NULL, '::1', '2026-09-25 19:11:35'),
(46, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-25 19:11:53'),
(47, 1, 'activity.registered', 'activity', 1, NULL, '::1', '2026-09-25 19:12:09'),
(48, 1, 'announcement.created', 'announcement', 1, '{\"audience\":\"all\",\"school_id\":3}', '::1', '2026-09-25 19:13:21'),
(49, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-25 19:13:26'),
(50, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-25 19:14:28'),
(51, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-25 19:14:37'),
(52, 5, 'user.login', 'user', 5, NULL, '::1', '2026-09-25 19:14:44'),
(53, 5, 'attendance.marked', 'batch', 1, '{\"date\":\"2026-09-24\",\"count\":1}', '::1', '2026-09-25 19:28:59'),
(54, 5, 'user.logout', 'user', 5, NULL, '::1', '2026-09-25 19:29:13'),
(55, 6, 'user.login', 'user', 6, NULL, '::1', '2026-09-25 19:29:30'),
(56, 6, 'user.logout', 'user', 6, NULL, '::1', '2026-09-25 19:29:58'),
(57, 4, 'user.login', 'user', 4, NULL, '::1', '2026-09-25 19:30:16'),
(58, 4, 'user.logout', 'user', 4, NULL, '::1', '2026-09-25 19:31:56'),
(59, 6, 'user.login', 'user', 6, NULL, '::1', '2026-09-25 19:32:06'),
(60, 6, 'user.logout', 'user', 6, NULL, '::1', '2026-09-25 19:32:42'),
(61, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-25 19:33:34'),
(62, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-25 19:33:44'),
(63, 5, 'user.login', 'user', 5, NULL, '::1', '2026-09-25 19:33:49'),
(64, 5, 'activity.assigned', 'batch', 1, '{\"activity_id\":1,\"assignment_id\":1}', '::1', '2026-09-25 19:34:46'),
(65, 5, 'user.logout', 'user', 5, NULL, '::1', '2026-09-25 19:46:33'),
(66, 4, 'user.login', 'user', 4, NULL, '::1', '2026-09-25 19:46:47'),
(67, 4, 'lesson.created', 'lesson', 2, '{\"title\":\"qqqqqqq\",\"school_id\":3,\"batch_id\":null,\"attachments\":1}', '::1', '2026-09-25 19:54:14'),
(68, 4, 'user.logout', 'user', 4, NULL, '::1', '2026-09-25 20:10:25'),
(69, 5, 'user.login', 'user', 5, NULL, '::1', '2026-09-25 20:10:47'),
(70, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-26 13:39:06'),
(71, 1, 'school.created', 'school', 4, '{\"name\":\"ISL Dhanbad\",\"code\":\"ID-E799\"}', '::1', '2026-09-26 13:41:12'),
(72, 1, 'school.created', 'school', 5, '{\"name\":\"DAV Ranchi\",\"code\":\"DR-03CA\"}', '::1', '2026-09-26 13:42:38'),
(73, 1, 'school.created', 'school', 6, '{\"name\":\"DAV KDPS\",\"code\":\"DK-95BB\"}', '::1', '2026-09-26 13:44:31'),
(74, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-26 13:45:59'),
(75, 9, 'user.login', 'user', 9, NULL, '::1', '2026-09-26 13:51:53'),
(76, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-26 13:52:58'),
(77, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-26 13:53:20'),
(78, 9, 'user.login', 'user', 9, NULL, '::1', '2026-09-26 13:53:40'),
(79, 9, 'user.logout', 'user', 9, NULL, '::1', '2026-09-26 13:53:47'),
(80, 8, 'user.login', 'user', 8, NULL, '::1', '2026-09-26 13:54:10'),
(81, 8, 'user.logout', 'user', 8, NULL, '::1', '2026-09-26 13:54:35'),
(82, 7, 'user.login', 'user', 7, NULL, '::1', '2026-09-26 13:54:43'),
(83, 9, 'user.login', 'user', 9, NULL, '::1', '2026-09-26 13:54:50'),
(84, 7, 'user.logout', 'user', 7, NULL, '::1', '2026-09-26 13:54:50'),
(85, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"testteacher@dav.com\",\"role\":\"teacher\",\"school_id\":5}', '::1', '2026-09-26 13:56:04'),
(86, NULL, 'registration.completed', 'user', 10, '{\"role\":\"teacher\",\"school_id\":5}', '::1', '2026-09-26 13:56:13'),
(87, 8, 'user.login', 'user', 8, NULL, '::1', '2026-09-26 13:57:13'),
(88, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"nishant012543@gmail.com\",\"role\":\"teacher\",\"school_id\":6}', '::1', '2026-09-26 13:57:17'),
(89, 8, 'school.user_approved', 'user', 10, '{\"school_id\":5}', '::1', '2026-09-26 13:57:22'),
(90, 8, 'user.logout', 'user', 8, NULL, '::1', '2026-09-26 13:57:25'),
(91, 10, 'user.login', 'user', 10, NULL, '::1', '2026-09-26 13:57:37'),
(92, 10, 'user.logout', 'user', 10, NULL, '::1', '2026-09-26 13:57:51'),
(93, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"nishant012543@gmail.com\",\"role\":\"teacher\",\"school_id\":6}', '::1', '2026-09-26 13:58:11'),
(94, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-26 14:12:25'),
(95, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"nishant012543@gmail.com\",\"role\":\"teacher\",\"school_id\":6}', '127.0.0.1', '2026-09-26 14:25:43'),
(96, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"nishant012543@gmail.com\",\"role\":\"teacher\",\"school_id\":6}', '127.0.0.1', '2026-09-26 14:33:01'),
(97, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-26 21:53:35'),
(98, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-27 20:00:40'),
(99, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-27 20:00:45'),
(100, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"kumaraviraj682@gmail.com\",\"role\":\"student\",\"school_id\":6}', '::1', '2026-09-28 11:52:57'),
(101, NULL, 'registration.completed', 'user', 11, '{\"role\":\"student\",\"school_id\":6}', '::1', '2026-09-28 11:53:23'),
(102, 9, 'user.login', 'user', 9, NULL, '::1', '2026-09-28 11:54:04'),
(103, 9, 'school.user_approved', 'user', 11, '{\"school_id\":6}', '::1', '2026-09-28 11:54:15'),
(104, 9, 'user.logout', 'user', 9, NULL, '::1', '2026-09-28 11:54:48'),
(105, 11, 'user.login', 'user', 11, NULL, '::1', '2026-09-28 11:55:05'),
(106, 11, 'user.logout', 'user', 11, NULL, '::1', '2026-09-28 11:55:31'),
(107, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-28 11:59:02'),
(108, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-28 12:20:14'),
(109, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-28 12:21:08'),
(110, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-28 12:21:27'),
(111, 5, 'user.login', 'user', 5, NULL, '::1', '2026-09-28 12:21:50'),
(112, 5, 'certificate.generated', 'certificate', 1, '{\"student_id\":6,\"type\":\"winner\"}', '::1', '2026-09-28 12:22:13'),
(113, 5, 'user.logout', 'user', 5, NULL, '::1', '2026-09-28 12:22:37'),
(114, 6, 'user.login', 'user', 6, NULL, '::1', '2026-09-28 12:22:49'),
(115, 6, 'user.logout', 'user', 6, NULL, '::1', '2026-09-28 12:26:52'),
(116, 5, 'user.login', 'user', 5, NULL, '::1', '2026-09-28 12:27:10'),
(117, 5, 'certificate.generated', 'certificate', 2, '{\"student_id\":6,\"type\":\"winner\"}', '::1', '2026-09-28 12:27:28'),
(118, 5, 'user.logout', 'user', 5, NULL, '::1', '2026-09-28 12:27:31'),
(119, 5, 'user.login', 'user', 5, NULL, '::1', '2026-09-28 12:28:13'),
(120, 5, 'user.logout', 'user', 5, NULL, '::1', '2026-09-28 12:28:22'),
(121, 6, 'user.login', 'user', 6, NULL, '::1', '2026-09-28 12:28:33'),
(122, 6, 'user.logout', 'user', 6, NULL, '::1', '2026-09-28 12:32:16'),
(123, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"test@test.com\",\"role\":\"student\",\"school_id\":3}', '::1', '2026-09-28 12:34:09'),
(124, 5, 'user.login', 'user', 5, NULL, '::1', '2026-09-28 12:35:12'),
(125, 5, 'certificate.generated', 'certificate', 3, '{\"student_id\":6,\"type\":\"runner_up\"}', '::1', '2026-09-28 12:35:38'),
(126, 5, 'user.logout', 'user', 5, NULL, '::1', '2026-09-28 12:35:40'),
(127, 6, 'user.login', 'user', 6, NULL, '::1', '2026-09-28 12:35:49'),
(128, 6, 'user.logout', 'user', 6, NULL, '::1', '2026-09-28 12:39:36'),
(129, 5, 'user.login', 'user', 5, NULL, '::1', '2026-09-28 12:39:47'),
(130, 5, 'certificate.generated', 'certificate', 4, '{\"student_id\":6,\"type\":\"runner_up\"}', '::1', '2026-09-28 12:40:02'),
(131, 5, 'user.logout', 'user', 5, NULL, '::1', '2026-09-28 12:40:04'),
(132, 6, 'user.login', 'user', 6, NULL, '::1', '2026-09-28 12:40:14'),
(133, 6, 'user.logout', 'user', 6, NULL, '::1', '2026-09-28 12:44:16'),
(134, 5, 'user.login', 'user', 5, NULL, '::1', '2026-09-28 12:44:29'),
(135, 5, 'certificate.generated', 'certificate', 5, '{\"student_id\":6,\"type\":\"runner_up\"}', '::1', '2026-09-28 12:44:45'),
(136, 5, 'user.logout', 'user', 5, NULL, '::1', '2026-09-28 12:44:48'),
(137, 6, 'user.login', 'user', 6, NULL, '::1', '2026-09-28 12:44:57'),
(138, 6, 'user.logout', 'user', 6, NULL, '::1', '2026-09-28 12:53:10'),
(139, 5, 'user.login', 'user', 5, NULL, '::1', '2026-09-28 12:53:28'),
(140, 5, 'certificate.generated', 'certificate', 6, '{\"student_id\":6,\"type\":\"winner\"}', '::1', '2026-09-28 12:53:41'),
(141, 5, 'user.logout', 'user', 5, NULL, '::1', '2026-09-28 12:53:47'),
(142, 6, 'user.login', 'user', 6, NULL, '::1', '2026-09-28 12:53:56'),
(143, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:d9ec:9d5c:bf10:d26f', '2026-09-28 11:17:39'),
(144, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:d9ec:9d5c:bf10:d26f', '2026-09-28 11:20:25'),
(145, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:d9ec:9d5c:bf10:d26f', '2026-09-28 11:31:06'),
(146, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:d9ec:9d5c:bf10:d26f', '2026-09-28 11:31:15'),
(147, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:d9ec:9d5c:bf10:d26f', '2026-09-28 11:31:27'),
(148, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c882:d9ec:9d5c:bf10:d26f', '2026-09-28 11:32:12'),
(149, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:d9ec:9d5c:bf10:d26f', '2026-09-28 11:32:41'),
(150, 1, 'user.logout', 'user', 1, NULL, '2405:201:a41a:c882:d9ec:9d5c:bf10:d26f', '2026-09-28 11:34:52'),
(151, 8, 'user.login', 'user', 8, NULL, '2405:201:a41a:c882:d9ec:9d5c:bf10:d26f', '2026-09-28 11:36:00'),
(152, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:51cd:58f0:1870:3b9d', '2026-09-29 06:16:51'),
(153, 7, 'user.login', 'user', 7, NULL, '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 06:30:10'),
(154, 7, 'user.logout', 'user', 7, NULL, '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 06:32:33'),
(155, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"mamtapathak051@gmail.com\",\"role\":\"teacher\",\"school_id\":4}', '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 06:35:06'),
(156, NULL, 'registration.completed', 'user', 12, '{\"role\":\"teacher\",\"school_id\":4}', '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 06:35:43'),
(157, 7, 'user.login', 'user', 7, NULL, '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 06:37:06'),
(158, 7, 'school.user_approved', 'user', 12, '{\"school_id\":4}', '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 06:37:26'),
(159, 7, 'user.logout', 'user', 7, NULL, '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 06:38:17'),
(160, 12, 'user.login', 'user', 12, NULL, '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 06:38:31'),
(161, 12, 'user.logout', 'user', 12, NULL, '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 06:42:51'),
(162, 8, 'user.login', 'user', 8, NULL, '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 06:43:39'),
(163, 8, 'user.logout', 'user', 8, NULL, '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 06:43:57'),
(164, 1, 'user.logout', 'user', 1, NULL, '2405:201:a41a:c882:51cd:58f0:1870:3b9d', '2026-09-29 07:00:25'),
(165, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:51cd:58f0:1870:3b9d', '2026-09-29 07:02:14'),
(166, 1, 'user.login', 'user', 1, NULL, '103.149.126.42', '2026-09-29 07:14:48'),
(167, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 07:22:42'),
(168, 4, 'user.logout', 'user', 4, NULL, '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 07:24:36'),
(169, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:764c:4c7f:196c:cbff', '2026-09-29 08:03:05'),
(170, 1, 'user.logout', 'user', 1, NULL, '2405:201:a41a:c882:764c:4c7f:196c:cbff', '2026-09-29 08:10:16'),
(171, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:764c:4c7f:196c:cbff', '2026-09-29 08:10:35'),
(172, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:764c:4c7f:196c:cbff', '2026-09-29 08:11:08'),
(173, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:764c:4c7f:196c:cbff', '2026-09-29 08:11:43'),
(174, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 08:56:17'),
(175, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:9e56:dbaa:c7db:7762', '2026-09-29 10:12:35'),
(176, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c882:9e56:dbaa:c7db:7762', '2026-09-29 10:13:25'),
(177, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 10:30:01'),
(178, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 10:33:37'),
(179, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 10:33:47'),
(180, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 10:34:11'),
(181, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 10:34:23'),
(182, 5, 'certificate.generated', 'certificate', 7, '{\"student_id\":6,\"type\":\"winner\"}', '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 10:35:50'),
(183, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 10:36:10'),
(184, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 10:36:18'),
(185, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 10:40:51'),
(186, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 10:41:11'),
(187, 1, 'school.created', 'school', 8, '{\"name\":\"AJ\",\"code\":\"A-5A24\"}', '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 10:50:06'),
(188, 1, 'school.created', 'school', 9, '{\"name\":\"AIVERSE\",\"code\":\"A-176B\"}', '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 10:58:45'),
(189, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 06:16:48'),
(190, 1, 'school.archived', 'school', 8, '{\"users_suspended\":1}', '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 06:17:06'),
(191, 1, 'school.restored', 'school', 8, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 06:18:34'),
(192, 1, 'user.logout', 'user', 1, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 06:37:42'),
(193, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 06:38:19'),
(194, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 06:39:48'),
(195, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 06:48:25'),
(196, 6, 'counseling.created', 'counseling', 1, '{\"type\":\"career\",\"school\":3}', '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 06:49:16'),
(197, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 06:49:19'),
(198, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 06:49:50'),
(199, 4, 'counseling.assigned', 'counseling', 1, '{\"teacher\":5}', '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 06:59:35'),
(200, 4, 'user.logout', 'user', 4, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 07:02:06'),
(201, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 07:02:19'),
(202, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 07:11:18'),
(203, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 07:11:27'),
(204, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 08:51:23'),
(205, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:10:52'),
(206, 4, 'user.logout', 'user', 4, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:11:28'),
(207, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:11:38'),
(208, 5, 'attendance.marked', 'batch', 1, '{\"date\":\"2026-09-30\",\"count\":1}', '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:11:50'),
(209, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:12:07'),
(210, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:12:16'),
(211, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:12:47'),
(212, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:13:00'),
(213, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:1c47:73a5:e68f:4064', '2026-09-30 09:14:41'),
(214, 1, 'user.logout', 'user', 1, NULL, '2405:201:a41a:c882:1c47:73a5:e68f:4064', '2026-09-30 09:16:26'),
(215, 1, 'user.logout', 'user', 1, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:22:39'),
(216, 14, 'user.login', 'user', 14, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:23:17'),
(217, 14, 'lesson.created', 'lesson', 3, '{\"title\":\"REAL NUMBERS\",\"school_id\":9,\"batch_id\":null,\"attachments\":1}', '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:24:44'),
(218, 14, 'user.logout', 'user', 14, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:25:03'),
(219, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:39:41'),
(220, 1, 'user.logout', 'user', 1, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:39:59'),
(221, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 10:32:05'),
(222, 1, 'user.logout', 'user', 1, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 11:07:19'),
(223, 14, 'user.login', 'user', 14, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-10-01 04:42:50'),
(224, 14, 'password.reset_requested', 'user', 14, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-10-01 04:43:36'),
(225, 14, 'password.reset_requested', 'user', 14, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-10-01 04:51:26'),
(226, 14, 'password.reset_completed', 'user', 14, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-10-01 04:52:02'),
(227, 14, 'user.login', 'user', 14, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-10-01 04:52:23'),
(228, 1, 'user.login', 'user', 1, NULL, '2001:67c:2628:647:32:604:0:8e', '2026-10-01 07:25:50'),
(229, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:51be:dafd:9277:9db4', '2026-10-03 06:10:11'),
(230, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 07:11:57'),
(231, 1, 'user.logout', 'user', 1, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 07:12:03'),
(232, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 07:12:18'),
(233, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:ad7b:fdc:1cf:3c33', '2026-10-05 07:25:19'),
(234, 6, 'chess.calibration_completed', 'user', 6, '{\"final_rating\":1729,\"tier\":\"master\"}', '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 08:03:18'),
(235, 6, 'chess.calibration_completed', 'user', 6, '{\"final_rating\":1915,\"tier\":\"master\"}', '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 08:04:31'),
(236, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c015:ad61:e99a:893c:82d5', '2026-10-05 08:18:59'),
(237, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 09:13:03'),
(238, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:ad7b:fdc:1cf:3c33', '2026-10-05 09:27:53'),
(239, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 10:23:31'),
(240, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 10:24:13'),
(241, 1, 'user.logout', 'user', 1, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 10:25:09'),
(242, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 10:26:55'),
(243, 4, 'password.changed', 'user', 4, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 10:27:48'),
(244, 4, 'user.logout', 'user', 4, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 10:27:55'),
(245, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 10:28:06'),
(246, 5, 'chess.live_session_started', 'chess_session', 1, '{\"type\":\"1-on-1\",\"student\":null,\"batch\":null}', '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 10:28:26'),
(247, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 10:30:08'),
(248, 5, 'chess.live_session_ended', 'chess_session', 1, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 10:31:46'),
(249, 5, 'chess.live_session_started', 'chess_session', 2, '{\"type\":\"1-on-1\",\"student\":null,\"batch\":null}', '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 11:00:01'),
(250, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 11:00:26'),
(251, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 11:00:29'),
(252, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 11:00:39'),
(253, 5, 'chess.live_session_started', 'chess_session', 3, '{\"type\":\"1-on-1\",\"student\":6,\"batch\":null}', '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 11:01:04'),
(254, 5, 'chess.live_session_ended', 'chess_session', 3, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 11:01:55'),
(255, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 11:02:05'),
(256, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 11:02:09'),
(257, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:8695:9db9:dec3:d773', '2026-10-06 05:51:48'),
(258, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:a4cf:acae:67d6:746c', '2026-10-06 06:02:05'),
(259, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c882:a4cf:acae:67d6:746c', '2026-10-06 06:08:25'),
(260, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"swiftcompass@mailinator.com\",\"role\":\"student\",\"school_id\":3}', '2405:201:a41a:c882:a4cf:acae:67d6:746c', '2026-10-06 06:09:43'),
(261, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:a4cf:acae:67d6:746c', '2026-10-06 06:12:20'),
(262, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:8695:9db9:dec3:d773', '2026-10-06 07:57:16'),
(263, 1, 'user.logout', 'user', 1, NULL, '2405:201:a41a:c882:8695:9db9:dec3:d773', '2026-10-06 08:00:46'),
(264, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:8695:9db9:dec3:d773', '2026-10-06 08:01:13'),
(265, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:8695:9db9:dec3:d773', '2026-10-06 08:02:47'),
(266, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c882:8695:9db9:dec3:d773', '2026-10-06 08:03:34'),
(267, 4, 'user.logout', 'user', 4, NULL, '2405:201:a41a:c882:8695:9db9:dec3:d773', '2026-10-06 08:06:24');

-- --------------------------------------------------------

--
-- Table structure for table `batches`
--

CREATE TABLE `batches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `class_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `class_level` varchar(50) DEFAULT NULL,
  `teacher_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `capacity` int(10) UNSIGNED DEFAULT 50,
  `status` enum('active','archived') DEFAULT 'active',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `batches`
--

INSERT INTO `batches` (`id`, `uuid`, `school_id`, `class_id`, `name`, `description`, `subject`, `class_level`, `teacher_id`, `cover_image`, `capacity`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, '7972d24f-2fd6-44c5-b8e6-b07e40f517a9', NULL, NULL, 'science class 6', NULL, 'science', '6', 5, NULL, 50, 'active', 5, '2026-09-25 15:20:56', '2026-09-25 15:20:56'),
(2, 'a5df7e98-d36b-4b9d-9863-fcfdf06aaae3', 3, NULL, 'Chess - Elite Rooks (Master)', 'Rising Rooks Chess Academy batch for Master tier (1501-2000 Elo).', 'Chess', 'Master', NULL, NULL, 50, 'active', NULL, '2026-10-05 08:03:18', '2026-10-05 08:03:18');

-- --------------------------------------------------------

--
-- Table structure for table `batch_activity_assignments`
--

CREATE TABLE `batch_activity_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `batch_id` bigint(20) UNSIGNED NOT NULL,
  `activity_id` bigint(20) UNSIGNED NOT NULL,
  `assigned_by` bigint(20) UNSIGNED NOT NULL,
  `scheduled_date` date DEFAULT NULL,
  `scheduled_time` time DEFAULT NULL,
  `duration_minutes` int(10) UNSIGNED DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('scheduled','in_progress','completed','cancelled') DEFAULT 'scheduled',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `batch_activity_assignments`
--

INSERT INTO `batch_activity_assignments` (`id`, `batch_id`, `activity_id`, `assigned_by`, `scheduled_date`, `scheduled_time`, `duration_minutes`, `instructions`, `due_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 5, '2026-09-26', '13:30:00', 30, NULL, '2026-09-27', 'scheduled', '2026-09-25 19:34:46', '2026-09-25 19:34:46');

-- --------------------------------------------------------

--
-- Table structure for table `batch_assignment_acknowledgments`
--

CREATE TABLE `batch_assignment_acknowledgments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `assignment_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `acknowledged_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `batch_students`
--

CREATE TABLE `batch_students` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `batch_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `joined_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `batch_students`
--

INSERT INTO `batch_students` (`id`, `batch_id`, `student_user_id`, `joined_at`) VALUES
(1, 1, 6, '2026-09-25 15:21:14'),
(2, 2, 6, '2026-10-05 08:03:18');

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `type` enum('participation','achievement','completion','winner','merit') DEFAULT 'participation',
  `certificate_type` enum('winner','runner_up','participant') DEFAULT 'participant',
  `issued_for` enum('activity','competition','course','lesson','other') DEFAULT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `certificate_url` varchar(500) DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `metadata` longtext DEFAULT NULL CHECK (json_valid(`metadata`)),
  `issued_by` bigint(20) UNSIGNED DEFAULT NULL,
  `issued_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`id`, `uuid`, `student_user_id`, `title`, `type`, `certificate_type`, `issued_for`, `reference_id`, `certificate_url`, `file_path`, `metadata`, `issued_by`, `issued_at`) VALUES
(1, 'f286af20-c3cf-4fc9-ae3a-54485cc4af07', 6, 'Certificate of Achievement', 'winner', 'winner', 'activity', NULL, '/uploads/certificates/cert_winner_6_20260928_122213_4e3686.pdf', '/uploads/certificates/cert_winner_6_20260928_122213_4e3686.pdf', '{\"event_title\":\"QUIZWIZ\",\"rank\":\"1st Place\",\"date_completed\":\"28 Sep 2026\",\"school_name\":\"Test SCHOOL\"}', 5, '2026-09-28 12:22:13'),
(2, 'c094a034-f911-4edb-b83b-a1c278cad8b8', 6, 'Certificate of Achievement', 'winner', 'winner', 'activity', NULL, '/uploads/certificates/cert_winner_6_20260928_122728_528795.pdf', '/uploads/certificates/cert_winner_6_20260928_122728_528795.pdf', '{\"event_title\":\"QUIZWIZ\",\"rank\":\"1st Place\",\"date_completed\":\"28 Sep 2026\",\"school_name\":\"Test SCHOOL\"}', 5, '2026-09-28 12:27:28'),
(3, '13dc8cd4-f61a-4236-a3d8-ff34e1feb5db', 6, 'Certificate of Runner-up', 'winner', 'runner_up', 'activity', NULL, '/uploads/certificates/cert_runner_up_6_20260928_123538_0b1f4b.pdf', '/uploads/certificates/cert_runner_up_6_20260928_123538_0b1f4b.pdf', '{\"event_title\":\"QUIZWIZ\",\"rank\":\"2nd Place (Runner-up)\",\"date_completed\":\"28 Sep 2026\",\"school_name\":\"Test SCHOOL\"}', 5, '2026-09-28 12:35:38'),
(4, 'ea59593c-eea7-46de-8969-a6eb68a84a44', 6, 'Certificate of Runner-up', 'winner', 'runner_up', 'activity', NULL, '/uploads/certificates/cert_runner_up_6_20260928_124002_d1f121.pdf', '/uploads/certificates/cert_runner_up_6_20260928_124002_d1f121.pdf', '{\"event_title\":\"QUIZWIZ\",\"rank\":\"3th Place\",\"date_completed\":\"28 Sep 2026\",\"school_name\":\"Test SCHOOL\"}', 5, '2026-09-28 12:40:02'),
(5, 'c813160c-c7e4-4344-967b-815c69dc2650', 6, 'Certificate of Runner-up', 'winner', 'runner_up', 'activity', NULL, '/uploads/certificates/cert_runner_up_6_20260928_124445_44b06b.pdf', '/uploads/certificates/cert_runner_up_6_20260928_124445_44b06b.pdf', '{\"event_title\":\"QUIZWIZ\",\"rank\":\"3th Place\",\"date_completed\":\"28 Sep 2026\",\"school_name\":\"Test SCHOOL\"}', 5, '2026-09-28 12:44:45'),
(6, '266132c6-41a8-46bb-938f-7733770b07dd', 6, 'Certificate of Achievement', 'winner', 'winner', 'activity', NULL, '/uploads/certificates/cert_winner_6_20260928_125341_eb4364.pdf', '/uploads/certificates/cert_winner_6_20260928_125341_eb4364.pdf', '{\"event_title\":\"QUIZWIZ\",\"rank\":\"1st Place\",\"date_completed\":\"28 Sep 2026\",\"school_name\":\"Test SCHOOL\"}', 5, '2026-09-28 12:53:41'),
(7, '347a4d3c-3d71-43ba-8430-f5009361c30e', 6, 'Certificate of Achievement', 'winner', 'winner', 'activity', NULL, '/uploads/certificates/cert_winner_6_20260929_103550_255bbe.pdf', '/uploads/certificates/cert_winner_6_20260929_103550_255bbe.pdf', '{\"event_title\":\"QUIZWIZ\",\"rank\":\"1st Place\",\"date_completed\":\"29 Sep 2026\",\"school_name\":\"Test SCHOOL\"}', 5, '2026-09-29 10:35:50');

-- --------------------------------------------------------

--
-- Table structure for table `chapters`
--

CREATE TABLE `chapters` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `class_level` varchar(50) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(10) UNSIGNED DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chess_challenges`
--

CREATE TABLE `chess_challenges` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `challenger_user_id` bigint(20) UNSIGNED NOT NULL,
  `challenged_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `challenger_color` enum('white','black','random') NOT NULL DEFAULT 'random',
  `time_control_minutes` tinyint(3) UNSIGNED NOT NULL DEFAULT 10,
  `increment_seconds` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `rated` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('pending','accepted','declined','cancelled','expired') NOT NULL DEFAULT 'pending',
  `game_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chess_coach_assignments`
--

CREATE TABLE `chess_coach_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `teacher_user_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_tier` enum('learner','rookie','intermediate','master','grandmaster','all') DEFAULT 'all',
  `notes` text DEFAULT NULL,
  `assigned_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chess_coach_assignments`
--

INSERT INTO `chess_coach_assignments` (`id`, `school_id`, `teacher_user_id`, `student_user_id`, `assigned_tier`, `notes`, `assigned_by`, `created_at`) VALUES
(1, 3, 5, 6, 'all', 'endgame', 4, '2026-10-05 10:27:29');

-- --------------------------------------------------------

--
-- Table structure for table `chess_curriculum`
--

CREATE TABLE `chess_curriculum` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tier` enum('learner','rookie','intermediate','master','grandmaster') NOT NULL,
  `category` enum('opening','middlegame','endgame','tactics') NOT NULL,
  `title` varchar(200) NOT NULL,
  `subtitle` varchar(300) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `initial_fen` varchar(150) NOT NULL DEFAULT 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1',
  `solution_pgn` text DEFAULT NULL,
  `key_concepts` longtext DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chess_games`
--

CREATE TABLE `chess_games` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `white_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `black_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `batch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `game_type` enum('calibration','bot','pvp','promotion','relegation','live_coaching','tournament') NOT NULL DEFAULT 'bot',
  `bot_difficulty` tinyint(3) UNSIGNED DEFAULT NULL,
  `bot_rating_target` int(10) UNSIGNED DEFAULT NULL,
  `initial_fen` varchar(150) NOT NULL DEFAULT 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1',
  `current_fen` varchar(150) NOT NULL DEFAULT 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1',
  `pgn` longtext DEFAULT NULL,
  `moves_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `result` enum('1-0','0-1','1/2-1/2','*') NOT NULL DEFAULT '*',
  `winner_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `termination` varchar(100) DEFAULT NULL,
  `white_rating_before` int(10) UNSIGNED DEFAULT NULL,
  `black_rating_before` int(10) UNSIGNED DEFAULT NULL,
  `white_rating_change` int(11) DEFAULT 0,
  `black_rating_change` int(11) DEFAULT 0,
  `white_accuracy` decimal(5,2) DEFAULT NULL,
  `black_accuracy` decimal(5,2) DEFAULT NULL,
  `coach_notes` text DEFAULT NULL,
  `status` enum('in_progress','completed','aborted') NOT NULL DEFAULT 'in_progress',
  `created_at` datetime DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chess_games`
--

INSERT INTO `chess_games` (`id`, `uuid`, `white_user_id`, `black_user_id`, `school_id`, `batch_id`, `game_type`, `bot_difficulty`, `bot_rating_target`, `initial_fen`, `current_fen`, `pgn`, `moves_count`, `result`, `winner_user_id`, `termination`, `white_rating_before`, `black_rating_before`, `white_rating_change`, `black_rating_change`, `white_accuracy`, `black_accuracy`, `coach_notes`, `status`, `created_at`, `completed_at`) VALUES
(1, '4c2c6846-d222-49b4-9d4e-313b90c1c743', 6, NULL, 3, NULL, 'calibration', 1, 400, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', '6k1/2p5/p1p2R1Q/7p/1P6/2BP4/1Pb3PP/6K1 w - - 5 27', '1. e4 e5 2. Nf3 a6 3. Bc4 Bb4 4. a3 Nf6 5. axb4 Nxe4 6. Nc3 O-O 7. O-O g5 8. d3 Nxf2 9. Rxf2 Qf6 10. Bxg5 e4 11. Bxf6 exf3 12. Rxf3 d5 13. Bxd5 h5 14. Qf1 Nc6 15. Bxc6 bxc6 16. Ne4 Be6 17. Ng5 Bb3 18. Bc3 Bxc2 19. Nxf7 Rxf7 20. Rxf7 Rc8 21. Qf2 Bb3 22. Rf1 Ra8 23. Rf8+ Rxf8 24. Qxf8+ Kh7 25. Rf6 Bc2 26. Qh6+ Kg8', 52, '1-0', 6, 'White wins by Checkmate!', 400, 400, 0, 0, NULL, NULL, NULL, 'completed', '2026-10-05 07:39:36', '2026-10-05 07:41:08'),
(2, '1f0df15d-2798-4a3a-bf5c-d4d1b2445633', 6, NULL, 3, NULL, 'calibration', 3, 750, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'k5R1/3Q4/1p6/4P3/8/5N2/1PP2PK1/5R2 b - - 4 27', '1. e4 d5 2. exd5 Qxd5 3. Nc3 Qxa2 4. Rxa2 a5 5. Bb5+ Kd8 6. d4 Ra6 7. Bxa6 Nxa6 8. Nf3 h5 9. O-O e5 10. dxe5+ Ke8 11. h3 Bxh3 12. Bg5 Bxg2 13. Kxg2 Be7 14. Bxe7 Kxe7 15. Rxa5 Kf8 16. Qd3 Nf6 17. Nb5 Ne4 18. Qxe4 b6 19. Rxa6 f5 20. Qxf5+ Ke8 21. Nxc7+ Kd8 22. Ra8+ Kxc7 23. Rxh8 g5 24. Rxh5 Kc6 25. Rxg5 Kb7 26. Qd7+ Ka8 27. Rg8#', 53, '1-0', 6, 'White wins by Checkmate!', 500, 750, 0, 0, NULL, NULL, NULL, 'completed', '2026-10-05 07:41:17', '2026-10-05 07:42:39'),
(3, '8617bb0f-30f7-4f69-a8f9-e154531a04f1', 6, NULL, 3, NULL, 'calibration', 5, 1100, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', '4r3/2p3p1/4B2k/pp2R3/6Q1/3P1N2/PPP2PPP/2K4R w - - 2 27', '1. Nc3 e5 2. e4 d5 3. exd5 Qxd5 4. Nxd5 e4 5. Bc4 Na6 6. Qe2 Kd8 7. Qxe4 Nb8 8. d3 Bb4+ 9. Nxb4 Kd7 10. Qd4+ Ke8 11. Qd5 Ne7 12. Qxf7+ Kd8 13. Bg5 a6 14. Bxe7+ Kd7 15. Qe6+ Ke8 16. Qxc8+ Kxe7 17. Qxh8 Kf6 18. Qxh7 Nc6 19. Qh4+ Kg6 20. Be6 Nxb4 21. Qxb4 Rc8 22. Qg4+ Kh6 23. Nf3 b6 24. O-O-O b5 25. Rde1 a5 26. Re5 Re8', 52, '1-0', 6, 'White wins by Checkmate!', 725, 1100, 0, 0, NULL, NULL, NULL, 'completed', '2026-10-05 07:43:26', '2026-10-05 07:47:12'),
(4, '5cb06c18-4e6d-483f-b8d1-6f77becf5cf9', 6, NULL, 3, NULL, 'calibration', 7, 1500, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', NULL, 0, '*', NULL, NULL, 1013, 1500, 0, 0, NULL, NULL, NULL, 'in_progress', '2026-10-05 07:47:24', NULL),
(5, '9800c6b3-645d-4b9d-8821-cce6abb9d0a9', 6, NULL, 3, NULL, 'calibration', 7, 1500, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', '2k5/R3R3/8/4N2P/1p6/1P6/7P/2B3K1 w - - 1 31', '1. e4 d5 2. exd5 Qxd5 3. Nc3 Qxd2+ 4. Qxd2 h6 5. Bc4 g6 6. Nf3 e5 7. Nxe5 a6 8. O-O Be7 9. Re1 Bf5 10. a3 Bxc2 11. Qxc2 Bxa3 12. Rxa3 f5 13. Nxg6+ Ne7 14. Nxh8 f4 15. g3 fxg3 16. fxg3 h5 17. Ng6 a5 18. Rxe7+ Kd8 19. Qd3+ Kc8 20. Qd7+ Nxd7 21. Nd5 h4 22. gxh4 Ne5 23. Nxe5 c6 24. Nb6+ Kb8 25. Nxa8 Kxa8 26. h5 b5 27. Bxb5 cxb5 28. Rxa5+ Kb8 29. Raa7 b4 30. b3 Kc8', 60, '1-0', 6, 'White wins by Checkmate!', 1013, 1500, 0, 0, NULL, NULL, NULL, 'completed', '2026-10-05 08:00:18', '2026-10-05 08:01:41'),
(6, 'dc612449-8b2e-4a8e-8f41-b521734ef713', 6, NULL, 3, NULL, 'calibration', 9, 1900, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rn2BR1k/p7/1p4Q1/2p5/8/2N1B3/PPP2PP1/2KR4 b - - 11 25', '1. e4 d5 2. exd5 Qxd5 3. Nc3 Qxd2+ 4. Qxd2 e5 5. Nf3 e4 6. Nd4 f5 7. Nxf5 Bxf5 8. Qf4 Bg4 9. Qxg4 e3 10. Bb5+ Kf7 11. Bxe3 Nh6 12. Qg5 Bd6 13. O-O-O Bxh2 14. Rxh2 b6 15. Bd4 Ke6 16. Qxg7 Re8 17. Qxh6+ Ke7 18. Qxh7+ Kd6 19. Bxe8 c5 20. Be3+ Ke5 21. Qh5+ Ke6 22. Rh4 Kf6 23. Rf4+ Kg7 24. Qg6+ Kh8 25. Rf8#', 49, '1-0', 6, 'White wins by Checkmate!', 1357, 1900, 0, 0, NULL, NULL, NULL, 'completed', '2026-10-05 08:01:45', '2026-10-05 08:03:18'),
(7, '12844d83-39ea-488e-a7fc-ce370594e686', 6, NULL, 3, NULL, 'calibration', 9, 1900, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', '8/8/p3R3/3Q4/2p2K1k/7P/PPP2P2/R7 w - - 5 31', '1. e4 d5 2. exd5 Qxd5 3. Nc3 Qxd2+ 4. Qxd2 e5 5. Bc4 e4 6. Nxe4 b5 7. Nf3 bxc4 8. O-O Bg4 9. h3 Bxf3 10. gxf3 a6 11. Qd4 f5 12. Ng3 Bd6 13. Nxf5 Bh2+ 14. Kxh2 g6 15. Qxh8 gxf5 16. Qxg8+ Ke7 17. Qxh7+ Kf6 18. Qxc7 Nd7 19. Qxd7 Ke5 20. Re1+ Kf6 21. Bg5+ Kxg5 22. f4+ Kxf4 23. Kg2 Rg8+ 24. Kh2 Rg2+ 25. Kxg2 Kg5 26. Kg3 f4+ 27. Kf3 Kh6 28. Kxf4 Kg6 29. Re6+ Kh5 30. Qd5+ Kh4', 60, '1-0', 6, 'White wins by Checkmate!', 1729, 1900, 0, 0, NULL, NULL, NULL, 'completed', '2026-10-05 08:03:20', '2026-10-05 08:04:31'),
(8, '08a8401e-7e9d-4cbc-a116-82e6e4d2b0d0', 6, NULL, 3, NULL, 'calibration', 9, 1900, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', NULL, 0, '*', NULL, NULL, 1915, 1900, 0, 0, NULL, NULL, NULL, 'in_progress', '2026-10-05 08:04:33', NULL),
(9, 'dba8a094-2434-4cd9-a3f5-2ebf40d11692', 6, NULL, 3, NULL, 'bot', 3, 800, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rnbqkbnr/pppp1ppp/8/4p3/4P3/8/PPPP1PPP/RNBQKBNR w KQkq e6 0 2', '1. e4 e5', 2, '*', NULL, NULL, 1915, 800, 0, 0, NULL, NULL, NULL, 'in_progress', '2026-10-05 08:10:28', NULL),
(10, '5725158b-eb64-46cc-87dc-000473e1d7a7', 6, NULL, 3, NULL, 'bot', 3, 800, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', '4Kqk1/pp3ppp/8/8/3r4/8/8/8 w - - 4 41', '1. e4 c5 2. f3 Nc6 3. c4 Nf6 4. d3 e5 5. Bg5 Be7 6. Bxf6 Bxf6 7. Ne2 Qb6 8. g4 Qxb2 9. Nd2 Bh4+ 10. Ng3 O-O 11. Kf2 d6 12. Kg2 Bxg3 13. Kxg3 Nd4 14. h4 Re8 15. g5 Be6 16. f4 exf4+ 17. Kxf4 Rad8 18. Bh3 Nc2 19. Rb1 Qe5+ 20. Kf3 Nd4+ 21. Ke3 Qg3+ 22. Nf3 Bxh3 23. Rb2 Bc8 24. Qe2 Nxe2 25. Rxe2 Bg4 26. Rf2 d5 27. cxd5 Rxd5 28. Rg1 Rxd3+ 29. Kxd3 Qxf2 30. Rxg4 Qxf3+ 31. Kc4 Qxg4 32. Kxc5 Rxe4 33. a3 Qxh4 34. g6 Qg5+ 35. Kd6 Qxg6+ 36. Kc7 Qb6+ 37. Kd7 Qc5 38. a4 Rxa4 39. Kd8 Rd4+ 40. Ke8 Qf8#', 80, '0-1', NULL, 'Black wins by Checkmate!', 1915, 800, -32, 0, NULL, NULL, NULL, 'completed', '2026-10-05 08:19:07', '2026-10-05 08:28:07'),
(11, 'f15d9fe6-60e2-472a-854d-f356a96f9f8b', 6, NULL, 3, NULL, 'bot', 3, 800, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', NULL, 0, '*', NULL, NULL, 1883, 800, 0, 0, NULL, NULL, NULL, 'in_progress', '2026-10-05 08:28:18', NULL),
(12, 'dd927007-a561-4cfd-afb3-16be18b253d3', 6, NULL, 3, NULL, 'bot', 10, 2700, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', '1Qn2k1r/pp3ppp/8/1B6/8/R7/1PP2PPP/2B1R1K1 w - - 1 18', '1. e4 d5 2. exd5 Qxd5 3. Nc3 Qxd2+ 4. Qxd2 e5 5. Nf3 e4 6. Nxe4 Nd7 7. Bb5 Rb8 8. O-O Ra8 9. Re1 Rb8 10. a3 Bxa3 11. Rxa3 Ra8 12. Ne5 Rb8 13. Nxd7 Bxd7 14. Qxd7+ Kf8 15. Nd6 cxd6 16. Qxd6+ Ne7 17. Qxb8+ Nc8', 34, '1-0', 6, 'White wins by Checkmate!', 1883, 2700, 32, 0, NULL, NULL, NULL, 'completed', '2026-10-05 08:56:19', '2026-10-05 08:57:16'),
(13, 'd62e4d35-9538-4d08-afe3-107c74e5320a', 6, NULL, 3, NULL, 'bot', 8, 2000, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', NULL, 0, '*', NULL, NULL, 1915, 2000, 0, 0, NULL, NULL, NULL, 'in_progress', '2026-10-05 09:02:40', NULL),
(14, '37b7e5e3-4058-4890-9466-587a327c88ff', 6, NULL, 3, NULL, 'bot', 8, 2000, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', '6n1/2Q5/p5pk/2p3N1/2B4p/P6P/P4PP1/R1B1R1K1 w - - 3 25', '1. e4 d5 2. exd5 Qxd5 3. Nc3 Qxd2+ 4. Qxd2 g6 5. Nf3 e5 6. Nxe5 c6 7. Bc4 Ba3 8. bxa3 Ne7 9. O-O Ng8 10. Qg5 Bf5 11. Re1 Bxc2 12. h3 Be4 13. Nxe4 h5 14. Nd6+ Kf8 15. Qd8+ Kg7 16. Nexf7 c5 17. Nxh8 Kxh8 18. Nf7+ Kh7 19. Ng5+ Kg7 20. Qc7+ Kh6 21. Qxb7 a6 22. Qxa8 h4 23. Qxb8 Kg7 24. Qc7+ Kh6', 48, '0-1', NULL, 'Flag Fall — You ran out of time!', 1915, 2000, -12, 0, NULL, NULL, NULL, 'completed', '2026-10-05 09:02:48', '2026-10-05 09:06:02'),
(15, 'e9144171-dffe-4611-b520-d8f303a702f1', 6, NULL, 3, NULL, 'bot', 10, 2700, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', '2r1k2r/ppp1Q1pp/8/8/8/2N2N2/PPP2PPP/2KRR3 b - - 0 16', '1. e4 d5 2. exd5 Qxd5 3. Nc3 Qxd2+ 4. Qxd2 e5 5. Bc4 e4 6. Qe3 Nd7 7. Qxe4+ Ne5 8. Qxe5+ Be6 9. Bxe6 fxe6 10. Qxe6+ Kd8 11. Bg5+ Be7 12. O-O-O+ Ke8 13. Nf3 Rb8 14. Rhe1 Rc8 15. Bxe7 Nxe7 16. Qxe7#', 31, '1-0', 6, 'White wins by Checkmate!', 1903, 2700, 32, 0, NULL, NULL, NULL, 'completed', '2026-10-05 09:06:53', '2026-10-05 09:07:32'),
(16, '50abb893-5f82-4cb0-89e0-aac2e9a6799e', 6, NULL, 3, NULL, 'bot', 3, 750, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', NULL, 0, '*', NULL, NULL, 1935, 750, 0, 0, NULL, NULL, NULL, 'in_progress', '2026-10-05 09:10:31', NULL),
(17, 'aaa95c4d-bb5a-4b12-bf0f-dd3a4b1f5a5e', 6, NULL, 3, NULL, 'bot', 8, 2000, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', NULL, 0, '*', NULL, NULL, 1935, 2000, 0, 0, NULL, NULL, NULL, 'in_progress', '2026-10-05 09:28:15', NULL),
(18, '4bf9aa7f-3aaa-4d84-a251-f7285b673381', 6, NULL, 3, NULL, 'bot', 10, 2700, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', NULL, 0, '*', NULL, NULL, 1935, 2700, 0, 0, NULL, NULL, NULL, 'in_progress', '2026-10-05 09:43:15', NULL),
(19, 'c1524e3c-f508-455c-bd8c-e04fd06f6afc', 6, NULL, 3, NULL, 'bot', 10, 2700, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', '2k5/Q7/8/5B2/8/5N2/RBP2PPP/3R2K1 b - - 10 32', '1. d4 d5 2. Nf3 e5 3. dxe5 Bb4+ 4. Nc3 Bxc3+ 5. bxc3 d4 6. cxd4 Qxd4 7. Qxd4 Nd7 8. e4 Nxe5 9. Qxe5+ Be6 10. Bb5+ Kf8 11. O-O Bxa2 12. Rxa2 Rb8 13. Qxc7 Rc8 14. Qxc8+ Ke7 15. Ba3+ Kf6 16. Qd8+ Ne7 17. Qxh8 Nd5 18. exd5 a6 19. Bd3 b6 20. Qxh7 g6 21. Bb2+ Ke7 22. d6+ Kxd6 23. Re1 Kd5 24. Qxf7+ Kc6 25. Qxg6+ Kd5 26. Qxb6 a5 27. Qxa5+ Kc6 28. Be4+ Kd7 29. Rd1+ Kc8 30. Qc5+ Kb8 31. Qa7+ Kc8 32. Bf5#', 63, '1-0', 6, 'White wins by Checkmate!', 1935, 2700, 32, 0, NULL, NULL, NULL, 'completed', '2026-10-05 09:43:21', '2026-10-05 09:45:01'),
(20, '51ae1f4c-d995-44f6-8e70-cd54868deb12', NULL, 6, 3, NULL, 'bot', 10, 2700, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', NULL, 0, '*', NULL, NULL, 2700, 1967, 0, 0, NULL, NULL, NULL, 'in_progress', '2026-10-05 09:45:18', NULL),
(21, '88dd10f7-6358-43a3-abec-ca27ed2bb14f', NULL, 6, 3, NULL, 'bot', 10, 2700, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', NULL, 0, '*', NULL, NULL, 2700, 1967, 0, 0, NULL, NULL, NULL, 'in_progress', '2026-10-05 09:45:21', NULL),
(22, 'cc83272a-b415-412a-af70-1540b28be5b4', NULL, 6, 3, NULL, 'bot', 10, 2700, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'r2q3k/bR3Q2/8/4P1pp/3P4/7P/5PP1/2BR2K1 w - - 1 27', '1. c4 e5 2. Nc3 Nc6 3. Nf3 Nf6 4. a3 d5 5. cxd5 Nxd5 6. e4 Nxc3 7. bxc3 Bg4 8. Rb1 Bc5 9. h3 Bxf3 10. Qxf3 O-O 11. Bc4 Qe7 12. a4 a6 13. O-O b5 14. Bd5 Na5 15. Bxa8 Rxa8 16. axb5 axb5 17. Rxb5 c6 18. Rb1 Nc4 19. Rd1 Nd6 20. d4 exd4 21. cxd4 Ba7 22. e5 Nc4 23. Qxc6 Qd8 24. Qxc4 h5 25. Rb7 g5 26. Qxf7+ Kh8', 52, '1-0', NULL, 'White wins by Checkmate!', 2700, 1967, 0, 0, NULL, NULL, NULL, 'completed', '2026-10-05 10:09:28', '2026-10-05 10:12:50'),
(23, '3d019082-01c4-4ed3-802d-cbe9085c82c4', NULL, 6, 3, NULL, 'bot', 1, 100, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'r2qkb1r/pp3ppp/4p3/np1pNb1Q/3P1B2/P1N1P2P/1PP2PP1/R3K2R w KQkq - 0 12', '1. d4 d5 2. Bf4 Nc6 3. Nf3 Nf6 4. a3 Bf5 5. Ne5 Na5 6. e3 Qd7 7. h3 Qd8 8. Nc3 e6 9. Be2 Nh5 10. Bb5+ c6 11. Qxh5 cxb5', 22, '1-0', NULL, 'White wins by Checkmate!', 100, 1967, 0, -32, NULL, NULL, NULL, 'completed', '2026-10-05 10:13:59', '2026-10-05 10:16:18'),
(24, '1638daa6-d479-4baa-8ee1-6067811d3919', 5, NULL, 3, NULL, 'calibration', 1, 100, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', NULL, 0, '*', NULL, NULL, 400, 100, 0, 0, NULL, NULL, NULL, 'in_progress', '2026-10-05 10:59:42', NULL),
(25, '8576ec36-9990-42e9-b1f1-49438d66636c', 6, NULL, 3, NULL, 'bot', 1, 100, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', NULL, 0, '*', NULL, NULL, 1935, 100, 0, 0, NULL, NULL, NULL, 'in_progress', '2026-10-06 07:49:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `chess_live_sessions`
--

CREATE TABLE `chess_live_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `teacher_user_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `batch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `session_type` enum('1-on-1','batch_class') NOT NULL DEFAULT '1-on-1',
  `current_fen` varchar(150) NOT NULL DEFAULT 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1',
  `pgn` longtext DEFAULT NULL,
  `orientation` enum('white','black') NOT NULL DEFAULT 'white',
  `annotations_json` longtext DEFAULT NULL CHECK (json_valid(`annotations_json`)),
  `teacher_turn` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('scheduled','live','ended') NOT NULL DEFAULT 'scheduled',
  `scheduled_at` datetime DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `ended_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chess_live_sessions`
--

INSERT INTO `chess_live_sessions` (`id`, `uuid`, `school_id`, `teacher_user_id`, `student_user_id`, `batch_id`, `title`, `session_type`, `current_fen`, `pgn`, `orientation`, `annotations_json`, `teacher_turn`, `status`, `scheduled_at`, `started_at`, `ended_at`, `created_at`) VALUES
(1, 'd33434ce-7abe-4555-aea4-64b40e803dd9', 3, 5, NULL, NULL, 'Endgame', '1-on-1', 'rnbqkbnr/pppp1ppp/8/4p3/4P3/8/PPPP1PPP/RNBQKBNR w KQkq e6 0 2', '[SetUp \"1\"]\n[FEN \"rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq e3 0 1\"]\n\n1. ... e5', 'white', NULL, 1, 'ended', NULL, '2026-10-05 10:28:26', '2026-10-05 10:31:46', '2026-10-05 10:28:26'),
(2, 'e37f4e57-90d8-46c9-83cd-c4baf910611f', 3, 5, NULL, NULL, 'Endgame', '1-on-1', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', NULL, 'white', NULL, 1, 'live', NULL, '2026-10-05 11:00:01', NULL, '2026-10-05 11:00:01'),
(3, '6dd58c77-ffae-41c1-8635-4ce6f9150bf4', 3, 5, 6, NULL, '1-on-1 Coaching with STUTEST1', '1-on-1', 'r1bq1rk1/pppp1ppp/2n2n2/1B2p3/1b2P3/2N2N2/PPPP1PPP/R1BQ1RK1 w - - 8 6', '[SetUp \"1\"]\n[FEN \"r1bqk2r/pppp1ppp/2n2n2/1B2p3/1b2P3/2N2N2/PPPP1PPP/R1BQ1RK1 b kq - 7 5\"]\n\n5. ... O-O', 'white', NULL, 1, 'ended', NULL, '2026-10-05 11:01:04', '2026-10-05 11:01:55', '2026-10-05 11:01:04');

-- --------------------------------------------------------

--
-- Table structure for table `chess_ratings`
--

CREATE TABLE `chess_ratings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `current_rating` int(10) UNSIGNED NOT NULL DEFAULT 400,
  `tier` enum('learner','rookie','intermediate','master','grandmaster') NOT NULL DEFAULT 'learner',
  `games_played` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `wins` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `draws` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `losses` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `peak_rating` int(10) UNSIGNED NOT NULL DEFAULT 400,
  `calibration_completed` tinyint(1) NOT NULL DEFAULT 0,
  `calibration_matches_played` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `in_promotion_battle` tinyint(1) NOT NULL DEFAULT 0,
  `promotion_matches_played` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `promotion_wins` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `promotion_draws` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `in_relegation_defense` tinyint(1) NOT NULL DEFAULT 0,
  `relegation_matches_played` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `relegation_wins` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `relegation_draws` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `season_number` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `season_reset_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chess_ratings`
--

INSERT INTO `chess_ratings` (`id`, `student_user_id`, `school_id`, `current_rating`, `tier`, `games_played`, `wins`, `draws`, `losses`, `peak_rating`, `calibration_completed`, `calibration_matches_played`, `in_promotion_battle`, `promotion_matches_played`, `promotion_wins`, `promotion_draws`, `in_relegation_defense`, `relegation_matches_played`, `relegation_wins`, `relegation_draws`, `season_number`, `season_reset_at`, `created_at`, `updated_at`) VALUES
(1, 6, 3, 1935, 'master', 13, 9, 0, 4, 1967, 1, 6, 0, 0, 0, 0, 0, 0, 0, 0, 1, NULL, '2026-10-05 07:18:07', '2026-10-05 10:16:18'),
(2, 1, NULL, 400, 'learner', 0, 0, 0, 0, 400, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, NULL, '2026-10-05 10:24:29', '2026-10-05 10:24:29'),
(3, 5, 3, 400, 'learner', 0, 0, 0, 0, 400, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, NULL, '2026-10-05 10:30:27', '2026-10-05 10:30:27');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `section` varchar(10) DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `competitions`
--

CREATE TABLE `competitions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `level` enum('school','district','state','national','international','online') DEFAULT 'school',
  `description` longtext DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `venue` varchar(200) DEFAULT NULL,
  `registration_deadline` date DEFAULT NULL,
  `fee` decimal(10,2) DEFAULT 0.00,
  `cover_image` varchar(255) DEFAULT NULL,
  `status` enum('draft','published','closed','archived') DEFAULT 'published',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `competition_participants`
--

CREATE TABLE `competition_participants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `competition_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('registered','participated','qualified','disqualified') DEFAULT 'registered',
  `registered_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `competition_results`
--

CREATE TABLE `competition_results` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `competition_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `rank_position` int(10) UNSIGNED DEFAULT NULL,
  `score` decimal(7,2) DEFAULT NULL,
  `certificate_url` varchar(500) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `recorded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `recorded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `counseling_logs`
--

CREATE TABLE `counseling_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `request_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(50) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `counseling_logs`
--

INSERT INTO `counseling_logs` (`id`, `request_id`, `user_id`, `action`, `note`, `created_at`) VALUES
(1, 1, 6, 'submitted', 'Request created', '2026-09-30 06:49:16'),
(2, 1, 4, 'assigned', 'Teacher #5 assigned', '2026-09-30 06:59:35'),
(3, 1, 5, 'in_progress', NULL, '2026-09-30 07:07:41'),
(4, 1, 5, 'completed', 'Hello', '2026-09-30 07:07:51');

-- --------------------------------------------------------

--
-- Table structure for table `counseling_requests`
--

CREATE TABLE `counseling_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `counseling_type` enum('academics','career','admission','psychometric') NOT NULL,
  `rating_concentration` tinyint(3) UNSIGNED NOT NULL,
  `rating_cognitive` tinyint(3) UNSIGNED NOT NULL,
  `rating_comprehension` tinyint(3) UNSIGNED NOT NULL,
  `rating_creativity` tinyint(3) UNSIGNED NOT NULL,
  `rating_confidence` tinyint(3) UNSIGNED NOT NULL,
  `rating_communication` tinyint(3) UNSIGNED NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `current_class` varchar(50) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `preferred_time` varchar(100) DEFAULT NULL,
  `status` enum('pending','assigned','in_progress','completed','cancelled') DEFAULT 'pending',
  `assigned_teacher_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `assigned_by` bigint(20) UNSIGNED DEFAULT NULL,
  `teacher_notes` text DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `rating_by_student` tinyint(3) UNSIGNED DEFAULT NULL,
  `student_feedback` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `counseling_requests`
--

INSERT INTO `counseling_requests` (`id`, `uuid`, `student_user_id`, `school_id`, `counseling_type`, `rating_concentration`, `rating_cognitive`, `rating_comprehension`, `rating_creativity`, `rating_confidence`, `rating_communication`, `full_name`, `email`, `phone`, `current_class`, `city`, `state`, `reason`, `preferred_time`, `status`, `assigned_teacher_id`, `assigned_at`, `assigned_by`, `teacher_notes`, `completed_at`, `rating_by_student`, `student_feedback`, `created_at`, `updated_at`) VALUES
(1, '894bf296-41fc-4816-9bf2-6204219d28ac', 6, 3, 'career', 2, 5, 3, 5, 1, 1, 'STUTEST1', 'stu@gmail.com', '852369745', '6', 'qw', 'qw', 'Hi, I cannot decide what to do after 10th.', 'AT LUNCH', 'completed', 5, '2026-09-30 06:59:35', 4, 'Hello', '2026-09-30 07:07:51', NULL, NULL, '2026-09-30 06:49:16', '2026-09-30 07:07:51');

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `owner_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_path` varchar(500) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `size_bytes` bigint(20) UNSIGNED DEFAULT NULL,
  `purpose` enum('profile_image','lesson_attachment','activity_cover','certificate','submission','other') DEFAULT 'other',
  `related_table` varchar(50) DEFAULT NULL,
  `related_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `chapter_id` bigint(20) UNSIGNED DEFAULT NULL,
  `topic_id` bigint(20) UNSIGNED DEFAULT NULL,
  `batch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `summary` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `attachments` longtext DEFAULT NULL CHECK (json_valid(`attachments`)),
  `is_ai_generated` tinyint(1) DEFAULT 0,
  `difficulty` enum('easy','medium','hard') DEFAULT 'medium',
  `estimated_minutes` int(10) UNSIGNED DEFAULT 15,
  `published` tinyint(1) DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`id`, `subject_id`, `chapter_id`, `topic_id`, `batch_id`, `title`, `slug`, `summary`, `content`, `video_url`, `attachment`, `attachments`, `is_ai_generated`, `difficulty`, `estimated_minutes`, `published`, `created_by`, `school_id`, `created_at`, `updated_at`) VALUES
(1, 4, NULL, NULL, NULL, 'environment', 'environment-404f', '', '', '', '/uploads/lessons/7bde61a535282de2_1790344898.jpg', NULL, 0, 'medium', 15, 1, 4, 3, '2026-09-25 19:31:38', '2026-09-25 19:31:38'),
(2, 1, NULL, NULL, NULL, 'qqqqqqq', 'qqqqqqq-0e5d', NULL, NULL, NULL, NULL, '[{\"path\":\"\\/uploads\\/lessons\\/dcb9c6042f626aa3_1790346254.pdf\",\"mime\":\"application\\/pdf\",\"ext\":\"pdf\",\"name\":\"Nishant_kumar_singh_Internship_offer_letter.pdf\",\"size\":715935}]', 0, 'easy', 15, 1, 4, 3, '2026-09-25 19:54:14', '2026-09-25 19:54:14'),
(3, 1, NULL, NULL, NULL, 'REAL NUMBERS', 'real-numbers-d776', NULL, NULL, NULL, NULL, '[{\"path\":\"\\/uploads\\/lessons\\/a1244f70d9729fe2_1790760284.docx\",\"mime\":\"application\\/vnd.openxmlformats-officedocument.wordprocessingml.document\",\"ext\":\"docx\",\"name\":\"Real Number set_1 MCQ.docx\",\"size\":29314}]', 0, 'medium', 15, 1, 14, 9, '2026-09-30 09:24:44', '2026-09-30 09:24:44');

-- --------------------------------------------------------

--
-- Table structure for table `lesson_doubts`
--

CREATE TABLE `lesson_doubts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `lesson_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `batch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `attachment_url` varchar(500) DEFAULT NULL,
  `status` enum('open','answered','resolved') NOT NULL DEFAULT 'open',
  `replies_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lesson_doubt_replies`
--

CREATE TABLE `lesson_doubt_replies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `doubt_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `user_role` enum('student','teacher','admin') NOT NULL,
  `reply_text` text NOT NULL,
  `is_teacher_endorsed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lesson_progress`
--

CREATE TABLE `lesson_progress` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lesson_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('not_started','in_progress','completed') DEFAULT 'not_started',
  `progress_percent` tinyint(3) UNSIGNED DEFAULT 0,
  `last_viewed_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lesson_progress`
--

INSERT INTO `lesson_progress` (`id`, `lesson_id`, `student_user_id`, `status`, `progress_percent`, `last_viewed_at`, `completed_at`) VALUES
(1, 1, 6, 'in_progress', 10, '2026-09-25 19:32:10', NULL),
(2, 2, 4, 'in_progress', 10, '2026-09-25 20:00:48', NULL),
(3, 1, 4, 'in_progress', 10, '2026-09-25 20:00:38', NULL),
(9, 1, 1, 'in_progress', 10, '2026-09-29 08:09:45', NULL),
(10, 2, 5, 'in_progress', 10, '2026-09-29 07:08:30', NULL),
(12, 2, 1, 'in_progress', 10, '2026-09-29 08:10:00', NULL),
(13, 3, 14, 'in_progress', 10, '2026-09-30 09:24:49', NULL),
(14, 3, 1, 'in_progress', 10, '2026-10-06 08:00:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lesson_quizzes`
--

CREATE TABLE `lesson_quizzes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lesson_id` bigint(20) UNSIGNED NOT NULL,
  `questions_json` longtext NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lesson_quiz_attempts`
--

CREATE TABLE `lesson_quiz_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lesson_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `score` tinyint(3) UNSIGNED NOT NULL,
  `total_questions` tinyint(3) UNSIGNED NOT NULL DEFAULT 10,
  `passed` tinyint(1) NOT NULL DEFAULT 0,
  `xp_awarded` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `answers_json` longtext DEFAULT NULL,
  `attempted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(60) NOT NULL,
  `title` varchar(200) NOT NULL,
  `body` text DEFAULT NULL,
  `link` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `body`, `link`, `is_read`, `created_at`) VALUES
(1, 5, 'account.approved', 'Your account was approved 🎉', 'Welcome to Infinity Learning! You can now access your dashboard.', '/dashboard', 1, '2026-09-25 15:12:57'),
(2, 6, 'account.approved', 'Your account was approved 🎉', 'Welcome to Infinity Learning! You can now access your dashboard.', '/dashboard', 1, '2026-09-25 15:17:51'),
(3, 6, 'password.changed', 'Your password was changed', 'If this wasn\'t you, reset it immediately and contact support.', '/profile', 1, '2026-09-25 18:39:45'),
(4, 6, 'password.changed', 'Your password was changed', 'If this wasn\'t you, reset it immediately and contact support.', '/profile', 1, '2026-09-25 18:43:12'),
(5, 4, 'announcement', 'Quizwiz', 'There is going to be a quiz competation please participate.', '/dashboard', 1, '2026-09-25 19:13:21'),
(6, 5, 'announcement', 'Quizwiz', 'There is going to be a quiz competation please participate.', '/dashboard', 1, '2026-09-25 19:13:21'),
(7, 6, 'announcement', 'Quizwiz', 'There is going to be a quiz competation please participate.', '/dashboard', 1, '2026-09-25 19:13:21'),
(8, 10, 'account.approved', 'Your account was approved 🎉', 'Welcome to Infinity Learning! You can now access your dashboard.', '/dashboard', 1, '2026-09-26 13:57:22'),
(9, 11, 'account.approved', 'Your account was approved 🎉', 'Welcome to Infinity Learning! You can now access your dashboard.', '/dashboard', 1, '2026-09-28 11:54:15'),
(10, 6, 'certificate.issued', 'Your certificate is ready! 🏅', 'You received a certificate for: QUIZWIZ', '/my-certificates', 1, '2026-09-28 12:22:13'),
(11, 6, 'certificate.issued', 'Your certificate is ready! 🏅', 'You received a certificate for: QUIZWIZ', '/my-certificates', 1, '2026-09-28 12:27:28'),
(12, 6, 'certificate.issued', 'Your certificate is ready! 🏅', 'You received a certificate for: QUIZWIZ', '/my-certificates', 1, '2026-09-28 12:35:38'),
(13, 6, 'certificate.issued', 'Your certificate is ready! 🏅', 'You received a certificate for: QUIZWIZ', '/my-certificates', 1, '2026-09-28 12:40:02'),
(14, 6, 'certificate.issued', 'Your certificate is ready! 🏅', 'You received a certificate for: QUIZWIZ', '/my-certificates', 1, '2026-09-28 12:44:45'),
(15, 6, 'certificate.issued', 'Your certificate is ready! 🏅', 'You received a certificate for: QUIZWIZ', '/my-certificates', 1, '2026-09-28 12:53:41'),
(16, 12, 'account.approved', 'Your account was approved 🎉', 'Welcome to Infinity Learning! You can now access your dashboard.', '/dashboard', 0, '2026-09-29 06:37:26'),
(17, 6, 'certificate.issued', 'Your certificate is ready! 🏅', 'You received a certificate for: QUIZWIZ', '/my-certificates', 1, '2026-09-29 10:35:50'),
(18, 4, 'counseling.request', '🧭 New counseling request', 'STUTEST1 requested Career counseling.', '/school/counseling', 1, '2026-09-30 06:49:16'),
(19, 5, 'counseling.assigned', '🧭 New counseling request assigned to you', 'A Career counseling request has been assigned to you.', '/teacher/counseling?id=1', 1, '2026-09-30 06:59:35'),
(20, 6, 'counseling.completed', '✅ Your counseling session is complete', 'Your counselor has shared notes from your session.', '/counseling', 1, '2026-09-30 07:07:51'),
(21, 4, 'counseling.completed', 'Counseling session completed', 'STUTEST1 — Career session finished.', '/school/counseling-detail?id=1', 1, '2026-09-30 07:07:51'),
(22, 5, 'chess.student_assigned', '♟️ New Chess Mentee Assigned', 'A student has been assigned to you for chess coaching and 1-on-1 sessions.', '/teacher/chess', 1, '2026-10-05 10:27:29'),
(23, 6, 'chess.coach_assigned', '♟️ Your Chess Coach Has Been Assigned!', 'Your school assigned a mentor to help you advance in Rising Rooks Chess Academy.', '/chess', 1, '2026-10-05 10:27:29'),
(24, 4, 'password.changed', 'Your password was changed', 'If this wasn\'t you, reset it immediately and contact support.', '/profile', 1, '2026-10-05 10:27:48');

-- --------------------------------------------------------

--
-- Table structure for table `otps`
--

CREATE TABLE `otps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `phone` varchar(20) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `purpose` enum('registration','password_reset','phone_change') NOT NULL,
  `attempts` tinyint(3) UNSIGNED DEFAULT 0,
  `expires_at` datetime NOT NULL,
  `consumed_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `otps`
--

INSERT INTO `otps` (`id`, `phone`, `otp_hash`, `purpose`, `attempts`, `expires_at`, `consumed_at`, `ip_address`, `created_at`) VALUES
(1, '7774441110', '$argon2id$v=19$m=65536,t=4,p=1$WXJTMTJoL2dJWC56NWpwcw$IDotjr19P7synIQTwsTtIPZzmZzK61iUOROfeleo1Zs', 'registration', 0, '2026-09-25 15:22:24', '2026-09-25 15:12:34', '::1', '2026-09-25 15:12:24'),
(2, '852369745', '$argon2id$v=19$m=65536,t=4,p=1$VHluZnZ6NnZ1N3Z4ZHY5NA$3Ntxw8kmC1bQLA3AHiyPt4D5+kwxUorB6S7Udt3Brik', 'registration', 0, '2026-09-25 15:26:36', '2026-09-25 15:17:03', '::1', '2026-09-25 15:16:36'),
(3, '+919876625417', '$argon2id$v=19$m=65536,t=4,p=1$LzZSbU1DVDM2ZC91dkdkVQ$URc39NR1jjmnLAeM1no+nePeIO50qiwRm1ToqW+FCO4', 'registration', 0, '2026-09-26 14:06:04', '2026-09-26 13:56:13', '::1', '2026-09-26 13:56:04'),
(4, '+917488485464', '$argon2id$v=19$m=65536,t=4,p=1$SWlMbGk3b2RoTHNEdzJFeQ$oU4sLMsn+0be06cy0vcVz+KOImysA0sbsb9NJoXgrBo', 'registration', 0, '2026-09-26 14:07:17', '2026-09-26 13:58:10', '::1', '2026-09-26 13:57:17'),
(5, '+917488485464', '$argon2id$v=19$m=65536,t=4,p=1$SVBtc1VROUpzSnBGMXlrRA$NfD2+rtZFGhpIedq6olIkn+wbMiaIx7WfhdAL8I7mdw', 'registration', 0, '2026-09-26 14:08:11', '2026-09-26 14:25:43', '::1', '2026-09-26 13:58:11'),
(6, '+917488485464', '$argon2id$v=19$m=65536,t=4,p=1$SGNHSXlDeHMxR2pOWk1tbQ$+KeEVUos4PIcLM2Juv/PZdbd8fMc24Y5LHErTaHwxjg', 'registration', 0, '2026-09-26 14:35:43', '2026-09-26 14:33:01', '127.0.0.1', '2026-09-26 14:25:43'),
(7, '+917488485464', '$argon2id$v=19$m=65536,t=4,p=1$aDFibkUzZU9LenpUQXhIMA$vJqEzWIf1cUr4Unatly1P94Qb0KDHiZLEiey0aV1DMs', 'registration', 0, '2026-09-26 14:43:01', NULL, '127.0.0.1', '2026-09-26 14:33:01'),
(8, '+919905893514', '$argon2id$v=19$m=65536,t=4,p=1$U0l5YlVRZDZhM2RUTnROYg$H0M43dwae3LKUymTE4lVppFTF8ViHPWSHnC3JhXZqyo', 'registration', 0, '2026-09-28 12:02:51', '2026-09-28 11:53:23', '::1', '2026-09-28 11:52:51'),
(9, '+917418525502', '$argon2id$v=19$m=65536,t=4,p=1$dXhhOFJnTnRzWExGdVR3Sg$0Ts5cdoLH6+7gA11Q8Jr1q/NP/qFxpONmM6OULgfFSg', 'registration', 0, '2026-09-28 12:43:55', NULL, '::1', '2026-09-28 12:33:55'),
(10, '+917061509292', '$argon2id$v=19$m=65536,t=4,p=1$UHhuUUswMnFaUkRzeGhDOA$UfTkZr1R/7kqszN3hOhdSCtH7sttL7b1wkHuOF7x34E', 'registration', 0, '2026-09-29 06:45:03', '2026-09-29 06:35:43', '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 06:35:03'),
(11, '+917899987878', '$argon2id$v=19$m=65536,t=4,p=1$WWkwTW5UMzJ2VlVsOXJqcQ$HnA6QXl+IsD1Z7U0wwXsCJ2v67qnv3g+rAQEmEMWT6Y', 'registration', 1, '2026-10-06 06:19:40', NULL, '2405:201:a41a:c882:a4cf:acae:67d6:746c', '2026-10-06 06:09:40');

-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE `parents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `relationship` enum('father','mother','guardian','other') DEFAULT 'guardian',
  `occupation` varchar(150) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `parents`
--

INSERT INTO `parents` (`id`, `user_id`, `full_name`, `email`, `phone`, `relationship`, `occupation`, `city`, `state`, `created_at`, `updated_at`) VALUES
(1, NULL, 'asdf', 'as@test.com', '+917459823143', 'mother', 'Doctor', 'Pune', 'qw', '2026-09-25 15:17:04', '2026-09-25 19:11:21'),
(2, NULL, 'Madhu Kumari', 'mk.raregeo@gmail.com', '+919431733088', 'mother', 'Doctor', 'Ranchi', 'Jharkhand', '2026-09-28 11:53:23', '2026-09-28 11:53:23');

-- --------------------------------------------------------

--
-- Table structure for table `parent_email_log`
--

CREATE TABLE `parent_email_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `week_start` date NOT NULL,
  `sent_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('sent','failed') NOT NULL DEFAULT 'sent',
  `error_message` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `consumed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token_hash`, `expires_at`, `consumed_at`, `created_at`) VALUES
(2, 14, '2d122d5b118f12432bfea89c008314b21eff5fd826382f816b78ff672ab7fc8b', '2026-10-01 05:51:23', '2026-10-01 04:52:02', '2026-10-01 04:51:23');

-- --------------------------------------------------------

--
-- Table structure for table `question_bank`
--

CREATE TABLE `question_bank` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `teacher_user_id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `chapter_id` bigint(20) UNSIGNED DEFAULT NULL,
  `topic_id` bigint(20) UNSIGNED DEFAULT NULL,
  `class_level` varchar(50) DEFAULT NULL,
  `question_text` longtext NOT NULL,
  `question_type` enum('mcq','short_answer','descriptive','numerical','true_false') NOT NULL DEFAULT 'mcq',
  `options_json` longtext DEFAULT NULL,
  `correct_answer` text DEFAULT NULL,
  `explanation` text DEFAULT NULL,
  `marks` decimal(4,1) NOT NULL DEFAULT 1.0,
  `difficulty` enum('easy','medium','hard') NOT NULL DEFAULT 'medium',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `identifier` varchar(150) NOT NULL,
  `action` varchar(50) NOT NULL,
  `attempts` int(10) UNSIGNED DEFAULT 0,
  `window_start` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rate_limits`
--

INSERT INTO `rate_limits` (`id`, `identifier`, `action`, `attempts`, `window_start`) VALUES
(6, '::1', 'register', 4, '2026-09-28 11:52:29'),
(7, '7774441110', 'otp_verify', 1, '2026-09-25 15:12:34'),
(11, '852369745', 'otp_verify', 1, '2026-09-25 15:17:03'),
(17, 'teststu@gmail.com|::1', 'login', 1, '2026-09-25 17:29:58'),
(23, 'admin@infinityearning.net|::1', 'login', 3, '2026-09-25 19:13:51'),
(29, 'testteac@gmail.com|::1', 'login', 1, '2026-09-25 19:33:12'),
(33, 'testteacher@test.com|::1', 'login', 1, '2026-09-25 20:10:38'),
(35, 'admin@infinitlearning.net|::1', 'login', 1, '2026-09-26 13:38:54'),
(43, '+919876625417', 'otp_verify', 1, '2026-09-26 13:56:13'),
(44, 'testteacher1@dav.com|::1', 'login', 1, '2026-09-26 13:56:28'),
(48, '127.0.0.1', 'register', 2, '2026-09-26 14:25:43'),
(52, '+919905893514', 'otp_verify', 1, '2026-09-28 11:53:22'),
(60, 'stutest@gmail.com|::1', 'login', 2, '2026-09-28 12:27:46'),
(69, 'testteacher@gmai.com|::1', 'login', 1, '2026-09-28 12:53:20'),
(77, 'testschool1@test.com|2405:201:a41a:c882:d9ec:9d5c:bf10:d26f', 'login', 5, '2026-09-28 11:33:33'),
(81, '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', 'register', 5, '2026-09-29 06:35:03'),
(82, '+917061509292', 'otp_verify', 1, '2026-09-29 06:35:43'),
(89, 'admin@infinitylearning.ner|2405:201:a41a:c882:764c:4c7f:196c:cbff', 'login', 1, '2026-09-29 08:02:14'),
(93, 'testteacheerr@gmail.com|2405:201:a41a:c882:9e56:dbaa:c7db:7762', 'login', 1, '2026-09-29 10:12:09'),
(94, 'testteacheer@gmail.com|2405:201:a41a:c882:9e56:dbaa:c7db:7762', 'login', 1, '2026-09-29 10:12:22'),
(116, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', 'forgot', 2, '2026-10-01 04:43:32'),
(134, 'testschool@gmail.com|2405:201:a41a:c882:a4cf:acae:67d6:746c', 'login', 1, '2026-10-06 06:01:43'),
(135, 'testschool1@gmail.com|2405:201:a41a:c882:a4cf:acae:67d6:746c', 'login', 1, '2026-10-06 06:01:49'),
(137, '2405:201:a41a:c882:a4cf:acae:67d6:746c', 'register', 1, '2026-10-06 06:09:40'),
(138, '+917899987878', 'otp_verify', 1, '2026-10-06 06:11:46'),
(140, 'admin@infintylearning.net|2405:201:a41a:c882:8695:9db9:dec3:d773', 'login', 1, '2026-10-06 07:56:58');

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--

CREATE TABLE `schools` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `code` varchar(20) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `principal_name` varchar(150) DEFAULT NULL,
  `board` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `status` enum('active','archived') DEFAULT 'active',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `admin_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `student_capacity` int(10) UNSIGNED DEFAULT 500,
  `teacher_capacity` int(10) UNSIGNED DEFAULT 100,
  `approved_at` datetime DEFAULT NULL,
  `brand_primary_color` varchar(20) DEFAULT '#0A192F',
  `brand_secondary_color` varchar(20) DEFAULT '#BE9420',
  `brand_tagline` varchar(255) DEFAULT NULL,
  `crest_logo` varchar(500) DEFAULT NULL,
  `principal_signature` varchar(500) DEFAULT NULL,
  `official_seal` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schools`
--

INSERT INTO `schools` (`id`, `uuid`, `code`, `name`, `principal_name`, `board`, `city`, `state`, `address`, `contact_email`, `contact_phone`, `phone`, `website`, `logo`, `status`, `created_by`, `admin_user_id`, `created_at`, `updated_at`, `student_capacity`, `teacher_capacity`, `approved_at`, `brand_primary_color`, `brand_secondary_color`, `brand_tagline`, `crest_logo`, `principal_signature`, `official_seal`) VALUES
(3, '33c27435-d519-4644-b2ef-ecf0d2a2f8c7', 'TS-F59C', 'Test SCHOOL', 'TEST#', 'CBSE', 'RANCHi', 'hd', 'sdffffffaf', 'test@school.com', '749863520', '741852960', NULL, NULL, 'active', 1, 4, '2026-09-25 15:10:42', '2026-09-25 15:10:43', 500, 100, '2026-09-25 15:10:43', '#0A192F', '#BE9420', NULL, NULL, NULL, NULL),
(4, 'f25fb68a-d373-445b-9a93-7618e39c919d', 'ID-E799', 'ISL Dhanbad', 'XYZ', 'CBSE', 'Dhanbad', 'Jharkhand', 'XYZ', 'xyz@gmail.com', '718563055', '968527410', NULL, NULL, 'active', 1, 7, '2026-09-26 13:41:11', '2026-09-26 13:41:12', 500, 100, '2026-09-26 13:41:12', '#0A192F', '#BE9420', NULL, NULL, NULL, NULL),
(5, '422036df-b6c2-466b-a788-21cafa29ac47', 'DR-03CA', 'DAV Ranchi', 'YSA', 'CBSE', 'Ranchi', 'Jharkhand', 'RANCHI', 'davran@dav.com', '789546321', '7894561230', NULL, NULL, 'active', 1, 8, '2026-09-26 13:42:38', '2026-09-26 13:42:38', 500, 100, '2026-09-26 13:42:38', '#0A192F', '#BE9420', NULL, NULL, NULL, NULL),
(6, '66d20a48-dd10-4459-9f0d-195c60bb37fa', 'DK-95BB', 'DAV KDPS', 'MK', 'ICSE', 'RANCHI', 'JHarkhand', 'KADRU', 'davkdps@kds.in', '7988547630', '7789998546', NULL, NULL, 'active', 1, 9, '2026-09-26 13:44:30', '2026-09-26 13:44:31', 10, 8, '2026-09-26 13:44:31', '#0A192F', '#BE9420', NULL, NULL, NULL, NULL),
(8, '1602c295-afcd-48f0-a379-e34751f8410b', 'A-5A24', 'AJ', 'ME', 'CBSE', 'RAnchi', 'Jharkhand', 'MAHABIR TOWER', 'avj@femail.com', '9988552200', '7485961524', NULL, NULL, 'active', 1, 13, '2026-09-29 10:50:06', '2026-09-30 06:18:34', 500, 100, '2026-09-29 10:50:06', '#0A192F', '#BE9420', NULL, NULL, NULL, NULL),
(9, 'bbc410aa-4a29-4437-91e3-de61c282b5d5', 'A-176B', 'AIVERSE', 'RAVI SHARMA', 'CBSE', 'RANCHI', 'JHARKHAND', 'MAHABIR TOWER', 'india.futurenxt@gmail.com', '9801506511', '9099937500', NULL, NULL, 'active', 1, 14, '2026-09-29 10:58:45', '2026-09-29 10:58:45', 500, 100, '2026-09-29 10:58:45', '#0A192F', '#BE9420', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` char(64) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `last_activity` datetime NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `gender` enum('male','female','other','prefer_not_to_say') DEFAULT NULL,
  `age` tinyint(3) UNSIGNED DEFAULT NULL,
  `school_name` varchar(200) DEFAULT NULL,
  `school_board` varchar(100) DEFAULT NULL,
  `current_class` varchar(50) DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `interests` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `full_name`, `profile_image`, `gender`, `age`, `school_name`, `school_board`, `current_class`, `academic_year`, `city`, `state`, `interests`, `created_at`, `updated_at`) VALUES
(1, 6, 'STUTEST1', NULL, 'female', 10, NULL, 'CBSE', '6', '2026', 'qw', 'qw', 'maths', '2026-09-25 15:17:04', '2026-09-25 15:17:04'),
(2, 11, 'Aviraj', NULL, 'male', 18, NULL, 'ICSE', '12', '2025-2026', 'Ranchi', 'Jharkhand', 'Maths', '2026-09-28 11:53:23', '2026-09-28 11:53:23');

-- --------------------------------------------------------

--
-- Table structure for table `student_fees`
--

CREATE TABLE `student_fees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `batch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `academic_year` varchar(20) NOT NULL DEFAULT '2026-2027',
  `fee_month` varchar(20) NOT NULL,
  `fee_type` enum('tuition','admission','transport','exam','chess_academy','activity') NOT NULL DEFAULT 'tuition',
  `amount_due` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `due_date` date DEFAULT NULL,
  `status` enum('pending','partial','paid','overdue','waived') NOT NULL DEFAULT 'pending',
  `payment_mode` enum('upi','cash','bank_transfer','card','cheque') DEFAULT NULL,
  `receipt_number` varchar(50) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `recorded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_knowledge_profile`
--

CREATE TABLE `student_knowledge_profile` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `topic_id` bigint(20) UNSIGNED DEFAULT NULL,
  `mastery_percent` tinyint(3) UNSIGNED DEFAULT 0,
  `strength_level` enum('weak','developing','strong','mastered') DEFAULT 'developing',
  `last_evaluated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_parents`
--

CREATE TABLE `student_parents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_parents`
--

INSERT INTO `student_parents` (`id`, `student_user_id`, `parent_id`, `is_primary`, `created_at`) VALUES
(1, 6, 1, 1, '2026-09-25 15:17:04'),
(2, 11, 2, 1, '2026-09-28 11:53:23');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `icon` varchar(60) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `slug`, `icon`, `color`, `description`, `created_at`) VALUES
(1, 'Mathematics', 'mathematics', '➗', '#FF7A18', NULL, '2026-09-24 12:19:47'),
(2, 'Physics', 'physics', '⚛️', '#2563EB', NULL, '2026-09-24 12:19:47'),
(3, 'Chemistry', 'chemistry', '🧪', '#16A34A', NULL, '2026-09-24 12:19:47'),
(4, 'Biology', 'biology', '🧬', '#10B981', NULL, '2026-09-24 12:19:47'),
(5, 'English', 'english', '📚', '#3B82F6', NULL, '2026-09-24 12:19:47'),
(6, 'Hindi', 'hindi', '🕉️', '#F97316', NULL, '2026-09-24 12:19:47'),
(7, 'Sanskrit', 'sanskrit', '📜', '#D97706', NULL, '2026-09-24 12:19:47'),
(8, 'Computer Science', 'computer-science', '💻', '#EC4899', NULL, '2026-09-24 12:19:47'),
(9, 'Social Science', 'social-science', '🌍', '#A855F7', NULL, '2026-09-24 12:19:47'),
(10, 'AI & Innovation', 'ai-innovation', '🤖', '#F59E0B', NULL, '2026-09-24 12:19:47');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `gender` enum('male','female','other','prefer_not_to_say') DEFAULT NULL,
  `school_name` varchar(200) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `expert_field` varchar(150) DEFAULT NULL,
  `years_experience` tinyint(3) UNSIGNED DEFAULT NULL,
  `additional_expertise` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `user_id`, `full_name`, `profile_image`, `gender`, `school_name`, `city`, `state`, `expert_field`, `years_experience`, `additional_expertise`, `bio`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 5, 'TEST techaer 1', NULL, 'male', NULL, 'ranchi', 'jharkhand', 'science', 2, NULL, NULL, 4, '2026-09-25 15:12:57', '2026-09-25 15:12:35', '2026-09-25 15:12:57'),
(2, 10, 'Test Teacher 1', NULL, 'female', NULL, 'Ranchi', 'Jharkhand', 'History', 30, 'Civics', 'HI', 8, '2026-09-26 13:57:22', '2026-09-26 13:56:13', '2026-09-26 13:57:22'),
(3, 12, 'Mamta kumari', NULL, 'female', NULL, 'Dhanbad', 'Jharkhand', 'Computer', 2, 'Science', NULL, 7, '2026-09-29 06:37:26', '2026-09-29 06:35:43', '2026-09-29 06:37:26');

-- --------------------------------------------------------

--
-- Table structure for table `timetables`
--

CREATE TABLE `timetables` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `class_level` varchar(50) NOT NULL,
  `section` varchar(10) DEFAULT 'A',
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday') NOT NULL,
  `period_number` tinyint(3) UNSIGNED NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `teacher_user_id` bigint(20) UNSIGNED NOT NULL,
  `room_number` varchar(30) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `topics`
--

CREATE TABLE `topics` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `chapter_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(10) UNSIGNED DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('student','teacher','admin') NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('pending','active','suspended','rejected') DEFAULT 'pending',
  `phone_verified_at` datetime DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `uuid`, `email`, `phone`, `password_hash`, `role`, `school_id`, `status`, `phone_verified_at`, `email_verified_at`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, '4291aeef-6976-48dd-9440-c4737143e415', 'admin@infinitylearning.net', '+919523176706', '$argon2id$v=19$m=65536,t=4,p=1$MDhvMDNRLmFDeUdlQVhaQg$QRS1Ay3K59/ZIwR3Ta6InDM98hhDRDrqi7m+aJaaDaM', 'admin', NULL, 'active', '2026-09-24 12:27:47', '2026-09-24 12:27:47', '2026-10-06 07:57:16', '2026-09-24 12:27:47', '2026-10-06 07:57:16'),
(2, 'b3d1dcae-81c2-4789-b54b-1fdb7ec606b5', 'admin@test.com', '7418529630', '$argon2id$v=19$m=65536,t=4,p=1$djJVSnpxaldIcGtVeWEwZw$s95WBErWujg81WVJYf0Qju74eDHaiGS7l2AI2EaQgGY', 'admin', 1, 'active', '2026-09-25 14:59:33', '2026-09-25 14:59:33', '2026-09-25 15:00:01', '2026-09-25 14:59:33', '2026-09-25 15:00:01'),
(4, 'de215c01-7d3a-4298-b452-670bc6fdc355', 'testschool1@test.com', '7894623102', '$argon2id$v=19$m=65536,t=4,p=1$Z0RuUWFKUHZFU1h1UkZQZw$TSVW0NS6/gp25VlaWyaZXRFQKLBwgwCU5OMor6jsD2g', 'admin', 3, 'active', '2026-09-25 15:10:43', '2026-09-25 15:10:43', '2026-10-06 08:03:34', '2026-09-25 15:10:43', '2026-10-06 08:03:34'),
(5, '3ac142ea-234d-4669-a2a9-36d83c761ce5', 'testteacher@gmail.com', '7774441110', '$argon2id$v=19$m=65536,t=4,p=1$L1FsaXRzRm5FRnNKeGhJeA$nNEaMZSuG8tJ2YhcKLC5Av8yEiXxq21JfxBniJRgYi4', 'teacher', 3, 'active', '2026-09-25 15:12:35', NULL, '2026-10-06 06:02:05', '2026-09-25 15:12:35', '2026-10-06 06:02:05'),
(6, '33d536c8-ecf0-4f2f-a8a3-07436fe54ee1', 'stu@gmail.com', '852369745', '$argon2id$v=19$m=65536,t=4,p=1$QW5xZThTMC85RmR6VS5FNw$QQGDoOk4356q7Vy1ErStt1hdtVnZDACNQkvHSBr0vzE', 'student', 3, 'active', '2026-09-25 15:17:04', NULL, '2026-10-06 08:01:13', '2026-09-25 15:17:04', '2026-10-06 08:01:13'),
(7, '3c0f6f9a-e95b-4c12-a08b-fdfbd73ed9ee', 'adminisl@isl.com', '7988546123', '$argon2id$v=19$m=65536,t=4,p=1$ZG9VU1cyOUh2RExVYXNQLg$hwPtlzgehR2eP9T6YnFJg9yWdfXbQB/Ggwmi2OZ0z6k', 'admin', 4, 'active', '2026-09-26 13:41:12', '2026-09-26 13:41:12', '2026-09-29 06:37:06', '2026-09-26 13:41:12', '2026-09-29 06:37:06'),
(8, '574d7a7f-a080-4cf4-b08d-c29b71cb33ea', 'admindavran@dav.com', '7894856125', '$argon2id$v=19$m=65536,t=4,p=1$YXFTeFZaeEd5L3NTd3Nzbg$ywMqDhRLl/IKms/v625bJCeEG6Z6cP0QFEf2cqzlZWQ', 'admin', 5, 'active', '2026-09-26 13:42:38', '2026-09-26 13:42:38', '2026-09-29 06:43:39', '2026-09-26 13:42:38', '2026-09-29 06:43:39'),
(9, '3cba1927-da35-4286-8cfe-cf06d1557155', 'adminkdps@kdps.in', '7898852136', '$argon2id$v=19$m=65536,t=4,p=1$UkguNkgyQVVvamxEYkdMNQ$Zx9G3yQlu+JhmmwxLr01Dh9541Xo//2i6RarZOx+BvM', 'admin', 6, 'active', '2026-09-26 13:44:31', '2026-09-26 13:44:31', '2026-09-28 11:54:04', '2026-09-26 13:44:31', '2026-09-28 11:54:04'),
(10, '4f52e97b-76ce-4715-b63b-ca34539a0bb0', 'testteacher@dav.com', '+919876625417', '$argon2id$v=19$m=65536,t=4,p=1$THZoZU94a3FJSmVEUk9USg$diHDzr/P2RUaHiT08XdRaIekI9E62Pn2sU4mcvaQcpc', 'teacher', 5, 'active', '2026-09-26 13:56:13', NULL, '2026-09-26 13:57:37', '2026-09-26 13:56:13', '2026-09-26 13:57:37'),
(11, '213749eb-40ac-4e54-97c1-4d04132700a7', 'kumaraviraj682@gmail.com', '+919905893514', '$argon2id$v=19$m=65536,t=4,p=1$dnYyeG50ckN5ZkxXdlM3cg$cnWY+bshxuTvVuPx+FjJXVkGrDEcyR1qTfxw145y1iY', 'student', 6, 'active', '2026-09-28 11:53:23', NULL, '2026-09-28 11:55:05', '2026-09-28 11:53:23', '2026-09-28 11:55:05'),
(12, 'f3c3917a-0368-4aa0-b424-e3f9a64ac250', 'mamtapathak051@gmail.com', '+917061509292', '$argon2id$v=19$m=65536,t=4,p=1$aGMxTlhuMDVLY3ZpYUhWRQ$8t5gresHmSTl2csWIVXoXCnPPgkw4aHgIdBDxTZaBXo', 'teacher', 4, 'active', '2026-09-29 06:35:43', NULL, '2026-09-29 06:38:31', '2026-09-29 06:35:43', '2026-09-29 06:38:31'),
(13, '349909fc-e319-4ccb-9966-a70731a0dc96', 'avirajk07@gmail.com', '9905893514', '$argon2id$v=19$m=65536,t=4,p=1$c21EMWFYOXdrcmd6V3I2Zg$+dUXiRMSfer5a2iwm2zd0bQljNTJ5U3ei7LvnB1UC1s', 'admin', 8, 'active', '2026-09-29 10:50:06', '2026-09-29 10:50:06', NULL, '2026-09-29 10:50:06', '2026-09-30 06:18:34'),
(14, '1ad705dd-7fcd-4b26-97bb-d66f89f2dcb2', 'fnxt.interns@gmail.com', '9470181755', '$argon2id$v=19$m=65536,t=4,p=1$eDJwenRPTmxzTEg1QmdaaA$wY1meQDa0bOcSqlIE08J2KWixNs7V11obpU8HvLo1nk', 'admin', 9, 'active', '2026-09-29 10:58:45', '2026-09-29 10:58:45', '2026-10-01 04:52:23', '2026-09-29 10:58:45', '2026-10-01 04:52:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_assessments`
--
ALTER TABLE `academic_assessments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_aa_uuid` (`uuid`),
  ADD KEY `idx_aa_teacher` (`teacher_user_id`),
  ADD KEY `idx_aa_batch` (`batch_id`),
  ADD KEY `idx_aa_type` (`assessment_type`),
  ADD KEY `idx_aa_due` (`due_date`);

--
-- Indexes for table `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `awarded_by` (`awarded_by`),
  ADD KEY `idx_student` (`student_user_id`),
  ADD KEY `idx_category` (`category`);

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `batch_id` (`batch_id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_event_date` (`event_date`),
  ADD KEY `idx_school` (`school_id`);

--
-- Indexes for table `activity_registrations`
--
ALTER TABLE `activity_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_activity_user` (`activity_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `activity_results`
--
ALTER TABLE `activity_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_activity_student` (`activity_id`,`student_user_id`),
  ADD KEY `student_user_id` (`student_user_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `ai_answers`
--
ALTER TABLE `ai_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ai_question_id` (`ai_question_id`),
  ADD KEY `student_user_id` (`student_user_id`);

--
-- Indexes for table `ai_questions`
--
ALTER TABLE `ai_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ai_session_id` (`ai_session_id`);

--
-- Indexes for table `ai_sessions`
--
ALTER TABLE `ai_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `topic_id` (`topic_id`),
  ADD KEY `idx_student` (`student_user_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_school` (`school_id`);

--
-- Indexes for table `assessment_submissions`
--
ALTER TABLE `assessment_submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_as_uuid` (`uuid`),
  ADD UNIQUE KEY `uk_as_assessment_student` (`assessment_id`,`student_user_id`),
  ADD KEY `idx_as_student` (`student_user_id`),
  ADD KEY `idx_as_assessment` (`assessment_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_batch_student_date` (`batch_id`,`student_user_id`,`attendance_date`),
  ADD KEY `marked_by` (`marked_by`),
  ADD KEY `idx_date` (`attendance_date`),
  ADD KEY `idx_student` (`student_user_id`);

--
-- Indexes for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_batch_session` (`batch_id`,`session_date`,`session_label`),
  ADD KEY `marked_by` (`marked_by`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `batches`
--
ALTER TABLE `batches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_teacher` (`teacher_id`),
  ADD KEY `idx_class` (`class_id`);

--
-- Indexes for table `batch_activity_assignments`
--
ALTER TABLE `batch_activity_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_batch` (`batch_id`),
  ADD KEY `idx_activity` (`activity_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `assigned_by` (`assigned_by`);

--
-- Indexes for table `batch_assignment_acknowledgments`
--
ALTER TABLE `batch_assignment_acknowledgments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_assignment_student` (`assignment_id`,`student_user_id`),
  ADD KEY `student_user_id` (`student_user_id`);

--
-- Indexes for table `batch_students`
--
ALTER TABLE `batch_students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_batch_student` (`batch_id`,`student_user_id`),
  ADD KEY `student_user_id` (`student_user_id`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `issued_by` (`issued_by`),
  ADD KEY `idx_student` (`student_user_id`),
  ADD KEY `idx_student_type` (`student_user_id`,`certificate_type`);

--
-- Indexes for table `chapters`
--
ALTER TABLE `chapters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_subject` (`subject_id`);

--
-- Indexes for table `chess_challenges`
--
ALTER TABLE `chess_challenges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_challenge_uuid` (`uuid`),
  ADD KEY `idx_challenger` (`challenger_user_id`),
  ADD KEY `idx_challenged` (`challenged_user_id`),
  ADD KEY `idx_challenge_school` (`school_id`),
  ADD KEY `fk_cc_game` (`game_id`);

--
-- Indexes for table `chess_coach_assignments`
--
ALTER TABLE `chess_coach_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cca_teacher` (`teacher_user_id`),
  ADD KEY `idx_cca_student` (`student_user_id`),
  ADD KEY `idx_cca_school` (`school_id`),
  ADD KEY `fk_cca_admin` (`assigned_by`);

--
-- Indexes for table `chess_curriculum`
--
ALTER TABLE `chess_curriculum`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_curr_tier_cat` (`tier`,`category`);

--
-- Indexes for table `chess_games`
--
ALTER TABLE `chess_games`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_chess_game_uuid` (`uuid`),
  ADD KEY `idx_white_user` (`white_user_id`),
  ADD KEY `idx_black_user` (`black_user_id`),
  ADD KEY `idx_game_type` (`game_type`),
  ADD KEY `idx_game_school` (`school_id`);

--
-- Indexes for table `chess_live_sessions`
--
ALTER TABLE `chess_live_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_live_session_uuid` (`uuid`),
  ADD KEY `idx_live_teacher` (`teacher_user_id`),
  ADD KEY `idx_live_student` (`student_user_id`),
  ADD KEY `idx_live_school` (`school_id`);

--
-- Indexes for table `chess_ratings`
--
ALTER TABLE `chess_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_student_chess` (`student_user_id`),
  ADD KEY `idx_tier` (`tier`),
  ADD KEY `idx_rating` (`current_rating`),
  ADD KEY `idx_school_chess` (`school_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_school` (`school_id`);

--
-- Indexes for table `competitions`
--
ALTER TABLE `competitions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `competition_participants`
--
ALTER TABLE `competition_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_comp_student` (`competition_id`,`student_user_id`),
  ADD KEY `student_user_id` (`student_user_id`);

--
-- Indexes for table `competition_results`
--
ALTER TABLE `competition_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_comp_student_result` (`competition_id`,`student_user_id`),
  ADD KEY `student_user_id` (`student_user_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `counseling_logs`
--
ALTER TABLE `counseling_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_request` (`request_id`),
  ADD KEY `fk_cl_user` (`user_id`);

--
-- Indexes for table `counseling_requests`
--
ALTER TABLE `counseling_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `idx_student` (`student_user_id`),
  ADD KEY `idx_school` (`school_id`),
  ADD KEY `idx_teacher` (`assigned_teacher_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_type` (`counseling_type`),
  ADD KEY `fk_cr_assigner` (`assigned_by`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `idx_owner` (`owner_user_id`),
  ADD KEY `idx_purpose` (`purpose`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chapter_id` (`chapter_id`),
  ADD KEY `topic_id` (`topic_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_subject` (`subject_id`),
  ADD KEY `idx_batch` (`batch_id`),
  ADD KEY `idx_school` (`school_id`),
  ADD KEY `idx_batch_school` (`batch_id`,`school_id`);

--
-- Indexes for table `lesson_doubts`
--
ALTER TABLE `lesson_doubts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_ld_uuid` (`uuid`),
  ADD KEY `idx_ld_lesson` (`lesson_id`),
  ADD KEY `idx_ld_student` (`student_user_id`),
  ADD KEY `idx_ld_status` (`status`);

--
-- Indexes for table `lesson_doubt_replies`
--
ALTER TABLE `lesson_doubt_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ldr_doubt` (`doubt_id`),
  ADD KEY `idx_ldr_user` (`user_id`);

--
-- Indexes for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_lesson_student` (`lesson_id`,`student_user_id`),
  ADD KEY `student_user_id` (`student_user_id`);

--
-- Indexes for table `lesson_quizzes`
--
ALTER TABLE `lesson_quizzes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_lq_lesson` (`lesson_id`);

--
-- Indexes for table `lesson_quiz_attempts`
--
ALTER TABLE `lesson_quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lqa_student` (`student_user_id`),
  ADD KEY `idx_lqa_lesson` (`lesson_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_unread` (`user_id`,`is_read`);

--
-- Indexes for table `otps`
--
ALTER TABLE `otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_phone_purpose` (`phone`,`purpose`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `parent_email_log`
--
ALTER TABLE `parent_email_log`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_parent_student_week` (`parent_id`,`student_user_id`,`week_start`),
  ADD KEY `idx_week` (`week_start`),
  ADD KEY `idx_parent` (`parent_id`),
  ADD KEY `pel_student` (`student_user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `question_bank`
--
ALTER TABLE `question_bank`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_qb_uuid` (`uuid`),
  ADD KEY `idx_qb_teacher` (`teacher_user_id`),
  ADD KEY `idx_qb_subject` (`subject_id`),
  ADD KEY `idx_qb_chapter` (`chapter_id`),
  ADD KEY `idx_qb_topic` (`topic_id`),
  ADD KEY `idx_qb_school` (`school_id`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_identifier_action` (`identifier`,`action`);

--
-- Indexes for table `schools`
--
ALTER TABLE `schools`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_city` (`city`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_school` (`school_name`),
  ADD KEY `idx_city` (`city`);

--
-- Indexes for table `student_fees`
--
ALTER TABLE `student_fees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sf_student` (`student_user_id`),
  ADD KEY `idx_sf_school` (`school_id`),
  ADD KEY `idx_sf_batch` (`batch_id`),
  ADD KEY `idx_sf_month` (`fee_month`),
  ADD KEY `idx_sf_status` (`status`);

--
-- Indexes for table `student_knowledge_profile`
--
ALTER TABLE `student_knowledge_profile`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_student_topic` (`student_user_id`,`subject_id`,`topic_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- Indexes for table `student_parents`
--
ALTER TABLE `student_parents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_student_parent` (`student_user_id`,`parent_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_school` (`school_name`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `timetables`
--
ALTER TABLE `timetables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_timetable_slot` (`school_id`,`class_level`,`section`,`day_of_week`,`period_number`),
  ADD KEY `idx_tt_teacher` (`teacher_user_id`),
  ADD KEY `idx_tt_school` (`school_id`),
  ADD KEY `idx_tt_day` (`day_of_week`);

--
-- Indexes for table `topics`
--
ALTER TABLE `topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_chapter` (`chapter_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_school` (`school_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_assessments`
--
ALTER TABLE `academic_assessments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `achievements`
--
ALTER TABLE `achievements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `activity_registrations`
--
ALTER TABLE `activity_registrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `activity_results`
--
ALTER TABLE `activity_results`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `ai_answers`
--
ALTER TABLE `ai_answers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_questions`
--
ALTER TABLE `ai_questions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_sessions`
--
ALTER TABLE `ai_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `assessment_submissions`
--
ALTER TABLE `assessment_submissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=268;

--
-- AUTO_INCREMENT for table `batches`
--
ALTER TABLE `batches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `batch_activity_assignments`
--
ALTER TABLE `batch_activity_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `batch_assignment_acknowledgments`
--
ALTER TABLE `batch_assignment_acknowledgments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `batch_students`
--
ALTER TABLE `batch_students`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `chapters`
--
ALTER TABLE `chapters`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chess_challenges`
--
ALTER TABLE `chess_challenges`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chess_coach_assignments`
--
ALTER TABLE `chess_coach_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `chess_curriculum`
--
ALTER TABLE `chess_curriculum`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chess_games`
--
ALTER TABLE `chess_games`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `chess_live_sessions`
--
ALTER TABLE `chess_live_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `chess_ratings`
--
ALTER TABLE `chess_ratings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `competitions`
--
ALTER TABLE `competitions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `competition_participants`
--
ALTER TABLE `competition_participants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `competition_results`
--
ALTER TABLE `competition_results`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `counseling_logs`
--
ALTER TABLE `counseling_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `counseling_requests`
--
ALTER TABLE `counseling_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lesson_doubts`
--
ALTER TABLE `lesson_doubts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lesson_doubt_replies`
--
ALTER TABLE `lesson_doubt_replies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `lesson_quizzes`
--
ALTER TABLE `lesson_quizzes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lesson_quiz_attempts`
--
ALTER TABLE `lesson_quiz_attempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `otps`
--
ALTER TABLE `otps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `parents`
--
ALTER TABLE `parents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `parent_email_log`
--
ALTER TABLE `parent_email_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `question_bank`
--
ALTER TABLE `question_bank`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=144;

--
-- AUTO_INCREMENT for table `schools`
--
ALTER TABLE `schools`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `student_fees`
--
ALTER TABLE `student_fees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_knowledge_profile`
--
ALTER TABLE `student_knowledge_profile`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_parents`
--
ALTER TABLE `student_parents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `timetables`
--
ALTER TABLE `timetables`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `topics`
--
ALTER TABLE `topics`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `achievements`
--
ALTER TABLE `achievements`
  ADD CONSTRAINT `achievements_ibfk_1` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `achievements_ibfk_2` FOREIGN KEY (`awarded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `activities_ibfk_2` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `activity_registrations`
--
ALTER TABLE `activity_registrations`
  ADD CONSTRAINT `activity_registrations_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `activity_registrations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `activity_results`
--
ALTER TABLE `activity_results`
  ADD CONSTRAINT `activity_results_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `activity_results_ibfk_2` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `activity_results_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ai_answers`
--
ALTER TABLE `ai_answers`
  ADD CONSTRAINT `ai_answers_ibfk_1` FOREIGN KEY (`ai_question_id`) REFERENCES `ai_questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ai_answers_ibfk_2` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ai_questions`
--
ALTER TABLE `ai_questions`
  ADD CONSTRAINT `ai_questions_ibfk_1` FOREIGN KEY (`ai_session_id`) REFERENCES `ai_sessions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ai_sessions`
--
ALTER TABLE `ai_sessions`
  ADD CONSTRAINT `ai_sessions_ibfk_1` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ai_sessions_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ai_sessions_ibfk_3` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_3` FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  ADD CONSTRAINT `attendance_sessions_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_sessions_ibfk_2` FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `batches`
--
ALTER TABLE `batches`
  ADD CONSTRAINT `batches_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `batches_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `batches_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `batches_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `batch_activity_assignments`
--
ALTER TABLE `batch_activity_assignments`
  ADD CONSTRAINT `batch_activity_assignments_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `batch_activity_assignments_ibfk_2` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `batch_activity_assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `batch_assignment_acknowledgments`
--
ALTER TABLE `batch_assignment_acknowledgments`
  ADD CONSTRAINT `batch_assignment_acknowledgments_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `batch_activity_assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `batch_assignment_acknowledgments_ibfk_2` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `batch_students`
--
ALTER TABLE `batch_students`
  ADD CONSTRAINT `batch_students_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `batch_students_ibfk_2` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificates_ibfk_2` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `chapters`
--
ALTER TABLE `chapters`
  ADD CONSTRAINT `chapters_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chess_challenges`
--
ALTER TABLE `chess_challenges`
  ADD CONSTRAINT `fk_cc_challenged` FOREIGN KEY (`challenged_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cc_challenger` FOREIGN KEY (`challenger_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cc_game` FOREIGN KEY (`game_id`) REFERENCES `chess_games` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `chess_coach_assignments`
--
ALTER TABLE `chess_coach_assignments`
  ADD CONSTRAINT `fk_cca_admin` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cca_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cca_student` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cca_teacher` FOREIGN KEY (`teacher_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chess_games`
--
ALTER TABLE `chess_games`
  ADD CONSTRAINT `fk_cg_black` FOREIGN KEY (`black_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cg_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cg_white` FOREIGN KEY (`white_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `chess_live_sessions`
--
ALTER TABLE `chess_live_sessions`
  ADD CONSTRAINT `fk_cls_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cls_student` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cls_teacher` FOREIGN KEY (`teacher_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chess_ratings`
--
ALTER TABLE `chess_ratings`
  ADD CONSTRAINT `fk_chess_rating_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_chess_rating_student` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `competitions`
--
ALTER TABLE `competitions`
  ADD CONSTRAINT `competitions_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `competition_participants`
--
ALTER TABLE `competition_participants`
  ADD CONSTRAINT `competition_participants_ibfk_1` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `competition_participants_ibfk_2` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `competition_results`
--
ALTER TABLE `competition_results`
  ADD CONSTRAINT `competition_results_ibfk_1` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `competition_results_ibfk_2` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `competition_results_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `counseling_logs`
--
ALTER TABLE `counseling_logs`
  ADD CONSTRAINT `fk_cl_request` FOREIGN KEY (`request_id`) REFERENCES `counseling_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cl_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `counseling_requests`
--
ALTER TABLE `counseling_requests`
  ADD CONSTRAINT `fk_cr_assigner` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cr_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cr_student` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cr_teacher` FOREIGN KEY (`assigned_teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lessons_ibfk_2` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lessons_ibfk_3` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lessons_ibfk_4` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lessons_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  ADD CONSTRAINT `lesson_progress_ibfk_1` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lesson_progress_ibfk_2` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `parents`
--
ALTER TABLE `parents`
  ADD CONSTRAINT `parents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `parent_email_log`
--
ALTER TABLE `parent_email_log`
  ADD CONSTRAINT `pel_parent` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pel_student` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schools`
--
ALTER TABLE `schools`
  ADD CONSTRAINT `schools_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_knowledge_profile`
--
ALTER TABLE `student_knowledge_profile`
  ADD CONSTRAINT `student_knowledge_profile_ibfk_1` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_knowledge_profile_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_knowledge_profile_ibfk_3` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_parents`
--
ALTER TABLE `student_parents`
  ADD CONSTRAINT `student_parents_ibfk_1` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_parents_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teachers_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `topics`
--
ALTER TABLE `topics`
  ADD CONSTRAINT `topics_ibfk_1` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
