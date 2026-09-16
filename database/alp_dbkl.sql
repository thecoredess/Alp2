-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 10, 2026 at 09:50 AM
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
-- Database: `alp_dbkl`
--

-- --------------------------------------------------------

--
-- Table structure for table `allocations`
--

CREATE TABLE `allocations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `alp_id` bigint(20) UNSIGNED NOT NULL,
  `financial_year_id` bigint(20) UNSIGNED NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `allocations`
--

INSERT INTO `allocations` (`id`, `alp_id`, `financial_year_id`, `reference_no`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'DBKL/BGT/2026/0001', 'Peruntukan tahunan (data pembangunan)', NULL, '2026-09-03 23:14:31', '2026-09-03 23:14:31'),
(2, 2, 1, 'DBKL/BGT/2026/0002', 'Peruntukan tahunan (data pembangunan)', NULL, '2026-09-03 23:14:32', '2026-09-03 23:14:32'),
(3, 3, 1, 'DBKL/BGT/2026/0003', 'Peruntukan tahunan (data pembangunan)', NULL, '2026-09-03 23:14:32', '2026-09-03 23:14:32'),
(4, 4, 1, 'URS/2026/ALP-04', 'Peruntukan tahunan URS', 2, '2026-09-05 06:18:27', '2026-09-05 06:18:27'),
(5, 5, 1, 'URS/2026/ALP-05', 'Peruntukan tahunan URS', 2, '2026-09-05 06:18:27', '2026-09-05 06:18:27'),
(6, 6, 1, 'URS/2026/ALP-06', 'Peruntukan tahunan URS', 2, '2026-09-05 06:18:27', '2026-09-05 06:18:27'),
(7, 7, 1, 'URS/2026/ALP-07', 'Peruntukan tahunan URS', 2, '2026-09-05 06:18:27', '2026-09-05 06:18:27'),
(8, 8, 1, 'URS/2026/ALP-08', 'Peruntukan tahunan URS', 2, '2026-09-05 06:18:27', '2026-09-05 06:18:27'),
(9, 9, 1, 'URS/2026/ALP-09', 'Peruntukan tahunan URS', 2, '2026-09-05 06:18:27', '2026-09-05 06:18:27'),
(10, 10, 1, 'URS/2026/ALP-10', 'Peruntukan tahunan URS', 2, '2026-09-05 06:18:27', '2026-09-05 06:18:27'),
(11, 11, 1, 'URS/2026/ALP-11', 'Peruntukan tahunan URS', 2, '2026-09-05 06:18:27', '2026-09-05 06:18:27'),
(12, 12, 1, 'URS/2026/ALP-12', 'Peruntukan tahunan URS', 2, '2026-09-05 06:18:27', '2026-09-05 06:18:27'),
(13, 13, 1, 'URS/2026/ALP-13', 'Peruntukan tahunan URS', 2, '2026-09-05 06:18:27', '2026-09-05 06:18:27'),
(14, 14, 1, 'URS/2026/ALP-14', 'Peruntukan tahunan URS', 2, '2026-09-05 06:18:27', '2026-09-05 06:18:27'),
(15, 15, 1, 'URS/2026/ALP-15', 'Peruntukan tahunan URS', 2, '2026-09-05 06:18:27', '2026-09-05 06:18:27');

-- --------------------------------------------------------

--
-- Table structure for table `alps`
--

CREATE TABLE `alps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `ref_code` varchar(50) NOT NULL,
  `portfolio_zone` varchar(255) DEFAULT NULL,
  `appointment_start` date DEFAULT NULL,
  `appointment_end` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `alps`
--

INSERT INTO `alps` (`id`, `name`, `ref_code`, `portfolio_zone`, `appointment_start`, `appointment_end`, `status`, `phone`, `email`, `address`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'YBhg. Datuk Muhammad Azmi bin Mohd Zain', 'ALP-01', 'Ketua Pengarah (Jabatan Wilayah Persekutuan)', '2026-02-09', '2028-02-08', 'active', '013-3977697', 'azmizain@jwp.gov.my', NULL, 'IC: 710803035319\nPA: Puan Syika · norsyika@jwp.gov.my\nSumber: Senarai ALP DBKL Jun 2026', NULL, '2026-09-03 23:14:28', '2026-09-05 04:24:46'),
(2, 'Y.A.D Raja Dato\' Muzaffar bin Raja Redzwa', 'ALP-02', 'Orang Besar Daerah Hulu Selangor', '2023-11-09', NULL, 'active', '013-3516079', 'muzaffri888@gmail.com', NULL, 'IC: 580429106355\nTamat lantikan: sehingga dan selagi perwakilan itu diperkenan oleh Raja dalam Mesyuarat\nSumber: Senarai ALP DBKL Jun 2026', NULL, '2026-09-03 23:14:28', '2026-09-05 04:24:47'),
(3, 'Y.A.D Dato\' Setia Haji Haris bin Kasim', 'ALP-03', '—', '2023-11-09', NULL, 'active', '012-2937007', 'silverpatris@gmail.com', NULL, 'IC: 630129085209\nTamat lantikan: sehingga dan selagi perwakilan itu diperkenan oleh Raja dalam Mesyuarat\nSumber: Senarai ALP DBKL Jun 2026', NULL, '2026-09-03 23:14:28', '2026-09-05 04:24:47'),
(4, 'YBhg. Dato\' Sri Ab Rahim bin Ab Rahman', 'ALP-04', 'Timbalan Ketua Setiausaha Perbendaharaan (Pengurusan) Kementerian Kewangan', '2026-05-15', '2028-05-14', 'active', '019-6237576', 'ab.rahim@treasury.gov.my', NULL, 'IC: 721210035253\nEmel PA: norasakina.teramuji@treasury.gov.my\nSumber: Senarai ALP DBKL Jun 2026', NULL, '2026-09-05 04:24:47', '2026-09-05 04:24:47'),
(5, 'YBrs. Encik Che Kodir bin Baharum', 'ALP-05', 'Pengarah Bahagian Perkhidmatan Sosial Kementerian Ekonomi', '2024-09-01', '2026-08-31', 'active', '019-2762237', 'Kodir.baharum@ekonomi.gov.my', NULL, 'IC: 710925025821\nEmel PA: norahayu.zakaria@ekonomi.gov.my\nSumber: Senarai ALP DBKL Jun 2026', NULL, '2026-09-05 04:24:48', '2026-09-05 04:24:48'),
(6, 'YBhg. Datuk Tengku Azman bin Tengku Zainol Abidin', 'ALP-06', 'Ahli Profesional', '2025-09-10', '2027-09-09', 'active', '019-3670036', 'tengkuazman@yahoo.com', NULL, 'IC: 691223715077\nSumber: Senarai ALP DBKL Jun 2026', NULL, '2026-09-05 04:24:48', '2026-09-05 04:24:48'),
(7, 'YBhg. Datuk Azizulrahman bin Mohd Hussain Malim', 'ALP-07', 'Ahli Profesional', '2025-09-10', '2027-09-09', 'active', '019-2290470', 'cukupcekap@gmail.com', NULL, 'IC: 700723075469\nSumber: Senarai ALP DBKL Jun 2026', NULL, '2026-09-05 04:24:48', '2026-09-05 04:24:48'),
(8, 'YBhg. Datuk Tong Nguen Khoong', 'ALP-08', 'Ahli Profesional', '2025-09-10', '2027-09-09', 'active', '012-2096686', 'nkt@bukitkiara.com', NULL, 'IC: 680213105685\nEmel lain: janiechio@bukitkiara.com\nSumber: Senarai ALP DBKL Jun 2026', NULL, '2026-09-05 04:24:48', '2026-09-05 04:24:48'),
(9, 'YBrs. Encik Ahmad Asri bin Talib', 'ALP-09', 'Ahli Profesional', '2025-09-10', '2027-09-09', 'active', '016-2507539', 'asritalib@amanah.org.my', NULL, 'IC: 690803086161\nSumber: Senarai ALP DBKL Jun 2026', NULL, '2026-09-05 04:24:49', '2026-09-05 04:24:49'),
(10, 'YBrs. Dr. Pua Eng Teck', 'ALP-10', 'Ahli Profesional', '2025-09-10', '2027-09-09', 'active', '016-3220909', 'Ronaldpua19@gmail.com', NULL, 'IC: 820419105471\nSumber: Senarai ALP DBKL Jun 2026', NULL, '2026-09-05 04:24:49', '2026-09-05 04:24:49'),
(11, 'YBrs. Encik Mohd Ashraf bin Mazlan', 'ALP-11', 'Ahli Profesional', '2025-09-10', '2027-09-09', 'active', '017-2337467', 'Ashraf.mazlan@gmail.com', NULL, 'IC: 810725105059\nSumber: Senarai ALP DBKL Jun 2026', NULL, '2026-09-05 04:24:49', '2026-09-05 04:24:49'),
(12, 'YBrs. Puan Idawate binti Pariman', 'ALP-12', 'Ahli Profesional', '2025-09-10', '2027-09-09', 'active', '010-5502416', 'aidareez@yahoo.com', NULL, 'IC: 750909135824\nSumber: Senarai ALP DBKL Jun 2026', NULL, '2026-09-05 04:24:50', '2026-09-05 04:24:50'),
(13, 'YBrs. Puan Choo Chen Leece', 'ALP-13', 'Ahli Profesional', '2025-09-10', '2027-09-09', 'active', '016-2582280', 'Janicechoo2@gmail.com', NULL, 'IC: 871003145584\nSumber: Senarai ALP DBKL Jun 2026', NULL, '2026-09-05 04:24:50', '2026-09-05 04:24:50'),
(14, 'YBrs. Encik Lee Bing Hong', 'ALP-14', 'Ahli Profesional', '2025-09-10', '2027-09-09', 'active', '012-3375968', 'terencelbh@gmail.com', NULL, 'IC: 850301146371\nSumber: Senarai ALP DBKL Jun 2026', NULL, '2026-09-05 04:24:50', '2026-09-05 04:24:50'),
(15, 'YBrs. Encik Thiyagaraj Sankaranarayanan', 'ALP-15', 'Ahli Profesional', '2026-05-01', '2028-04-30', 'active', '016-4756587', 'thiyagaraj.harapan@gmail.com', NULL, 'IC: 810808075465\nSumber: Senarai ALP DBKL Jun 2026', NULL, '2026-09-05 04:24:51', '2026-09-05 04:24:51');

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_number` varchar(40) NOT NULL,
  `financial_year_id` bigint(20) UNSIGNED NOT NULL,
  `alp_id` bigint(20) UNSIGNED NOT NULL,
  `recipient_id` bigint(20) UNSIGNED DEFAULT NULL,
  `application_type` varchar(20) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `recipient_name` varchar(255) DEFAULT NULL,
  `recipient_ros_number` varchar(64) DEFAULT NULL,
  `program_date` date DEFAULT NULL,
  `program_category` varchar(40) DEFAULT NULL,
  `recipient_bank_account` varchar(64) DEFAULT NULL,
  `recipient_address` varchar(500) DEFAULT NULL,
  `requested_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(30) NOT NULL DEFAULT 'draft',
  `revision_number` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `payment_status` varchar(40) DEFAULT NULL,
  `payment_voucher_no` varchar(100) DEFAULT NULL,
  `payment_supplier_no` varchar(100) DEFAULT NULL,
  `payment_voucher_date` date DEFAULT NULL,
  `payment_reference` varchar(150) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `payment_remarks` text DEFAULT NULL,
  `payment_updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `payment_updated_at` timestamp NULL DEFAULT NULL,
  `sent_to_jkew_at` timestamp NULL DEFAULT NULL,
  `jkew_crosscheck_status` varchar(32) DEFAULT NULL,
  `jkew_crosscheck_remarks` text DEFAULT NULL,
  `report_card_submitted_at` timestamp NULL DEFAULT NULL,
  `report_card_status` varchar(30) DEFAULT NULL,
  `report_card_reminder_sent_at` timestamp NULL DEFAULT NULL,
  `report_card_remarks` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `application_number`, `financial_year_id`, `alp_id`, `recipient_id`, `application_type`, `purpose`, `recipient_name`, `recipient_ros_number`, `program_date`, `program_category`, `recipient_bank_account`, `recipient_address`, `requested_amount`, `status`, `revision_number`, `submitted_at`, `payment_status`, `payment_voucher_no`, `payment_supplier_no`, `payment_voucher_date`, `payment_reference`, `paid_at`, `payment_remarks`, `payment_updated_by`, `payment_updated_at`, `sent_to_jkew_at`, `jkew_crosscheck_status`, `jkew_crosscheck_remarks`, `report_card_submitted_at`, `report_card_status`, `report_card_reminder_sent_at`, `report_card_remarks`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(17, 'ALP/SUM/2026/0007', 1, 1, 6, 'sumbangan', 'TEST', 'PERSATUAN BELIA PROGRESIF', NULL, NULL, NULL, '165626727177', NULL, 3000.00, 'approved', 0, '2026-09-05 06:42:36', 'pending_payment', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, 3, '2026-09-05 06:41:54', '2026-09-06 05:13:48'),
(18, 'ALP/SUM/2026/0008', 1, 1, 7, 'sumbangan', 'TUJUAN', 'PERSATUAN WARGA EMAS KZ', NULL, NULL, NULL, '1646152564748', NULL, 3000.00, 'approved', 1, '2026-09-07 01:51:58', 'pending_payment', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, 3, '2026-09-07 01:09:18', '2026-09-07 01:56:20'),
(19, 'ALP/SUM/2026/0009', 1, 1, 8, 'sumbangan', 'test', 'test', '1234', '2026-11-09', 'komuniti', '123455', 'kl', 1000.00, 'draft', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, 3, '2026-09-07 06:10:47', '2026-09-07 06:10:47'),
(27, 'ALP/SUM/2026/0017', 1, 1, 9, 'sumbangan', '[SIM] Draf — belum muat naik lampiran', 'Persatuan Komuniti Simulasi KL', 'ROS-SIM-741509', '2026-11-07', 'komuniti', '9876543210', 'No. 10, Jalan Raja Laut, 50350 Kuala Lumpur', 1200.00, 'revision_required', 0, '2026-09-07 06:39:32', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-09-07 06:22:23', '2026-09-07 18:59:07'),
(28, 'ALP/SUM/2026/0018', 1, 1, NULL, 'sumbangan', '[SIM] Draf — sedia hantar kepada JP', 'Persatuan Komuniti Simulasi KL', 'ROS-SIM-625105', '2026-11-07', 'pendidikan', '9876543210', 'No. 10, Jalan Raja Laut, 50350 Kuala Lumpur', 1500.00, 'draft', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(29, 'ALP/SUM/2026/0019', 1, 1, NULL, 'sumbangan', '[SIM] Draf — tarikh program perlu dikemaskini', 'Persatuan Komuniti Simulasi KL', 'ROS-SIM-884104', '2026-10-31', 'sukan', '9876543210', 'No. 10, Jalan Raja Laut, 50350 Kuala Lumpur', 1800.00, 'draft', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-17 06:22:23', '2026-08-17 06:22:23'),
(30, 'ALP/SUM/2026/0020', 1, 1, 9, 'sumbangan', '[SIM] Menunggu semakan Pegawai JP', 'Persatuan Komuniti Simulasi KL', 'ROS-SIM-360011', '2026-11-07', 'kemasyarakatan', '9876543210', 'No. 10, Jalan Raja Laut, 50350 Kuala Lumpur', 300.00, 'revision_required', 0, '2026-09-07 06:22:23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-09-07 06:22:23', '2026-09-07 19:16:07'),
(31, 'ALP/SUM/2026/0021', 1, 1, 9, 'sumbangan', '[SIM] Menunggu Peraku (TP/Pengarah JP)', 'Persatuan Komuniti Simulasi KL', 'ROS-SIM-421545', '2026-11-07', 'komuniti', '9876543210', 'No. 10, Jalan Raja Laut, 50350 Kuala Lumpur', 300.00, 'pending_approval', 0, '2026-09-07 06:22:23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(32, 'ALP/SUM/2026/0022', 1, 1, 9, 'sumbangan', '[SIM] Menunggu kelulusan PEPU', 'Persatuan Komuniti Simulasi KL', 'ROS-SIM-965441', '2026-11-07', 'pendidikan', '9876543210', 'No. 10, Jalan Raja Laut, 50350 Kuala Lumpur', 350.00, 'approved', 0, '2026-09-07 06:22:23', 'voucher_prepared', '12qqqqqqqqqqqqqqq', 'qqqqqqqqqqqqqq', '2026-09-08', NULL, NULL, NULL, 8, '2026-09-08 06:31:10', NULL, NULL, NULL, '2026-09-08 06:32:25', 'approved', NULL, NULL, NULL, 3, '2026-09-07 06:22:23', '2026-09-08 06:34:27'),
(33, 'ALP/SUM/2026/0023', 1, 1, 9, 'sumbangan', '[SIM] Diluluskan — menunggu baucar', 'Persatuan Komuniti Simulasi KL', 'ROS-SIM-699319', '2026-11-07', 'komuniti', '9876543210', 'No. 10, Jalan Raja Laut, 50350 Kuala Lumpur', 350.00, 'approved', 0, '2026-09-07 06:22:23', 'pending_payment', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(34, 'ALP/SUM/2026/0024', 1, 1, 9, 'sumbangan', '[SIM] Diluluskan — baucar disedia', 'Persatuan Komuniti Simulasi KL', 'ROS-SIM-822020', '2026-11-07', 'sukan', '9876543210', 'No. 10, Jalan Raja Laut, 50350 Kuala Lumpur', 400.00, 'approved', 0, '2026-09-07 06:22:23', 'voucher_prepared', 'BV/SIM/2026/001', '14587463', '2026-09-08', NULL, NULL, 'ok', 8, '2026-09-07 20:01:26', NULL, NULL, NULL, '2026-09-08 06:14:38', 'approved', NULL, NULL, NULL, 3, '2026-09-07 06:22:23', '2026-09-08 06:16:28'),
(35, 'ALP/SUM/2026/0025', 1, 1, 9, 'sumbangan', '[SIM] Permohonan ditolak', 'Persatuan Komuniti Simulasi KL', 'ROS-SIM-010929', '2026-11-07', 'kemasyarakatan', '9876543210', 'No. 10, Jalan Raja Laut, 50350 Kuala Lumpur', 300.00, 'rejected', 0, '2026-09-07 06:22:23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(36, 'ALP/SUM/2026/0026', 1, 1, 10, 'sumbangan', 'test2', 'test 2', '123456', '2026-10-08', 'komuniti', '123', 'kl', 100.00, 'draft', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, 3, '2026-09-07 06:27:53', '2026-09-07 06:27:53'),
(37, 'ALP/SUM/2026/0027', 1, 1, 11, 'sumbangan', 'PERTANDINGAN RATU CANTIK', 'PERSATUAN BELIA BELIAWANIS CANTIK', 'A33983', '2026-10-08', 'komuniti', '164164816993', 'JALAN RAJA LAUT, POSKOD 50350, KUALA LUMPUR', 1000.00, 'submitted', 0, '2026-09-07 19:39:47', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 2, '2026-09-07 19:38:49', '2026-09-07 19:39:47'),
(38, 'ALP/SUM/2026/0028', 1, 1, 12, 'sumbangan', 'untuk program masyarakat asli', 'faizal warga emas', '44444444', '2026-11-15', 'kemasyarakatan', '55555555555', '02 jalan butik kl', 100.00, 'approved', 0, '2026-09-07 20:34:00', 'voucher_prepared', 'c33333333', 'sssssssssssssss', '2026-09-08', NULL, NULL, NULL, 8, '2026-09-07 23:13:59', NULL, NULL, NULL, '2026-09-08 05:09:32', 'approved', NULL, NULL, 3, 3, '2026-09-07 20:32:32', '2026-09-08 05:09:32'),
(39, 'ALP/SUM/2026/0029', 1, 2, 8, 'sumbangan', 'test', 'final 1', '1234', '2026-11-09', 'kemasyarakatan', '1234567890', 'kl', 1000.00, 'approved', 0, '2026-09-08 00:34:49', 'voucher_prepared', '012310', '123456789', '2026-09-08', NULL, NULL, 'ok', 8, '2026-09-08 01:11:50', NULL, NULL, NULL, '2026-09-08 05:54:46', 'approved', NULL, NULL, 4, 4, '2026-09-08 00:32:39', '2026-09-08 05:54:46');

-- --------------------------------------------------------

--
-- Table structure for table `application_approvals`
--

CREATE TABLE `application_approvals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `approval_level_id` bigint(20) UNSIGNED DEFAULT NULL,
  `approver_id` bigint(20) UNSIGNED DEFAULT NULL,
  `decision` varchar(30) NOT NULL,
  `comments` text DEFAULT NULL,
  `sequence` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `revision_number` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `decided_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application_approvals`
--

