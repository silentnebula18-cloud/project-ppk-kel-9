-- File: Proyek PPK Kel. 9 DB

DROP DATABASE IF EXISTS projectppk;
CREATE DATABASE projectppk;
USE projectppk;

CREATE TABLE unverified_acc (
    unv_id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    unv_username VARCHAR(30) NOT NULL UNIQUE,
    unv_password VARCHAR(255) NOT NULL,
    unv_email VARCHAR(255) NOT NULL UNIQUE,
    unv_registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    user_id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    username VARCHAR(30) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    role ENUM('pengguna', 'petugas', 'admin') NOT NULL
);

CREATE TABLE facilities (
    fac_id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    fac_name VARCHAR(30) NOT NULL,
    fac_photo VARCHAR(300),
    type ENUM('ruang kelas', 'aula', 'laboratorium', 'alat', 'lapangan') NOT NULL,
    location VARCHAR(255) NOT NULL,
    capacity INT NOT NULL,
    fac_desc VARCHAR(1000) NOT NULL,
    fac_status ENUM('aktif', 'nonaktif', 'dalam perbaikan') NOT NULL
);

CREATE TABLE reservations (
    rsv_id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id CHAR(36) NOT NULL,
    fac_id CHAR(36) NOT NULL,
    rsv_date DATE NOT NULL,
    start_time TIME NOT NULL CHECK (start_time >= '07:00:00'),
    end_time TIME NOT NULL CHECK (end_time <= '20:00:00'),
    rsv_status ENUM('proses pengajuan', 'disetujui', 'ditolak', 'dibatalkan') NOT NULL,
    purpose VARCHAR(300) NOT NULL,
    cancelled_at TIMESTAMP NULL,
    cancelled_by CHAR(36) NULL,
    cancel_reason VARCHAR(300) NULL,
    
    CONSTRAINT fk_reservations_users_r FOREIGN KEY (user_id) REFERENCES users(user_id),
    CONSTRAINT fk_reservations_facilities FOREIGN KEY (fac_id) REFERENCES facilities(fac_id),
    CONSTRAINT fk_reservations_users_c FOREIGN KEY (cancelled_by) REFERENCES users(user_id),
    CONSTRAINT chk_start_end_time CHECK (start_time < end_time),
    CONSTRAINT chk_mod_rsv_time CHECK (TIMESTAMPDIFF(MINUTE, start_time, end_time) % 30 = 0),
    CONSTRAINT chk_cancelled_at CHECK (
        (rsv_status IN ('ditolak', 'dibatalkan') AND cancelled_at IS NOT NULL) 
        OR (rsv_status NOT IN ('ditolak', 'dibatalkan'))
    ),
    CONSTRAINT chk_cancelled_by CHECK (
        (rsv_status IN ('ditolak', 'dibatalkan') AND cancelled_by IS NOT NULL) 
        OR (rsv_status NOT IN ('ditolak', 'dibatalkan'))
    ),
    CONSTRAINT chk_cancel_reason CHECK (
        (rsv_status = 'ditolak' AND cancel_reason IS NOT NULL) 
        OR (rsv_status != 'ditolak')
    )
);

-- Trigger pada tabel reservations untuk mengisi cancelled_at
DELIMITER //
CREATE TRIGGER reservation_cancelled_at
BEFORE UPDATE ON reservations
FOR EACH ROW
BEGIN
    IF NEW.rsv_status IN ('ditolak', 'dibatalkan') AND OLD.rsv_status NOT IN ('ditolak', 'dibatalkan') THEN
        SET NEW.cancelled_at = CURRENT_TIMESTAMP;
    END IF;
END //
DELIMITER ;

CREATE TABLE reports (
    rep_id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    category ENUM('kerusakan bangunan', 'kerusakan peralatan', 
                   'kelistrikan', 'kebersihan', 'lainnya') NOT NULL,
    rep_desc VARCHAR(1000) NOT NULL,
    rep_photo VARCHAR(300) NOT NULL,
    rep_status ENUM('baru', 'diproses', 'selesai', 'ditolak') NOT NULL,
    user_id CHAR(36) NOT NULL,
    fac_id CHAR(36) NOT NULL,
    resolution_note VARCHAR(1000),
    closed_at TIMESTAMP,
    
    CONSTRAINT fk_reports_users FOREIGN KEY (user_id) REFERENCES users(user_id),
    CONSTRAINT fk_reports_facilities FOREIGN KEY (fac_id) REFERENCES facilities(fac_id),
    CONSTRAINT chk_res_note CHECK (
        (rep_status = 'selesai' AND resolution_note IS NOT NULL)
        OR (rep_status != 'selesai')
    )
);

