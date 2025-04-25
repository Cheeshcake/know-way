-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 25, 2025 at 10:19 PM
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
-- Database: `know-way`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `creator_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `video_type` enum('youtube','vimeo','uploaded') DEFAULT NULL,
  `status` enum('draft','pending','published') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `creator_id`, `title`, `description`, `image`, `video`, `video_type`, `status`, `created_at`, `updated_at`, `approved_at`, `approved_by`) VALUES
(2, 12, 'First Course', 'First Course Ever Created', 'course_1741735925.png', NULL, NULL, 'published', '2025-03-11 23:32:05', '2025-04-24 14:00:00', '2025-04-24 14:00:00', NULL),
(10, 15, 'test', 'test', 'course_1741736924.png', NULL, NULL, 'pending', '2025-03-11 23:48:44', NULL, NULL, NULL),
(16, 17, 'Python Course', 'Python for Beginners - Learn Coding with Python in 1 Hour', 'course_1745552342_9976.jpeg', 'https://youtu.be/kqtD5dpn9C8', 'youtube', 'published', '2025-04-25 03:39:02', NULL, NULL, NULL),
(17, 18, 'Testing Learner Account', 'This course is an example of how it looks to create a course with your own profile', NULL, 'https://youtu.be/Jc2UW3nlNBA', 'youtube', 'pending', '2025-04-25 18:55:13', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_comments`
--

CREATE TABLE `course_comments` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_comments`
--

INSERT INTO `course_comments` (`id`, `course_id`, `user_id`, `comment`, `created_at`, `updated_at`) VALUES
(1, 10, 17, 'I really like this', '2025-04-25 02:38:54', NULL),
(2, 16, 17, 'I love this', '2025-04-25 03:52:14', NULL),
(3, 16, 18, 'Thanks for making everything clear!', '2025-04-25 17:02:05', NULL),
(4, 2, 18, 'Yes', '2025-04-25 17:07:46', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_likes`
--

CREATE TABLE `course_likes` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_likes`
--

INSERT INTO `course_likes` (`id`, `course_id`, `user_id`, `created_at`) VALUES
(4, 10, 17, '2025-04-25 02:37:36'),
(5, 2, 17, '2025-04-25 02:57:51'),
(6, 16, 17, '2025-04-25 03:39:12'),
(7, 16, 18, '2025-04-25 17:02:10'),
(8, 2, 18, '2025-04-25 17:07:48'),
(9, 17, 18, '2025-04-25 18:55:19');

-- --------------------------------------------------------

--
-- Table structure for table `course_quizzes`
--

CREATE TABLE `course_quizzes` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_quizzes`
--

INSERT INTO `course_quizzes` (`id`, `course_id`, `title`, `description`, `created_at`) VALUES
(4, 16, 'Quiz about Python', 'Five very easy multiple-choice questions on the topic of Python programming', '2025-04-25 03:51:33');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_answers`
--

CREATE TABLE `quiz_answers` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer_text` text NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_answers`
--

INSERT INTO `quiz_answers` (`id`, `question_id`, `answer_text`, `is_correct`) VALUES
(1, 1, '.java', 0),
(2, 1, '.mp4', 0),
(3, 1, '.py', 1),
(4, 1, '.xml', 0),
(5, 2, 'console.log(\"Hello\")', 0),
(6, 2, 'print(\"Hello\")', 1),
(7, 2, 'printf(\"Hello\"', 0),
(8, 2, 'echo(\"Hello\")', 0),
(9, 3, '//', 0),
(10, 3, '<!--', 0),
(11, 3, '#', 1),
(12, 3, '**', 0),
(13, 4, '32', 0),
(14, 4, ' 5', 1),
(15, 4, '6', 0),
(16, 4, 'Error', 0),
(17, 5, 'value_2', 1),
(18, 5, 'value-2', 0),
(19, 5, '2value', 0),
(20, 5, 'value 2', 0);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `score` int(11) DEFAULT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_attempts`
--

