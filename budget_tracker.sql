create database if not exists budget_tracker;
use budget_tracker;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(75) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin', 'user') DEFAULT 'user',
  date_joined TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(20) CHECK (type IN ('income', 'expense')),
  name VARCHAR(50) NOT NULL,
  user_id INT NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS budgets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  category_id INT DEFAULT NULL,
  month VARCHAR(7) NOT NULL,
  amount_limit DECIMAL(10,2) NOT NULL,
  transactionId BIGINT UNSIGNED DEFAULT NULL,
  balance DECIMAL(10,2) DEFAULT 0.00,
  date_created DATETIME DEFAULT CURRENT_TIMESTAMP,
  date_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS transactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  category_id INT DEFAULT NULL,
  amount DECIMAL(10,2) NOT NULL,
  description TEXT DEFAULT NULL,
  date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

INSERT INTO users (username, email, password_hash, role)
VALUES ('Mungai', 'kyle@gmail.com', '$2y$10$/uHot4YmKEPszyLAyA/ANu3T0gPQTwvlqGa7Bhe.kDUKhUgBLZB2m', 'admin');

desc users;
SELECT DISTINCT role FROM users;

select * from users;
select * from transactions;
select * from budgets;


