-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 10, 2025 at 06:17 PM
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
-- Database: `quiz_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `quiz_code` varchar(8) NOT NULL,
  `title` varchar(255) NOT NULL,
  `questions` text NOT NULL,
  `time_limit` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `start_datetime` datetime NOT NULL DEFAULT current_timestamp(),
  `end_datetime` datetime NOT NULL DEFAULT (current_timestamp() + interval 1 hour)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `quiz_code`, `title`, `questions`, `time_limit`, `created_at`, `created_by`, `start_datetime`, `end_datetime`) VALUES
(30, '1ab263c6', 'coding', '[{\"type\":\"identification\",\"text\":\"MMMM\",\"answer\":\"Lapu-Lapu\"}]', 20, '2025-04-09 00:11:48', 1, '2025-04-09 17:45:00', '2025-04-09 16:55:00'),
(31, '434d63e6', 'SHABU', '[{\"type\":\"identification\",\"text\":\"MMMM\",\"answer\":\"Lapu-Lapu\"}]', 20, '2025-04-09 00:15:28', 1, '2025-04-09 17:50:00', '2025-04-09 18:00:00'),
(32, '140e6c7e', 'pop', '[{\"type\":\"identification\",\"text\":\"erre\",\"answer\":\"erre\"}]', 20, '2025-04-10 21:21:00', 1, '2025-04-10 21:21:00', '2025-04-10 22:21:00'),
(33, '3673b752', 'baste quiz', '[{\"type\":\"identification\",\"text\":\"yeh yeh\",\"answer\":\"BASTE\"}]', 30, '2025-04-10 22:29:46', 2, '2025-04-10 22:29:46', '2025-04-10 23:29:46'),
(34, 'c33c5d87', 'baste quiz 02', '[{\"type\":\"multiple_choice\",\"text\":\"gfhggfgey et wt\",\"answer\":{\"options\":[\"sdsd\",\"dsds\",\"sdsd\",\"sdsd\"],\"correct\":\"sdsd\"}}]', 30, '2025-04-10 23:24:14', 2, '2025-04-10 23:24:14', '2025-04-11 00:24:14'),
(36, '7251122e', 'baste quiz 03', '[{\"type\":\"multiple_choice\",\"text\":\"eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee\",\"answer\":{\"options\":[\"wweeeeeeeeeeeeeeeeeeeeeeeeeeee\",\"eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee\",\"eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee\",\"eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee\"],\"correct\":\"wweeeeeeeeeeeeeeeeeeeeeeeeeeee\"}}]', 20, '2025-04-10 23:31:35', 2, '2025-04-10 23:31:35', '2025-04-11 00:31:35');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_answers`
--

CREATE TABLE `quiz_answers` (
  `id` int(11) NOT NULL,
  `quiz_result_id` int(11) NOT NULL,
  `user_answer` text NOT NULL,
  `correct_answer` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_answers`
--

INSERT INTO `quiz_answers` (`id`, `quiz_result_id`, `user_answer`, `correct_answer`) VALUES
(1, 11, 'ERRE', 'erre');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_results`
--

CREATE TABLE `quiz_results` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) DEFAULT NULL,
  `student_name` varchar(100) DEFAULT NULL,
  `student_email` varchar(100) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `total_questions` int(11) DEFAULT NULL,
  `date_taken` datetime DEFAULT NULL,
  `answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`answers`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_results`
--

INSERT INTO `quiz_results` (`id`, `quiz_id`, `student_name`, `student_email`, `score`, `total_questions`, `date_taken`, `answers`) VALUES
(11, 32, 'francis mateo', 'francismateo711@gmail.com', 1, 1, '2025-04-10 15:31:24', '[{\"question\":\"erre\",\"user_answer\":\"ERRE\",\"correct_answer\":\"erre\"}]'),
(13, 32, 'aira nicolas', 'aira123@gmail.com', 1, 1, '2025-04-10 16:16:34', '[{\"question\":\"erre\",\"user_answer\":\"ERRE\",\"correct_answer\":\"erre\"}]'),
(14, 33, 'aira nicolas', 'aira123@gmail.com', 1, 1, '2025-04-10 16:30:10', '[{\"question\":\"yeh yeh\",\"user_answer\":\"baste\",\"correct_answer\":\"BASTE\"}]');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `dob` date NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(255) DEFAULT NULL,
  `cover_photo` varchar(255) NOT NULL DEFAULT 'cover_default.jpg',
  `bio` text DEFAULT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'student'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `first_name`, `last_name`, `dob`, `username`, `email`, `password`, `created_at`, `profile_picture`, `cover_photo`, `bio`, `role`) VALUES
(1, 'francis', 'mateo', '2002-02-02', 'Hyper', 'francismateo711@gmail.com', '$2y$10$6Xg6MPbZA476NhEX2Y8BLeUIVBTeRww5YInmGJcTFM3r0Eg/weYUm', '2025-04-03 16:05:34', 'uploads/1_1744043567.jpg', 'uploads/1_1744043579.jpg', NULL, 'student'),
(2, 'aira', 'nicolas', '2003-02-02', 'Aira', 'aira123@gmail.com', '$2y$10$///8hHmzUfI92xM4PZwEEODsP1KxhCOamz5k.FvhwPP2SHuvClsxC', '2025-04-08 01:58:07', NULL, 'cover_default.jpg', NULL, 'student');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `dob` date NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(255) NOT NULL,
  `cover_photo` varchar(255) NOT NULL DEFAULT 'cover_default.jpg',
  `bio` text DEFAULT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'teacher'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `first_name`, `last_name`, `dob`, `username`, `email`, `password`, `created_at`, `profile_picture`, `cover_photo`, `bio`, `role`) VALUES
(1, 'Aileen joyce', 'Martinez', '2002-02-02', 'aileenjoyce23', 'aileenjoyce23@gmail.com', '$2y$10$B3mC35yHpNKbeIjJBHP0yOU7HU.CYCMFAoZxtBX9fTOmligsZI1Ma', '2025-04-09 01:08:55', '', 'cover_default.jpg', NULL, 'teacher'),
(2, 'Nolan', 'Grayson', '0000-00-00', 'grayson69', 'grayson69@gmail.com', '$2y$10$RoOaKk10PHjX80d9vBarDuR3mfJ2vJuasl/vzYbS6Ph4tZjhimUJq', '2025-04-09 01:12:33', '', 'cover_default.jpg', NULL, 'teacher');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_quiz_teacher` (`created_by`);

--
-- Indexes for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_result_id` (`quiz_result_id`);

--
-- Indexes for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `quiz_results`
--
ALTER TABLE `quiz_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `fk_quiz_teacher` FOREIGN KEY (`created_by`) REFERENCES `teachers` (`id`);

--
-- Constraints for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD CONSTRAINT `quiz_answers_ibfk_1` FOREIGN KEY (`quiz_result_id`) REFERENCES `quiz_results` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
