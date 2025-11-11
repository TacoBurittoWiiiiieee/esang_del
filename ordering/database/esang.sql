-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 08, 2025 at 08:49 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `esang`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(1, 'Desserts'),
(2, 'Main Dish'),
(3, 'Appetizers');

-- --------------------------------------------------------

--
-- Table structure for table `customer_details`
--

CREATE TABLE `customer_details` (
  `customer_details_id` int(11) NOT NULL,
  `house_number` varchar(100) NOT NULL,
  `street_village_sitio` varchar(100) NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_details`
--

INSERT INTO `customer_details` (`customer_details_id`, `house_number`, `street_village_sitio`, `barangay`, `city`, `user_id`) VALUES
(1, 'L1 B15', 'Triumph street, Diamond Crest Village', 'San Manuel', 'San Jose del Monte', 11);

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_description` varchar(255) NOT NULL,
  `product_image` varchar(255) NOT NULL,
  `unit_price` double NOT NULL,
  `stock` int(11) NOT NULL,
  `category` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`product_id`, `product_name`, `product_description`, `product_image`, `unit_price`, `stock`, `category`) VALUES
(1, 'Leche Flan', 'Sweet', 'https://placehold.co/320x200/efefef/333?text=Product\'', 70, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `order_status` varchar(50) NOT NULL,
  `details_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `order_type` varchar(50) NOT NULL,
  `rider_id` int(11) NOT NULL,
  `order_date` varchar(60) NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `user_type` enum('CUSTOMER','ADMIN','CASHIER','ORDER_MANAGER','RIDER') DEFAULT 'CUSTOMER',
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `email_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `otp` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `phone_number`, `password_hash`, `user_type`, `status`, `email_verified`, `created_at`, `updated_at`, `last_login`, `profile_image`, `otp`) VALUES
(11, 'Alvin Jeffrey', 'Delos Reyes', 'capstoneesang@gmail.com', '9959255328', '202cb962ac59075b964b07152d234b70', 'CUSTOMER', 'active', 1, '2025-11-01 15:12:20', '2025-11-08 07:37:26', NULL, 'user_11_20251108_083726.png', ''),
(13, 'Test', 'Account2', 'capstoneesang2@gmail.com', '9959255329', '202cb962ac59075b964b07152d234b70', 'ADMIN', 'active', 1, '2025-11-01 15:12:20', '2025-11-02 15:04:52', NULL, 'user_11_20251102_154016.png', ''),
(16, 'Test', 'Account3', 'capstoneesang3@gmail.com', '9959255333', '202cb962ac59075b964b07152d234b70', 'CASHIER', 'active', 1, '2025-11-01 15:12:20', '2025-11-05 19:49:01', NULL, 'user_11_20251102_154016.png', ''),
(17, 'Test', 'Account4', 'capstoneesang4@gmail.com', '9959255399', '202cb962ac59075b964b07152d234b70', 'ORDER_MANAGER', 'active', 1, '2025-11-01 15:12:20', '2025-11-02 15:04:52', NULL, 'user_11_20251102_154016.png', ''),
(18, 'Test', 'Account4', 'capstoneesang5@gmail.com', '9959255311', '202cb962ac59075b964b07152d234b70', 'RIDER', 'active', 1, '2025-11-01 15:12:20', '2025-11-02 15:04:52', NULL, 'user_11_20251102_154016.png', ''),
(21, 'test', 'Account6', 'capstoneesang6@gmail.com', '12312312312', '202cb962ac59075b964b07152d234b70', 'CUSTOMER', 'active', 1, '2025-11-05 21:26:36', '2025-11-05 21:27:24', NULL, NULL, ''),
(22, 'Alvin Jefftey', 'Delos Reyes', 'jdoe03.ph@gmail.com', '9959255327', '202cb962ac59075b964b07152d234b70', 'CUSTOMER', 'active', 1, '2025-11-08 04:48:49', '2025-11-08 04:52:06', NULL, 'user_22_20251108_055206.png', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `customer_details`
--
ALTER TABLE `customer_details`
  ADD PRIMARY KEY (`customer_details_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone_number` (`phone_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `customer_details`
--
ALTER TABLE `customer_details`
  MODIFY `customer_details_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
