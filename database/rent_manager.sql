-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 13, 2026 at 08:45 AM
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
-- Database: `rent_manager`
--

-- --------------------------------------------------------

--
-- Table structure for table `advance`
--

CREATE TABLE `advance` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `paid_amount` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `advance`
--

INSERT INTO `advance` (`id`, `tenant_id`, `unit_id`, `paid_amount`, `date`) VALUES
(2, 3, 28, 2000, '2026-03-17 04:40:01'),
(3, 11, 36, 31000, '2026-04-02 14:37:54'),
(4, 8, 33, 30000, '2026-04-02 14:46:35'),
(5, 7, 32, 31000, '2026-04-02 14:49:27'),
(6, 6, 31, 30000, '2026-04-02 14:51:22'),
(7, 5, 30, 35000, '2026-04-02 14:53:22'),
(8, 25, 61, 7500, '2026-04-05 09:06:04'),
(13, 24, 60, 9000, '2026-04-10 13:43:59'),
(14, 27, 63, 50000, '2026-04-10 13:46:57'),
(15, 18, 49, 6400, '2026-04-11 11:55:54'),
(16, 29, 64, 150000, '2026-04-13 04:11:39'),
(17, 28, 62, 150000, '2026-04-13 04:12:16'),
(18, 13, 40, 6400, '2026-04-15 17:01:30'),
(19, 23, 58, 6500, '2026-04-16 15:18:22'),
(20, 21, 53, 2400, '2026-04-16 15:29:20'),
(21, 26, 50, 2400, '2026-04-16 15:36:08'),
(22, 21, 53, 4000, '2026-04-16 15:44:02'),
(23, 33, 45, 5800, '2026-04-22 06:27:37'),
(24, 31, 57, 6700, '2026-04-24 11:53:27'),
(26, 35, 37, 100000, '2026-04-29 12:00:41'),
(27, 36, 38, 30000, '2026-04-29 12:03:52'),
(28, 37, 41, 1000, '2026-05-11 16:53:58'),
(29, 37, 41, 4900, '2026-05-11 16:54:19'),
(30, 38, 43, 4000, '2026-05-20 06:55:47'),
(31, 38, 43, 2400, '2026-05-20 06:55:56'),
(32, 19, 51, 4000, '2026-05-20 07:09:57'),
(44, 12, 39, 6400, '2026-06-11 06:35:00'),
(45, 4, 29, 5000, '2026-07-05 06:39:00');

-- --------------------------------------------------------

--
-- Table structure for table `building`
--

CREATE TABLE `building` (
  `id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `building_type` int(11) NOT NULL,
  `address` text NOT NULL,
  `description` text NOT NULL,
  `image` varchar(256) NOT NULL,
  `location` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `building`
--

INSERT INTO `building` (`id`, `name`, `building_type`, `address`, `description`, `image`, `location`) VALUES
(17, 'BAGH E ABDULLAH', 5, '1/G, 5/1 Modhubag Road, Dhaka', 'BAGH E ABDULLAH', '1779522699_6a115c8b6e848.jpg', 'https://maps.app.goo.gl/KM8hqQGTacAaqznDA'),
(18, 'ABDULLAH VILLA', 5, 'Block-A,Said Nagar,Madani Ave.,Gulshan,Dhaka.', '', '1779522685_6a115c7d72d8d.jpg', '');

-- --------------------------------------------------------

--
-- Table structure for table `expense`
--

CREATE TABLE `expense` (
  `id` int(11) NOT NULL,
  `date` varchar(100) NOT NULL,
  `expense_month` varchar(100) NOT NULL,
  `building_id` varchar(100) NOT NULL,
  `unit_id` varchar(100) DEFAULT NULL,
  `expense_for` varchar(100) NOT NULL,
  `amount` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `expense_method` varchar(100) NOT NULL,
  `expense_by` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expense`
--

INSERT INTO `expense` (`id`, `date`, `expense_month`, `building_id`, `unit_id`, `expense_for`, `amount`, `description`, `expense_method`, `expense_by`) VALUES
(4, '2026-04-28', '2026-04', '17', '', 'Electricity', '18096', '', 'Bank', 'Admin'),
(5, '2026-04-28', '2026-04', '17', '', 'Water bill', '9541', '', 'Bkash', 'Admin'),
(6, '2026-06-13', '2017-03', '17', '', 'Sed sed magni in iru', '3000', 'Reprehenderit incidu', 'Bkash', 'Admin'),
(7, '2026-06-13', '2026-06', '17', '36', 'test', '3000', 'test', 'Cash', 'Admin'),
(8, '2026-06-13', '2026-06', '18', '50', 'test', '5000', 'test', 'Cash', 'Manager'),
(9, '2026-06-13', '2026-06', '17', '35', 'test', '1000', '', 'Cash', 'Manager');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `billing_month` varchar(100) DEFAULT NULL,
  `total_amount` int(11) DEFAULT NULL,
  `paid_amount` int(11) DEFAULT NULL,
  `status` enum('Paid','Unpaid','Partial') NOT NULL,
  `note` text DEFAULT NULL,
  `Rent` varchar(100) DEFAULT NULL,
  `Gas` int(11) DEFAULT NULL,
  `Water` int(11) DEFAULT NULL,
  `Electricity` int(11) DEFAULT NULL,
  `Others` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `Gas_month` varchar(100) DEFAULT NULL,
  `Water_month` varchar(100) DEFAULT NULL,
  `Electricity_month` varchar(100) DEFAULT NULL,
  `Others_month` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `tenant_id`, `unit_id`, `billing_month`, `total_amount`, `paid_amount`, `status`, `note`, `Rent`, `Gas`, `Water`, `Electricity`, `Others`, `created_at`, `Gas_month`, `Water_month`, `Electricity_month`, `Others_month`) VALUES
