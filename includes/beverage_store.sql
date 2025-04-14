-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th3 31, 2025 lúc 10:04 AM
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
  `status` varchar(50) NOT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'cod',
  `address` text NOT NULL,
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `total_amount`, `status`, `payment_method`, `address`, `note`) VALUES
(35, 1, '2025-03-28 20:00:08', 75000.00, '', 'cod', '168 Nguyen Dong Chi , Ha Npi', 'aaa'),
(36, 1, '2025-03-28 20:01:58', 75000.00, 'cancelled', 'cod', 'fg n ', ''),
(37, 1, '2025-03-28 20:07:58', 154000.00, '', 'cod', '168 Nguyen Dong Chi , Ha Npi', 'aa'),
(38, 1, '2025-03-28 20:10:25', 15000.00, 'processing', 'cod', 'sẻhm', ''),
(39, 1, '2025-03-28 20:15:54', 14000.00, 'Completed', 'cod', 'xd', ''),
(40, 1, '2025-03-28 20:23:09', 150000.00, 'Completed', 'cod', '168 Nguyen Dong Chi , Ha Npi', 'a'),
(41, 1, '2025-03-29 07:30:37', 25000.00, 'Completed', 'cod', '168 Nguyen Dong Chi , Ha Npi', '00'),
(42, 1, '2025-03-29 18:23:18', 20000.00, 'Completed', 'cod', '168 Nguyen Dong Chi , Ha Npi', 'â'),
(43, 1, '2025-03-29 19:09:41', 42000.00, 'Completed', 'cod', '168 Nguyen Dong Chi , Ha Npi', 'ứed'),
(44, 1, '2025-03-30 22:32:47', 325000.00, 'Cancelled', 'cod', '168 Nguyen Dong Chi , Ha Npi', '');

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
(76, 35, 1, 5, 15000.00),
(77, 36, 3, 3, 25000.00),
(78, 37, 2, 11, 14000.00),
(79, 38, 1, 1, 15000.00),
(80, 39, 2, 1, 14000.00),
(81, 40, 3, 6, 25000.00),
(82, 41, 8, 1, 25000.00),
(83, 42, 5, 1, 20000.00),
(84, 43, 2, 3, 14000.00),
(85, 44, 3, 13, 25000.00);

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
(1, 1, 'Coca-Cola', 15000.00, '330ml', 'Original', 53, 'coca.jpg', 'Nước ngọt có ga Coca-Cola'),
(2, 1, 'Pepsi', 14000.00, '330ml', 'Original', 36, 'pepsi.jpg', 'Nước ngọt có ga Pepsi'),
(3, 2, 'Red Bull', 25000.00, '250ml', 'Original', 7, 'redbull.jpg', 'Nước tăng lực Red Bull'),
(4, 3, 'Trà xanh không độ', 10000.00, '500ml', 'Trà xanh', 91, 'tra_xanh.jpg', 'Trà xanh không đường'),
(5, 4, 'Cà phê sữa đá', 20000.00, '250ml', 'Cà phê sữa', 50, 'cafe_sua.jpg', 'Cà phê sữa đá đóng chai'),
(8, 3, 'Trà sữa trân châu đường đen', 25000.00, '350ml', 'Thơm mùi trà và đậm vị sữa, chân trâu thì dai giòn', 0, 'tra_sua.png', 'Món Bét Seo Lơ của quán em , đặt nhanh kẻo hết hàng ạ ! ');

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
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `full_name`, `phone`, `address`, `role_id`, `created_at`) VALUES
(1, 'admin', '$2y$10$V2V.PVYggxtz0Y.yAIUwK./YR5.FbUvhqruR66pgb8fjPZ4aQglH6', 'admin@example.com', NULL, NULL, NULL, 1, '2025-03-29 13:16:11'),
(2, 'staff1', '$2y$10$EYPg7B4emGoyZBqJ2jG3JuMa1B4zpg3g4kfB3Jh2xpFOB.TJkpidG', 'staff1@gmail.com', NULL, NULL, NULL, 3, '2025-03-29 13:16:11'),
(3, 'customer1', '$2y$10$i0abnm7xXvOCOIDDA.hELeoR//yp5ZFKb5tHHs1sJQ4p6py.2QB7u', 'customer@example.com', NULL, NULL, NULL, 2, '2025-03-29 13:16:11'),
(5, 'customer3', '$2y$10$4gnqPObbmvrCjE6mldj/s.c6H3hQ5s6hOBT8WDT3vY9y3K/vrYHVW', 'customer3@gmail.com', NULL, NULL, NULL, 2, '2025-03-29 13:16:11'),
(6, 'customer2', '$2y$10$DpxcNMrOl7VXkmht7hobpuXqIOwaBaMnKAU2SQlX2obhc7Yvk2UPm', 'customer2@gmail.com', NULL, NULL, NULL, 2, '2025-03-29 13:16:11'),
(10, '2', '$2y$10$jg8pVhu8TkCANUElGHyl8e7TVbyPr00BRZcMVb9Tmwpoe0Lavpyh6', '2@gmail.com', NULL, NULL, NULL, 2, '2025-03-29 13:16:11'),
(11, '1', '$2y$10$7J31E.a4VOY0htyiU5XzMuzrzpZvOGfnHHYzjcAUJRun7.iPfUeqK', '1@gmail.com', NULL, NULL, NULL, 3, '2025-03-29 13:19:57'),
(12, '3', '$2y$10$iYRZdJF1Pe1rpVJEzSiMIeEov4SfNUXQ8fE4dByZCXY8ZJqzlKTQK', '3@gmail.com', NULL, NULL, NULL, 2, '2025-03-29 13:38:37'),
(14, 'Hòa', '$2y$10$rHTzK8Jk9jYX/EuYIf9Ozu75RRkQKjA41tpwkUh5NURpEgyrJtrcu', 'hoa@gmail.com', 'Đình Văn Hòa', '0123443210', 'Thanh Hóa', 2, '2025-03-30 15:40:05');

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
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT cho bảng `order_details`
--
ALTER TABLE `order_details`
  MODIFY `order_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

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
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
