-- Dakéré Plus v2 : à importer dans phpMyAdmin (base vide ou existante)
SET NAMES utf8mb4;
CREATE TABLE IF NOT EXISTS settings(k VARCHAR(40) PRIMARY KEY,v TEXT) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS items(id INT AUTO_INCREMENT PRIMARY KEY,cat VARCHAR(60),name VARCHAR(120),descr VARCHAR(255),price INT,photo VARCHAR(80) NULL,best TINYINT DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS gallery(id INT AUTO_INCREMENT PRIMARY KEY,photo VARCHAR(80),caption VARCHAR(160)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Commandes : le numéro (id) est auto-incrémenté et les commandes ne sont jamais supprimées => numéro unique, jamais réutilisé
CREATE TABLE IF NOT EXISTS orders(id INT AUTO_INCREMENT PRIMARY KEY,tbl VARCHAR(10) NOT NULL,phone VARCHAR(20) NOT NULL DEFAULT '',total INT NOT NULL,note VARCHAR(255) NULL,status ENUM('en_cours','servie','payee') DEFAULT 'en_cours',created_at DATETIME DEFAULT CURRENT_TIMESTAMP,paid_at DATETIME NULL,INDEX(created_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1001;
CREATE TABLE IF NOT EXISTS order_items(id INT AUTO_INCREMENT PRIMARY KEY,order_id INT NOT NULL,name VARCHAR(120),price INT,qty INT,INDEX(order_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS print_jobs(id INT AUTO_INCREMENT PRIMARY KEY,order_id INT NOT NULL,station VARCHAR(10) NOT NULL,printed TINYINT DEFAULT 0,created_at DATETIME DEFAULT CURRENT_TIMESTAMP,printed_at DATETIME NULL,INDEX(station,printed)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS users(login VARCHAR(20) PRIMARY KEY,role VARCHAR(20) NOT NULL,pass_hash VARCHAR(255) NOT NULL DEFAULT '') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT IGNORE INTO users(login,role) VALUES('cuisinier','cuisinier'),('caissier','caissier'),('admin','admin');
CREATE TABLE IF NOT EXISTS reviews(id INT AUTO_INCREMENT PRIMARY KEY,order_id INT NULL UNIQUE,name VARCHAR(60),rating TINYINT NOT NULL,comment VARCHAR(500),created_at DATETIME DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Stockage
CREATE TABLE IF NOT EXISTS stock_items(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120),type VARCHAR(60),unit VARCHAR(20),qty DECIMAL(10,2) DEFAULT 0,created_at DATETIME DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS stock_moves(id INT AUTO_INCREMENT PRIMARY KEY,item_id INT NULL,item_name VARCHAR(120),type VARCHAR(60),unit VARCHAR(20),kind ENUM('entree','sortie','ajust') NOT NULL,qty DECIMAL(10,2) NOT NULL,note VARCHAR(160) NULL,moved_at DATE NOT NULL,created_at DATETIME DEFAULT CURRENT_TIMESTAMP,INDEX(moved_at),INDEX(item_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

REPLACE INTO settings(k,v) VALUES('phone_show','680 465 140'),('hours','Lun – Sam : 10h – 22h\nDimanche : 12h – 21h'),('addr','Douala, Cameroun'),
('printer_cuisine','IMPRIMANTE_CUISINE_01'),('printer_caisse','IMPRIMANTE_CAISSE_01');
INSERT INTO items(cat,name,descr,price,best) VALUES
('Plats','Thieboudienne','Riz au poisson, légumes mijotés, sauce tomate épicée',3500,1),
('Plats','Poulet Yassa','Poulet mariné citron-oignons caramélisés, riz blanc',3000,1),
('Plats','Mafé','Ragoût de bœuf à la pâte d''arachide, riz',3000,0),
('Plats','Poisson braisé','Poisson grillé, sauce oignon, attiéké ou riz',4000,0),
('Entrées','Pastels','Beignets farcis au poisson (6 pcs)',1500,0),
('Entrées','Accras','Beignets croustillants de poisson (6 pcs)',1500,0),
('Boissons','Jus de bissap','Hibiscus frais, menthe',800,1),
('Boissons','Jus de gingembre','Piquant et rafraîchissant',800,0),
('Boissons','Jus de bouye','Fruit du baobab onctueux',1000,0),
('Desserts','Thiakry','Dessert de mil, lait caillé et vanille',1200,0),
('Desserts','Beignets sucrés','Chauds et dorés (5 pcs)',1000,0);
INSERT INTO stock_items(name,type,unit,qty) VALUES('Poulet','Viande','pièce',40),('Laitue','Légume','tête',25),('Lait caillé','Produit laitier','bouteille',30),('Jus de bissap','Boisson','bouteille',60),('Spaghetti','Féculent','paquet',20);
INSERT INTO stock_moves(item_id,item_name,type,unit,kind,qty,note,moved_at) SELECT id,name,type,unit,'entree',qty,'Stock initial',CURDATE() FROM stock_items;
