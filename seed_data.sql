-- ============================================================
--  DISANAYAKA AYURVEDA HOSPITAL — Sample Dataset
--  Database : ayurveda_inventory
--  Run this in phpMyAdmin (Import tab) or MySQL CLI
-- ============================================================

USE `ayurveda_inventory`;

-- ============================================================
-- 0. RESET  (clears data, keeps table structure)
-- ============================================================
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `stock_out`;
TRUNCATE TABLE `stock_in`;
TRUNCATE TABLE `items`;
TRUNCATE TABLE `categories`;
TRUNCATE TABLE `suppliers`;
TRUNCATE TABLE `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 1. USERS   (columns: username, password, full_name, role)
-- ============================================================
INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`) VALUES
(1, 'admin',       MD5('1234'), 'Dr. Chamodi Rathnayake',  'admin'),
(2, 'pharmacist',  MD5('1234'), 'Nimal Perera',             'staff'),
(3, 'storekeeper', MD5('1234'), 'Kumari Jayasinghe',        'staff'),
(4, 'nurse1',      MD5('1234'), 'Sanduni Wickramasinghe',   'staff');

-- ============================================================
-- 2. CATEGORIES
-- ============================================================
INSERT INTO `categories` (`id`, `name`) VALUES
(1,  'Churna (Herbal Powders)'),
(2,  'Kashaya (Decoctions)'),
(3,  'Tailam (Medicated Oils)'),
(4,  'Ghrita (Medicated Ghee)'),
(5,  'Arishta / Asava (Fermented)'),
(6,  'Lepa (External Applications)'),
(7,  'Vati / Gulika (Tablets & Pills)'),
(8,  'Bhasma & Rasa (Calcinations)'),
(9,  'Raw Herbs & Dried Ingredients'),
(10, 'Bandages & Dressings');

-- ============================================================
-- 3. SUPPLIERS  (columns: name, phone, address)
-- ============================================================
INSERT INTO `suppliers` (`id`, `name`, `phone`, `address`) VALUES
(1, 'Nalanda Ayurveda Traders',    '071-4523890', '24/B, Kandy Road, Kurunegala'),
(2, 'Lanka Herbal Pvt Ltd',         '011-2876543', 'Industrial Zone, Peliyagoda, Colombo'),
(3, 'Sampath Ayurveda Suppliers',  '081-2234567', 'Temple Road, Kandy'),
(4, 'Government Medical Stores',   '011-2696231', 'Rev. Baddegama Mw, Colombo 10'),
(5, 'Siddhalepa Herbals',          '011-2913913', 'No. 106, Dutugemunu St, Kesbewa');

