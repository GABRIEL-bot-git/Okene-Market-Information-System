-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 29, 2026 at 11:39 AM
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
-- Database: `okene_cmpis`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`) VALUES
(5, 'Fruits'),
(1, 'Grains'),
(3, 'Legumes'),
(6, 'Livestock'),
(2, 'Tubers'),
(4, 'Vegetables');

-- --------------------------------------------------------

--
-- Table structure for table `commodities`
--

CREATE TABLE `commodities` (
  `id` int(11) NOT NULL,
  `commodity_name` varchar(100) NOT NULL,
  `category` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `commodities`
--

INSERT INTO `commodities` (`id`, `commodity_name`, `category`) VALUES
(1, 'White Yam', 'Tubers'),
(2, 'Local Rice', 'Grains'),
(3, 'Brown Beans', 'Legumes'),
(4, 'Cassava Tuber', 'Tubers'),
(5, 'Tomatoes', 'Vegetables');

-- --------------------------------------------------------

--
-- Table structure for table `markets`
--

CREATE TABLE `markets` (
  `id` int(11) NOT NULL,
  `market_name` varchar(100) NOT NULL,
  `location` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `markets`
--

INSERT INTO `markets` (`id`, `market_name`, `location`) VALUES
(1, 'Okene Main Market', 'Central Okene'),
(2, 'Inoziomi Market', 'Inoziomi Ward'),
(3, 'Iru Market', 'Iru Okene'),
(4, 'Ajaokuta Junction Market', 'Ajaokuta Road');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `sent_at`, `is_read`) VALUES
(1, 3, 1, 'Hi', '2026-07-28 15:04:18', 1),
(2, 1, 3, 'Hello, please how may I help you', '2026-07-28 16:07:33', 1);

-- --------------------------------------------------------

--
-- Table structure for table `prices`
--

CREATE TABLE `prices` (
  `id` int(11) NOT NULL,
  `commodity_id` int(11) DEFAULT NULL,
  `market_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `date_recorded` date NOT NULL,
  `recorded_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prices`
--

INSERT INTO `prices` (`id`, `commodity_id`, `market_id`, `price`, `unit`, `date_recorded`, `recorded_by`) VALUES
(1, 1, 1, 2100.00, '1 Tuber', '2026-01-15', 1),
(2, 1, 1, 2250.00, '1 Tuber', '2026-02-14', 1),
(3, 1, 1, 2400.00, '1 Tuber', '2026-03-10', 1),
(4, 1, 1, 2800.00, '1 Tuber', '2026-04-12', 1),
(5, 1, 1, 3100.00, '1 Tuber', '2026-05-18', 1),
(6, 1, 1, 3500.00, '1 Tuber', '2026-06-18', 1),
(7, 2, 2, 3500.00, '1 rubber', '2026-06-18', 2),
(8, 2, 2, 3500.00, '1 rubber', '2026-06-18', 2),
(9, 2, 2, 3450.00, '1 rubber', '2026-06-18', 2);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT 'default.png',
  `role` enum('Admin','Trader','Farmer') NOT NULL DEFAULT 'Farmer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `password`, `phone_number`, `profile_pic`, `role`, `created_at`) VALUES
(1, 'admin', 'admin', '$2y$10$2p80OHsaNUDzoVSBnHrr6OSxKVwkBoa81AwCgvU3bK4wMRxIQD3e2', NULL, 'default.png', 'Admin', '2026-06-18 12:44:16'),
(2, 'trader', 'trader', '$2y$10$1SpneTxedtAyEnYCFtTC6eir/XvzJZV4VZH5wTJ28S8c5zUk1YBxO', '08012345678', 'default.png', 'Trader', '2026-06-18 12:44:40'),
(3, 'farmer', 'farmer', '$2y$10$tL4UUOe6ixQmYqwGU9LWiuTeGcurXnRdGz.SCsc8CCUAYXBoBAR9G', '08012345678', 'default.png', 'Farmer', '2026-06-18 12:44:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `commodities`
--
ALTER TABLE `commodities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `markets`
--
ALTER TABLE `markets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `prices`
--
ALTER TABLE `prices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `commodity_id` (`commodity_id`),
  ADD KEY `market_id` (`market_id`),
  ADD KEY `recorded_by` (`recorded_by`);

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
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `commodities`
--
ALTER TABLE `commodities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `markets`
--
ALTER TABLE `markets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `prices`
--
ALTER TABLE `prices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prices`
--
ALTER TABLE `prices`
  ADD CONSTRAINT `prices_ibfk_1` FOREIGN KEY (`commodity_id`) REFERENCES `commodities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prices_ibfk_2` FOREIGN KEY (`market_id`) REFERENCES `markets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prices_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
