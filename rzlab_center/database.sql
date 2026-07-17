-- ============================================
-- RzLab Center - Database Schema & Dummy Data
-- ============================================

-- ============================================
-- USERS TABLE
-- ============================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `role` ENUM('admin','asisten','user') NOT NULL DEFAULT 'user',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO `users` (`username`, `password`, `email`, `role`) VALUES
('admin',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@rzlab.id',    'admin'),
('asisten1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'asisten1@rzlab.id', 'asisten'),
('asisten2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'asisten2@rzlab.id', 'asisten'),
('user1',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user1@rzlab.id',    'user');
-- Default password for all accounts: password

-- ============================================
-- INVENTORY TABLE
-- ============================================
DROP TABLE IF EXISTS `inventory`;
CREATE TABLE `inventory` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `item_code` VARCHAR(20) NOT NULL UNIQUE,
    `item_name` VARCHAR(150) NOT NULL,
    `brand` VARCHAR(100) NOT NULL,
    `category` ENUM('PC','Monitor','Laptop','Networking','Accessories') NOT NULL,
    `serial_number` VARCHAR(100) DEFAULT NULL,
    `condition` ENUM('Good','Maintenance','Damaged') NOT NULL DEFAULT 'Good',
    `lab_room` ENUM('Lab A','Lab B','Lab C') NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO `inventory` (`item_code`, `item_name`, `brand`, `category`, `serial_number`, `condition`, `lab_room`, `quantity`) VALUES
('PC-LAB001',  'Desktop PC Workstation',          'Dell',        'PC',          'SN-PC-2024-001', 'Good',        'Lab A', 20),
('PC-LAB002',  'Desktop PC Workstation',          'HP',          'PC',          'SN-PC-2024-002', 'Good',        'Lab B', 15),
('PC-LAB003',  'Desktop PC Gaming Spec',          'Lenovo',      'PC',          'SN-PC-2024-003', 'Maintenance', 'Lab C', 10),
('MON-LAB001', 'LED Monitor 24 inch',             'Samsung',     'Monitor',     'SN-MON-2024-001','Good',        'Lab A', 20),
('MON-LAB002', 'LED Monitor 22 inch',             'LG',          'Monitor',     'SN-MON-2024-002','Good',        'Lab B', 15),
('LAP-LAB001', 'Laptop Ultrabook 14 inch',        'ASUS',        'Laptop',      'SN-LAP-2024-001','Good',        'Lab A', 5),
('NET-LAB001', 'Managed Switch 24-Port',          'Cisco',       'Networking',  'SN-NET-2024-001','Good',        'Lab A', 3),
('NET-LAB002', 'Wireless Access Point',           'Ubiquiti',    'Networking',  'SN-NET-2024-002','Damaged',     'Lab C', 2),
('ACC-LAB001', 'Mechanical Keyboard RGB',         'Logitech',    'Accessories', 'SN-ACC-2024-001','Good',        'Lab A', 25),
('ACC-LAB002', 'Wireless Mouse',                  'Razer',       'Accessories', 'SN-ACC-2024-002','Good',        'Lab B', 20);

-- ============================================
-- DAMAGE REPORTS TABLE
-- ============================================
DROP TABLE IF EXISTS `damage_reports`;
CREATE TABLE `damage_reports` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `inventory_id` INT NOT NULL,
    `reporter_name` VARCHAR(100) NOT NULL,
    `reporter_email` VARCHAR(100) DEFAULT NULL,
    `damage_description` TEXT NOT NULL,
    `reported_condition` ENUM('Maintenance','Damaged') NOT NULL DEFAULT 'Maintenance',
    `report_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`inventory_id`) REFERENCES `inventory`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
