-- File: Proyek PPK Kel. 9 DB
-- Created at: 08 September 2026 (Updated: 24 September 2026)

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
    CONSTRAINT chk_cancelled_at CHECK (rsv_status NOT IN ('ditolak', 'dibatalkan') OR cancelled_at IS NOT NULL),
    CONSTRAINT chk_cancelled_by CHECK (rsv_status NOT IN ('ditolak', 'dibatalkan') OR cancelled_by IS NOT NULL),
    CONSTRAINT chk_cancel_reason CHECK (rsv_status != 'ditolak' OR cancel_reason IS NOT NULL)
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
    resolution_note VARCHAR(1000) NULL,
    closed_at TIMESTAMP NULL,
    
    CONSTRAINT fk_reports_users FOREIGN KEY (user_id) REFERENCES users(user_id),
    CONSTRAINT fk_reports_facilities FOREIGN KEY (fac_id) REFERENCES facilities(fac_id),
    CONSTRAINT chk_resolution_note CHECK (rep_status != 'selesai' OR resolution_note IS NOT NULL)
);

-- Trigger pada tabel reports untuk mengisi kolom closed_at
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