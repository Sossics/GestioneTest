-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 25, 2024 at 03:30 PM
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
-- Database: `gestione_test`
--

-- --------------------------------------------------------

--
-- Table structure for table `classe`
--

CREATE TABLE `classe` (
  `id` int(11) NOT NULL,
  `nome` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classe_studente`
--

CREATE TABLE `classe_studente` (
  `id` int(11) NOT NULL,
  `id_classe` int(11) NOT NULL,
  `cf_studente` varchar(16) NOT NULL,
  `periodo` varchar(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `domanda`
--

CREATE TABLE `domanda` (
  `id` int(11) NOT NULL,
  `testo` text NOT NULL,
  `tipo` enum('APERTA','MULTIPLA','','') NOT NULL,
  `test_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `opzioni_domanda`
--

CREATE TABLE `opzioni_domanda` (
  `id` int(11) NOT NULL,
  `domanda_id` int(11) NOT NULL,
  `testo_opzione` varchar(500) NOT NULL,
  `corretta` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessione`
--

CREATE TABLE `sessione` (
  `id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `classe_id` int(11) NOT NULL,
  `cf_docente` varchar(16) NOT NULL,
  `data_inizio` date NOT NULL,
  `data_fine` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `test`
--

CREATE TABLE `test` (
  `id` int(11) NOT NULL,
  `titolo` varchar(100) NOT NULL,
  `cf_docente` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `utente`
--

CREATE TABLE `utente` (
  `codice_fiscale` varchar(16) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `login` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `ruolo` enum('STUDENTE','DOCENTE','','') NOT NULL DEFAULT 'STUDENTE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `classe`
--
ALTER TABLE `classe`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `classe_studente`
--
ALTER TABLE `classe_studente`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_cls_std` (`id_classe`,`cf_studente`,`periodo`),
  ADD KEY `fk_classe_studente` (`cf_studente`);

--
-- Indexes for table `domanda`
--
ALTER TABLE `domanda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_domande_test` (`test_id`);

--
-- Indexes for table `opzioni_domanda`
--
ALTER TABLE `opzioni_domanda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_opzioni_domanda` (`domanda_id`);

--
-- Indexes for table `sessione`
--
ALTER TABLE `sessione`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sessione_test` (`test_id`),
  ADD KEY `fk_sessione_docente` (`cf_docente`);

--
-- Indexes for table `test`
--
ALTER TABLE `test`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_test_docente` (`cf_docente`);

--
-- Indexes for table `utente`
--
ALTER TABLE `utente`
  ADD PRIMARY KEY (`codice_fiscale`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `classe`
--
ALTER TABLE `classe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `classe_studente`
--
ALTER TABLE `classe_studente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `domanda`
--
ALTER TABLE `domanda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `opzioni_domanda`
--
ALTER TABLE `opzioni_domanda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sessione`
--
ALTER TABLE `sessione`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `test`
--
ALTER TABLE `test`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `classe_studente`
--
ALTER TABLE `classe_studente`
  ADD CONSTRAINT `fk_classe_studente` FOREIGN KEY (`cf_studente`) REFERENCES `utente` (`codice_fiscale`);

--
-- Constraints for table `domanda`
--
ALTER TABLE `domanda`
  ADD CONSTRAINT `fk_domande_test` FOREIGN KEY (`test_id`) REFERENCES `test` (`id`);

--
-- Constraints for table `opzioni_domanda`
--
ALTER TABLE `opzioni_domanda`
  ADD CONSTRAINT `fk_opzioni_domanda` FOREIGN KEY (`domanda_id`) REFERENCES `domanda` (`id`);

--
-- Constraints for table `sessione`
--
ALTER TABLE `sessione`
  ADD CONSTRAINT `fk_sessione_docente` FOREIGN KEY (`cf_docente`) REFERENCES `utente` (`codice_fiscale`),
  ADD CONSTRAINT `fk_sessione_test` FOREIGN KEY (`test_id`) REFERENCES `test` (`id`);

--
-- Constraints for table `test`
--
ALTER TABLE `test`
  ADD CONSTRAINT `fk_test_docente` FOREIGN KEY (`cf_docente`) REFERENCES `utente` (`codice_fiscale`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
