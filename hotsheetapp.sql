-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 26, 2026 at 11:07 PM
-- Server version: 10.6.24-MariaDB-cll-lve
-- PHP Version: 8.3.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hotsheetapp`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `created_at`, `last_login`) VALUES
(1, 'Janarthanan Kannan', 'jana@qpaymentz.com', '$2y$10$hXOxy2ejSX2PI30xzXqcHORgXQ8VrQ2xSsHHxe9S0tIlgQzpB6aj2', 'admin', '2025-09-16 11:48:49', '2026-01-23 05:14:33'),
(2, 'Vicky Sangar', 'vickysangar@newindiabazar.com', '$2y$10$Tfkus27gFmkUOhJIzOTGcuWc4gT8w3.MFNb0IJm.BM.mlbIWqSBoO', 'admin', '2025-09-18 15:37:08', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_activity`
--

CREATE TABLE `user_activity` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_activity`
--

INSERT INTO `user_activity` (`id`, `user_id`, `action`, `created_at`) VALUES
(1, 1, 'Logged in', '2025-09-19 06:22:54'),
(2, 1, 'Admin updated password for user 1', '2025-09-19 06:23:42'),
(3, 1, 'Janarthanan Kannan - Admin updated password for user 1', '2025-09-19 06:28:06'),
(4, 1, 'Uploaded image: 1758263818_9e6449610e.png', '2025-09-19 06:36:58'),
(5, 1, 'Deleted image: 1758263818_9e6449610e.png', '2025-09-19 06:37:51'),
(6, 1, 'Logged out', '2025-09-19 07:24:56'),
(7, 1, 'Logged in', '2026-01-23 02:18:53'),
(8, 1, 'Logged in', '2026-01-23 02:25:07'),
(9, 1, 'Logged out', '2026-01-23 02:25:37'),
(10, 1, 'Logged in', '2026-01-23 02:26:00'),
(11, 1, 'Janarthanan Kannan - Admin updated password for user 1', '2026-01-23 02:26:37'),
(12, 1, 'Logged out', '2026-01-23 02:26:42'),
(13, 1, 'Logged in', '2026-01-23 02:26:59'),
(14, 1, 'Uploaded image: 1769135267_6820879fe2.jpg', '2026-01-23 02:27:47'),
(15, 1, 'Deleted image: 1769135267_6820879fe2.jpg', '2026-01-23 02:28:14'),
(16, 1, 'Logged out', '2026-01-23 02:28:43'),
(17, 1, 'Logged in', '2026-01-23 02:37:01'),
(18, 1, 'Uploaded image: 1769136676_cb609ab637.png', '2026-01-23 02:51:16'),
(19, 1, 'Deleted image: 1769136676_cb609ab637.png', '2026-01-23 02:52:49'),
(20, 1, 'Logged in', '2026-01-23 03:22:53'),
(21, 1, 'Logged in', '2026-01-23 05:14:33'),
(22, 1, 'Logged out', '2026-01-23 05:24:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_activity`
--
ALTER TABLE `user_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_activity`
--
ALTER TABLE `user_activity`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_activity`
--
ALTER TABLE `user_activity`
  ADD CONSTRAINT `user_activity_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
