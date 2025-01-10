-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Gen 10, 2025 alle 11:21
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.0.30

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
-- Struttura della tabella `classe`
--

CREATE TABLE `classe` (
  `id` int(11) NOT NULL,
  `nome` varchar(25) NOT NULL,
  `anno_scolastico` varchar(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dump dei dati per la tabella `classe`
--

INSERT INTO `classe` (`id`, `nome`, `anno_scolastico`) VALUES
(9, '5AII', '2024/2025');

-- --------------------------------------------------------

--
-- Struttura della tabella `classe_studente`
--

CREATE TABLE `classe_studente` (
  `id_classe` int(11) NOT NULL,
  `cf_studente` varchar(16) NOT NULL,
  `periodo` varchar(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dump dei dati per la tabella `classe_studente`
--

INSERT INTO `classe_studente` (`id_classe`, `cf_studente`, `periodo`) VALUES
(9, 'FVRMRC06B08F443L', ''),
(9, 'RSSMCH06B15R222L', '');

-- --------------------------------------------------------

--
-- Struttura della tabella `domanda`
--

CREATE TABLE `domanda` (
  `id` int(11) NOT NULL,
  `testo` text NOT NULL,
  `tipo` enum('APERTA','MULTIPLA','','') NOT NULL,
  `test_id` int(11) NOT NULL,
  `punti` double DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dump dei dati per la tabella `domanda`
--

INSERT INTO `domanda` (`id`, `testo`, `tipo`, `test_id`, `punti`) VALUES
(84, 'Che cos\'Ã¨ l\'informatica?', 'APERTA', 8, 10),
(85, 'Quali di questi NON sono linguaggi di programmazione?', 'MULTIPLA', 8, 10),
(86, 'Come si stampa una stringa su C?', 'MULTIPLA', 8, 10),
(87, 'Nella domanda precedente, in che ordine compaiono i linguaggi?', 'MULTIPLA', 8, 10),
(88, 'Scrivi, in PHP, le righe che servono per stampare i numeri da uno a 10 in diversi <p> html', 'APERTA', 8, 10);

-- --------------------------------------------------------

--
-- Struttura della tabella `opzioni_domanda`
--

CREATE TABLE `opzioni_domanda` (
  `id` int(11) NOT NULL,
  `domanda_id` int(11) NOT NULL,
  `testo_opzione` varchar(500) NOT NULL,
  `corretta` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dump dei dati per la tabella `opzioni_domanda`
--

INSERT INTO `opzioni_domanda` (`id`, `domanda_id`, `testo_opzione`, `corretta`) VALUES
(70, 85, 'HTML', 1),
(71, 85, 'C++', 0),
(72, 85, 'CSS', 1),
(73, 85, 'Java', 0),
(74, 86, 'echo \"Hello world!\";', 0),
(75, 86, 'printf(\"Hello world!\");', 1),
(76, 86, 'print(\"Hello world!\")', 0),
(77, 86, 'System.out.print(\"Hello world!\");', 0),
(78, 87, 'Python, PHP, Java e C', 0),
(79, 87, 'PHP, C, Python e Java', 1),
(80, 87, 'Java, Python, C e PHP', 0),
(81, 87, 'Java, C, Python e PHP', 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `risposta`
--

CREATE TABLE `risposta` (
  `id` int(11) NOT NULL,
  `tentativo_id` int(11) NOT NULL,
  `domanda_id` int(11) NOT NULL,
  `risposta_aperta` text DEFAULT NULL,
  `risposta_multipla_id` int(11) DEFAULT NULL,
  `punteggio` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dump dei dati per la tabella `risposta`
--

INSERT INTO `risposta` (`id`, `tentativo_id`, `domanda_id`, `risposta_aperta`, `risposta_multipla_id`, `punteggio`) VALUES
(0, 27, 84, 'Non lo so', NULL, 0),
(0, 27, 85, NULL, 71, 0),
(0, 27, 85, NULL, 73, 0),
(0, 27, 86, NULL, 74, 0),
(0, 27, 87, NULL, 81, 0),
(0, 27, 88, 'echo \"<p>1</p><p>2</p><p>3</p><p>4</p><p>5</p><p>6</p><p>7</p><p>8</p><p>9</p><p>10</p>\";', NULL, 0),
(0, 28, 84, 'Una scienza', NULL, 10),
(0, 28, 85, NULL, 70, 5),
(0, 28, 85, NULL, 72, 5),
(0, 28, 86, NULL, 75, 10),
(0, 28, 87, NULL, 79, 10),
(0, 28, 88, '<?php \n      for($i=0; $i<10; $i++){  echo \"<p>\".$i.\"</p>\";  };\n?>', NULL, 10),
(0, 29, 84, 'Non lo so', NULL, 0),
(0, 29, 85, NULL, 71, 0),
(0, 29, 85, NULL, 72, 5),
(0, 29, 86, NULL, 74, 0),
(0, 29, 87, NULL, 79, 10),
(0, 29, 88, 'Non lo so', NULL, 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `sessione`
--

CREATE TABLE `sessione` (
  `id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `classe_id` int(11) NOT NULL,
  `cf_docente` varchar(16) NOT NULL,
  `data_inizio` datetime NOT NULL,
  `data_fine` datetime NOT NULL,
  `svolgibile` tinyint(1) NOT NULL DEFAULT 0,
  `visibilita_tentativi` tinyint(1) NOT NULL DEFAULT 1,
  `max_tentativi_ammessi` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dump dei dati per la tabella `sessione`
--

INSERT INTO `sessione` (`id`, `test_id`, `classe_id`, `cf_docente`, `data_inizio`, `data_fine`, `svolgibile`, `visibilita_tentativi`, `max_tentativi_ammessi`) VALUES
(19, 8, 9, 'FBOBSC80B10S111L', '2025-01-06 17:58:00', '2025-01-12 17:58:00', 0, 0, 3);

-- --------------------------------------------------------

--
-- Struttura della tabella `tentativo`
--

CREATE TABLE `tentativo` (
  `id` int(11) NOT NULL,
  `cf_studente` varchar(16) NOT NULL,
  `sessione_id` int(11) NOT NULL,
  `data_tentativo` timestamp NOT NULL DEFAULT current_timestamp(),
  `punteggio` double DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dump dei dati per la tabella `tentativo`
--

INSERT INTO `tentativo` (`id`, `cf_studente`, `sessione_id`, `data_tentativo`, `punteggio`) VALUES
(27, 'FVRMRC06B08F443L', 19, '2025-01-06 17:03:59', 0),
(28, 'FVRMRC06B08F443L', 19, '2025-01-06 17:06:57', 0),
(29, 'FVRMRC06B08F443L', 19, '2025-01-06 17:09:47', 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `test`
--

CREATE TABLE `test` (
  `id` int(11) NOT NULL,
  `titolo` varchar(100) NOT NULL,
  `cf_docente` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dump dei dati per la tabella `test`
--

INSERT INTO `test` (`id`, `titolo`, `cf_docente`) VALUES
(8, 'Verifica di Informatica', 'FBOBSC80B10S111L');

-- --------------------------------------------------------

--
-- Struttura della tabella `utente`
--

CREATE TABLE `utente` (
  `codice_fiscale` varchar(16) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `login` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `ruolo` enum('STUDENTE','DOCENTE','ADMIN','') NOT NULL DEFAULT 'STUDENTE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dump dei dati per la tabella `utente`
--

INSERT INTO `utente` (`codice_fiscale`, `nome`, `cognome`, `login`, `password`, `ruolo`) VALUES
('ADMADM00A00A000D', 'admin', 'admin', 'admin', 'admin', 'ADMIN'),
('FBOBSC80B10S111L', 'Fabio', 'Biscaro', 'biscaro.fabio', 'password', 'DOCENTE'),
('FVRMRC06B08F443L', 'Marco', 'Favaro', 'favaro.marco', 'password', 'STUDENTE'),
('RSSMCH06B15R222L', 'Michele', 'Russo', 'russo.michele', 'password', 'STUDENTE'),
('ZRLGSP80F12R445L', 'Giuseppe', 'Zerilli', 'zerilli.giuseppe', 'password', 'DOCENTE');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `classe`
--
ALTER TABLE `classe`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_cls_anno` (`nome`,`anno_scolastico`);

--
-- Indici per le tabelle `classe_studente`
--
ALTER TABLE `classe_studente`
  ADD PRIMARY KEY (`id_classe`,`cf_studente`),
  ADD KEY `fk_classe_studente` (`cf_studente`),
  ADD KEY `idx_cls_std` (`id_classe`,`cf_studente`) USING BTREE;

--
-- Indici per le tabelle `domanda`
--
ALTER TABLE `domanda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_domande_test` (`test_id`);

--
-- Indici per le tabelle `opzioni_domanda`
--
ALTER TABLE `opzioni_domanda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_opzioni_domanda` (`domanda_id`);

--
-- Indici per le tabelle `risposta`
--
ALTER TABLE `risposta`
  ADD KEY `fk_risposta_domanda` (`domanda_id`),
  ADD KEY `fk_risposta_multipla_opzione` (`risposta_multipla_id`),
  ADD KEY `fk_risposta_tentativo` (`tentativo_id`);

--
-- Indici per le tabelle `sessione`
--
ALTER TABLE `sessione`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sessione_test` (`test_id`),
  ADD KEY `fk_sessione_docente` (`cf_docente`),
  ADD KEY `fk_sessione_classe` (`classe_id`);

--
-- Indici per le tabelle `tentativo`
--
ALTER TABLE `tentativo`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `fk_tent_studente` (`cf_studente`),
  ADD KEY `fk_tent_sessione` (`sessione_id`);

--
-- Indici per le tabelle `test`
--
ALTER TABLE `test`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_test_docente` (`cf_docente`);

--
-- Indici per le tabelle `utente`
--
ALTER TABLE `utente`
  ADD PRIMARY KEY (`codice_fiscale`) USING BTREE;

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `classe`
--
ALTER TABLE `classe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT per la tabella `domanda`
--
ALTER TABLE `domanda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT per la tabella `opzioni_domanda`
--
ALTER TABLE `opzioni_domanda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT per la tabella `sessione`
--
ALTER TABLE `sessione`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT per la tabella `tentativo`
--
ALTER TABLE `tentativo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT per la tabella `test`
--
ALTER TABLE `test`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `classe_studente`
--
ALTER TABLE `classe_studente`
  ADD CONSTRAINT `fk_classe_classe` FOREIGN KEY (`id_classe`) REFERENCES `classe` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_classe_studente` FOREIGN KEY (`cf_studente`) REFERENCES `utente` (`codice_fiscale`) ON DELETE CASCADE;

--
-- Limiti per la tabella `domanda`
--
ALTER TABLE `domanda`
  ADD CONSTRAINT `fk_domande_test` FOREIGN KEY (`test_id`) REFERENCES `test` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `opzioni_domanda`
--
ALTER TABLE `opzioni_domanda`
  ADD CONSTRAINT `fk_opzioni_domanda` FOREIGN KEY (`domanda_id`) REFERENCES `domanda` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `risposta`
--
ALTER TABLE `risposta`
  ADD CONSTRAINT `fk_risposta_domanda` FOREIGN KEY (`domanda_id`) REFERENCES `domanda` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_risposta_multipla_opzione` FOREIGN KEY (`risposta_multipla_id`) REFERENCES `opzioni_domanda` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_risposta_tentativo` FOREIGN KEY (`tentativo_id`) REFERENCES `tentativo` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Limiti per la tabella `sessione`
--
ALTER TABLE `sessione`
  ADD CONSTRAINT `fk_sessione_classe` FOREIGN KEY (`classe_id`) REFERENCES `classe` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sessione_docente` FOREIGN KEY (`cf_docente`) REFERENCES `utente` (`codice_fiscale`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sessione_test` FOREIGN KEY (`test_id`) REFERENCES `test` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `tentativo`
--
ALTER TABLE `tentativo`
  ADD CONSTRAINT `fk_tent_sessione` FOREIGN KEY (`sessione_id`) REFERENCES `sessione` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tent_studente` FOREIGN KEY (`cf_studente`) REFERENCES `utente` (`codice_fiscale`) ON DELETE CASCADE;

--
-- Limiti per la tabella `test`
--
ALTER TABLE `test`
  ADD CONSTRAINT `fk_test_docente` FOREIGN KEY (`cf_docente`) REFERENCES `utente` (`codice_fiscale`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
