ALTER TABLE `tentativo` CHANGE `data_tentativo` `data_tentativo` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `sessione` CHANGE `data_inizio` `data_inizio` DATETIME NOT NULL, CHANGE `data_fine` `data_fine` DATETIME NOT NULL;
