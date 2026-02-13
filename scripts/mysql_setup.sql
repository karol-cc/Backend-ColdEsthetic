-- Run this in MySQL Workbench (connected as a user with permissions, e.g. root)

CREATE DATABASE IF NOT EXISTS `coldesthetic`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- Create a dedicated app user (recommended)
-- Change the password below before running.
CREATE USER IF NOT EXISTS 'coldesthetic'@'localhost' IDENTIFIED BY 'CHANGE_ME_DEV_PASSWORD';
CREATE USER IF NOT EXISTS 'coldesthetic'@'127.0.0.1' IDENTIFIED BY 'CHANGE_ME_DEV_PASSWORD';

GRANT ALL PRIVILEGES ON `coldesthetic`.* TO 'coldesthetic'@'localhost';
GRANT ALL PRIVILEGES ON `coldesthetic`.* TO 'coldesthetic'@'127.0.0.1';

FLUSH PRIVILEGES;
