SET GLOBAL event_scheduler = ON;

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
    type ENUM('Ruang Kelas', 'Aula', 'Laboratorium', 'Alat', 'Lapangan') NOT NULL,
    location VARCHAR(255) NOT NULL,
    capacity INT NOT NULL,
    fac_desc VARCHAR(1000) NOT NULL,
    fac_photo VARCHAR(300) NULL,
    fac_status ENUM('Aktif', 'Dalam Perbaikan', 'Nonaktif') NOT NULL DEFAULT 'Aktif'
);

CREATE TABLE reservations (
    rsv_id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id CHAR(36) NOT NULL,
    fac_id CHAR(36) NOT NULL,
    rsv_date DATE NOT NULL,
    start_time TIME NOT NULL CHECK (start_time >= '07:00:00'),
    end_time TIME NOT NULL CHECK (end_time <= '20:00:00'),
    rsv_status ENUM('proses pengajuan', 'disetujui', 'ditolak', 'dibatalkan', 'kadaluarsa') NOT NULL DEFAULT 'proses pengajuan',
    purpose VARCHAR(300) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
    category ENUM('kerusakan bangunan', 'kerusakan peralatan', 'kelistrikan', 'kebersihan', 'lainnya') NOT NULL,
    rep_desc VARCHAR(1000) NOT NULL,
    rep_status ENUM('baru', 'diproses', 'selesai', 'ditolak') NOT NULL,
    user_id CHAR(36) NOT NULL,
    fac_id CHAR(36) NOT NULL,
    resolution_note VARCHAR(1000) NULL,
    closed_at TIMESTAMP NULL,
    
    CONSTRAINT fk_reports_users FOREIGN KEY (user_id) REFERENCES users(user_id),
    CONSTRAINT fk_reports_facilities FOREIGN KEY (fac_id) REFERENCES facilities(fac_id),
    CONSTRAINT chk_res_note CHECK (
        (rep_status = 'selesai' AND resolution_note IS NOT NULL)
        OR (rep_status != 'selesai')
    )
);

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

CREATE EVENT IF NOT EXISTS expire_pending_reservations
ON SCHEDULE EVERY 1 MINUTE
DO
    UPDATE reservations
    SET rsv_status = 'kadaluarsa'
    WHERE rsv_status = 'proses pengajuan'
      AND TIMESTAMP(rsv_date, start_time) <= NOW();

INSERT INTO facilities (fac_name, type, location, capacity, fac_desc, fac_status) VALUES
('Aula Utama', 'Aula', 'Gedung A Lantai 1', 300, 'Aula untuk seminar dan acara besar', 'Aktif'),
('Aula Mini', 'Aula', 'Gedung A Lantai 2', 80, 'Aula kecil untuk rapat dan workshop', 'Aktif'),
('Lab Komputer 1', 'Laboratorium', 'Gedung B Lantai 2', 40, 'Lab dengan 40 unit PC', 'Aktif'),
('Lab Jaringan', 'Laboratorium', 'Gedung B Lantai 3', 30, 'Lab untuk praktikum jaringan komputer', 'Dalam Perbaikan'),
('Ruang Kelas 101', 'Ruang Kelas', 'Gedung C Lantai 1', 35, 'Ruang kelas reguler dengan proyektor', 'Aktif'),
('Ruang Kelas 102', 'Ruang Kelas', 'Gedung C Lantai 1', 35, 'Ruang kelas reguler dengan AC', 'Aktif'),
('Ruang Kelas 201', 'Ruang Kelas', 'Gedung C Lantai 2', 50, 'Ruang kelas besar untuk kuliah umum', 'Nonaktif'),
('Proyektor Portabel', 'Alat', 'Gudang Sarpras', 5, 'Proyektor yang bisa dipinjam per unit', 'Aktif'),
('Lapangan Basket', 'Lapangan', 'Area Olahraga Timur', 20, 'Lapangan basket outdoor', 'Aktif'),
('Lapangan Futsal', 'Lapangan', 'Area Olahraga Barat', 24, 'Lapangan futsal indoor', 'Dalam Perbaikan'),
('Ruang Kuliah Gedung A', 'Ruang Kelas', 'Fakultas Hukum (FH)', 50, 'Ruang kelas dilengkapi AC, proyektor, dan papan tulis interaktif.', 'Aktif'),
('Hall Utama FEB', 'Aula', 'Fakultas Ekonomika dan Bisnis (FEB)', 300, 'Aula luas untuk seminar nasional, simposium, dan acara akademik.', 'Aktif'),
('Lab Komputer Pemrograman', 'Laboratorium', 'Fakultas Teknik (FT)', 40, 'Laboratorium komputer spesifikasi tinggi untuk praktikum koding.', 'Aktif'),
('Mikroskop Binokuler Advanced', 'Alat', 'Fakultas Kedokteran (FK)', 15, 'Perangkat mikroskop presisi tinggi untuk riset patologi dan praktikum.', 'Aktif'),
('Lapangan Agrostologi', 'Lapangan', 'Fakultas Peternakan dan Pertanian (FPP)', 100, 'Lapangan terbuka untuk penelitian vegetasi dan praktikum lapangan.', 'Aktif'),
('Ruang Teater Bahasa', 'Ruang Kelas', 'Fakultas Ilmu Budaya (FIB)', 80, 'Ruang audio visual khusus pementasan karya dan studi linguistik.', 'Aktif'),
('Studio Diskusi Publik', 'Laboratorium', 'Fakultas Ilmu Sosial dan Ilmu Politik (FISIP)', 25, 'Studio khusus simulasi debat, fokus grup, dan penyiaran.', 'Dalam Perbaikan'),
('Lab Kimia Organik', 'Laboratorium', 'Fakultas Sains dan Matematika (FSM)', 35, 'Laboratorium dengan peralatan isolasi bahan kimia dan lemari asam.', 'Aktif'),
('Lapangan Basket Vokasi', 'Lapangan', 'Sekolah Vokasi & Sekolah Pascasarjana', 150, 'Lapangan olahraga outdoor untuk mahasiswa dan kegiatan kampus.', 'Aktif'),
('Auditorium Pascasarjana', 'Aula', 'Sekolah Vokasi & Sekolah Pascasarjana', 200, 'Aula ber-AC dengan sistem suara modern untuk sidang terbuka.', 'Dalam Perbaikan');

