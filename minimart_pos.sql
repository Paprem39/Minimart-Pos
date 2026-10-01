-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 01, 2026 at 11:48 PM
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
-- Database: `minimart_pos`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'เครื่องดื่ม'),
(2, 'ขนม'),
(3, 'ของใช้');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','transfer') NOT NULL,
  `cash_received` decimal(10,2) NOT NULL,
  `change_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `category_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `stock_qty` int(11) NOT NULL DEFAULT 0,
  `min_stock` int(11) NOT NULL DEFAULT 5,
  `received_date` date NOT NULL DEFAULT curdate(),
  `expire_date` date DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `barcode`, `name`, `category_id`, `price`, `cost`, `stock_qty`, `min_stock`, `received_date`, `expire_date`, `image`, `updated_at`) VALUES
(1, NULL, 'โกรีโก้', 2, 24.00, 16.00, 14, 5, '2026-10-02', NULL, NULL, '2026-10-01 20:15:28'),
(2, NULL, 'เลย์', 2, 32.00, 25.00, 4, 5, '2026-10-02', NULL, NULL, '2026-10-01 21:06:20'),
(3, NULL, 'พริงเกิลส์', 2, 65.00, 58.00, 15, 15, '2026-10-02', NULL, NULL, '2026-10-01 21:36:25'),
(4, NULL, 'สแน็คแจ๊ค', 2, 22.00, 17.00, 13, 12, '2026-10-02', NULL, NULL, '2026-10-01 21:36:25'),
(5, NULL, 'ปูเบนโตะ', 2, 39.00, 27.00, 20, 10, '2026-10-02', NULL, NULL, '2026-10-01 19:04:08'),
(6, NULL, 'โค้ก Original', 1, 16.00, 7.00, 50, 25, '2026-10-02', NULL, NULL, '2026-10-01 19:13:25'),
(7, NULL, 'สปอนเซอร์', 1, 12.00, 7.00, 4, 5, '2026-10-02', NULL, NULL, '2026-10-01 21:36:25'),
(8, NULL, 'แป็บซี่', 1, 20.00, 11.00, 10, 5, '2026-10-01', NULL, NULL, '2026-10-01 21:36:25'),
(9, NULL, 'น้ำทิพย์650มล.', 1, 24.00, 20.00, 10, 5, '2026-10-02', NULL, NULL, '2026-10-01 21:18:45'),
(10, NULL, 'น้ำแร่', 1, 29.00, 22.00, 10, 7, '2026-10-02', NULL, NULL, '2026-10-01 21:18:42'),
(11, NULL, 'แปรงสีฟัน', 3, 29.00, 23.00, 20, 12, '2026-10-02', NULL, NULL, '2026-10-01 19:06:40'),
(12, NULL, 'ครีมทาหน้า', 3, 29.00, 24.00, 20, 12, '2026-10-02', NULL, NULL, '2026-10-01 21:18:39'),
(13, NULL, 'น้ำยาปรับผ้านุ่ม', 3, 25.00, 20.00, 7, 5, '2026-10-02', NULL, NULL, '2026-10-01 21:38:41'),
(14, NULL, 'ที่ขัดรองเท้า', 3, 79.00, 75.00, 10, 5, '2026-10-02', NULL, NULL, '2026-10-01 21:18:29'),
(15, NULL, 'น้ำยาล้างจาน', 3, 69.00, 45.00, 4, 5, '2026-10-02', NULL, NULL, '2026-10-01 21:38:41'),
(16, NULL, 'ขนมปังฟาร์มเฮ้า', 2, 42.00, 36.00, 10, 7, '2026-10-02', '2026-10-06', NULL, '2026-10-01 21:18:26');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `total_profit` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `cash_received` decimal(10,2) DEFAULT 0.00,
  `change_amount` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `total_amount`, `total_cost`, `total_profit`, `payment_method`, `cash_received`, `change_amount`, `created_at`) VALUES
(5, 72.00, 42.00, 30.00, 'cash', 100.00, 28.00, '2026-10-01 21:16:37'),
(6, 631.00, 495.00, 136.00, 'cash', 1000.00, 369.00, '2026-10-01 21:36:25'),
(7, 489.00, 330.00, 159.00, 'cash', 1000.00, 511.00, '2026-10-01 21:38:41');

-- --------------------------------------------------------

--
-- Table structure for table `sale_details`
--

CREATE TABLE `sale_details` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `total_profit` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_details`
--

INSERT INTO `sale_details` (`id`, `sale_id`, `product_id`, `quantity`, `unit_price`, `unit_cost`, `total_price`, `total_profit`) VALUES
(6, 5, 7, 6, 12.00, 7.00, 72.00, 30.00),
(7, 6, 3, 5, 65.00, 58.00, 325.00, 35.00),
(8, 6, 4, 7, 22.00, 17.00, 154.00, 35.00),
(9, 6, 8, 4, 20.00, 11.00, 80.00, 36.00),
(10, 6, 7, 6, 12.00, 7.00, 72.00, 30.00),
(11, 7, 13, 3, 25.00, 20.00, 75.00, 15.00),
(12, 7, 15, 6, 69.00, 45.00, 414.00, 144.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `pin` varchar(4) NOT NULL,
  `role` enum('admin','cashier') NOT NULL DEFAULT 'cashier',
  `status` enum('pending','approved') DEFAULT 'approved',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `pin`, `role`, `status`, `created_at`) VALUES
(1, 'เจ้าของร้าน (Admin)', '1234', 'admin', 'approved', '2026-10-01 17:23:35'),
(2, 'น้องใหม่', '0000', 'cashier', 'approved', '2026-10-01 19:09:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sale_details`
--
ALTER TABLE `sale_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pin` (`pin`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `sale_details`
--
ALTER TABLE `sale_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sale_details`
--
ALTER TABLE `sale_details`
  ADD CONSTRAINT `sale_details_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sale_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
