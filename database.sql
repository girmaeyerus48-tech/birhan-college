-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 03, 2026 at 02:27 PM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `birhan_college_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_semesters`
--

CREATE TABLE `academic_semesters` (
  `id` int(11) NOT NULL,
  `academic_year` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `semester` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'Inactive',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `academic_semesters`
--

INSERT INTO `academic_semesters` (`id`, `academic_year`, `semester`, `status`, `created_at`) VALUES
(1, '2019 ዓ.ም', '1ኛ ሴሚስተር', 'Active', '2026-10-28 12:40:56');

-- --------------------------------------------------------

--
-- Table structure for table `administrators`
--

CREATE TABLE `administrators` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Super Admin',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `administrators`
--

INSERT INTO `administrators` (`id`, `full_name`, `username`, `email`, `password`, `role`, `status`, `created_at`) VALUES
(1, 'ዋና አስተዳዳሪ', 'admin', NULL, '$2y$10$YZDSpnLtTtkwerutvyVC8O3NaZvM9FoAVGGE.1kDJ9.QzIdPmaME6', 'Super Admin', 'active', '2026-10-30 04:26:49'),
(3, 'Birhan College', 'birhan', 'birhan@gmail.com', '$2y$10$9j8l5B/Y/m5CNsxNc7wwNuy6fkIooy5iN6thrDWSkEbMWM1INqg0u', 'Super Admin', 'active', '2026-10-30 06:35:26'),
(4, 'Engedazer Muluneh', 'engedazer', 'engedazer@gmail.com', '$2y$10$hAhrGNj2OJw3X4FFxXfmq.ukhkGpl5pO0EmXWhx1o9h6RIWF0QZou', 'Admission Officer', 'active', '2026-10-30 06:36:45'),
(5, 'Abebe Kebede', 'abebe_admin', 'abebe@gmail.com', '$2y$10$2zC7mWzoEzPz7ZEaTYxSG.ApvYFLWeg05DRvCYPxjs7pzVLdKzszq', 'Content Admin', 'active', '2026-10-30 06:40:19'),
(6, 'Tsehay Gezahegn', 'tsehay.g', 'tsehay@gmail.com', '$2y$10$ODN8q4Njp/m.SbxNjvOxS.MEC1ouSLIqjOKLOe3cSP/u.Dm.McT9W', 'Department Head', 'active', '2026-10-30 06:40:44');

-- --------------------------------------------------------

--
-- Table structure for table `admissions`
--

CREATE TABLE `admissions` (
  `id` int(11) NOT NULL,
  `reference_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `program` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `department` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `document_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admissions`
--

INSERT INTO `admissions` (`id`, `reference_code`, `full_name`, `email`, `phone`, `program`, `created_at`, `department`, `gender`, `dob`, `document_path`, `status`) VALUES
(5, 'BC-2018-99C94', 'Frew Gemechu', 'fgemechu.25@gmail.com', '0925477665', 'Computer Science / IT', '2026-10-30 06:48:13', NULL, 'Male', '2004-06-23', 'uploads/documents/DOC_1793342893_4441.pdf', 'Pending'),
(6, 'BC-2018-4BCBA', 'Abebe Kebede', 'abebe@gmail.com', '0921212121', 'Accounting and Finance', '2026-10-30 12:59:25', NULL, 'Male', '2005-06-14', 'uploads/documents/DOC_1793365165_2562.pdf', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `posted_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `reference_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `program_id` int(11) NOT NULL,
  `previous_education` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Pending','Approved','Rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `reference_no`, `full_name`, `phone`, `email`, `program_id`, `previous_education`, `status`, `applied_at`) VALUES
(1, 'BC-CF1BE2', 'Frew Gemechu', '0925477665', 'fgemechu.25@gmail.com', 1, '12', 'Pending', '2026-10-28 06:58:20'),
(2, 'BC-D3F283', 'Frew Gemechu', '0925477665', 'fgemechu.25@gmail.com', 2, '12', 'Pending', '2026-10-28 07:45:01');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `action_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `performed_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `course_title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `credit_hours` int(11) NOT NULL DEFAULT 3,
  `year_semester` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_allocations`
--

CREATE TABLE `course_allocations` (
  `id` int(11) NOT NULL,
  `teacher_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `course_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `academic_year` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_materials`
--

CREATE TABLE `course_materials` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_registrations`
--

CREATE TABLE `course_registrations` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `student_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `courses` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `academic_year` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '2019 ዓ.ም',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_registrations`
--

INSERT INTO `course_registrations` (`id`, `student_id`, `student_name`, `department`, `courses`, `academic_year`, `status`, `created_at`) VALUES
(3, '18', 'Frew Gemechu', 'Computer Science', 'Object-Oriented Programming (Java)', '2019 ዓ.ም', 'Approved', '2026-10-30 10:56:36'),
(4, '18', 'Frew Gemechu', 'Computer Science', 'Database Systems', '2019 ዓ.ም', 'Approved', '2026-10-30 10:56:39'),
(5, '18', 'Frew Gemechu', 'Computer Science', 'Data Structures & Algorithms', '2019 ዓ.ም', 'Approved', '2026-10-30 10:56:43'),
(6, '18', 'Frew Gemechu', 'Computer Science', 'Computer Networks', '2019 ዓ.ም', 'Approved', '2026-10-30 10:56:47'),
(7, '18', 'Frew Gemechu', 'Computer Science', 'Web Programming', '2019 ዓ.ም', 'Approved', '2026-10-30 10:56:49');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `dept_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents_gallery`
--

CREATE TABLE `documents_gallery` (
  `id` int(11) NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Document',
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `documents_gallery`
--

INSERT INTO `documents_gallery` (`id`, `title`, `category`, `file_path`, `created_at`) VALUES
(1, 'ማስታወቂያ', 'Gallery', '../uploads/1793302049_ቢረሃነ ሳመፐል.jpg', '2026-10-29 19:27:29');

-- --------------------------------------------------------

--
-- Table structure for table `examinations`
--

CREATE TABLE `examinations` (
  `id` int(11) NOT NULL,
  `exam_title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `exam_date` date NOT NULL,
  `exam_time` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `academic_year` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '2019 ዓ.ም',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_schedules`
--

CREATE TABLE `exam_schedules` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `course_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `exam_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `exam_date` date NOT NULL,
  `start_time` time NOT NULL,
  `room_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news_announcements`
--

CREATE TABLE `news_announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'General',
  `publish_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `id` int(11) NOT NULL,
  `title_am` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title_en` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_am` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `tuition_fee` decimal(10,2) DEFAULT 0.00,
  `description_en` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`id`, `title_am`, `title_en`, `category`, `slug`, `description_am`, `duration`, `created_at`, `tuition_fee`, `description_en`) VALUES
(1, 'አካውንቲንግ እና ፋይናንስ (ዲግሪ)', 'Accounting & Finance (Degree)', 'Degree', 'accounting-degree', 'በመጀመሪያ ዲግሪ በመደበኛ፣ በኤክስቴንሽን እና በርቀት ፕሮግራም የሚሰጥ።', '3-4 Years', '2026-10-27 14:36:09', '0.00', NULL),
(2, 'ማኔጅመንት (ዲግሪ)', 'Management (Degree)', 'Degree', 'management-degree', 'በኤክስቴንሽን እና በርቀት ብቻ የሚሰጥ (መደበኛ ተዘግቷል)።', '3-4 Years', '2026-10-27 14:36:09', '0.00', NULL),
(3, 'ፋርማሲ (ቲቪኢቲ - ደረጃ IV)', 'Pharmacy (TVET L-IV)', 'TVET', 'pharmacy-tvet', 'በመደበኛ ብቻ የሚሰጥ (ቦታዎች እየተጠናቀቁ ነው)።', '2 Years', '2026-10-27 14:36:09', '0.00', NULL),
(4, 'ዌብ ልማት እና ዳታቤዝ አስተዳደር (ቲቪኢቲ - ደረጃ IV)', 'Web Dev & Database Admin (TVET L-IV)', 'TVET', 'database-tvet', 'በመደበኛ እና በኤክስቴንሽን የሚሰጥ።', '2 Years', '2026-10-27 14:36:09', '0.00', NULL),
(5, 'አካውንቲንግ እና ፋይናንስ (ቲቪኢቲ - ደረጃ IV)', 'Accounting & Finance (TVET L-IV)', 'TVET', 'accounting-tvet', 'በመደበኛና በኤክስቴንሽን የሚሰጥ።', '2 Years', '2026-10-27 14:36:09', '0.00', NULL),
(6, 'ሶሻል ሳይንስ (ሪሜዲያል)', 'Social Science (Remedial)', 'Remedial', 'remedial-social', 'በመደበኛና በኤክስቴንሽን የሚሰጥ የሪሜዲያል ፕሮግራም።', '1 Year', '2026-10-27 14:36:09', '1000.00', ''),
(7, 'ናቹራል ሳይንስ (ሪሜዲያል)', 'Natural Science (Remedial)', 'Remedial', 'remedial-natural', 'በመደበኛና በኤክስቴንሽን የሚሰጥ የሪሜዲያል ፕሮግራም።', '1 Year', '2026-10-27 14:36:09', '1200.00', '');

-- --------------------------------------------------------

--
-- Table structure for table `roles_permissions`
--

CREATE TABLE `roles_permissions` (
  `id` int(11) NOT NULL,
  `role_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permissions` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_content`
--

CREATE TABLE `site_content` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_teachers`
--

CREATE TABLE `staff_teachers` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qualification` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `username` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_teachers`
--

INSERT INTO `staff_teachers` (`id`, `full_name`, `position`, `qualification`, `department`, `phone`, `email`, `created_at`, `username`) VALUES
(5, 'Frew Gemechu', 'Instructor / Lecturer', 'Master of Science (MSc / MA)', 'Accounting and Finance', '0925477665', 'fgemechu.25@gmail.com', '2026-10-30 04:59:25', 'frew.g'),
(6, 'Frew Gemechu', 'Head of Department', 'PhD', 'Computer Science / IT', '0925477665', 'fgemechu.25@gmail.com', '2026-10-30 12:53:02', 'frew.g1');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year_level` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gpa` decimal(3,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reference_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `academic_year` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'Active',
  `dob` date DEFAULT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_id`, `full_name`, `department`, `year_level`, `gpa`, `created_at`, `reference_code`, `gender`, `phone`, `academic_year`, `status`, `dob`, `username`, `password`, `document_path`, `date_of_birth`, `email`) VALUES
(18, 'BC/2019/001', 'Frew Gemechu', 'Computer Science / IT', '1st Year (አንደኛ ዓመት)', '0.00', '2026-10-30 07:10:02', 'BC-2018-99C94', 'Male', '0925477665', '2019 ዓ.ም', 'Active', '2004-06-23', 'BC/2019/001', '$2y$10$7XoecAPOmlKeHUsJiSgk4.pdVL/Neog3gLPtVk0ZArie1TBoIljZi', 'uploads/documents/DOC_1793342893_4441.pdf', NULL, 'fgemechu.25@gmail.com'),
(19, 'BC/2019/002', 'Abebe Kebede', 'Accounting and Finance', '1st Year (አንደኛ ዓመት)', '0.00', '2026-10-30 13:01:44', 'BC-2018-4BCBA', 'Male', '0921212121', '2019 ዓ.ም', 'Active', '2005-06-14', 'BC/2019/002', '$2y$10$IkTPswUHykOzKMZeU/H2CubdNp/mEjFwqhO15o7bb1irUVw8dPC4O', 'uploads/documents/DOC_1793365165_2562.pdf', NULL, 'abebe@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `student_announcements`
--

CREATE TABLE `student_announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `posted_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'ኮሌጅ አስተዳደር',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_appeals`
--

CREATE TABLE `student_appeals` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `student_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `appeal_type` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_attendance`
--

CREATE TABLE `student_attendance` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `student_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `course_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attendance_date` date NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'Present',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_document_requests`
--

CREATE TABLE `student_document_requests` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `student_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_type` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_grades`
--

CREATE TABLE `student_grades` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `course_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `semester` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `academic_year` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `assessment_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_graduations`
--

CREATE TABLE `student_graduations` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `student_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cgpa` decimal(3,2) NOT NULL,
  `graduation_year` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '2019 ዓ.ም',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_materials`
--

CREATE TABLE `student_materials` (
  `id` int(11) NOT NULL,
  `course_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'መምህር',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_payments`
--

CREATE TABLE `student_payments` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `student_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `semester` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '1ኛ ሴሚስተር',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'Paid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_receipts`
--

CREATE TABLE `student_receipts` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `receipt_no` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_for` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_date` date NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'Completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_transcripts`
--

CREATE TABLE `student_transcripts` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `student_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cgpa` decimal(3,2) DEFAULT 0.00,
  `total_credits` int(11) DEFAULT 0,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'Draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_users`
--

CREATE TABLE `student_users` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_users`
--

INSERT INTO `student_users` (`id`, `student_id`, `username`, `password`, `full_name`, `created_at`) VALUES
(1, 'BC/2019/001', 'BC/2019/001', '$2y$10$sQpEt4UvpRzGkQDhSPTnXe..usEJ8FoE9TBm5chepfCC51P/uR4JC', 'Abebe Kebede', '2026-10-28 13:45:09'),
(2, 'BC/2019/002', 'BC/2019/002', '$2y$10$16zrtvqkE3/XJERy3QweBuJj.6K2MdnrLS9bHvggpjv4vod0XVG06', 'Almaz Yalew', '2026-10-28 14:04:12'),
(3, 'BC/2019/003', 'BC/2019/003', '$2y$10$Hubr9lieEnsal2qM/Uov4OVYKoAjKmt7E7HrInsgLYtn0k4lZP0Vq', 'Almaz Yalew', '2026-10-28 14:16:13');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `staff_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qualification` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teacher_assignments`
--

CREATE TABLE `teacher_assignments` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `due_date` date NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teacher_notifications`
--

CREATE TABLE `teacher_notifications` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teacher_timetables`
--

CREATE TABLE `teacher_timetables` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `day_of_week` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `course_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `room_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `timetables`
--

CREATE TABLE `timetables` (
  `id` int(11) NOT NULL,
  `course_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `day_of_week` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `time_slot` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `room_no` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instructor` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `username`, `password`, `role`, `created_at`) VALUES
(3, 'Frew Gemechu', 'frew.g', '$2y$10$a0duNZdKNMuTuX1RnlHXieJ.KJFQ1JBb7G39OtyMW.jif5xAMBgna', 'Instructor', '2026-10-30 04:59:25'),
(4, 'Frew Gemechu', 'frew.g1', '$2y$10$PnTR51r9I9eNitFJ9dtYvefy0or7Zx4d/i73uIfd/gnj7XNUDcv.S', 'Department Head', '2026-10-30 12:53:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_semesters`
--
ALTER TABLE `academic_semesters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `administrators`
--
ALTER TABLE `administrators`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `admissions`
--
ALTER TABLE `admissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_code` (`reference_code`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_no` (`reference_no`),
  ADD KEY `program_id` (`program_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_code` (`course_code`);

--
-- Indexes for table `course_allocations`
--
ALTER TABLE `course_allocations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_materials`
--
ALTER TABLE `course_materials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_registrations`
--
ALTER TABLE `course_registrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `documents_gallery`
--
ALTER TABLE `documents_gallery`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `examinations`
--
ALTER TABLE `examinations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exam_schedules`
--
ALTER TABLE `exam_schedules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news_announcements`
--
ALTER TABLE `news_announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `roles_permissions`
--
ALTER TABLE `roles_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `site_content`
--
ALTER TABLE `site_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `staff_teachers`
--
ALTER TABLE `staff_teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `student_announcements`
--
ALTER TABLE `student_announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_appeals`
--
ALTER TABLE `student_appeals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_attendance`
--
ALTER TABLE `student_attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_document_requests`
--
ALTER TABLE `student_document_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_grades`
--
ALTER TABLE `student_grades`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_graduations`
--
ALTER TABLE `student_graduations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_materials`
--
ALTER TABLE `student_materials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_payments`
--
ALTER TABLE `student_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_receipts`
--
ALTER TABLE `student_receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_transcripts`
--
ALTER TABLE `student_transcripts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_users`
--
ALTER TABLE `student_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staff_id` (`staff_id`);

--
-- Indexes for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teacher_notifications`
--
ALTER TABLE `teacher_notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teacher_timetables`
--
ALTER TABLE `teacher_timetables`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `timetables`
--
ALTER TABLE `timetables`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_semesters`
--
ALTER TABLE `academic_semesters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `administrators`
--
ALTER TABLE `administrators`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `admissions`
--
ALTER TABLE `admissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_allocations`
--
ALTER TABLE `course_allocations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_materials`
--
ALTER TABLE `course_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_registrations`
--
ALTER TABLE `course_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `documents_gallery`
--
ALTER TABLE `documents_gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `examinations`
--
ALTER TABLE `examinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_schedules`
--
ALTER TABLE `exam_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news_announcements`
--
ALTER TABLE `news_announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `roles_permissions`
--
ALTER TABLE `roles_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_content`
--
ALTER TABLE `site_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_teachers`
--
ALTER TABLE `staff_teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `student_announcements`
--
ALTER TABLE `student_announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_appeals`
--
ALTER TABLE `student_appeals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_attendance`
--
ALTER TABLE `student_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_document_requests`
--
ALTER TABLE `student_document_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_grades`
--
ALTER TABLE `student_grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_graduations`
--
ALTER TABLE `student_graduations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_materials`
--
ALTER TABLE `student_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_payments`
--
ALTER TABLE `student_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_receipts`
--
ALTER TABLE `student_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_transcripts`
--
ALTER TABLE `student_transcripts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_users`
--
ALTER TABLE `student_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teacher_notifications`
--
ALTER TABLE `teacher_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teacher_timetables`
--
ALTER TABLE `teacher_timetables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `timetables`
--
ALTER TABLE `timetables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