(1, 21, 53, '2026-05', 6400, NULL, 'Unpaid', NULL, '6200', 0, 200, 0, 0, '2026-05-07 12:17:10', '2026-04', 'April 2026', 'Mar 2026', ''),
(2, 21, 53, '2026-04', 6400, 6400, 'Paid', NULL, '6200', 0, 200, 0, 0, '2026-05-07 12:19:35', '2026-04', 'March 2026', 'Mar 2026', ''),
(3, 25, 61, '2026-05', 7500, 7500, 'Paid', NULL, '7300', 0, 200, 0, 0, '2026-05-07 12:22:58', '2026-04', 'April 2026', 'Mar 2026', ''),
(4, 31, 57, '2026-05', 6700, 6600, 'Partial', NULL, '6500', 0, 200, 0, 0, '2026-05-10 15:50:21', 'May 2026', 'April 2026', 'May 2026', ''),
(5, 24, 60, '2026-05', 9000, 9000, 'Paid', NULL, '8800', 0, 200, 0, 0, '2026-05-11 16:07:33', '2026-04', 'April 2026', 'Mar 2026', ''),
(6, 30, 42, '2026-05', 5800, 5800, 'Paid', NULL, '5600', 0, 200, 0, 0, '2026-05-11 16:13:42', '2026-04', 'Apr 2026', 'Mar 2026', ''),
(8, 37, 41, '2026-05', 5900, 5900, 'Paid', NULL, '5700', 0, 200, 0, 0, '2026-05-11 16:54:52', '2026-04', 'April 2026', 'Mar 2026', ''),
(9, 27, 63, '2026-05', 6500, 6500, 'Paid', NULL, '6500', 0, 0, 0, 0, '2026-05-11 17:02:31', '2026-04', 'Jan 2026', 'Mar 2026', ''),
(10, 4, 29, '2026-05', 14529, 14529, 'Paid', NULL, '10500', 1080, 1063, 1886, 0, '2026-05-11 17:12:05', '2026-04', 'Jan 2026', 'Mar 2026', ''),
(13, 9, 34, '2026-05', 73266, NULL, 'Unpaid', NULL, '0', 0, 0, 0, 73266, '2026-05-11 17:24:17', '2026-04', 'Jan 2026', 'Mar 2026', 'Previous Due Moru- 37860/= & Asha 35406/='),
(14, 9, 34, '2026-05', 19666, NULL, 'Unpaid', NULL, '16500', 1080, 1063, 1023, 0, '2026-05-11 17:25:31', '2026-04', 'Jan 2026', 'Mar 2026', ''),
(17, 26, 50, '2026-05', 6600, 6600, 'Paid', NULL, '6400', 0, 200, 0, 0, '2026-05-13 05:07:18', '2026-04', 'April 2026', 'Mar 2026', ''),
(18, 3, 28, '2026-05', 7000, 7000, 'Paid', NULL, '5000', 1080, 0, 920, 0, '2026-05-13 05:12:22', '2026-04', 'Jan 2026', 'Mar 2026', ''),
(19, 5, 30, '2026-05', 21752, 21752, 'Paid', NULL, '17500', 1080, 1063, 2109, 0, '2026-05-13 05:21:16', '2026-04', 'Jan 2026', 'Mar 2026', ''),
(20, 6, 31, '2026-05', 21424, 21424, 'Paid', NULL, '18000', 1080, 1063, 1281, 0, '2026-05-13 05:25:40', '2026-04', 'Jan 2026', 'Mar 2026', ''),
(21, 10, 35, '2026-05', 20827, 20827, 'Paid', NULL, '18000', 1080, 1063, 684, 0, '2026-05-13 05:31:11', '2026-04', 'Jan 2026', 'Mar 2026', ''),
(22, 8, 33, '2026-05', 22121, 22121, 'Paid', NULL, '18500', 1080, 1063, 1478, 0, '2026-05-13 05:35:28', '2026-04', 'Jan 2026', 'Mar 2026', ''),
(23, 11, 36, '2026-05', 19743, 19743, 'Paid', NULL, '16500', 1080, 1063, 1100, 0, '2026-05-13 05:37:52', '2026-04', 'Jan 2026', 'Mar 2026', ''),
(24, 18, 49, '2026-05', 6600, 6600, 'Paid', NULL, '6400', 0, 200, 0, 0, '2026-05-15 06:33:22', '2026-04', 'Apr 2026', 'Mar 2026', ''),
(25, 35, 37, '2026-05', 9942, 9942, 'Paid', NULL, '8000', 0, 500, 1442, 0, '2026-05-15 07:01:34', 'May 2026', 'May 2026', 'May 2026', '659-556=103 Units '),
(28, 13, 40, '2026-05', 6400, 6400, 'Paid', NULL, '6200', 0, 200, 0, 0, '2026-05-17 03:02:12', '2026-04', ' April 2026', 'Mar 2026', ''),
(29, 17, 47, '2026-05', 7500, 7500, 'Paid', NULL, '7300', 0, 200, 0, 0, '2026-05-17 03:06:35', '2026-04', 'April 2026', 'Mar 2026', ''),
(30, 22, 56, '2026-04', 5200, 5200, 'Paid', NULL, '5000', 0, 200, 0, 0, '2026-05-17 03:08:59', '2026-04', 'Mar 2026', 'Mar 2026', ''),
(31, 22, 56, '2026-05', 5200, NULL, 'Unpaid', NULL, '5000', 0, 200, 0, 0, '2026-05-17 03:10:00', '2026-04', 'April 2026', 'Mar 2026', ''),
(32, 23, 58, '2026-05', 6500, 6500, 'Paid', NULL, '6300', 0, 200, 0, 0, '2026-05-17 03:12:04', '2026-04', 'April 2026', 'Mar 2026', ''),
(33, 38, 43, '2026-05', 6400, 6400, 'Paid', NULL, '6200', 0, 200, 0, 0, '2026-05-20 06:56:43', '2026-04', 'April 2026', 'Mar 2026', ''),
(34, 29, 64, '2026-05', 12500, 12500, 'Paid', NULL, '12000', 0, 500, 0, 0, '2026-05-20 07:01:22', '2026-04', 'April 2026', 'Mar 2026', ''),
(35, 19, 51, '2026-05', 5800, 5800, 'Paid', NULL, '5600', 0, 200, 0, 0, '2026-05-20 07:06:30', '2026-04', 'April 2026', 'Mar 2026', ''),
(37, 28, 62, '2026-05', 12500, 12500, 'Paid', NULL, '12000', 0, 500, 0, 0, '2026-05-20 07:13:45', '2026-04', 'April 2026', 'Mar 2026', ''),
(38, 39, 48, '2026-05', 6600, 6600, 'Paid', NULL, '6400', 0, 200, 0, 0, '2026-05-20 07:19:25', '2026-04', 'April 2026', 'Mar 2026', ''),
(39, 40, 29, '2026-05', 11580, NULL, 'Unpaid', NULL, '10500', 1080, 0, 0, 0, '2026-05-21 10:09:40', '2026-04', 'Jan 2026', 'Mar 2026', ''),
(42, 36, 38, '2026-05', 4000, NULL, 'Unpaid', NULL, '4000', 0, 0, 0, 0, '2026-05-24 09:34:49', '2026-04', 'Jan 2026', 'Mar 2026', ''),
(44, 12, 39, '2026-06', 6200, 6200, 'Paid', NULL, '6200', 0, 0, 0, 0, '2026-06-10 09:10:31', '2026-05', 'Feb 2026', 'Apr 2026', ''),
(45, 13, 40, '2026-06', 6400, NULL, 'Unpaid', NULL, '6200', 0, 200, 0, 0, '2026-06-10 09:10:43', '2026-05', 'Feb 2026', 'Apr 2026', ''),
(46, 37, 41, '2026-06', 5900, NULL, 'Unpaid', NULL, '5700', 0, 200, 0, 0, '2026-06-10 09:10:55', '2026-05', 'Feb 2026', 'Apr 2026', ''),
(47, 3, 28, '2026-06', 6080, 6080, 'Paid', NULL, '5000', 1080, 0, 0, 0, '2026-06-13 09:42:32', '2026-05', 'Feb 2026', 'Apr 2026', ''),
(48, 4, 29, '2026-06', 13143, 13143, 'Paid', NULL, '10500', 1080, 1063, 500, 0, '2026-06-13 09:46:09', '2026-05', 'Feb 2026', 'Apr 2026', ''),
(49, 5, 30, '2026-06', 19643, 10643, 'Partial', NULL, '17500', 1080, 1063, 0, 0, '2026-06-13 09:47:08', '2026-05', 'Feb 2026', 'Apr 2026', ''),
(51, 59, 40, '2026-07', 6200, NULL, 'Unpaid', NULL, '6200', 0, 0, 0, 0, '2026-07-06 11:55:35', '2026-06', 'Mar 2026', 'May 2026', ''),
(53, 4, 29, '2026-07', 12643, 2643, 'Partial', NULL, '10500', 1080, 1063, 0, 0, '2026-07-12 08:13:33', '2026-06', 'Mar 2026', 'May 2026', ''),
(55, 12, 39, '2026-08', 6200, 1200, 'Partial', NULL, '6200', 0, 0, 0, 0, '2026-08-11 06:41:05', '2026-07', 'Apr 2026', 'Jun 2026', ''),
(56, 4, 29, '2026-08', 12643, NULL, 'Unpaid', NULL, '10500', 1080, 1063, 0, 0, '2026-08-11 07:40:59', '2026-07', 'Apr 2026', 'Jun 2026', ''),
(57, 61, 28, '2026-09', 6080, 80, 'Partial', 'this is', '5000', 1080, 0, 0, 0, '2026-09-13 05:11:28', '2026-08', 'May 2026', 'Jul 2026', 'this is invoce test not'),
(58, 61, 28, '2026-09', 6080, NULL, 'Unpaid', 'this is test note for invoice', '5000', 1080, 0, 0, 0, '2026-09-13 05:28:40', '2026-08', 'May 2026', 'Jul 2026', '');

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` varchar(150) DEFAULT NULL,
  `status` enum('Approved','Pending') DEFAULT NULL,
  `reed` enum('Yes','No') DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`id`, `tenant_id`, `title`, `description`, `status`, `reed`, `date`) VALUES
(8, 4, 'Payment Pending', 'Payment of 200 ৳ Received for Invoice #INV-53 for the month of Jul 2026.', 'Pending', 'No', '2026-07-12 15:40:31'),
(9, 4, 'Payment Pending', 'Payment of 800 ৳ Received for Invoice #INV-53 for the month of Jul 2026.', 'Pending', 'No', '2026-07-12 18:11:56'),
(10, 4, 'Payment Successful', 'Payment of 800 ৳ Received for Invoice #INV-53 for the month of Jul 2026.', 'Approved', 'Yes', '2026-07-12 18:15:27'),
(11, 12, 'Payment Successful', 'Payment of 620 ৳ Received for Invoice #INV-54 for the month of Aug 2026.', 'Approved', 'Yes', '2026-08-11 06:35:22'),
(12, 12, 'Payment Successful', 'Payment of 200 ৳ Received for Invoice #INV-55 for the month of Aug 2026.', 'Approved', 'Yes', '2026-08-11 06:41:15'),
(13, 12, 'Payment Successful', 'Payment of 1000 ৳ Received for Invoice #INV-55 for the month of Aug 2026.', 'Approved', 'Yes', '2026-08-11 06:42:11'),
(14, 4, 'Payment Pending', 'Payment of 1000 ৳ Received for Invoice #INV-53 for the month of Jul 2026.', 'Pending', 'No', '2026-08-11 07:41:36'),
(15, 61, 'Payment Successful', 'Payment of 80 ৳ Received for Invoice #INV-57 for the month of Sep 2026.', 'Approved', 'Yes', '2026-09-13 05:11:53'),
(16, 35, 'Payment Successful', 'Payment of 9942 ৳ Received for Invoice #INV-25 for the month of May 2026.', 'Approved', 'Yes', '2026-09-13 05:59:49');

-- --------------------------------------------------------

--
-- Table structure for table `payment_history`
--

CREATE TABLE `payment_history` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `bill_month` varchar(100) DEFAULT NULL,
  `payment_method` varchar(100) NOT NULL,
  `paid_amount` int(11) NOT NULL,
  `note` text DEFAULT NULL,
  `manager_paid` varchar(100) DEFAULT NULL,
  `payment_date` varchar(256) NOT NULL,
  `transaction_id` varchar(200) DEFAULT NULL,
  `manager_payment_method` varchar(100) DEFAULT NULL,
  `transaction_number` varchar(100) DEFAULT NULL,
  `transaction_slip` varchar(100) DEFAULT NULL,
  `status` enum('Approved','Pending') NOT NULL DEFAULT 'Approved'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_history`