-- Trigger pada tabel reports untuk mengisi kolom closed_at (kapan laporan ditutup)
DELIMITER //
CREATE TRIGGER reports_closed_at
BEFORE UPDATE ON reports
FOR EACH ROW
BEGIN
    IF NEW.rep_status = 'selesai' AND OLD.rep_status != 'selesai' THEN
        SET NEW.closed_at = CURRENT_TIMESTAMP;
    END IF;
END //
DELIMITER ;


-- =================================================================
-- INSERT DATA DUMMY
-- Note: Password untuk semua akun adalah "Password123!"
-- Hash PHP: $2y$10$wE9m0W0wIq4qM5/eK7/J.eFmS30eR7jYv1B3G5m5q00a4N4I4G4qO
-- =================================================================

-- 1. TABEL unverified_acc (5 Data)
INSERT INTO unverified_acc (unv_username, unv_password, unv_email) VALUES
('mhs_baru1', '$2y$10$ntt7oKn7ADdDFX8L/0nkluHwC4voLd/7FIw7EDLmZgGyS/6lZO4YK', 'mhsbaru1@mail.undip.ac.id'),
('mhs_baru2', '$2y$10$ntt7oKn7ADdDFX8L/0nkluHwC4voLd/7FIw7EDLmZgGyS/6lZO4YK', 'mhsbaru2@mail.undip.ac.id'),
('dosen_tamu', '$2y$10$ntt7oKn7ADdDFX8L/0nkluHwC4voLd/7FIw7EDLmZgGyS/6lZO4YK', 'dosentamu@mail.undip.ac.id');

-- 2. TABEL users (5 Data: 1 Admin, 1 Petugas, 3 Pengguna)
INSERT INTO users (username, password, email, role) VALUES
('admin_utama', '$2y$10$ntt7oKn7ADdDFX8L/0nkluHwC4voLd/7FIw7EDLmZgGyS/6lZO4YK', 'admin@mail.undip.ac.id', 'admin'),
('petugas_sarpras', '$2y$10$ntt7oKn7ADdDFX8L/0nkluHwC4voLd/7FIw7EDLmZgGyS/6lZO4YK', 'petugas@mail.undip.ac.id', 'petugas'),
('budi_santoso', '$2y$10$ntt7oKn7ADdDFX8L/0nkluHwC4voLd/7FIw7EDLmZgGyS/6lZO4YK', 'budi@mail.undip.ac.id', 'pengguna'),
('siti_aminah', '$2y$10$ntt7oKn7ADdDFX8L/0nkluHwC4voLd/7FIw7EDLmZgGyS/6lZO4YK', 'siti@mail.undip.ac.id', 'pengguna'),
('rizky_pratama', '$2y$10$ntt7oKn7ADdDFX8L/0nkluHwC4voLd/7FIw7EDLmZgGyS/6lZO4YK', 'rizky@mail.undip.ac.id', 'pengguna');

-- 3. TABEL facilities (10 Data - fac_photo dikosongkan/NULL)
INSERT INTO facilities (fac_name, fac_photo, type, location, capacity, fac_desc, fac_status) VALUES
('Ruang Kuliah Gedung A', NULL, 'ruang kelas', 'Fakultas Hukum (FH)', 50, 'Ruang kelas dilengkapi AC, proyektor, dan papan tulis interaktif.', 'aktif'),
('Hall Utama FEB', NULL, 'aula', 'Fakultas Ekonomika dan Bisnis (FEB)', 300, 'Aula luas untuk seminar nasional, simposium, dan acara akademik.', 'aktif'),
('Lab Komputer Pemrograman', NULL, 'laboratorium', 'Fakultas Teknik (FT)', 40, 'Laboratorium komputer spesifikasi tinggi untuk praktikum koding.', 'aktif'),
('Mikroskop Binokuler Advanced', NULL, 'alat', 'Fakultas Kedokteran (FK)', 15, 'Perangkat mikroskop presisi tinggi untuk riset patologi dan praktikum.', 'aktif'),
('Lapangan Agrostologi', NULL, 'lapangan', 'Fakultas Peternakan dan Pertanian (FPP)', 100, 'Lapangan terbuka untuk penelitian vegetasi dan praktikum lapangan.', 'aktif'),
('Ruang Teater Bahasa', NULL, 'ruang kelas', 'Fakultas Ilmu Budaya (FIB)', 80, 'Ruang audio visual khusus pementasan karya dan studi linguistik.', 'aktif'),
('Studio Diskusi Publik', NULL, 'laboratorium', 'Fakultas Ilmu Sosial dan Ilmu Politik (FISIP)', 25, 'Studio khusus simulasi debat, fokus grup, dan penyiaran.', 'dalam perbaikan'),
('Lab Kimia Organik', NULL, 'laboratorium', 'Fakultas Sains dan Matematika (FSM)', 35, 'Laboratorium dengan peralatan isolasi bahan kimia dan lemari asam.', 'aktif'),
('Lapangan Basket Vokasi', NULL, 'lapangan', 'Sekolah Vokasi & Sekolah Pascasarjana', 150, 'Lapangan olahraga outdoor untuk mahasiswa dan kegiatan kampus.', 'aktif'),
('Auditorium Pascasarjana', NULL, 'aula', 'Sekolah Vokasi & Sekolah Pascasarjana', 200, 'Aula ber-AC dengan sistem suara modern untuk sidang terbuka.', 'dalam perbaikan');