INSERT INTO `application_approvals` (`id`, `application_id`, `approval_level_id`, `approver_id`, `decision`, `comments`, `sequence`, `revision_number`, `decided_at`, `created_at`) VALUES
(1, 17, NULL, 10, 'approved', 'diluluskan untuk dibawa ke PEPU', 1, 0, '2026-09-06 04:54:46', '2026-09-06 04:54:46'),
(2, 17, 5, 11, 'approved', 'diluluskan', 2, 0, '2026-09-06 05:13:48', '2026-09-06 05:13:48'),
(3, 18, 4, 10, 'approved', 'DISYORKAN', 1, 0, '2026-09-07 01:44:58', '2026-09-07 01:44:58'),
(4, 18, 5, 10, 'return_for_revision', 'PERSATUAN PENAH MOHON', 2, 0, '2026-09-07 01:47:05', '2026-09-07 01:47:05'),
(5, 18, 4, 10, 'approved', 'DISYTORLAN', 1, 1, '2026-09-07 01:53:13', '2026-09-07 01:53:13'),
(6, 18, 5, 11, 'approved', 'DILULUSKAN', 2, 1, '2026-09-07 01:56:20', '2026-09-07 01:56:20'),
(7, 32, 4, 10, 'approved', 'Peraku lulus (simulasi)', 1, 0, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(8, 33, 4, 10, 'approved', NULL, 1, 0, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(9, 33, 5, 11, 'approved', 'PEPU lulus (simulasi)', 2, 0, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(10, 34, 4, 10, 'approved', NULL, 1, 0, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(11, 34, 5, 11, 'approved', NULL, 2, 0, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(12, 35, 4, 10, 'rejected', 'Ditolak (simulasi)', 1, 0, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(13, 31, 4, 10, 'approved', 'syor', 1, 0, '2026-09-07 23:11:24', '2026-09-07 23:11:24'),
(14, 38, 4, 10, 'approved', NULL, 1, 0, '2026-09-07 23:11:44', '2026-09-07 23:11:44'),
(15, 38, 5, 11, 'approved', NULL, 2, 0, '2026-09-07 23:12:22', '2026-09-07 23:12:22'),
(16, 39, 4, 10, 'approved', NULL, 1, 0, '2026-09-08 00:53:42', '2026-09-08 00:53:42'),
(17, 39, 5, 11, 'approved', 'ok', 2, 0, '2026-09-08 00:54:17', '2026-09-08 00:54:17'),
(18, 32, 5, 11, 'approved', NULL, 2, 0, '2026-09-08 06:30:15', '2026-09-08 06:30:15');

-- --------------------------------------------------------

--
-- Table structure for table `application_documents`
--

CREATE TABLE `application_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `document_type` varchar(40) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `stored_path` varchar(255) NOT NULL,
  `mime_type` varchar(150) DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `sha256` varchar(64) DEFAULT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application_documents`
--

INSERT INTO `application_documents` (`id`, `application_id`, `document_type`, `original_filename`, `stored_path`, `mime_type`, `file_size`, `sha256`, `uploaded_by`, `created_at`, `updated_at`) VALUES
(16, 17, 'pendaftaran_pertubuhan', 'cadangan_teknikal_harga (1).pdf', 'applications/17/9JrjPF1dvccJHBib37s1PYOmuTRP0tmYCGb7Y6PO.pdf', 'application/pdf', 79472, '4af075ae6118e9b1221c3192804ae1ba59ec0663eb31d84deb957761fd8da3f8', 3, '2026-09-05 06:42:00', '2026-09-05 06:42:00'),
(17, 17, 'borang_eft', 'cadangan_teknikal_harga (1).pdf', 'applications/17/0nvQLD1n4atn4ZWVVf2qid05eOuXzzHZ7822Fbq7.pdf', 'application/pdf', 79472, '4af075ae6118e9b1221c3192804ae1ba59ec0663eb31d84deb957761fd8da3f8', 3, '2026-09-05 06:42:05', '2026-09-05 06:42:05'),
(18, 17, 'penyata_bank', 'cadangan_teknikal_harga (1).pdf', 'applications/17/3iJVJxUormBAQ3mIDkmfM2V05A3xPFyxC46Qv1Uh.pdf', 'application/pdf', 79472, '4af075ae6118e9b1221c3192804ae1ba59ec0663eb31d84deb957761fd8da3f8', 3, '2026-09-05 06:42:10', '2026-09-05 06:42:10'),
(19, 17, 'kertas_kerja', 'cadangan_teknikal_harga (1).pdf', 'applications/17/y35bu7cFG5H6xcZ6nLsfUjdW9smXak4f8gNNbxCr.pdf', 'application/pdf', 79472, '4af075ae6118e9b1221c3192804ae1ba59ec0663eb31d84deb957761fd8da3f8', 3, '2026-09-05 06:42:16', '2026-09-05 06:42:16'),
(20, 17, 'sijil_ros', 'cadangan_teknikal_harga (1).pdf', 'applications/17/XEhzs9kuiuAqqScOv2bsvnhDvDsAkNo2ofBhTiwJ.pdf', 'application/pdf', 79472, '4af075ae6118e9b1221c3192804ae1ba59ec0663eb31d84deb957761fd8da3f8', 3, '2026-09-05 06:42:20', '2026-09-05 06:42:20'),
(21, 18, 'pendaftaran_pertubuhan', 'Panduan-Dokumen-Persatuan-ALP-DBKL (1).pdf', 'applications/18/DI6o4MtnfzsWMHsn6umXVxJDepU6HjxqQOtr1pCD.pdf', 'application/pdf', 1368965, 'ad96fe82bd1ef9c744cc8540910b3a28664a09976659aa65e775fc276c4d614e', 3, '2026-09-07 01:09:57', '2026-09-07 01:09:57'),
(22, 18, 'borang_eft', 'Panduan-Dokumen-Persatuan-ALP-DBKL (1).pdf', 'applications/18/9Gwvpsr7UcnyEg4gURBlVttIHaLSApHLE6EGD8zF.pdf', 'application/pdf', 1368965, 'ad96fe82bd1ef9c744cc8540910b3a28664a09976659aa65e775fc276c4d614e', 3, '2026-09-07 01:10:02', '2026-09-07 01:10:02'),
(23, 18, 'penyata_bank', 'Panduan-Dokumen-Persatuan-ALP-DBKL (1).pdf', 'applications/18/hOFxioHM1ob56lHas94fAJY9xrSNxVcem6XoIW2E.pdf', 'application/pdf', 1368965, 'ad96fe82bd1ef9c744cc8540910b3a28664a09976659aa65e775fc276c4d614e', 3, '2026-09-07 01:10:16', '2026-09-07 01:10:16'),
(24, 18, 'kertas_kerja', 'Panduan-Dokumen-Persatuan-ALP-DBKL (1).pdf', 'applications/18/OzXx9D2UVGfr7dqHarugvWH8KpgQTlhYJFohRSeC.pdf', 'application/pdf', 1368965, 'ad96fe82bd1ef9c744cc8540910b3a28664a09976659aa65e775fc276c4d614e', 3, '2026-09-07 01:10:22', '2026-09-07 01:10:22'),
(25, 18, 'sijil_ros', 'Panduan-Dokumen-Persatuan-ALP-DBKL (1).pdf', 'applications/18/v9wIfi4gMFAfZprU7hXDdymUScYawbks3vQqJ3OW.pdf', 'application/pdf', 1368965, 'ad96fe82bd1ef9c744cc8540910b3a28664a09976659aa65e775fc276c4d614e', 3, '2026-09-07 01:10:29', '2026-09-07 01:10:29'),
(26, 19, 'pendaftaran_pertubuhan', 'Panduan-Dokumen-Persatuan-ALP-DBKL (1) (1).pdf', 'applications/19/hL26I0PwlXdneoad1FwtEoVV0iUJby03v2fa8ReF.pdf', 'application/pdf', 1368965, 'ad96fe82bd1ef9c744cc8540910b3a28664a09976659aa65e775fc276c4d614e', 3, '2026-09-07 06:11:33', '2026-09-07 06:11:33'),
(47, 28, 'kertas_kerja', 'sim-kertas_kerja.pdf', 'simulation/28/sim-kertas_kerja.pdf', 'application/pdf', 37, 'ed9f3ba77c4c9c6bf52290c3882653a191ff553339db589cd641f4687802486f', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(48, 28, 'pendaftaran_pertubuhan', 'sim-pendaftaran_pertubuhan.pdf', 'simulation/28/sim-pendaftaran_pertubuhan.pdf', 'application/pdf', 47, 'c5a53ed4869201acd718f5e7ff2f23a8ee471b5f691cc54c7da7282eec961c33', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(49, 28, 'borang_eft', 'sim-borang_eft.pdf', 'simulation/28/sim-borang_eft.pdf', 'application/pdf', 35, '34bd1aa0012502ff3044ad4a95bd7e1c48242a4b52f557799a47283ef4657ae7', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(50, 28, 'penyata_bank', 'sim-penyata_bank.pdf', 'simulation/28/sim-penyata_bank.pdf', 'application/pdf', 37, '68a1f4898d5c01257eaf473b9a38273be24b46ec62f9d64ea16175ad78247ad8', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(51, 28, 'sijil_ros', 'sim-sijil_ros.pdf', 'simulation/28/sim-sijil_ros.pdf', 'application/pdf', 34, '1fa121629bfd1c8a77e3fa18318534427fb1efd63eb15c859790015132bf36e4', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(52, 29, 'kertas_kerja', 'sim-kertas_kerja.pdf', 'simulation/29/sim-kertas_kerja.pdf', 'application/pdf', 37, 'ed9f3ba77c4c9c6bf52290c3882653a191ff553339db589cd641f4687802486f', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(53, 29, 'pendaftaran_pertubuhan', 'sim-pendaftaran_pertubuhan.pdf', 'simulation/29/sim-pendaftaran_pertubuhan.pdf', 'application/pdf', 47, 'c5a53ed4869201acd718f5e7ff2f23a8ee471b5f691cc54c7da7282eec961c33', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(54, 29, 'borang_eft', 'sim-borang_eft.pdf', 'simulation/29/sim-borang_eft.pdf', 'application/pdf', 35, '34bd1aa0012502ff3044ad4a95bd7e1c48242a4b52f557799a47283ef4657ae7', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(55, 29, 'penyata_bank', 'sim-penyata_bank.pdf', 'simulation/29/sim-penyata_bank.pdf', 'application/pdf', 37, '68a1f4898d5c01257eaf473b9a38273be24b46ec62f9d64ea16175ad78247ad8', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(56, 29, 'sijil_ros', 'sim-sijil_ros.pdf', 'simulation/29/sim-sijil_ros.pdf', 'application/pdf', 34, '1fa121629bfd1c8a77e3fa18318534427fb1efd63eb15c859790015132bf36e4', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(57, 30, 'kertas_kerja', 'sim-kertas_kerja.pdf', 'simulation/30/sim-kertas_kerja.pdf', 'application/pdf', 37, 'ed9f3ba77c4c9c6bf52290c3882653a191ff553339db589cd641f4687802486f', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(58, 30, 'pendaftaran_pertubuhan', 'sim-pendaftaran_pertubuhan.pdf', 'simulation/30/sim-pendaftaran_pertubuhan.pdf', 'application/pdf', 47, 'c5a53ed4869201acd718f5e7ff2f23a8ee471b5f691cc54c7da7282eec961c33', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(59, 30, 'borang_eft', 'sim-borang_eft.pdf', 'simulation/30/sim-borang_eft.pdf', 'application/pdf', 35, '34bd1aa0012502ff3044ad4a95bd7e1c48242a4b52f557799a47283ef4657ae7', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(60, 30, 'penyata_bank', 'sim-penyata_bank.pdf', 'simulation/30/sim-penyata_bank.pdf', 'application/pdf', 37, '68a1f4898d5c01257eaf473b9a38273be24b46ec62f9d64ea16175ad78247ad8', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(61, 30, 'sijil_ros', 'sim-sijil_ros.pdf', 'simulation/30/sim-sijil_ros.pdf', 'application/pdf', 34, '1fa121629bfd1c8a77e3fa18318534427fb1efd63eb15c859790015132bf36e4', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(62, 31, 'kertas_kerja', 'sim-kertas_kerja.pdf', 'simulation/31/sim-kertas_kerja.pdf', 'application/pdf', 37, 'ed9f3ba77c4c9c6bf52290c3882653a191ff553339db589cd641f4687802486f', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(63, 31, 'pendaftaran_pertubuhan', 'sim-pendaftaran_pertubuhan.pdf', 'simulation/31/sim-pendaftaran_pertubuhan.pdf', 'application/pdf', 47, 'c5a53ed4869201acd718f5e7ff2f23a8ee471b5f691cc54c7da7282eec961c33', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(64, 31, 'borang_eft', 'sim-borang_eft.pdf', 'simulation/31/sim-borang_eft.pdf', 'application/pdf', 35, '34bd1aa0012502ff3044ad4a95bd7e1c48242a4b52f557799a47283ef4657ae7', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(65, 31, 'penyata_bank', 'sim-penyata_bank.pdf', 'simulation/31/sim-penyata_bank.pdf', 'application/pdf', 37, '68a1f4898d5c01257eaf473b9a38273be24b46ec62f9d64ea16175ad78247ad8', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(66, 31, 'sijil_ros', 'sim-sijil_ros.pdf', 'simulation/31/sim-sijil_ros.pdf', 'application/pdf', 34, '1fa121629bfd1c8a77e3fa18318534427fb1efd63eb15c859790015132bf36e4', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(67, 32, 'kertas_kerja', 'sim-kertas_kerja.pdf', 'simulation/32/sim-kertas_kerja.pdf', 'application/pdf', 37, 'ed9f3ba77c4c9c6bf52290c3882653a191ff553339db589cd641f4687802486f', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(68, 32, 'pendaftaran_pertubuhan', 'sim-pendaftaran_pertubuhan.pdf', 'simulation/32/sim-pendaftaran_pertubuhan.pdf', 'application/pdf', 47, 'c5a53ed4869201acd718f5e7ff2f23a8ee471b5f691cc54c7da7282eec961c33', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(69, 32, 'borang_eft', 'sim-borang_eft.pdf', 'simulation/32/sim-borang_eft.pdf', 'application/pdf', 35, '34bd1aa0012502ff3044ad4a95bd7e1c48242a4b52f557799a47283ef4657ae7', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(70, 32, 'penyata_bank', 'sim-penyata_bank.pdf', 'simulation/32/sim-penyata_bank.pdf', 'application/pdf', 37, '68a1f4898d5c01257eaf473b9a38273be24b46ec62f9d64ea16175ad78247ad8', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(71, 32, 'sijil_ros', 'sim-sijil_ros.pdf', 'simulation/32/sim-sijil_ros.pdf', 'application/pdf', 34, '1fa121629bfd1c8a77e3fa18318534427fb1efd63eb15c859790015132bf36e4', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(72, 33, 'kertas_kerja', 'sim-kertas_kerja.pdf', 'simulation/33/sim-kertas_kerja.pdf', 'application/pdf', 37, 'ed9f3ba77c4c9c6bf52290c3882653a191ff553339db589cd641f4687802486f', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(73, 33, 'pendaftaran_pertubuhan', 'sim-pendaftaran_pertubuhan.pdf', 'simulation/33/sim-pendaftaran_pertubuhan.pdf', 'application/pdf', 47, 'c5a53ed4869201acd718f5e7ff2f23a8ee471b5f691cc54c7da7282eec961c33', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(74, 33, 'borang_eft', 'sim-borang_eft.pdf', 'simulation/33/sim-borang_eft.pdf', 'application/pdf', 35, '34bd1aa0012502ff3044ad4a95bd7e1c48242a4b52f557799a47283ef4657ae7', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(75, 33, 'penyata_bank', 'sim-penyata_bank.pdf', 'simulation/33/sim-penyata_bank.pdf', 'application/pdf', 37, '68a1f4898d5c01257eaf473b9a38273be24b46ec62f9d64ea16175ad78247ad8', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(76, 33, 'sijil_ros', 'sim-sijil_ros.pdf', 'simulation/33/sim-sijil_ros.pdf', 'application/pdf', 34, '1fa121629bfd1c8a77e3fa18318534427fb1efd63eb15c859790015132bf36e4', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(77, 34, 'kertas_kerja', 'sim-kertas_kerja.pdf', 'simulation/34/sim-kertas_kerja.pdf', 'application/pdf', 37, 'ed9f3ba77c4c9c6bf52290c3882653a191ff553339db589cd641f4687802486f', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(78, 34, 'pendaftaran_pertubuhan', 'sim-pendaftaran_pertubuhan.pdf', 'simulation/34/sim-pendaftaran_pertubuhan.pdf', 'application/pdf', 47, 'c5a53ed4869201acd718f5e7ff2f23a8ee471b5f691cc54c7da7282eec961c33', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(79, 34, 'borang_eft', 'sim-borang_eft.pdf', 'simulation/34/sim-borang_eft.pdf', 'application/pdf', 35, '34bd1aa0012502ff3044ad4a95bd7e1c48242a4b52f557799a47283ef4657ae7', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(80, 34, 'penyata_bank', 'sim-penyata_bank.pdf', 'simulation/34/sim-penyata_bank.pdf', 'application/pdf', 37, '68a1f4898d5c01257eaf473b9a38273be24b46ec62f9d64ea16175ad78247ad8', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(81, 34, 'sijil_ros', 'sim-sijil_ros.pdf', 'simulation/34/sim-sijil_ros.pdf', 'application/pdf', 34, '1fa121629bfd1c8a77e3fa18318534427fb1efd63eb15c859790015132bf36e4', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(82, 35, 'kertas_kerja', 'sim-kertas_kerja.pdf', 'simulation/35/sim-kertas_kerja.pdf', 'application/pdf', 37, 'ed9f3ba77c4c9c6bf52290c3882653a191ff553339db589cd641f4687802486f', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(83, 35, 'pendaftaran_pertubuhan', 'sim-pendaftaran_pertubuhan.pdf', 'simulation/35/sim-pendaftaran_pertubuhan.pdf', 'application/pdf', 47, 'c5a53ed4869201acd718f5e7ff2f23a8ee471b5f691cc54c7da7282eec961c33', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(84, 35, 'borang_eft', 'sim-borang_eft.pdf', 'simulation/35/sim-borang_eft.pdf', 'application/pdf', 35, '34bd1aa0012502ff3044ad4a95bd7e1c48242a4b52f557799a47283ef4657ae7', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(85, 35, 'penyata_bank', 'sim-penyata_bank.pdf', 'simulation/35/sim-penyata_bank.pdf', 'application/pdf', 37, '68a1f4898d5c01257eaf473b9a38273be24b46ec62f9d64ea16175ad78247ad8', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(86, 35, 'sijil_ros', 'sim-sijil_ros.pdf', 'simulation/35/sim-sijil_ros.pdf', 'application/pdf', 34, '1fa121629bfd1c8a77e3fa18318534427fb1efd63eb15c859790015132bf36e4', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(87, 27, 'pendaftaran_pertubuhan', 'Panduan-Dokumen-Persatuan-ALP-DBKL (1) (1).pdf', 'applications/27/Ch0DdpHNTvW4jPEc6bCMhQUpdBeqHd6438NRYt2c.pdf', 'application/pdf', 1368965, 'ad96fe82bd1ef9c744cc8540910b3a28664a09976659aa65e775fc276c4d614e', 3, '2026-09-07 06:36:28', '2026-09-07 06:36:28'),
(88, 27, 'borang_eft', 'Panduan-Dokumen-Persatuan-ALP-DBKL (1) (2).pdf', 'applications/27/2wqu3RCVDQvMOWxLzUbCy9ZVlVZDbjMwWaHtEaJ0.pdf', 'application/pdf', 1368965, 'ad96fe82bd1ef9c744cc8540910b3a28664a09976659aa65e775fc276c4d614e', 3, '2026-09-07 06:36:36', '2026-09-07 06:36:36'),
(89, 27, 'penyata_bank', 'Panduan-Dokumen-Persatuan-ALP-DBKL (1) (1).pdf', 'applications/27/w8rFc2oRZYeKCooL6RSRXgmbO30WmAjLmyubBuVV.pdf', 'application/pdf', 1368965, 'ad96fe82bd1ef9c744cc8540910b3a28664a09976659aa65e775fc276c4d614e', 3, '2026-09-07 06:36:54', '2026-09-07 06:36:54'),
(90, 27, 'kertas_kerja', 'Panduan-Dokumen-Persatuan-ALP-DBKL (1) (1).pdf', 'applications/27/706iSs5YiOtdVAf7aoCwgyN4Nra1CTGHuBResrN1.pdf', 'application/pdf', 1368965, 'ad96fe82bd1ef9c744cc8540910b3a28664a09976659aa65e775fc276c4d614e', 3, '2026-09-07 06:37:04', '2026-09-07 06:37:04'),
(91, 27, 'sijil_ros', 'Panduan-Dokumen-Persatuan-ALP-DBKL (1) (1).pdf', 'applications/27/KWv2RDQdfx6riA8UI6b7Ju13ymSdGSo8Kg3RWoN4.pdf', 'application/pdf', 1368965, 'ad96fe82bd1ef9c744cc8540910b3a28664a09976659aa65e775fc276c4d614e', 3, '2026-09-07 06:37:11', '2026-09-07 06:37:11'),
(92, 37, 'pendaftaran_pertubuhan', 'PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf', 'applications/37/tmG5CBNm6uovI0ncLuU6puWR9BVaJ0sjcJYDMN8O.pdf', 'application/pdf', 71194, 'bf847997d69673ad1c2e220d6246af76df7c919313448ee914e62517cabf8b4e', 2, '2026-09-07 19:39:04', '2026-09-07 19:39:04'),
(93, 37, 'borang_eft', 'PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf', 'applications/37/ss3UkRLDfBn5sbJRW44nzo8PxgWMtIcchaYSJn1g.pdf', 'application/pdf', 71194, 'bf847997d69673ad1c2e220d6246af76df7c919313448ee914e62517cabf8b4e', 2, '2026-09-07 19:39:13', '2026-09-07 19:39:13'),
(94, 37, 'penyata_bank', 'PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf', 'applications/37/wWPXQE13RoP4yafnSmJSt7iOECzhrByauCbrVwt8.pdf', 'application/pdf', 71194, 'bf847997d69673ad1c2e220d6246af76df7c919313448ee914e62517cabf8b4e', 2, '2026-09-07 19:39:21', '2026-09-07 19:39:21'),
(95, 37, 'kertas_kerja', 'PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf', 'applications/37/1qUcOpj1gAUU6AlNGjQHCwgziLQzweuMj2cMq0qH.pdf', 'application/pdf', 71194, 'bf847997d69673ad1c2e220d6246af76df7c919313448ee914e62517cabf8b4e', 2, '2026-09-07 19:39:28', '2026-09-07 19:39:28'),
(96, 37, 'sijil_ros', 'PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf', 'applications/37/nOz6vMAj2oh9BJjK9xMn84NCh43s0HsTkk0pdNI7.pdf', 'application/pdf', 71194, 'bf847997d69673ad1c2e220d6246af76df7c919313448ee914e62517cabf8b4e', 2, '2026-09-07 19:39:37', '2026-09-07 19:39:37'),
(97, 38, 'pendaftaran_pertubuhan', 'PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf', 'applications/38/5QkB3iOKXvrehmbr1hahEAy5Fkbr8nCUzUFcszp0.pdf', 'application/pdf', 71194, 'bf847997d69673ad1c2e220d6246af76df7c919313448ee914e62517cabf8b4e', 3, '2026-09-07 20:32:52', '2026-09-07 20:32:52'),
(98, 38, 'penyata_bank', 'PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf', 'applications/38/KIptX5h38HbJs2VVqTCgKalANff5BIYArrLi2Srh.pdf', 'application/pdf', 71194, 'bf847997d69673ad1c2e220d6246af76df7c919313448ee914e62517cabf8b4e', 3, '2026-09-07 20:33:03', '2026-09-07 20:33:03'),
(99, 38, 'sijil_ros', 'PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf', 'applications/38/6JMwMYxiBonED25yLJdEHvJjA7tTqVagzqmYvFTa.pdf', 'application/pdf', 71194, 'bf847997d69673ad1c2e220d6246af76df7c919313448ee914e62517cabf8b4e', 3, '2026-09-07 20:33:17', '2026-09-07 20:33:17'),
(100, 38, 'borang_eft', 'PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf', 'applications/38/UQCLdhEVB0Ps36wi2I30b57V5054YxbV50rrlVeT.pdf', 'application/pdf', 71194, 'bf847997d69673ad1c2e220d6246af76df7c919313448ee914e62517cabf8b4e', 3, '2026-09-07 20:33:28', '2026-09-07 20:33:28'),
(101, 38, 'kertas_kerja', 'PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf', 'applications/38/F2A04q6LYGHrFIRevoZeeN6psBJV3Rioz5lfVksV.pdf', 'application/pdf', 71194, 'bf847997d69673ad1c2e220d6246af76df7c919313448ee914e62517cabf8b4e', 3, '2026-09-07 20:33:36', '2026-09-07 20:33:36'),
(102, 39, 'pendaftaran_pertubuhan', 'PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf', 'applications/39/hJKs6uz4rSFn1a0GB9bVQ6VdYteg48SiWzzjGveM.pdf', 'application/pdf', 71194, 'bf847997d69673ad1c2e220d6246af76df7c919313448ee914e62517cabf8b4e', 4, '2026-09-08 00:33:05', '2026-09-08 00:33:05'),
(103, 39, 'penyata_bank', 'PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf', 'applications/39/fR109PMjsGhku41HPKBcn618UB1wZkldT5qQP5cB.pdf', 'application/pdf', 71194, 'bf847997d69673ad1c2e220d6246af76df7c919313448ee914e62517cabf8b4e', 4, '2026-09-08 00:33:15', '2026-09-08 00:33:15'),
(104, 39, 'borang_eft', 'PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf', 'applications/39/AtCIbOAvJsnu5NXB7xhxusfzmAjjlM7atgprW26v.pdf', 'application/pdf', 71194, 'bf847997d69673ad1c2e220d6246af76df7c919313448ee914e62517cabf8b4e', 4, '2026-09-08 00:33:23', '2026-09-08 00:33:23'),
(105, 39, 'kertas_kerja', 'PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf', 'applications/39/TxjTIqFu00OuOCLyBPRkUENnRGbmryfX1rjlgP3N.pdf', 'application/pdf', 71194, 'bf847997d69673ad1c2e220d6246af76df7c919313448ee914e62517cabf8b4e', 4, '2026-09-08 00:33:31', '2026-09-08 00:33:31'),
(106, 39, 'sijil_ros', 'PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf', 'applications/39/YKN2HPIwpaBx2uItexej1EFNiCTny8rJQyQQ4ncy.pdf', 'application/pdf', 71194, 'bf847997d69673ad1c2e220d6246af76df7c919313448ee914e62517cabf8b4e', 4, '2026-09-08 00:33:41', '2026-09-08 00:33:41'),
(107, 38, 'laporan_aktiviti', 'Panduan-Dokumen-Persatuan-ALP-DBKL.pdf', 'applications/38/report-card/ijA9gYbgJ17KPB3e98dyrXqmWBzaFxr0TXhe2kb0.pdf', 'application/pdf', 2264908, '4068d60deffb13ebab24fb4556ae82a8fd981b849660a07daeaf8b850c0977f1', 3, '2026-09-08 05:09:32', '2026-09-08 05:09:32'),
(108, 39, 'laporan_aktiviti', 'Panduan-Dokumen-Persatuan-ALP-DBKL.pdf', 'applications/39/report-card/1JyeISZxajzmtEw0flrmAk1RLNo6KJi6KIOC3UbU.pdf', 'application/pdf', 2264908, '4068d60deffb13ebab24fb4556ae82a8fd981b849660a07daeaf8b850c0977f1', 4, '2026-09-08 05:54:46', '2026-09-08 05:54:46'),
(109, 34, 'laporan_aktiviti', 'PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS.pdf', 'applications/34/report-card/CeAd92be00Wy7laalb5ZbVW3fXNRxO4PO5sK8GzD.pdf', 'application/pdf', 71194, 'bf847997d69673ad1c2e220d6246af76df7c919313448ee914e62517cabf8b4e', 3, '2026-09-08 06:14:38', '2026-09-08 06:14:38'),
(110, 32, 'laporan_aktiviti', 'PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf', 'applications/32/report-card/OEbev8R4Jlt1Lc6AlfO0r4PXFoaNWQflz9rYzeSn.pdf', 'application/pdf', 71194, 'bf847997d69673ad1c2e220d6246af76df7c919313448ee914e62517cabf8b4e', 3, '2026-09-08 06:32:25', '2026-09-08 06:32:25');

-- --------------------------------------------------------

--
-- Table structure for table `application_reviews`
--

CREATE TABLE `application_reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `review_type` varchar(20) NOT NULL,
  `reviewer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `decision` varchar(30) NOT NULL,
  `comments` text DEFAULT NULL,
  `checklist` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`checklist`)),
  `revision_number` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application_reviews`
--

INSERT INTO `application_reviews` (`id`, `application_id`, `review_type`, `reviewer_id`, `decision`, `comments`, `checklist`, `revision_number`, `reviewed_at`, `created_at`) VALUES
(6, 17, 'secretariat', 7, 'recommend', 'disyorkan', '{\"recipient\":\"lengkap\",\"program_syarat\":\"lengkap\",\"dokumen\":\"lengkap\",\"bajet\":\"lengkap\",\"baki\":\"lengkap\",\"polisi\":\"lengkap\"}', 0, '2026-09-06 04:43:00', '2026-09-06 04:43:00'),
(7, 18, 'secretariat', 7, 'recommend', 'DISYORKAN', '{\"recipient\":\"lengkap\",\"program_syarat\":\"lengkap\",\"dokumen\":\"lengkap\",\"bajet\":\"lengkap\",\"baki\":\"lengkap\",\"polisi\":\"lengkap\"}', 0, '2026-09-07 01:42:47', '2026-09-07 01:42:47'),
(8, 18, 'secretariat', 7, 'recommend', NULL, '{\"recipient\":\"lengkap\",\"program_syarat\":\"lengkap\",\"dokumen\":\"lengkap\",\"bajet\":\"lengkap\",\"baki\":\"lengkap\",\"polisi\":\"lengkap\"}', 1, '2026-09-07 01:52:45', '2026-09-07 01:52:45'),
(9, 31, 'secretariat', 7, 'recommend', 'Disyorkan (simulasi)', '{\"recipient\":\"lengkap\",\"program_syarat\":\"lengkap\",\"dokumen\":\"lengkap\",\"bajet\":\"lengkap\",\"baki\":\"lengkap\",\"polisi\":\"lengkap\"}', 0, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(10, 32, 'secretariat', 7, 'recommend', NULL, '{\"recipient\":\"lengkap\",\"program_syarat\":\"lengkap\",\"dokumen\":\"lengkap\",\"bajet\":\"lengkap\",\"baki\":\"lengkap\",\"polisi\":\"lengkap\"}', 0, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(11, 33, 'secretariat', 7, 'recommend', NULL, '{\"recipient\":\"lengkap\",\"program_syarat\":\"lengkap\",\"dokumen\":\"lengkap\",\"bajet\":\"lengkap\",\"baki\":\"lengkap\",\"polisi\":\"lengkap\"}', 0, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(12, 34, 'secretariat', 7, 'recommend', NULL, '{\"recipient\":\"lengkap\",\"program_syarat\":\"lengkap\",\"dokumen\":\"lengkap\",\"bajet\":\"lengkap\",\"baki\":\"lengkap\",\"polisi\":\"lengkap\"}', 0, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(13, 35, 'secretariat', 7, 'recommend', NULL, '{\"recipient\":\"lengkap\",\"program_syarat\":\"lengkap\",\"dokumen\":\"lengkap\",\"bajet\":\"lengkap\",\"baki\":\"lengkap\",\"polisi\":\"lengkap\"}', 0, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
(14, 27, 'secretariat', 2, 'return_for_revision', 'buatla betul betul', '{\"recipient\":\"lengkap\",\"program_syarat\":\"lengkap\",\"dokumen\":\"lengkap\",\"bajet\":\"lengkap\",\"baki\":\"lengkap\",\"polisi\":\"tidak_lengkap\"}', 0, '2026-09-07 18:59:07', '2026-09-07 18:59:07'),
(15, 30, 'secretariat', 2, 'return_for_revision', 'betulkan apa yg salah', '{\"recipient\":\"tidak_lengkap\",\"program_syarat\":\"lengkap\",\"dokumen\":\"tidak_lengkap\",\"bajet\":\"lengkap\",\"baki\":\"tidak_lengkap\",\"polisi\":\"lengkap\"}', 0, '2026-09-07 19:16:07', '2026-09-07 19:16:07'),
(16, 38, 'secretariat', 2, 'recommend', 'ok', '{\"recipient\":\"lengkap\",\"program_syarat\":\"lengkap\",\"dokumen\":\"lengkap\",\"bajet\":\"lengkap\",\"baki\":\"lengkap\",\"polisi\":\"lengkap\"}', 0, '2026-09-07 20:36:05', '2026-09-07 20:36:05'),
(17, 38, 'secretariat', 7, 'recommend', 'syor', '{\"recipient\":\"lengkap\",\"program_syarat\":\"lengkap\",\"dokumen\":\"lengkap\",\"bajet\":\"lengkap\",\"baki\":\"lengkap\",\"polisi\":\"lengkap\"}', 0, '2026-09-07 23:07:24', '2026-09-07 23:07:24'),
(18, 39, 'secretariat', 2, 'recommend', 'ok', '{\"recipient\":\"lengkap\",\"program_syarat\":\"lengkap\",\"dokumen\":\"lengkap\",\"bajet\":\"lengkap\",\"baki\":\"lengkap\",\"polisi\":\"lengkap\"}', 0, '2026-09-08 00:50:43', '2026-09-08 00:50:43'),
(19, 39, 'secretariat', 7, 'recommend', NULL, '{\"recipient\":\"lengkap\",\"program_syarat\":\"lengkap\",\"dokumen\":\"lengkap\",\"bajet\":\"lengkap\",\"baki\":\"lengkap\",\"polisi\":\"lengkap\"}', 0, '2026-09-08 00:51:30', '2026-09-08 00:51:30');

-- --------------------------------------------------------

--
-- Table structure for table `application_revisions`
--

CREATE TABLE `application_revisions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `revision_number` int(10) UNSIGNED NOT NULL,
  `previous_requested_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`snapshot`)),
  `returned_by` bigint(20) UNSIGNED DEFAULT NULL,
  `return_stage` varchar(30) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `returned_at` timestamp NULL DEFAULT NULL,
  `resubmitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application_revisions`
--

INSERT INTO `application_revisions` (`id`, `application_id`, `revision_number`, `previous_requested_amount`, `snapshot`, `returned_by`, `return_stage`, `reason`, `returned_at`, `resubmitted_at`, `created_at`) VALUES
(1, 18, 0, 3000.00, '{\"purpose\":\"TUJUAN\",\"recipient_name\":\"PERSATUAN WARGA EMAS KZ\",\"recipient_bank_account\":\"1646152564748\",\"requested_amount\":\"3000.00\",\"documents\":[{\"document_type\":\"pendaftaran_pertubuhan\",\"original_filename\":\"Panduan-Dokumen-Persatuan-ALP-DBKL (1).pdf\"},{\"document_type\":\"borang_eft\",\"original_filename\":\"Panduan-Dokumen-Persatuan-ALP-DBKL (1).pdf\"},{\"document_type\":\"penyata_bank\",\"original_filename\":\"Panduan-Dokumen-Persatuan-ALP-DBKL (1).pdf\"},{\"document_type\":\"kertas_kerja\",\"original_filename\":\"Panduan-Dokumen-Persatuan-ALP-DBKL (1).pdf\"},{\"document_type\":\"sijil_ros\",\"original_filename\":\"Panduan-Dokumen-Persatuan-ALP-DBKL (1).pdf\"}]}', 10, 'approval', 'PERSATUAN PENAH MOHON', '2026-09-07 01:47:05', '2026-09-07 01:51:58', '2026-09-07 01:47:05'),
(2, 27, 0, 1200.00, '{\"purpose\":\"[SIM] Draf \\u2014 belum muat naik lampiran\",\"recipient_name\":\"Persatuan Komuniti Simulasi KL\",\"recipient_bank_account\":\"9876543210\",\"requested_amount\":\"1200.00\",\"documents\":[{\"document_type\":\"borang_eft\",\"original_filename\":\"Panduan-Dokumen-Persatuan-ALP-DBKL (1) (2).pdf\"},{\"document_type\":\"kertas_kerja\",\"original_filename\":\"Panduan-Dokumen-Persatuan-ALP-DBKL (1) (1).pdf\"},{\"document_type\":\"pendaftaran_pertubuhan\",\"original_filename\":\"Panduan-Dokumen-Persatuan-ALP-DBKL (1) (1).pdf\"},{\"document_type\":\"penyata_bank\",\"original_filename\":\"Panduan-Dokumen-Persatuan-ALP-DBKL (1) (1).pdf\"},{\"document_type\":\"sijil_ros\",\"original_filename\":\"Panduan-Dokumen-Persatuan-ALP-DBKL (1) (1).pdf\"}]}', 2, 'secretariat', 'buatla betul betul', '2026-09-07 18:59:07', NULL, '2026-09-07 18:59:07'),
(3, 30, 0, 300.00, '{\"purpose\":\"[SIM] Menunggu semakan Pegawai JP\",\"recipient_name\":\"Persatuan Komuniti Simulasi KL\",\"recipient_bank_account\":\"9876543210\",\"requested_amount\":\"300.00\",\"documents\":[{\"document_type\":\"borang_eft\",\"original_filename\":\"sim-borang_eft.pdf\"},{\"document_type\":\"kertas_kerja\",\"original_filename\":\"sim-kertas_kerja.pdf\"},{\"document_type\":\"pendaftaran_pertubuhan\",\"original_filename\":\"sim-pendaftaran_pertubuhan.pdf\"},{\"document_type\":\"penyata_bank\",\"original_filename\":\"sim-penyata_bank.pdf\"},{\"document_type\":\"sijil_ros\",\"original_filename\":\"sim-sijil_ros.pdf\"}]}', 2, 'secretariat', 'betulkan apa yg salah', '2026-09-07 19:16:07', NULL, '2026-09-07 19:16:07');

-- --------------------------------------------------------

--
-- Table structure for table `application_sequences`
--

CREATE TABLE `application_sequences` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `year` smallint(5) UNSIGNED NOT NULL,
  `type` varchar(20) NOT NULL,
  `last_number` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application_sequences`
--

INSERT INTO `application_sequences` (`id`, `year`, `type`, `last_number`, `created_at`, `updated_at`) VALUES
(1, 2026, 'DEV', 4, '2026-09-03 23:14:32', '2026-09-05 04:34:40'),
(2, 2026, 'CSR', 6, '2026-09-03 23:14:32', '2026-09-03 23:30:48'),
(11, 2026, 'SUM', 29, '2026-09-05 04:55:23', '2026-09-08 00:32:39');

-- --------------------------------------------------------

--
-- Table structure for table `application_status_histories`
--

CREATE TABLE `application_status_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `from_status` varchar(30) DEFAULT NULL,
  `to_status` varchar(30) NOT NULL,
  `changed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application_status_histories`
--

INSERT INTO `application_status_histories` (`id`, `application_id`, `from_status`, `to_status`, `changed_by`, `remarks`, `created_at`) VALUES
(12, 17, 'draft', 'submitted', 3, 'Permohonan dihantar', '2026-09-05 06:42:37'),
(13, 17, 'submitted', 'pending_approval', 7, 'Semakan Pegawai JP selesai', '2026-09-06 04:43:00'),
(14, 17, 'pending_approval', 'approved', 10, 'Permohonan diluluskan', '2026-09-06 04:54:46'),
(15, 17, 'approved', 'pending_approval', NULL, 'Pemulihan aliran: menunggu kelulusan PEPU selepas perakuan TP/Pengarah JP', '2026-09-06 05:08:07'),
(16, 17, 'pending_approval', 'approved', 11, 'Permohonan diluluskan', '2026-09-06 05:13:48'),
(17, 18, 'draft', 'submitted', 3, 'Permohonan dihantar', '2026-09-07 01:12:32'),
(18, 18, 'submitted', 'pending_approval', 7, 'Semakan Pegawai JP selesai', '2026-09-07 01:42:47'),
(19, 18, 'pending_approval', 'pending_approval', 10, 'Kelulusan aras 1 (Peraku untuk diangkat ke PEPU (TP/Pengarah JP)) diberikan', '2026-09-07 01:44:58'),
(20, 18, 'pending_approval', 'revision_required', 10, 'Dikembalikan untuk pembetulan (approval)', '2026-09-07 01:47:05'),
(21, 18, 'revision_required', 'submitted', 3, 'Permohonan dihantar semula (pembetulan #1)', '2026-09-07 01:51:58'),
(22, 18, 'submitted', 'pending_approval', 7, 'Semakan Pegawai JP selesai', '2026-09-07 01:52:45'),
(23, 18, 'pending_approval', 'pending_approval', 10, 'Kelulusan aras 1 (Peraku untuk diangkat ke PEPU (TP/Pengarah JP)) diberikan', '2026-09-07 01:53:13'),
(24, 18, 'pending_approval', 'approved', 11, 'Permohonan diluluskan', '2026-09-07 01:56:20'),
(26, 30, 'draft', 'submitted', 3, 'Permohonan dihantar', '2026-09-07 06:22:23'),
(27, 31, 'draft', 'submitted', 3, 'Permohonan dihantar', '2026-09-07 06:22:23'),
(28, 31, 'submitted', 'pending_approval', 7, 'Semakan Pegawai JP selesai', '2026-09-07 06:22:23'),
(29, 32, 'draft', 'submitted', 3, 'Permohonan dihantar', '2026-09-07 06:22:23'),
(30, 32, 'submitted', 'pending_approval', 7, 'Semakan Pegawai JP selesai', '2026-09-07 06:22:23'),
(31, 32, 'pending_approval', 'pending_approval', 10, 'Kelulusan aras 1 (Peraku untuk diangkat ke PEPU (TP/Pengarah JP)) diberikan', '2026-09-07 06:22:23'),
(32, 33, 'draft', 'submitted', 3, 'Permohonan dihantar', '2026-09-07 06:22:23'),
(33, 33, 'submitted', 'pending_approval', 7, 'Semakan Pegawai JP selesai', '2026-09-07 06:22:23'),
(34, 33, 'pending_approval', 'pending_approval', 10, 'Kelulusan aras 1 (Peraku untuk diangkat ke PEPU (TP/Pengarah JP)) diberikan', '2026-09-07 06:22:23'),
(35, 33, 'pending_approval', 'approved', 11, 'Permohonan diluluskan', '2026-09-07 06:22:23'),
(36, 34, 'draft', 'submitted', 3, 'Permohonan dihantar', '2026-09-07 06:22:23'),
(37, 34, 'submitted', 'pending_approval', 7, 'Semakan Pegawai JP selesai', '2026-09-07 06:22:23'),
(38, 34, 'pending_approval', 'pending_approval', 10, 'Kelulusan aras 1 (Peraku untuk diangkat ke PEPU (TP/Pengarah JP)) diberikan', '2026-09-07 06:22:23'),
(39, 34, 'pending_approval', 'approved', 11, 'Permohonan diluluskan', '2026-09-07 06:22:23'),
(40, 35, 'draft', 'submitted', 3, 'Permohonan dihantar', '2026-09-07 06:22:23'),
(41, 35, 'submitted', 'pending_approval', 7, 'Semakan Pegawai JP selesai', '2026-09-07 06:22:23'),
(42, 35, 'pending_approval', 'rejected', 10, 'Permohonan ditolak', '2026-09-07 06:22:23'),
(43, 27, 'draft', 'submitted', 3, 'Permohonan dihantar', '2026-09-07 06:39:32'),
(44, 27, 'submitted', 'revision_required', 2, 'Dikembalikan untuk pembetulan (secretariat)', '2026-09-07 18:59:07'),
(45, 30, 'submitted', 'revision_required', 2, 'Dikembalikan untuk pembetulan (secretariat)', '2026-09-07 19:16:07'),
(46, 37, 'draft', 'submitted', 2, 'Permohonan dihantar oleh Admin JP kepada Pegawai JP', '2026-09-07 19:39:47'),
(47, 38, 'draft', 'submitted', 3, 'Permohonan dihantar', '2026-09-07 20:34:00'),
(48, 38, 'submitted', 'pending_approval', 2, 'Semakan Pegawai JP selesai', '2026-09-07 20:36:05'),
(49, 38, 'pending_approval', 'under_secretariat_review', 2, 'Dibetulkan: tunggu pengesyoran Pegawai JP selepas Admin JP', '2026-09-07 20:50:42'),
(50, 38, 'under_secretariat_review', 'pending_approval', 7, 'Semakan Pegawai JP selesai', '2026-09-07 23:07:24'),
(51, 31, 'pending_approval', 'pending_approval', 10, 'Kelulusan aras 1 (Pengesyoran PEPU) diberikan', '2026-09-07 23:11:24'),
(52, 38, 'pending_approval', 'pending_approval', 10, 'Kelulusan aras 1 (Pengesyoran PEPU) diberikan', '2026-09-07 23:11:44'),
(53, 38, 'pending_approval', 'approved', 11, 'Permohonan diluluskan', '2026-09-07 23:12:22'),
(54, 39, 'draft', 'submitted', 4, 'Permohonan dihantar', '2026-09-08 00:34:49'),
(55, 39, 'submitted', 'under_secretariat_review', 2, 'Keputusan Admin JP — menunggu perakuan Pegawai JP', '2026-09-08 00:50:43'),
(56, 39, 'under_secretariat_review', 'pending_approval', 7, 'Semakan Pegawai JP selesai', '2026-09-08 00:51:30'),
(57, 39, 'pending_approval', 'pending_approval', 10, 'Kelulusan aras 1 (Pengesyoran PEPU) diberikan', '2026-09-08 00:53:42'),
(58, 39, 'pending_approval', 'approved', 11, 'Permohonan diluluskan', '2026-09-08 00:54:17'),
(59, 32, 'pending_approval', 'approved', 11, 'Permohonan diluluskan', '2026-09-08 06:30:15');

-- --------------------------------------------------------

--
-- Table structure for table `application_workflow_settings`
--

CREATE TABLE `application_workflow_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_type` varchar(20) NOT NULL,
  `requires_technical_review` tinyint(1) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application_workflow_settings`
--

INSERT INTO `application_workflow_settings` (`id`, `application_type`, `requires_technical_review`, `active`, `created_at`, `updated_at`) VALUES
(1, 'csr', 0, 1, '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(2, 'development', 1, 1, '2026-09-03 23:14:28', '2026-09-03 23:14:28');

-- --------------------------------------------------------

--
-- Table structure for table `approval_levels`
--

CREATE TABLE `approval_levels` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `financial_year_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `min_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `max_amount` decimal(15,2) DEFAULT NULL,
  `required_role` varchar(50) NOT NULL,
  `sequence` int(10) UNSIGNED NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `approval_levels`
--

INSERT INTO `approval_levels` (`id`, `financial_year_id`, `name`, `min_amount`, `max_amount`, `required_role`, `sequence`, `active`, `created_at`, `updated_at`) VALUES
(4, NULL, 'Pengesyoran PEPU', 0.00, 999999999.98, 'pelulus', 1, 1, '2026-09-06 05:00:15', '2026-09-07 19:35:03'),
(5, NULL, 'Kelulusan PEPU / Pengurusan Tertinggi', 999999999.99, NULL, 'pengurusan', 2, 1, '2026-09-06 05:00:15', '2026-09-06 05:09:01');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(60) NOT NULL,
  `entity_type` varchar(100) DEFAULT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `created_at`) VALUES
(1, NULL, 'ALLOCATION_REQUEST_CREATED', 'App\\Models\\BudgetRequest', 1, NULL, '{\"type\":\"initial_allocation\",\"amount\":\"500000.00\",\"alp_id\":1}', '127.0.0.1', '2026-09-03 23:14:31'),
(2, NULL, 'ALLOCATION_REQUEST_SUBMITTED', 'App\\Models\\BudgetRequest', 1, NULL, '{\"amount\":\"500000.00\",\"revision_number\":0}', '127.0.0.1', '2026-09-03 23:14:31'),
(3, NULL, 'INITIAL_ALLOCATION_CREATED', 'App\\Models\\Allocation', 1, NULL, '{\"amount\":\"500000.00\",\"reference_no\":\"DBKL\\/BGT\\/2026\\/0001\",\"transaction_id\":1,\"budget_request_id\":1}', '127.0.0.1', '2026-09-03 23:14:31'),
(4, NULL, 'ALLOCATION_REQUEST_APPROVED', 'App\\Models\\BudgetRequest', 1, NULL, '{\"amount\":\"500000.00\",\"checker_id\":10}', '127.0.0.1', '2026-09-03 23:14:31'),
(5, NULL, 'ALLOCATION_REQUEST_CREATED', 'App\\Models\\BudgetRequest', 2, NULL, '{\"type\":\"initial_allocation\",\"amount\":\"500000.00\",\"alp_id\":2}', '127.0.0.1', '2026-09-03 23:14:32'),
(6, NULL, 'ALLOCATION_REQUEST_SUBMITTED', 'App\\Models\\BudgetRequest', 2, NULL, '{\"amount\":\"500000.00\",\"revision_number\":0}', '127.0.0.1', '2026-09-03 23:14:32'),
(7, NULL, 'INITIAL_ALLOCATION_CREATED', 'App\\Models\\Allocation', 2, NULL, '{\"amount\":\"500000.00\",\"reference_no\":\"DBKL\\/BGT\\/2026\\/0002\",\"transaction_id\":2,\"budget_request_id\":2}', '127.0.0.1', '2026-09-03 23:14:32'),
(8, NULL, 'ALLOCATION_REQUEST_APPROVED', 'App\\Models\\BudgetRequest', 2, NULL, '{\"amount\":\"500000.00\",\"checker_id\":10}', '127.0.0.1', '2026-09-03 23:14:32'),
(9, NULL, 'ADJUSTMENT_REQUEST_CREATED', 'App\\Models\\BudgetRequest', 3, NULL, '{\"type\":\"allocation_increase\",\"amount\":\"50000.00\",\"alp_id\":2}', '127.0.0.1', '2026-09-03 23:14:32'),
(10, NULL, 'ADJUSTMENT_REQUEST_SUBMITTED', 'App\\Models\\BudgetRequest', 3, NULL, '{\"amount\":\"50000.00\",\"revision_number\":0}', '127.0.0.1', '2026-09-03 23:14:32'),
(11, NULL, 'ALLOCATION_ADJUSTMENT_CREATED', 'App\\Models\\Allocation', 2, NULL, '{\"amount\":\"50000.00\",\"reference_no\":\"DBKL\\/BGT\\/2026\\/PIND\\/0001\",\"remarks\":\"Tambahan peruntukan (contoh)\",\"transaction_id\":3,\"budget_request_id\":3}', '127.0.0.1', '2026-09-03 23:14:32'),
(12, NULL, 'ADJUSTMENT_REQUEST_APPROVED', 'App\\Models\\BudgetRequest', 3, NULL, '{\"amount\":\"50000.00\",\"checker_id\":10}', '127.0.0.1', '2026-09-03 23:14:32'),
(13, NULL, 'ALLOCATION_REQUEST_CREATED', 'App\\Models\\BudgetRequest', 4, NULL, '{\"type\":\"initial_allocation\",\"amount\":\"300000.00\",\"alp_id\":3}', '127.0.0.1', '2026-09-03 23:14:32'),
(14, NULL, 'ALLOCATION_REQUEST_SUBMITTED', 'App\\Models\\BudgetRequest', 4, NULL, '{\"amount\":\"300000.00\",\"revision_number\":0}', '127.0.0.1', '2026-09-03 23:14:32'),
(15, NULL, 'INITIAL_ALLOCATION_CREATED', 'App\\Models\\Allocation', 3, NULL, '{\"amount\":\"300000.00\",\"reference_no\":\"DBKL\\/BGT\\/2026\\/0003\",\"transaction_id\":4,\"budget_request_id\":4}', '127.0.0.1', '2026-09-03 23:14:32'),
(16, NULL, 'ALLOCATION_REQUEST_APPROVED', 'App\\Models\\BudgetRequest', 4, NULL, '{\"amount\":\"300000.00\",\"checker_id\":10}', '127.0.0.1', '2026-09-03 23:14:32'),
(17, NULL, 'PROJECT_CREATED', 'App\\Models\\Project', 1, NULL, '{\"application_id\":3,\"project_number\":\"PRJ\\/CSR\\/2026\\/0001\",\"approved_amount\":\"40000.00\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(18, NULL, 'BUDGET_COMMITMENT_CREATED', 'App\\Models\\BudgetTransaction', 5, NULL, '{\"application_id\":3,\"application_number\":\"ALP\\/CSR\\/2026\\/0002\",\"amount\":\"40000.00\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(19, NULL, 'PROJECT_STARTED', 'App\\Models\\Project', 1, NULL, '{\"project_number\":\"PRJ\\/CSR\\/2026\\/0001\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(20, NULL, 'PROJECT_PROGRESS_UPDATED', 'App\\Models\\Project', 1, NULL, '{\"progress\":40}', '127.0.0.1', '2026-09-03 23:14:32'),
(21, NULL, 'EXPENSE_CREATED', 'App\\Models\\ProjectExpense', 1, NULL, '{\"amount\":\"15000.00\",\"project_id\":1}', '127.0.0.1', '2026-09-03 23:14:32'),
(22, NULL, 'EXPENSE_SUBMITTED', 'App\\Models\\ProjectExpense', 1, NULL, '{\"amount\":\"15000.00\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(23, NULL, 'BUDGET_EXPENDITURE_CREATED', 'App\\Models\\BudgetTransaction', 6, NULL, '{\"project_id\":1,\"project_expense_id\":1,\"amount\":\"15000.00\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(24, NULL, 'EXPENSE_VERIFIED', 'App\\Models\\ProjectExpense', 1, NULL, '{\"amount\":\"15000.00\",\"checker_id\":10}', '127.0.0.1', '2026-09-03 23:14:32'),
(25, NULL, 'REFUND_CREATED', 'App\\Models\\ProjectExpenseRefund', 1, NULL, '{\"amount\":\"3000.00\",\"project_expense_id\":1}', '127.0.0.1', '2026-09-03 23:14:32'),
(26, NULL, 'REFUND_SUBMITTED', 'App\\Models\\ProjectExpenseRefund', 1, NULL, '{\"amount\":\"3000.00\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(27, NULL, 'BUDGET_REFUND_CREATED', 'App\\Models\\BudgetTransaction', 7, NULL, '{\"project_id\":1,\"project_expense_refund_id\":1,\"amount\":\"3000.00\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(28, NULL, 'REFUND_VERIFIED', 'App\\Models\\ProjectExpenseRefund', 1, NULL, '{\"amount\":\"3000.00\",\"checker_id\":10}', '127.0.0.1', '2026-09-03 23:14:32'),
(29, NULL, 'EXPENSE_CREATED', 'App\\Models\\ProjectExpense', 2, NULL, '{\"amount\":\"5000.00\",\"project_id\":1}', '127.0.0.1', '2026-09-03 23:14:32'),
(30, NULL, 'EXPENSE_SUBMITTED', 'App\\Models\\ProjectExpense', 2, NULL, '{\"amount\":\"5000.00\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(31, NULL, 'REFUND_CREATED', 'App\\Models\\ProjectExpenseRefund', 2, NULL, '{\"amount\":\"1000.00\",\"project_expense_id\":1}', '127.0.0.1', '2026-09-03 23:14:32'),
(32, NULL, 'REFUND_SUBMITTED', 'App\\Models\\ProjectExpenseRefund', 2, NULL, '{\"amount\":\"1000.00\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(33, NULL, 'PROJECT_CREATED', 'App\\Models\\Project', 2, NULL, '{\"application_id\":4,\"project_number\":\"PRJ\\/CSR\\/2026\\/0002\",\"approved_amount\":\"30000.00\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(34, NULL, 'BUDGET_COMMITMENT_CREATED', 'App\\Models\\BudgetTransaction', 8, NULL, '{\"application_id\":4,\"application_number\":\"ALP\\/CSR\\/2026\\/0003\",\"amount\":\"30000.00\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(35, NULL, 'PROJECT_STARTED', 'App\\Models\\Project', 2, NULL, '{\"project_number\":\"PRJ\\/CSR\\/2026\\/0002\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(36, NULL, 'EXPENSE_CREATED', 'App\\Models\\ProjectExpense', 3, NULL, '{\"amount\":\"12000.00\",\"project_id\":2}', '127.0.0.1', '2026-09-03 23:14:32'),
(37, NULL, 'EXPENSE_SUBMITTED', 'App\\Models\\ProjectExpense', 3, NULL, '{\"amount\":\"12000.00\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(38, NULL, 'BUDGET_EXPENDITURE_CREATED', 'App\\Models\\BudgetTransaction', 9, NULL, '{\"project_id\":2,\"project_expense_id\":3,\"amount\":\"12000.00\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(39, NULL, 'EXPENSE_VERIFIED', 'App\\Models\\ProjectExpense', 3, NULL, '{\"amount\":\"12000.00\",\"checker_id\":10}', '127.0.0.1', '2026-09-03 23:14:32'),
(40, NULL, 'PROJECT_PROGRESS_UPDATED', 'App\\Models\\Project', 2, NULL, '{\"progress\":45}', '127.0.0.1', '2026-09-03 23:14:32'),
(41, NULL, 'PROJECT_CREATED', 'App\\Models\\Project', 3, NULL, '{\"application_id\":5,\"project_number\":\"PRJ\\/CSR\\/2026\\/0003\",\"approved_amount\":\"25000.00\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(42, NULL, 'BUDGET_COMMITMENT_CREATED', 'App\\Models\\BudgetTransaction', 10, NULL, '{\"application_id\":5,\"application_number\":\"ALP\\/CSR\\/2026\\/0004\",\"amount\":\"25000.00\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(43, NULL, 'PROJECT_STARTED', 'App\\Models\\Project', 3, NULL, '{\"project_number\":\"PRJ\\/CSR\\/2026\\/0003\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(44, NULL, 'EXPENSE_CREATED', 'App\\Models\\ProjectExpense', 4, NULL, '{\"amount\":\"20000.00\",\"project_id\":3}', '127.0.0.1', '2026-09-03 23:14:32'),
(45, NULL, 'EXPENSE_SUBMITTED', 'App\\Models\\ProjectExpense', 4, NULL, '{\"amount\":\"20000.00\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(46, NULL, 'BUDGET_EXPENDITURE_CREATED', 'App\\Models\\BudgetTransaction', 11, NULL, '{\"project_id\":3,\"project_expense_id\":4,\"amount\":\"20000.00\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(47, NULL, 'EXPENSE_VERIFIED', 'App\\Models\\ProjectExpense', 4, NULL, '{\"amount\":\"20000.00\",\"checker_id\":10}', '127.0.0.1', '2026-09-03 23:14:32'),
(48, NULL, 'PROJECT_PROGRESS_UPDATED', 'App\\Models\\Project', 3, NULL, '{\"progress\":100}', '127.0.0.1', '2026-09-03 23:14:32'),
(49, NULL, 'PROJECT_COMPLETED', 'App\\Models\\Project', 3, NULL, '{\"project_number\":\"PRJ\\/CSR\\/2026\\/0003\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(50, NULL, 'PROJECT_REPORT_SUBMITTED', 'App\\Models\\Project', 3, NULL, '{\"report_id\":1}', '127.0.0.1', '2026-09-03 23:14:32'),
(51, NULL, 'PROJECT_CREATED', 'App\\Models\\Project', 4, NULL, '{\"application_id\":6,\"project_number\":\"PRJ\\/DEV\\/2026\\/0001\",\"approved_amount\":\"50000.00\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(52, NULL, 'BUDGET_COMMITMENT_CREATED', 'App\\Models\\BudgetTransaction', 12, NULL, '{\"application_id\":6,\"application_number\":\"ALP\\/DEV\\/2026\\/0002\",\"amount\":\"50000.00\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(53, NULL, 'PROJECT_STARTED', 'App\\Models\\Project', 4, NULL, '{\"project_number\":\"PRJ\\/DEV\\/2026\\/0001\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(54, NULL, 'EXPENSE_CREATED', 'App\\Models\\ProjectExpense', 5, NULL, '{\"amount\":\"46000.00\",\"project_id\":4}', '127.0.0.1', '2026-09-03 23:14:32'),
(55, NULL, 'EXPENSE_SUBMITTED', 'App\\Models\\ProjectExpense', 5, NULL, '{\"amount\":\"46000.00\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(56, NULL, 'BUDGET_EXPENDITURE_CREATED', 'App\\Models\\BudgetTransaction', 13, NULL, '{\"project_id\":4,\"project_expense_id\":5,\"amount\":\"46000.00\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(57, NULL, 'EXPENSE_VERIFIED', 'App\\Models\\ProjectExpense', 5, NULL, '{\"amount\":\"46000.00\",\"checker_id\":10}', '127.0.0.1', '2026-09-03 23:14:32'),
(58, NULL, 'PROJECT_PROGRESS_UPDATED', 'App\\Models\\Project', 4, NULL, '{\"progress\":100}', '127.0.0.1', '2026-09-03 23:14:32'),
(59, NULL, 'PROJECT_COMPLETED', 'App\\Models\\Project', 4, NULL, '{\"project_number\":\"PRJ\\/DEV\\/2026\\/0001\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(60, NULL, 'PROJECT_REPORT_SUBMITTED', 'App\\Models\\Project', 4, NULL, '{\"report_id\":2}', '127.0.0.1', '2026-09-03 23:14:32'),
(61, NULL, 'COMMITMENT_RELEASE_CREATED', 'App\\Models\\BudgetTransaction', 14, NULL, '{\"project_id\":4,\"amount\":\"4000.00\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(62, NULL, 'PROJECT_CLOSED', 'App\\Models\\Project', 4, NULL, '{\"project_number\":\"PRJ\\/DEV\\/2026\\/0001\",\"released\":\"4000.00\"}', '127.0.0.1', '2026-09-03 23:14:32'),
(63, NULL, 'SECRETARIAT_REVIEW_COMPLETED', 'App\\Models\\Application', 2, NULL, '{\"decision\":\"recommend\",\"revision_number\":0}', '127.0.0.1', '2026-09-03 23:30:48'),
(64, NULL, 'FINANCE_REVIEW_COMPLETED', 'App\\Models\\Application', 2, NULL, '{\"decision\":\"recommend\",\"revision_number\":0}', '127.0.0.1', '2026-09-03 23:30:48'),
(65, NULL, 'SECRETARIAT_REVIEW_COMPLETED', 'App\\Models\\Application', 8, NULL, '{\"decision\":\"recommend\",\"revision_number\":0}', '127.0.0.1', '2026-09-03 23:30:48'),
(66, NULL, 'SECRETARIAT_REVIEW_COMPLETED', 'App\\Models\\Application', 9, NULL, '{\"decision\":\"recommend\",\"revision_number\":0}', '127.0.0.1', '2026-09-03 23:30:48'),
(67, NULL, 'FINANCE_REVIEW_COMPLETED', 'App\\Models\\Application', 9, NULL, '{\"decision\":\"recommend\",\"revision_number\":0}', '127.0.0.1', '2026-09-03 23:30:48'),
(68, 3, 'APPLICATION_CREATED', 'App\\Models\\Application', 10, NULL, '{\"application_number\":\"ALP\\/DEV\\/2026\\/0004\",\"type\":\"development\"}', '::1', '2026-09-05 04:34:40'),
(69, 3, 'APPLICATION_CREATED', 'App\\Models\\Application', 11, NULL, '{\"application_number\":\"ALP\\/SUM\\/2026\\/0001\",\"amount\":\"5000.00\"}', '::1', '2026-09-05 04:55:23'),
(70, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 11, NULL, '{\"document_type\":\"borang_eft\",\"original_filename\":\"senarai_kuantiti (1).pdf\",\"file_size\":11191}', '::1', '2026-09-05 04:55:57'),
(71, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 11, NULL, '{\"document_type\":\"penyata_bank\",\"original_filename\":\"senarai_kuantiti.pdf\",\"file_size\":11191}', '::1', '2026-09-05 04:56:12'),
(72, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 11, NULL, '{\"document_type\":\"kertas_kerja\",\"original_filename\":\"cadangan_teknikal_harga.pdf\",\"file_size\":79472}', '::1', '2026-09-05 04:56:30'),
(73, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 11, NULL, '{\"document_type\":\"pendaftaran_pertubuhan\",\"original_filename\":\"cadangan_teknikal_harga.pdf\",\"file_size\":79472}', '::1', '2026-09-05 04:56:54'),
(74, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 11, NULL, '{\"document_type\":\"sijil_ros\",\"original_filename\":\"cadangan_teknikal_harga.pdf\",\"file_size\":79472}', '::1', '2026-09-05 04:58:16'),
(75, 3, 'APPLICATION_CREATED', 'App\\Models\\Application', 12, NULL, '{\"application_number\":\"ALP\\/SUM\\/2026\\/0002\",\"amount\":\"3000.00\"}', '::1', '2026-09-05 05:07:19'),
(76, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 12, NULL, '{\"document_type\":\"pendaftaran_pertubuhan\",\"original_filename\":\"cadangan_teknikal_harga (1).pdf\",\"file_size\":79472}', '::1', '2026-09-05 05:09:13'),
(77, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 12, NULL, '{\"document_type\":\"borang_eft\",\"original_filename\":\"cadangan_teknikal_harga (1).pdf\",\"file_size\":79472}', '::1', '2026-09-05 05:09:19'),
(78, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 12, NULL, '{\"document_type\":\"penyata_bank\",\"original_filename\":\"cadangan_teknikal_harga (1).pdf\",\"file_size\":79472}', '::1', '2026-09-05 05:09:26'),
(79, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 12, NULL, '{\"document_type\":\"kertas_kerja\",\"original_filename\":\"cadangan_teknikal_harga (1).pdf\",\"file_size\":79472}', '::1', '2026-09-05 05:09:49'),
(80, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 12, NULL, '{\"document_type\":\"sijil_ros\",\"original_filename\":\"cadangan_teknikal_harga (1).pdf\",\"file_size\":79472}', '::1', '2026-09-05 05:09:55'),
(81, 3, 'APPLICATION_SUBMITTED', 'App\\Models\\Application', 12, NULL, '{\"requested_amount\":\"3000.00\",\"financial_year\":2026,\"application_number\":\"ALP\\/SUM\\/2026\\/0002\"}', '::1', '2026-09-05 05:10:36'),
(82, 3, 'APPLICATION_CREATED', 'App\\Models\\Application', 13, NULL, '{\"application_number\":\"ALP\\/SUM\\/2026\\/0003\",\"amount\":\"2000.00\"}', '::1', '2026-09-05 05:52:26'),
(83, 3, 'APPLICATION_CREATED', 'App\\Models\\Application', 14, NULL, '{\"application_number\":\"ALP\\/SUM\\/2026\\/0004\",\"amount\":\"2000.00\"}', '::1', '2026-09-05 05:52:53'),
(84, 3, 'APPLICATION_CREATED', 'App\\Models\\Application', 15, NULL, '{\"application_number\":\"ALP\\/SUM\\/2026\\/0005\",\"amount\":\"2334.00\"}', '::1', '2026-09-05 05:53:21'),
(85, 3, 'APPLICATION_CREATED', 'App\\Models\\Application', 16, NULL, '{\"application_number\":\"ALP\\/SUM\\/2026\\/0006\",\"amount\":\"3000.00\"}', '::1', '2026-09-05 05:55:19'),
(86, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 16, NULL, '{\"document_type\":\"pendaftaran_pertubuhan\",\"original_filename\":\"cadangan_teknikal_harga (1).pdf\",\"file_size\":79472}', '::1', '2026-09-05 05:55:26'),
(87, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 16, NULL, '{\"document_type\":\"borang_eft\",\"original_filename\":\"cadangan_teknikal_harga (1).pdf\",\"file_size\":79472}', '::1', '2026-09-05 05:55:31'),
(88, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 16, NULL, '{\"document_type\":\"penyata_bank\",\"original_filename\":\"cadangan_teknikal_harga (1).pdf\",\"file_size\":79472}', '::1', '2026-09-05 05:55:37'),
(89, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 16, NULL, '{\"document_type\":\"kertas_kerja\",\"original_filename\":\"cadangan_teknikal_harga (1).pdf\",\"file_size\":79472}', '::1', '2026-09-05 05:55:43'),
(90, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 16, NULL, '{\"document_type\":\"sijil_ros\",\"original_filename\":\"cadangan_teknikal_harga (1).pdf\",\"file_size\":79472}', '::1', '2026-09-05 05:55:56'),
(91, 3, 'APPLICATION_UPDATED', 'App\\Models\\Application', 16, NULL, '{\"step\":\"borang\"}', '::1', '2026-09-05 06:07:42'),
(92, 2, 'ALLOCATE', 'App\\Models\\Allocation', 4, NULL, '{\"amount\":\"20000.00\",\"reference_no\":\"URS\\/2026\\/ALP-04\",\"transaction_id\":18,\"budget_request_id\":null}', '127.0.0.1', '2026-09-05 06:18:27'),
(93, 2, 'ALLOCATE', 'App\\Models\\Allocation', 5, NULL, '{\"amount\":\"30000.00\",\"reference_no\":\"URS\\/2026\\/ALP-05\",\"transaction_id\":19,\"budget_request_id\":null}', '127.0.0.1', '2026-09-05 06:18:27'),
(94, 2, 'ALLOCATE', 'App\\Models\\Allocation', 6, NULL, '{\"amount\":\"30000.00\",\"reference_no\":\"URS\\/2026\\/ALP-06\",\"transaction_id\":20,\"budget_request_id\":null}', '127.0.0.1', '2026-09-05 06:18:27'),
(95, 2, 'ALLOCATE', 'App\\Models\\Allocation', 7, NULL, '{\"amount\":\"30000.00\",\"reference_no\":\"URS\\/2026\\/ALP-07\",\"transaction_id\":21,\"budget_request_id\":null}', '127.0.0.1', '2026-09-05 06:18:27'),
(96, 2, 'ALLOCATE', 'App\\Models\\Allocation', 8, NULL, '{\"amount\":\"30000.00\",\"reference_no\":\"URS\\/2026\\/ALP-08\",\"transaction_id\":22,\"budget_request_id\":null}', '127.0.0.1', '2026-09-05 06:18:27'),
(97, 2, 'ALLOCATE', 'App\\Models\\Allocation', 9, NULL, '{\"amount\":\"30000.00\",\"reference_no\":\"URS\\/2026\\/ALP-09\",\"transaction_id\":23,\"budget_request_id\":null}', '127.0.0.1', '2026-09-05 06:18:27'),
(98, 2, 'ALLOCATE', 'App\\Models\\Allocation', 10, NULL, '{\"amount\":\"30000.00\",\"reference_no\":\"URS\\/2026\\/ALP-10\",\"transaction_id\":24,\"budget_request_id\":null}', '127.0.0.1', '2026-09-05 06:18:27'),
(99, 2, 'ALLOCATE', 'App\\Models\\Allocation', 11, NULL, '{\"amount\":\"30000.00\",\"reference_no\":\"URS\\/2026\\/ALP-11\",\"transaction_id\":25,\"budget_request_id\":null}', '127.0.0.1', '2026-09-05 06:18:27'),
(100, 2, 'ALLOCATE', 'App\\Models\\Allocation', 12, NULL, '{\"amount\":\"30000.00\",\"reference_no\":\"URS\\/2026\\/ALP-12\",\"transaction_id\":26,\"budget_request_id\":null}', '127.0.0.1', '2026-09-05 06:18:27'),
(101, 2, 'ALLOCATE', 'App\\Models\\Allocation', 13, NULL, '{\"amount\":\"30000.00\",\"reference_no\":\"URS\\/2026\\/ALP-13\",\"transaction_id\":27,\"budget_request_id\":null}', '127.0.0.1', '2026-09-05 06:18:27'),
(102, 2, 'ALLOCATE', 'App\\Models\\Allocation', 14, NULL, '{\"amount\":\"30000.00\",\"reference_no\":\"URS\\/2026\\/ALP-14\",\"transaction_id\":28,\"budget_request_id\":null}', '127.0.0.1', '2026-09-05 06:18:27'),
(103, 2, 'ALLOCATE', 'App\\Models\\Allocation', 15, NULL, '{\"amount\":\"20000.00\",\"reference_no\":\"URS\\/2026\\/ALP-15\",\"transaction_id\":29,\"budget_request_id\":null}', '127.0.0.1', '2026-09-05 06:18:27'),
(104, 3, 'APPLICATION_CREATED', 'App\\Models\\Application', 17, NULL, '{\"application_number\":\"ALP\\/SUM\\/2026\\/0007\",\"amount\":\"3000.00\"}', '::1', '2026-09-05 06:41:54'),
(105, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 17, NULL, '{\"document_type\":\"pendaftaran_pertubuhan\",\"original_filename\":\"cadangan_teknikal_harga (1).pdf\",\"file_size\":79472}', '::1', '2026-09-05 06:42:00'),
(106, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 17, NULL, '{\"document_type\":\"borang_eft\",\"original_filename\":\"cadangan_teknikal_harga (1).pdf\",\"file_size\":79472}', '::1', '2026-09-05 06:42:05'),
(107, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 17, NULL, '{\"document_type\":\"penyata_bank\",\"original_filename\":\"cadangan_teknikal_harga (1).pdf\",\"file_size\":79472}', '::1', '2026-09-05 06:42:10'),
(108, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 17, NULL, '{\"document_type\":\"kertas_kerja\",\"original_filename\":\"cadangan_teknikal_harga (1).pdf\",\"file_size\":79472}', '::1', '2026-09-05 06:42:16'),
(109, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 17, NULL, '{\"document_type\":\"sijil_ros\",\"original_filename\":\"cadangan_teknikal_harga (1).pdf\",\"file_size\":79472}', '::1', '2026-09-05 06:42:20'),
(110, 3, 'APPLICATION_SUBMITTED', 'App\\Models\\Application', 17, NULL, '{\"requested_amount\":\"3000.00\",\"financial_year\":2026,\"application_number\":\"ALP\\/SUM\\/2026\\/0007\"}', '::1', '2026-09-05 06:42:37'),
(111, 7, 'SECRETARIAT_REVIEW_COMPLETED', 'App\\Models\\Application', 17, NULL, '{\"decision\":\"recommend\",\"revision_number\":0}', '::1', '2026-09-06 04:43:00'),
(112, 10, 'APPROVAL_LEVEL_APPROVED', 'App\\Models\\Application', 17, NULL, '{\"level\":\"Aras 1 \\u2014 Pegawai\",\"sequence\":1,\"is_final\":true}', '::1', '2026-09-06 04:54:46'),
(113, 10, 'BUDGET_COMMITMENT_CREATED', 'App\\Models\\BudgetTransaction', 30, NULL, '{\"application_id\":17,\"application_number\":\"ALP\\/SUM\\/2026\\/0007\",\"amount\":\"3000.00\"}', '::1', '2026-09-06 04:54:46'),
(114, 10, 'APPLICATION_APPROVED', 'App\\Models\\Application', 17, NULL, '{\"amount\":\"3000.00\",\"application_number\":\"ALP\\/SUM\\/2026\\/0007\"}', '::1', '2026-09-06 04:54:46'),
(115, 11, 'APPROVAL_LEVEL_APPROVED', 'App\\Models\\Application', 17, NULL, '{\"level\":\"Kelulusan PEPU \\/ Pengurusan Tertinggi\",\"sequence\":2,\"is_final\":true}', '::1', '2026-09-06 05:13:48'),
(116, 11, 'BUDGET_COMMITMENT_CREATED', 'App\\Models\\BudgetTransaction', 31, NULL, '{\"application_id\":17,\"application_number\":\"ALP\\/SUM\\/2026\\/0007\",\"amount\":\"3000.00\"}', '::1', '2026-09-06 05:13:48'),
(117, 11, 'APPLICATION_APPROVED', 'App\\Models\\Application', 17, NULL, '{\"amount\":\"3000.00\",\"application_number\":\"ALP\\/SUM\\/2026\\/0007\"}', '::1', '2026-09-06 05:13:48'),
(118, 3, 'APPLICATION_CREATED', 'App\\Models\\Application', 18, NULL, '{\"application_number\":\"ALP\\/SUM\\/2026\\/0008\",\"amount\":\"3000.00\"}', '::1', '2026-09-07 01:09:18'),
(119, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 18, NULL, '{\"document_type\":\"pendaftaran_pertubuhan\",\"original_filename\":\"Panduan-Dokumen-Persatuan-ALP-DBKL (1).pdf\",\"file_size\":1368965}', '::1', '2026-09-07 01:09:57'),
(120, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 18, NULL, '{\"document_type\":\"borang_eft\",\"original_filename\":\"Panduan-Dokumen-Persatuan-ALP-DBKL (1).pdf\",\"file_size\":1368965}', '::1', '2026-09-07 01:10:02'),
(121, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 18, NULL, '{\"document_type\":\"penyata_bank\",\"original_filename\":\"Panduan-Dokumen-Persatuan-ALP-DBKL (1).pdf\",\"file_size\":1368965}', '::1', '2026-09-07 01:10:16'),
(122, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 18, NULL, '{\"document_type\":\"kertas_kerja\",\"original_filename\":\"Panduan-Dokumen-Persatuan-ALP-DBKL (1).pdf\",\"file_size\":1368965}', '::1', '2026-09-07 01:10:22'),
(123, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 18, NULL, '{\"document_type\":\"sijil_ros\",\"original_filename\":\"Panduan-Dokumen-Persatuan-ALP-DBKL (1).pdf\",\"file_size\":1368965}', '::1', '2026-09-07 01:10:29'),
(124, 3, 'APPLICATION_UPDATED', 'App\\Models\\Application', 18, NULL, '{\"step\":\"borang\"}', '::1', '2026-09-07 01:11:56'),
(125, 3, 'APPLICATION_SUBMITTED', 'App\\Models\\Application', 18, NULL, '{\"requested_amount\":\"3000.00\",\"financial_year\":2026,\"application_number\":\"ALP\\/SUM\\/2026\\/0008\"}', '::1', '2026-09-07 01:12:32'),
(126, 7, 'SECRETARIAT_REVIEW_COMPLETED', 'App\\Models\\Application', 18, NULL, '{\"decision\":\"recommend\",\"revision_number\":0}', '::1', '2026-09-07 01:42:47'),
(127, 10, 'APPROVAL_LEVEL_APPROVED', 'App\\Models\\Application', 18, NULL, '{\"level\":\"Peraku untuk diangkat ke PEPU (TP\\/Pengarah JP)\",\"sequence\":1,\"is_final\":false}', '::1', '2026-09-07 01:44:58'),
(128, 10, 'APPLICATION_RETURNED_FOR_REVISION', 'App\\Models\\Application', 18, NULL, '{\"stage\":\"approval\",\"reason\":\"PERSATUAN PENAH MOHON\",\"revision_number\":0}', '::1', '2026-09-07 01:47:05'),
(129, 3, 'APPLICATION_UPDATED', 'App\\Models\\Application', 18, NULL, '{\"step\":\"borang\"}', '::1', '2026-09-07 01:51:49'),
(130, 3, 'APPLICATION_RESUBMITTED', 'App\\Models\\Application', 18, NULL, '{\"requested_amount\":\"3000.00\",\"revision_number\":1,\"application_number\":\"ALP\\/SUM\\/2026\\/0008\"}', '::1', '2026-09-07 01:51:58'),
(131, 7, 'SECRETARIAT_REVIEW_COMPLETED', 'App\\Models\\Application', 18, NULL, '{\"decision\":\"recommend\",\"revision_number\":1}', '::1', '2026-09-07 01:52:45'),
(132, 10, 'APPROVAL_LEVEL_APPROVED', 'App\\Models\\Application', 18, NULL, '{\"level\":\"Peraku untuk diangkat ke PEPU (TP\\/Pengarah JP)\",\"sequence\":1,\"is_final\":false}', '::1', '2026-09-07 01:53:13'),
(133, 11, 'APPROVAL_LEVEL_APPROVED', 'App\\Models\\Application', 18, NULL, '{\"level\":\"Kelulusan PEPU \\/ Pengurusan Tertinggi\",\"sequence\":2,\"is_final\":true}', '::1', '2026-09-07 01:56:20'),
(134, 11, 'BUDGET_COMMITMENT_CREATED', 'App\\Models\\BudgetTransaction', 32, NULL, '{\"application_id\":18,\"application_number\":\"ALP\\/SUM\\/2026\\/0008\",\"amount\":\"3000.00\"}', '::1', '2026-09-07 01:56:20'),
(135, 11, 'APPLICATION_APPROVED', 'App\\Models\\Application', 18, NULL, '{\"amount\":\"3000.00\",\"application_number\":\"ALP\\/SUM\\/2026\\/0008\"}', '::1', '2026-09-07 01:56:20'),
(136, 3, 'APPLICATION_CREATED', 'App\\Models\\Application', 19, NULL, '{\"application_number\":\"ALP\\/SUM\\/2026\\/0009\",\"amount\":\"1000.00\"}', '::1', '2026-09-07 06:10:47'),
(137, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 19, NULL, '{\"document_type\":\"pendaftaran_pertubuhan\",\"original_filename\":\"Panduan-Dokumen-Persatuan-ALP-DBKL (1) (1).pdf\",\"file_size\":1368965}', '::1', '2026-09-07 06:11:33'),
(138, 3, 'APPLICATION_UPDATED', 'App\\Models\\Application', 19, NULL, '{\"step\":\"borang\"}', '::1', '2026-09-07 06:12:14'),
(139, NULL, 'APPLICATION_SUBMITTED', 'App\\Models\\Application', 25, NULL, '{\"requested_amount\":\"2000.00\",\"financial_year\":2026,\"application_number\":\"ALP\\/SUM\\/2026\\/0015\"}', '127.0.0.1', '2026-09-07 06:21:36'),
(140, NULL, 'APPLICATION_SUBMITTED', 'App\\Models\\Application', 30, NULL, '{\"requested_amount\":\"300.00\",\"financial_year\":2026,\"application_number\":\"ALP\\/SUM\\/2026\\/0020\"}', '127.0.0.1', '2026-09-07 06:22:23'),
(141, NULL, 'APPLICATION_SUBMITTED', 'App\\Models\\Application', 31, NULL, '{\"requested_amount\":\"300.00\",\"financial_year\":2026,\"application_number\":\"ALP\\/SUM\\/2026\\/0021\"}', '127.0.0.1', '2026-09-07 06:22:23'),
(142, NULL, 'SECRETARIAT_REVIEW_COMPLETED', 'App\\Models\\Application', 31, NULL, '{\"decision\":\"recommend\",\"revision_number\":0}', '127.0.0.1', '2026-09-07 06:22:23'),
(143, NULL, 'APPLICATION_SUBMITTED', 'App\\Models\\Application', 32, NULL, '{\"requested_amount\":\"350.00\",\"financial_year\":2026,\"application_number\":\"ALP\\/SUM\\/2026\\/0022\"}', '127.0.0.1', '2026-09-07 06:22:23'),
(144, NULL, 'SECRETARIAT_REVIEW_COMPLETED', 'App\\Models\\Application', 32, NULL, '{\"decision\":\"recommend\",\"revision_number\":0}', '127.0.0.1', '2026-09-07 06:22:23'),
(145, NULL, 'APPROVAL_LEVEL_APPROVED', 'App\\Models\\Application', 32, NULL, '{\"level\":\"Peraku untuk diangkat ke PEPU (TP\\/Pengarah JP)\",\"sequence\":1,\"is_final\":false}', '127.0.0.1', '2026-09-07 06:22:23'),
(146, NULL, 'APPLICATION_SUBMITTED', 'App\\Models\\Application', 33, NULL, '{\"requested_amount\":\"350.00\",\"financial_year\":2026,\"application_number\":\"ALP\\/SUM\\/2026\\/0023\"}', '127.0.0.1', '2026-09-07 06:22:23'),
(147, NULL, 'SECRETARIAT_REVIEW_COMPLETED', 'App\\Models\\Application', 33, NULL, '{\"decision\":\"recommend\",\"revision_number\":0}', '127.0.0.1', '2026-09-07 06:22:23'),
(148, NULL, 'APPROVAL_LEVEL_APPROVED', 'App\\Models\\Application', 33, NULL, '{\"level\":\"Peraku untuk diangkat ke PEPU (TP\\/Pengarah JP)\",\"sequence\":1,\"is_final\":false}', '127.0.0.1', '2026-09-07 06:22:23'),
(149, NULL, 'APPROVAL_LEVEL_APPROVED', 'App\\Models\\Application', 33, NULL, '{\"level\":\"Kelulusan PEPU \\/ Pengurusan Tertinggi\",\"sequence\":2,\"is_final\":true}', '127.0.0.1', '2026-09-07 06:22:23'),
(150, NULL, 'BUDGET_COMMITMENT_CREATED', 'App\\Models\\BudgetTransaction', 33, NULL, '{\"application_id\":33,\"application_number\":\"ALP\\/SUM\\/2026\\/0023\",\"amount\":\"350.00\"}', '127.0.0.1', '2026-09-07 06:22:23'),
(151, NULL, 'APPLICATION_APPROVED', 'App\\Models\\Application', 33, NULL, '{\"amount\":\"350.00\",\"application_number\":\"ALP\\/SUM\\/2026\\/0023\"}', '127.0.0.1', '2026-09-07 06:22:23'),
(152, NULL, 'APPLICATION_SUBMITTED', 'App\\Models\\Application', 34, NULL, '{\"requested_amount\":\"400.00\",\"financial_year\":2026,\"application_number\":\"ALP\\/SUM\\/2026\\/0024\"}', '127.0.0.1', '2026-09-07 06:22:23'),
(153, NULL, 'SECRETARIAT_REVIEW_COMPLETED', 'App\\Models\\Application', 34, NULL, '{\"decision\":\"recommend\",\"revision_number\":0}', '127.0.0.1', '2026-09-07 06:22:23'),
(154, NULL, 'APPROVAL_LEVEL_APPROVED', 'App\\Models\\Application', 34, NULL, '{\"level\":\"Peraku untuk diangkat ke PEPU (TP\\/Pengarah JP)\",\"sequence\":1,\"is_final\":false}', '127.0.0.1', '2026-09-07 06:22:23'),
(155, NULL, 'APPROVAL_LEVEL_APPROVED', 'App\\Models\\Application', 34, NULL, '{\"level\":\"Kelulusan PEPU \\/ Pengurusan Tertinggi\",\"sequence\":2,\"is_final\":true}', '127.0.0.1', '2026-09-07 06:22:23'),
(156, NULL, 'BUDGET_COMMITMENT_CREATED', 'App\\Models\\BudgetTransaction', 34, NULL, '{\"application_id\":34,\"application_number\":\"ALP\\/SUM\\/2026\\/0024\",\"amount\":\"400.00\"}', '127.0.0.1', '2026-09-07 06:22:23'),
(157, NULL, 'APPLICATION_APPROVED', 'App\\Models\\Application', 34, NULL, '{\"amount\":\"400.00\",\"application_number\":\"ALP\\/SUM\\/2026\\/0024\"}', '127.0.0.1', '2026-09-07 06:22:23'),
(158, NULL, 'APPLICATION_SUBMITTED', 'App\\Models\\Application', 35, NULL, '{\"requested_amount\":\"300.00\",\"financial_year\":2026,\"application_number\":\"ALP\\/SUM\\/2026\\/0025\"}', '127.0.0.1', '2026-09-07 06:22:23'),
(159, NULL, 'SECRETARIAT_REVIEW_COMPLETED', 'App\\Models\\Application', 35, NULL, '{\"decision\":\"recommend\",\"revision_number\":0}', '127.0.0.1', '2026-09-07 06:22:23'),
(160, NULL, 'APPLICATION_REJECTED', 'App\\Models\\Application', 35, NULL, '{\"reason\":\"Ditolak (simulasi)\"}', '127.0.0.1', '2026-09-07 06:22:23'),
(161, 3, 'APPLICATION_CREATED', 'App\\Models\\Application', 36, NULL, '{\"application_number\":\"ALP\\/SUM\\/2026\\/0026\",\"amount\":\"100.00\"}', '::1', '2026-09-07 06:27:53'),
(162, 3, 'APPLICATION_UPDATED', 'App\\Models\\Application', 27, NULL, '{\"step\":\"borang\"}', '::1', '2026-09-07 06:36:19'),
(163, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 27, NULL, '{\"document_type\":\"pendaftaran_pertubuhan\",\"original_filename\":\"Panduan-Dokumen-Persatuan-ALP-DBKL (1) (1).pdf\",\"file_size\":1368965}', '::1', '2026-09-07 06:36:28'),
(164, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 27, NULL, '{\"document_type\":\"borang_eft\",\"original_filename\":\"Panduan-Dokumen-Persatuan-ALP-DBKL (1) (2).pdf\",\"file_size\":1368965}', '::1', '2026-09-07 06:36:36'),
(165, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 27, NULL, '{\"document_type\":\"penyata_bank\",\"original_filename\":\"Panduan-Dokumen-Persatuan-ALP-DBKL (1) (1).pdf\",\"file_size\":1368965}', '::1', '2026-09-07 06:36:54'),
(166, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 27, NULL, '{\"document_type\":\"kertas_kerja\",\"original_filename\":\"Panduan-Dokumen-Persatuan-ALP-DBKL (1) (1).pdf\",\"file_size\":1368965}', '::1', '2026-09-07 06:37:04'),
(167, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 27, NULL, '{\"document_type\":\"sijil_ros\",\"original_filename\":\"Panduan-Dokumen-Persatuan-ALP-DBKL (1) (1).pdf\",\"file_size\":1368965}', '::1', '2026-09-07 06:37:11'),
(168, 3, 'APPLICATION_SUBMITTED', 'App\\Models\\Application', 27, NULL, '{\"requested_amount\":\"1200.00\",\"financial_year\":2026,\"application_number\":\"ALP\\/SUM\\/2026\\/0017\"}', '::1', '2026-09-07 06:39:32'),
(169, 2, 'APPLICATION_RETURNED_FOR_REVISION', 'App\\Models\\Application', 27, NULL, '{\"stage\":\"secretariat\",\"reason\":\"buatla betul betul\",\"revision_number\":0}', '::1', '2026-09-07 18:59:07'),
(170, 2, 'APPLICATION_RETURNED_FOR_REVISION', 'App\\Models\\Application', 30, NULL, '{\"stage\":\"secretariat\",\"reason\":\"betulkan apa yg salah\",\"revision_number\":0}', '::1', '2026-09-07 19:16:07'),
(171, 2, 'APPLICATION_CREATED', 'App\\Models\\Application', 37, NULL, '{\"application_number\":\"ALP\\/SUM\\/2026\\/0027\",\"amount\":\"1000.00\",\"on_behalf\":true}', '::1', '2026-09-07 19:38:50'),
(172, 2, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 37, NULL, '{\"document_type\":\"pendaftaran_pertubuhan\",\"original_filename\":\"PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf\",\"file_size\":71194}', '::1', '2026-09-07 19:39:04'),
(173, 2, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 37, NULL, '{\"document_type\":\"borang_eft\",\"original_filename\":\"PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf\",\"file_size\":71194}', '::1', '2026-09-07 19:39:13'),
(174, 2, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 37, NULL, '{\"document_type\":\"penyata_bank\",\"original_filename\":\"PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf\",\"file_size\":71194}', '::1', '2026-09-07 19:39:21'),
(175, 2, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 37, NULL, '{\"document_type\":\"kertas_kerja\",\"original_filename\":\"PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf\",\"file_size\":71194}', '::1', '2026-09-07 19:39:28'),
(176, 2, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 37, NULL, '{\"document_type\":\"sijil_ros\",\"original_filename\":\"PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf\",\"file_size\":71194}', '::1', '2026-09-07 19:39:37'),
(177, 2, 'APPLICATION_SUBMITTED', 'App\\Models\\Application', 37, NULL, '{\"requested_amount\":\"1000.00\",\"financial_year\":2026,\"application_number\":\"ALP\\/SUM\\/2026\\/0027\"}', '::1', '2026-09-07 19:39:47'),
(178, 8, 'APPLICATION_PAYMENT_UPDATED', 'App\\Models\\Application', 34, NULL, '{\"from\":\"voucher_prepared\",\"to\":\"voucher_prepared\",\"voucher_no\":\"BV\\/SIM\\/2026\\/001\",\"supplier_no\":\"14587463\",\"voucher_date\":\"2026-09-08\",\"reference\":null,\"sent_to_jkew_at\":null,\"jkew_crosscheck\":null}', '::1', '2026-09-07 20:01:26'),
(179, 3, 'APPLICATION_CREATED', 'App\\Models\\Application', 38, NULL, '{\"application_number\":\"ALP\\/SUM\\/2026\\/0028\",\"amount\":\"100.00\",\"on_behalf\":false}', '::1', '2026-09-07 20:32:32'),
(180, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 38, NULL, '{\"document_type\":\"pendaftaran_pertubuhan\",\"original_filename\":\"PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf\",\"file_size\":71194}', '::1', '2026-09-07 20:32:52'),
(181, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 38, NULL, '{\"document_type\":\"penyata_bank\",\"original_filename\":\"PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf\",\"file_size\":71194}', '::1', '2026-09-07 20:33:03'),
(182, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 38, NULL, '{\"document_type\":\"sijil_ros\",\"original_filename\":\"PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf\",\"file_size\":71194}', '::1', '2026-09-07 20:33:17'),
(183, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 38, NULL, '{\"document_type\":\"borang_eft\",\"original_filename\":\"PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf\",\"file_size\":71194}', '::1', '2026-09-07 20:33:28'),
(184, 3, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 38, NULL, '{\"document_type\":\"kertas_kerja\",\"original_filename\":\"PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf\",\"file_size\":71194}', '::1', '2026-09-07 20:33:36'),
(185, 3, 'APPLICATION_SUBMITTED', 'App\\Models\\Application', 38, NULL, '{\"requested_amount\":\"100.00\",\"financial_year\":2026,\"application_number\":\"ALP\\/SUM\\/2026\\/0028\"}', '::1', '2026-09-07 20:34:00'),
(186, 2, 'SECRETARIAT_REVIEW_COMPLETED', 'App\\Models\\Application', 38, NULL, '{\"decision\":\"recommend\",\"revision_number\":0}', '::1', '2026-09-07 20:36:05'),
(187, 7, 'SECRETARIAT_REVIEW_COMPLETED', 'App\\Models\\Application', 38, NULL, '{\"decision\":\"recommend\",\"revision_number\":0,\"actor\":\"pegawai_jp\"}', '::1', '2026-09-07 23:07:24'),
(188, 10, 'APPROVAL_LEVEL_APPROVED', 'App\\Models\\Application', 31, NULL, '{\"level\":\"Pengesyoran PEPU\",\"sequence\":1,\"is_final\":false}', '::1', '2026-09-07 23:11:24'),
(189, 10, 'APPROVAL_LEVEL_APPROVED', 'App\\Models\\Application', 38, NULL, '{\"level\":\"Pengesyoran PEPU\",\"sequence\":1,\"is_final\":false}', '::1', '2026-09-07 23:11:44'),
(190, 11, 'APPROVAL_LEVEL_APPROVED', 'App\\Models\\Application', 38, NULL, '{\"level\":\"Kelulusan PEPU \\/ Pengurusan Tertinggi\",\"sequence\":2,\"is_final\":true}', '::1', '2026-09-07 23:12:22'),
(191, 11, 'BUDGET_COMMITMENT_CREATED', 'App\\Models\\BudgetTransaction', 35, NULL, '{\"application_id\":38,\"application_number\":\"ALP\\/SUM\\/2026\\/0028\",\"amount\":\"100.00\"}', '::1', '2026-09-07 23:12:22'),
(192, 11, 'APPLICATION_APPROVED', 'App\\Models\\Application', 38, NULL, '{\"amount\":\"100.00\",\"application_number\":\"ALP\\/SUM\\/2026\\/0028\"}', '::1', '2026-09-07 23:12:22'),
(193, 8, 'APPLICATION_PAYMENT_UPDATED', 'App\\Models\\Application', 38, NULL, '{\"from\":\"pending_payment\",\"to\":\"voucher_prepared\",\"voucher_no\":\"c33333333\",\"supplier_no\":\"sssssssssssssss\",\"voucher_date\":\"2026-09-08\",\"reference\":null,\"sent_to_jkew_at\":null,\"jkew_crosscheck\":null}', '::1', '2026-09-07 23:13:59'),
(194, 4, 'APPLICATION_CREATED', 'App\\Models\\Application', 39, NULL, '{\"application_number\":\"ALP\\/SUM\\/2026\\/0029\",\"amount\":\"1000.00\",\"on_behalf\":false}', '::1', '2026-09-08 00:32:39'),
(195, 4, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 39, NULL, '{\"document_type\":\"pendaftaran_pertubuhan\",\"original_filename\":\"PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf\",\"file_size\":71194}', '::1', '2026-09-08 00:33:05'),
(196, 4, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 39, NULL, '{\"document_type\":\"penyata_bank\",\"original_filename\":\"PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf\",\"file_size\":71194}', '::1', '2026-09-08 00:33:15'),
(197, 4, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 39, NULL, '{\"document_type\":\"borang_eft\",\"original_filename\":\"PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf\",\"file_size\":71194}', '::1', '2026-09-08 00:33:23'),
(198, 4, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 39, NULL, '{\"document_type\":\"kertas_kerja\",\"original_filename\":\"PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf\",\"file_size\":71194}', '::1', '2026-09-08 00:33:31'),
(199, 4, 'APPLICATION_DOCUMENT_UPLOADED', 'App\\Models\\Application', 39, NULL, '{\"document_type\":\"sijil_ros\",\"original_filename\":\"PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf\",\"file_size\":71194}', '::1', '2026-09-08 00:33:41'),
(200, 4, 'APPLICATION_SUBMITTED', 'App\\Models\\Application', 39, NULL, '{\"requested_amount\":\"1000.00\",\"financial_year\":2026,\"application_number\":\"ALP\\/SUM\\/2026\\/0029\"}', '::1', '2026-09-08 00:34:49'),
(201, 2, 'SECRETARIAT_REVIEW_COMPLETED', 'App\\Models\\Application', 39, NULL, '{\"decision\":\"recommend\",\"revision_number\":0,\"actor\":\"admin_jp\"}', '::1', '2026-09-08 00:50:43'),
(202, 7, 'SECRETARIAT_REVIEW_COMPLETED', 'App\\Models\\Application', 39, NULL, '{\"decision\":\"recommend\",\"revision_number\":0,\"actor\":\"pegawai_jp\"}', '::1', '2026-09-08 00:51:30'),
(203, 10, 'APPROVAL_LEVEL_APPROVED', 'App\\Models\\Application', 39, NULL, '{\"level\":\"Pengesyoran PEPU\",\"sequence\":1,\"is_final\":false}', '::1', '2026-09-08 00:53:42'),
(204, 11, 'APPROVAL_LEVEL_APPROVED', 'App\\Models\\Application', 39, NULL, '{\"level\":\"Kelulusan PEPU \\/ Pengurusan Tertinggi\",\"sequence\":2,\"is_final\":true}', '::1', '2026-09-08 00:54:17'),
(205, 11, 'BUDGET_COMMITMENT_CREATED', 'App\\Models\\BudgetTransaction', 36, NULL, '{\"application_id\":39,\"application_number\":\"ALP\\/SUM\\/2026\\/0029\",\"amount\":\"1000.00\"}', '::1', '2026-09-08 00:54:17'),
(206, 11, 'APPLICATION_APPROVED', 'App\\Models\\Application', 39, NULL, '{\"amount\":\"1000.00\",\"application_number\":\"ALP\\/SUM\\/2026\\/0029\"}', '::1', '2026-09-08 00:54:17'),
(207, 8, 'APPLICATION_PAYMENT_UPDATED', 'App\\Models\\Application', 39, NULL, '{\"from\":\"pending_payment\",\"to\":\"voucher_prepared\",\"voucher_no\":\"012310\",\"supplier_no\":\"123456789\",\"voucher_date\":\"2026-09-08\",\"reference\":null,\"sent_to_jkew_at\":null,\"jkew_crosscheck\":null}', '::1', '2026-09-08 01:11:50'),
(208, 3, 'APPLICATION_REPORT_CARD_UPLOADED', 'App\\Models\\Application', 38, NULL, '{\"document_type\":\"laporan_aktiviti\",\"original_filename\":\"Panduan-Dokumen-Persatuan-ALP-DBKL.pdf\"}', '::1', '2026-09-08 05:09:32'),
(209, 4, 'APPLICATION_REPORT_CARD_UPLOADED', 'App\\Models\\Application', 39, NULL, '{\"document_type\":\"laporan_aktiviti\",\"original_filename\":\"Panduan-Dokumen-Persatuan-ALP-DBKL.pdf\"}', '::1', '2026-09-08 05:54:46'),
(210, 3, 'APPLICATION_REPORT_CARD_UPLOADED', 'App\\Models\\Application', 34, NULL, '{\"document_type\":\"laporan_aktiviti\",\"original_filename\":\"PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS.pdf\"}', '::1', '2026-09-08 06:14:38'),
(211, 2, 'REPORT_CARD_ADMIN_REVIEWED', 'App\\Models\\Application', 34, NULL, '{\"decision\":\"recommend\"}', '::1', '2026-09-08 06:15:44'),
(212, 7, 'REPORT_CARD_APPROVED', 'App\\Models\\Application', 34, NULL, '{\"decision\":\"recommend\"}', '::1', '2026-09-08 06:16:28'),
(213, 11, 'APPROVAL_LEVEL_APPROVED', 'App\\Models\\Application', 32, NULL, '{\"level\":\"Kelulusan PEPU \\/ Pengurusan Tertinggi\",\"sequence\":2,\"is_final\":true}', '::1', '2026-09-08 06:30:15'),
(214, 11, 'BUDGET_COMMITMENT_CREATED', 'App\\Models\\BudgetTransaction', 37, NULL, '{\"application_id\":32,\"application_number\":\"ALP\\/SUM\\/2026\\/0022\",\"amount\":\"350.00\"}', '::1', '2026-09-08 06:30:15'),
(215, 11, 'APPLICATION_APPROVED', 'App\\Models\\Application', 32, NULL, '{\"amount\":\"350.00\",\"application_number\":\"ALP\\/SUM\\/2026\\/0022\"}', '::1', '2026-09-08 06:30:15'),
(216, 8, 'APPLICATION_PAYMENT_UPDATED', 'App\\Models\\Application', 32, NULL, '{\"from\":\"pending_payment\",\"to\":\"voucher_prepared\",\"voucher_no\":\"12qqqqqqqqqqqqqqq\",\"supplier_no\":\"qqqqqqqqqqqqqq\",\"voucher_date\":\"2026-09-08\",\"reference\":null,\"sent_to_jkew_at\":null,\"jkew_crosscheck\":null}', '::1', '2026-09-08 06:31:10'),
(217, 3, 'APPLICATION_REPORT_CARD_UPLOADED', 'App\\Models\\Application', 32, NULL, '{\"document_type\":\"laporan_aktiviti\",\"original_filename\":\"PERSATUAN PENDUDUK TAMAN SENTUL_XLULUS (1).pdf\"}', '::1', '2026-09-08 06:32:25'),
(218, 2, 'REPORT_CARD_ADMIN_REVIEWED', 'App\\Models\\Application', 32, NULL, '{\"decision\":\"recommend\"}', '::1', '2026-09-08 06:33:26'),
(219, 7, 'REPORT_CARD_APPROVED', 'App\\Models\\Application', 32, NULL, '{\"decision\":\"recommend\"}', '::1', '2026-09-08 06:34:27');

-- --------------------------------------------------------

--
-- Table structure for table `budget_requests`
--

CREATE TABLE `budget_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `request_type` varchar(30) NOT NULL,
  `alp_id` bigint(20) UNSIGNED NOT NULL,
  `financial_year_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'draft',
  `reason` text DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `revision_number` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `submitted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_by` bigint(20) UNSIGNED DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `returned_by` bigint(20) UNSIGNED DEFAULT NULL,
  `returned_at` timestamp NULL DEFAULT NULL,
  `return_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `budget_requests`
--

INSERT INTO `budget_requests` (`id`, `request_type`, `alp_id`, `financial_year_id`, `amount`, `status`, `reason`, `reference_number`, `revision_number`, `created_by`, `submitted_by`, `submitted_at`, `approved_by`, `approved_at`, `rejected_by`, `rejected_at`, `rejection_reason`, `returned_by`, `returned_at`, `return_reason`, `created_at`, `updated_at`) VALUES
(1, 'initial_allocation', 1, 1, 500000.00, 'approved', 'Peruntukan tahunan (data pembangunan)', 'DBKL/BGT/2026/0001', 0, 8, 8, '2026-09-03 23:14:31', 10, '2026-09-03 23:14:31', NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-03 23:14:31', '2026-09-03 23:14:31'),
(2, 'initial_allocation', 2, 1, 500000.00, 'approved', 'Peruntukan tahunan (data pembangunan)', 'DBKL/BGT/2026/0002', 0, 8, 8, '2026-09-03 23:14:32', 10, '2026-09-03 23:14:32', NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-03 23:14:32', '2026-09-03 23:14:32'),
(3, 'allocation_increase', 2, 1, 50000.00, 'approved', 'Tambahan peruntukan (contoh)', 'DBKL/BGT/2026/PIND/0001', 0, 8, 8, '2026-09-03 23:14:32', 10, '2026-09-03 23:14:32', NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-03 23:14:32', '2026-09-03 23:14:32'),
(4, 'initial_allocation', 3, 1, 300000.00, 'approved', 'Peruntukan tahunan (data pembangunan)', 'DBKL/BGT/2026/0003', 0, 8, 8, '2026-09-03 23:14:32', 10, '2026-09-03 23:14:32', NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-03 23:14:32', '2026-09-03 23:14:32');

-- --------------------------------------------------------

--
-- Table structure for table `budget_request_histories`
--

CREATE TABLE `budget_request_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `budget_request_id` bigint(20) UNSIGNED NOT NULL,
  `from_status` varchar(30) DEFAULT NULL,
  `to_status` varchar(30) NOT NULL,
  `changed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `budget_request_histories`
--

INSERT INTO `budget_request_histories` (`id`, `budget_request_id`, `from_status`, `to_status`, `changed_by`, `remarks`, `created_at`) VALUES
(1, 1, NULL, 'draft', 8, 'Cadangan dicipta', '2026-09-03 23:14:31'),
(2, 1, 'draft', 'pending_approval', 8, 'Dihantar untuk kelulusan', '2026-09-03 23:14:31'),
(3, 1, 'pending_approval', 'approved', 10, 'Diluluskan & diposkan ke ledger', '2026-09-03 23:14:31'),
(4, 2, NULL, 'draft', 8, 'Cadangan dicipta', '2026-09-03 23:14:32'),
(5, 2, 'draft', 'pending_approval', 8, 'Dihantar untuk kelulusan', '2026-09-03 23:14:32'),
(6, 2, 'pending_approval', 'approved', 10, 'Diluluskan & diposkan ke ledger', '2026-09-03 23:14:32'),
(7, 3, NULL, 'draft', 8, 'Cadangan dicipta', '2026-09-03 23:14:32'),
(8, 3, 'draft', 'pending_approval', 8, 'Dihantar untuk kelulusan', '2026-09-03 23:14:32'),
(9, 3, 'pending_approval', 'approved', 10, 'Diluluskan & diposkan ke ledger', '2026-09-03 23:14:32'),
(10, 4, NULL, 'draft', 8, 'Cadangan dicipta', '2026-09-03 23:14:32'),
(11, 4, 'draft', 'pending_approval', 8, 'Dihantar untuk kelulusan', '2026-09-03 23:14:32'),
(12, 4, 'pending_approval', 'approved', 10, 'Diluluskan & diposkan ke ledger', '2026-09-03 23:14:32');

-- --------------------------------------------------------

--
-- Table structure for table `budget_transactions`
--

CREATE TABLE `budget_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `allocation_id` bigint(20) UNSIGNED NOT NULL,
  `alp_id` bigint(20) UNSIGNED NOT NULL,
  `financial_year_id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED DEFAULT NULL,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `project_expense_id` bigint(20) UNSIGNED DEFAULT NULL,
  `project_expense_refund_id` bigint(20) UNSIGNED DEFAULT NULL,
  `budget_request_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(40) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `budget_transactions`
--

INSERT INTO `budget_transactions` (`id`, `allocation_id`, `alp_id`, `financial_year_id`, `application_id`, `project_id`, `project_expense_id`, `project_expense_refund_id`, `budget_request_id`, `type`, `amount`, `reference_no`, `description`, `meta`, `created_by`, `created_at`) VALUES
(15, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 'initial_allocation', 30000.00, 'URS/2026/ALP-01', 'Peruntukan tahunan URS (RM30,000)', NULL, 2, '2026-09-05 06:18:18'),
(16, 2, 2, 1, NULL, NULL, NULL, NULL, NULL, 'initial_allocation', 30000.00, 'URS/2026/ALP-02', 'Peruntukan tahunan URS (RM30,000)', NULL, 2, '2026-09-05 06:18:18'),
(17, 3, 3, 1, NULL, NULL, NULL, NULL, NULL, 'initial_allocation', 30000.00, 'URS/2026/ALP-03', 'Peruntukan tahunan URS (RM30,000)', NULL, 2, '2026-09-05 06:18:18'),
(18, 4, 4, 1, NULL, NULL, NULL, NULL, NULL, 'initial_allocation', 30000.00, 'URS/2026/ALP-04', 'Peruntukan tahunan URS (RM30,000)', NULL, 2, '2026-09-05 06:18:27'),
(19, 5, 5, 1, NULL, NULL, NULL, NULL, NULL, 'initial_allocation', 30000.00, 'URS/2026/ALP-05', 'Peruntukan awal', NULL, 2, '2026-09-05 06:18:27'),
(20, 6, 6, 1, NULL, NULL, NULL, NULL, NULL, 'initial_allocation', 30000.00, 'URS/2026/ALP-06', 'Peruntukan awal', NULL, 2, '2026-09-05 06:18:27'),
(21, 7, 7, 1, NULL, NULL, NULL, NULL, NULL, 'initial_allocation', 30000.00, 'URS/2026/ALP-07', 'Peruntukan awal', NULL, 2, '2026-09-05 06:18:27'),
(22, 8, 8, 1, NULL, NULL, NULL, NULL, NULL, 'initial_allocation', 30000.00, 'URS/2026/ALP-08', 'Peruntukan awal', NULL, 2, '2026-09-05 06:18:27'),
(23, 9, 9, 1, NULL, NULL, NULL, NULL, NULL, 'initial_allocation', 30000.00, 'URS/2026/ALP-09', 'Peruntukan awal', NULL, 2, '2026-09-05 06:18:27'),
(24, 10, 10, 1, NULL, NULL, NULL, NULL, NULL, 'initial_allocation', 30000.00, 'URS/2026/ALP-10', 'Peruntukan awal', NULL, 2, '2026-09-05 06:18:27'),
(25, 11, 11, 1, NULL, NULL, NULL, NULL, NULL, 'initial_allocation', 30000.00, 'URS/2026/ALP-11', 'Peruntukan awal', NULL, 2, '2026-09-05 06:18:27'),
(26, 12, 12, 1, NULL, NULL, NULL, NULL, NULL, 'initial_allocation', 30000.00, 'URS/2026/ALP-12', 'Peruntukan awal', NULL, 2, '2026-09-05 06:18:27'),
(27, 13, 13, 1, NULL, NULL, NULL, NULL, NULL, 'initial_allocation', 30000.00, 'URS/2026/ALP-13', 'Peruntukan awal', NULL, 2, '2026-09-05 06:18:27'),
(28, 14, 14, 1, NULL, NULL, NULL, NULL, NULL, 'initial_allocation', 30000.00, 'URS/2026/ALP-14', 'Peruntukan awal', NULL, 2, '2026-09-05 06:18:27'),
(29, 15, 15, 1, NULL, NULL, NULL, NULL, NULL, 'initial_allocation', 30000.00, 'URS/2026/ALP-15', 'Peruntukan tahunan URS (RM30,000)', NULL, 2, '2026-09-05 06:18:27'),
(31, 1, 1, 1, 17, NULL, NULL, NULL, NULL, 'commitment', 3000.00, 'ALP/SUM/2026/0007', 'Komitmen bajet — permohonan diluluskan', NULL, 11, '2026-09-06 05:13:48'),
(32, 1, 1, 1, 18, NULL, NULL, NULL, NULL, 'commitment', 3000.00, 'ALP/SUM/2026/0008', 'Komitmen bajet — permohonan diluluskan', NULL, 11, '2026-09-07 01:56:20'),
(33, 1, 1, 1, 33, NULL, NULL, NULL, NULL, 'commitment', 350.00, 'ALP/SUM/2026/0023', 'Komitmen bajet — permohonan diluluskan', NULL, NULL, '2026-09-07 06:22:23'),
(34, 1, 1, 1, 34, NULL, NULL, NULL, NULL, 'commitment', 400.00, 'ALP/SUM/2026/0024', 'Komitmen bajet — permohonan diluluskan', NULL, NULL, '2026-09-07 06:22:23'),
(35, 1, 1, 1, 38, NULL, NULL, NULL, NULL, 'commitment', 100.00, 'ALP/SUM/2026/0028', 'Komitmen bajet — permohonan diluluskan', NULL, 11, '2026-09-07 23:12:22'),
(36, 2, 2, 1, 39, NULL, NULL, NULL, NULL, 'commitment', 1000.00, 'ALP/SUM/2026/0029', 'Komitmen bajet — permohonan diluluskan', NULL, 11, '2026-09-08 00:54:17'),
(37, 1, 1, 1, 32, NULL, NULL, NULL, NULL, 'commitment', 350.00, 'ALP/SUM/2026/0022', 'Komitmen bajet — permohonan diluluskan', NULL, 11, '2026-09-08 06:30:15');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('sistem-alp-dbkl-cache-17ba0791499db908433b80f37c5fbc89b870084b', 'i:2;', 1788872118),
('sistem-alp-dbkl-cache-17ba0791499db908433b80f37c5fbc89b870084b:timer', 'i:1788872118;', 1788872118),
('sistem-alp-dbkl-cache-1b6453892473a467d07372d45eb05abc2031647a', 'i:1;', 1788872401),
('sistem-alp-dbkl-cache-1b6453892473a467d07372d45eb05abc2031647a:timer', 'i:1788872401;', 1788872401),
('sistem-alp-dbkl-cache-356a192b7913b04c54574d18c28d46e6395428ab', 'i:2;', 1788970538),
('sistem-alp-dbkl-cache-356a192b7913b04c54574d18c28d46e6395428ab:timer', 'i:1788970538;', 1788970538),
('sistem-alp-dbkl-cache-424f74a6a7ed4d4ed4761507ebcd209a6ef0937b', 'i:1;', 1789026594),
('sistem-alp-dbkl-cache-424f74a6a7ed4d4ed4761507ebcd209a6ef0937b:timer', 'i:1789026594;', 1789026594),
('sistem-alp-dbkl-cache-5c785c036466adea360111aa28563bfd556b5fba', 'i:1;', 1788879143),
('sistem-alp-dbkl-cache-5c785c036466adea360111aa28563bfd556b5fba:timer', 'i:1788879143;', 1788879143),
('sistem-alp-dbkl-cache-77de68daecd823babbb58edb1c8e14d7106e83bb', 'i:2;', 1789020526),
('sistem-alp-dbkl-cache-77de68daecd823babbb58edb1c8e14d7106e83bb:timer', 'i:1789020526;', 1789020526),
('sistem-alp-dbkl-cache-902ba3cda1883801594b6e1b452790cc53948fda', 'i:1;', 1788851288),
('sistem-alp-dbkl-cache-902ba3cda1883801594b6e1b452790cc53948fda:timer', 'i:1788851288;', 1788851288),
('sistem-alp-dbkl-cache-b1d5781111d84f7b3fe45a0852e59758cd7a87e5', 'i:1;', 1788850979),
('sistem-alp-dbkl-cache-b1d5781111d84f7b3fe45a0852e59758cd7a87e5:timer', 'i:1788850979;', 1788850979),
('sistem-alp-dbkl-cache-da4b9237bacccdf19c0760cab7aec4a8359010b0', 'i:1;', 1788873218),
('sistem-alp-dbkl-cache-da4b9237bacccdf19c0760cab7aec4a8359010b0:timer', 'i:1788873218;', 1788873218),
('sistem-alp-dbkl-cache-jkew@dbkl.test|::1', 'i:1;', 1788970478),
('sistem-alp-dbkl-cache-jkew@dbkl.test|::1:timer', 'i:1788970478;', 1788970478),
('sistem-alp-dbkl-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:80:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:14:\"dashboard.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:9:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;i:4;i:6;i:5;i:7;i:6;i:8;i:7;i:9;i:8;i:10;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:19:\"dashboard.executive\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:9;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:10:\"users.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:12:\"users.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:12:\"users.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:16:\"users.deactivate\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:17:\"users.assign_role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:10:\"roles.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:9:\"alps.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:7:{i:0;i:2;i:1;i:4;i:2;i:5;i:3;i:6;i:4;i:7;i:5;i:8;i:6;i:9;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:11:\"alps.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:11:\"alps.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:15:\"alps.deactivate\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:20:\"financial_years.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:2;i:1;i:9;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:22:\"financial_years.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:22:\"financial_years.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:22:\"financial_years.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:16:\"allocations.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:2;i:1;i:5;i:2;i:6;i:3;i:8;i:4;i:9;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:15:\"budget.view_all\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:2;i:1;i:6;i:2;i:8;i:3;i:9;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:26:\"allocations.request.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:6;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:26:\"allocations.request.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:6;}}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:26:\"allocations.request.submit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:6;}}i:21;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:19:\"allocations.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:8;}}i:22;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:18:\"allocations.reject\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:8;}}i:23;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:18:\"allocations.return\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:8;}}i:24;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:26:\"adjustments.request.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:6;}}i:25;a:4:{s:1:\"a\";i:26;s:1:\"b\";s:26:\"adjustments.request.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:6;}}i:26;a:4:{s:1:\"a\";i:27;s:1:\"b\";s:26:\"adjustments.request.submit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:6;}}i:27;a:4:{s:1:\"a\";i:28;s:1:\"b\";s:19:\"adjustments.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:8;}}i:28;a:4:{s:1:\"a\";i:29;s:1:\"b\";s:18:\"adjustments.reject\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:8;}}i:29;a:4:{s:1:\"a\";i:30;s:1:\"b\";s:18:\"adjustments.return\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:8;}}i:30;a:4:{s:1:\"a\";i:31;s:1:\"b\";s:19:\"applications.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:3;i:1;i:4;}}i:31;a:4:{s:1:\"a\";i:32;s:1:\"b\";s:21:\"applications.view_all\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:7:{i:0;i:2;i:1;i:5;i:2;i:6;i:3;i:7;i:4;i:8;i:5;i:9;i:6;i:10;}}i:32;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:31:\"applications.review.secretariat\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:2;i:1;i:5;}}i:33;a:4:{s:1:\"a\";i:34;s:1:\"b\";s:27:\"applications.review.finance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:6;}}i:34;a:4:{s:1:\"a\";i:35;s:1:\"b\";s:29:\"applications.review.technical\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:7;}}i:35;a:4:{s:1:\"a\";i:36;s:1:\"b\";s:20:\"applications.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:8;i:1;i:9;}}i:36;a:4:{s:1:\"a\";i:37;s:1:\"b\";s:19:\"applications.reject\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:8;i:1;i:9;}}i:37;a:4:{s:1:\"a\";i:38;s:1:\"b\";s:20:\"approval_matrix.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:2;i:1;i:9;}}i:38;a:4:{s:1:\"a\";i:39;s:1:\"b\";s:22:\"approval_matrix.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:39;a:4:{s:1:\"a\";i:40;s:1:\"b\";s:13:\"projects.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:3;i:1;i:4;}}i:40;a:4:{s:1:\"a\";i:41;s:1:\"b\";s:17:\"projects.view_all\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:2;i:1;i:5;i:2;i:6;i:3;i:7;i:4;i:8;i:5;i:9;}}i:41;a:4:{s:1:\"a\";i:42;s:1:\"b\";s:15:\"projects.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:42;a:4:{s:1:\"a\";i:43;s:1:\"b\";s:17:\"projects.progress\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:43;a:4:{s:1:\"a\";i:44;s:1:\"b\";s:19:\"projects.milestones\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:44;a:4:{s:1:\"a\";i:45;s:1:\"b\";s:17:\"projects.complete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:45;a:4:{s:1:\"a\";i:46;s:1:\"b\";s:14:\"projects.close\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:8;}}i:46;a:4:{s:1:\"a\";i:47;s:1:\"b\";s:13:\"expenses.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:5;i:1;i:6;i:2;i:8;}}i:47;a:4:{s:1:\"a\";i:48;s:1:\"b\";s:15:\"expenses.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:6;}}i:48;a:4:{s:1:\"a\";i:49;s:1:\"b\";s:15:\"expenses.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:6;}}i:49;a:4:{s:1:\"a\";i:50;s:1:\"b\";s:15:\"expenses.submit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:6;}}i:50;a:4:{s:1:\"a\";i:51;s:1:\"b\";s:15:\"expenses.verify\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:8;}}i:51;a:4:{s:1:\"a\";i:52;s:1:\"b\";s:15:\"expenses.reject\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:8;}}i:52;a:4:{s:1:\"a\";i:53;s:1:\"b\";s:15:\"expenses.return\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:8;}}i:53;a:4:{s:1:\"a\";i:54;s:1:\"b\";s:23:\"expenses.documents.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:2;i:1;i:5;i:2;i:6;i:3;i:7;i:4;i:8;i:5;i:9;}}i:54;a:4:{s:1:\"a\";i:55;s:1:\"b\";s:25:\"expenses.documents.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:6;}}i:55;a:4:{s:1:\"a\";i:56;s:1:\"b\";s:12:\"refunds.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:2;i:1;i:5;i:2;i:6;i:3;i:7;i:4;i:8;i:5;i:9;}}i:56;a:4:{s:1:\"a\";i:57;s:1:\"b\";s:14:\"refunds.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:6;}}i:57;a:4:{s:1:\"a\";i:58;s:1:\"b\";s:14:\"refunds.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:6;}}i:58;a:4:{s:1:\"a\";i:59;s:1:\"b\";s:14:\"refunds.submit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:6;}}i:59;a:4:{s:1:\"a\";i:60;s:1:\"b\";s:14:\"refunds.verify\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:8;}}i:60;a:4:{s:1:\"a\";i:61;s:1:\"b\";s:14:\"refunds.reject\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:8;}}i:61;a:4:{s:1:\"a\";i:62;s:1:\"b\";s:14:\"refunds.return\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:8;}}i:62;a:4:{s:1:\"a\";i:63;s:1:\"b\";s:31:\"projects.closure-documents.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:2;i:1;i:5;i:2;i:6;i:3;i:7;i:4;i:8;i:5;i:9;}}i:63;a:4:{s:1:\"a\";i:64;s:1:\"b\";s:33:\"projects.closure-documents.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:64;a:4:{s:1:\"a\";i:65;s:1:\"b\";s:22:\"project-reports.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:65;a:4:{s:1:\"a\";i:66;s:1:\"b\";s:22:\"project-reports.review\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:8;}}i:66;a:4:{s:1:\"a\";i:67;s:1:\"b\";s:17:\"dashboard.finance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:6;i:1;i:8;i:2;i:9;}}i:67;a:4:{s:1:\"a\";i:68;s:1:\"b\";s:12:\"reports.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:9:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;i:4;i:6;i:5;i:7;i:6;i:8;i:7;i:9;i:8;i:10;}}i:68;a:4:{s:1:\"a\";i:69;s:1:\"b\";s:17:\"reports.financial\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:6;i:1;i:8;i:2;i:9;}}i:69;a:4:{s:1:\"a\";i:70;s:1:\"b\";s:20:\"reports.applications\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:2;i:1;i:5;i:2;i:9;i:3;i:10;}}i:70;a:4:{s:1:\"a\";i:71;s:1:\"b\";s:16:\"reports.projects\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:2;i:1;i:5;i:2;i:6;i:3;i:7;i:4;i:8;i:5;i:9;}}i:71;a:4:{s:1:\"a\";i:72;s:1:\"b\";s:11:\"reports.csr\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:5;i:1;i:9;}}i:72;a:4:{s:1:\"a\";i:73;s:1:\"b\";s:13:\"reports.audit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:2;i:1;i:9;}}i:73;a:4:{s:1:\"a\";i:74;s:1:\"b\";s:14:\"reports.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:8:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;i:4;i:6;i:5;i:8;i:6;i:9;i:7;i:10;}}i:74;a:4:{s:1:\"a\";i:75;s:1:\"b\";s:15:\"settings.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:75;a:4:{s:1:\"a\";i:76;s:1:\"b\";s:18:\"allocations.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:76;a:4:{s:1:\"a\";i:77;s:1:\"b\";s:13:\"payments.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:2;i:1;i:6;i:2;i:8;i:3;i:9;i:4;i:10;}}i:77;a:4:{s:1:\"a\";i:78;s:1:\"b\";s:15:\"payments.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:6;}}i:78;a:4:{s:1:\"a\";i:79;s:1:\"b\";s:19:\"payments.jkew_scope\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:10;}}i:79;a:4:{s:1:\"a\";i:80;s:1:\"b\";s:29:\"applications.create_on_behalf\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}}s:5:\"roles\";a:9:{i:0;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:12:\"system_admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:3:\"alp\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:13:\"urussetia_alp\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";i:5;s:1:\"b\";s:17:\"pegawai_urussetia\";s:1:\"c\";s:3:\"web\";}i:4;a:3:{s:1:\"a\";i:6;s:1:\"b\";s:16:\"pegawai_kewangan\";s:1:\"c\";s:3:\"web\";}i:5;a:3:{s:1:\"a\";i:7;s:1:\"b\";s:16:\"pegawai_teknikal\";s:1:\"c\";s:3:\"web\";}i:6;a:3:{s:1:\"a\";i:8;s:1:\"b\";s:7:\"pelulus\";s:1:\"c\";s:3:\"web\";}i:7;a:3:{s:1:\"a\";i:9;s:1:\"b\";s:10:\"pengurusan\";s:1:\"c\";s:3:\"web\";}i:8;a:3:{s:1:\"a\";i:10;s:1:\"b\";s:12:\"pegawai_jkew\";s:1:\"c\";s:3:\"web\";}}}', 1789052476),
('sistem-alp-dbkl-cache-system_settings', 'a:5:{s:18:\"urs_policy_enabled\";s:1:\"1\";s:25:\"urs_max_annual_allocation\";s:8:\"30000.00\";s:23:\"urs_max_per_application\";s:7:\"3000.00\";s:16:\"urs_period_quota\";s:8:\"10000.00\";s:16:\"urs_overdue_days\";s:2:\"14\";}', 1789026576);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_requirements`
--

CREATE TABLE `document_requirements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `document_type` varchar(40) NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `document_requirements`
--

INSERT INTO `document_requirements` (`id`, `document_type`, `is_required`, `active`, `created_at`, `updated_at`) VALUES
(1, 'kertas_kerja', 1, 1, '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(2, 'pecahan_bajet', 0, 0, '2026-09-03 23:14:28', '2026-09-07 06:22:23'),
(3, 'sebut_harga', 0, 0, '2026-09-03 23:14:28', '2026-09-07 06:22:23'),
(4, 'surat_sokongan', 0, 0, '2026-09-03 23:14:28', '2026-09-07 06:22:23'),
(7, 'pelan_lokasi', 0, 0, '2026-09-03 23:14:28', '2026-09-07 06:22:23'),
(9, 'dokumen_teknikal', 0, 0, '2026-09-03 23:14:28', '2026-09-07 06:22:23'),
(10, 'pendaftaran_pertubuhan', 1, 1, '2026-09-05 04:24:51', '2026-09-05 04:24:51'),
(11, 'borang_eft', 1, 1, '2026-09-05 04:24:51', '2026-09-05 04:24:51'),
(12, 'penyata_bank', 1, 1, '2026-09-05 04:24:51', '2026-09-05 04:24:51'),
(13, 'sijil_ros', 1, 1, '2026-09-05 04:24:51', '2026-09-05 04:24:51');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `financial_years`
--

CREATE TABLE `financial_years` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `year` smallint(5) UNSIGNED NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `opened_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `financial_years`
--

INSERT INTO `financial_years` (`id`, `year`, `label`, `status`, `is_active`, `opened_at`, `closed_at`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 2026, 'Tahun Kewangan 2026', 'active', 1, '2026-09-03 23:14:28', NULL, NULL, NULL, '2026-09-03 23:14:28', '2026-09-03 23:14:28');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_08_19_030215_create_permission_tables', 1),
(5, '2026_08_20_000001_create_financial_years_table', 1),
(6, '2026_08_20_000002_create_alps_table', 1),
(7, '2026_08_20_000003_add_alp_relation_to_users_table', 1),
(8, '2026_08_21_000001_create_allocations_table', 1),
(9, '2026_08_21_000002_create_budget_transactions_table', 1),
(10, '2026_08_21_000003_create_audit_logs_table', 1),
(11, '2026_08_22_000001_create_applications_table', 1),
(12, '2026_08_22_000002_create_application_budget_items_table', 1),
(13, '2026_08_22_000003_create_application_documents_table', 1),
(14, '2026_08_22_000004_create_application_status_histories_table', 1),
(15, '2026_08_22_000005_create_application_sequences_table', 1),
(16, '2026_08_22_000006_create_document_requirements_table', 1),
(17, '2026_08_23_000001_add_application_link_to_budget_transactions', 1),
(18, '2026_08_23_000002_create_application_reviews_table', 1),
(19, '2026_08_23_000003_create_approval_levels_table', 1),
(20, '2026_08_23_000004_create_application_approvals_table', 1),
(21, '2026_08_23_000005_create_application_revisions_table', 1),
(22, '2026_08_23_000006_create_application_workflow_settings_table', 1),
(23, '2026_08_23_000007_add_revision_number_to_applications_table', 1),
(24, '2026_08_24_000001_create_budget_requests_table', 1),
(25, '2026_08_24_000002_create_budget_request_histories_table', 1),
(26, '2026_08_24_000003_add_budget_request_link_to_budget_transactions', 1),
(27, '2026_08_25_000001_create_projects_table', 1),
(28, '2026_08_25_000002_create_project_sequences_table', 1),
(29, '2026_08_25_000003_create_project_milestones_table', 1),
(30, '2026_08_25_000004_create_project_progress_histories_table', 1),
(31, '2026_08_25_000005_create_project_status_histories_table', 1),
(32, '2026_08_25_000006_create_project_expenses_table', 1),
(33, '2026_08_25_000007_create_project_expense_histories_table', 1),
(34, '2026_08_25_000008_create_project_reports_table', 1),
(35, '2026_08_25_000009_create_project_documents_table', 1),
(36, '2026_08_25_000010_add_project_link_to_budget_transactions', 1),
(37, '2026_08_26_000001_create_project_expense_refunds_table', 1),
(38, '2026_08_26_000002_create_project_expense_refund_histories_table', 1),
(39, '2026_08_26_000003_add_refund_link_to_project_documents', 1),
(40, '2026_08_26_000004_add_refund_link_to_budget_transactions', 1),
(41, '2026_08_26_000005_create_project_document_requirements_table', 1),
(42, '2026_08_25_120000_create_system_settings_and_notifications_tables', 2),
(43, '2026_08_25_150000_add_payment_fields_to_applications_table', 2),
(44, '2026_09_05_000001_add_avatar_to_users_table', 2),
(45, '2026_09_05_041255_add_checklist_to_application_reviews_table', 2),
(46, '2026_09_05_042610_create_recipients_table', 2),
(47, '2026_09_05_120000_add_urs_recipient_fields_to_applications_table', 2),
(48, '2026_09_05_140000_add_urs_program_and_jkew_fields_to_applications_table', 2),
(49, '2026_09_05_160000_add_report_card_fields_to_applications_table', 2),
(50, '2026_09_05_210000_refactor_applications_to_borang_penyaluran', 3),
(51, '2026_09_06_130000_sync_approval_levels_to_urs_v12', 4),
(52, '2026_09_06_140000_require_pepu_after_peraku', 5),
(53, '2026_09_07_210000_add_program_date_to_applications_table', 6),
(54, '2026_09_07_214000_add_program_category_and_address_to_applications_table', 7),
(55, '2026_09_07_230000_add_voucher_supplier_and_date_to_applications', 8),
(56, '2026_09_08_220000_add_report_card_review_workflow', 9);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2),
(3, 'App\\Models\\User', 3),
(3, 'App\\Models\\User', 4),
(3, 'App\\Models\\User', 5),
(3, 'App\\Models\\User', 12),
(3, 'App\\Models\\User', 13),
(3, 'App\\Models\\User', 14),
(3, 'App\\Models\\User', 15),
(3, 'App\\Models\\User', 16),
(3, 'App\\Models\\User', 17),
(3, 'App\\Models\\User', 18),
(3, 'App\\Models\\User', 19),
(3, 'App\\Models\\User', 20),
(3, 'App\\Models\\User', 21),
(3, 'App\\Models\\User', 22),
(3, 'App\\Models\\User', 23),
(4, 'App\\Models\\User', 6),
(5, 'App\\Models\\User', 7),
(6, 'App\\Models\\User', 8),
(7, 'App\\Models\\User', 9),
(8, 'App\\Models\\User', 10),
(9, 'App\\Models\\User', 11);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
('00ba4400-735f-4ec1-a232-6544de3f1715', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 2, '{\"event\":\"submitted\",\"title\":\"Permohonan dihantar\",\"message\":\"Permohonan baharu telah dihantar dan menunggu semakan Admin JP.\",\"application_id\":39,\"application_number\":\"ALP\\/SUM\\/2026\\/0029\",\"url\":\"\\/permohonan\\/39\"}', '2026-09-08 00:35:34', '2026-09-08 00:34:49', '2026-09-08 00:35:34'),
('08edfa59-6b89-4009-95fd-30d6bc5fb3ab', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 2, '{\"event\":\"submitted\",\"title\":\"Permohonan dihantar\",\"message\":\"Permohonan baharu telah dihantar dan menunggu semakan Urus Setia.\",\"application_id\":38,\"application_number\":\"ALP\\/SUM\\/2026\\/0028\",\"url\":\"\\/permohonan\\/38\"}', '2026-09-07 20:34:30', '2026-09-07 20:34:00', '2026-09-07 20:34:30'),
('105806cf-e6f8-4f5c-995d-2580253b0f47', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 10, '{\"event\":\"awaiting_peraku\",\"title\":\"Menunggu perakuan\",\"message\":\"Permohonan menunggu perakuan (NT-003). Aras: Peraku untuk diangkat ke PEPU (TP\\/Pengarah JP).\",\"application_id\":33,\"application_number\":\"ALP\\/SUM\\/2026\\/0023\",\"url\":\"\\/permohonan\\/33\"}', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
('14eed585-a00f-4113-9d09-626b31c947fd', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 6, '{\"event\":\"payment_voucher\",\"title\":\"Baucar pembayaran\",\"message\":\"Baucar pembayaran untuk permohonan anda sedang disediakan \\/ telah direkod.\",\"application_id\":34,\"application_number\":\"ALP\\/SUM\\/2026\\/0024\",\"url\":\"\\/permohonan\\/34\"}', NULL, '2026-09-07 20:01:27', '2026-09-07 20:01:27'),
('19575262-3d6a-4427-9a4f-b91d1fc056e4', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 3, '{\"event\":\"revision_required\",\"title\":\"Permohonan dikembalikan\",\"message\":\"Permohonan anda dikembalikan untuk pembetulan. Sebab: betulkan apa yg salah\",\"application_id\":30,\"application_number\":\"ALP\\/SUM\\/2026\\/0020\",\"url\":\"\\/permohonan\\/30\"}', '2026-09-07 19:16:31', '2026-09-07 19:16:07', '2026-09-07 19:16:31'),
('22fb1a41-b2a8-44e1-aa59-c8d9e63986c0', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 7, '{\"event\":\"submitted\",\"title\":\"Permohonan dihantar\",\"message\":\"Permohonan baharu telah dihantar dan menunggu semakan Urus Setia.\",\"application_id\":30,\"application_number\":\"ALP\\/SUM\\/2026\\/0020\",\"url\":\"\\/permohonan\\/30\"}', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
('24418a29-c089-4d35-b180-3467af5efa2f', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 11, '{\"event\":\"awaiting_pepu\",\"title\":\"Menunggu kelulusan PEPU\",\"message\":\"Permohonan menunggu kelulusan PEPU \\/ aras seterusnya (NT-004). Aras: Kelulusan PEPU \\/ Pengurusan Tertinggi.\",\"application_id\":18,\"application_number\":\"ALP\\/SUM\\/2026\\/0008\",\"url\":\"\\/permohonan\\/18\"}', NULL, '2026-09-07 01:53:13', '2026-09-07 01:53:13'),
('25bb85ab-ba23-4542-9074-2778fdafebbc', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 7, '{\"event\":\"submitted\",\"title\":\"Permohonan dihantar\",\"message\":\"Permohonan baharu telah dihantar dan menunggu semakan Urus Setia.\",\"application_id\":17,\"application_number\":\"ALP\\/SUM\\/2026\\/0007\",\"url\":\"\\/permohonan\\/17\"}', '2026-09-06 04:27:40', '2026-09-05 06:42:37', '2026-09-06 04:27:40'),
('291a8862-1db8-4947-bbcc-341319468bcb', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 11, '{\"event\":\"awaiting_pepu\",\"title\":\"Menunggu kelulusan PEPU\",\"message\":\"Permohonan menunggu kelulusan PEPU \\/ aras seterusnya (NT-004). Aras: Kelulusan PEPU \\/ Pengurusan Tertinggi.\",\"application_id\":32,\"application_number\":\"ALP\\/SUM\\/2026\\/0022\",\"url\":\"\\/permohonan\\/32\"}', '2026-09-08 06:29:16', '2026-09-07 06:22:23', '2026-09-08 06:29:16'),
('3484ca49-1f7a-42a3-9b13-fc4e0d1f1b99', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 8, '{\"event\":\"awaiting_payment\",\"title\":\"Sedia untuk proses bayaran\",\"message\":\"Permohonan telah diluluskan PEPU dan sedia untuk proses bayaran. Sila sediakan baucar (jumlah: RM3,000.00).\",\"application_id\":18,\"application_number\":\"ALP\\/SUM\\/2026\\/0008\",\"url\":\"\\/permohonan\\/18\"}', NULL, '2026-09-08 01:07:21', '2026-09-08 01:07:21'),
('35c1caa0-75ba-46d5-b9e4-8a17cd24897f', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 7, '{\"event\":\"submitted\",\"title\":\"Permohonan dihantar\",\"message\":\"Permohonan baharu telah dihantar dan menunggu semakan Urus Setia.\",\"application_id\":27,\"application_number\":\"ALP\\/SUM\\/2026\\/0017\",\"url\":\"\\/permohonan\\/27\"}', NULL, '2026-09-07 06:39:32', '2026-09-07 06:39:32'),
('3abe58f4-3a3b-41e6-8208-8cdecc551732', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 11, '{\"event\":\"awaiting_pepu\",\"title\":\"Menunggu kelulusan PEPU\",\"message\":\"Permohonan menunggu kelulusan PEPU \\/ aras seterusnya (NT-004). Aras: Kelulusan PEPU \\/ Pengurusan Tertinggi.\",\"application_id\":39,\"application_number\":\"ALP\\/SUM\\/2026\\/0029\",\"url\":\"\\/permohonan\\/39\"}', '2026-09-08 00:54:04', '2026-09-08 00:53:42', '2026-09-08 00:54:04'),
('3e5ab518-da01-4825-9842-013c874a9843', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 10, '{\"event\":\"awaiting_peraku\",\"title\":\"Menunggu perakuan\",\"message\":\"Permohonan menunggu perakuan (NT-003). Aras: Pengesyoran PEPU.\",\"application_id\":38,\"application_number\":\"ALP\\/SUM\\/2026\\/0028\",\"url\":\"\\/permohonan\\/38\"}', NULL, '2026-09-07 23:07:25', '2026-09-07 23:07:25'),
('3f27439b-e7cd-410c-b084-f0c150abea77', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 6, '{\"event\":\"approved\",\"title\":\"Permohonan diluluskan\",\"message\":\"Permohonan anda telah diluluskan. Anda boleh mencetak surat\\/ringkasan kelulusan.\",\"application_id\":38,\"application_number\":\"ALP\\/SUM\\/2026\\/0028\",\"url\":\"\\/permohonan\\/38\"}', NULL, '2026-09-07 23:12:22', '2026-09-07 23:12:22'),
('43430e4e-c6ec-4bdc-b1a2-03bff730586e', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 3, '{\"event\":\"approved\",\"title\":\"Permohonan diluluskan\",\"message\":\"Permohonan anda telah diluluskan. Anda boleh mencetak surat\\/ringkasan kelulusan.\",\"application_id\":17,\"application_number\":\"ALP\\/SUM\\/2026\\/0007\",\"url\":\"\\/permohonan\\/17\"}', '2026-09-07 01:18:51', '2026-09-06 04:54:46', '2026-09-07 01:18:51'),
('44c5e186-5ea4-418e-8d7f-51119ed4a413', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 6, '{\"event\":\"approved\",\"title\":\"Permohonan diluluskan\",\"message\":\"Permohonan anda telah diluluskan. Anda boleh mencetak surat\\/ringkasan kelulusan.\",\"application_id\":33,\"application_number\":\"ALP\\/SUM\\/2026\\/0023\",\"url\":\"\\/permohonan\\/33\"}', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
('4e5679fc-6ad9-4cf9-8086-78a2b3ace2ae', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 10, '{\"event\":\"awaiting_peraku\",\"title\":\"Menunggu perakuan\",\"message\":\"Permohonan menunggu perakuan (NT-003). Aras: Peraku untuk diangkat ke PEPU (TP\\/Pengarah JP).\",\"application_id\":31,\"application_number\":\"ALP\\/SUM\\/2026\\/0021\",\"url\":\"\\/permohonan\\/31\"}', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
('4f24583e-cd75-4fb6-a1be-42f41f532663', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 6, '{\"event\":\"revision_required\",\"title\":\"Permohonan dikembalikan\",\"message\":\"Permohonan anda dikembalikan untuk pembetulan. Sebab: betulkan apa yg salah\",\"application_id\":30,\"application_number\":\"ALP\\/SUM\\/2026\\/0020\",\"url\":\"\\/permohonan\\/30\"}', NULL, '2026-09-07 19:16:07', '2026-09-07 19:16:07'),
('503e48aa-3306-40f5-af03-b8cccdc382bc', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 3, '{\"event\":\"approved\",\"title\":\"Permohonan diluluskan\",\"message\":\"Permohonan anda telah diluluskan. Anda boleh mencetak surat\\/ringkasan kelulusan.\",\"application_id\":32,\"application_number\":\"ALP\\/SUM\\/2026\\/0022\",\"url\":\"\\/permohonan\\/32\"}', NULL, '2026-09-08 06:30:15', '2026-09-08 06:30:15'),
('504307eb-14d1-478a-84da-a82fbdadc8f0', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 7, '{\"event\":\"submitted\",\"title\":\"Permohonan dihantar\",\"message\":\"Permohonan baharu telah dihantar dan menunggu semakan Urus Setia.\",\"application_id\":18,\"application_number\":\"ALP\\/SUM\\/2026\\/0008\",\"url\":\"\\/permohonan\\/18\"}', NULL, '2026-09-07 01:12:32', '2026-09-07 01:12:32'),
('54e4bca4-795a-4202-8ac7-55045d87d290', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 3, '{\"event\":\"report_card_approved\",\"title\":\"Laporan aktiviti disahkan\",\"message\":\"Laporan aktiviti anda telah disahkan Pegawai JP.\",\"application_id\":32,\"application_number\":\"ALP\\/SUM\\/2026\\/0022\",\"url\":\"\\/permohonan\\/32\"}', '2026-09-08 06:35:01', '2026-09-08 06:34:27', '2026-09-08 06:35:01'),
('55ccda13-140d-4786-9e9d-56ac4904d2d6', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 2, '{\"event\":\"report_card_submitted\",\"title\":\"Laporan aktiviti menunggu semakan\",\"message\":\"Laporan aktiviti telah dimuat naik oleh ALP dan menunggu semakan Admin JP.\",\"application_id\":34,\"application_number\":\"ALP\\/SUM\\/2026\\/0024\",\"url\":\"\\/permohonan\\/34\"}', '2026-09-08 06:15:14', '2026-09-08 06:14:38', '2026-09-08 06:15:14'),
('5804ca6b-2ddb-490e-b8e6-30b99fec1170', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 8, '{\"event\":\"awaiting_payment\",\"title\":\"Sedia untuk proses bayaran\",\"message\":\"Permohonan telah diluluskan PEPU dan sedia untuk proses bayaran. Sila sediakan baucar (jumlah: RM1,000.00).\",\"application_id\":39,\"application_number\":\"ALP\\/SUM\\/2026\\/0029\",\"url\":\"\\/permohonan\\/39\"}', '2026-09-08 01:11:23', '2026-09-08 01:07:21', '2026-09-08 01:11:23'),
('59f773a1-c3de-44b4-8202-e8fc82524165', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 10, '{\"event\":\"awaiting_peraku\",\"title\":\"Menunggu perakuan\",\"message\":\"Permohonan menunggu perakuan (NT-003). Aras: Aras 1 \\u2014 Pegawai.\",\"application_id\":17,\"application_number\":\"ALP\\/SUM\\/2026\\/0007\",\"url\":\"\\/permohonan\\/17\"}', NULL, '2026-09-06 04:43:00', '2026-09-06 04:43:00'),
('5a0b8407-5bee-4aaa-87be-4a6ccf8ca11d', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 7, '{\"event\":\"submitted\",\"title\":\"Permohonan dihantar\",\"message\":\"Permohonan baharu telah dihantar dan menunggu semakan Urus Setia.\",\"application_id\":25,\"application_number\":\"ALP\\/SUM\\/2026\\/0015\",\"url\":\"\\/permohonan\\/25\"}', NULL, '2026-09-07 06:21:36', '2026-09-07 06:21:36'),
('5e563c78-32d6-4033-bdd7-c51b74370f7e', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 6, '{\"event\":\"payment_voucher\",\"title\":\"Baucar pembayaran\",\"message\":\"Baucar telah disedia. Sila muat naik laporan aktiviti dalam 1 bulan (akhir: 08\\/10\\/2026).\",\"application_id\":32,\"application_number\":\"ALP\\/SUM\\/2026\\/0022\",\"url\":\"\\/permohonan\\/32\"}', NULL, '2026-09-08 06:31:11', '2026-09-08 06:31:11'),
('5ee50816-0a96-4fdd-9029-f4571f052dcc', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 7, '{\"event\":\"submitted\",\"title\":\"Permohonan dihantar\",\"message\":\"Permohonan baharu telah dihantar dan menunggu semakan Urus Setia.\",\"application_id\":12,\"application_number\":\"ALP\\/SUM\\/2026\\/0002\",\"url\":\"\\/permohonan\\/12\"}', '2026-09-06 04:44:51', '2026-09-05 05:10:36', '2026-09-06 04:44:51'),
('627c1f96-30c3-490c-bb49-e3e97be06b66', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 10, '{\"event\":\"awaiting_peraku\",\"title\":\"Menunggu perakuan\",\"message\":\"Permohonan menunggu perakuan (NT-003). Aras: Peraku untuk diangkat ke PEPU (TP\\/Pengarah JP).\",\"application_id\":34,\"application_number\":\"ALP\\/SUM\\/2026\\/0024\",\"url\":\"\\/permohonan\\/34\"}', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
('64753270-04cf-4612-8b08-f68125f6b93f', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 3, '{\"event\":\"approved\",\"title\":\"Permohonan diluluskan\",\"message\":\"Permohonan anda telah diluluskan. Anda boleh mencetak surat\\/ringkasan kelulusan.\",\"application_id\":18,\"application_number\":\"ALP\\/SUM\\/2026\\/0008\",\"url\":\"\\/permohonan\\/18\"}', '2026-09-07 01:56:42', '2026-09-07 01:56:20', '2026-09-07 01:56:42'),
('68c2b839-3174-4a34-8b3d-05036eee832a', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 3, '{\"event\":\"approved\",\"title\":\"Permohonan diluluskan\",\"message\":\"Permohonan anda telah diluluskan. Anda boleh mencetak surat\\/ringkasan kelulusan.\",\"application_id\":38,\"application_number\":\"ALP\\/SUM\\/2026\\/0028\",\"url\":\"\\/permohonan\\/38\"}', '2026-09-08 06:07:44', '2026-09-07 23:12:22', '2026-09-08 06:07:44'),
('6bd6f66c-53eb-408a-b4bf-5bab802ec6e7', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 10, '{\"event\":\"awaiting_peraku\",\"title\":\"Menunggu perakuan\",\"message\":\"Permohonan menunggu perakuan (NT-003). Aras: Pengesyoran PEPU.\",\"application_id\":38,\"application_number\":\"ALP\\/SUM\\/2026\\/0028\",\"url\":\"\\/permohonan\\/38\"}', '2026-09-07 20:39:23', '2026-09-07 20:36:05', '2026-09-07 20:39:23'),
('6c1e76a9-c7c4-4d19-93a9-f0851a5676fe', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 3, '{\"event\":\"report_card_approved\",\"title\":\"Laporan aktiviti disahkan\",\"message\":\"Laporan aktiviti anda telah disahkan Pegawai JP.\",\"application_id\":34,\"application_number\":\"ALP\\/SUM\\/2026\\/0024\",\"url\":\"\\/permohonan\\/34\"}', '2026-09-08 06:17:34', '2026-09-08 06:16:28', '2026-09-08 06:17:34'),
('6d8420cf-1e3d-45b1-ad43-9557c56c0f95', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 6, '{\"event\":\"approved\",\"title\":\"Permohonan diluluskan\",\"message\":\"Permohonan anda telah diluluskan. Anda boleh mencetak surat\\/ringkasan kelulusan.\",\"application_id\":34,\"application_number\":\"ALP\\/SUM\\/2026\\/0024\",\"url\":\"\\/permohonan\\/34\"}', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
('6da96158-fe00-49a1-ac3b-cb9f9ecc7347', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 6, '{\"event\":\"rejected\",\"title\":\"Permohonan ditolak\",\"message\":\"Permohonan anda telah ditolak. Sebab: Ditolak (simulasi)\",\"application_id\":35,\"application_number\":\"ALP\\/SUM\\/2026\\/0025\",\"url\":\"\\/permohonan\\/35\"}', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
('6e27765b-9585-4c6b-af11-ded75068b440', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 8, '{\"event\":\"awaiting_payment\",\"title\":\"Sedia untuk proses bayaran\",\"message\":\"Permohonan telah diluluskan PEPU dan sedia untuk proses bayaran. Sila sediakan baucar (jumlah: RM400.00).\",\"application_id\":34,\"application_number\":\"ALP\\/SUM\\/2026\\/0024\",\"url\":\"\\/permohonan\\/34\"}', NULL, '2026-09-08 01:07:21', '2026-09-08 01:07:21'),
('6f63865f-bd9f-489c-aaef-693528c240d8', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 7, '{\"event\":\"submitted\",\"title\":\"Permohonan dihantar\",\"message\":\"Permohonan baharu telah dihantar dan menunggu semakan Urus Setia.\",\"application_id\":31,\"application_number\":\"ALP\\/SUM\\/2026\\/0021\",\"url\":\"\\/permohonan\\/31\"}', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
('6fd20c6f-f10f-43ed-bba3-fb3b7ddbe1ab', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 2, '{\"event\":\"submitted\",\"title\":\"Permohonan dihantar\",\"message\":\"Permohonan baharu telah dihantar dan menunggu semakan Urus Setia.\",\"application_id\":37,\"application_number\":\"ALP\\/SUM\\/2026\\/0027\",\"url\":\"\\/permohonan\\/37\"}', NULL, '2026-09-07 19:39:47', '2026-09-07 19:39:47'),
('76a9415e-c26a-4828-a6be-834f99225ea8', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 6, '{\"event\":\"approved\",\"title\":\"Permohonan diluluskan\",\"message\":\"Permohonan anda telah diluluskan. Anda boleh mencetak surat\\/ringkasan kelulusan.\",\"application_id\":17,\"application_number\":\"ALP\\/SUM\\/2026\\/0007\",\"url\":\"\\/permohonan\\/17\"}', NULL, '2026-09-06 04:54:46', '2026-09-06 04:54:46'),
('7e39f8e8-5ed0-4fe5-91d4-fee02cc18565', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 7, '{\"event\":\"submitted\",\"title\":\"Permohonan dihantar\",\"message\":\"Permohonan baharu telah dihantar dan menunggu semakan Urus Setia.\",\"application_id\":37,\"application_number\":\"ALP\\/SUM\\/2026\\/0027\",\"url\":\"\\/permohonan\\/37\"}', NULL, '2026-09-07 19:39:47', '2026-09-07 19:39:47'),
('7f9faa57-656a-4ba0-b7fc-740512934835', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 7, '{\"event\":\"report_card_awaiting_pegawai_jp\",\"title\":\"Laporan menunggu pengesahan Pegawai JP\",\"message\":\"Laporan aktiviti telah disemak Admin JP dan menunggu pengesahan Pegawai JP.\",\"application_id\":32,\"application_number\":\"ALP\\/SUM\\/2026\\/0022\",\"url\":\"\\/permohonan\\/32\"}', '2026-09-08 06:34:15', '2026-09-08 06:33:26', '2026-09-08 06:34:15'),
('815dd5fa-3b79-46f1-904d-fe8ce60a37fe', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 10, '{\"event\":\"awaiting_peraku\",\"title\":\"Menunggu perakuan\",\"message\":\"Permohonan menunggu perakuan (NT-003). Aras: Peraku untuk diangkat ke PEPU (TP\\/Pengarah JP).\",\"application_id\":35,\"application_number\":\"ALP\\/SUM\\/2026\\/0025\",\"url\":\"\\/permohonan\\/35\"}', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
('858f81df-9ba1-4829-9e2d-9f2d4f61d522', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 6, '{\"event\":\"approved\",\"title\":\"Permohonan diluluskan\",\"message\":\"Permohonan anda telah diluluskan. Anda boleh mencetak surat\\/ringkasan kelulusan.\",\"application_id\":17,\"application_number\":\"ALP\\/SUM\\/2026\\/0007\",\"url\":\"\\/permohonan\\/17\"}', NULL, '2026-09-06 05:13:48', '2026-09-06 05:13:48'),
('864a3bee-7aa3-456e-9b0d-0ad8502991f3', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 3, '{\"event\":\"approved\",\"title\":\"Permohonan diluluskan\",\"message\":\"Permohonan anda telah diluluskan. Anda boleh mencetak surat\\/ringkasan kelulusan.\",\"application_id\":17,\"application_number\":\"ALP\\/SUM\\/2026\\/0007\",\"url\":\"\\/permohonan\\/17\"}', '2026-09-07 01:18:33', '2026-09-06 05:13:48', '2026-09-07 01:18:33'),
('88bb867b-4484-48a8-a740-fa7e7d530af9', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 10, '{\"event\":\"awaiting_peraku\",\"title\":\"Menunggu perakuan\",\"message\":\"Permohonan menunggu perakuan (NT-003). Aras: Peraku untuk diangkat ke PEPU (TP\\/Pengarah JP).\",\"application_id\":18,\"application_number\":\"ALP\\/SUM\\/2026\\/0008\",\"url\":\"\\/permohonan\\/18\"}', NULL, '2026-09-07 01:42:47', '2026-09-07 01:42:47'),
('8a76bd5f-61df-42dc-8d68-7ac331df724f', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 6, '{\"event\":\"approved\",\"title\":\"Permohonan diluluskan\",\"message\":\"Permohonan anda telah diluluskan. Anda boleh mencetak surat\\/ringkasan kelulusan.\",\"application_id\":18,\"application_number\":\"ALP\\/SUM\\/2026\\/0008\",\"url\":\"\\/permohonan\\/18\"}', NULL, '2026-09-07 01:56:20', '2026-09-07 01:56:20'),
('8ec4005b-4d03-4621-b72b-a923b90423b4', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 3, '{\"event\":\"revision_required\",\"title\":\"Permohonan dikembalikan\",\"message\":\"Permohonan anda dikembalikan untuk pembetulan. Sebab: buatla betul betul\",\"application_id\":27,\"application_number\":\"ALP\\/SUM\\/2026\\/0017\",\"url\":\"\\/permohonan\\/27\"}', '2026-09-07 18:59:29', '2026-09-07 18:59:07', '2026-09-07 18:59:29'),
('8ff111f3-fe55-458e-b373-112dd6d302d6', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 4, '{\"event\":\"payment_voucher\",\"title\":\"Baucar pembayaran\",\"message\":\"Baucar pembayaran untuk permohonan anda sedang disediakan \\/ telah direkod.\",\"application_id\":39,\"application_number\":\"ALP\\/SUM\\/2026\\/0029\",\"url\":\"\\/permohonan\\/39\"}', '2026-09-08 01:13:16', '2026-09-08 01:11:51', '2026-09-08 01:13:16'),
('9292e456-3371-49b4-a4a9-d628ead6f424', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 7, '{\"event\":\"submitted\",\"title\":\"Permohonan dihantar\",\"message\":\"Permohonan baharu telah dihantar dan menunggu semakan Urus Setia.\",\"application_id\":18,\"application_number\":\"ALP\\/SUM\\/2026\\/0008\",\"url\":\"\\/permohonan\\/18\"}', NULL, '2026-09-07 01:51:58', '2026-09-07 01:51:58'),
('93e35e2c-91d0-4062-ab4b-9e6d64988251', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 4, '{\"event\":\"approved\",\"title\":\"Permohonan diluluskan\",\"message\":\"Permohonan anda telah diluluskan. Anda boleh mencetak surat\\/ringkasan kelulusan.\",\"application_id\":39,\"application_number\":\"ALP\\/SUM\\/2026\\/0029\",\"url\":\"\\/permohonan\\/39\"}', '2026-09-08 00:55:07', '2026-09-08 00:54:17', '2026-09-08 00:55:07'),
('9bde6e93-b0d5-458a-94c4-86e87170af78', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 6, '{\"event\":\"approved\",\"title\":\"Permohonan diluluskan\",\"message\":\"Permohonan anda telah diluluskan. Anda boleh mencetak surat\\/ringkasan kelulusan.\",\"application_id\":32,\"application_number\":\"ALP\\/SUM\\/2026\\/0022\",\"url\":\"\\/permohonan\\/32\"}', NULL, '2026-09-08 06:30:15', '2026-09-08 06:30:15'),
('9e8e38c7-527a-4d1a-9b9f-bcd46a0f2ad4', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 6, '{\"event\":\"report_card_approved\",\"title\":\"Laporan aktiviti disahkan\",\"message\":\"Laporan aktiviti anda telah disahkan Pegawai JP.\",\"application_id\":34,\"application_number\":\"ALP\\/SUM\\/2026\\/0024\",\"url\":\"\\/permohonan\\/34\"}', NULL, '2026-09-08 06:16:28', '2026-09-08 06:16:28'),
('a185b1fc-e765-495d-909e-b163b5d5ea45', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 3, '{\"event\":\"rejected\",\"title\":\"Permohonan ditolak\",\"message\":\"Permohonan anda telah ditolak. Sebab: Ditolak (simulasi)\",\"application_id\":35,\"application_number\":\"ALP\\/SUM\\/2026\\/0025\",\"url\":\"\\/permohonan\\/35\"}', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
('a50e247f-e5bd-472d-9e38-ffa24da6eac4', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 8, '{\"event\":\"awaiting_payment\",\"title\":\"Sedia untuk proses bayaran\",\"message\":\"Permohonan telah diluluskan PEPU dan sedia untuk proses bayaran. Sila sediakan baucar (jumlah: RM350.00).\",\"application_id\":32,\"application_number\":\"ALP\\/SUM\\/2026\\/0022\",\"url\":\"\\/permohonan\\/32\"}', '2026-09-08 06:30:52', '2026-09-08 06:30:15', '2026-09-08 06:30:52'),
('a7749993-0dc8-4f2b-b1af-5f0d14f350fe', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 8, '{\"event\":\"awaiting_payment\",\"title\":\"Sedia untuk proses bayaran\",\"message\":\"Permohonan telah diluluskan PEPU dan sedia untuk proses bayaran. Sila sediakan baucar (jumlah: RM100.00).\",\"application_id\":38,\"application_number\":\"ALP\\/SUM\\/2026\\/0028\",\"url\":\"\\/permohonan\\/38\"}', NULL, '2026-09-08 01:07:21', '2026-09-08 01:07:21'),
('b0da4714-6181-4854-978a-aed4fb301f73', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 7, '{\"event\":\"submitted\",\"title\":\"Permohonan dihantar\",\"message\":\"Permohonan baharu telah dihantar dan menunggu semakan Urus Setia.\",\"application_id\":32,\"application_number\":\"ALP\\/SUM\\/2026\\/0022\",\"url\":\"\\/permohonan\\/32\"}', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
('b18c50d0-4ddc-43ee-ada9-3a07c1b472da', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 3, '{\"event\":\"approved\",\"title\":\"Permohonan diluluskan\",\"message\":\"Permohonan anda telah diluluskan. Anda boleh mencetak surat\\/ringkasan kelulusan.\",\"application_id\":33,\"application_number\":\"ALP\\/SUM\\/2026\\/0023\",\"url\":\"\\/permohonan\\/33\"}', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
('b395c44a-0d05-4c81-9dee-3e8d8c56b15b', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 3, '{\"event\":\"payment_voucher\",\"title\":\"Baucar pembayaran\",\"message\":\"Baucar pembayaran untuk permohonan anda sedang disediakan \\/ telah direkod.\",\"application_id\":34,\"application_number\":\"ALP\\/SUM\\/2026\\/0024\",\"url\":\"\\/permohonan\\/34\"}', '2026-09-08 06:08:05', '2026-09-07 20:01:26', '2026-09-08 06:08:05'),
('b4ca5d72-5eac-4031-b15a-eff0c87f3493', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 10, '{\"event\":\"awaiting_peraku\",\"title\":\"Menunggu perakuan\",\"message\":\"Permohonan menunggu perakuan (NT-003). Aras: Peraku untuk diangkat ke PEPU (TP\\/Pengarah JP).\",\"application_id\":18,\"application_number\":\"ALP\\/SUM\\/2026\\/0008\",\"url\":\"\\/permohonan\\/18\"}', NULL, '2026-09-07 01:52:45', '2026-09-07 01:52:45'),
('b968503c-c99b-4094-9a71-168a04820e63', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 3, '{\"event\":\"revision_required\",\"title\":\"Permohonan dikembalikan\",\"message\":\"Permohonan anda dikembalikan untuk pembetulan. Sebab: PERSATUAN PENAH MOHON\",\"application_id\":18,\"application_number\":\"ALP\\/SUM\\/2026\\/0008\",\"url\":\"\\/permohonan\\/18\"}', NULL, '2026-09-07 01:47:05', '2026-09-07 01:47:05'),
('ba424a97-be6d-4bed-ac03-5628f2bb3788', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 7, '{\"event\":\"submitted\",\"title\":\"Permohonan dihantar\",\"message\":\"Permohonan baharu telah dihantar dan menunggu semakan Urus Setia.\",\"application_id\":33,\"application_number\":\"ALP\\/SUM\\/2026\\/0023\",\"url\":\"\\/permohonan\\/33\"}', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
('bc5bd4e9-8489-4a5b-a318-1d02ea5bcc6d', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 11, '{\"event\":\"awaiting_pepu\",\"title\":\"Menunggu kelulusan PEPU\",\"message\":\"Permohonan menunggu kelulusan PEPU \\/ aras seterusnya (NT-004). Aras: Kelulusan PEPU \\/ Pengurusan Tertinggi.\",\"application_id\":38,\"application_number\":\"ALP\\/SUM\\/2026\\/0028\",\"url\":\"\\/permohonan\\/38\"}', NULL, '2026-09-07 23:11:44', '2026-09-07 23:11:44'),
('c5afef7c-dcba-4673-a591-cf3ca9f5b381', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 6, '{\"event\":\"report_card_approved\",\"title\":\"Laporan aktiviti disahkan\",\"message\":\"Laporan aktiviti anda telah disahkan Pegawai JP.\",\"application_id\":32,\"application_number\":\"ALP\\/SUM\\/2026\\/0022\",\"url\":\"\\/permohonan\\/32\"}', NULL, '2026-09-08 06:34:28', '2026-09-08 06:34:28'),
('cc229687-50d8-46da-b297-24a7b0ae96bf', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 6, '{\"event\":\"revision_required\",\"title\":\"Permohonan dikembalikan\",\"message\":\"Permohonan anda dikembalikan untuk pembetulan. Sebab: PERSATUAN PENAH MOHON\",\"application_id\":18,\"application_number\":\"ALP\\/SUM\\/2026\\/0008\",\"url\":\"\\/permohonan\\/18\"}', NULL, '2026-09-07 01:47:05', '2026-09-07 01:47:05'),
('cdc7f814-9dee-45cf-910b-1edc286ab1c8', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 7, '{\"event\":\"submitted\",\"title\":\"Permohonan dihantar\",\"message\":\"Permohonan baharu telah dihantar dan menunggu semakan Urus Setia.\",\"application_id\":35,\"application_number\":\"ALP\\/SUM\\/2026\\/0025\",\"url\":\"\\/permohonan\\/35\"}', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
('d33f9dc1-de4f-4307-a1dd-ebc693d99250', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 7, '{\"event\":\"submitted\",\"title\":\"Permohonan dihantar\",\"message\":\"Permohonan baharu telah dihantar dan menunggu semakan Urus Setia.\",\"application_id\":38,\"application_number\":\"ALP\\/SUM\\/2026\\/0028\",\"url\":\"\\/permohonan\\/38\"}', NULL, '2026-09-07 20:34:00', '2026-09-07 20:34:00'),
('d35854b8-9f8d-4ecb-98c1-7eaa8a353b5e', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 3, '{\"event\":\"payment_voucher\",\"title\":\"Baucar pembayaran\",\"message\":\"Baucar telah disedia. Sila muat naik laporan aktiviti dalam 1 bulan (akhir: 08\\/10\\/2026).\",\"application_id\":32,\"application_number\":\"ALP\\/SUM\\/2026\\/0022\",\"url\":\"\\/permohonan\\/32\"}', '2026-09-08 06:32:01', '2026-09-08 06:31:10', '2026-09-08 06:32:01'),
('d3acc866-6365-4b08-a9ed-fa2f5571c9d5', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 8, '{\"event\":\"awaiting_payment\",\"title\":\"Sedia untuk proses bayaran\",\"message\":\"Permohonan telah diluluskan PEPU dan sedia untuk proses bayaran. Sila sediakan baucar (jumlah: RM3,000.00).\",\"application_id\":17,\"application_number\":\"ALP\\/SUM\\/2026\\/0007\",\"url\":\"\\/permohonan\\/17\"}', NULL, '2026-09-08 01:07:21', '2026-09-08 01:07:21'),
('d6c2dbb2-c812-41cb-9466-fa540fdacd00', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 10, '{\"event\":\"awaiting_peraku\",\"title\":\"Menunggu perakuan\",\"message\":\"Permohonan menunggu perakuan (NT-003). Aras: Pengesyoran PEPU.\",\"application_id\":39,\"application_number\":\"ALP\\/SUM\\/2026\\/0029\",\"url\":\"\\/permohonan\\/39\"}', '2026-09-08 00:51:59', '2026-09-08 00:51:30', '2026-09-08 00:51:59'),
('d8411d69-60f5-4b3b-9db7-ed768e69da2c', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 7, '{\"event\":\"submitted\",\"title\":\"Permohonan dihantar\",\"message\":\"Permohonan baharu telah dihantar dan menunggu semakan Urus Setia.\",\"application_id\":34,\"application_number\":\"ALP\\/SUM\\/2026\\/0024\",\"url\":\"\\/permohonan\\/34\"}', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
('db427a03-f568-45dd-b2be-f3fc499a107d', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 7, '{\"event\":\"awaiting_pegawai_jp\",\"title\":\"Kemaskini permohonan\",\"message\":\"Permohonan telah disemak Admin JP dan menunggu pengesyoran Pegawai JP kepada Pengarah JP.\",\"application_id\":39,\"application_number\":\"ALP\\/SUM\\/2026\\/0029\",\"url\":\"\\/permohonan\\/39\"}', '2026-09-08 00:51:16', '2026-09-08 00:50:43', '2026-09-08 00:51:16'),
('db7556d5-9c0d-44b0-8603-e6ed4baa2f07', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 10, '{\"event\":\"awaiting_peraku\",\"title\":\"Menunggu perakuan\",\"message\":\"Permohonan menunggu perakuan (NT-003). Aras: Peraku untuk diangkat ke PEPU (TP\\/Pengarah JP).\",\"application_id\":32,\"application_number\":\"ALP\\/SUM\\/2026\\/0022\",\"url\":\"\\/permohonan\\/32\"}', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
('dcf137bc-0e48-4bdd-a0f3-694ca37f4bbc', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 11, '{\"event\":\"awaiting_pepu\",\"title\":\"Menunggu kelulusan PEPU\",\"message\":\"Permohonan menunggu kelulusan PEPU \\/ aras seterusnya (NT-004). Aras: Kelulusan PEPU \\/ Pengurusan Tertinggi.\",\"application_id\":34,\"application_number\":\"ALP\\/SUM\\/2026\\/0024\",\"url\":\"\\/permohonan\\/34\"}', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
('dedefddf-a0b9-4e10-8051-57aae4006ce2', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 7, '{\"event\":\"report_card_awaiting_pegawai_jp\",\"title\":\"Laporan menunggu pengesahan Pegawai JP\",\"message\":\"Laporan aktiviti telah disemak Admin JP dan menunggu pengesahan Pegawai JP.\",\"application_id\":34,\"application_number\":\"ALP\\/SUM\\/2026\\/0024\",\"url\":\"\\/permohonan\\/34\"}', '2026-09-08 06:16:12', '2026-09-08 06:15:44', '2026-09-08 06:16:12'),
('e07a2710-0fff-452d-9193-470f1caec7cb', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 8, '{\"event\":\"awaiting_payment\",\"title\":\"Sedia untuk proses bayaran\",\"message\":\"Permohonan telah diluluskan PEPU dan sedia untuk proses bayaran. Sila sediakan baucar (jumlah: RM350.00).\",\"application_id\":33,\"application_number\":\"ALP\\/SUM\\/2026\\/0023\",\"url\":\"\\/permohonan\\/33\"}', NULL, '2026-09-08 01:07:21', '2026-09-08 01:07:21'),
('e387c4dd-843f-48d4-aef5-21075b6c51b6', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 3, '{\"event\":\"payment_voucher\",\"title\":\"Baucar pembayaran\",\"message\":\"Baucar pembayaran untuk permohonan anda sedang disediakan \\/ telah direkod.\",\"application_id\":38,\"application_number\":\"ALP\\/SUM\\/2026\\/0028\",\"url\":\"\\/permohonan\\/38\"}', '2026-09-07 23:14:34', '2026-09-07 23:14:00', '2026-09-07 23:14:34'),
('e3a71a33-214d-49da-853d-0f13e88479d4', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 11, '{\"event\":\"awaiting_pepu\",\"title\":\"Menunggu kelulusan PEPU\",\"message\":\"Permohonan menunggu kelulusan PEPU \\/ aras seterusnya (NT-004). Aras: Kelulusan PEPU \\/ Pengurusan Tertinggi.\",\"application_id\":31,\"application_number\":\"ALP\\/SUM\\/2026\\/0021\",\"url\":\"\\/permohonan\\/31\"}', NULL, '2026-09-07 23:11:24', '2026-09-07 23:11:24'),
('e69e3e4c-1a44-491e-aac7-42814baf1d61', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 11, '{\"event\":\"awaiting_pepu\",\"title\":\"Menunggu kelulusan PEPU\",\"message\":\"Permohonan menunggu kelulusan PEPU \\/ aras seterusnya (NT-004). Aras: Kelulusan PEPU \\/ Pengurusan Tertinggi.\",\"application_id\":18,\"application_number\":\"ALP\\/SUM\\/2026\\/0008\",\"url\":\"\\/permohonan\\/18\"}', NULL, '2026-09-07 01:44:58', '2026-09-07 01:44:58'),
('eec6dfc2-a348-451f-9cb3-7c0d886dd0f8', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 11, '{\"event\":\"awaiting_pepu\",\"title\":\"Menunggu kelulusan PEPU\",\"message\":\"Permohonan menunggu kelulusan PEPU \\/ aras seterusnya (NT-004). Aras: Kelulusan PEPU \\/ Pengurusan Tertinggi.\",\"application_id\":33,\"application_number\":\"ALP\\/SUM\\/2026\\/0023\",\"url\":\"\\/permohonan\\/33\"}', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
('f0010db1-0ad3-4078-9a83-94dd0794bb81', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 2, '{\"event\":\"report_card_submitted\",\"title\":\"Laporan aktiviti menunggu semakan\",\"message\":\"Laporan aktiviti telah dimuat naik oleh ALP dan menunggu semakan Admin JP.\",\"application_id\":32,\"application_number\":\"ALP\\/SUM\\/2026\\/0022\",\"url\":\"\\/permohonan\\/32\"}', '2026-09-08 06:33:09', '2026-09-08 06:32:25', '2026-09-08 06:33:09'),
('f2fcb90e-9916-42a9-a7ec-2e8554668082', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 3, '{\"event\":\"approved\",\"title\":\"Permohonan diluluskan\",\"message\":\"Permohonan anda telah diluluskan. Anda boleh mencetak surat\\/ringkasan kelulusan.\",\"application_id\":34,\"application_number\":\"ALP\\/SUM\\/2026\\/0024\",\"url\":\"\\/permohonan\\/34\"}', NULL, '2026-09-07 06:22:23', '2026-09-07 06:22:23'),
('f8237f1a-dafc-42d8-9a08-80a147fa43ac', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 6, '{\"event\":\"revision_required\",\"title\":\"Permohonan dikembalikan\",\"message\":\"Permohonan anda dikembalikan untuk pembetulan. Sebab: buatla betul betul\",\"application_id\":27,\"application_number\":\"ALP\\/SUM\\/2026\\/0017\",\"url\":\"\\/permohonan\\/27\"}', NULL, '2026-09-07 18:59:08', '2026-09-07 18:59:08'),
('fa72278f-ea9d-413f-8c45-70c5b87cc604', 'App\\Notifications\\ApplicationWorkflowNotification', 'App\\Models\\User', 6, '{\"event\":\"payment_voucher\",\"title\":\"Baucar pembayaran\",\"message\":\"Baucar pembayaran untuk permohonan anda sedang disediakan \\/ telah direkod.\",\"application_id\":38,\"application_number\":\"ALP\\/SUM\\/2026\\/0028\",\"url\":\"\\/permohonan\\/38\"}', NULL, '2026-09-07 23:14:00', '2026-09-07 23:14:00');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'dashboard.view', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(2, 'dashboard.executive', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(3, 'users.view', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(4, 'users.create', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(5, 'users.update', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(6, 'users.deactivate', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(7, 'users.assign_role', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(8, 'roles.view', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(9, 'alps.view', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(10, 'alps.create', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(11, 'alps.update', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(12, 'alps.deactivate', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(13, 'financial_years.view', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(14, 'financial_years.create', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(15, 'financial_years.update', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(16, 'financial_years.manage', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(17, 'allocations.view', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(18, 'budget.view_all', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(19, 'allocations.request.create', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(20, 'allocations.request.update', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(21, 'allocations.request.submit', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(22, 'allocations.approve', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(23, 'allocations.reject', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(24, 'allocations.return', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(25, 'adjustments.request.create', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(26, 'adjustments.request.update', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(27, 'adjustments.request.submit', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(28, 'adjustments.approve', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(29, 'adjustments.reject', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(30, 'adjustments.return', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(31, 'applications.create', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(32, 'applications.view_all', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(33, 'applications.review.secretariat', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(34, 'applications.review.finance', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(35, 'applications.review.technical', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(36, 'applications.approve', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(37, 'applications.reject', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(38, 'approval_matrix.view', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(39, 'approval_matrix.manage', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(40, 'projects.view', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(41, 'projects.view_all', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(42, 'projects.update', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(43, 'projects.progress', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(44, 'projects.milestones', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(45, 'projects.complete', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(46, 'projects.close', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(47, 'expenses.view', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(48, 'expenses.create', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(49, 'expenses.update', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(50, 'expenses.submit', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(51, 'expenses.verify', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(52, 'expenses.reject', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(53, 'expenses.return', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(54, 'expenses.documents.view', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(55, 'expenses.documents.manage', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(56, 'refunds.view', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(57, 'refunds.create', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(58, 'refunds.update', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(59, 'refunds.submit', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(60, 'refunds.verify', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(61, 'refunds.reject', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(62, 'refunds.return', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(63, 'projects.closure-documents.view', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(64, 'projects.closure-documents.manage', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(65, 'project-reports.create', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(66, 'project-reports.review', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(67, 'dashboard.finance', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(68, 'reports.view', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(69, 'reports.financial', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(70, 'reports.applications', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(71, 'reports.projects', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(72, 'reports.csr', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(73, 'reports.audit', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(74, 'reports.export', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(75, 'settings.manage', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(76, 'allocations.manage', 'web', '2026-09-07 06:21:20', '2026-09-07 06:21:20'),
(77, 'payments.view', 'web', '2026-09-07 06:21:20', '2026-09-07 06:21:20'),
(78, 'payments.manage', 'web', '2026-09-07 06:21:20', '2026-09-07 06:21:20'),
(79, 'payments.jkew_scope', 'web', '2026-09-07 06:21:20', '2026-09-07 06:21:20'),
(80, 'applications.create_on_behalf', 'web', '2026-09-07 19:24:10', '2026-09-07 19:24:10');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_number` varchar(40) NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `alp_id` bigint(20) UNSIGNED NOT NULL,
  `financial_year_id` bigint(20) UNSIGNED NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `project_type` varchar(20) NOT NULL,
  `approved_amount` decimal(15,2) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `actual_start_date` date DEFAULT NULL,
  `actual_completion_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'not_started',
  `progress_percent` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_documents`
--

CREATE TABLE `project_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `project_expense_id` bigint(20) UNSIGNED DEFAULT NULL,
  `project_expense_refund_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category` varchar(30) NOT NULL,
  `document_type` varchar(40) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `stored_path` varchar(255) NOT NULL,
  `mime_type` varchar(150) DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `sha256` varchar(64) DEFAULT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_document_requirements`
--

CREATE TABLE `project_document_requirements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_type` varchar(20) NOT NULL,
  `category` varchar(30) NOT NULL,
  `document_type` varchar(40) NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_document_requirements`
--

INSERT INTO `project_document_requirements` (`id`, `project_type`, `category`, `document_type`, `is_required`, `active`, `created_at`, `updated_at`) VALUES
(1, 'csr', 'closure_evidence', 'final_report', 1, 1, '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(2, 'csr', 'closure_evidence', 'completion_photo', 1, 1, '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(3, 'csr', 'closure_evidence', 'program_attendance', 0, 1, '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(4, 'development', 'closure_evidence', 'final_report', 1, 1, '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(5, 'development', 'closure_evidence', 'completion_certificate', 1, 1, '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(6, 'development', 'closure_evidence', 'completion_photo', 1, 1, '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(7, 'development', 'closure_evidence', 'technical_completion', 0, 1, '2026-09-03 23:14:28', '2026-09-03 23:14:28');

-- --------------------------------------------------------

--
-- Table structure for table `project_expenses`
--

CREATE TABLE `project_expenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `expense_date` date NOT NULL,
  `reference_number` varchar(100) NOT NULL,
  `payee` varchar(255) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'draft',
  `revision_number` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `submitted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `rejected_by` bigint(20) UNSIGNED DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `returned_by` bigint(20) UNSIGNED DEFAULT NULL,
  `returned_at` timestamp NULL DEFAULT NULL,
  `return_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_expense_histories`
--

CREATE TABLE `project_expense_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_expense_id` bigint(20) UNSIGNED NOT NULL,
  `from_status` varchar(30) DEFAULT NULL,
  `to_status` varchar(30) NOT NULL,
  `changed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_expense_refunds`
--

CREATE TABLE `project_expense_refunds` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_expense_id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `reason` text DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `refund_date` date NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'draft',
  `revision_number` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `submitted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `rejected_by` bigint(20) UNSIGNED DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `returned_by` bigint(20) UNSIGNED DEFAULT NULL,
  `returned_at` timestamp NULL DEFAULT NULL,
  `return_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_expense_refund_histories`
--

CREATE TABLE `project_expense_refund_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_expense_refund_id` bigint(20) UNSIGNED NOT NULL,
  `from_status` varchar(30) DEFAULT NULL,
  `to_status` varchar(30) NOT NULL,
  `changed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_milestones`
--

CREATE TABLE `project_milestones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `target_date` date DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `remarks` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_progress_histories`
--

CREATE TABLE `project_progress_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `progress_percent` tinyint(3) UNSIGNED NOT NULL,
  `status` varchar(20) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_reports`
--

CREATE TABLE `project_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `summary` text DEFAULT NULL,
  `outcome` text DEFAULT NULL,
  `beneficiary_count` int(10) UNSIGNED DEFAULT NULL,
  `impact_summary` text DEFAULT NULL,
  `completion_summary` text DEFAULT NULL,
  `issues` text DEFAULT NULL,
  `lessons_learned` text DEFAULT NULL,
  `final_remarks` text DEFAULT NULL,
  `submitted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_sequences`
--

CREATE TABLE `project_sequences` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `year` smallint(5) UNSIGNED NOT NULL,
  `type` varchar(20) NOT NULL,
  `last_number` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_sequences`
--

INSERT INTO `project_sequences` (`id`, `year`, `type`, `last_number`, `created_at`, `updated_at`) VALUES
(1, 2026, 'CSR', 3, '2026-09-03 23:14:32', '2026-09-03 23:14:32'),
(4, 2026, 'DEV', 1, '2026-09-03 23:14:32', '2026-09-03 23:14:32');

-- --------------------------------------------------------

--
-- Table structure for table `project_status_histories`
--

CREATE TABLE `project_status_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `from_status` varchar(20) DEFAULT NULL,
  `to_status` varchar(20) NOT NULL,
  `changed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recipients`
--

CREATE TABLE `recipients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `ros_number` varchar(100) NOT NULL,
  `bank_account` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recipients`
--

INSERT INTO `recipients` (`id`, `name`, `ros_number`, `bank_account`, `address`, `created_at`, `updated_at`) VALUES
(1, 'PERSATUAN RAKYAT PERHATIN', 'AKAUN-16645262902', '16645262902', NULL, '2026-09-05 04:55:23', '2026-09-05 04:55:23'),
(2, 'PERSATUAN RAKYAT PERHATIN', 'AKAUN-11212I1211243', '11212I1211243', NULL, '2026-09-05 05:07:19', '2026-09-05 05:07:19'),
(3, 'PERSATUAN BELIA', 'AKAUN-165442993332', '165442993332', NULL, '2026-09-05 05:52:26', '2026-09-05 05:52:26'),
(4, 'AA', 'AKAUN-1212', '1212', NULL, '2026-09-05 05:53:21', '2026-09-05 05:53:21'),
(5, 'PERSATUAN BELIA', 'AKAUN-121883434343', '121883434343', NULL, '2026-09-05 05:55:19', '2026-09-05 05:55:19'),
(6, 'PERSATUAN BELIA PROGRESIF', 'AKAUN-165626727177', '165626727177', NULL, '2026-09-05 06:41:54', '2026-09-05 06:41:54'),
(7, 'PERSATUAN WARGA EMAS KZ', 'AKAUN-1646152564748', '1646152564748', NULL, '2026-09-07 01:09:18', '2026-09-07 01:09:18'),
(8, 'final 1', '1234', '1234567890', 'kl', '2026-09-07 06:10:47', '2026-09-08 00:32:39'),
(9, 'Persatuan Komuniti Simulasi KL', 'ROS-SIM-741509', '9876543210', 'No. 10, Jalan Raja Laut, 50350 Kuala Lumpur', '2026-09-07 06:21:36', '2026-09-07 06:36:19'),
(10, 'test 2', '123456', '123', 'kl', '2026-09-07 06:27:53', '2026-09-07 06:27:53'),
(11, 'PERSATUAN BELIA BELIAWANIS CANTIK', 'A33983', '164164816993', 'JALAN RAJA LAUT, POSKOD 50350, KUALA LUMPUR', '2026-09-07 19:38:49', '2026-09-07 19:38:49'),
(12, 'faizal warga emas', '44444444', '55555555555', '02 jalan butik kl', '2026-09-07 20:32:32', '2026-09-07 20:32:32');

-- --------------------------------------------------------

--
-- Table structure for table `report_card_reviews`
--

CREATE TABLE `report_card_reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `reviewer_id` bigint(20) UNSIGNED NOT NULL,
  `stage` varchar(20) NOT NULL,
  `decision` varchar(30) NOT NULL,
  `comments` text DEFAULT NULL,
  `reviewed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `report_card_reviews`
--

INSERT INTO `report_card_reviews` (`id`, `application_id`, `reviewer_id`, `stage`, `decision`, `comments`, `reviewed_at`, `created_at`) VALUES
(1, 34, 2, 'admin_jp', 'recommend', NULL, '2026-09-08 06:15:44', '2026-09-08 06:15:44'),
(2, 34, 7, 'pegawai_jp', 'recommend', NULL, '2026-09-08 06:16:28', '2026-09-08 06:16:28'),
(3, 32, 2, 'admin_jp', 'recommend', NULL, '2026-09-08 06:33:26', '2026-09-08 06:33:26'),
(4, 32, 7, 'pegawai_jp', 'recommend', NULL, '2026-09-08 06:34:27', '2026-09-08 06:34:27');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(2, 'system_admin', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(3, 'alp', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(4, 'urussetia_alp', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(5, 'pegawai_urussetia', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(6, 'pegawai_kewangan', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(7, 'pegawai_teknikal', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(8, 'pelulus', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(9, 'pengurusan', 'web', '2026-09-03 23:14:28', '2026-09-03 23:14:28'),
(10, 'pegawai_jkew', 'web', '2026-09-07 06:21:20', '2026-09-07 06:21:20');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(2, 9),
(3, 2),
(4, 2),
(5, 2),
(6, 2),
(7, 2),
(8, 2),
(9, 2),
(9, 4),
(9, 5),
(9, 6),
(9, 7),
(9, 8),
(9, 9),
(10, 2),
(11, 2),
(12, 2),
(13, 2),
(13, 9),
(14, 2),
(15, 2),
(16, 2),
(17, 2),
(17, 5),
(17, 6),
(17, 8),
(17, 9),
(18, 2),
(18, 6),
(18, 8),
(18, 9),
(19, 6),
(20, 6),
(21, 6),
(22, 8),
(23, 8),
(24, 8),
(25, 6),
(26, 6),
(27, 6),
(28, 8),
(29, 8),
(30, 8),
(31, 3),
(31, 4),
(32, 2),
(32, 5),
(32, 6),
(32, 7),
(32, 8),
(32, 9),
(32, 10),
(33, 2),
(33, 5),
(34, 6),
(35, 7),
(36, 8),
(36, 9),
(37, 8),
(37, 9),
(38, 2),
(38, 9),
(39, 2),
(40, 3),
(40, 4),
(41, 2),
(41, 5),
(41, 6),
(41, 7),
(41, 8),
(41, 9),
(42, 5),
(43, 5),
(44, 5),
(45, 5),
(46, 8),
(47, 5),
(47, 6),
(47, 8),
(48, 6),
(49, 6),
(50, 6),
(51, 8),
(52, 8),
(53, 8),
(54, 2),
(54, 5),
(54, 6),
(54, 7),
(54, 8),
(54, 9),
(55, 6),
(56, 2),
(56, 5),
(56, 6),
(56, 7),
(56, 8),
(56, 9),
(57, 6),
(58, 6),
(59, 6),
(60, 8),
(61, 8),
(62, 8),
(63, 2),
(63, 5),
(63, 6),
(63, 7),
(63, 8),
(63, 9),
(64, 5),
(65, 5),
(66, 8),
(67, 6),
(67, 8),
(67, 9),
(68, 2),
(68, 3),
(68, 4),
(68, 5),
(68, 6),
(68, 7),
(68, 8),
(68, 9),
(68, 10),
(69, 6),
(69, 8),
(69, 9),
(70, 2),
(70, 5),
(70, 9),
(70, 10),
(71, 2),
(71, 5),
(71, 6),
(71, 7),
(71, 8),
(71, 9),
(72, 5),
(72, 9),
(73, 2),
(73, 9),
(74, 2),
(74, 3),
(74, 4),
(74, 5),
(74, 6),
(74, 8),
(74, 9),
(74, 10),
(75, 2),
(76, 2),
(77, 2),
(77, 6),
(77, 8),
(77, 9),
(77, 10),
(78, 6),
(79, 10),
(80, 2);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('7VviI7luAK7JCsy0eSunVsHSzJBkPGWNrUUqSNm0', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiM0RvS2puQUFrakhtRkZ6eGRTZ2R6alNsRG1QZ3h3bHVoYWJLQzBndCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly9sb2NhbGhvc3Q6ODA4MC9BbHAyL3B1YmxpYy9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=', 1788987256),
('d2yRgUrCLeFaSShPj58wUKiuO0vkRXEZtyI8zOkH', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiQzR4QThOSWoxN3lVR3I2T2E0RUJweWd0ZkVQQzJGN2JnWmZ3QkZETyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly9sb2NhbGhvc3Q6ODA4MC9BbHAyL3B1YmxpYy9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6MzoidXJsIjthOjE6e3M6ODoiaW50ZW5kZWQiO3M6NDM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODA4MC9BbHAyL3B1YmxpYy9kYXNoYm9hcmQiO319', 1788970636),
('LR74ulHtoEaMUs6sPLPd4OpyLFd4hkGKLO4mgB2Q', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoibTAxZHg2S2liUXkzeUthektkUTFjT2xBRVFmTzlaZEJ6UzBrUGZhdiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0MzoiaHR0cDovL2xvY2FsaG9zdDo4MDgwL0FscDIvcHVibGljL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM5OiJodHRwOi8vbG9jYWxob3N0OjgwODAvQWxwMi9wdWJsaWMvbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1788986891),
('Oji8pns3KO1utJ5DeAK6LguUQ5jZAdvxzYgGUoKd', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZ1ZXN2R1QlRvMGNWMHFXMmJpMDdaTExzazJ4VDZFRnIycFhWZUtYVSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODA4MC9BbHAyL3B1YmxpYy9wZXJtb2hvbmFuLzM4IjtzOjU6InJvdXRlIjtzOjE3OiJhcHBsaWNhdGlvbnMuc2hvdyI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM7fQ==', 1789026555),
('RNl2LHpa4Sour2g34C9YwMLEjJemhoGNGKlTRWk0', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.2.11 Chrome/142.0.7444.265 Electron/39.8.1 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoidTBURFYzRXozekRDR1M3REZrdGZpd01rOE1WQ3hFM25tWXlJYmJGRiI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjQzOiJodHRwOi8vbG9jYWxob3N0OjgwODAvQWxwMi9wdWJsaWMvZGFzaGJvYXJkIjtzOjU6InJvdXRlIjtzOjk6ImRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1789019876),
('S5HN8OAxi2Nnl3szC1cmGfwaBsl5cattdsbBhjZL', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRTlBOHd2WkhVQkNCTUdNVlp6VDlmOXRHRWh1eXM4RWdObkl5d2lqeSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMjoiaHR0cDovLzEyNy4wLjAuMTo4MTI0L3Blcm1vaG9uYW4iO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyNzoiaHR0cDovLzEyNy4wLjAuMTo4MTI0L2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1788967080),
('zs1sWlYxm5bkbzBVTUqwvZXqFJgfzs5ToQC2555q', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.2.11 Chrome/142.0.7444.265 Electron/39.8.1 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNWFDdnRXb2dSVkVmOGx2alZvQWtpT3B4ZG5HN0pHN0ZjZkFjUGJDZyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODA4MC9BbHAyL3B1YmxpYy9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Mjt9', 1788971575);

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'urs_policy_enabled', '1', '2026-09-05 04:24:46', '2026-09-05 04:24:46'),
(2, 'urs_max_annual_allocation', '30000.00', '2026-09-05 04:24:46', '2026-09-05 04:24:46'),
(3, 'urs_max_per_application', '3000.00', '2026-09-05 04:24:46', '2026-09-05 04:24:46'),
(4, 'urs_period_quota', '10000.00', '2026-09-05 04:24:46', '2026-09-05 04:24:46'),
(5, 'urs_overdue_days', '14', '2026-09-05 04:24:46', '2026-09-05 04:24:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `unit` varchar(255) DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `avatar_icon` varchar(40) DEFAULT NULL,
  `alp_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `status`, `unit`, `avatar_path`, `avatar_icon`, `alp_id`, `created_by`, `must_change_password`, `last_login_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'superadmin@dbkl.test', NULL, '$2y$12$E11OP7/g.OZkleaLFxc/MOfuyvpv4qYWbrqGFjGbaaVuee2LF34L.', 'active', 'DBKL', NULL, NULL, NULL, NULL, 0, '2026-09-09 21:57:55', NULL, '2026-09-03 23:14:28', '2026-09-09 21:57:55'),
(2, 'Pentadbir Sistem', 'sysadmin@dbkl.test', NULL, '$2y$12$wLAqBWIrMhv6ZCETZMKq2uYU3FTqKEHrE7tEDlqzTRoSryiRhv0pm', 'active', 'DBKL', NULL, NULL, NULL, NULL, 0, '2026-09-09 22:11:36', NULL, '2026-09-03 23:14:29', '2026-09-09 22:11:36'),
(3, 'YBhg. Datuk Muhammad Azmi bin Mohd Zain', 'alp01@dbkl.test', NULL, '$2y$12$4hbreCcvo2vBxKJ.AosY4uMeedzWFhdvyQbdMoiA9YtzhSFz3VtO2', 'active', NULL, NULL, NULL, 1, NULL, 0, '2026-09-09 23:48:55', NULL, '2026-09-03 23:14:29', '2026-09-09 23:48:55'),
(4, 'Y.A.D Raja Dato\' Muzaffar bin Raja Redzwa', 'alp02@dbkl.test', NULL, '$2y$12$zkAR4rx7KlvrH3k2SjUL4OcxjVdp1o1gRoiDCTdrMMEhSd5b.c8Ze', 'active', NULL, NULL, NULL, 2, NULL, 0, '2026-09-08 05:57:47', NULL, '2026-09-03 23:14:29', '2026-09-08 05:57:47'),
(5, 'Y.A.D Dato\' Setia Haji Haris bin Kasim', 'alp03@dbkl.test', NULL, '$2y$12$FjHd3M0YLJ.qNCwXQG93wuv3ukV4/EsVV6595vYHiICIZdwiOaghy', 'active', NULL, NULL, NULL, 3, NULL, 0, NULL, NULL, '2026-09-03 23:14:30', '2026-09-05 04:24:47'),
(6, 'Urus Setia ALP-01', 'urussetiaalp@dbkl.test', NULL, '$2y$12$M6UEgsXwfhHzCReoQTb2Yurw4aZbalHsr0/w87BnQZpj7g8C/Gyc.', 'active', NULL, NULL, NULL, 1, NULL, 0, NULL, NULL, '2026-09-03 23:14:30', '2026-09-03 23:14:30'),
(7, 'Pegawai Urus Setia DBKL', 'urussetia@dbkl.test', NULL, '$2y$12$RdrxsagLFlkLwxwm4mNcD.CuMTZIxjqtidhbHx9dVg1PoVjYC3WQa', 'active', 'DBKL', NULL, NULL, NULL, NULL, 0, '2026-09-09 22:22:59', NULL, '2026-09-03 23:14:30', '2026-09-09 22:22:59'),
(8, 'Pegawai Kewangan', 'kewangan@dbkl.test', NULL, '$2y$12$tPeIfCls/i9i3J4pjb1bauqAyxHzm1JwLWshT4iAAVkeQPdg3e.P2', 'active', 'DBKL', NULL, NULL, NULL, NULL, 0, '2026-09-08 06:30:44', NULL, '2026-09-03 23:14:31', '2026-09-08 06:30:44'),
(9, 'Pegawai Teknikal', 'teknikal@dbkl.test', NULL, '$2y$12$NHxPTz5mwG8v5k8skk243eTvzs6JI1WSvCQIEYB00rv3y1EnaFqI2', 'active', 'DBKL', NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-09-03 23:14:31', '2026-09-03 23:14:31'),
(10, 'TP / Pengarah JP', 'pelulus@dbkl.test', NULL, '$2y$12$RPoueW5ZPoEKASKBAwiJe.Ws16e9YVhlpP0KjgYtXXCDrL6ASYuCy', 'active', 'DBKL', NULL, NULL, NULL, NULL, 0, '2026-09-08 00:51:50', NULL, '2026-09-03 23:14:31', '2026-09-08 00:51:50'),
(11, 'Pengurusan DBKL', 'pengurusan@dbkl.test', NULL, '$2y$12$GFoJOA9Ln0VvrcN8BGnuqufg1tpFZIy2fHttz19G2NQdTf0Kvhjmy', 'active', 'DBKL', NULL, NULL, NULL, NULL, 0, '2026-09-08 06:28:17', NULL, '2026-09-03 23:14:31', '2026-09-08 06:28:17'),
(12, 'YBhg. Dato\' Sri Ab Rahim bin Ab Rahman', 'alp04@dbkl.test', NULL, '$2y$12$gTKrryUZCzWIFch27u2eueWBXu74mRtUclPscqFzrVONybzehxibm', 'active', NULL, NULL, NULL, 4, NULL, 0, NULL, NULL, '2026-09-05 04:24:48', '2026-09-05 04:24:48'),
(13, 'YBrs. Encik Che Kodir bin Baharum', 'alp05@dbkl.test', NULL, '$2y$12$DG4NCdN/zRbjaKX4ZGosEONzrcTmo0stM3l.r/Fob37z0M4EVfY0.', 'active', NULL, NULL, NULL, 5, NULL, 0, NULL, NULL, '2026-09-05 04:24:48', '2026-09-05 04:24:48'),
(14, 'YBhg. Datuk Tengku Azman bin Tengku Zainol Abidin', 'alp06@dbkl.test', NULL, '$2y$12$2zwHdItvWs/fsDs5Wp/4zeLXIjl4L6QhGHMpiYreEkd6N0AzOYkwO', 'active', NULL, NULL, NULL, 6, NULL, 0, NULL, NULL, '2026-09-05 04:24:48', '2026-09-05 04:24:48'),
(15, 'YBhg. Datuk Azizulrahman bin Mohd Hussain Malim', 'alp07@dbkl.test', NULL, '$2y$12$Xy3KKbjYQxChYsQL9Gj0a.zdvUvzUnem9tgKvkoNuJ.57X7HgBv/m', 'active', NULL, NULL, NULL, 7, NULL, 0, NULL, NULL, '2026-09-05 04:24:48', '2026-09-05 04:24:48'),
(16, 'YBhg. Datuk Tong Nguen Khoong', 'alp08@dbkl.test', NULL, '$2y$12$Gk8A9IDvy49gDQh0yMMm8uDAw2ugnXUoSy2AA6zQVlGpIRg61d1Hu', 'active', NULL, NULL, NULL, 8, NULL, 0, NULL, NULL, '2026-09-05 04:24:49', '2026-09-05 04:24:49'),
(17, 'YBrs. Encik Ahmad Asri bin Talib', 'alp09@dbkl.test', NULL, '$2y$12$bqqv78MrfBWVRtCPjMvaWuTNew8jlcgD/sdXG2FnRVnFcEwETGAhO', 'active', NULL, NULL, NULL, 9, NULL, 0, NULL, NULL, '2026-09-05 04:24:49', '2026-09-05 04:24:49'),
(18, 'YBrs. Dr. Pua Eng Teck', 'alp10@dbkl.test', NULL, '$2y$12$B7uD7Qp7X/yWemUJs3ARWO2xHUmChhkQVhqJIkv58vvxPMW5B1//a', 'active', NULL, NULL, NULL, 10, NULL, 0, NULL, NULL, '2026-09-05 04:24:49', '2026-09-05 04:24:49'),
(19, 'YBrs. Encik Mohd Ashraf bin Mazlan', 'alp11@dbkl.test', NULL, '$2y$12$WynW8gqnHrD6m9FuyJy0MO31oDR1ekThL4JY.kU23bqiyt3uCerpO', 'active', NULL, NULL, NULL, 11, NULL, 0, NULL, NULL, '2026-09-05 04:24:50', '2026-09-05 04:24:50'),
(20, 'YBrs. Puan Idawate binti Pariman', 'alp12@dbkl.test', NULL, '$2y$12$j6F3uOdAOu5ru4wV3qdtvOYZftpbT2QA5FAZZkWHv.N8TGBBQUoWS', 'active', NULL, NULL, NULL, 12, NULL, 0, NULL, NULL, '2026-09-05 04:24:50', '2026-09-05 04:24:50'),
(21, 'YBrs. Puan Choo Chen Leece', 'alp13@dbkl.test', NULL, '$2y$12$wwPkk8KX7gnxotshZVHWo.3gy3cz8qkT6.nrkDE0UzYdUM8BUdgIS', 'active', NULL, NULL, NULL, 13, NULL, 0, NULL, NULL, '2026-09-05 04:24:50', '2026-09-05 04:24:50'),
(22, 'YBrs. Encik Lee Bing Hong', 'alp14@dbkl.test', NULL, '$2y$12$sIID8p2F6l/vOhICC3spFuU/8LuzNiKQr1CnmnLB/4rJol9JWSnA.', 'active', NULL, NULL, NULL, 14, NULL, 0, NULL, NULL, '2026-09-05 04:24:51', '2026-09-05 04:24:51'),
(23, 'YBrs. Encik Thiyagaraj Sankaranarayanan', 'alp15@dbkl.test', NULL, '$2y$12$G7BkfkuO.1dPChEjpCqokuqRV0xA0ntPPp9eY8oA2FlCGXxb9Yvam', 'active', NULL, NULL, NULL, 15, NULL, 0, NULL, NULL, '2026-09-05 04:24:51', '2026-09-05 04:24:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `allocations`
--
ALTER TABLE `allocations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `allocations_alp_id_financial_year_id_unique` (`alp_id`,`financial_year_id`),
  ADD KEY `allocations_financial_year_id_foreign` (`financial_year_id`),
  ADD KEY `allocations_created_by_foreign` (`created_by`);

--
-- Indexes for table `alps`
--
ALTER TABLE `alps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `alps_ref_code_unique` (`ref_code`),
  ADD KEY `alps_created_by_foreign` (`created_by`),
  ADD KEY `alps_status_index` (`status`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `applications_application_number_unique` (`application_number`),
  ADD KEY `applications_financial_year_id_foreign` (`financial_year_id`),
  ADD KEY `applications_created_by_foreign` (`created_by`),
  ADD KEY `applications_updated_by_foreign` (`updated_by`),
  ADD KEY `applications_alp_id_financial_year_id_index` (`alp_id`,`financial_year_id`),
  ADD KEY `applications_status_index` (`status`),
  ADD KEY `applications_application_type_index` (`application_type`),
  ADD KEY `applications_payment_updated_by_foreign` (`payment_updated_by`),
  ADD KEY `applications_payment_status_index` (`payment_status`),
  ADD KEY `applications_recipient_id_foreign` (`recipient_id`);

--
-- Indexes for table `application_approvals`
--
ALTER TABLE `application_approvals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `app_level_rev_unique` (`application_id`,`approval_level_id`,`revision_number`),
  ADD KEY `application_approvals_approval_level_id_foreign` (`approval_level_id`),
  ADD KEY `application_approvals_approver_id_foreign` (`approver_id`),
  ADD KEY `application_approvals_application_id_index` (`application_id`);

--
-- Indexes for table `application_documents`
--
ALTER TABLE `application_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_documents_uploaded_by_foreign` (`uploaded_by`),
  ADD KEY `application_documents_application_id_document_type_index` (`application_id`,`document_type`);

--
-- Indexes for table `application_reviews`
--
ALTER TABLE `application_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_reviews_reviewer_id_foreign` (`reviewer_id`),
  ADD KEY `application_reviews_application_id_review_type_index` (`application_id`,`review_type`);

--
-- Indexes for table `application_revisions`
--
ALTER TABLE `application_revisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_revisions_returned_by_foreign` (`returned_by`),
  ADD KEY `application_revisions_application_id_index` (`application_id`);

--
-- Indexes for table `application_sequences`
--
ALTER TABLE `application_sequences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_sequences_year_type_unique` (`year`,`type`);

--
-- Indexes for table `application_status_histories`
--
ALTER TABLE `application_status_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_status_histories_changed_by_foreign` (`changed_by`),
  ADD KEY `application_status_histories_application_id_index` (`application_id`);

--
-- Indexes for table `application_workflow_settings`
--
ALTER TABLE `application_workflow_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_workflow_settings_application_type_unique` (`application_type`);

--
-- Indexes for table `approval_levels`
--
ALTER TABLE `approval_levels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `approval_levels_financial_year_id_foreign` (`financial_year_id`),
  ADD KEY `approval_levels_active_sequence_index` (`active`,`sequence`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_entity_type_entity_id_index` (`entity_type`,`entity_id`),
  ADD KEY `audit_logs_user_id_index` (`user_id`),
  ADD KEY `audit_logs_action_index` (`action`);

--
-- Indexes for table `budget_requests`
--
ALTER TABLE `budget_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `budget_requests_financial_year_id_foreign` (`financial_year_id`),
  ADD KEY `budget_requests_created_by_foreign` (`created_by`),
  ADD KEY `budget_requests_submitted_by_foreign` (`submitted_by`),
  ADD KEY `budget_requests_approved_by_foreign` (`approved_by`),
  ADD KEY `budget_requests_rejected_by_foreign` (`rejected_by`),
  ADD KEY `budget_requests_returned_by_foreign` (`returned_by`),
  ADD KEY `budget_requests_alp_id_financial_year_id_index` (`alp_id`,`financial_year_id`),
  ADD KEY `budget_requests_status_index` (`status`),
  ADD KEY `budget_requests_request_type_index` (`request_type`);

--
-- Indexes for table `budget_request_histories`
--
ALTER TABLE `budget_request_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `budget_request_histories_changed_by_foreign` (`changed_by`),
  ADD KEY `budget_request_histories_budget_request_id_index` (`budget_request_id`);

--
-- Indexes for table `budget_transactions`
--
ALTER TABLE `budget_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bt_application_type_unique` (`application_id`,`type`),
  ADD UNIQUE KEY `bt_budget_request_unique` (`budget_request_id`),
  ADD UNIQUE KEY `bt_project_expense_unique` (`project_expense_id`),
  ADD UNIQUE KEY `bt_refund_unique` (`project_expense_refund_id`),
  ADD KEY `budget_transactions_allocation_id_foreign` (`allocation_id`),
  ADD KEY `budget_transactions_created_by_foreign` (`created_by`),
  ADD KEY `budget_transactions_alp_id_financial_year_id_index` (`alp_id`,`financial_year_id`),
  ADD KEY `budget_transactions_financial_year_id_index` (`financial_year_id`),
  ADD KEY `budget_transactions_type_index` (`type`),
  ADD KEY `budget_transactions_project_id_index` (`project_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `document_requirements`
--
ALTER TABLE `document_requirements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `document_requirements_document_type_unique` (`document_type`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `financial_years`
--
ALTER TABLE `financial_years`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `financial_years_year_unique` (`year`),
  ADD KEY `financial_years_created_by_foreign` (`created_by`),
  ADD KEY `financial_years_status_index` (`status`),
  ADD KEY `financial_years_is_active_index` (`is_active`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `projects_project_number_unique` (`project_number`),
  ADD UNIQUE KEY `projects_application_id_unique` (`application_id`),
  ADD KEY `projects_financial_year_id_foreign` (`financial_year_id`),
  ADD KEY `projects_created_by_foreign` (`created_by`),
  ADD KEY `projects_alp_id_financial_year_id_index` (`alp_id`,`financial_year_id`),
  ADD KEY `projects_status_index` (`status`);

--
-- Indexes for table `project_documents`
--
ALTER TABLE `project_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_documents_uploaded_by_foreign` (`uploaded_by`),
  ADD KEY `project_documents_project_id_category_index` (`project_id`,`category`),
  ADD KEY `project_documents_project_expense_id_index` (`project_expense_id`),
  ADD KEY `project_documents_project_expense_refund_id_index` (`project_expense_refund_id`);

--
-- Indexes for table `project_document_requirements`
--
ALTER TABLE `project_document_requirements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pdr_unique` (`project_type`,`category`,`document_type`);

--
-- Indexes for table `project_expenses`
--
ALTER TABLE `project_expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_expenses_created_by_foreign` (`created_by`),
  ADD KEY `project_expenses_submitted_by_foreign` (`submitted_by`),
  ADD KEY `project_expenses_verified_by_foreign` (`verified_by`),
  ADD KEY `project_expenses_rejected_by_foreign` (`rejected_by`),
  ADD KEY `project_expenses_returned_by_foreign` (`returned_by`),
  ADD KEY `project_expenses_project_id_status_index` (`project_id`,`status`);

--
-- Indexes for table `project_expense_histories`
--
ALTER TABLE `project_expense_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_expense_histories_changed_by_foreign` (`changed_by`),
  ADD KEY `project_expense_histories_project_expense_id_index` (`project_expense_id`);

--
-- Indexes for table `project_expense_refunds`
--
ALTER TABLE `project_expense_refunds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_expense_refunds_created_by_foreign` (`created_by`),
  ADD KEY `project_expense_refunds_submitted_by_foreign` (`submitted_by`),
  ADD KEY `project_expense_refunds_verified_by_foreign` (`verified_by`),
  ADD KEY `project_expense_refunds_rejected_by_foreign` (`rejected_by`),
  ADD KEY `project_expense_refunds_returned_by_foreign` (`returned_by`),
  ADD KEY `project_expense_refunds_project_expense_id_status_index` (`project_expense_id`,`status`),
  ADD KEY `project_expense_refunds_project_id_index` (`project_id`);

--
-- Indexes for table `project_expense_refund_histories`
--
ALTER TABLE `project_expense_refund_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_expense_refund_histories_changed_by_foreign` (`changed_by`),
  ADD KEY `perh_refund_idx` (`project_expense_refund_id`);

--
-- Indexes for table `project_milestones`
--
ALTER TABLE `project_milestones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_milestones_created_by_foreign` (`created_by`),
  ADD KEY `project_milestones_updated_by_foreign` (`updated_by`),
  ADD KEY `project_milestones_project_id_index` (`project_id`);

--
-- Indexes for table `project_progress_histories`
--
ALTER TABLE `project_progress_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_progress_histories_updated_by_foreign` (`updated_by`),
  ADD KEY `project_progress_histories_project_id_index` (`project_id`);

--
-- Indexes for table `project_reports`
--
ALTER TABLE `project_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `project_reports_project_id_unique` (`project_id`),
  ADD KEY `project_reports_submitted_by_foreign` (`submitted_by`),
  ADD KEY `project_reports_approved_by_foreign` (`approved_by`);

--
-- Indexes for table `project_sequences`
--
ALTER TABLE `project_sequences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `project_sequences_year_type_unique` (`year`,`type`);

--
-- Indexes for table `project_status_histories`
--
ALTER TABLE `project_status_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_status_histories_changed_by_foreign` (`changed_by`),
  ADD KEY `project_status_histories_project_id_index` (`project_id`);

--
-- Indexes for table `recipients`
--
ALTER TABLE `recipients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `recipients_ros_number_unique` (`ros_number`);

--
-- Indexes for table `report_card_reviews`
--
ALTER TABLE `report_card_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_card_reviews_reviewer_id_foreign` (`reviewer_id`),
  ADD KEY `report_card_reviews_application_id_stage_index` (`application_id`,`stage`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `system_settings_key_unique` (`key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_status_index` (`status`),
  ADD KEY `users_alp_id_foreign` (`alp_id`),
  ADD KEY `users_created_by_foreign` (`created_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `allocations`
--
ALTER TABLE `allocations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `alps`
--
ALTER TABLE `alps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `application_approvals`
--
ALTER TABLE `application_approvals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `application_documents`
--
ALTER TABLE `application_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `application_reviews`
--
ALTER TABLE `application_reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `application_revisions`
--
ALTER TABLE `application_revisions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `application_sequences`
--
ALTER TABLE `application_sequences`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `application_status_histories`
--
ALTER TABLE `application_status_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `application_workflow_settings`
--
ALTER TABLE `application_workflow_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `approval_levels`
--
ALTER TABLE `approval_levels`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=220;

--
-- AUTO_INCREMENT for table `budget_requests`
--
ALTER TABLE `budget_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `budget_request_histories`
--
ALTER TABLE `budget_request_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `budget_transactions`
--
ALTER TABLE `budget_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `document_requirements`
--
ALTER TABLE `document_requirements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `financial_years`
--
ALTER TABLE `financial_years`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `project_documents`
--
ALTER TABLE `project_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `project_document_requirements`
--
ALTER TABLE `project_document_requirements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `project_expenses`
--
ALTER TABLE `project_expenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `project_expense_histories`
--
ALTER TABLE `project_expense_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `project_expense_refunds`
--
ALTER TABLE `project_expense_refunds`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `project_expense_refund_histories`
--
ALTER TABLE `project_expense_refund_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `project_milestones`
--
ALTER TABLE `project_milestones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_progress_histories`
--
ALTER TABLE `project_progress_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `project_reports`
--
ALTER TABLE `project_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `project_sequences`
--
ALTER TABLE `project_sequences`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `project_status_histories`
--
ALTER TABLE `project_status_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `recipients`
--
ALTER TABLE `recipients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `report_card_reviews`
--
ALTER TABLE `report_card_reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `allocations`
--
ALTER TABLE `allocations`
  ADD CONSTRAINT `allocations_alp_id_foreign` FOREIGN KEY (`alp_id`) REFERENCES `alps` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `allocations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `allocations_financial_year_id_foreign` FOREIGN KEY (`financial_year_id`) REFERENCES `financial_years` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `alps`
--
ALTER TABLE `alps`
  ADD CONSTRAINT `alps_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_alp_id_foreign` FOREIGN KEY (`alp_id`) REFERENCES `alps` (`id`),
  ADD CONSTRAINT `applications_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `applications_financial_year_id_foreign` FOREIGN KEY (`financial_year_id`) REFERENCES `financial_years` (`id`),
  ADD CONSTRAINT `applications_payment_updated_by_foreign` FOREIGN KEY (`payment_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `applications_recipient_id_foreign` FOREIGN KEY (`recipient_id`) REFERENCES `recipients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `applications_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `application_approvals`
--
ALTER TABLE `application_approvals`
  ADD CONSTRAINT `application_approvals_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `application_approvals_approval_level_id_foreign` FOREIGN KEY (`approval_level_id`) REFERENCES `approval_levels` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `application_approvals_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `application_documents`
--
ALTER TABLE `application_documents`
  ADD CONSTRAINT `application_documents_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `application_documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `application_reviews`
--
ALTER TABLE `application_reviews`
  ADD CONSTRAINT `application_reviews_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `application_reviews_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `application_revisions`
--
ALTER TABLE `application_revisions`
  ADD CONSTRAINT `application_revisions_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `application_revisions_returned_by_foreign` FOREIGN KEY (`returned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `application_status_histories`
--
ALTER TABLE `application_status_histories`
  ADD CONSTRAINT `application_status_histories_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `application_status_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `approval_levels`
--
ALTER TABLE `approval_levels`
  ADD CONSTRAINT `approval_levels_financial_year_id_foreign` FOREIGN KEY (`financial_year_id`) REFERENCES `financial_years` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `budget_requests`
--
ALTER TABLE `budget_requests`
  ADD CONSTRAINT `budget_requests_alp_id_foreign` FOREIGN KEY (`alp_id`) REFERENCES `alps` (`id`),
  ADD CONSTRAINT `budget_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `budget_requests_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `budget_requests_financial_year_id_foreign` FOREIGN KEY (`financial_year_id`) REFERENCES `financial_years` (`id`),
  ADD CONSTRAINT `budget_requests_rejected_by_foreign` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `budget_requests_returned_by_foreign` FOREIGN KEY (`returned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `budget_requests_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `budget_request_histories`
--
ALTER TABLE `budget_request_histories`
  ADD CONSTRAINT `budget_request_histories_budget_request_id_foreign` FOREIGN KEY (`budget_request_id`) REFERENCES `budget_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `budget_request_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `budget_transactions`
--
ALTER TABLE `budget_transactions`
  ADD CONSTRAINT `budget_transactions_allocation_id_foreign` FOREIGN KEY (`allocation_id`) REFERENCES `allocations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `budget_transactions_alp_id_foreign` FOREIGN KEY (`alp_id`) REFERENCES `alps` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `budget_transactions_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `budget_transactions_budget_request_id_foreign` FOREIGN KEY (`budget_request_id`) REFERENCES `budget_requests` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `budget_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `budget_transactions_financial_year_id_foreign` FOREIGN KEY (`financial_year_id`) REFERENCES `financial_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `budget_transactions_project_expense_id_foreign` FOREIGN KEY (`project_expense_id`) REFERENCES `project_expenses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `budget_transactions_project_expense_refund_id_foreign` FOREIGN KEY (`project_expense_refund_id`) REFERENCES `project_expense_refunds` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `budget_transactions_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `financial_years`
--
ALTER TABLE `financial_years`
  ADD CONSTRAINT `financial_years_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_alp_id_foreign` FOREIGN KEY (`alp_id`) REFERENCES `alps` (`id`),
  ADD CONSTRAINT `projects_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`),
  ADD CONSTRAINT `projects_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `projects_financial_year_id_foreign` FOREIGN KEY (`financial_year_id`) REFERENCES `financial_years` (`id`);

--
-- Constraints for table `project_documents`
--
ALTER TABLE `project_documents`
  ADD CONSTRAINT `project_documents_project_expense_id_foreign` FOREIGN KEY (`project_expense_id`) REFERENCES `project_expenses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_documents_project_expense_refund_id_foreign` FOREIGN KEY (`project_expense_refund_id`) REFERENCES `project_expense_refunds` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_documents_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_expenses`
--
ALTER TABLE `project_expenses`
  ADD CONSTRAINT `project_expenses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_expenses_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_expenses_rejected_by_foreign` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_expenses_returned_by_foreign` FOREIGN KEY (`returned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_expenses_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_expenses_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_expense_histories`
--
ALTER TABLE `project_expense_histories`
  ADD CONSTRAINT `project_expense_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_expense_histories_project_expense_id_foreign` FOREIGN KEY (`project_expense_id`) REFERENCES `project_expenses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_expense_refunds`
--
ALTER TABLE `project_expense_refunds`
  ADD CONSTRAINT `project_expense_refunds_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_expense_refunds_project_expense_id_foreign` FOREIGN KEY (`project_expense_id`) REFERENCES `project_expenses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_expense_refunds_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_expense_refunds_rejected_by_foreign` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_expense_refunds_returned_by_foreign` FOREIGN KEY (`returned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_expense_refunds_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_expense_refunds_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_expense_refund_histories`
--
ALTER TABLE `project_expense_refund_histories`
  ADD CONSTRAINT `perh_refund_fk` FOREIGN KEY (`project_expense_refund_id`) REFERENCES `project_expense_refunds` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_expense_refund_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_milestones`
--
ALTER TABLE `project_milestones`
  ADD CONSTRAINT `project_milestones_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_milestones_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_milestones_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_progress_histories`
--
ALTER TABLE `project_progress_histories`
  ADD CONSTRAINT `project_progress_histories_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_progress_histories_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_reports`
--
ALTER TABLE `project_reports`
  ADD CONSTRAINT `project_reports_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_reports_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_reports_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_status_histories`
--
ALTER TABLE `project_status_histories`
  ADD CONSTRAINT `project_status_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_status_histories_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `report_card_reviews`
--
ALTER TABLE `report_card_reviews`
  ADD CONSTRAINT `report_card_reviews_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_card_reviews_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_alp_id_foreign` FOREIGN KEY (`alp_id`) REFERENCES `alps` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
