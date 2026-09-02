
--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT 'default_admin.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `name`, `email`, `password`, `phone`, `profile_picture`, `created_at`) VALUES
(1, 'System Admin', 'admin@company.com', '$2y$10$nJOW3sGB/dC070aqLAXVOeA3C6Rm1UJzn5F0.2qlon3BJGdr6nXUG', '1234567890', 'default_admin.png', '2025-11-05 11:49:17');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `check_in_time` time DEFAULT NULL,
  `check_out_time` time DEFAULT NULL,
  `status` enum('present','absent','late','half_day','on_leave') DEFAULT 'absent',
  `remarks` text DEFAULT NULL,
  `marked_by` enum('self','admin') DEFAULT 'self',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `description`, `created_at`) VALUES
(1, 'Human Resources', 'Manages employee relations and organizational culture', '2025-11-05 11:49:17'),
(2, 'IT Department', 'Information Technology and System Management', '2025-11-05 11:49:17'),
(3, 'Sales & Marketing', 'Business development and customer relations', '2025-11-05 11:49:17'),
(4, 'Finance & Accounts', 'Financial management and accounting', '2025-11-05 11:49:17'),
(5, 'Operations', 'Day-to-day business operations', '2025-11-05 11:49:17');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `shift_start` time DEFAULT '09:00:00',
  `shift_end` time DEFAULT '18:00:00',
  `joining_date` date NOT NULL,
  `profile_picture` varchar(255) DEFAULT 'default_employee.png',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `name`, `email`, `password`, `phone`, `department_id`, `designation`, `shift_start`, `shift_end`, `joining_date`, `profile_picture`, `status`, `created_at`) VALUES
(1, 'John Doe', 'john.doe@company.com', '$2y$10$QPJkXZY/NyStK.LkqHPdle.fz8/r6aE7Hzjhg1OQmKY7RoNsJBBcy', '9876543210', 2, 'Software Developer', '09:00:00', '18:00:00', '2024-01-15', 'default_employee.png', 'active', '2025-11-05 11:49:17'),
(2, 'Jane Smith', 'jane.smith@company.com', '$2y$10$QPJkXZY/NyStK.LkqHPdle.fz8/r6aE7Hzjhg1OQmKY7RoNsJBBcy', '9876543211', 3, 'Marketing Manager', '09:00:00', '18:00:00', '2024-02-20', 'default_employee.png', 'active', '2025-11-05 11:49:17'),
(3, 'Mike Johnson', 'mike.johnson@company.com', '$2y$10$QPJkXZY/NyStK.LkqHPdle.fz8/r6aE7Hzjhg1OQmKY7RoNsJBBcy', '9876543212', 4, 'Accountant', '09:00:00', '18:00:00', '2024-03-10', 'default_employee.png', 'active', '2025-11-05 11:49:17');

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `leave_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_type` enum('sick','casual','annual','emergency','unpaid') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_remarks` text DEFAULT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_type` enum('admin','employee') NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `log_id` int(11) NOT NULL,
  `user_type` enum('admin','employee') NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`log_id`, `user_type`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES
(1, 'employee', 0, 'Failed Login', 'Failed login attempt for email: john.doe@company.com', '::1', '2025-11-05 11:50:15'),
(2, 'employee', 0, 'Failed Login', 'Failed login attempt for email: john.doe@company.com', '::1', '2025-11-05 11:52:51'),
(3, 'employee', 0, 'Failed Login', 'Failed login attempt for email: john.doe@company.com', '::1', '2025-11-05 11:56:35'),
(4, 'employee', 1, 'Login', 'Employee logged in successfully', '::1', '2025-11-05 12:08:19'),
(5, 'employee', 1, 'Logout', 'Employee logged out', '::1', '2025-11-05 12:09:21'),
(6, 'employee', 1, 'Login', 'Employee logged in successfully', '::1', '2025-11-05 12:09:31'),
(7, 'admin', 1, 'Login', 'Admin logged in successfully', '::1', '2025-11-05 12:14:51'),
(8, 'employee', 1, 'Logout', 'Employee logged out', '::1', '2025-11-05 12:31:33'),
(9, 'admin', 1, 'Login', 'Admin logged in successfully', '::1', '2025-11-05 12:32:35'),
(10, 'employee', 1, 'Login', 'Employee logged in successfully', '::1', '2025-11-05 12:33:13'),
(11, 'employee', 1, 'Logout', 'Employee logged out', '::1', '2025-11-05 12:33:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD UNIQUE KEY `unique_attendance` (`employee_id`,`date`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`leave_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `processed_by` (`processed_by`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `leave_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL;

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_requests_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `admins` (`admin_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


