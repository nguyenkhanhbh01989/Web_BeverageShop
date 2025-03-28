-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th3 23, 2025 lúc 04:32 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `beverage_store`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`) VALUES
(1, 'Nước ngọt', 'Các loại nước ngọt có ga'),
(2, 'Nước tăng lực', 'Nước uống tăng cường năng lượng'),
(3, 'Trà', 'Trà đóng chai và trà túi lọc'),
(4, 'Cà phê', 'Cà phê đóng chai và cà phê hòa tan');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','confirmed','rejected','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) NOT NULL DEFAULT 'cod',
  `address` text NOT NULL,
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `total_amount`, `status`, `payment_method`, `address`, `note`) VALUES
(1, 2, '2025-03-20 20:31:14', 0.00, 'rejected', 'cod', '', NULL),
(2, 3, '2025-03-20 20:31:58', 0.00, 'rejected', 'cod', '', NULL),
(3, 3, '2025-03-20 20:36:37', 0.00, 'confirmed', 'cod', '', NULL),
(5, 1, '2025-03-20 21:12:26', 68000.00, 'rejected', 'cod', '', NULL),
(6, 1, '2025-03-20 21:12:34', 15000.00, 'rejected', 'cod', '', NULL),
(7, 1, '2025-03-20 21:27:06', 129000.00, 'confirmed', 'cod', '', NULL),
(8, 1, '2025-03-20 22:41:11', 104000.00, 'confirmed', 'cod', '', NULL),
(9, 1, '2025-03-20 23:55:25', 138000.00, 'rejected', 'cod', '', NULL),
(10, 3, '2025-03-21 00:45:29', 59000.00, 'cancelled', 'cod', '', NULL),
(11, 3, '2025-03-21 01:15:07', 15000.00, 'cancelled', 'cod', '', NULL),
(12, 3, '2025-03-21 01:45:52', 80000.00, 'cancelled', 'cod', '', NULL),
(13, 3, '2025-03-21 01:49:46', 56000.00, 'cancelled', 'cod', '168 Nguyen Dong Chi , Ha Npi', 'fdghfd'),
(14, 6, '2025-03-21 16:57:05', 113000.00, 'confirmed', 'cod', '168 Nguyen Dong Chi , Ha Npi', ''),
(15, 2, '2025-03-21 17:19:58', 50000.00, 'confirmed', 'cod', '168 Nguyen Dong Chi , Ha Npi', 'it da giup em'),
(16, 2, '2025-03-21 20:00:21', 25000.00, 'cancelled', 'momo', '168 Nguyen Dong Chi , Ha Npi', ''),
(17, 2, '2025-03-21 22:34:13', 42000.00, 'rejected', 'cod', '168 Nguyen Dong Chi , Ha Npi', 'it da');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_details`
--

CREATE TABLE `order_details` (
  `order_detail_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `order_details`
--

INSERT INTO `order_details` (`order_detail_id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 2, 4, 14000.00),
(2, 1, 3, 1, 25000.00),
(3, 1, 4, 1, 10000.00),
(4, 2, 1, 4, 15000.00),
(5, 2, 2, 4, 14000.00),
(6, 2, 3, 1, 25000.00),
(7, 2, 4, 1, 10000.00),
(8, 2, 5, 1, 20000.00),
(9, 3, 1, 4, 15000.00),
(10, 3, 2, 3, 14000.00),
(11, 3, 3, 2, 25000.00),
(12, 3, 4, 1, 10000.00),
(13, 3, 5, 1, 20000.00),
(15, 5, 2, 2, 14000.00),
(16, 5, 1, 1, 15000.00),
(17, 5, 3, 1, 25000.00),
(18, 6, 1, 1, 15000.00),
(19, 7, 1, 1, 15000.00),
(20, 7, 2, 1, 14000.00),
(21, 7, 3, 2, 25000.00),
(22, 7, 4, 1, 10000.00),
(23, 7, 5, 2, 20000.00),
(24, 8, 1, 1, 15000.00),
(25, 8, 2, 1, 14000.00),
(26, 8, 3, 1, 25000.00),
(27, 8, 4, 1, 10000.00),
(28, 8, 5, 2, 20000.00),
(29, 9, 1, 2, 15000.00),
(30, 9, 2, 2, 14000.00),
(31, 9, 3, 2, 25000.00),
(32, 9, 4, 1, 10000.00),
(33, 9, 5, 1, 20000.00),
(34, 10, 2, 1, 14000.00),
(35, 10, 4, 2, 10000.00),
(36, 10, 3, 1, 25000.00),
(37, 11, 1, 1, 15000.00),
(38, 12, 5, 4, 20000.00),
(39, 13, 2, 4, 14000.00),
(40, 14, 1, 4, 15000.00),
(41, 14, 2, 2, 14000.00),
(42, 14, 3, 1, 25000.00),
(43, 15, 8, 2, 25000.00),
(44, 16, 8, 1, 25000.00),
(45, 17, 2, 3, 14000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `product_name` varchar(200) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `size` varchar(50) DEFAULT NULL,
  `flavor` varchar(50) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `price`, `size`, `flavor`, `stock`, `image`, `description`) VALUES
(1, 1, 'Coca-Cola', 15000.00, '330ml', 'Original', 91, 'coca.jpg', 'Nước ngọt có ga Coca-Cola'),
(2, 1, 'Pepsi', 14000.00, '330ml', 'Original', 66, 'pepsi.jpg', 'Nước ngọt có ga Pepsi'),
(3, 2, 'Red Bull', 25000.00, '250ml', 'Original', 43, 'redbull.jpg', 'Nước tăng lực Red Bull'),
(4, 3, 'Trà xanh không độ', 10000.00, '500ml', 'Trà xanh', 115, 'tra_xanh.jpg', 'Trà xanh không đường'),
(5, 4, 'Cà phê sữa đá', 20000.00, '250ml', 'Cà phê sữa', 51, 'cafe_sua.jpg', 'Cà phê sữa đá đóng chai'),
(8, 3, 'Trà sữa trân châu đường đen', 25000.00, '350ml', 'Thơm mùi trà và đậm vị sữa, chân trâu thì dai giòn', 19, 'tra_sua.png', 'Món Bét Seo Lơ của quán em , đặt nhanh kẻo hết hàng ạ ! ');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'admin'),
(2, 'customer'),
(3, 'staff');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `role_id`) VALUES
(1, 'admin', '$2y$10$V2V.PVYggxtz0Y.yAIUwK./YR5.FbUvhqruR66pgb8fjPZ4aQglH6', 'admin@example.com', 1),
(2, 'staff1', '$2y$10$EYPg7B4emGoyZBqJ2jG3JuMa1B4zpg3g4kfB3Jh2xpFOB.TJkpidG', 'staff1@gmail.com', 3),
(3, 'customer1', '$2y$10$i0abnm7xXvOCOIDDA.hELeoR//yp5ZFKb5tHHs1sJQ4p6py.2QB7u', 'customer@example.com', 2),
(5, 'customer3', '$2y$10$4gnqPObbmvrCjE6mldj/s.c6H3hQ5s6hOBT8WDT3vY9y3K/vrYHVW', 'customer3@gmail.com', 2),
(6, 'customer2', '$2y$10$DpxcNMrOl7VXkmht7hobpuXqIOwaBaMnKAU2SQlX2obhc7Yvk2UPm', 'customer2@gmail.com', 2);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`order_detail_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Chỉ mục cho bảng `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT cho bảng `order_details`
--
ALTER TABLE `order_details`
  MODIFY `order_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Các ràng buộc cho bảng `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Các ràng buộc cho bảng `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
