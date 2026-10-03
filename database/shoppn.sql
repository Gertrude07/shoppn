CREATE DATABASE IF NOT EXISTS `shoppn` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `shoppn`;

DROP TABLE IF EXISTS `cart`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `brands`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `customer`;

CREATE TABLE `brands` (
  `brand_id` int NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(100) NOT NULL,
  PRIMARY KEY (`brand_id`),
  UNIQUE KEY `brand_name_unique` (`brand_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `categories` (
  `cat_id` int NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(100) NOT NULL,
  PRIMARY KEY (`cat_id`),
  UNIQUE KEY `cat_name_unique` (`cat_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `customer` (
  `customer_id` int NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_pass` varchar(255) NOT NULL,
  `customer_country` varchar(100) NOT NULL,
  `customer_city` varchar(100) NOT NULL,
  `customer_contact` varchar(30) NOT NULL,
  `customer_image` varchar(255) DEFAULT NULL,
  `user_role` tinyint NOT NULL DEFAULT '2',
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `customer_email_unique` (`customer_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `products` (
  `product_id` int NOT NULL AUTO_INCREMENT,
  `product_cat` int NOT NULL,
  `product_brand` int NOT NULL,
  `product_title` varchar(255) NOT NULL,
  `product_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `product_currency` varchar(10) NOT NULL DEFAULT 'USD',
  `product_desc` text NOT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `product_keywords` varchar(255) NOT NULL,
  PRIMARY KEY (`product_id`),
  KEY `fk_product_category` (`product_cat`),
  KEY `fk_product_brand` (`product_brand`),
  CONSTRAINT `fk_product_brand` FOREIGN KEY (`product_brand`) REFERENCES `brands` (`brand_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_product_category` FOREIGN KEY (`product_cat`) REFERENCES `categories` (`cat_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cart` (
  `cart_id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `product_id` int NOT NULL,
  `qty` int NOT NULL DEFAULT '1',
  `price_at_time` decimal(10,2) NOT NULL DEFAULT '0.00',
  `currency_at_time` varchar(10) NOT NULL DEFAULT 'USD',
  PRIMARY KEY (`cart_id`),
  KEY `fk_cart_customer` (`customer_id`),
  KEY `fk_cart_product` (`product_id`),
  CONSTRAINT `fk_cart_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `brands` (`brand_name`) VALUES
('Nike'),
('Samsung'),
('Apple');

INSERT INTO `categories` (`cat_name`) VALUES
('Electronics'),
('Fashion'),
('Home');

INSERT INTO `customer` (`customer_name`, `customer_email`, `customer_pass`, `customer_country`, `customer_city`, `customer_contact`, `user_role`) VALUES
('Admin User', 'admin@shoppn.com', '$2y$10$7XgA9EJjFvQ6pN2l8Hz/S.O9q7JY3bM89xvYpyhK6Hz0cVA6Q3b4K', 'Ghana', 'Accra', '+233200000000', 1),
('Customer User', 'customer@shoppn.com', '$2y$10$7XgA9EJjFvQ6pN2l8Hz/S.O9q7JY3bM89xvYpyhK6Hz0cVA6Q3b4K', 'Ghana', 'Kumasi', '+233244444444', 2);

INSERT INTO `products` (`product_cat`, `product_brand`, `product_title`, `product_price`, `product_currency`, `product_desc`, `product_image`, `product_keywords`) VALUES
(1, 1, 'Nike Running Shoes', 120.00, 'USD', 'Comfortable sports shoes suitable for running and everyday use.', 'sample-shoe.jpg', 'shoes, nike, running'),
(1, 2, 'Samsung Galaxy A54', 450.00, 'USD', 'Smartphone with a bright display and long battery life.', 'sample-phone.jpg', 'phone, samsung, android');

INSERT INTO `cart` (`customer_id`, `product_id`, `qty`, `price_at_time`, `currency_at_time`) VALUES
(2, 1, 1, 120.00, 'USD');