-- ============================================================
-- 4. ITEMS  (columns: name, category_id, unit, min_stock, description)
-- ============================================================
INSERT INTO `items` (`id`, `name`, `category_id`, `unit`, `min_stock`, `description`) VALUES
-- Churna (Herbal Powders)
(1,  'Triphala Churna',               1, 'Kg',     10, 'Equal parts Amalaki, Bibhitaki, Haritaki. Digestive and detoxifying formula.'),
(2,  'Ashwagandha Churna',            1, 'Kg',     8,  'Withania somnifera root powder. Adaptogen and rejuvenator.'),
(3,  'Trikatu Churna',                1, 'Kg',     6,  'Ginger, Long Pepper, Black Pepper blend. Stimulates Agni (digestive fire).'),
(4,  'Shatavari Churna',              1, 'Kg',     6,  'Asparagus racemosus. Tonic for female reproductive health.'),
(5,  'Haritaki Churna',               1, 'Kg',     5,  'Terminalia chebula powder. Mild laxative and liver tonic.'),
(6,  'Amla Powder (Amalaki)',         1, 'Kg',     8,  'Phyllanthus emblica. High Vitamin C, immunity booster.'),
-- Kashaya (Decoctions)
(7,  'Dasamula Kashaya',              2, 'Litre',  15, 'Classical decoction of ten roots. Anti-inflammatory, Vata pacifying.'),
(8,  'Panchakola Kashaya',            2, 'Litre',  10, 'Five spicy herbs decoction. Improves digestion and absorption.'),
(9,  'Ginger Kashaya',                2, 'Litre',  8,  'Fresh ginger decoction. Common cold, cough and flu remedy.'),
-- Tailam (Medicated Oils)
(10, 'Dhanwantharam Tailam',          3, 'Litre',  5,  'Classical oil for Vata disorders and postpartum care.'),
(11, 'Mahanarayana Tailam',           3, 'Litre',  5,  'Joint pain and muscle relief. Arthritis management.'),
(12, 'Ksheerabala Tailam',            3, 'Litre',  4,  'Nervine tonic oil. Neurological and muscle disorders.'),
(13, 'Sesame Base Oil (Tila Tailam)', 3, 'Litre',  20, 'Pure cold-pressed sesame oil used as base for Ayurvedic oils.'),
-- Ghrita (Medicated Ghee)
(14, 'Brahmi Ghrita',                 4, 'Kg',     3,  'Clarified butter processed with Brahmi. Memory and cognition enhancer.'),
(15, 'Triphala Ghrita',               4, 'Kg',     3,  'Ghee processed with Triphala. Used for eye disorders and digestion.'),
-- Arishta / Asava
(16, 'Ashokarishta',                  5, 'Bottle', 20, '450ml bottle. Classical fermented preparation for female disorders.'),
(17, 'Dashamularishta',               5, 'Bottle', 15, '450ml bottle. Post-partum restorative tonic. General debility.'),
(18, 'Draksharishta',                 5, 'Bottle', 10, '450ml bottle. Grape-based tonic for cardiac health and anaemia.'),
(19, 'Kutajarishta',                  5, 'Bottle', 8,  '450ml bottle. Dysentery, diarrhoea and IBS management.'),
-- Lepa (External Applications)
(20, 'Jatyadi Lepa',                  6, 'Kg',     3,  'Classical wound healing external paste. Post-surgical care.'),
(21, 'Nalpamara Lepa',                6, 'Kg',     2,  'Skin disease external application. Psoriasis and dermatitis.'),
-- Vati / Gulika (Tablets)
(22, 'Triphala Guggulu',              7, 'Box',    12, '60 tablets/box. Obesity, joint disorders and detox.'),
(23, 'Chandraprabha Vati',            7, 'Box',    10, '60 tablets/box. Urinary tract and reproductive disorders.'),
(24, 'Arogyavardhini Vati',           7, 'Box',    10, '60 tablets/box. Liver, skin and fever management.'),
(25, 'Gokshuradi Guggulu',            7, 'Box',    8,  '60 tablets/box. Kidney and urinary system support.'),
(26, 'Suvarna Sutashekar Rasa',       7, 'Box',    5,  '30 tablets/box. Acid peptic disorders and gastritis.'),
-- Bhasma & Rasa
(27, 'Abhrak Bhasma',                 8, 'g',      50, 'Purified mica calcination. Respiratory and liver conditions.'),
(28, 'Shankha Bhasma',                8, 'g',      50, 'Conch shell calcination. Acidity and chronic indigestion.'),
(29, 'Yashad Bhasma',                 8, 'g',      30, 'Zinc calcination. Wound healing and eye disorders.'),
-- Raw Herbs
(30, 'Dried Neem Leaves',             9, 'Kg',     5,  'Azadirachta indica. Antibacterial, antifungal, antiparasitic.'),
(31, 'Turmeric Root (Haridra)',        9, 'Kg',     10, 'Curcuma longa rhizome. Anti-inflammatory, antiseptic, antioxidant.'),
(32, 'Dried Ginger (Shunti)',          9, 'Kg',     8,  'Zingiber officinale. Digestive stimulant and carminative.'),
(33, 'Cardamom (Ela)',                 9, 'Kg',     3,  'Elettaria cardamomum. Aromatic flavour and digestive aid.'),
(34, 'Long Pepper (Pippali)',          9, 'Kg',     4,  'Piper longum. Bioavailability enhancer (Yogavahi).'),
(35, 'Licorice Root (Yashtimadhu)',    9, 'Kg',     4,  'Glycyrrhiza glabra. Cough, gastritis and adrenal support.'),
-- Bandages & Dressings
(36, 'Sterile Gauze Rolls',          10, 'Roll',   30, '5cm x 4m. For wound dressings and post-treatment care.'),
(37, 'Crepe Bandage',                10, 'Roll',   20, '10cm x 4m. Compression and support bandaging.'),
(38, 'Adhesive Plaster Strips',      10, 'Box',    15, 'Box of 100 strips. General wound and minor cut care.');

