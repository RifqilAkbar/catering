-- =========================================================
-- Dapur Catering - Seed Data
-- Jalankan setelah schema.sql
-- =========================================================
USE dapur_catering;

-- Admin default: admin@dapurcatering.com / admin123
-- Pelanggan demo: pelanggan@dapurcatering.com / pelanggan123
INSERT INTO admins (id, name, email, password) VALUES
(1, 'Admin Dapur Catering', 'admin@dapurcatering.com', '$2y$10$CLza2Z0dj0Otv6StIDVhdeLpJLURK3auWExEQ3DNXSuA.6XKmuy5i');

INSERT INTO users (id, name, email, phone, password) VALUES
(1, 'Pelanggan Demo', 'pelanggan@dapurcatering.com', '081234567890', '$2y$10$WHwtDZskt.Al9NICcox3EeqfHffTKSHFsDnMSnsfEjylqQkwPuf7K');

INSERT INTO categories (id, name, slug, description, image) VALUES
(1, 'Nasi Kotak', 'nasi-kotak',
 'Praktis, higienis, dan mengenyangkan. Cocok untuk rapat, seminar, syukuran, dan acara harian dengan pilihan lauk lengkap.', 'images/13k.jpg'),
(2, 'Tumpeng', 'tumpeng',
 'Tumpeng syukuran dengan tampilan megah dan cita rasa istimewa. Tersedia dalam berbagai ukuran sesuai jumlah tamu Anda.', 'images/bg4.jpg'),
(3, 'Ayam Geprek', 'ayam-geprek',
 'Ayam geprek renyah dengan sambal khas yang pedasnya bisa disesuaikan. Favorit untuk makan siang dan acara santai.', 'images/geprek-10k.jpg');

INSERT INTO products (id, category_id, name, slug, description, image, base_price, stock, is_available, is_featured) VALUES
-- Nasi Kotak
(1, 1, 'Nasi Kotak Ekonomis', 'nasi-kotak-ekonomis',
 'Paket hemat dengan nasi, lauk pilihan, sayuran, sambal, dan kerupuk. Pilihan tepat untuk kebutuhan makan sehari-hari dalam jumlah banyak.',
 'images/13k.jpg', 13000, 100, 1, 1),
(2, 1, 'Nasi Kotak Standar', 'nasi-kotak-standar',
 'Nasi kotak dengan lauk ayam, mie atau urap, sambal goreng, dan kerupuk udang. Seimbang antara harga dan porsi.',
 'images/15k.jpg', 15000, 100, 1, 1),
(3, 1, 'Nasi Kotak Premium', 'nasi-kotak-premium',
 'Pilihan lauk lebih lengkap dengan porsi lebih besar, nugget, dan kerupuk udang. Cocok untuk tamu undangan.',
 'images/18k.jpg', 18000, 80, 1, 0),
(4, 1, 'Nasi Kotak Spesial', 'nasi-kotak-spesial',
 'Paket paling lengkap dengan ayam porsi besar, telur, nugget, dan kerupuk udang. Untuk acara yang ingin lebih berkesan.',
 'images/20k.jpg', 20000, 80, 1, 0),
-- Tumpeng
(5, 2, 'Tumpeng Mini', 'tumpeng-mini',
 'Tumpeng kecil untuk 4-5 orang dengan lauk pendamping lengkap. Pas untuk syukuran keluarga kecil di rumah.',
 'images/bg1.jpg', 85000, 20, 1, 1),
(6, 2, 'Tumpeng Nampan', 'tumpeng-nampan',
 'Tumpeng ukuran nampan untuk 6-8 orang, cocok untuk arisan dan syukuran kantor.',
 'images/bg2.jpg', 150000, 15, 1, 0),
(7, 2, 'Tumpeng 10 Orang', 'tumpeng-10-orang',
 'Tumpeng porsi 10 orang dengan aneka lauk, sambal goreng, dan dekorasi rapi.',
 'images/bg3.jpg', 250000, 12, 1, 1),
(8, 2, 'Tumpeng 20 Orang', 'tumpeng-20-orang',
 'Tumpeng besar untuk 20 orang, ideal untuk acara syukuran, ulang tahun, dan perayaan kantor.',
 'images/bg4.jpg', 450000, 8, 1, 0),
(9, 2, 'Tumpeng Custom', 'tumpeng-custom',
 'Tumpeng dengan ukuran, lauk, dan dekorasi yang dapat disesuaikan dengan tema acara Anda.',
 'images/bg5.jpg', 600000, 5, 1, 0),
-- Ayam Geprek
(10, 3, 'Ayam Geprek Original', 'ayam-geprek-original',
 'Ayam goreng renyah digeprek dengan sambal original khas Dapur Catering. Pedasnya bisa dipilih sesuai selera.',
 'images/geprek-10k.jpg', 10000, 150, 1, 1),
(11, 3, 'Ayam Geprek Sambal Bawang', 'ayam-geprek-sambal-bawang',
 'Ayam geprek dengan sambal bawang harum dan gurih yang bikin nambah nasi.',
 'images/geprek-10k.jpg', 12000, 150, 1, 0),
(12, 3, 'Ayam Geprek Sambal Matah', 'ayam-geprek-sambal-matah',
 'Ayam geprek dengan sambal matah segar khas Bali, perpaduan pedas dan wangi serai.',
 'images/geprek-10k.jpg', 13000, 120, 1, 0),
