-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 02, 2025 at 12:47 PM
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
-- Table structure for table `customer_rates_cooks`
--

CREATE TABLE `customer_rates_cooks` (
  `ReviewID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `CookID` int(11) NOT NULL,
  `Rating` decimal(2,1) DEFAULT NULL CHECK (`Rating` >= 1 and `Rating` <= 5),
  `Comment` text DEFAULT NULL,
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp()
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
  `Pricing` decimal(10,2) NOT NULL,
  `Cuisine` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meal`
--

INSERT INTO `meal` (`Meal_ID`, `Name`, `Description`, `Proportion`, `Pricing`, `Cuisine`) VALUES
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
  `OrderID` int(11) NOT NULL,
  `Customer_ID` int(10) NOT NULL,
  `Cost` decimal(10,2) NOT NULL,
  `Status` enum('Pending','On the way','Accepted','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
  `Date` date NOT NULL,
  `Catering Service` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`OrderID`, `Customer_ID`, `Cost`, `Status`, `Date`, `Catering Service`) VALUES
(1, 1, 300.00, 'Pending', '2025-09-01', 0),
(2, 1, 200.00, 'Pending', '2025-09-01', 0),
(3, 1, 120.00, 'Pending', '2025-09-01', 0),
(4, 1, 270.00, 'Pending', '2025-09-01', 0),
(5, 1, 150.00, 'Pending', '2025-09-01', 0),
(6, 1004, 350.00, 'Pending', '2025-09-02', 0),
(7, 1004, 100.00, 'Pending', '2025-09-02', 0),
(8, 1004, 300.00, 'Pending', '2025-09-02', 0),
(9, 1004, 300.00, 'Pending', '2025-09-02', 0);

-- --------------------------------------------------------

--
-- Table structure for table `orders_have_meal`
--

CREATE TABLE `orders_have_meal` (
  `M_ID` int(10) NOT NULL,
  `OrderID` int(10) NOT NULL,
  `Quantity` int(11) NOT NULL DEFAULT 1,
  `Price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders_have_meal`
--

INSERT INTO `orders_have_meal` (`M_ID`, `OrderID`, `Quantity`, `Price`) VALUES
(1, 4, 1, 150.00),
(1, 5, 1, 150.00),
(1, 6, 1, 150.00),
(2, 6, 2, 100.00),
(2, 7, 1, 100.00),
(3, 3, 1, 120.00),
(3, 4, 1, 120.00),
(4, 2, 1, 200.00),
(5, 8, 1, 300.00),
(6, 9, 1, 300.00);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `U_ID` int(10) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `Exp_Years` int(50) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Address` varchar(100) NOT NULL,
  `Type` enum('Admin','Customer','Cook','') NOT NULL DEFAULT 'Customer',
  `Password` varchar(15) NOT NULL
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
(1003, 'jungkook@gmail.com', 0, 'Jung Kook', '16/f Gulshan-1, Dhaka', 'Customer', 'kook97'),
(1004, 'farzana.eti@gmail.com', 0, 'Farzana Eti', 'mirpur', 'Customer', '1234');

--
-- Triggers `user`
--
DELIMITER $$
CREATE TRIGGER `auto_assign_user_id` BEFORE INSERT ON `user` FOR EACH ROW BEGIN
    DECLARE next_user_id INT DEFAULT 0;
    
    -- Only assign ID if it's not provided (0 or NULL)
    IF NEW.U_ID IS NULL OR NEW.U_ID = 0 THEN
        -- Get the next ID for this user type
        SELECT next_id INTO next_user_id 
        FROM user_id_tracker 
        WHERE user_type = NEW.Type;
        
        -- Assign the ID
        SET NEW.U_ID = next_user_id;
        
        -- Update the tracker for next time
        UPDATE user_id_tracker 
        SET next_id = next_id + 1 
        WHERE user_type = NEW.Type;
    END IF;
END
$$
DELIMITER ;

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
-- Table structure for table `user_id_tracker`
--

CREATE TABLE `user_id_tracker` (
  `user_type` enum('Cook','Customer','Admin') NOT NULL,
  `next_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_id_tracker`
--

INSERT INTO `user_id_tracker` (`user_type`, `next_id`) VALUES
('Cook', 3),
('Customer', 1005),
('Admin', 102);

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
-- Indexes for table `customer_rates_cooks`
--
ALTER TABLE `customer_rates_cooks`
  ADD PRIMARY KEY (`ReviewID`),
  ADD KEY `cook_rate_fk` (`CookID`),
  ADD KEY `customer_rate_fk` (`CustomerID`);

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
-- Indexes for table `user_id_tracker`
--
ALTER TABLE `user_id_tracker`
  ADD PRIMARY KEY (`user_type`);

--
-- Indexes for table `user_phone_no`
--
ALTER TABLE `user_phone_no`
  ADD PRIMARY KEY (`Phone_No`),
  ADD KEY `user_phone_fk` (`User_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customer_rates_cooks`
--
ALTER TABLE `customer_rates_cooks`
  MODIFY `ReviewID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `complaint_support`
--
ALTER TABLE `complaint_support`
  ADD CONSTRAINT `user_complaint_fk` FOREIGN KEY (`User_ID`) REFERENCES `user` (`U_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `customer_rates_cooks`
--
ALTER TABLE `customer_rates_cooks`
  ADD CONSTRAINT `cook_rate_fk` FOREIGN KEY (`CookID`) REFERENCES `user` (`U_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `customer_rate_fk` FOREIGN KEY (`CustomerID`) REFERENCES `user` (`U_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

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