--

INSERT INTO `payment_history` (`id`, `tenant_id`, `invoice_id`, `bill_month`, `payment_method`, `paid_amount`, `note`, `manager_paid`, `payment_date`, `transaction_id`, `manager_payment_method`, `transaction_number`, `transaction_slip`, `status`) VALUES
(1, 21, 2, '2026-04', 'Manager', 6400, '', '6400', '2026-05-06 18:20:00', '', 'Cash', '', '', 'Approved'),
(2, 25, 3, '2026-05', 'Bkash', 7500, '', '0', '2026-05-05 14:49:00', 'DE52TO7LQ0', '', '01805124660', '', 'Approved'),
(3, 31, 4, '2026-05', 'Bkash', 6600, '', '0', '2026-05-10 21:13:00', 'DEA72Z4YRZ', '', '01843261468', '', 'Approved'),
(4, 24, 5, '2026-05', 'Bkash', 9000, '', '0', '2026-05-09 22:04:00', 'DE991WCBOT', '', '01612450304', '', 'Approved'),
(5, 30, 6, '2026-05', 'Bkash', 5800, '', '0', '2026-05-09 22:04:00', 'DE991WCBOT', '', '01612450304', '', 'Approved'),
(7, 37, 8, '2026-05', 'Manager', 5900, '', '5900', '2026-05-11 22:54:00', 'DE76WJP3UW', 'Bkash', '01914375298', '', 'Approved'),
(8, 27, 9, '2026-05', 'Bkash', 6500, '', '0', '2026-05-11 22:03:00', 'DEB14EHOVL', '', '01610103367', '', 'Approved'),
(10, 26, 17, '2026-05', 'Manager', 6600, '', '6600', '2026-05-12 20:18:00', 'DEC75HMXIR', 'Bkash', '01719257062', '', 'Approved'),
(11, 3, 18, '2026-05', 'Cash', 7000, 'Received by Tuli', '0', '2026-05-12 11:11:00', '', '', '', '', 'Approved'),
(12, 4, 10, '2026-05', 'Bank Transfer', 14529, 'City Bank', '0', '2026-05-13 10:19:00', '261333778181', '', '2103772649001', '', 'Approved'),
(13, 5, 19, '2026-05', 'Bkash', 21752, '', '0', '2026-05-12 19:05:00', 'DEC95DAT2Z', '', '01736370806', '', 'Approved'),
(14, 6, 20, '2026-05', 'Bank Transfer', 21424, 'eft', '0', '2026-05-12 10:54:00', '12052026105550255:224150', '', '1101005102731', '', 'Approved'),
(15, 10, 21, '2026-05', 'Cash', 20827, 'Pay to Younus Mia', '0', '2026-05-12 23:30:00', '', '', '', '', 'Approved'),
(16, 8, 22, '2026-05', 'Cash', 22121, '', '0', '2026-05-06 11:33:00', '', '', '', '', 'Approved'),
(17, 11, 23, '2026-05', 'Cash', 19743, '', '0', '2026-05-08 11:37:00', '', '', '', '', 'Approved'),
(18, 18, 24, '2026-05', 'Bank Transfer', 6600, 'Brac Bank', '0', '2026-05-14 16:21:00', 'S92397627', '', '1075969990001', '', 'Approved'),
(19, 13, 28, '2026-05', 'Manager', 6400, '', '6400', '2026-05-16 18:38:00', 'DEG7A38V5Z', 'Bkash', '01843261468', '', 'Approved'),
(20, 17, 29, '2026-05', 'Manager', 7500, '', '7500', '2026-05-16 18:38:00', 'DEG7A38V5Z', 'Bkash', '01843261468', '', 'Approved'),
(21, 22, 30, '2026-04', 'Manager', 5200, '', '5200', '2026-05-17 09:09:00', 'DEG7A38V5Z', 'Bkash', '01843261468', '', 'Approved'),
(22, 23, 32, '2026-05', 'Manager', 6500, '', '6500', '2026-05-16 18:38:00', 'DEG7A38V5Z', 'Bkash', '01843261468', '', 'Approved'),
(23, 38, 33, '2026-05', 'Bkash', 6400, '', '0', '2026-05-08 18:55:00', 'DE8200UPC0', '', '01825455933', '', 'Approved'),
(24, 29, 34, '2026-05', 'Bkash', 12500, '', '0', '2026-05-18 11:54:00', 'DEI4C0TPOE', '', '01311468431', '', 'Approved'),
(25, 19, 35, '2026-05', 'Manager', 5800, '', '5800', '2026-05-17 13:06:00', '', 'Bkash', '', '', 'Approved'),
(26, 28, 37, '2026-05', 'Manager', 12500, '', '12500', '2026-05-17 16:13:00', '', 'Bkash', '', '', 'Approved'),
(27, 39, 38, '2026-05', 'Bank Transfer', 6600, '', '0', '2026-05-17 17:19:00', '', '', '2302675841001', '', 'Approved'),
(34, 4, 48, '2026-06', 'Cash', 12643, '', '0', '2026-06-13 15:46:00', '', '', '', '', 'Approved'),
(35, 5, 49, '2026-06', 'Manager', 10643, '', '643', '2026-06-13 15:47:00', '', 'Cash', '', '', 'Approved'),
(36, 3, 47, '2026-06', 'Manager', 6080, '', '80', '2026-06-14 18:28:00', 'sdfsdfsdf', 'Bkash', '3535345345', '', 'Approved'),
(38, 12, 44, '2026-06', 'Cash', 6200, '', '0', '2026-06-23 17:52:00', '', '', '', '', 'Approved'),
(39, 4, 48, '2026-06', 'Bank Transfer', 500, '', '0', '2026-07-11 12:18:00', '', '', '', '', 'Approved'),
(49, 4, 53, '2026-07', 'Nagad', 643, '', '0', '2026-07-12 14:14:00', '', '', '', '1783844063_9046.png', 'Approved'),
(55, 4, 53, '2026-07', 'Bank Transfer', 200, '', '0', '2026-07-12 21:37:25', '4546456456', '', '53345345345', 'Capture.png_1783870645_9511.png', 'Pending'),
(56, 4, 53, '2026-07', 'Card', 800, '', '0', '2026-07-13 00:11:56', '5343453453', '', 'werwer', 'Capture.png_1783879916_6254.png', 'Approved'),
(58, 12, 55, '2026-08', 'Bkash', 200, '', '0', '2026-08-11 12:36:00', '', '', '', NULL, 'Approved'),
(59, 12, 55, '2026-08', 'Nagad', 1000, 'fsfsf', '0', '2026-08-11 12:41:00', 'rwrwr', '', 'wrewerr', NULL, 'Approved'),
(60, 4, 53, '2026-07', 'Bank Transfer', 1000, 'sdfsfs', '0', '2026-08-11 13:41:36', 'fsdfsdf', '', 'sfsdfsf', 'Capture.png_1786434096_8513.png', 'Pending'),
(61, 61, 57, '2026-09', 'Cash', 80, 'this is test not', '0', '2026-09-13 11:11:00', '', '', '', NULL, 'Approved'),
(62, 35, 25, '2026-05', 'Rocket', 9942, '', '0', '2026-09-13 11:59:00', '', '', '', NULL, 'Approved');