INSERT INTO unverified_acc (unv_username, unv_password, unv_email) VALUES
('jennifer_fylia', '$2y$10$C9vDC1YiNRo8sq7Qcr3BaehltDFzTwjkdnu8wUNBQFIhpehbm6hze', 'jennifer_fylia@example.com'),
('gigi_hadid', '$2y$10$C9vDC1YiNRo8sq7Qcr3BaehltDFzTwjkdnu8wUNBQFIhpehbm6hze', 'gigi_hadid@example.com'),
('zayn_malik', '$2y$10$C9vDC1YiNRo8sq7Qcr3BaehltDFzTwjkdnu8wUNBQFIhpehbm6hze', 'zayn_malik@example.com'),
('justin_bieber', '$2y$10$C9vDC1YiNRo8sq7Qcr3BaehltDFzTwjkdnu8wUNBQFIhpehbm6hze', 'justin_bieber@example.com'),
('hailey_bieber', '$2y$10$C9vDC1YiNRo8sq7Qcr3BaehltDFzTwjkdnu8wUNBQFIhpehbm6hze', 'hailey_bieber@example.com'),
('taylor_swift', '$2y$10$C9vDC1YiNRo8sq7Qcr3BaehltDFzTwjkdnu8wUNBQFIhpehbm6hze', 'taylor_swift@example.com'),
('tom_holland', '$2y$10$C9vDC1YiNRo8sq7Qcr3BaehltDFzTwjkdnu8wUNBQFIhpehbm6hze', 'tom_holland@example.com'),
('zendaya_coleman', '$2y$10$C9vDC1YiNRo8sq7Qcr3BaehltDFzTwjkdnu8wUNBQFIhpehbm6hze', 'zendaya_coleman@example.com'),
('margot_robbie', '$2y$10$C9vDC1YiNRo8sq7Qcr3BaehltDFzTwjkdnu8wUNBQFIhpehbm6hze', 'margot_robbie@example.com'),
('ryan_gosling', '$2y$10$C9vDC1YiNRo8sq7Qcr3BaehltDFzTwjkdnu8wUNBQFIhpehbm6hze', 'ryan_gosling@example.com'),
('mhs_dimas', '$2y$12$Vk2JUb5PRwqahFZx2W/Fiu/OLmo/bcHv77V3d0RGQPVXKbP3pKLWq', 'dimas.prasetyo@students.undip.ac.id'),
('mhs_siti', '$2y$12$Vk2JUb5PRwqahFZx2W/Fiu/OLmo/bcHv77V3d0RGQPVXKbP3pKLWq', 'siti.nurhaliza@students.undip.ac.id'),
('mhs_kevin', '$2y$12$Vk2JUb5PRwqahFZx2W/Fiu/OLmo/bcHv77V3d0RGQPVXKbP3pKLWq', 'kevin.aditya@students.undip.ac.id'),
('mhs_nadia', '$2y$12$Vk2JUb5PRwqahFZx2W/Fiu/OLmo/bcHv77V3d0RGQPVXKbP3pKLWq', 'nadia.safitri@students.undip.ac.id');

INSERT INTO users (username, password, email, role) VALUES
('admin_utama', '$2y$12$Vk2JUb5PRwqahFZx2W/Fiu/OLmo/bcHv77V3d0RGQPVXKbP3pKLWq', 'admin.sarpras@undip.ac.id', 'admin'),
('admin_bapsi', '$2y$12$Vk2JUb5PRwqahFZx2W/Fiu/OLmo/bcHv77V3d0RGQPVXKbP3pKLWq', 'admin.bapsi@undip.ac.id', 'admin'),
('petugas_ft', '$2y$12$Vk2JUb5PRwqahFZx2W/Fiu/OLmo/bcHv77V3d0RGQPVXKbP3pKLWq', 'petugas.ft@undip.ac.id', 'petugas'),
('petugas_feb', '$2y$12$Vk2JUb5PRwqahFZx2W/Fiu/OLmo/bcHv77V3d0RGQPVXKbP3pKLWq', 'petugas.feb@undip.ac.id', 'petugas'),
('petugas_vokasi', '$2y$12$Vk2JUb5PRwqahFZx2W/Fiu/OLmo/bcHv77V3d0RGQPVXKbP3pKLWq', 'petugas.vokasi@undip.ac.id', 'petugas'),
('budi_santoso', '$2y$12$Vk2JUb5PRwqahFZx2W/Fiu/OLmo/bcHv77V3d0RGQPVXKbP3pKLWq', 'budi.santoso@students.undip.ac.id', 'pengguna'),
('anisa_rahma', '$2y$12$Vk2JUb5PRwqahFZx2W/Fiu/OLmo/bcHv77V3d0RGQPVXKbP3pKLWq', 'anisa.rahma@students.undip.ac.id', 'pengguna'),
('reza_firmansyah', '$2y$12$Vk2JUb5PRwqahFZx2W/Fiu/OLmo/bcHv77V3d0RGQPVXKbP3pKLWq', 'reza.firmansyah@students.undip.ac.id', 'pengguna');
