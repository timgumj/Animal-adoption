-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 04, 2024 at 05:26 PM
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
-- Database: `be22_exam5_animal_adoption_juliustimgum`
--
CREATE DATABASE IF NOT EXISTS `be22_exam5_animal_adoption_juliustimgum` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `be22_exam5_animal_adoption_juliustimgum`;

-- --------------------------------------------------------

--
-- Table structure for table `animal`
--

CREATE TABLE `animal` (
  `id` int(1) NOT NULL,
  `name` varchar(255) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `email` varchar(50) NOT NULL,
  `short_description` text DEFAULT NULL,
  `vaccinated` enum('Vaccinated','Not Vaccinated') NOT NULL,
  `address` varchar(255) NOT NULL,
  `size` varchar(255) NOT NULL,
  `age` text NOT NULL,
  `breed` varchar(200) NOT NULL,
  `status` varchar(220) NOT NULL DEFAULT '1',
  `user_id` int(11) NOT NULL DEFAULT 5,
  `photos` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `animal`
--

INSERT INTO `animal` (`id`, `name`, `image_path`, `email`, `short_description`, `vaccinated`, `address`, `size`, `age`, `breed`, `status`, `user_id`, `photos`) VALUES
(180, 'ZIGGY', 'pictures/66af951ddbcbb_1722783005.jpg', 'ziggy@gmail.com', 'Very adorable chilled out dog. You will enjoy the company with it.', 'Vaccinated', 'Hackengasse 40', '23', '8', 'Cavapor', 'Available', 5, NULL),
(181, 'KATI', 'pictures/66af99434c501_1722784067.jpg', 'kati@gmail.com', 'Cute cat, a bit old but still so sweet and nice.', 'Vaccinated', 'katengasse 2', '12', '9', 'Feline', 'Available', 5, NULL),
(182, 'MONI', 'pictures/66af998d8ffaa_1722784141.jpg', 'moni@gmail.com', 'Cute fun monkey, very well behaved. Get it.', 'Vaccinated', 'Hellagasse 4', '40', '10', 'Baboon', 'Available', 5, NULL),
(183, 'PAROTA', 'pictures/66af99df15aa9_1722784223.jpg', 'par@gmail.com', 'Sweet and always mysterious Parrot, you will love it.', 'Vaccinated', 'Mariahilfestrasse 3', '12', '6', 'Parrota', 'Available', 5, NULL),
(184, 'TINI', 'pictures/66af9a3133758_1722784305.jpg', 'small@gmail.com', 'Cute baby cat, looking for parent to stay with. Take it.', 'Vaccinated', 'Longastrasse 3', '6', '1', 'Feline', 'Available', 5, NULL),
(185, 'TOTI', 'pictures/66af9a86610b2_1722784390.jpg', 'toti@mail.com', 'Chilled Tourtoise, you will love it.', 'Vaccinated', 'Manistrasse 6', '17', '65', 'Tourtella', 'Available', 5, NULL),
(186, 'GOLI', 'pictures/66af9ad083ffd_1722784464.jpg', 'goli@mail.com', 'Nice gold fish, great for your indoor.', 'Vaccinated', 'Manistrasse 3', '5', '2', 'Gold fish', 'Available', 5, NULL),
(187, 'GOLDIE', 'pictures/66af9b0a12e5a_1722784522.jpg', 'go@mail.com', 'Another really cute gold fish', 'Vaccinated', 'ziglerstrasse 2', '2', '2', 'Gold Fish', 'Available', 5, NULL),
(188, 'CHI', 'pictures/66af9bdfbddbc_1722784735.jpg', 'chi@gmail.com', 'Great Chihuahua Dog breed looking for a owner', 'Vaccinated', 'alengasse 3', '9', '9', 'Chihuahua', 'Available', 5, NULL),
(189, 'CATO', 'pictures/66af9c1a0cf3a_1722784794.jpg', 'cato@gmai.com', 'Great cat, enjoy it.', 'Vaccinated', 'Helagasse 22', '8', '7', 'Feline', 'Available', 5, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pet_adoption`
--

CREATE TABLE `pet_adoption` (
  `id` int(1) NOT NULL,
  `adoption_date` date NOT NULL,
  `users_id` int(11) DEFAULT NULL,
  `animal_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `animal_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `photos`
--

CREATE TABLE `photos` (
  `id` int(11) NOT NULL,
  `animal_id` int(11) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `photos`
--

INSERT INTO `photos` (`id`, `animal_id`, `photo_path`) VALUES
(140, 180, 'pictures/66af951dded6e_1722783005_0.jpg'),
(141, 180, 'pictures/66af951ddf9dc_1722783005_1.jpg'),
(142, 180, 'pictures/66af951de02fe_1722783005_2.jpg'),
(143, 181, 'pictures/66af99434e6f0_1722784067_0.jpg'),
(144, 182, 'pictures/66af998d90ca4_1722784141_0.jpg'),
(145, 183, 'pictures/66af99df16628_1722784223_0.jpg'),
(146, 184, 'pictures/66af9a3134764_1722784305_0.jpg'),
(147, 185, 'pictures/66af9a8661da7_1722784390_0.jpg'),
(148, 186, 'pictures/66af9ad084d4b_1722784464_0.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(225) NOT NULL,
  `last_name` varchar(225) NOT NULL,
  `password` varchar(225) NOT NULL,
  `date_of_birth` date NOT NULL,
  `email` varchar(225) NOT NULL,
  `picture` varchar(225) NOT NULL,
  `status` varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `password`, `date_of_birth`, `email`, `picture`, `status`) VALUES
(1, 'Tim', 'Test', '$2y$10$juSkUqC/K6cTTUAnqTEw7uD9k7VHiqStFeqm57i5J3C2rNIsWIV0W', '0000-00-00', 'tim@gmail.com', '', 'acti'),
(2, 'Ngwainmbi Timgum', 'Julius', '$2y$10$pWrssB5ReoKAMM4y0PwsI.5M.WDUbsMoUGHxlcJXiim1YQ8RX.Aj6', '2024-07-13', 'timjay08@gmail.com', '', 'acti'),
(3, 'Jules', 'Tim', '$2y$10$9SsNDhjaY3ga0FS4bILwwu.WO092NdslJQkqNeTc9r.9FLzocOUAq', '2024-06-13', 'sixteenbars16@gmail.com', '', 'acti'),
(4, 'Ngwainmbi Timgum', 'Julius', '$2y$10$x6wdstzWRAGRV7cPLYSRwejJm0cfxzAXIDST32o/DUxKxM2tcOhD.', '2024-07-11', 'timjay8@gmail.com', '', ''),
(5, 'hihihi', 'hihi', '$2y$10$Xm.2M4TzAzfXQSEQNnavjenuuEqC7Z23vDyaNSgNnUz9ScJG7lXQK', '2024-07-06', 'hihihi@mail.com', '', 'adm'),
(6, 'cle', 'lia', '$2y$10$JE2xtR0Az/PItcupki1Jje61sOGb/Jd0S4/CDh6x/xCkyiIm0m9DS', '2024-07-10', 'clelia@mail.com', '', 'acti'),
(7, 'meme', 'julo', '$2y$10$3qylHocKFasKTmmGEzcgfuJb1GS.7bm.Cu54vQUBTkqAcF8e/4NiK', '2024-07-25', 'mimi@mail.com', '', 'acti'),
(8, 'juju', 'titi', '$2y$10$M.kT3SDAyQtybcy2WQ8dgeytLKsYkRwfHM/FbL1ddlwvDb3/r5xhy', '2024-08-28', 'juju@mail.com', '', 'acti'),
(9, 'isa', 'tim', '$2y$10$EB0y0BeuK3HA0baep48SKe6nOJz.Xq1HbPwaFWJiO2mh1w1e.VL5W', '2024-08-16', 'isa@mail.com', '', 'acti'),
(10, 'isabella', 'tim', '$2y$10$tQvCVN/yBKWIIbEhBiTHFe7DOLPrDGvLpHy2dgZoBWhcfo2K7Rc2G', '2024-08-09', 'isabella@mail.com', '', 'acti'),
(11, 'zala', 'zala', '$2y$10$xwppCR1har5o2vsIRxRZhuE2VXUdn75JIQQ9PkE404v/HmzuDSgGW', '2024-08-14', 'zala@mail.com', '', 'acti'),
(12, 'hihi', 'haha', '$2y$10$NG3eY3dRdu.DeQMAaxobf.ZPtU86Vu/SN9Cm.7tal61sa1EW/ChZq', '2024-08-08', 'haha@mail.com', '', 'acti'),
(13, 'abc', 'abc', '$2y$10$lJ1uUpDOiFspea7obAeOCOV7cnWU9r2M/SsGA5pof5IaEuQRvx4Ui', '2024-08-06', 'abc@mail.com', '66ae9e7b16635.jpg', 'acti'),
(14, 'abcde', 'abcde', '$2y$10$siT8VOdJVBshCFt/aJDjfOBU76d1KmAbWrXnWlQeYRCPjeD..d7MG', '2024-08-29', 'abcd@mail.com', '66aea2eeae0a8.png', 'acti'),
(15, 'jijiji', 'ju', '$2y$10$IbEWtC9mh454HKZ71U5.jucMwv2xiGqoIfLYUl0QwuTvBCjVwA8M6', '2024-08-14', 'jiji@mail.com', '66af8e741b42e.jpg', 'acti');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `animal`
--
ALTER TABLE `animal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_id` (`user_id`);

--
-- Indexes for table `pet_adoption`
--
ALTER TABLE `pet_adoption`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_users_id` (`users_id`),
  ADD KEY `fk_animals_id` (`animal_id`);

--
-- Indexes for table `photos`
--
ALTER TABLE `photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `animal_id` (`animal_id`) USING BTREE;

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `animal`
--
ALTER TABLE `animal`
  MODIFY `id` int(1) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=190;

--
-- AUTO_INCREMENT for table `pet_adoption`
--
ALTER TABLE `pet_adoption`
  MODIFY `id` int(1) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `photos`
--
ALTER TABLE `photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=149;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `animal`
--
ALTER TABLE `animal`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `pet_adoption`
--
ALTER TABLE `pet_adoption`
  ADD CONSTRAINT `fk_animals_id` FOREIGN KEY (`animal_id`) REFERENCES `animal` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_users_id` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `photos`
--
ALTER TABLE `photos`
  ADD CONSTRAINT `photos_ibfk_1` FOREIGN KEY (`animal_id`) REFERENCES `animal` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
