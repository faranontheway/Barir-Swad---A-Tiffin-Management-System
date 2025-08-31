-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 29, 2025 at 06:09 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `barirswad`
--

-- --------------------------------------------------------

--
-- Table structure for table `complaint_support`
--

CREATE TABLE `complaint_support` (
  `User_ID` int(10) NOT NULL,
  `Complaint_ID` int(10) NOT NULL,
  `Description` text NOT NULL,
  `Status` enum('Open','In Progress','Resolved','Closed') NOT NULL DEFAULT 'Open',
  `Submitted_Date` date NOT NULL,
  `Messages` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_rates_cook`
--

CREATE TABLE `customer_rates_cook` (
  `Customer_ID` int(10) NOT NULL,
  `Cook_ID` int(10) NOT NULL,
  `Rating` decimal(3,2) NOT NULL,
  `Comment` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meal`
--

CREATE TABLE `meal` (
  `Meal_ID` int(10) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Description` text NOT NULL,
  `Proportion` varchar(50) NOT NULL,
  `Pricing List` decimal(10,2) NOT NULL,
  `Cuisine` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meal`
--

INSERT INTO `meal` (`Meal_ID`, `Name`, `Description`, `Proportion`, `Pricing List`, `Cuisine`) VALUES
(1, 'Murgi Thali', 'Rice, Chicken curry, Cashewnut Salad', '1:1', 150.00, 'Bengali'),
(2, 'Vorta Thali', 'Aloo Vorta, Shutki Vorta, Potol Vorta, Begoon Vorta, Dim Vorta, Morich Vorta', '1:1', 100.00, 'Bengali'),
(3, 'Indian Thali', 'Dosa, Sambar, Rayta, Pickle', '1:1', 120.00, 'Indian'),
(4, 'Chinese Thali', 'Chowmein, Dim Sum, Fried Rice, Chili Chicken', '1:1', 200.00, 'Chinese'),
(5, 'Korean Thali', 'Beef Ramen, Boiled eggs(x2), Dumplings', '1:1', 300.00, 'Korean'),
(6, 'Burmese Thali', 'Myanmar Curry, Mohinga, Tea leaf Salad, Tofu', '1:1', 300.00, 'Burmese'),
(7, 'Italian Thali', 'Prawn Spaghetti, Tiramisu, Margherita Pizza', '1:1', 500.00, 'Italian'),
(8, 'Veg Thali', 'Veg Biriyani, Tomato Curry, Green Salad', '1:1', 200.00, 'Continental'),
(9, 'Bengali Mach Thali', 'Shorisha Hilsha, Fresh Rupchanda, Loitta fry, Rice, Dal', '1:1', 600.00, 'Bengali');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `OrderID` int(10) NOT NULL,
  `Customer_ID` int(10) NOT NULL,
  `Cost` decimal(10,2) NOT NULL,
  `Status` enum('Pending','On the way','Accepted','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
  `Date` date NOT NULL,
  `Catering Service` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders_have_meal`
--

CREATE TABLE `orders_have_meal` (
  `M_ID` int(10) NOT NULL,
  `OrderID` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `U_ID` INT(10) NOT NULL AUTO_INCREMENT,
  `Email` VARCHAR(50) NOT NULL,
  `Exp_Years` INT(50) NOT NULL,
  `Name` VARCHAR(100) NOT NULL,
  `Address` VARCHAR(100) NOT NULL,
  `Type` ENUM('Admin','Customer','Cook') NOT NULL DEFAULT 'Customer',
  `Password` VARCHAR(15) NOT NULL,
  PRIMARY KEY (`U_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;





--
-- Dumping data for table `user`
--

INSERT INTO `user` (`U_ID`, `Email`, `Exp_Years`, `Name`, `Address`, `Type`, `Password`) VALUES
(1, 'mehjabin.hasan@gmail.com', 11, 'Mehjabin Hasan', '3/B Selpark, Mirpur', 'Cook', 'mou1111'),
(2, 'araf.cooker@gmail.com', 3, 'Araf Rakib', 'Sec-4, Uttara, Dhaka', 'Cook', 'araf4200'),
(101, 'admin101@gmail.com', 0, 'Admin01', '34/d Baily Road, Dhaka', 'Admin', 'admin123'),
(1001, 'farhan.zahin@gmail.com', 0, 'Farhan Zahin', '7/A Banasree, Dhaka', 'Customer', 'fz1234'),
(1002, 'ahona.hasan@gmail.com', 0, 'Ahona Hasan', '33/c Banani, Dhaka', 'Customer', 'ahona1234'),
(1003, 'jungkook@gmail.com', 0, 'Jung Kook', '16/f Gulshan-1, Dhaka', 'Customer', 'kook97');

-- --------------------------------------------------------

--
-- Table structure for table `user_cooks_meal`
--

CREATE TABLE `user_cooks_meal` (
  `Cook_ID` int(10) NOT NULL,
  `Meal_ID` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_cooks_meal`
--

INSERT INTO `user_cooks_meal` (`Cook_ID`, `Meal_ID`) VALUES
(1, 2),
(1, 3),
(1, 5),
(2, 3),
(2, 6);

-- --------------------------------------------------------

--
-- Table structure for table `user_phone_no`
--

CREATE TABLE `user_phone_no` (
  `User_ID` int(10) NOT NULL,
  `Phone_No` int(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_phone_no`
--

INSERT INTO `user_phone_no` (`User_ID`, `Phone_No`) VALUES
(1001, 1552306466),
(1002, 1316733425),
(1003, 1635895385);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `complaint_support`
--
ALTER TABLE `complaint_support`
  ADD PRIMARY KEY (`Complaint_ID`),
  ADD KEY `user_complaint_fk` (`User_ID`);

--
-- Indexes for table `customer_rates_cook`
--
ALTER TABLE `customer_rates_cook`
  ADD PRIMARY KEY (`Customer_ID`,`Cook_ID`),
  ADD KEY `cook_rate_fk` (`Cook_ID`);

--
-- Indexes for table `meal`
--
ALTER TABLE `meal`
  ADD PRIMARY KEY (`Meal_ID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`OrderID`),
  ADD KEY `customer_order_fk` (`Customer_ID`);

--
-- Indexes for table `orders_have_meal`
--
ALTER TABLE `orders_have_meal`
  ADD PRIMARY KEY (`M_ID`,`OrderID`),
  ADD KEY `order_meal_fk` (`OrderID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`U_ID`);

--
-- Indexes for table `user_cooks_meal`
--
ALTER TABLE `user_cooks_meal`
  ADD PRIMARY KEY (`Cook_ID`,`Meal_ID`),
  ADD KEY `meal_cook_fk` (`Meal_ID`);

--
-- Indexes for table `user_phone_no`
--
ALTER TABLE `user_phone_no`
  ADD PRIMARY KEY (`Phone_No`),
  ADD KEY `user_phone_fk` (`User_ID`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `complaint_support`
--
ALTER TABLE `complaint_support`
  ADD CONSTRAINT `user_complaint_fk` FOREIGN KEY (`User_ID`) REFERENCES `user` (`U_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `customer_rates_cook`
--
ALTER TABLE `customer_rates_cook`
  ADD CONSTRAINT `cook_rate_fk` FOREIGN KEY (`Cook_ID`) REFERENCES `user` (`U_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `customer_rate_fk` FOREIGN KEY (`Customer_ID`) REFERENCES `user` (`U_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `customer_order_fk` FOREIGN KEY (`Customer_ID`) REFERENCES `user` (`U_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders_have_meal`
--
ALTER TABLE `orders_have_meal`
  ADD CONSTRAINT `meal_order_fk` FOREIGN KEY (`M_ID`) REFERENCES `meal` (`Meal_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_meal_fk` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_cooks_meal`
--
ALTER TABLE `user_cooks_meal`
  ADD CONSTRAINT `cook_meal_fk` FOREIGN KEY (`Cook_ID`) REFERENCES `user` (`U_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `meal_cook_fk` FOREIGN KEY (`Meal_ID`) REFERENCES `meal` (`Meal_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_phone_no`
--
ALTER TABLE `user_phone_no`
  ADD CONSTRAINT `user_phone_fk` FOREIGN KEY (`User_ID`) REFERENCES `user` (`U_ID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
