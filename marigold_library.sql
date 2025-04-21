-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 18, 2025 at 09:02 PM
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
-- Database: `marigold_library`
--

-- --------------------------------------------------------

--
-- Table structure for table `active_loans`
--

CREATE TABLE `active_loans` (
  `loan_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `loan_date` date NOT NULL,
  `due_date` date NOT NULL,
  `returned_date` date DEFAULT NULL,
  `status` enum('Active','Returned','Overdue') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `student_number` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `active_loans`
--

INSERT INTO `active_loans` (`loan_id`, `student_id`, `book_id`, `title`, `loan_date`, `due_date`, `returned_date`, `status`, `created_at`, `updated_at`, `student_number`) VALUES
(1, 1, 1, '', '2025-04-17', '2025-04-17', '2025-04-18', 'Returned', '2025-04-17 22:49:57', '2025-04-18 01:46:38', 'S-000'),
(2, 2, 11, '', '2025-04-18', '2025-04-18', '2025-04-18', 'Returned', '2025-04-18 00:48:33', '2025-04-18 12:31:33', 'S-001'),
(3, 4, 1, '', '2025-04-18', '2025-04-19', '2025-04-18', 'Returned', '2025-04-18 00:49:40', '2025-04-18 01:47:02', 'S-003'),
(10, 3, 4, '', '2025-04-18', '2025-04-22', '2025-04-18', 'Returned', '2025-04-18 01:01:15', '2025-04-18 15:58:22', 'S-002'),
(11, 5, 1, '', '2025-04-18', '2025-04-19', '2025-04-18', 'Returned', '2025-04-18 01:34:46', '2025-04-18 12:47:17', 'S-004'),
(12, 6, 1, '', '2025-04-18', '2025-04-19', '2025-04-18', 'Returned', '2025-04-18 01:37:38', '2025-04-18 13:30:32', 'S-005'),
(13, 7, 1, '', '2025-04-18', '2025-05-07', '2025-04-18', 'Returned', '2025-04-18 01:40:37', '2025-04-18 16:07:49', 'S-006'),
(34, 4, 1, 'Things Fall Apart', '2025-04-18', '2025-05-02', '2025-04-18', 'Returned', '2025-04-18 13:37:35', '2025-04-18 16:08:32', 'S-003'),
(35, 1, 4, 'IDGAF', '2025-04-18', '2025-05-02', NULL, 'Active', '2025-04-18 13:38:08', '2025-04-18 13:38:08', 'S-000'),
(36, 8, 12, 'The gods are not to blame', '2025-04-18', '2025-05-02', NULL, 'Active', '2025-04-18 13:50:46', '2025-04-18 13:50:46', 'S-007'),
(37, 5, 1, 'Things Fall Apart', '2025-04-18', '2025-05-03', '2025-04-18', 'Returned', '2025-04-18 13:56:12', '2025-04-18 13:56:22', 'S-004'),
(38, 1, 12, 'The gods are not to blame', '2025-04-18', '2025-04-18', '2025-04-18', 'Returned', '2025-04-18 13:57:14', '2025-04-18 14:06:13', 'S-000'),
(39, 5, 1, 'Things Fall Apart', '2025-04-18', '2025-04-18', '2025-04-18', 'Returned', '2025-04-18 14:07:30', '2025-04-18 14:07:43', 'S-004'),
(40, 5, 1, 'Things Fall Apart', '2025-04-18', '2025-04-18', '2025-04-18', 'Returned', '2025-04-18 14:09:39', '2025-04-18 15:53:18', 'S-004'),
(41, 3, 14, 'Saturated', '2025-04-18', '2025-04-18', '2025-04-18', 'Returned', '2025-04-18 15:51:43', '2025-04-18 15:52:08', 'S-002'),
(42, 3, 14, 'Saturated', '2025-04-18', '2025-04-18', '2025-04-18', 'Returned', '2025-04-18 15:54:07', '2025-04-18 15:54:30', 'S-002');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `book_id` int(11) NOT NULL,
  `isbn` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(100) NOT NULL,
  `category_id` int(11) NOT NULL,
  `total_copies` int(11) NOT NULL DEFAULT 0,
  `available_copies` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`book_id`, `isbn`, `title`, `author`, `category_id`, `total_copies`, `available_copies`, `created_at`, `updated_at`) VALUES
(1, '0-306-40615-259', 'Things Fall Apart', 'Chinue Achebe', 8, 85, 53, '2025-04-17 19:24:35', '2025-04-18 16:08:32'),
(4, '0-306-40415-99', 'IDGAF', 'Albert Agyepong', 5, 15, 4, '2025-04-17 19:34:17', '2025-04-18 15:58:22'),
(5, '0-306-40615-55', 'Baby Oil Season', 'Sean Combs', 4, 100, 99, '2025-04-17 19:38:44', '2025-04-18 12:32:33'),
(10, '0-306-40615-222', 'Dont Come for Me', 'Lithe', 1, 12, 11, '2025-04-17 21:32:15', '2025-04-18 10:20:41'),
(11, '0-306-40615-588', 'To The Moon', 'Khalid', 10, 3, 3, '2025-04-17 21:40:31', '2025-04-18 12:31:33'),
(12, '0-306-40615-580', 'The gods are not to blame', 'Ola Rotimi', 8, 2, 1, '2025-04-17 21:50:56', '2025-04-18 14:06:13'),
(14, '0-306-40615-254', 'Saturated', 'Edward Ofosu Mensah', 5, 3, 3, '2025-04-18 15:51:01', '2025-04-18 15:54:30');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `created_at`, `updated_at`) VALUES
(1, 'Fiction', '2025-04-17 16:53:50', '2025-04-17 16:53:50'),
(2, 'Non-Fiction', '2025-04-17 16:53:50', '2025-04-17 16:53:50'),
(3, 'Science Fiction', '2025-04-17 16:53:50', '2025-04-17 16:53:50'),
(4, 'Fantasy', '2025-04-17 16:53:50', '2025-04-17 16:53:50'),
(5, 'Romance', '2025-04-17 16:53:50', '2025-04-17 16:53:50'),
(6, 'Mystery', '2025-04-17 16:53:50', '2025-04-17 16:53:50'),
(7, 'Biography', '2025-04-17 16:53:50', '2025-04-17 16:53:50'),
(8, 'History', '2025-04-17 16:53:50', '2025-04-17 16:53:50'),
(9, 'Science', '2025-04-17 16:53:50', '2025-04-17 16:53:50'),
(10, 'Technology', '2025-04-17 16:53:50', '2025-04-17 16:53:50');

-- --------------------------------------------------------

--
-- Table structure for table `loan_history`
--

CREATE TABLE `loan_history` (
  `history_id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `book_id` int(11) NOT NULL,
  `loan_date` date NOT NULL,
  `due_date` date NOT NULL,
  `returned_date` date NOT NULL,
  `status` enum('On Time','Late','Damaged') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_history`
--

INSERT INTO `loan_history` (`history_id`, `loan_id`, `student_id`, `student_number`, `book_id`, `loan_date`, `due_date`, `returned_date`, `status`, `created_at`) VALUES
(1, 1, 1, 'S-000', 1, '2025-04-17', '2025-04-17', '2025-04-18', '', '2025-04-17 22:49:57'),
(2, 2, 2, 'S-001', 11, '2025-04-18', '2025-04-18', '0000-00-00', 'On Time', '2025-04-18 00:48:33'),
(4, 10, 3, 'S-002', 4, '2025-04-18', '2025-04-22', '0000-00-00', 'On Time', '2025-04-18 01:01:15'),
(5, 11, 5, 'S-004', 1, '2025-04-18', '2025-04-19', '0000-00-00', 'On Time', '2025-04-18 01:34:46'),
(6, 12, 6, 'S-005', 1, '2025-04-18', '2025-04-19', '0000-00-00', 'On Time', '2025-04-18 01:37:38'),
(7, 13, 7, 'S-006', 1, '2025-04-18', '2025-05-07', '0000-00-00', 'On Time', '2025-04-18 01:40:37'),
(8, 2, 2, 'S-001', 11, '2025-04-18', '2025-04-18', '2025-04-18', 'On Time', '2025-04-18 12:31:33'),
(9, 11, 5, 'S-004', 1, '2025-04-18', '2025-04-19', '2025-04-18', 'On Time', '2025-04-18 12:47:17'),
(11, 12, 6, 'S-005', 1, '2025-04-18', '2025-04-19', '2025-04-18', 'On Time', '2025-04-18 13:30:32'),
(12, 37, 5, 'S-004', 1, '2025-04-18', '2025-05-03', '2025-04-18', 'On Time', '2025-04-18 13:56:22'),
(13, 38, 1, 'S-000', 12, '2025-04-18', '2025-04-18', '2025-04-18', 'On Time', '2025-04-18 14:06:13'),
(14, 39, 5, 'S-004', 1, '2025-04-18', '2025-04-18', '2025-04-18', 'On Time', '2025-04-18 14:07:43'),
(15, 41, 3, 'S-002', 14, '2025-04-18', '2025-04-18', '2025-04-18', 'On Time', '2025-04-18 15:52:08'),
(16, 40, 5, 'S-004', 1, '2025-04-18', '2025-04-18', '2025-04-18', 'On Time', '2025-04-18 15:53:18'),
(17, 42, 3, 'S-002', 14, '2025-04-18', '2025-04-18', '2025-04-18', 'On Time', '2025-04-18 15:54:30'),
(18, 10, 3, 'S-002', 4, '2025-04-18', '2025-04-22', '2025-04-18', 'On Time', '2025-04-18 15:58:22'),
(19, 13, 7, 'S-006', 1, '2025-04-18', '2025-05-07', '2025-04-18', 'On Time', '2025-04-18 16:07:49'),
(20, 34, 4, 'S-003', 1, '2025-04-18', '2025-05-02', '2025-04-18', 'On Time', '2025-04-18 16:08:32');

-- --------------------------------------------------------

--
-- Table structure for table `loan_settings`
--

CREATE TABLE `loan_settings` (
  `setting_id` int(11) NOT NULL,
  `loan_duration` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `loan_settings`
--

INSERT INTO `loan_settings` (`setting_id`, `loan_duration`, `created_at`, `updated_at`) VALUES
(1, 14, '2025-04-17 23:57:26', '2025-04-17 23:57:26'),
(2, 5, '2025-04-18 00:11:16', '2025-04-18 00:11:16'),
(3, 14, '2025-04-18 00:16:00', '2025-04-18 00:16:00'),
(4, 5, '2025-04-18 00:18:01', '2025-04-18 00:18:01'),
(5, 6, '2025-04-18 00:18:12', '2025-04-18 00:18:12'),
(6, 10, '2025-04-18 00:24:45', '2025-04-18 00:24:45'),
(7, 1, '2025-04-18 00:25:59', '2025-04-18 00:25:59'),
(8, 11, '2025-04-18 00:26:17', '2025-04-18 00:26:17'),
(9, 11, '2025-04-18 00:27:33', '2025-04-18 00:27:33'),
(10, 1, '2025-04-18 00:27:41', '2025-04-18 00:27:41'),
(11, 11, '2025-04-18 00:28:09', '2025-04-18 00:28:09'),
(12, 11, '2025-04-18 00:29:31', '2025-04-18 00:29:31'),
(13, 11, '2025-04-18 00:29:38', '2025-04-18 00:29:38'),
(14, 1, '2025-04-18 00:29:46', '2025-04-18 00:29:46'),
(15, 11, '2025-04-18 00:31:12', '2025-04-18 00:31:12'),
(16, 1, '2025-04-18 00:32:24', '2025-04-18 00:32:24'),
(17, 11, '2025-04-18 00:34:29', '2025-04-18 00:34:29'),
(18, 11, '2025-04-18 00:34:31', '2025-04-18 00:34:31'),
(19, 1, '2025-04-18 00:34:39', '2025-04-18 00:34:39'),
(20, 2, '2025-04-18 00:34:47', '2025-04-18 00:34:47'),
(21, 5, '2025-04-18 00:43:59', '2025-04-18 00:43:59'),
(22, 2, '2025-04-18 00:46:27', '2025-04-18 00:46:27');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `registration_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `student_number`, `first_name`, `last_name`, `email`, `date_of_birth`, `registration_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'S-000', 'Claude', 'Quartey C', 'claude.joshua@marigold.edu.gh', '2011-02-17', '2025-04-17', 1, '2025-04-17 21:29:38', '2025-04-18 16:17:01'),
(2, 'S-001', 'Eureka', 'Cooper ', 'eureka.aggrey@marigold.edu.gh', '2011-07-06', '2025-04-17', 1, '2025-04-17 21:33:27', '2025-04-18 10:26:22'),
(3, 'S-002', 'Francesca', 'Anyam', 'francesca.anyam@marigold.edu.gh', '2008-06-17', '2025-04-17', 1, '2025-04-17 21:46:31', '2025-04-17 21:46:31'),
(4, 'S-003', 'Edward', 'O Mensah', 'eddiemens0462@gmail.com', '2004-05-08', '2025-04-18', 1, '2025-04-18 00:20:46', '2025-04-18 01:50:38'),
(5, 'S-004', 'Jemima', 'Kukua', 'jemimah.arhin@marigold.edu.gh', '2006-06-18', '2025-04-18', 1, '2025-04-18 01:29:59', '2025-04-18 13:18:13'),
(6, 'S-005', 'Maurene', 'Kyere', 'maureen.kyere@marigold.edu.gh', '1994-07-18', '2025-04-18', 1, '2025-04-18 01:37:22', '2025-04-18 01:37:22'),
(7, 'S-006', 'Sean', 'Lamptey', 'paul.lamptey@marigold.edu.gh', '2005-06-14', '2025-04-18', 1, '2025-04-18 01:40:14', '2025-04-18 01:56:35'),
(8, 'S-007', 'Helena', 'Yaa', 'helena.yaa@marigold.edu.gh', '2007-06-18', '2025-04-18', 1, '2025-04-18 13:50:27', '2025-04-18 13:50:27'),
(10, 'S-008', 'Araba', 'Botchway', 'araba.botchway@marigold.edu.gh', '2003-12-29', '2025-04-18', 1, '2025-04-18 16:18:43', '2025-04-18 16:18:43');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `first_name`, `last_name`, `email`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'eddiekay', '$2y$10$yLwVLGVwvA8SET/fJ38./OrN7s6pcDmlmCDmTjMXX0YgypVVZpAyS', 'Edward', 'Mensah', 'edward.mensah@marigold.edu', 1, '2025-04-17 18:23:19', '2025-04-17 18:23:19'),
(2, 'toliver', '$2y$10$qsCh/q1WqY5H81dGEJ9pJemkLnMtSFENc3kjEDXugNMyYL2q66Jjy', 'Don', 'Toliver', 'don.toliver@marigold.edu.gh', 1, '2025-04-17 18:49:40', '2025-04-17 18:49:40'),
(3, 'pawuah', '$2y$10$wi8h97TSdJ7hgTvYEpwSrOM5R6uPURZslkemFXo/0Aov2aZKXriDq', 'Patrick', 'Awuah', 'patrick.awuah@marigold.edu.gh', 1, '2025-04-18 01:26:36', '2025-04-18 01:26:36'),
(4, 'lcarthy', '$2y$10$3A8A.cl1U.7UgQO/JuXnxuX4C5lDitnRrw/uwJz/sRUi924VD32p.', 'Lovette', 'Carthy', 'lovette.carthy@marigold.edu.gh', 1, '2025-04-18 10:27:50', '2025-04-18 10:27:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `active_loans`
--
ALTER TABLE `active_loans`
  ADD PRIMARY KEY (`loan_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `idx_active_loans_status` (`status`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_books_isbn` (`isbn`),
  ADD KEY `idx_books_title` (`title`),
  ADD KEY `idx_books_author` (`author`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `loan_history`
--
ALTER TABLE `loan_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `loan_id` (`loan_id`),
  ADD KEY `idx_loan_history_student` (`student_id`),
  ADD KEY `idx_loan_history_book` (`book_id`);

--
-- Indexes for table `loan_settings`
--
ALTER TABLE `loan_settings`
  ADD PRIMARY KEY (`setting_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `student_number` (`student_number`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_students_email` (`email`),
  ADD KEY `idx_students_student_number` (`student_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `active_loans`
--
ALTER TABLE `active_loans`
  MODIFY `loan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `loan_history`
--
ALTER TABLE `loan_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `loan_settings`
--
ALTER TABLE `loan_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `active_loans`
--
ALTER TABLE `active_loans`
  ADD CONSTRAINT `active_loans_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `active_loans_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`);

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `loan_history`
--
ALTER TABLE `loan_history`
  ADD CONSTRAINT `loan_history_ibfk_1` FOREIGN KEY (`loan_id`) REFERENCES `active_loans` (`loan_id`),
  ADD CONSTRAINT `loan_history_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `loan_history_ibfk_3` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
