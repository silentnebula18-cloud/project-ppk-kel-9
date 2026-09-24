-- File: Proyek PPK Kel. 9 DB
-- Created at: 08 September 2026 (Updated: 14 September 2026)

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
    type ENUM('ruang kelas', 'aula', 'laboratorium', 'alat', 'lapangan') NOT NULL,
    location VARCHAR(255) NOT NULL,
    capacity INT NOT NULL,
    fac_desc VARCHAR(1000) NOT NULL,
    fac_status ENUM('aktif', 'dalam perbaikan') NOT NULL
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
    photo VARCHAR(300) NOT NULL,
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

INSERT INTO facilities (fac_name, type, location, capacity, fac_desc, fac_status) VALUES
('Ruang Kuliah Gedung A', 'ruang kelas', 'Fakultas Hukum (FH)', 50, 'Ruang kelas dilengkapi AC, proyektor, dan papan tulis interaktif.', 'aktif'),
('Hall Utama FEB', 'aula', 'Fakultas Ekonomika dan Bisnis (FEB)', 300, 'Aula luas untuk seminar nasional, simposium, dan acara akademik.', 'aktif'),
('Lab Komputer Pemrograman', 'laboratorium', 'Fakultas Teknik (FT)', 40, 'Laboratorium komputer spesifikasi tinggi untuk praktikum koding.', 'aktif'),
('Mikroskop Binokuler Advanced', 'alat', 'Fakultas Kedokteran (FK)', 15, 'Perangkat mikroskop presisi tinggi untuk riset patologi dan praktikum.', 'aktif'),
('Lapangan Agrostologi', 'lapangan', 'Fakultas Peternakan dan Pertanian (FPP)', 100, 'Lapangan terbuka untuk penelitian vegetasi dan praktikum lapangan.', 'aktif'),
('Ruang Teater Bahasa', 'ruang kelas', 'Fakultas Ilmu Budaya (FIB)', 80, 'Ruang audio visual khusus pementasan karya dan studi linguistik.', 'aktif'),
('Studio Diskusi Publik', 'laboratorium', 'Fakultas Ilmu Sosial dan Ilmu Politik (FISIP)', 25, 'Studio khusus simulasi debat, fokus grup, dan penyiaran.', 'dalam perbaikan'),
('Lab Kimia Organik', 'laboratorium', 'Fakultas Sains dan Matematika (FSM)', 35, 'Laboratorium dengan peralatan isolasi bahan kimia dan lemari asam.', 'aktif'),
('Lapangan Basket Vokasi', 'lapangan', 'Sekolah Vokasi & Sekolah Pascasarjana', 150, 'Lapangan olahraga outdoor untuk mahasiswa dan kegiatan kampus.', 'aktif'),
('Auditorium Pascasarjana', 'aula', 'Sekolah Vokasi & Sekolah Pascasarjana', 200, 'Aula ber-AC dengan sistem suara modern untuk sidang terbuka.', 'dalam perbaikan');