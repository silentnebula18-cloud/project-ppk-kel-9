-- File: Proyek PPK Kel. 9 DB
-- Created at: 08 September 2026 (Updated: 22 September 2026)

CREATE DATABASE projectppk;
USE projectppk;

CREATE TABLE unverified_acc(
	unv_id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
	unv_username VARCHAR(30) NOT NULL UNIQUE,
    unv_password VARCHAR(255) NOT NULL,
    unv_email VARCHAR(255) NOT NULL UNIQUE,
    unv_registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Data contoh akun pending
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
('ryan_gosling', '$2y$10$C9vDC1YiNRo8sq7Qcr3BaehltDFzTwjkdnu8wUNBQFIhpehbm6hze', 'ryan_gosling@example.com');

CREATE TABLE users(
	user_id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    username VARCHAR(30) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    role ENUM("pengguna", "petugas", "admin") NOT NULL
);

CREATE TABLE facilities(
	fac_id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    fac_name VARCHAR(30) NOT NULL,
    type ENUM("Ruang Kelas", "Aula", "Laboratorium", "Alat", "Lapangan") NOT NULL,
    location VARCHAR(255) NOT NULL,
    capacity INT NOT NULL,
    fac_desc VARCHAR(1000) NOT NULL,
    fac_status ENUM("Aktif", "Dalam perbaikan", "Nonaktif") NOT NULL
);

-- Data contoh fasilitas
INSERT INTO facilities (fac_name, type, location, capacity, fac_desc, fac_status) VALUES
('Aula Utama', 'Aula', 'Gedung A Lantai 1', 300, 'Aula untuk seminar dan acara besar', 'Aktif'),
('Aula Mini', 'Aula', 'Gedung A Lantai 2', 80, 'Aula kecil untuk rapat dan workshop', 'Aktif'),
('Lab Komputer 1', 'Laboratorium', 'Gedung B Lantai 2', 40, 'Lab dengan 40 unit PC', 'Aktif'),
('Lab Jaringan', 'Laboratorium', 'Gedung B Lantai 3', 30, 'Lab untuk praktikum jaringan komputer', 'Dalam perbaikan'),
('Ruang Kelas 101', 'Ruang Kelas', 'Gedung C Lantai 1', 35, 'Ruang kelas reguler dengan proyektor', 'Aktif'),
('Ruang Kelas 102', 'Ruang Kelas', 'Gedung C Lantai 1', 35, 'Ruang kelas reguler dengan AC', 'Aktif'),
('Ruang Kelas 201', 'Ruang Kelas', 'Gedung C Lantai 2', 50, 'Ruang kelas besar untuk kuliah umum', 'Nonaktif'),
('Proyektor Portabel', 'Alat', 'Gudang Sarpras', 5, 'Proyektor yang bisa dipinjam per unit', 'Aktif'),
('Lapangan Basket', 'Lapangan', 'Area Olahraga Timur', 20, 'Lapangan basket outdoor', 'Aktif'),
('Lapangan Futsal', 'Lapangan', 'Area Olahraga Barat', 24, 'Lapangan futsal indoor', 'Dalam perbaikan');

CREATE TABLE reservations(
	rsv_id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id CHAR(36) NOT NULL,
    fac_id CHAR(36) NOT NULL,
    rsv_date DATE NOT NULL,
    start_time TIME NOT NULL CHECK (start_time >= '07:00:00'),
    end_time TIME NOT NULL CHECK (end_time <= '20:00:00'),
    rsv_status ENUM("proses pengajuan", "disetujui", "ditolak", "dibatalkan") NOT NULL,
    purpose VARCHAR(300) NOT NULL,
    
    -- Jika reservasi ditolak petugas atau dibatalkan pengguna
    cancelled_at TIMESTAMP CHECK(
		CASE 
			WHEN rsv_status IN ("ditolak", "dibatalkan") THEN cancelled_at IS NOT NULL
            ELSE TRUE
		END
    ),
    
    cancelled_by CHAR(36) CHECK(
		CASE 
			WHEN rsv_status IN ("ditolak", "dibatalkan") THEN cancelled_by IS NOT NULL
            ELSE TRUE
		END
    ),
    
    cancel_reason VARCHAR(300) CHECK(
		CASE 
			WHEN rsv_status = "ditolak" THEN cancel_reason IS NOT NULL
            ELSE TRUE
		END
    ),
    
    CONSTRAINT fk_reservations_users_r FOREIGN KEY (user_id) REFERENCES users(user_id),
    CONSTRAINT fk_reservations_facilites FOREIGN KEY (fac_id) REFERENCES facilities(fac_id),
    CONSTRAINT fk_reservations_users_c FOREIGN KEY (cancelled_by) REFERENCES users(user_id),
    CONSTRAINT chk_start_end_time CHECK (start_time < end_time)
);

-- Trigger pada tabel reservation untuk mengisi cancelled_at (kapan reservasi dibatalkan)
DELIMITER //
CREATE TRIGGER reservation_cancelled_at
BEFORE UPDATE ON reservations
FOR EACH ROW
BEGIN
    IF NEW.rsv_status IN ("ditolak", "dibatalkan") THEN
        SET NEW.cancelled_at = CURRENT_TIMESTAMP;
    END IF;
END //
DELIMITER ;

CREATE TABLE reports(
	rep_id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    category ENUM("kerusakan bangunan", "kerusakan peralatan", 
				   "kelistrikan", "kebersihan", "lainnya") NOT NULL,
    rep_desc VARCHAR(1000) NOT NULL,
    photo VARCHAR(300) NOT NULL,
    rep_status ENUM("baru", "diproses", "selesai", "ditolak") NOT NULL,
    user_id CHAR(36) NOT NULL,
    fac_id CHAR(36) NOT NULL,
    resolution_note VARCHAR(1000) CHECK(
		CASE 
			WHEN rep_status = "selesai" THEN resolution_note IS NOT NULL
            ELSE TRUE
		END
		),
	closed_at TIMESTAMP NULL,
    
    CONSTRAINT fk_reports_users FOREIGN KEY (user_id) REFERENCES users(user_id),
    CONSTRAINT fk_reports_facilites FOREIGN KEY (fac_id) REFERENCES facilities(fac_id)
    
);

-- Trigger pada tabel reports untuk mengisi kolom closed_at (kapan laporan ditutup)
DELIMITER //
CREATE TRIGGER reports_closed_at
BEFORE UPDATE ON reports
FOR EACH ROW
BEGIN
    IF NEW.rep_status = 'selesai' THEN
        SET NEW.closed_at = CURRENT_TIMESTAMP;
    END IF;
END //
DELIMITER ;