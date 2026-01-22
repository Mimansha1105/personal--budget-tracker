-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 22, 2026 at 07:23 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dailyexpense`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_table`
--

CREATE TABLE `account_table` (
  `account_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `account_name` varchar(50) DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_table`
--

INSERT INTO `account_table` (`account_id`, `user_id`, `account_name`, `balance`) VALUES
(11, 19, 'amay', 878500.00),
(14, 23, 'cusat', 12001.00),
(15, 23, 'cusat', 1.00),
(16, 31, 'cucek', 10000.00),
(17, 31, 'mimansha', 1010080.00),
(18, 33, 'maim', 13993.00),
(19, 34, 'test4', 1000.00),
(20, 36, 'bharti', 10000.00),
(21, 37, 'hamil', 10000.00),
(22, 38, 'nilu', 5000.00);

-- --------------------------------------------------------

--
-- Table structure for table `budget_table`
--

CREATE TABLE `budget_table` (
  `budget_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budget_table`
--

INSERT INTO `budget_table` (`budget_id`, `user_id`, `amount`) VALUES
(1, 13, 25000.00),
(2, 14, 40000.00),
(3, 15, 45000.00),
(4, 15, 10000.00),
(26, 19, 5000.00),
(27, 19, 4000.00),
(28, 19, 4000.00),
(29, 19, 4000.00),
(30, 19, 5000.00),
(31, 19, 5000.00),
(32, 19, 6000.00),
(33, 19, 6000.00),
(34, 19, 1000.00),
(35, 19, 6000.00),
(36, 19, 2000.00),
(37, 19, 500000.00),
(38, 20, 8000.00),
(39, 21, 25000.00),
(40, 20, 9000.00),
(41, 20, 700.00),
(42, 23, 10000.00),
(43, 23, 1.00),
(45, 31, 10000.00),
(46, 33, 7000.00),
(47, 33, 3000.00),
(48, 34, 800.00),
(49, 37, 6000.00),
(50, 38, 3000.00);

-- --------------------------------------------------------

--
-- Table structure for table `category_table`
--

CREATE TABLE `category_table` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category_table`
--

INSERT INTO `category_table` (`category_id`, `category_name`) VALUES
(1, 'Medicine'),
(2, 'Food'),
(3, 'Bills & Recharges'),
(4, 'Entertainment'),
(5, 'Clothings'),
(6, 'Rent'),
(7, 'Household Items'),
(8, 'Others');

-- --------------------------------------------------------

--
-- Table structure for table `savings_goals`
--

CREATE TABLE `savings_goals` (
  `goal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `targetdate` date DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `savings_goals`
--

INSERT INTO `savings_goals` (`goal_id`, `user_id`, `amount`, `targetdate`) VALUES
(1, 13, 1000.00, '2025-03-29'),
(19, 23, 2000.00, '2025-04-30'),
(20, 31, 100.00, '2025-10-06'),
(21, 31, 100.00, '2025-10-06'),
(22, 31, 500.00, '2025-10-06'),
(23, 31, 200.00, '2025-10-06'),
(24, 31, 200.00, '2025-10-06'),
(25, 31, 200.00, '2025-10-06'),
(26, 31, 77.00, '2025-10-06'),
(27, 33, 1000.00, '2025-11-12'),
(28, 34, 200.00, '2025-11-30'),
(29, 37, 2000.00, '2025-12-31'),
(30, 37, 500.00, '2025-12-02'),
(31, 37, 5000.00, '2025-12-02'),
(32, 38, 1000.00, '2025-12-31');

-- --------------------------------------------------------

--
-- Table structure for table `transaction_table`
--

CREATE TABLE `transaction_table` (
  `expense_id` int(20) NOT NULL,
  `user_id` varchar(15) NOT NULL,
  `expense` int(20) NOT NULL,
  `expensedate` varchar(15) NOT NULL,
  `expensecategory` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `transaction_table`
--

INSERT INTO `transaction_table` (`expense_id`, `user_id`, `expense`, `expensedate`, `expensecategory`) VALUES
(101, '9', 789, '2023-08-31', 'Medicine'),
(102, '9', 3, '2023-08-31', 'Entertainment'),
(103, '9', 469, '2023-08-29', 'Clothings'),
(104, '9', 985, '2023-08-25', 'Entertainment'),
(128, '14', 5000, '2025-03-30', 'Medicine'),
(129, '14', 20000, '2025-03-30', 'Bills & Recharges'),
(130, '14', 100, '2025-03-30', 'Clothings'),
(131, '14', 3000, '2025-03-30', 'Household Items'),
(132, '14', 40000, '2025-03-30', 'Medicine'),
(133, '13', 80, '2025-03-31', 'Entertainment'),
(134, '15', 500, '2025-04-01', 'Medicine'),
(135, '15', 1000, '2025-04-01', 'Food'),
(136, '15', 2000, '2025-04-01', 'Bills & Recharges'),
(137, '15', 500, '2025-04-01', 'Medicine'),
(138, '15', 80, '2025-04-02', 'Medicine'),
(139, '16', 1000, '2025-04-01', 'Food'),
(140, '16', 2000, '2025-04-16', 'Bills & Recharges'),
(141, '16', 5000, '2025-04-06', 'Rent'),
(142, '16', 10000, '2025-04-01', 'Household Items'),
(143, '16', 1000, '2025-04-01', 'Others'),
(144, '18', 500, '2025-04-02', 'Food'),
(145, '18', 1000, '2025-04-03', 'Entertainment'),
(146, '18', 5000, '2025-04-15', 'Rent'),
(148, '19', 15000, '2025-04-05', 'Medicine'),
(149, '19', 15000, '2025-04-08', 'Rent'),
(150, '19', 10000, '2025-04-24', 'Household Items'),
(152, '19', 500, '2025-04-05', 'Medicine'),
(153, '19', 1000, '2025-04-05', 'Medicine'),
(154, '19', 1000, '2025-04-05', 'Medicine'),
(155, '19', 1000, '2025-04-05', 'Others'),
(156, '19', 1000, '2025-04-05', 'Others'),
(157, '19', 1000, '2025-04-05', 'Others'),
(158, '19', 1000, '2025-04-05', 'Others'),
(159, '19', 500, '2025-04-05', 'Medicine'),
(160, '19', 1000, '2025-04-05', 'Medicine'),
(161, '19', 5000, '2025-04-05', 'Clothings'),
(163, '20', 100, '2025-04-19', 'Medicine'),
(164, '20', 4000, '2025-04-19', 'Food'),
(165, '20', 1000, '2025-04-19', 'Medicine'),
(168, '21', 1000, '2025-04-20', 'Medicine'),
(169, '21', 3500, '2025-04-20', 'Entertainment'),
(170, '21', 500, '2025-04-20', 'Medicine'),
(171, '21', 25000, '2025-04-20', 'Medicine'),
(172, '20', 900, '2025-04-20', 'Medicine'),
(173, '20', 9000, '2025-04-20', 'Medicine'),
(174, '23', 9500, '2025-04-21', 'Medicine'),
(175, '23', 500, '2025-04-19', 'Household Items'),
(176, '23', 1, '2025-04-21', 'Medicine'),
(178, '33', 2000, '2025-11-12', 'Food'),
(179, '33', 500, '2025-11-14', 'Medicine'),
(180, '34', 3000, '2025-11-30', 'Medicine'),
(181, '36', 5500, '2025-12-01', 'Medicine'),
(182, '37', 500, '2025-12-02', 'Medicine'),
(183, '37', 3000, '2025-12-02', 'Medicine'),
(184, '37', 1000, '2025-12-02', 'Medicine'),
(185, '37', 1600, '2025-12-02', 'Entertainment'),
(186, '38', 1000, '2025-12-03', 'Medicine'),
(187, '38', 1000, '2025-12-11', 'Entertainment'),
(188, '38', 1100, '2025-12-03', 'Medicine'),
(189, '38', 1000, '2025-12-03', 'Medicine');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(25) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `firstname`, `lastname`, `email`, `password`) VALUES
(25, 'hey', 'you', 'heyyou@gmail.com', '$2y$10$KOigmqhy27OUIgI3rRNJC.79NPaiNIJzgkuoXM8tmOr'),
(26, 'mimansha', 'mishra', 'mimansha.clg@gmail.com', '$2y$10$tQWwafENTUFKsnU83C/JYOLQH8FLg00uQyVfAxnA1Lu'),
(27, 'k', 'v', 'kv@gmail.com', '$2y$10$G2HyfHfsBIGQbgbxKhlqg./HN3QbTr9s1EM4/Y3wbJA'),
(28, 'amay', 'mishra', 'amay@gmail.com', '$2y$10$K4oLm4dYFaAqalwVw/.BqeR0eYMPIa4s61r2lETWg5hSs7ZGHiLtG'),
(29, 'lalu', 'yadav', 'lalu@gmail.com', '$2y$10$fYiP5FrWjgeVOkfgCOzINOAFBYWCE0I9Vo1EG8M7bZ6lorJK4DAY2'),
(30, 'nitish', 'kumar', 'nitish@gmail.com', '$2y$10$upqXwGhYaFX5t.TCIPJoFOr4IHQirK2uxvpcsTb1dGZLceoTNJdR2'),
(32, 'test', 'two', 'testwo@gmai.com', '$2y$10$8FbXxpt//RmcRkk.g6CeFew9l1EVQLjGT.2MHYlo9iTVHjzbx35We'),
(33, 'test', 'three', 'test3@gmail.com', '$2y$10$J7yq2cQDlSphwoAiFR3F/.78GWfc5b5tvMK7pT96NAdZxMI5pVwwu'),
(34, 'Test', '4', 'test4@gmail.com', '$2y$10$eJPRVYLu.iNb0dw9oA5ek.PnPVOa9SKMkjiyLbTI4UPYHRzYQFfjC'),
(35, 'bharti', 'kumari', 'bharti@gmail.com', '$2y$10$POVnCW8V5nKkhsN/LE6Hzu5Hjiw0Ueo8m2hTk6oaG9OlXht.9nWCK'),
(36, 'bharti', 'kumari', 'bharti.clg@gmail.com', '$2y$10$AkEvKQS4TmBDTf5RJlFdAet2auIs3uVPWGtCK2yReQH.g8GzZHgdm'),
(37, 'hamil', 'hashir', 'hamil@gmail.com', '$2y$10$7utMlqXtkGlSJjifN0xVWuCcmgVAFT8CwzbQB1TsBYtgMBHYHWdFC'),
(38, 'nilu', 'mishra', 'nilu@gmail.com', '$2y$10$crhuP3QMQioAqDXsA9LU2ev0zUbI6oe2Re78AhLHCMbkn13bGDgtK');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_table`
--
ALTER TABLE `account_table`
  ADD PRIMARY KEY (`account_id`);

--
-- Indexes for table `budget_table`
--
ALTER TABLE `budget_table`
  ADD PRIMARY KEY (`budget_id`);

--
-- Indexes for table `category_table`
--
ALTER TABLE `category_table`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `savings_goals`
--
ALTER TABLE `savings_goals`
  ADD PRIMARY KEY (`goal_id`);

--
-- Indexes for table `transaction_table`
--
ALTER TABLE `transaction_table`
  ADD PRIMARY KEY (`expense_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_table`
--
ALTER TABLE `account_table`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `budget_table`
--
ALTER TABLE `budget_table`
  MODIFY `budget_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `category_table`
--
ALTER TABLE `category_table`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `savings_goals`
--
ALTER TABLE `savings_goals`
  MODIFY `goal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `transaction_table`
--
ALTER TABLE `transaction_table`
  MODIFY `expense_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=190;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
