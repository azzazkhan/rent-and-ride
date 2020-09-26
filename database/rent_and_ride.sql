-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 26, 2020 at 07:46 PM
-- Server version: 10.4.13-MariaDB
-- PHP Version: 7.4.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rent_and_ride`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `app_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact` char(11) NOT NULL,
  `nic_number` char(16) NOT NULL,
  `address` text NOT NULL,
  `car_id` int(11) UNSIGNED NOT NULL,
  `shop_id` int(11) UNSIGNED NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `car_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `specifications` varchar(255) DEFAULT NULL,
  `daily_price` int(6) DEFAULT NULL,
  `weekly_price` int(6) DEFAULT NULL,
  `slug` varchar(70) NOT NULL,
  `tags` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`car_id`, `name`, `specifications`, `daily_price`, `weekly_price`, `slug`, `tags`) VALUES
(1, 'Honda Civic', '2017 White Color', 2000, 5000, 'honda-civic-2017-white', '[\"Honda\", \"Civic\", \"Hybrid\", \"2017\"]'),
(2, 'Toyota Corolla', '2017 Silver Color', 2000, 5000, 'toyota-corolla-2017-sliver', '[\"Toyota\", \"Corolla\", \"2017\"]'),
(3, 'Suzuki Swift', '2017 White Color', 1500, 4000, 'suzuki-swift-2017-white', '[\"Suzuki\", \"Swift\", \"2017\"]'),
(4, 'Suzuki Cultus', '2012 Grey Color', 1300, 3000, 'suzuki-cultus-2012-grey', '[\"Suzuki\", \"Cultus\", \"2012\"]'),
(5, 'Suzuki Alto', '2019 Grey Color', 1000, 2500, 'suzuki-alto-2019-grey', '[\"Suzuki\", \"Alto\", \"2019\"]'),
(6, 'Suzuki Mehran', '2013 White Color', 500, 2000, 'suzuki-mehran-2013-white', '[\"Suzuki\", \"Mehran\", \"2013\"]');

-- --------------------------------------------------------

--
-- Table structure for table `car_shop`
--

CREATE TABLE `car_shop` (
  `relation_id` int(11) UNSIGNED NOT NULL,
  `shop_id` int(11) UNSIGNED NOT NULL,
  `car_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `car_shop`
--

INSERT INTO `car_shop` (`relation_id`, `shop_id`, `car_id`) VALUES
(1, 3, 1),
(8, 1, 2),
(2, 3, 2),
(7, 2, 3),
(3, 3, 3),
(5, 2, 4),
(4, 2, 5),
(6, 2, 6);

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `location_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`location_id`, `name`, `slug`) VALUES
(1, 'Saddar', 'saddar'),
(2, 'Hayatabad', 'hayatabad'),
(3, 'Warsak Road', 'warsak-road'),
(4, 'Ring Road', 'ring-road');

-- --------------------------------------------------------

--
-- Table structure for table `shops`
--

CREATE TABLE `shops` (
  `shop_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` char(14) DEFAULT '091-000-0000',
  `address` varchar(255) DEFAULT NULL,
  `slug` varchar(50) NOT NULL,
  `location_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `shops`
--

INSERT INTO `shops` (`shop_id`, `name`, `phone`, `address`, `slug`, `location_id`) VALUES
(1, 'Toyota Motors', '091-000-0000', NULL, 'toyota-motors', 4),
(2, 'Suzuki Motors', '091-000-0000', NULL, 'suzuki-motors', 4),
(3, 'Frontier Motors', '091-000-0000', NULL, 'frontier-motors', 1),
(4, 'Peshawar Motors', '091-000-0000', NULL, 'peshawar-motors', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`app_id`),
  ADD KEY `application_product` (`car_id`),
  ADD KEY `application_to` (`shop_id`);

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`car_id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `car_shop`
--
ALTER TABLE `car_shop`
  ADD PRIMARY KEY (`relation_id`),
  ADD UNIQUE KEY `no_car_duplication` (`car_id`,`shop_id`),
  ADD KEY `shop_reference` (`shop_id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`location_id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `shops`
--
ALTER TABLE `shops`
  ADD PRIMARY KEY (`shop_id`),
  ADD UNIQUE KEY `no_duplicate_shop_at_location` (`slug`,`location_id`),
  ADD KEY `shop_location` (`location_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `app_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `car_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `car_shop`
--
ALTER TABLE `car_shop`
  MODIFY `relation_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `location_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `shops`
--
ALTER TABLE `shops`
  MODIFY `shop_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `application_product` FOREIGN KEY (`car_id`) REFERENCES `cars` (`car_id`),
  ADD CONSTRAINT `application_to` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`shop_id`);

--
-- Constraints for table `car_shop`
--
ALTER TABLE `car_shop`
  ADD CONSTRAINT `car_reference` FOREIGN KEY (`car_id`) REFERENCES `cars` (`car_id`),
  ADD CONSTRAINT `shop_reference` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`shop_id`);

--
-- Constraints for table `shops`
--
ALTER TABLE `shops`
  ADD CONSTRAINT `shop_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