(13, 3, 'Ayam Geprek Keju', 'ayam-geprek-keju',
 'Ayam geprek dengan topping keju melimpah, gurih dan pedas dalam satu gigitan.',
 'images/geprek-10k.jpg', 15000, 100, 1, 0),
(14, 3, 'Paket Ayam Geprek Komplit', 'paket-ayam-geprek-komplit',
 'Ayam geprek komplit dengan nasi, telur, lalapan, dan minuman. Porsi kenyang untuk satu orang.',
 'images/geprek-10k.jpg', 20000, 100, 1, 1);

-- ---------------------------------------------------------
-- Varian / paket & ukuran
-- ---------------------------------------------------------
INSERT INTO product_variants (product_id, name, price) VALUES
-- Nasi Kotak Ekonomis
(1, 'Reguler', 13000), (1, 'Jumbo', 16000),
-- Nasi Kotak Standar
(2, 'Reguler', 15000), (2, 'Jumbo', 18000),
-- Nasi Kotak Premium
(3, 'Reguler', 18000), (3, 'Jumbo', 22000),
-- Nasi Kotak Spesial
(4, 'Reguler', 20000), (4, 'Jumbo', 25000),
-- Tumpeng Mini
(5, 'Standar (4-5 orang)', 85000), (5, 'Premium (4-5 orang)', 120000),
-- Tumpeng Nampan
(6, 'Standar (6-8 orang)', 150000), (6, 'Premium (6-8 orang)', 195000),
-- Tumpeng 10 Orang
(7, 'Standar', 250000), (7, 'Premium', 320000),
-- Tumpeng 20 Orang
(8, 'Standar', 450000), (8, 'Premium', 550000),
-- Tumpeng Custom
(9, '30 Orang', 600000), (9, '50 Orang', 950000), (9, 'Custom (konsultasi)', 1000000),
-- Ayam Geprek: level pedas
(10, 'Level 1', 10000), (10, 'Level 2', 10000), (10, 'Level 3', 10000), (10, 'Level 4', 10000), (10, 'Level 5', 10000),
(11, 'Level 1', 12000), (11, 'Level 2', 12000), (11, 'Level 3', 12000), (11, 'Level 4', 12000), (11, 'Level 5', 12000),
(12, 'Level 1', 13000), (12, 'Level 2', 13000), (12, 'Level 3', 13000), (12, 'Level 4', 13000), (12, 'Level 5', 13000),
(13, 'Level 1', 15000), (13, 'Level 2', 15000), (13, 'Level 3', 15000), (13, 'Level 4', 15000), (13, 'Level 5', 15000),
(14, 'Level 1', 20000), (14, 'Level 2', 20000), (14, 'Level 3', 20000), (14, 'Level 4', 20000), (14, 'Level 5', 20000);

-- ---------------------------------------------------------
-- Addon / tambahan
-- ---------------------------------------------------------
INSERT INTO product_addons (product_id, name, price) VALUES
-- Nasi Kotak
(1, 'Extra Ayam', 8000), (1, 'Extra Telur', 3000), (1, 'Kerupuk Udang', 2000), (1, 'Air Mineral', 3000),
(2, 'Extra Ayam', 8000), (2, 'Extra Telur', 3000), (2, 'Kerupuk Udang', 2000), (2, 'Air Mineral', 3000),
(3, 'Extra Ayam', 9000), (3, 'Extra Telur', 3000), (3, 'Nugget Tambahan', 5000), (3, 'Air Mineral', 3000),
(4, 'Extra Ayam', 10000), (4, 'Extra Telur', 3000), (4, 'Nugget Tambahan', 5000), (4, 'Air Mineral', 3000),
-- Tumpeng
(5, 'Dekorasi Tambahan', 25000), (5, 'Lauk Tambahan', 30000), (5, 'Kemasan Eksklusif', 15000),
(6, 'Dekorasi Tambahan', 30000), (6, 'Lauk Tambahan', 40000), (6, 'Kemasan Eksklusif', 15000),
(7, 'Dekorasi Tambahan', 40000), (7, 'Lauk Tambahan', 50000), (7, 'Kemasan Eksklusif', 20000),
(8, 'Dekorasi Tambahan', 50000), (8, 'Lauk Tambahan', 75000), (8, 'Kemasan Eksklusif', 25000),
(9, 'Dekorasi Tambahan', 60000), (9, 'Lauk Tambahan', 100000), (9, 'Kemasan Eksklusif', 30000),
-- Ayam Geprek
(10, 'Tambah Nasi', 4000), (10, 'Topping Keju', 5000), (10, 'Telur Ceplok', 3000), (10, 'Es Teh', 4000), (10, 'Air Mineral', 3000),
(11, 'Tambah Nasi', 4000), (11, 'Topping Keju', 5000), (11, 'Telur Ceplok', 3000), (11, 'Es Teh', 4000), (11, 'Air Mineral', 3000),
(12, 'Tambah Nasi', 4000), (12, 'Topping Keju', 5000), (12, 'Telur Ceplok', 3000), (12, 'Es Teh', 4000), (12, 'Air Mineral', 3000),
(13, 'Tambah Nasi', 4000), (13, 'Topping Keju', 5000), (13, 'Telur Ceplok', 3000), (13, 'Es Teh', 4000), (13, 'Air Mineral', 3000),
(14, 'Tambah Nasi', 4000), (14, 'Topping Keju', 5000), (14, 'Telur Ceplok', 3000), (14, 'Es Teh', 4000), (14, 'Air Mineral', 3000);