-- --------------------------------------------------------

--
-- Table structure for table `tenants`
--

CREATE TABLE `tenants` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password` varchar(256) NOT NULL DEFAULT '827ccb0eea8a706c4c34a16891f84e7b',
  `status` enum('Active','Inactive','Booked') NOT NULL DEFAULT 'Active',
  `role` enum('Admin','Tenant','Manager') NOT NULL,
  `booking_month` varchar(100) DEFAULT NULL,
  `start_tanent` date DEFAULT NULL,
  `nid_no` varchar(200) DEFAULT NULL,
  `permanent_address` text DEFAULT NULL,
  `family_member` int(11) DEFAULT NULL,
  `tenant_image` varchar(255) DEFAULT NULL,
  `nid_image` varchar(255) DEFAULT NULL,
  `building_id` int(11) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenants`
--

INSERT INTO `tenants` (`id`, `name`, `phone`, `email`, `password`, `status`, `role`, `booking_month`, `start_tanent`, `nid_no`, `permanent_address`, `family_member`, `tenant_image`, `nid_image`, `building_id`, `unit_id`, `created_at`) VALUES
(3, 'Admin', '+8801700000000', 'admin@gmail.com', '827ccb0eea8a706c4c34a16891f84e7b', 'Inactive', 'Admin', '2026-07', '0000-00-00', '', 'Dhaka', 4, 'tenant_1782193998.jpg', '', 17, 28, '2026-03-17 04:39:10'),
(4, 'Saleh Ahmed (Topu)', '+8801405595384', 'saleh@gmail.com', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '2026-08', '2026-07-24', '23456', 'Dhaka', 3, '1783426846_Capture.png', '', 17, 29, '2026-03-17 05:37:12'),
(5, 'Biplob Kumar', '+8801736370806', 'biplob@gmail.com', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '0000-00-00', '', 'Dhaka', 4, '', '', 17, 30, '2026-03-17 05:38:16'),
(6, 'Mr Foisal Ahmed', '+8801923125105', 'foisal@gamil.com', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '0000-00-00', '', 'Dhaka', 5, '', '', 17, 31, '2026-03-17 05:40:34'),
(7, 'Miss Marjana Akter', '+8801731579340', 'marjana@gmail.com', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '0000-00-00', '', 'Dhaka', 5, '', '', 17, 32, '2026-03-17 05:41:32'),
(8, 'Mr Kashem', '+8801644364274', 'kashem@gamil.com', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '0000-00-00', '', 'Dhaka', 4, '', '', 17, 33, '2026-03-17 05:42:35'),
(9, 'Moriom', '+8801409400233', 'Moriom@gmail.com', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '0000-00-00', '', 'Dhaka', 5, '', '', 17, 34, '2026-03-17 05:43:15'),
(10, 'Mr Md Zia Uddin', '+8801714556674', 'ziauddin@gamil.com', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '0000-00-00', '', 'Dhaka', 4, '', '', 17, 35, '2026-03-17 05:45:32'),
(11, 'Mr. Md Reasat Akter (Arin)', '+8801635168023', 'reasat@gamil.com', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '0000-00-00', '', 'Dhaka', 3, '', '', 17, 36, '2026-03-17 05:46:28'),
(12, 'Mr Jakir', '+8801643238250', '', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '2026-08-21', '', '', 3, '1786353770_Capture.png', '', 18, 39, '2026-03-31 12:17:28'),
(13, 'Mr Helal', '+8801916601140', '', '827ccb0eea8a706c4c34a16891f84e7b', 'Inactive', 'Tenant', '', '0000-00-00', '', '', 3, '', '', 18, 40, '2026-03-31 12:19:30'),
(17, 'Mr Imran', '+9901999935927', '', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '0000-00-00', '', '', 2, '', '', 18, 47, '2026-03-31 12:29:37'),
(19, 'Mr Rakib Mia', '+8801844630897', '', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '0000-00-00', '', '', 3, '', '', 18, 51, '2026-03-31 12:51:14'),
(20, 'Mr Abul Khair', '+8801820064565', '', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '0000-00-00', '', '', 2, '', '', 18, 52, '2026-03-31 12:52:29'),
(21, 'Mr FazLey Rabby', '+8801703861440', '', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '0000-00-00', '', '', 3, '', '', 18, 53, '2026-03-31 12:53:57'),
(22, 'Mr Morsher Alom Foysal', '+8801887452309', '', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '0000-00-00', '', '', 0, '', '', 18, 56, '2026-03-31 12:55:24'),
(23, 'Mrs Sharmin', '+8801627879011', '', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '0000-00-00', '', '', 3, '', '', 18, 58, '2026-03-31 12:57:01'),
(24, 'Mr.Habil Mia', '+8801945672347', '', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '0000-00-00', '', '', 5, '', '', 18, 60, '2026-03-31 12:59:55'),
(25, 'Mr. Md Rahmat Ali', '+8801910758602', '', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '0000-00-00', '', '', 4, '', '', 18, 61, '2026-03-31 13:01:54'),
(26, 'Mr Masud', '+8801335123984', '', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '0000-00-00', '', '', 3, '', '', 18, 50, '2026-03-31 13:04:12'),
(27, 'Mr Rubel Hossain', '+8801610103367', '', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '0000-00-00', '', '', 0, '', '', 18, 63, '2026-03-31 13:05:45'),
(28, 'Mr Robin Poramanik', '+8801795356499', '', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '0000-00-00', '', '', 1, '', '', 18, 62, '2026-03-31 13:10:01'),
(29, 'Mr Razu', '+8801618466537', '', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '2026-08-13', '', '', 1, '1786353744_Capture.png', '', 18, 64, '2026-03-31 13:14:22'),
(30, 'Mr Mazharul Islam Linkon', '+8801739351913', '', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '0000-00-00', '', '', 3, '', '', 18, 42, '2026-04-05 10:06:35'),
(31, 'Mr Montu Mondol', '+8801303953458', '', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '0000-00-00', '', '', 3, '', '', 18, 57, '2026-04-05 10:38:22'),
(33, 'Riad Hossain', '+8801313197825', '', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '2026-02-01', '3711807031', 'Chor Shivi,Dawlot Khan,Vhola', 3, '', '', 18, 45, '2026-04-16 14:46:31'),
(35, 'Mazu', '+8801978997218', '', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '2026-04-01', '000', 'Dhaka', 3, '', '', 17, 37, '2026-04-29 11:58:16'),
(36, 'Mazu', '+8801978997218', '', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '2026-04-01', '000', 'Dhaka ', 3, '', '', 17, 38, '2026-04-29 11:59:14'),
(37, 'Md Ramjan Ali Raju', '01302609730', '1razukhan7@gmai.com', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '1996-12-21', '1466153481', 'parbotipur', 0, '', '', 18, 41, '2026-05-11 16:53:03'),
(38, 'Md Razu Mia', '+8801825455933', '', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '2003-12-12', '1042443000', 'Bakul Tola,Betagi,Borguna, Work- GSO Nagad,K 227,Dakkahin Kuril, 4th Fl,', 2, '', '', 18, 43, '2026-05-20 06:54:29'),
(39, 'Md Ridoy Ahsan', '+8801674489794', '', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '', '2005-09-08', '00', '', 3, '', '', 18, 48, '2026-05-20 07:18:45'),
(57, 'August Hayden', '+1 (947) 775-8863', 'pugodefol@mailinator.com', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '2006-04', '2016-05-26', 'Ut adipisci quas et ', 'Veniam deserunt vel', 65, '', '', 18, 65, '2026-05-23 06:17:18'),
(59, 'Modon', '+1 (335) 122-6013', 'xenavapij@mailinator.com', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '1990-04', '2018-06-01', 'Qui amet in aliquip', 'Quis tempore eum om', 89, '', '', 18, 40, '2026-06-10 12:22:32'),
(61, 'Sloane Wyatt', '+1 (774) 837-3496', 'lofubidy@mailinator.com', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '1999-01', '2005-01-14', 'Elit mollit occaeca', 'Iure blanditiis ex e', 46, '', '', 17, 28, '2026-06-23 11:16:17'),
(65, 'gggggggggggggggggg', '+880175555555', 'vimi@mailinator.com', '827ccb0eea8a706c4c34a16891f84e7b', 'Active', 'Tenant', '2013-01', '1986-09-27', 'Asperiores quasi pro', 'Nulla dolorem incidi', 22, '', '', 18, 46, '2026-07-13 06:12:26'),
(67, 'Rae Russo', '+8801700000000', 'lorop@mailinator.com', '827ccb0eea8a706c4c34a16891f84e7b', 'Booked', 'Tenant', '1988-04', '1986-10-18', 'Enim est incididunt ', 'Quam quis ut dicta q', 36, '', '', 17, 32, '2026-07-13 06:46:49'),
(68, 'ffffffffffffffffff', '+8801878657656', 'fyreqoza@mailinator.com', '827ccb0eea8a706c4c34a16891f84e7b', 'Booked', 'Tenant', '1979-09', '1979-12-22', 'Id perferendis nisi ', 'Nesciunt eius tempo', 95, '', '', 18, 52, '2026-07-13 06:56:08');

