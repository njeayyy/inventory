-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2024 at 03:09 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventory_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category`) VALUES
(0, 'No Category'),
(14, 'Boston '),
(15, 'Chief'),
(16, 'Peripherals'),
(17, 'phone');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `in_stock` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `product_added` timestamp NOT NULL DEFAULT current_timestamp(),
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `in_stock`, `price`, `product_added`, `category_id`) VALUES
(20, 'Syringe', 100, 1500.00, '2024-11-21 15:06:38', 15),
(22, 'Router', 5, 500.00, '2024-11-22 16:43:08', NULL),
(23, 'piggy bank', 25, 50.00, '2024-11-22 16:55:32', NULL),
(27, 'sir chef', 15, 2.00, '2024-11-23 16:43:04', NULL),
(28, 'AHHHHH', 10, 50.00, '2024-11-23 17:11:06', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_activities`
--

CREATE TABLE `product_activities` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_sale`
--

CREATE TABLE `product_sale` (
  `id` int(11) NOT NULL,
  `product_sale` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `sale_price` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `sale_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `product_id`, `quantity`, `sale_price`, `total_amount`, `sale_date`) VALUES
(5, 20, 4, 500.00, 2000.00, '2024-11-22 17:54:13'),
(6, 23, 50, 50.00, 2500.00, '2024-11-22 18:23:22'),
(8, 23, 40, 1.00, 40.00, '2024-11-23 17:44:08'),
(9, 27, 8, 5.00, 40.00, '2024-11-23 18:03:34'),
(10, 23, 25, 20.00, 500.00, '2024-11-23 18:03:53'),
(11, 28, 80, 5.00, 400.00, '2024-11-23 18:11:23'),
(12, 28, 10, 50.00, 500.00, '2024-11-23 18:19:17'),
(13, 22, 10, 500.00, 5000.00, '2024-11-23 19:34:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','User') NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `username`, `password`, `role`, `status`, `last_login`) VALUES
(53, 'admin@gmail.com', 'admin', '$2y$10$xZFPekZA04uY6kRVUAqTYeSEnQmnt6ENZmMeHzk/bKUcpr8eQhPcm', 'Admin', 'Active', '2024-12-02 09:26:02'),
(56, 'meow@gmail.com', 'meowmeow', '$2y$10$XnjzcDdfkaCvaJEWbl2EVejT/pJFtsWsQJJhHBpNu15FTpABXHDsy', 'User', 'Active', '2024-12-01 10:28:41'),
(57, 'woopsie@gmail.com', 'whoopy', '$2y$10$kCKW1hebUFaolDffZiaecu.SeWGBuL2h.D1PpN21O/mI.dnguxqc6', 'Admin', 'Active', '2024-12-01 10:32:57'),
(58, 'nj@gmail.com', 'nj', '$2y$10$wPIIE2d7UIKj/TmM9Su28ek.qB7pag/14OC5q3NjLXr5pgxjsvD76', 'User', 'Active', '2024-12-02 08:56:15'),
(59, 'angelo@gmail.com', 'ange', '$2y$10$wjgXAsfml410Q.A/JkhUmONbxpGucZYNtqPRflhxH8hecTNF8i/.C', 'Admin', 'Active', '2024-12-01 16:57:50'),
(63, 'k@gmail.com', 'njeayyy', '$2y$10$iEDG0v5a9et88bWdQOlmdO/SSTl86QO9/QzKgeQEjb2mHVL/ym8KK', 'Admin', 'Active', '2024-12-01 17:02:21'),
(64, 'kenntamayo6@gmail.com', 'kenn', '$2y$10$jPVliT2clFXFSXaBDhewHOCjLawHOFqTW2FS1Q5ecI7U2fqmk9mAO', 'User', 'Active', '2024-12-02 05:08:20'),
(65, 'kurt@edu.com', 'kurt', '$2y$10$PjZrJXga9Llp4xRrS1SogOH3oWDOIvxR7cgGp5eNn8CZpPOfDTBPq', 'User', 'Active', '2024-12-02 09:23:49'),
(66, 'test@gmail.com', 'test1', '', 'User', 'Active', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_category` (`category_id`);

--
-- Indexes for table `product_activities`
--
ALTER TABLE `product_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_sale`
--
ALTER TABLE `product_sale`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `product_activities`
--
ALTER TABLE `product_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_sale`
--
ALTER TABLE `product_sale`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `product_activities`
--
ALTER TABLE `product_activities`
  ADD CONSTRAINT `product_activities_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
