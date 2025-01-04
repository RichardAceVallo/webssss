-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 04, 2025 at 04:42 PM
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
-- Database: `comparts`
--

-- --------------------------------------------------------

--
-- Table structure for table `cpus`
--

CREATE TABLE `cpus` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` text NOT NULL,
  `socket_type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cpus`
--

INSERT INTO `cpus` (`id`, `name`, `price`, `image_url`, `socket_type`) VALUES
(1, 'Intel Core i5-12600K', 250.00, 'item/intel_i5_12th_gen.jpg', 'LGA'),
(2, 'Intel Core i7-12700K', 370.00, 'item/intel_i7_12th_gen.jpg', 'LGA'),
(3, 'AMD Ryzen 5 5600X', 200.00, 'item/AMD_Ryzen_5_5600X.jpg', 'AM'),
(4, 'AMD Ryzen 7 5800X', 340.00, 'item/AMD_Ryzen_7_5800X.jpg', 'AM');

-- --------------------------------------------------------

--
-- Table structure for table `motherboards`
--

CREATE TABLE `motherboards` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` text NOT NULL,
  `socket_type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `motherboards`
--

INSERT INTO `motherboards` (`id`, `name`, `price`, `image_url`, `socket_type`) VALUES
(1, 'ASUS ROG STRIX Z690-E (Intel)', 400.00, 'item/ASUS_ROG_STRIX_Z690-E_(Intel).png', 'LGA'),
(2, 'MSI MPG Z690 Carbon WiFi (Intel)', 370.00, 'item/MSI_MPG_Z690_Carbon_WiFi_(Intel).png', 'LGA'),
(3, 'ASUS ROG STRIX B550-F (AMD)', 200.00, 'item/ASUS_ROG_STRIX_B550-F_(AMD).png', 'AM'),
(4, 'MSI MAG B550 TOMAHAWK (AMD)', 180.00, 'item/MSI_MAG_B550_TOMAHAWK_(AMD).png', 'AM');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cpus`
--
ALTER TABLE `cpus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `motherboards`
--
ALTER TABLE `motherboards`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cpus`
--
ALTER TABLE `cpus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `motherboards`
--
ALTER TABLE `motherboards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