INSERT INTO `quiz_attempts` (`id`, `quiz_id`, `user_id`, `score`, `completed`, `started_at`, `completed_at`) VALUES
(1, 4, 17, NULL, 0, '2025-04-25 04:00:31', '2025-04-25 04:00:31'),
(2, 4, 17, NULL, 0, '2025-04-25 04:03:04', '2025-04-25 04:03:04'),
(3, 4, 17, NULL, 0, '2025-04-25 04:03:12', '2025-04-25 04:03:12'),
(4, 4, 17, 1, 1, '2025-04-25 04:05:10', '2025-04-25 04:05:10'),
(5, 4, 17, 0, 1, '2025-04-25 04:05:39', '2025-04-25 04:05:39'),
(6, 4, 17, 0, 1, '2025-04-25 04:05:55', '2025-04-25 04:05:55'),
(7, 4, 17, 0, 1, '2025-04-25 04:06:44', '2025-04-25 04:06:44'),
(8, 4, 17, 0, 1, '2025-04-25 04:06:52', '2025-04-25 04:06:52'),
(9, 4, 17, 0, 1, '2025-04-25 04:06:53', '2025-04-25 04:06:53'),
(10, 4, 17, 0, 1, '2025-04-25 04:06:54', '2025-04-25 04:06:54'),
(11, 4, 17, 0, 1, '2025-04-25 04:06:54', '2025-04-25 04:06:54'),
(12, 4, 17, 0, 1, '2025-04-25 04:06:54', '2025-04-25 04:06:54'),
(13, 4, 17, 1, 1, '2025-04-25 04:08:25', '2025-04-25 04:08:25'),
(14, 4, 17, 5, 1, '2025-04-25 04:08:38', '2025-04-25 04:08:38'),
(15, 4, 17, 1, 1, '2025-04-25 04:15:48', '2025-04-25 04:15:48'),
(16, 4, 17, 1, 1, '2025-04-25 04:16:17', '2025-04-25 04:16:17'),
(17, 4, 17, 1, 1, '2025-04-25 04:16:44', '2025-04-25 04:16:44'),
(18, 4, 17, 1, 1, '2025-04-25 04:17:08', '2025-04-25 04:17:08'),
(19, 4, 17, 1, 1, '2025-04-25 04:17:09', '2025-04-25 04:17:09'),
(20, 4, 17, 1, 1, '2025-04-25 04:17:34', '2025-04-25 04:17:35'),
(21, 4, 17, 5, 1, '2025-04-25 04:18:08', '2025-04-25 04:18:08');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempt_answers`
--

CREATE TABLE `quiz_attempt_answers` (
  `id` int(11) NOT NULL,
  `attempt_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `user_answer_id` int(11) DEFAULT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_attempt_answers`
--

INSERT INTO `quiz_attempt_answers` (`id`, `attempt_id`, `user_id`, `quiz_id`, `question_id`, `user_answer_id`, `is_correct`) VALUES
(1, 2, 17, 4, 1, 1, 0),
(2, 2, 17, 4, 2, 6, 1),
(3, 2, 17, 4, 3, 11, 1),
(4, 2, 17, 4, 4, 13, 0),
(5, 2, 17, 4, 5, 17, 1),
(6, 3, 17, 4, 1, 1, 0),
(7, 3, 17, 4, 2, 5, 0),
(8, 3, 17, 4, 3, 12, 0),
(9, 3, 17, 4, 4, 16, 0),
(10, 3, 17, 4, 5, 17, 1),
(11, 4, 17, 4, 1, 4, 0),
(12, 4, 17, 4, 2, 5, 0),
(13, 4, 17, 4, 3, 10, 0),
(14, 4, 17, 4, 4, 16, 0),
(15, 4, 17, 4, 5, 17, 1),
(16, 5, 17, 4, 1, 1, 0),
(17, 5, 17, 4, 2, 5, 0),
(18, 5, 17, 4, 3, 10, 0),
(19, 5, 17, 4, 4, 16, 0),
(20, 5, 17, 4, 5, 18, 0),
(21, 6, 17, 4, 1, 1, 0),
(22, 6, 17, 4, 2, 5, 0),
(23, 6, 17, 4, 3, 10, 0),
(24, 6, 17, 4, 4, 16, 0),
(25, 6, 17, 4, 5, 18, 0),
(26, 7, 17, 4, 1, 1, 0),
(27, 7, 17, 4, 2, 5, 0),
(28, 7, 17, 4, 3, 10, 0),
(29, 7, 17, 4, 4, 16, 0),
(30, 7, 17, 4, 5, 18, 0),
(31, 8, 17, 4, 1, 1, 0),
(32, 8, 17, 4, 2, 5, 0),
(33, 8, 17, 4, 3, 10, 0),
(34, 8, 17, 4, 4, 16, 0),
(35, 8, 17, 4, 5, 18, 0),
(36, 9, 17, 4, 1, 1, 0),
(37, 9, 17, 4, 2, 5, 0),
(38, 9, 17, 4, 3, 10, 0),
(39, 9, 17, 4, 4, 16, 0),
(40, 9, 17, 4, 5, 18, 0),
(41, 10, 17, 4, 1, 1, 0),
(42, 10, 17, 4, 2, 5, 0),
(43, 10, 17, 4, 3, 10, 0),
(44, 10, 17, 4, 4, 16, 0),
(45, 10, 17, 4, 5, 18, 0),
(46, 11, 17, 4, 1, 1, 0),
(47, 11, 17, 4, 2, 5, 0),
(48, 11, 17, 4, 3, 10, 0),
(49, 11, 17, 4, 4, 16, 0),
(50, 11, 17, 4, 5, 18, 0),
(51, 12, 17, 4, 1, 1, 0),
(52, 12, 17, 4, 2, 5, 0),
(53, 12, 17, 4, 3, 10, 0),
(54, 12, 17, 4, 4, 16, 0),
(55, 12, 17, 4, 5, 18, 0),
(56, 13, 17, 4, 1, 1, 0),
(57, 13, 17, 4, 2, 5, 0),
(58, 13, 17, 4, 3, 9, 0),
(59, 13, 17, 4, 4, 13, 0),
(60, 13, 17, 4, 5, 17, 1),
(61, 14, 17, 4, 1, 3, 1),
(62, 14, 17, 4, 2, 6, 1),
(63, 14, 17, 4, 3, 11, 1),
(64, 14, 17, 4, 4, 14, 1),
(65, 14, 17, 4, 5, 17, 1),
(66, 15, 17, 4, 1, 1, 0),
(67, 15, 17, 4, 2, 5, 0),
(68, 15, 17, 4, 3, 9, 0),
(69, 15, 17, 4, 4, 14, 1),
(70, 15, 17, 4, 5, 18, 0),
(71, 16, 17, 4, 1, 1, 0),
(72, 16, 17, 4, 2, 5, 0),
(73, 16, 17, 4, 3, 9, 0),
(74, 16, 17, 4, 4, 14, 1),
(75, 16, 17, 4, 5, 18, 0),
(76, 17, 17, 4, 1, 1, 0),
(77, 17, 17, 4, 2, 5, 0),
(78, 17, 17, 4, 3, 9, 0),
(79, 17, 17, 4, 4, 14, 1),
(80, 17, 17, 4, 5, 18, 0),
(81, 18, 17, 4, 1, 1, 0),
(82, 18, 17, 4, 2, 5, 0),
(83, 18, 17, 4, 3, 9, 0),
(84, 18, 17, 4, 4, 14, 1),
(85, 18, 17, 4, 5, 18, 0),
(86, 19, 17, 4, 1, 1, 0),
(87, 19, 17, 4, 2, 5, 0),
(88, 19, 17, 4, 3, 9, 0),
(89, 19, 17, 4, 4, 14, 1),
(90, 19, 17, 4, 5, 18, 0),
(91, 20, 17, 4, 1, 1, 0),
(92, 20, 17, 4, 2, 5, 0),
(93, 20, 17, 4, 3, 9, 0),
(94, 20, 17, 4, 4, 14, 1),
(95, 20, 17, 4, 5, 18, 0),
(96, 21, 17, 4, 1, 3, 1),
(97, 21, 17, 4, 2, 6, 1),
(98, 21, 17, 4, 3, 11, 1),
(99, 21, 17, 4, 4, 14, 1),
(100, 21, 17, 4, 5, 17, 1);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `type` enum('multiple_choice','true_false','text') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_questions`
--

