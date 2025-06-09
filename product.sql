-- Create the database
CREATE DATABASE IF NOT EXISTS product_data;
USE product_data;

-- Users table
CREATE TABLE users (
  id int(11) NOT NULL AUTO_INCREMENT,
  username varchar(50) NOT NULL,
  password varchar(255) NOT NULL,
  email varchar(100) NOT NULL,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  is_admin tinyint(1) DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY username (username),
  UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products table
CREATE TABLE products (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(100) NOT NULL,
  description text NOT NULL,
  price decimal(10,2) NOT NULL,
  image varchar(255) DEFAULT 'https://via.placeholder.com/300',
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cart table
CREATE TABLE cart (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  product_id int(11) NOT NULL,
  quantity int(11) NOT NULL DEFAULT 1,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY user_id (user_id),
  KEY product_id (product_id),
  CONSTRAINT cart_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT cart_ibfk_2 FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert admin user (password: admin123)
INSERT INTO users (username, password, email, is_admin) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 1);

-- Insert regular user (password: user123)
INSERT INTO users (username, password, email) VALUES
('user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user@example.com');


INSERT INTO `products` (`name`, `description`, `price`, `image`) VALUES
('Smartphone X', 'Latest smartphone with 6.5" display and triple camera', 58199.17, 'https://images.unsplash.com/photo-1592899677977-9c10ca588bbd'),
('Laptop Pro', 'Powerful laptop with 16GB RAM and 1TB SSD', 107299.17, 'https://images.unsplash.com/photo-1593642632823-8f785ba67e45'),
('Wireless Headphones', 'Noise-cancelling wireless headphones with 30h battery', 16559.17, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e'),
('Smart Watch', 'Fitness tracker with heart rate monitor and GPS', 12434.17, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30'),
('4K TV', '55-inch 4K Ultra HD Smart TV with HDR', 66359.17, 'https://images.unsplash.com/photo-1546538915-a9e2c8d6a3ba'),
('Digital Camera', '24MP DSLR camera with 18-55mm lens', 41499.17, 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32'),
('Gaming Console', 'Next-gen gaming console with 1TB storage', 41499.17, 'https://images.unsplash.com/photo-1607853202273-797f1c22a38e'),
('Bluetooth Speaker', 'Portable waterproof speaker with 20h playtime', 7469.17, 'https://images.unsplash.com/photo-1572569511254-d8f925fe2cbb');


