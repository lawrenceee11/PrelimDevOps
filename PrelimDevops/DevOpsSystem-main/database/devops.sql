-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 07, 2026 at 09:05 AM
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
-- Database: `devops`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(255) NOT NULL,
  `username` varchar(1000) NOT NULL,
  `password` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `username`, `password`) VALUES
(1, 'admin@admin', 'admin'),
(3, 'jay@admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `slots` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `date`, `start_time`, `end_time`, `slots`) VALUES
(5, '2026-01-09', '13:00:00', '14:00:00', 0),
(6, '2026-01-09', '15:00:00', '16:00:00', 3),
(7, '2026-01-10', '10:00:00', '11:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `id` int(11) NOT NULL,
  `name` varchar(1000) NOT NULL,
  `email` varchar(1000) NOT NULL,
  `message` varchar(1000) NOT NULL,
  `date_submitted` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enroll`
--

CREATE TABLE `enroll` (
  `id` int(255) NOT NULL,
  `username` varchar(1000) NOT NULL,
  `password` varchar(1000) NOT NULL,
  `email` varchar(1000) NOT NULL,
  `elemName` varchar(1000) NOT NULL,
  `elemYear` varchar(1000) NOT NULL,
  `juniorName` varchar(1000) NOT NULL,
  `juniorYear` varchar(1000) NOT NULL,
  `seniorName` varchar(1000) NOT NULL,
  `seniorYear` varchar(255) NOT NULL,
  `lastname` varchar(1000) NOT NULL,
  `firstname` varchar(1000) NOT NULL,
  `middlename` varchar(100) NOT NULL,
  `sex` varchar(100) NOT NULL,
  `dob` date NOT NULL,
  `phonenumber` varchar(20) DEFAULT NULL,
  `guardianName` varchar(1000) NOT NULL,
  `guardianPhoneNumber` varchar(20) DEFAULT NULL,
  `guardianAddress` varchar(1000) NOT NULL,
  `course` varchar(1000) NOT NULL,
  `year` varchar(1000) NOT NULL,
  `classroom` varchar(50) DEFAULT NULL,
  `section` varchar(10) DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `appointment_date` date NOT NULL,
  `time` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enroll`
--

INSERT INTO `enroll` (`id`, `username`, `password`, `email`, `elemName`, `elemYear`, `juniorName`, `juniorYear`, `seniorName`, `seniorYear`, `lastname`, `firstname`, `middlename`, `sex`, `dob`, `phonenumber`, `guardianName`, `guardianPhoneNumber`, `guardianAddress`, `course`, `year`, `classroom`, `section`, `status`, `appointment_date`, `time`) VALUES
(24, 'asdasdas', 'adasd', 'asdasd@adas', 'sadasds', '2312', 'asdasd', '12312', 'dasdas', '21312', 'sadasd', 'asd', 'adas', 'Male', '2026-01-21', 231312, 'sdasd', 213213, 'adads', 'BS Computer Science', 'Second Year', NULL, 'B', 'APPROVED', '2026-01-09', '3:00 PM - 4:00 PM');

-- --------------------------------------------------------

--
-- Table structure for table `feature_card`
--

CREATE TABLE `feature_card` (
  `id` int(11) NOT NULL,
  `header` varchar(255) NOT NULL DEFAULT 'Featured',
  `title` varchar(255) NOT NULL DEFAULT 'Special title treatment',
  `body` text NOT NULL,
  `footer` varchar(255) NOT NULL DEFAULT '',
  `bg_color` varchar(20) NOT NULL DEFAULT '#ffffff'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feature_card`
--

INSERT INTO `feature_card` (`id`, `header`, `title`, `body`, `footer`, `bg_color`) VALUES
(8, 'Featured', 'Cleanliness', 'vital for student health, academic focus, and overall well-being, reducing germ spread, improving concentration, and fostering a positive learning atmosphere', '2 days ago', '#66f98b'),
(9, 'Featured', 'Hospitality', 'specialized educational programs (like Bachelor of Science in Hospitality Management) that train students for careers in hotels, restaurants, tourism, and events,', '2 days ago', '#68bee8');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `instructor` varchar(100) NOT NULL,
  `prelim` decimal(5,2) DEFAULT NULL,
  `midterm` decimal(5,2) DEFAULT NULL,
  `finals` decimal(5,2) DEFAULT NULL,
  `average` decimal(5,2) DEFAULT NULL,
  `remarks` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `home_cards`
--

CREATE TABLE `home_cards` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `home_cards`
--

INSERT INTO `home_cards` (`id`, `title`, `description`, `image_path`, `status`, `sort_order`) VALUES
(4, 'Content Management System', 'Using a CMS is the ability to easily set up and manage website elements such as carousels, blogs, footers, and more. For example, you can easily create and manage a carousel, which is a slideshow of images or other content, to showcase your services on your website. You can also set up and manage a blog, which allows you to regularly publish new content to your website.', 'image/home_cards/card_695de4da676c61.40477931.jpg', 1, 1),
(5, 'Basic Messaging System', 'A basic messaging app can be a valuable tool for students or users to communicate with an administrator or other authorized parties. With this app, users can send messages that will be displayed in an inbox, where they can be reviewed and responded to as needed.', 'image/home_cards/card_695de2f2f2dbf1.07868084.jpg', 1, 2),
(6, 'Digital Enrollment System', 'Enrollment systems typically include a wide range of features, including student registration, course management, academic records management, and reporting. Students can use the system to select and register for courses', 'image/home_cards/card_695de38e6268e8.68553922.jpg', 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `course` varchar(255) NOT NULL,
  `instructor` varchar(255) DEFAULT NULL,
  `year_level` varchar(20) DEFAULT NULL,
  `hours` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `course`, `instructor`, `year_level`, `hours`) VALUES
(1, 'automata', 'BS Computer Science', 'Sir Gi', 'II', 3),
(3, 'computer programming', 'BS Computer Science', 'Sir Gi', 'III', 5),
(5, 'devops', 'BS Computer Engineering', 'Sir Gi', 'II', 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `enroll`
--
ALTER TABLE `enroll`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feature_card`
--
ALTER TABLE `feature_card`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `home_cards`
--
ALTER TABLE `home_cards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `enroll`
--
ALTER TABLE `enroll`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `feature_card`
--
ALTER TABLE `feature_card`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `home_cards`
--
ALTER TABLE `home_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

-- Ensure year_level can store full names like "Second Year"
ALTER TABLE `subjects` MODIFY `year_level` varchar(20) DEFAULT NULL;

-- Ensure enroll phone columns can store typical phone numbers (fixes numeric overflow / truncation issues)
ALTER TABLE `enroll` MODIFY `phonenumber` varchar(20) DEFAULT NULL;
ALTER TABLE `enroll` MODIFY `guardianPhoneNumber` varchar(20) DEFAULT NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