-- 4. TABEL reservations (5 Data menggunakan Subquery SELECT user_id & fac_id)
INSERT INTO reservations 
(user_id, fac_id, rsv_date, start_time, end_time, rsv_status, purpose, cancelled_at, cancelled_by, cancel_reason) 
VALUES
(
    (SELECT user_id FROM users WHERE username = 'budi_santoso'),
    (SELECT fac_id FROM facilities WHERE fac_name = 'Hall Utama FEB'),
    '2026-10-15', '08:00:00', '12:00:00', 'proses pengajuan', 
    'Seminar Nasional Kewirausahaan Mahasiswa', NULL, NULL, NULL
),
(
    (SELECT user_id FROM users WHERE username = 'siti_aminah'),
    (SELECT fac_id FROM facilities WHERE fac_name = 'Lab Komputer Pemrograman'),
    '2026-10-16', '13:00:00', '15:00:00', 'disetujui', 
    'Praktikum Tambahan Basis Data', NULL, NULL, NULL
),
(
    (SELECT user_id FROM users WHERE username = 'rizky_pratama'),
    (SELECT fac_id FROM facilities WHERE fac_name = 'Lapangan Basket Vokasi'),
    '2026-10-17', '16:00:00', '18:00:00', 'dibatalkan', 
    'Latihan Rutin UKM Basket', NOW(),
    (SELECT user_id FROM users WHERE username = 'rizky_pratama'),
    'Hujan deras dan lapangan tergenang'
),
(
    (SELECT user_id FROM users WHERE username = 'budi_santoso'),
    (SELECT fac_id FROM facilities WHERE fac_name = 'Studio Diskusi Publik'),
    '2026-10-18', '09:00:00', '11:00:00', 'ditolak', 
    'Latihan Debat Internal BEM', NOW(),
    (SELECT user_id FROM users WHERE username = 'petugas_sarpras'),
    'Fasilitas sedang dalam perbaikan berkala'
),
(
    (SELECT user_id FROM users WHERE username = 'siti_aminah'),
    (SELECT fac_id FROM facilities WHERE fac_name = 'Ruang Kuliah Gedung A'),
    '2026-10-20', '07:30:00', '09:30:00', 'proses pengajuan', 
    'Rapat Koordinasi Panitia Makrab', NULL, NULL, NULL
);

-- 5. TABEL reports (5 Data - rep_photo diisi string kosong '')
INSERT INTO reports 
(category, rep_desc, rep_photo, rep_status, user_id, fac_id, resolution_note, closed_at) 
VALUES
(
    'kelistrikan', 
    'Proyektor mati total saat digunakan kuliah dan mengeluarkan bau sangit.', '', 
    'baru', 
    (SELECT user_id FROM users WHERE username = 'budi_santoso'),
    (SELECT fac_id FROM facilities WHERE fac_name = 'Ruang Kuliah Gedung A'),
    NULL, NULL
),
(
    'kerusakan peralatan', 
    '2 unit komputer tidak bisa menyala pada row B nomor 04 dan 05.', '', 
    'diproses', 
    (SELECT user_id FROM users WHERE username = 'siti_aminah'),
    (SELECT fac_id FROM facilities WHERE fac_name = 'Lab Komputer Pemrograman'),
    NULL, NULL
),
(
    'kerusakan bangunan', 
    'AC ruangan bocor meneteskan air cukup deras ke lantai.', '', 
    'selesai', 
    (SELECT user_id FROM users WHERE username = 'rizky_pratama'),
    (SELECT fac_id FROM facilities WHERE fac_name = 'Ruang Teater Bahasa'),
    'AC sudah diperbaiki oleh teknisi dingin dan filter telah dibersihkan.', NOW()
),
(
    'kebersihan', 
    'Papan tulis sangat kotor dan banyak bekas spidol permanen.', '', 
    'ditolak', 
    (SELECT user_id FROM users WHERE username = 'budi_santoso'),
    (SELECT fac_id FROM facilities WHERE fac_name = 'Ruang Kuliah Gedung A'),
    NULL, NULL
),
(
    'kerusakan peralatan', 
    'Lensa mikroskop buram dan perlu dikalibrasi ulang.', '', 
    'baru', 
    (SELECT user_id FROM users WHERE username = 'siti_aminah'),
    (SELECT fac_id FROM facilities WHERE fac_name = 'Mikroskop Binokuler Advanced'),
    NULL, NULL
);