-- ============================================================
-- 5. STOCK IN
--    columns: item_id, supplier_id, quantity, batch_no,
--             expiry_date, unit_price, notes, received_by, received_at
-- ============================================================
INSERT INTO `stock_in` (`item_id`, `supplier_id`, `quantity`, `batch_no`, `expiry_date`, `unit_price`, `notes`, `received_by`, `received_at`) VALUES
-- ── Jan 2025 — Opening stock ──────────────────────────────
(1,  1, 25.00, 'BT-2501-001', '2027-01-10',  850.00, 'Opening stock - Triphala Churna',        1, '2025-01-10 09:00:00'),
(2,  1, 15.00, 'BT-2501-002', '2027-01-10', 1200.00, 'Opening stock - Ashwagandha Churna',     1, '2025-01-10 09:15:00'),
(3,  3, 10.00, 'BT-2501-003', '2026-12-01',  700.00, 'Opening stock - Trikatu Churna',         1, '2025-01-10 09:30:00'),
(7,  2, 30.00, 'BT-2501-004', '2026-06-30',  450.00, 'Dasamula Kashaya - Batch A',             2, '2025-01-10 10:00:00'),
(10, 3, 10.00, 'BT-2501-005', '2027-06-30', 2200.00, 'Dhanwantharam Tailam - opening',         2, '2025-01-10 10:30:00'),
(16, 2, 50,    'BT-2501-006', '2027-03-15',  320.00, 'Ashokarishta initial stock',             1, '2025-01-10 11:00:00'),
(22, 4, 30,    'BT-2501-007', '2027-06-01',  185.00, 'Triphala Guggulu - Govt supply',         1, '2025-01-10 11:30:00'),
(31, 1, 20.00, 'BT-2501-008', '2028-01-01',  180.00, 'Turmeric bulk purchase',                 3, '2025-01-10 12:00:00'),
(36, 4,100,    'BT-2501-009', '2028-06-01',   45.00, 'Gauze rolls - Govt supply',              3, '2025-01-10 12:30:00'),
(37, 4, 60,    'BT-2501-010', '2028-06-01',   85.00, 'Crepe bandage - Govt supply',            3, '2025-01-10 13:00:00'),
-- ── Apr 2025 ─────────────────────────────────────────────
(4,  1, 12.00, 'BT-2504-001', '2027-04-05', 1350.00, 'Shatavari Churna restock',               2, '2025-04-05 09:00:00'),
(5,  3,  8.00, 'BT-2504-002', '2027-04-05',  620.00, 'Haritaki Churna restock',                2, '2025-04-05 09:20:00'),
(6,  1, 18.00, 'BT-2504-003', '2027-04-05',  950.00, 'Amla Powder restock',                    2, '2025-04-05 09:40:00'),
(11, 3,  8.00, 'BT-2504-004', '2027-04-05', 2800.00, 'Mahanarayana Tailam',                    1, '2025-04-05 10:00:00'),
(17, 2, 30,    'BT-2504-005', '2027-09-01',  295.00, 'Dashamularishta restock',                2, '2025-04-05 10:30:00'),
(23, 5, 24,    'BT-2504-006', '2027-06-01',  195.00, 'Chandraprabha Vati - Siddhalepa',        2, '2025-04-05 11:00:00'),
(27, 4,200.00, 'BT-2504-007', '2028-01-01',   12.50, 'Abhrak Bhasma 200g',                     1, '2025-04-05 11:30:00'),
(28, 4,150.00, 'BT-2504-008', '2028-01-01',    8.00, 'Shankha Bhasma 150g',                    1, '2025-04-05 12:00:00'),
(32, 1, 15.00, 'BT-2504-009', '2028-01-01',  320.00, 'Dried Ginger restock',                   3, '2025-04-05 12:30:00'),
(38, 4, 40,    'BT-2504-010', '2028-01-01',  550.00, 'Adhesive plaster Govt supply',           3, '2025-04-05 13:00:00'),
-- ── Jul 2025 ─────────────────────────────────────────────
(1,  1, 20.00, 'BT-2507-001', '2027-07-15',  880.00, 'Triphala Churna Q3 restock',             2, '2025-07-15 09:00:00'),
(8,  3, 20.00, 'BT-2507-002', '2026-10-15',  420.00, 'Panchakola Kashaya',                     2, '2025-07-15 09:30:00'),
(9,  3, 15.00, 'BT-2507-003', '2026-10-01',  280.00, 'Ginger Kashaya',                         2, '2025-07-15 10:00:00'),
(12, 3,  5.00, 'BT-2507-004', '2027-07-15', 3100.00, 'Ksheerabala Tailam',                     1, '2025-07-15 10:30:00'),
(13, 5, 30.00, 'BT-2507-005', '2028-07-15',  650.00, 'Sesame base oil - bulk',                 3, '2025-07-15 11:00:00'),
(14, 3,  6.00, 'BT-2507-006', '2027-07-15', 2400.00, 'Brahmi Ghrita',                          1, '2025-07-15 11:30:00'),
(15, 3,  5.00, 'BT-2507-007', '2027-07-15', 2100.00, 'Triphala Ghrita',                        1, '2025-07-15 12:00:00'),
(18, 2, 20,    'BT-2507-008', '2027-09-01',  380.00, 'Draksharishta',                          2, '2025-07-15 12:30:00'),
(19, 2, 15,    'BT-2507-009', '2027-09-01',  340.00, 'Kutajarishta',                           2, '2025-07-15 13:00:00'),
(24, 5, 20,    'BT-2507-010', '2027-06-01',  210.00, 'Arogyavardhini Vati',                    2, '2025-07-15 13:30:00'),
(30, 1,  8.00, 'BT-2507-011', '2028-07-15',  420.00, 'Dried Neem Leaves',                      3, '2025-07-15 14:00:00'),
(33, 1,  5.00, 'BT-2507-012', '2028-07-15', 2800.00, 'Cardamom bulk',                          3, '2025-07-15 14:30:00'),
-- ── Jan 2026 — Near-expiry batch (triggers expiry alerts) ─
(7,  2, 10.00, 'BT-2601-001', '2026-10-10',  460.00, 'Dasamula Kashaya small restock',         2, '2026-01-08 09:00:00'),
(20, 3,  5.00, 'BT-2601-002', '2026-10-05', 1800.00, 'Jatyadi Lepa - near expiry batch',       1, '2026-01-08 09:30:00'),
(21, 3,  4.00, 'BT-2601-003', '2026-10-12', 2100.00, 'Nalpamara Lepa - near expiry batch',    1, '2026-01-08 10:00:00'),
(26, 4, 10,    'BT-2601-004', '2026-10-20',  380.00, 'Suvarna Sutashekar Rasa near expiry',    1, '2026-01-08 10:30:00'),
(34, 1,  6.00, 'BT-2601-005', '2026-10-15', 1900.00, 'Long Pepper restock',                   3, '2026-01-08 11:00:00'),
(35, 1,  5.00, 'BT-2601-006', '2026-10-18', 2200.00, 'Licorice Root',                          3, '2026-01-08 11:30:00'),
-- ── Aug 2026 — Most recent delivery ───────────────────────
(1,  1, 30.00, 'BT-2608-001', '2028-08-20',  900.00, 'Triphala Churna Q3 bulk order',          2, '2026-08-20 09:00:00'),
(2,  1, 20.00, 'BT-2608-002', '2028-08-20', 1250.00, 'Ashwagandha Churna monthly',             2, '2026-08-20 09:30:00'),
(6,  1, 15.00, 'BT-2608-003', '2028-08-20',  970.00, 'Amla Powder monthly order',              2, '2026-08-20 10:00:00'),
(13, 5, 25.00, 'BT-2608-004', '2029-08-20',  660.00, 'Sesame base oil monthly',                3, '2026-08-20 10:30:00'),
(16, 2, 40,    'BT-2608-005', '2028-08-20',  325.00, 'Ashokarishta monthly order',             2, '2026-08-20 11:00:00'),
(22, 4, 25,    'BT-2608-006', '2028-06-01',  188.00, 'Triphala Guggulu monthly',               2, '2026-08-20 11:30:00'),
(25, 5, 15,    'BT-2608-007', '2028-06-01',  220.00, 'Gokshuradi Guggulu',                     2, '2026-08-20 12:00:00'),
(29, 4, 60.00, 'BT-2608-008', '2029-01-01',   18.00, 'Yashad Bhasma 60g',                      1, '2026-08-20 12:30:00'),
(31, 1, 12.00, 'BT-2608-009', '2029-01-01',  185.00, 'Turmeric monthly order',                 3, '2026-08-20 13:00:00'),
(36, 4, 80,    'BT-2608-010', '2029-06-01',   46.00, 'Gauze rolls monthly',                    3, '2026-08-20 13:30:00'),
(37, 4, 50,    'BT-2608-011', '2029-06-01',   87.00, 'Crepe bandage monthly',                  3, '2026-08-20 14:00:00'),
(38, 4, 30,    'BT-2608-012', '2029-06-01',  555.00, 'Adhesive plaster monthly',               3, '2026-08-20 14:30:00');