INSERT INTO `quiz_questions` (`id`, `quiz_id`, `question`, `type`, `created_at`) VALUES
(1, 4, 'What is the correct file extension for Python files?', 'multiple_choice', '2025-04-25 03:51:33'),
(2, 4, 'How do you print something in Python?', 'multiple_choice', '2025-04-25 03:51:33'),
(3, 4, 'Which symbol is used to start a comment in Python?', 'multiple_choice', '2025-04-25 03:51:33'),
(4, 4, 'What is the output of: print(3 + 2)?', 'multiple_choice', '2025-04-25 03:51:33'),
(5, 4, 'Which of these is a valid variable name in Python?', 'multiple_choice', '2025-04-25 03:51:33');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` varchar(20) DEFAULT 'learner',
  `phone` varchar(20) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `role`, `phone`, `bio`, `avatar`) VALUES
(12, 'Farah Ncir', 'ncirfarah7@gmail.com', '$2y$10$V2r4aUEpuV1G9U2UUWK9puWJZqI5Lg.H5qEJubtRNtA.IIJpEF/0.', '2025-04-15 14:34:30', 'admin', NULL, NULL, NULL),
(15, 'touta ncir', 'touta@gmail.com', '$2y$10$iFb95r741La03GWMZWOys.jFRxnduBfPJNtdqZZJzzvgKxckUahcW', '2025-04-23 21:57:19', 'learner', NULL, NULL, NULL),
(16, 'erza', 'erza@gmail.com', '$2y$10$7HrXBr4BQ7aV0AySEMlQBOJuemNuC0FE5BVLpZCxF/hP8Bq7rKdBK', '2025-04-23 23:18:33', 'learner', NULL, NULL, NULL),
(17, 'Aziz', 'aziz@gmail.com', '$2y$10$DM/XJ.k7PZMA94DLbTl5WODI50fWHvgfDUl/Qga2ycvESzwvxM6eC', '2025-04-25 02:09:30', 'admin', '', '', '../uploads/avatars/avatar_17_1745547013.jpg'),
(18, 'Learner', 'learner@gmail.com', '$2y$10$UzdUxCPFwBkjQZAXLeRMc.gYHoEfYvbcH6c5z.1G5gAElMvwCnCyS', '2025-04-25 04:24:40', 'learner', '', '', '../uploads/avatars/avatar_680bd7efd8c8a.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `creator_id` (`creator_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `course_comments`
--
ALTER TABLE `course_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `course_likes`
--
ALTER TABLE `course_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`course_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `course_quizzes`
--
ALTER TABLE `course_quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `quiz_attempt_answers`
--
ALTER TABLE `quiz_attempt_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attempt_id` (`attempt_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `course_comments`
--
ALTER TABLE `course_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `course_likes`
--
ALTER TABLE `course_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `course_quizzes`
--
ALTER TABLE `course_quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `quiz_attempt_answers`
--
ALTER TABLE `quiz_attempt_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `courses_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `course_comments`
--
ALTER TABLE `course_comments`
  ADD CONSTRAINT `course_comments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_likes`
--
ALTER TABLE `course_likes`
  ADD CONSTRAINT `course_likes_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_quizzes`
--
ALTER TABLE `course_quizzes`
  ADD CONSTRAINT `course_quizzes_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD CONSTRAINT `quiz_answers_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `quiz_attempts_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `course_quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `quiz_questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `course_quizzes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
