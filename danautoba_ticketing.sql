-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 13, 2025 at 02:18 PM
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
-- Database: `danautoba_ticketing`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id_booking` int(11) NOT NULL,
  `id` int(11) DEFAULT NULL,
  `hotel_id` int(11) DEFAULT NULL,
  `check_in` date DEFAULT NULL,
  `check_out` date DEFAULT NULL,
  `guests` int(11) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id_booking`, `id`, `hotel_id`, `check_in`, `check_out`, `guests`, `total_price`, `status`, `booking_date`) VALUES
(39, 14, 3, '2025-12-20', '2026-01-10', 1, 37800000.00, 'pending', '2025-12-13 00:15:33'),
(40, 16, 4, '2025-12-27', '2026-01-10', 1, 19600000.00, 'pending', '2025-12-13 00:16:15'),
(41, 15, 1, '2025-12-20', '2026-01-07', 1, 12600000.00, 'pending', '2025-12-13 00:16:56'),
(42, 17, 7, '2025-12-17', '2026-01-10', 1, 43200000.00, 'pending', '2025-12-13 00:17:34'),
(43, 18, 2, '2025-12-27', '2025-12-30', 1, 2580000.00, 'pending', '2025-12-13 00:20:02'),
(44, 18, 2, '2025-12-13', '2025-12-20', 1, 6020000.00, 'pending', '2025-12-13 02:46:33');

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id_detailPemesanan` int(11) NOT NULL,
  `id_booking` int(11) NOT NULL,
  `jumlah_kamar` int(10) NOT NULL,
  `Subtotal` int(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id_detailPemesanan`, `id_booking`, `jumlah_kamar`, `Subtotal`) VALUES
(36, 39, 1, 37800000),
(37, 40, 1, 19600000),
(38, 41, 1, 12600000),
(39, 42, 1, 43200000),
(40, 43, 1, 2580000),
(41, 44, 1, 6020000);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `subject`, `message`, `rating`, `created_at`) VALUES
(21, 14, 'Bagus', 'Tampilannya menarik dan mempermudah sekali', 5, '2025-12-13 02:55:12'),
(22, 15, 'Keren', 'Programmernya pasti keren keren', 5, '2025-12-13 02:55:38'),
(23, 17, 'Hai', 'Pemesanan nya mudah', 4, '2025-12-13 02:56:13'),
(24, 16, 'Halo', 'Saya tertarik dengan pemesanan di sistem ini', 4, '2025-12-13 02:57:13'),
(25, 18, 'Good', 'Tim hebat', 3, '2025-12-13 02:57:39');

-- --------------------------------------------------------

--
-- Table structure for table `hotels`
--

CREATE TABLE `hotels` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(200) DEFAULT NULL,
  `price_per_night` decimal(10,2) DEFAULT NULL,
  `amenities` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_recommended` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hotels`
--

INSERT INTO `hotels` (`id`, `name`, `description`, `location`, `price_per_night`, `amenities`, `image_url`, `is_recommended`, `created_by`, `created_at`) VALUES
(1, 'Niagara Hotel ', 'Hotel ini menawarkan 179 buah kamar dengan berbagai tipe seperti superior hingga superior deluxe', 'No. 1 Parapat, Danau Toba, Sumatra Utara', 700000.00, '[\"WiFi Gratis\", \"Kolam Renang\", \"Restoran\", \"Parkir Gratis\"]', 'hotelNiagara.jpg', 1, NULL, '2025-12-05 08:12:31'),
(2, 'Khas Parapat Hotel', 'Menawarkan kenyamanan seperti berada di rumah sendiri', 'Girsang Si Pangan Bolon, Simalungun', 860000.00, '[\"AC\", \"TV\", \"Minibar\", \"Balkon Pribadi\"]', 'hotel_693a411f5cfe11765425439.jpeg', 1, NULL, '2025-12-05 08:12:31'),
(3, 'Taman Simalem Ressort', 'Resort ini menawarkan keindahan Danau Toba dari ketinggian 1.200 meter dengan kenyamanan sempurna karena dilengkapi fasilitas yang super modern', 'Silahisabungan, Danau Toba', 1800000.00, '[\"Ampitheather\", \"Tempat Ibadah\", \"Flower Nursery\"]', 'hotel_693a3f7ba7e7e1765425019.jpeg', 0, NULL, '2025-12-05 08:12:31'),
(4, 'Labera Toba Hotel & Convention', 'Hotel ini menawarkan pemandangan alam Danau Toba yang menakjubkan sekaligus menenangkan', 'Parapat, Sumatera Utara', 1400000.00, '[\"AC\", \"TV\", \"Minibar\", \"Wi-Fi\", \"Rooftop\"]', 'hotel_693a3d7a206bf1765424506.jpeg', 1, NULL, '2025-12-05 08:12:31'),
(7, 'Mariana Ressort & Convention', 'Menawarkan akomodasi premium dipadu kemewahan dengan sentuhan budaya Batak', 'Tuktuk, Samosir', 1800000.00, '[\"AC\", \"WiFi\", \"Kolam Renang\", \"Area Parkir\", \"Layanan 24 jam\"]', 'hotel_693a3c68020771765424232.jpeg', 0, 1, '2025-12-11 03:37:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@danautoba.com', '$2a$12$YEI.3pgH9GoqvpABJL4wreYT1eoCMP8FUa2hDcAMM4Oat8xeum/Xi', 'admin', '2025-12-05 08:12:31'),
(14, 'Cristine Gultom', 'cristinegultom@gmail.com', '$2y$10$kO.jMfwQfMVWo6/sH2w3K.FiJzOb25r8Op8BZyZtz6KRKdhXW99L.', 'user', '2025-12-13 00:05:46'),
(15, 'Ernawati Hutasoit', 'erna@gmail.com', '$2y$10$bVCC9Hhotfmn4eyBjt7vw.SuU2q7Zx45uTtBrkN4xffgxboVe2nLO', 'user', '2025-12-13 00:10:34'),
(16, 'Marthin Lubis', 'marthin@gmail.com', '$2y$10$piZlwQIWzQj26ZSVGPx5yOVTzGV/8g4UHB3rAU78a6x5aAMOwUi3S', 'user', '2025-12-13 00:11:31'),
(17, 'Johannes Mario', 'johannes@gmail.com', '$2y$10$93Z5U5sk9NouPmf1TxNj9OMfv5yR7gz6SnjLLinTYFco3rwJvushK', 'user', '2025-12-13 00:12:22'),
(18, 'emjc', 'emjc@gmail.com', '$2y$10$AfDZ67C8XaIoS2sZ1XF.xeT6z0XiTJsI//drYqPGrbP7mG.UI93/q', 'user', '2025-12-13 00:19:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id_booking`),
  ADD KEY `user_id` (`id`),
  ADD KEY `hotel_id` (`hotel_id`);

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id_detailPemesanan`),
  ADD KEY `id_booking` (`id_booking`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `hotels`
--
ALTER TABLE `hotels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id_booking` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id_detailPemesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `hotels`
--
ALTER TABLE `hotels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`);

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`id_booking`) REFERENCES `bookings` (`id_booking`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
