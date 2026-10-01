-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 01, 2026 at 11:49 PM
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
-- Database: `sirichaiprem`
--

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `Employee_id` int(11) NOT NULL,
  `Full_name` varchar(100) NOT NULL,
  `Gender` varchar(20) NOT NULL,
  `Position` varchar(50) NOT NULL,
  `Salary` decimal(10,2) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Birthday` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`Employee_id`, `Full_name`, `Gender`, `Position`, `Salary`, `Email`, `Birthday`) VALUES
(1, 'สมชาย ใจดี', 'ชาย', 'Software Developer', 35000.00, 'somchai@email.com', '1998-05-12'),
(2, 'สมหญิง รักเรียน', 'หญิง', 'Graphic Designer', 28000.00, 'somying@email.com', '2000-09-25'),
(3, 'ประเสริฐ มั่นคง', 'ชาย', 'Project Manager', 45000.00, 'prasert@email.com', '1995-02-15'),
(4, 'ศิริชัย ไพสิฐวรกุล', 'ชาย', 'ผู้จัดการ', 55000.00, 'sirichaiprem@gmail.com', '1996-02-14'),
(6, 'อิสรชน พรามณี', 'ชาย', 'เด็กฝึกงาน', 5900.00, 'Itsara@gmail.com', '2026-09-19');

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

CREATE TABLE `member` (
  `id` int(11) NOT NULL,
  `user` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `member`
--

INSERT INTO `member` (`id`, `user`, `password`, `role`) VALUES
(1, 'admin', 'cet123456', 'admin'),
(2, 'user', 'cet123456', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`Employee_id`);

--
-- Indexes for table `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `Employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `member`
--
ALTER TABLE `member`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
