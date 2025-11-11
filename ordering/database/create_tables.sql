-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `ordering` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE `ordering`;

-- Categories table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `item_type` enum('single-price','multi-price','flavors') NOT NULL DEFAULT 'single-price',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products table
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `image` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Product variations (for multi-price items)
CREATE TABLE IF NOT EXISTS `product_variations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `size` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_variations_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Product flavors (for flavor items)
CREATE TABLE IF NOT EXISTS `product_flavors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_flavors_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert some sample categories
INSERT INTO `categories` (`name`, `description`, `item_type`) VALUES
('Pizza', 'Delicious pizzas with various toppings', 'multi-price'),
('Pasta', 'Italian pasta dishes', 'single-price'),
('Drinks', 'Refreshing beverages', 'single-price'),
('Desserts', 'Sweet treats', 'flavors');

-- Insert some sample products
-- Note: These are just examples, you'll need to add actual images to the uploads folder
INSERT INTO `products` (`category_id`, `name`, `description`, `price`, `image`, `is_available`) VALUES
(1, 'Margherita Pizza', 'Classic pizza with tomato sauce, mozzarella, and basil', 0.00, '/uploads/products/pizza_margherita.jpg', 1),
(2, 'Spaghetti Carbonara', 'Creamy pasta with eggs, cheese, pancetta, and black pepper', 12.99, '/uploads/products/carbonara.jpg', 1),
(3, 'Cola', 'Refreshing cola drink', 2.50, '/uploads/products/cola.jpg', 1);

-- Insert some variations for the pizza (multi-price)
INSERT INTO `product_variations` (`product_id`, `size`, `price`, `is_available`) VALUES
(1, '12 inches', 10.99, 1),
(1, '14 inches', 14.99, 1),
(1, '16 inches', 18.99, 1);

-- Insert some flavors for desserts (flavors)
INSERT INTO `products` (`category_id`, `name`, `description`, `price`, `image`, `is_available`) VALUES
(4, 'Ice Cream', 'Creamy vanilla ice cream', 4.99, '/uploads/products/ice_cream.jpg', 1);

INSERT INTO `product_flavors` (`product_id`, `name`, `is_available`) VALUES
(4, 'Chocolate', 1),
(4, 'Vanilla', 1),
(4, 'Strawberry', 1);