-- --------------------------------------------------------

--
-- Table structure for table `unit`
--

CREATE TABLE `unit` (
  `id` int(11) NOT NULL,
  `unit_name` varchar(100) NOT NULL,
  `building_name` int(11) NOT NULL,
  `floor` text DEFAULT NULL,
  `unit_type` enum('Flat','Room','Shop') DEFAULT 'Flat',
  `size` varchar(50) DEFAULT NULL,
  `rent` int(11) DEFAULT NULL,
  `advance` int(11) DEFAULT NULL,
  `unit_image` varchar(255) DEFAULT NULL,
  `status` enum('Available','Rented') DEFAULT 'Available',
  `gas` int(11) DEFAULT NULL,
  `water` int(11) DEFAULT NULL,
  `available_from_date` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `unit`
--

INSERT INTO `unit` (`id`, `unit_name`, `building_name`, `floor`, `unit_type`, `size`, `rent`, `advance`, `unit_image`, `status`, `gas`, `water`, `available_from_date`, `created_at`) VALUES
(28, '0--A', 17, '2 Bedroom, 1 Bathroom,1 Kitchen/Baranda', 'Flat', 'Sub-Meater', 5000, 0, '', 'Rented', 1080, 0, NULL, '2026-03-17 04:33:57'),
(29, '1--A', 17, '2/3 Bedroom, 1 Drawing & Dining Room, 2 Bathroom,1 Kitchen ,2 Baranda', 'Flat', '25130191', 10500, 10000, '', 'Rented', 1080, 1053, NULL, '2026-03-17 04:36:47'),
(30, '2--A', 17, '2/3 Bedroom, 1 Drawing & Dining Room, 2 Bathroom,1 Kitchen ,2 Baranda', 'Flat', '25130151', 17500, 35000, '', 'Rented', 1080, 1053, NULL, '2026-03-17 05:31:15'),
(31, '3--A', 17, '2/3 Bedroom, 1 Drawing & Dining Room, 2 Bathroom,1 Kitchen ,2 Baranda', 'Flat', '25130039', 18000, 30000, '', 'Rented', 1080, 1035, NULL, '2026-03-17 05:31:43'),
(32, '4--A', 17, '2/3 Bedroom, 1 Drawing & Dining Room, 2 Bathroom,1 Kitchen ,2 Baranda', 'Flat', '25130128', 16500, 31000, '', 'Available', 1080, 1053, NULL, '2026-03-17 05:32:13'),
(33, '1--B', 17, '2/3 Bedroom, 1 Drawing & Dining Room, 2 Bathroom,1 Kitchen ,1 Baranda', 'Flat', '25130132', 18500, 30000, '', 'Rented', 1080, 1053, NULL, '2026-03-17 05:32:59'),
(34, '2--B', 17, '2/3 Bedroom, 1 Drawing & Dining Room, 2 Bathroom,1 Kitchen ,1 Baranda', 'Flat', '25130185', 16500, 0, '', 'Rented', 1080, 1053, NULL, '2026-03-17 05:33:24'),
(35, '3--B', 17, '2/3 Bedroom, 1 Drawing & Dining Room, 2 Bathroom,1 Kitchen ,1 Baranda', 'Flat', '25130170', 18000, 17500, '', 'Rented', 1080, 1053, NULL, '2026-03-17 05:34:01'),
(36, '4--B', 17, '2/3 Bedroom, 1 Drawing & Dining Room, 2 Bathroom,1 Kitchen ,1 Baranda', 'Flat', '25130147', 16500, 31000, '', 'Rented', 1080, 1053, NULL, '2026-03-17 05:34:32'),
(37, 'DK-1', 17, '8ft X 11ft', 'Shop', 'Sub-Meater', 8000, 100000, '', 'Rented', 0, 1000, NULL, '2026-03-29 16:01:44'),
(38, 'DK-2', 17, '6ftX7ft', 'Shop', 'Sub-Meater', 4000, 30000, '', 'Rented', 0, 0, NULL, '2026-03-29 16:02:47'),
(39, 'S-2', 18, '1 Room,1 Kitchen, 1 Toilet', 'Room', '', 6200, 6400, '', 'Rented', 0, 200, NULL, '2026-03-30 07:47:15'),
(40, 'S-3', 18, '1 Room,1 Kitchen, 1 Toilet', 'Room', '', 6200, 6400, '', 'Rented', 0, 200, NULL, '2026-03-30 07:48:02'),
(41, 'S-5', 18, '1 Room,1 Kitchen, 1 Toilet', 'Room', '', 5700, 5900, '', 'Rented', 0, 200, NULL, '2026-03-30 07:48:53'),
(42, 'S-6', 18, '1 Room,1 Kitchen, 1 Toilet', 'Room', '', 5600, 5800, '', 'Rented', 0, 200, NULL, '2026-03-30 07:49:42'),
(43, 'S-7', 18, '1 Room,1 Kitchen, 1 Toilet', 'Room', '', 6200, 6400, '', 'Rented', 0, 200, NULL, '2026-03-30 07:50:40'),
(44, 'S-8', 18, '1 Room,1 Kitchen, 1 Toilet', 'Room', '', 6200, 6400, '', 'Available', 0, 200, NULL, '2026-03-30 07:54:32'),
(45, 'S-9', 18, '1 Room,1 Kitchen, 1 Toilet', 'Room', '', 5600, 5800, '', 'Rented', 0, 200, NULL, '2026-03-30 07:55:14'),
(46, 'S-10', 18, '1 Room,1 Kitchen, 1 Toilet', 'Room', '', 5600, 5800, '', 'Rented', 0, 200, NULL, '2026-03-30 07:56:02'),
(47, 'S-11', 18, '1 Room,1 Kitchen, 1 Toilet', 'Room', '', 7300, 7500, '', 'Rented', 0, 200, NULL, '2026-03-30 07:56:54'),
(48, 'S-12', 18, '1 Room,1 Kitchen, 1 Toilet', 'Room', '', 6400, 6600, '', 'Rented', 0, 200, NULL, '2026-03-30 07:57:34'),
(50, 'S-14', 18, '1 Room,1 Kitchen, 1 Toilet', 'Flat', '', 6400, 6600, '', 'Rented', 0, 200, NULL, '2026-03-30 07:59:05'),
(51, 'S-15', 18, '1 Room,1 Kitchen, 1 Toilet', 'Room', '', 5600, 5800, '', 'Rented', 0, 200, NULL, '2026-03-30 07:59:49'),
(52, 'S-16', 18, '1 Room,1 Kitchen, 1 Toilet', 'Room', '', 7300, 7500, '', 'Rented', 0, 200, NULL, '2026-03-30 08:00:41'),
(53, 'S-17', 18, '', 'Room', '', 6200, 6400, '', 'Rented', 0, 200, NULL, '2026-03-30 08:01:54'),
(54, 'S-18', 18, '1 Room,1 Kitchen, 1 Toilet', 'Room', '', 6200, 6400, '', 'Available', 0, 200, NULL, '2026-03-30 08:02:32'),
(55, 'S-19', 18, '1 Room,1 Kitchen, 1 Toilet', 'Room', '', 5600, 5800, '', 'Available', 0, 200, NULL, '2026-03-30 08:03:03'),
(56, 'S-20', 18, '1 Room,1 Kitchen, 1 Toilet', 'Room', '', 5000, 5200, '', 'Rented', 0, 200, NULL, '2026-03-30 08:03:54'),
(57, 'S-21', 18, '1 Room,1 Kitchen, 1 Toilet', 'Room', '', 6500, 6700, '', 'Rented', 0, 200, NULL, '2026-03-30 08:04:46'),
(58, 'S-22', 18, '1 Room,1 Kitchen, 1 Toilet', 'Room', '', 6300, 6500, '', 'Rented', 0, 200, NULL, '2026-03-30 08:05:22'),
(59, 'S-23', 18, '1 Room,1 Kitchen, 1 Toilet', 'Room', '', 6300, 6500, '', 'Available', 0, 200, NULL, '2026-03-30 08:07:51'),
(60, 'D-1', 18, '2 Room,1 Kitchen, 1 Toilet', 'Room', '', 8800, 9000, '', 'Rented', 0, 200, '2026-11', '2026-03-30 08:12:14'),
(61, 'D-2', 18, '2 Room,1 Kitchen, 1 Toilet', 'Room', '', 7300, 7500, '', 'Rented', 0, 200, '2027-02', '2026-03-30 08:13:01'),
(62, 'DK-1', 18, '11ft x 9 fy', 'Shop', '', 12000, 150000, '', 'Rented', 0, 500, NULL, '2026-03-30 08:14:24'),
(63, 'DK-2', 18, '6 ft x 7 ft', 'Shop', '', 6500, 50000, '', 'Rented', 0, 0, NULL, '2026-03-30 08:15:29'),
(64, 'DK-3', 18, '10 ft x 11 ft', 'Shop', '', 12000, 150000, '', 'Rented', 0, 500, NULL, '2026-03-30 08:16:45'),
(65, 'DK-4', 18, '6 ft x 11 ft', 'Shop', '', 6500, 75000, '', 'Rented', 0, 500, NULL, '2026-03-30 08:17:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `advance`
--
ALTER TABLE `advance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `unit_id` (`unit_id`);

--
-- Indexes for table `building`
--
ALTER TABLE `building`
  ADD PRIMARY KEY (`id`),
  ADD KEY `building_type` (`building_type`);

--
-- Indexes for table `expense`
--
ALTER TABLE `expense`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `unit_id` (`unit_id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `bill_month` (`bill_month`),
  ADD KEY `invoice_id` (`tenant_id`),
  ADD KEY `tenant_id_2` (`tenant_id`),
  ADD KEY `invoice_id_2` (`invoice_id`);

--
-- Indexes for table `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `building_id` (`building_id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `phone` (`phone`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `unit`
--
ALTER TABLE `unit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `building_name` (`building_name`),
  ADD KEY `status` (`status`),
  ADD KEY `unit_name` (`unit_name`),
  ADD KEY `rent` (`rent`),
  ADD KEY `advance` (`advance`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `advance`
--
ALTER TABLE `advance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `building`
--
ALTER TABLE `building`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `expense`
--
ALTER TABLE `expense`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `payment_history`
--
ALTER TABLE `payment_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `unit`
--
ALTER TABLE `unit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tenants`
--
ALTER TABLE `tenants`
  ADD CONSTRAINT `tenants_ibfk_1` FOREIGN KEY (`building_id`) REFERENCES `building` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tenants_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `unit` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