-- ============================================================
-- 6. STOCK OUT
--    columns: item_id, quantity, reason, notes, issued_by, issued_at
-- ============================================================
INSERT INTO `stock_out` (`item_id`, `quantity`, `reason`, `notes`, `issued_by`, `issued_at`) VALUES
-- Jan 2025
(1,  3.00, 'OPD',        'OPD January — digestive complaints',            2, '2025-01-15 09:00:00'),
(7,  5.00, 'Patient',    'Inpatient ward — arthritis treatment',           2, '2025-01-18 10:00:00'),
(16, 8,    'Patient',    'Female ward — menstrual disorders',              2, '2025-01-20 11:00:00'),
(36,10,    'Department', 'Dressing room monthly supply',                  3, '2025-01-22 12:00:00'),
(31, 2.00, 'Department', 'Pharmacy compounding use',                      3, '2025-01-25 09:30:00'),
-- Feb 2025
(2,  2.00, 'OPD',        'OPD Feb — general tonic prescriptions',         2, '2025-02-05 09:00:00'),
(10, 2.00, 'Patient',    'Vata disorder — inpatient physiotherapy oil',   2, '2025-02-10 10:00:00'),
(22, 6,    'OPD',        'OPD obesity management',                        4, '2025-02-14 11:00:00'),
(1,  2.00, 'Patient',    'Detox programme inpatients',                    4, '2025-02-18 09:00:00'),
(37, 8,    'Department', 'Ward 2 bandage supply',                         3, '2025-02-20 10:30:00'),
-- Mar 2025
(4,  1.50, 'Patient',    'Female tonic — postpartum care',                2, '2025-03-05 09:00:00'),
(17, 6,    'Patient',    'Postpartum ward monthly issue',                  2, '2025-03-08 10:00:00'),
(3,  1.00, 'OPD',        'OPD digestive stimulant',                       4, '2025-03-12 09:00:00'),
(11, 1.50, 'Patient',    'Arthritis physiotherapy oil',                   2, '2025-03-15 10:00:00'),
(36,15,    'Department', 'Dressing room resupply',                        3, '2025-03-20 11:00:00'),
-- Apr 2025
(1,  4.00, 'OPD',        'OPD April bulk issuance',                       2, '2025-04-08 09:00:00'),
(6,  2.00, 'Patient',    'Immunity booster packs',                        4, '2025-04-10 10:00:00'),
(23, 5,    'OPD',        'Urinary disorders OPD',                         4, '2025-04-14 11:00:00'),
(16,10,    'Patient',    'Female ward April',                             2, '2025-04-18 09:00:00'),
(32, 2.00, 'Department', 'Pharmacy Kashaya preparation',                  3, '2025-04-22 12:00:00'),
-- May 2025
(2,  3.00, 'Patient',    'Stress clinic inpatients',                      2, '2025-05-06 09:00:00'),
(14, 0.50, 'Patient',    'Cognitive therapy patients',                    1, '2025-05-09 10:00:00'),
(7,  4.00, 'OPD',        'OPD musculoskeletal',                           4, '2025-05-12 09:00:00'),
(38, 5,    'Department', 'Minor OT wound dressing',                       3, '2025-05-15 11:00:00'),
(5,  1.00, 'OPD',        'OPD liver tonic prescriptions',                 4, '2025-05-20 10:00:00'),
-- Jun 2025
(1,  3.00, 'OPD',        'OPD June routine',                              2, '2025-06-05 09:00:00'),
(11, 1.00, 'Patient',    'Rheumatoid arthritis inpatient',                2, '2025-06-10 10:00:00'),
(22, 8,    'OPD',        'Obesity OPD June',                              4, '2025-06-14 09:00:00'),
(31, 1.50, 'Department', 'Pharmacy general use',                          3, '2025-06-18 12:00:00'),
(36,12,    'Department', 'Dressing room June',                            3, '2025-06-22 11:00:00'),
-- Jul 2025
(4,  2.00, 'Patient',    'Female health camp',                            2, '2025-07-04 09:00:00'),
(17, 4,    'OPD',        'General debility OPD',                          4, '2025-07-08 09:00:00'),
(24, 3,    'OPD',        'Liver and skin OPD July',                       4, '2025-07-12 10:00:00'),
(9,  2.00, 'Patient',    'Ward — cold and flu season',                    4, '2025-07-18 11:00:00'),
(37,10,    'Department', 'Ward 3 supply July',                            3, '2025-07-24 12:00:00'),
-- Aug 2025
(2,  2.50, 'OPD',        'OPD August — stress cases',                     2, '2025-08-05 09:00:00'),
(10, 1.50, 'Patient',    'Oil massage therapy',                           2, '2025-08-08 10:00:00'),
(16,12,    'Patient',    'Female ward August',                            2, '2025-08-12 09:00:00'),
(25, 4,    'OPD',        'Kidney support OPD',                            4, '2025-08-16 10:00:00'),
(31, 1.00, 'Department', 'Kitchen pharmacy use',                          3, '2025-08-22 11:00:00'),
-- Sep 2025
(1,  5.00, 'OPD',        'OPD Sept detox programme',                      2, '2025-09-03 09:00:00'),
(3,  0.50, 'Patient',    'Digestive disorder inpatient',                  4, '2025-09-07 10:00:00'),
(22, 5,    'OPD',        'Obesity management Sept',                       4, '2025-09-11 09:00:00'),
(19, 3,    'Patient',    'IBS inpatient ward',                            2, '2025-09-15 10:00:00'),
(36,10,    'Department', 'Dressing supplies Sept',                        3, '2025-09-20 11:00:00'),
-- Oct 2025
(6,  3.00, 'OPD',        'Immunity camp October',                         4, '2025-10-10 09:00:00'),
(14, 0.80, 'Patient',    'Memory clinic Oct',                             1, '2025-10-14 10:00:00'),
(7,  3.00, 'Patient',    'Rheumatology ward',                             2, '2025-10-18 09:00:00'),
-- Nov 2025
(2,  2.00, 'OPD',        'Stress OPD November',                           4, '2025-11-05 09:00:00'),
(17, 5,    'Patient',    'Post-surgery tonic November',                   2, '2025-11-10 10:00:00'),
(23, 6,    'OPD',        'Urinary OPD November',                          4, '2025-11-15 09:00:00'),
-- Dec 2025
(1,  4.00, 'OPD',        'OPD December routine',                          2, '2025-12-05 09:00:00'),
(16, 8,    'Patient',    'Female ward December',                          2, '2025-12-10 10:00:00'),
(36,15,    'Department', 'Year end dressing restock issue',               3, '2025-12-20 11:00:00'),
-- Jan 2026
(2,  3.00, 'OPD',        'OPD January 2026',                              2, '2026-01-08 09:00:00'),
(4,  1.50, 'Patient',    'Female tonic Jan 2026',                         2, '2026-01-12 10:00:00'),
(11, 2.00, 'Patient',    'Arthritis January',                             2, '2026-01-16 09:00:00'),
(24, 4,    'OPD',        'Liver OPD January',                             4, '2026-01-20 10:00:00'),
(31, 2.00, 'Department', 'Pharmacy January use',                          3, '2026-01-24 11:00:00'),
-- Feb 2026
(1,  6.00, 'OPD',        'OPD February 2026',                             2, '2026-02-05 09:00:00'),
(22,10,    'OPD',        'Obesity OPD February',                          4, '2026-02-10 10:00:00'),
(16,15,    'Patient',    'Female ward February',                          2, '2026-02-15 09:00:00'),
-- Mar 2026
(3,  1.50, 'OPD',        'Digestive OPD March',                           4, '2026-03-05 09:00:00'),
(7,  4.00, 'Patient',    'Rheumatology March',                            2, '2026-03-10 10:00:00'),
(36,12,    'Department', 'Dressing supplies March',                       3, '2026-03-18 11:00:00'),
-- Apr 2026
(2,  3.00, 'OPD',        'Stress OPD April 2026',                         4, '2026-04-07 09:00:00'),
(17, 6,    'Patient',    'Post-op tonic April',                           2, '2026-04-12 10:00:00'),
(13, 3.00, 'Department', 'Therapy oil base April',                        3, '2026-04-20 11:00:00'),
-- May 2026
(6,  4.00, 'OPD',        'Immunity booster May',                          4, '2026-05-06 09:00:00'),
(11, 1.50, 'Patient',    'Arthritis May',                                 2, '2026-05-10 10:00:00'),
(25, 5,    'OPD',        'Kidney OPD May',                                4, '2026-05-15 09:00:00'),
-- Jun 2026
(1,  5.00, 'OPD',        'OPD June 2026',                                 2, '2026-06-04 09:00:00'),
(16,10,    'Patient',    'Female ward June',                              2, '2026-06-08 10:00:00'),
(22, 8,    'OPD',        'Obesity OPD June',                              4, '2026-06-14 09:00:00'),
-- Jul 2026
(2,  2.00, 'OPD',        'OPD July 2026',                                 4, '2026-07-03 09:00:00'),
(7,  3.00, 'Patient',    'Rheumatology July',                             2, '2026-07-08 10:00:00'),
(36,10,    'Department', 'Dressing supplies July',                        3, '2026-07-15 11:00:00'),
-- Sep 2026 — Current month
(1,  4.00, 'OPD',        'OPD September 2026 routine',                    2, '2026-09-05 09:00:00'),
(6,  2.50, 'OPD',        'Immunity OPD September',                        4, '2026-09-08 10:00:00'),
(16, 6,    'Patient',    'Female ward September',                         2, '2026-09-12 09:00:00'),
(22, 5,    'OPD',        'Obesity OPD September',                         4, '2026-09-15 10:00:00'),
(13, 2.00, 'Department', 'Oil therapy September',                         3, '2026-09-18 11:00:00'),
-- Intentional low-stock triggers (heavy usage this week)
(5,  7.50, 'OPD',        'Haritaki bulk OPD — laxative prescriptions',    4, '2026-09-20 09:00:00'),
(15, 4.50, 'Patient',    'Triphala Ghrita — eye clinic issue',            1, '2026-09-20 10:00:00'),
(20, 4.80, 'Patient',    'Wound care — surgery ward',                     2, '2026-09-21 09:00:00'),
(34, 5.50, 'Department', 'Pharmacy Pippali compounding',                  3, '2026-09-21 10:00:00'),
(3,  1.80, 'Damage',     'Batch spillage — disposed safely',              1, '2026-09-22 09:00:00');
