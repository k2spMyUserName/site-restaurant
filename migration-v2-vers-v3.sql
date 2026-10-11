-- À importer UNIQUEMENT si la base v2 existe déjà (ajoute les comptes employés)
SET NAMES utf8mb4;
CREATE TABLE IF NOT EXISTS users(login VARCHAR(20) PRIMARY KEY,role VARCHAR(20) NOT NULL,pass_hash VARCHAR(255) NOT NULL DEFAULT '') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT IGNORE INTO users(login,role) VALUES('cuisinier','cuisinier'),('caissier','caissier'),('admin','admin');
