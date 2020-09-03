-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 23, 2020 at 01:49 PM
-- Server version: 10.1.25-MariaDB
-- PHP Version: 5.6.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `matchons_cric_1`
--

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `address` text,
  `gst` varchar(15) NOT NULL,
  `logo` varchar(100) NOT NULL,
  `placeofsupply` varchar(50) NOT NULL,
  `hsn` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`id`, `name`, `address`, `gst`, `logo`, `placeofsupply`, `hsn`) VALUES
(1, 'Match on Sports', 'Indore', 'GSTNUMBER', 'http://matchonsports.com/images/logo.png', 'MP(13)', '998439');

-- --------------------------------------------------------

--
-- Table structure for table `state`
--

CREATE TABLE `state` (
  `StateID` int(11) NOT NULL,
  `CountryID` int(11) NOT NULL,
  `StateName` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `state`
--

INSERT INTO `state` (`StateID`, `CountryID`, `StateName`) VALUES
(1, 1, 'ANDHRA PRADESH'),
(2, 1, 'ASSAM'),
(3, 1, 'ARUNACHAL PRADESH'),
(4, 1, 'GUJRAT'),
(5, 1, 'BIHAR'),
(6, 1, 'HARYANA'),
(7, 1, 'HIMACHAL PRADESH'),
(8, 1, 'JAMMU & KASHMIR'),
(9, 1, 'KARNATAKA'),
(10, 1, 'KERALA'),
(11, 1, 'MADHYA PRADESH'),
(12, 1, 'MAHARASHTRA'),
(13, 1, 'MANIPUR'),
(14, 1, 'MEGHALAYA'),
(15, 1, 'MIZORAM'),
(16, 1, 'NAGALAND'),
(17, 1, 'ORISSA'),
(18, 1, 'PUNJAB'),
(19, 1, 'RAJASTHAN'),
(20, 1, 'SIKKIM'),
(21, 1, 'TAMIL NADU'),
(22, 1, 'TRIPURA'),
(23, 1, 'UTTAR PRADESH'),
(24, 1, 'WEST BENGAL'),
(25, 1, 'DELHI'),
(26, 1, 'GOA'),
(27, 1, 'PONDICHERY'),
(28, 1, 'LAKSHDWEEP'),
(29, 1, 'DAMAN & DIU'),
(30, 1, 'DADRA & NAGAR'),
(31, 1, 'CHANDIGARH'),
(32, 1, 'ANDAMAN & NICOBAR'),
(33, 1, 'UTTARANCHAL'),
(34, 1, 'JHARKHAND'),
(35, 1, 'CHATTISGARH');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `state`
--
ALTER TABLE `state`
  ADD PRIMARY KEY (`StateID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `company`
--
ALTER TABLE `company`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `state`
--
ALTER TABLE `state`
  MODIFY `StateID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
