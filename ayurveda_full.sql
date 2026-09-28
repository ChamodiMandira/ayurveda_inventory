-- ============================================================
--  AYURVEDA INVENTORY — Complete Database Setup
--  Import this into: if0_43028949_ayurveda
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','staff') NOT NULL DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `unit` varchar(30) DEFAULT NULL,
  `min_stock` decimal(10,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `stock_in` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `batch_no` varchar(50) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `received_by` int(11) DEFAULT NULL,
  `received_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `received_by` (`received_by`),
  CONSTRAINT `stock_in_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  CONSTRAINT `stock_in_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  CONSTRAINT `stock_in_ibfk_3` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `stock_out` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `reason` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `issued_by` int(11) DEFAULT NULL,
  `issued_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `issued_by` (`issued_by`),
  CONSTRAINT `stock_out_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  CONSTRAINT `stock_out_ibfk_2` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- USERS (password = MD5 of '1234')
INSERT INTO `users` (`id`,`username`,`password`,`full_name`,`role`) VALUES
(1,'admin','81dc9bdb52d04dc20036dbd8313ed055','Dr. Chamodi Rathnayake','admin'),
(2,'pharmacist','81dc9bdb52d04dc20036dbd8313ed055','Nimal Perera','staff'),
(3,'storekeeper','81dc9bdb52d04dc20036dbd8313ed055','Kumari Jayasinghe','staff'),
(4,'nurse1','81dc9bdb52d04dc20036dbd8313ed055','Sanduni Wickramasinghe','staff');

-- CATEGORIES
INSERT INTO `categories` (`id`,`name`) VALUES
(1,'Churna (Herbal Powders)'),(2,'Kashaya (Decoctions)'),(3,'Tailam (Medicated Oils)'),
(4,'Ghrita (Medicated Ghee)'),(5,'Arishta / Asava (Fermented)'),(6,'Lepa (External Applications)'),
(7,'Vati / Gulika (Tablets & Pills)'),(8,'Bhasma & Rasa (Calcinations)'),
(9,'Raw Herbs & Dried Ingredients'),(10,'Bandages & Dressings');

-- SUPPLIERS
INSERT INTO `suppliers` (`id`,`name`,`phone`,`address`) VALUES
(1,'Nalanda Ayurveda Traders','071-4523890','24/B, Kandy Road, Kurunegala'),
(2,'Lanka Herbal Pvt Ltd','011-2876543','Industrial Zone, Peliyagoda, Colombo'),
(3,'Sampath Ayurveda Suppliers','081-2234567','Temple Road, Kandy'),
(4,'Government Medical Stores','011-2696231','Rev. Baddegama Mw, Colombo 10'),
(5,'Siddhalepa Herbals','011-2913913','No. 106, Dutugemunu St, Kesbewa');

-- ITEMS
INSERT INTO `items` (`id`,`name`,`category_id`,`unit`,`min_stock`,`description`) VALUES
(1,'Triphala Churna',1,'Kg',10,'Equal parts Amalaki, Bibhitaki, Haritaki.'),
(2,'Ashwagandha Churna',1,'Kg',8,'Withania somnifera root powder.'),
(3,'Trikatu Churna',1,'Kg',6,'Ginger, Long Pepper, Black Pepper blend.'),
(4,'Shatavari Churna',1,'Kg',6,'Asparagus racemosus.'),
(5,'Haritaki Churna',1,'Kg',5,'Terminalia chebula powder.'),
(6,'Amla Powder (Amalaki)',1,'Kg',8,'Phyllanthus emblica. High Vitamin C.'),
(7,'Dasamula Kashaya',2,'Litre',15,'Classical decoction of ten roots.'),
(8,'Panchakola Kashaya',2,'Litre',10,'Five spicy herbs decoction.'),
(9,'Ginger Kashaya',2,'Litre',8,'Fresh ginger decoction.'),
(10,'Dhanwantharam Tailam',3,'Litre',5,'Classical oil for Vata disorders.'),
(11,'Mahanarayana Tailam',3,'Litre',5,'Joint pain and muscle relief.'),
(12,'Ksheerabala Tailam',3,'Litre',4,'Nervine tonic oil.'),
(13,'Sesame Base Oil (Tila Tailam)',3,'Litre',20,'Pure cold-pressed sesame oil.'),
(14,'Brahmi Ghrita',4,'Kg',3,'Clarified butter processed with Brahmi.'),
(15,'Triphala Ghrita',4,'Kg',3,'Ghee processed with Triphala.'),
(16,'Ashokarishta',5,'Bottle',20,'450ml bottle. Female disorders.'),
(17,'Dashamularishta',5,'Bottle',15,'450ml bottle. Post-partum tonic.'),
(18,'Draksharishta',5,'Bottle',10,'450ml bottle. Cardiac health.'),
(19,'Kutajarishta',5,'Bottle',8,'450ml bottle. Dysentery management.'),
(20,'Jatyadi Lepa',6,'Kg',3,'Classical wound healing paste.'),
(21,'Nalpamara Lepa',6,'Kg',2,'Skin disease external application.'),
(22,'Triphala Guggulu',7,'Box',12,'60 tablets/box. Obesity, joint disorders.'),
(23,'Chandraprabha Vati',7,'Box',10,'60 tablets/box. Urinary disorders.'),
(24,'Arogyavardhini Vati',7,'Box',10,'60 tablets/box. Liver, skin and fever.'),
(25,'Gokshuradi Guggulu',7,'Box',8,'60 tablets/box. Kidney support.'),
(26,'Suvarna Sutashekar Rasa',7,'Box',5,'30 tablets/box. Acid peptic disorders.'),
(27,'Abhrak Bhasma',8,'g',50,'Purified mica calcination.'),
(28,'Shankha Bhasma',8,'g',50,'Conch shell calcination.'),
(29,'Yashad Bhasma',8,'g',30,'Zinc calcination.'),
(30,'Dried Neem Leaves',9,'Kg',5,'Azadirachta indica.'),
(31,'Turmeric Root (Haridra)',9,'Kg',10,'Curcuma longa rhizome.'),
(32,'Dried Ginger (Shunti)',9,'Kg',8,'Zingiber officinale.'),
(33,'Cardamom (Ela)',9,'Kg',3,'Elettaria cardamomum.'),
(34,'Long Pepper (Pippali)',9,'Kg',4,'Piper longum.'),
(35,'Licorice Root (Yashtimadhu)',9,'Kg',4,'Glycyrrhiza glabra.'),
(36,'Sterile Gauze Rolls',10,'Roll',30,'5cm x 4m. Wound dressings.'),
(37,'Crepe Bandage',10,'Roll',20,'10cm x 4m. Compression bandaging.'),
(38,'Adhesive Plaster Strips',10,'Box',15,'Box of 100 strips.');

-- STOCK IN
INSERT INTO `stock_in` (`item_id`,`supplier_id`,`quantity`,`batch_no`,`expiry_date`,`unit_price`,`notes`,`received_by`,`received_at`) VALUES
(1,1,25.00,'BT-2501-001','2027-01-10',850.00,'Opening stock - Triphala Churna',1,'2025-01-10 09:00:00'),
(2,1,15.00,'BT-2501-002','2027-01-10',1200.00,'Opening stock - Ashwagandha Churna',1,'2025-01-10 09:15:00'),
(3,3,10.00,'BT-2501-003','2026-12-01',700.00,'Opening stock - Trikatu Churna',1,'2025-01-10 09:30:00'),
(7,2,30.00,'BT-2501-004','2026-06-30',450.00,'Dasamula Kashaya - Batch A',2,'2025-01-10 10:00:00'),
(10,3,10.00,'BT-2501-005','2027-06-30',2200.00,'Dhanwantharam Tailam opening',2,'2025-01-10 10:30:00'),
(16,2,50.00,'BT-2501-006','2027-03-15',320.00,'Ashokarishta initial stock',1,'2025-01-10 11:00:00'),
(22,4,30.00,'BT-2501-007','2027-06-01',185.00,'Triphala Guggulu - Govt supply',1,'2025-01-10 11:30:00'),
(31,1,20.00,'BT-2501-008','2028-01-01',180.00,'Turmeric bulk purchase',3,'2025-01-10 12:00:00'),
(36,4,100.00,'BT-2501-009','2028-06-01',45.00,'Gauze rolls - Govt supply',3,'2025-01-10 12:30:00'),
(37,4,60.00,'BT-2501-010','2028-06-01',85.00,'Crepe bandage - Govt supply',3,'2025-01-10 13:00:00'),
(4,1,12.00,'BT-2504-001','2027-04-05',1350.00,'Shatavari Churna restock',2,'2025-04-05 09:00:00'),
(6,1,18.00,'BT-2504-003','2027-04-05',950.00,'Amla Powder restock',2,'2025-04-05 09:40:00'),
(11,3,8.00,'BT-2504-004','2027-04-05',2800.00,'Mahanarayana Tailam',1,'2025-04-05 10:00:00'),
(17,2,30.00,'BT-2504-005','2027-09-01',295.00,'Dashamularishta restock',2,'2025-04-05 10:30:00'),
(23,5,24.00,'BT-2504-006','2027-06-01',195.00,'Chandraprabha Vati',2,'2025-04-05 11:00:00'),
(27,4,200.00,'BT-2504-007','2028-01-01',12.50,'Abhrak Bhasma 200g',1,'2025-04-05 11:30:00'),
(32,1,15.00,'BT-2504-009','2028-01-01',320.00,'Dried Ginger restock',3,'2025-04-05 12:30:00'),
(38,4,40.00,'BT-2504-010','2028-01-01',550.00,'Adhesive plaster Govt supply',3,'2025-04-05 13:00:00'),
(1,1,20.00,'BT-2507-001','2027-07-15',880.00,'Triphala Churna Q3 restock',2,'2025-07-15 09:00:00'),
(8,3,20.00,'BT-2507-002','2026-10-15',420.00,'Panchakola Kashaya',2,'2025-07-15 09:30:00'),
(9,3,15.00,'BT-2507-003','2026-10-01',280.00,'Ginger Kashaya',2,'2025-07-15 10:00:00'),
(12,3,5.00,'BT-2507-004','2027-07-15',3100.00,'Ksheerabala Tailam',1,'2025-07-15 10:30:00'),
(13,5,30.00,'BT-2507-005','2028-07-15',650.00,'Sesame base oil - bulk',3,'2025-07-15 11:00:00'),
(14,3,6.00,'BT-2507-006','2027-07-15',2400.00,'Brahmi Ghrita',1,'2025-07-15 11:30:00'),
(15,3,5.00,'BT-2507-007','2027-07-15',2100.00,'Triphala Ghrita',1,'2025-07-15 12:00:00'),
(18,2,20.00,'BT-2507-008','2027-09-01',380.00,'Draksharishta',2,'2025-07-15 12:30:00'),
(19,2,15.00,'BT-2507-009','2027-09-01',340.00,'Kutajarishta',2,'2025-07-15 13:00:00'),
(24,5,20.00,'BT-2507-010','2027-06-01',210.00,'Arogyavardhini Vati',2,'2025-07-15 13:30:00'),
(30,1,8.00,'BT-2507-011','2028-07-15',420.00,'Dried Neem Leaves',3,'2025-07-15 14:00:00'),
(1,1,30.00,'BT-2608-001','2028-08-20',900.00,'Triphala Churna Q3 bulk order',2,'2026-08-20 09:00:00'),
(2,1,20.00,'BT-2608-002','2028-08-20',1250.00,'Ashwagandha Churna monthly',2,'2026-08-20 09:30:00'),
(6,1,15.00,'BT-2608-003','2028-08-20',970.00,'Amla Powder monthly order',2,'2026-08-20 10:00:00'),
(13,5,25.00,'BT-2608-004','2029-08-20',660.00,'Sesame base oil monthly',3,'2026-08-20 10:30:00'),
(16,2,40.00,'BT-2608-005','2028-08-20',325.00,'Ashokarishta monthly order',2,'2026-08-20 11:00:00'),
(22,4,25.00,'BT-2608-006','2028-06-01',188.00,'Triphala Guggulu monthly',2,'2026-08-20 11:30:00'),
(25,5,15.00,'BT-2608-007','2028-06-01',220.00,'Gokshuradi Guggulu',2,'2026-08-20 12:00:00'),
(36,4,80.00,'BT-2608-010','2029-06-01',46.00,'Gauze rolls monthly',3,'2026-08-20 13:30:00'),
(37,4,50.00,'BT-2608-011','2029-06-01',87.00,'Crepe bandage monthly',3,'2026-08-20 14:00:00');

-- STOCK OUT
INSERT INTO `stock_out` (`item_id`,`quantity`,`reason`,`notes`,`issued_by`,`issued_at`) VALUES
(1,3.00,'OPD','OPD January - digestive complaints',2,'2025-01-15 09:00:00'),
(7,5.00,'Patient','Inpatient ward - arthritis treatment',2,'2025-01-18 10:00:00'),
(16,8.00,'Patient','Female ward - menstrual disorders',2,'2025-01-20 11:00:00'),
(36,10.00,'Department','Dressing room monthly supply',3,'2025-01-22 12:00:00'),
(2,2.00,'OPD','OPD Feb - general tonic prescriptions',2,'2025-02-05 09:00:00'),
(10,2.00,'Patient','Vata disorder - inpatient physiotherapy oil',2,'2025-02-10 10:00:00'),
(22,6.00,'OPD','OPD obesity management',4,'2025-02-14 11:00:00'),
(4,1.50,'Patient','Female tonic - postpartum care',2,'2025-03-05 09:00:00'),
(17,6.00,'Patient','Postpartum ward monthly issue',2,'2025-03-08 10:00:00'),
(11,1.50,'Patient','Arthritis physiotherapy oil',2,'2025-03-15 10:00:00'),
(1,4.00,'OPD','OPD April bulk issuance',2,'2025-04-08 09:00:00'),
(6,2.00,'Patient','Immunity booster packs',4,'2025-04-10 10:00:00'),
(23,5.00,'OPD','Urinary disorders OPD',4,'2025-04-14 11:00:00'),
(16,10.00,'Patient','Female ward April',2,'2025-04-18 09:00:00'),
(2,2.50,'OPD','OPD August - stress cases',2,'2025-08-05 09:00:00'),
(16,12.00,'Patient','Female ward August',2,'2025-08-12 09:00:00'),
(1,5.00,'OPD','OPD Sept detox programme',2,'2025-09-03 09:00:00'),
(22,5.00,'OPD','Obesity management Sept',4,'2025-09-11 09:00:00'),
(1,3.00,'OPD','OPD December routine',2,'2025-12-05 09:00:00'),
(16,8.00,'Patient','Female ward December',2,'2025-12-10 10:00:00'),
(2,3.00,'OPD','OPD January 2026',2,'2026-01-08 09:00:00'),
(4,1.50,'Patient','Female tonic Jan 2026',2,'2026-01-12 10:00:00'),
(11,2.00,'Patient','Arthritis January',2,'2026-01-16 09:00:00'),
(1,6.00,'OPD','OPD February 2026',2,'2026-02-05 09:00:00'),
(22,10.00,'OPD','Obesity OPD February',4,'2026-02-10 10:00:00'),
(16,15.00,'Patient','Female ward February',2,'2026-02-15 09:00:00'),
(7,4.00,'Patient','Rheumatology March',2,'2026-03-10 10:00:00'),
(2,3.00,'OPD','Stress OPD April 2026',4,'2026-04-07 09:00:00'),
(6,4.00,'OPD','Immunity booster May',4,'2026-05-06 09:00:00'),
(11,1.50,'Patient','Arthritis May',2,'2026-05-10 10:00:00'),
(1,5.00,'OPD','OPD June 2026',2,'2026-06-04 09:00:00'),
(16,10.00,'Patient','Female ward June',2,'2026-06-08 10:00:00'),
(22,8.00,'OPD','Obesity OPD June',4,'2026-06-14 09:00:00'),
(1,4.00,'OPD','OPD September 2026 routine',2,'2026-09-05 09:00:00'),
(6,2.50,'OPD','Immunity OPD September',4,'2026-09-08 10:00:00'),
(16,6.00,'Patient','Female ward September',2,'2026-09-12 09:00:00'),
(22,5.00,'OPD','Obesity OPD September',4,'2026-09-15 10:00:00');

SET FOREIGN_KEY_CHECKS = 1;
