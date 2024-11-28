-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 28, 2024 at 05:40 AM
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
  `nome` varchar(25) NOT NULL,
  `anno_scolastico` varchar(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `classe`
--

INSERT INTO `classe` (`id`, `nome`, `anno_scolastico`) VALUES
(2, '5AEE', '2024/2025'),
(1, '5AII', '2024/2025');

-- --------------------------------------------------------

--
-- Table structure for table `classe_studente`
--

CREATE TABLE `classe_studente` (
  `id_classe` int(11) NOT NULL,
  `cf_studente` varchar(16) NOT NULL,
  `periodo` varchar(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `classe_studente`
--

INSERT INTO `classe_studente` (`id_classe`, `cf_studente`, `periodo`) VALUES
(1, 'BNCLLN90A01F205X', '2024/2025'),
(2, 'VRNGPP96L20F205X', '2024/2025');

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

--
-- Dumping data for table `domanda`
--

INSERT INTO `domanda` (`id`, `testo`, `tipo`, `test_id`) VALUES
(1, 'Spiega in poche parole in cosa consiste l\'indirizzo \"Informatica\" dell\'istituto ITIS Max Planck', 'APERTA', 1),
(2, 'Quanti anni ha Favaro?', 'MULTIPLA', 1),
(13, 'Quale di questi linguaggi informatici e\' il peggiore? (uno e\', quello rimarra\' per sempre)', 'MULTIPLA', 1),
(14, 'Come si chiamo\' il prof di Informatica della 5AII nel periodo 2024/2025', 'MULTIPLA', 1),
(15, 'Quale fu lo studente con i voti piu\' bassi in 5AII 2024/2025', 'MULTIPLA', 1),
(16, 'Descrivi Edoardo Menegazzi', 'APERTA', 1),
(17, 'Quali sono le differenze tra IPv4 e IPv6', 'APERTA', 1);

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

--
-- Dumping data for table `opzioni_domanda`
--

INSERT INTO `opzioni_domanda` (`id`, `domanda_id`, `testo_opzione`, `corretta`) VALUES
(1, 2, '12', 0),
(2, 2, '3', 0),
(3, 2, '18', 1),
(4, 14, 'Biscaro Fabio', 1),
(5, 14, 'Tosato Paolo', 0),
(6, 14, 'Sartori Alex', 0),
(7, 14, 'Olivotto Roberto', 0),
(8, 13, 'Java', 0),
(9, 13, 'PHP', 0),
(10, 13, 'THIS -> Rust <- THIS', 1),
(11, 13, 'C', 0),
(12, 13, 'Python', 0),
(13, 15, 'Favaro Marco', 0),
(14, 15, 'Hakani Dajivid', 0),
(15, 15, 'Russo Michele', 0),
(16, 15, 'Sebastiano Tiveron', 0);

-- --------------------------------------------------------

--
-- Table structure for table `risposta`
--

CREATE TABLE `risposta` (
  `id` int(11) NOT NULL,
  `tentativo_id` int(11) NOT NULL,
  `domanda_id` int(11) NOT NULL,
  `risposta_aperta` text NOT NULL,
  `risposta_multipla_id` int(11) NOT NULL
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
  `data_inizio` datetime NOT NULL,
  `data_fine` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `sessione`
--

INSERT INTO `sessione` (`id`, `test_id`, `classe_id`, `cf_docente`, `data_inizio`, `data_fine`) VALUES
(2, 1, 1, 'RSSMRA85M01H501Z', '2024-11-28 13:00:00', '2024-12-04 13:55:00');

-- --------------------------------------------------------

--
-- Table structure for table `tentativo`
--

CREATE TABLE `tentativo` (
  `id` int(11) NOT NULL,
  `cf_studente` varchar(16) NOT NULL,
  `sessione_id` int(11) NOT NULL,
  `data_tentativo` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tentativo`
--

INSERT INTO `tentativo` (`id`, `cf_studente`, `sessione_id`, `data_tentativo`) VALUES
(2, 'VRNGPP96L20F205X', 2, '2024-11-28 04:50:43'),
(3, 'VRNGPP96L20F205X', 2, '2024-11-28 04:50:54');

-- --------------------------------------------------------

--
-- Table structure for table `test`
--

CREATE TABLE `test` (
  `id` int(11) NOT NULL,
  `titolo` varchar(100) NOT NULL,
  `cf_docente` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `test`
--

INSERT INTO `test` (`id`, `titolo`, `cf_docente`) VALUES
(1, 'Test di Prova', 'RSSMRA85M01H501Z');

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
-- Dumping data for table `utente`
--

INSERT INTO `utente` (`codice_fiscale`, `nome`, `cognome`, `login`, `password`, `ruolo`) VALUES
('BNCLLN90A01F205X', 'Marco', 'Favaro', 'effeemme', 'password', 'STUDENTE'),
('RSSMRA85M01H501Z', 'Mario', 'Rossi', 'mrossi', 'password', 'DOCENTE'),
('VRNGPP96L20F205X', 'Michele', 'Russo', 'sossic', 'password', 'STUDENTE');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `classe`
--
ALTER TABLE `classe`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_cls_anno` (`nome`,`anno_scolastico`);

--
-- Indexes for table `classe_studente`
--
ALTER TABLE `classe_studente`
  ADD PRIMARY KEY (`id_classe`,`cf_studente`),
  ADD KEY `fk_classe_studente` (`cf_studente`),
  ADD KEY `idx_cls_std` (`id_classe`,`cf_studente`) USING BTREE;

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
-- Indexes for table `risposta`
--
ALTER TABLE `risposta`
  ADD KEY `fk_risposta_tentativo` (`tentativo_id`),
  ADD KEY `fk_risposta_domanda` (`domanda_id`),
  ADD KEY `fk_risposta_multipla_opzione` (`risposta_multipla_id`);

--
-- Indexes for table `sessione`
--
ALTER TABLE `sessione`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sessione_test` (`test_id`),
  ADD KEY `fk_sessione_docente` (`cf_docente`),
  ADD KEY `fk_sessione_classe` (`classe_id`);

--
-- Indexes for table `tentativo`
--
ALTER TABLE `tentativo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tent_studente` (`cf_studente`),
  ADD KEY `fk_tent_sessione` (`sessione_id`);

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
  ADD PRIMARY KEY (`codice_fiscale`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `classe`
--
ALTER TABLE `classe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `domanda`
--
ALTER TABLE `domanda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `opzioni_domanda`
--
ALTER TABLE `opzioni_domanda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `sessione`
--
ALTER TABLE `sessione`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tentativo`
--
ALTER TABLE `tentativo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `test`
--
ALTER TABLE `test`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `classe_studente`
--
ALTER TABLE `classe_studente`
  ADD CONSTRAINT `fk_classe_classe` FOREIGN KEY (`id_classe`) REFERENCES `classe` (`id`),
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
-- Constraints for table `risposta`
--
ALTER TABLE `risposta`
  ADD CONSTRAINT `fk_risposta_domanda` FOREIGN KEY (`domanda_id`) REFERENCES `domanda` (`id`),
  ADD CONSTRAINT `fk_risposta_multipla_opzione` FOREIGN KEY (`risposta_multipla_id`) REFERENCES `opzioni_domanda` (`id`),
  ADD CONSTRAINT `fk_risposta_tentativo` FOREIGN KEY (`tentativo_id`) REFERENCES `tentativo` (`id`);

--
-- Constraints for table `sessione`
--
ALTER TABLE `sessione`
  ADD CONSTRAINT `fk_sessione_classe` FOREIGN KEY (`classe_id`) REFERENCES `classe` (`id`),
  ADD CONSTRAINT `fk_sessione_docente` FOREIGN KEY (`cf_docente`) REFERENCES `utente` (`codice_fiscale`),
  ADD CONSTRAINT `fk_sessione_test` FOREIGN KEY (`test_id`) REFERENCES `test` (`id`);

--
-- Constraints for table `tentativo`
--
ALTER TABLE `tentativo`
  ADD CONSTRAINT `fk_tent_sessione` FOREIGN KEY (`sessione_id`) REFERENCES `sessione` (`id`),
  ADD CONSTRAINT `fk_tent_studente` FOREIGN KEY (`cf_studente`) REFERENCES `utente` (`codice_fiscale`);

--
-- Constraints for table `test`
--
ALTER TABLE `test`
  ADD CONSTRAINT `fk_test_docente` FOREIGN KEY (`cf_docente`) REFERENCES `utente` (`codice_fiscale`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
