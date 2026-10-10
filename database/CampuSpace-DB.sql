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
    fac_status ENUM('Aktif', 'Dalam perbaikan', 'Nonaktif') NOT NULL DEFAULT 'Aktif'
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

INSERT INTO unverified_acc (unv_id, unv_username, unv_password, unv_email, unv_registered_at) VALUES
('e0000001-0000-4000-8000-000000000001', 'dian_lestari', '$2y$12$ixYtKR4rQ17AbgleigAX3OVpxlB2o4F0wIU9Q3OI1djoaLgh6KuJa', 'dian.lestari@students.undip.ac.id', '2026-10-01 08:30:00'),
('e0000001-0000-4000-8000-000000000002', 'fajar_nugroho', '$2y$12$ixYtKR4rQ17AbgleigAX3OVpxlB2o4F0wIU9Q3OI1djoaLgh6KuJa', 'fajar.nugroho@students.undip.ac.id', '2026-10-02 09:15:00'),
('e0000001-0000-4000-8000-000000000003', 'gita_permata', '$2y$12$ixYtKR4rQ17AbgleigAX3OVpxlB2o4F0wIU9Q3OI1djoaLgh6KuJa', 'gita.permata@students.undip.ac.id', '2026-10-03 10:45:00'),
('e0000001-0000-4000-8000-000000000004', 'hendra_wijaya', '$2y$12$ixYtKR4rQ17AbgleigAX3OVpxlB2o4F0wIU9Q3OI1djoaLgh6KuJa', 'hendra.wijaya@students.undip.ac.id', '2026-10-04 11:20:00'),
('e0000001-0000-4000-8000-000000000005', 'indah_safitri', '$2y$12$ixYtKR4rQ17AbgleigAX3OVpxlB2o4F0wIU9Q3OI1djoaLgh6KuJa', 'indah.safitri@students.undip.ac.id', '2026-10-05 13:00:00'),
('e0000001-0000-4000-8000-000000000006', 'joko_susilo', '$2y$12$ixYtKR4rQ17AbgleigAX3OVpxlB2o4F0wIU9Q3OI1djoaLgh6KuJa', 'joko.susilo@students.undip.ac.id', '2026-10-06 14:10:00'),
('e0000001-0000-4000-8000-000000000007', 'kartika_dewi', '$2y$12$ixYtKR4rQ17AbgleigAX3OVpxlB2o4F0wIU9Q3OI1djoaLgh6KuJa', 'kartika.dewi@students.undip.ac.id', '2026-10-07 15:30:00'),
('e0000001-0000-4000-8000-000000000008', 'lukman_hakim', '$2y$12$ixYtKR4rQ17AbgleigAX3OVpxlB2o4F0wIU9Q3OI1djoaLgh6KuJa', 'lukman.hakim@students.undip.ac.id', '2026-10-08 08:50:00'),
('e0000001-0000-4000-8000-000000000009', 'maya_anggraini', '$2y$12$ixYtKR4rQ17AbgleigAX3OVpxlB2o4F0wIU9Q3OI1djoaLgh6KuJa', 'maya.anggraini@students.undip.ac.id', '2026-10-09 09:40:00'),
('e0000001-0000-4000-8000-000000000010', 'naufal_rizqi', '$2y$12$ixYtKR4rQ17AbgleigAX3OVpxlB2o4F0wIU9Q3OI1djoaLgh6KuJa', 'naufal.rizqi@students.undip.ac.id', '2026-10-10 10:15:00');

INSERT INTO users (user_id, username, password, email, role) VALUES
('a0000001-0000-4000-8000-000000000001', 'andi_pratama', '$2y$12$ixYtKR4rQ17AbgleigAX3OVpxlB2o4F0wIU9Q3OI1djoaLgh6KuJa', 'andi.pratama@students.undip.ac.id', 'pengguna'),
('a0000001-0000-4000-8000-000000000002', 'budi_santoso', '$2y$12$ixYtKR4rQ17AbgleigAX3OVpxlB2o4F0wIU9Q3OI1djoaLgh6KuJa', 'budi.santoso@students.undip.ac.id', 'pengguna'),
('a0000001-0000-4000-8000-000000000003', 'citra_lestari', '$2y$12$ixYtKR4rQ17AbgleigAX3OVpxlB2o4F0wIU9Q3OI1djoaLgh6KuJa', 'citra.lestari@students.undip.ac.id', 'pengguna'),
('b0000001-0000-4000-8000-000000000001', 'petugas_sarpras', '$2y$12$ixYtKR4rQ17AbgleigAX3OVpxlB2o4F0wIU9Q3OI1djoaLgh6KuJa', 'petugas.sarpras@undip.ac.id', 'petugas'),
('b0000001-0000-4000-8000-000000000002', 'petugas_teknik', '$2y$12$ixYtKR4rQ17AbgleigAX3OVpxlB2o4F0wIU9Q3OI1djoaLgh6KuJa', 'petugas.teknik@undip.ac.id', 'petugas'),
('c0000001-0000-4000-8000-000000000001', 'admin_utama', '$2y$12$ixYtKR4rQ17AbgleigAX3OVpxlB2o4F0wIU9Q3OI1djoaLgh6KuJa', 'admin.sarpras@undip.ac.id', 'admin');

INSERT INTO facilities (fac_id, fac_name, type, location, capacity, fac_desc, fac_photo, fac_status) VALUES
('f0000001-0000-4000-8000-000000000001', 'Aula Utama', 'Aula', 'Fakultas Sains dan Matematika (FSM)', 300, 'Aula utama serbaguna untuk kegiatan seminar nasional, simposium, dan acara universitas.', 'storage/fac_img/apa.webp', 'aktif'),
('f0000001-0000-4000-8000-000000000002', 'Aula Mini', 'Aula', 'Fakultas Sains dan Matematika (FSM)', 80, 'Aula kapasitas sedang dilengkapi proyektor dan sound system untuk rapat koordinasi.', NULL, 'aktif'),
('f0000001-0000-4000-8000-000000000003', 'Ruang Kelas 101', 'Ruang Kelas', 'Fakultas Hukum (FH)', 40, 'Ruang kuliah reguler dengan pendingin ruangan, proyektor LCD, dan papan tulis.', NULL, 'aktif'),
('f0000001-0000-4000-8000-000000000004', 'Ruang Kelas 102', 'Ruang Kelas', 'Fakultas Ekonomika dan Bisnis (FEB)', 40, 'Ruang kuliah reguler berkapasitas 40 mahasiswa dengan pencahayaan dan ventilasi memadai.', NULL, 'aktif'),
('f0000001-0000-4000-8000-000000000005', 'Lab Komputer 1', 'Laboratorium', 'Fakultas Teknik (FT)', 35, 'Laboratorium komputer spesifikasi tinggi untuk kegiatan praktikum komputasi dan pemrograman.', NULL, 'aktif'),
('f0000001-0000-4000-8000-000000000006', 'Lab Jaringan', 'Laboratorium', 'Fakultas Ilmu Sosial dan Ilmu Politik (FISIP)', 30, 'Laboratorium khusus praktikum jaringan komputer dan arsitektur perangkat keras.', NULL, 'Dalam perbaikan'),
('f0000001-0000-4000-8000-000000000007', 'Lapangan Basket', 'Lapangan', 'Fakultas Peternakan dan Pertanian (FPP)', 20, 'Lapangan basket luar ruangan standar pertandingan dengan pencahayaan malam hari.', NULL, 'aktif'),
('f0000001-0000-4000-8000-000000000008', 'Lapangan Futsal', 'Lapangan', 'Fakultas Kedokteran (FK)', 24, 'Lapangan futsal tertutup dengan lantai interlock dan jaring pengaman keliling.', NULL, 'Dalam perbaikan'),
('f0000001-0000-4000-8000-000000000009', 'Proyektor Portabel A', 'Alat', 'Sekolah Vokasi & Sekolah Pascasarjana', 5, 'Unit proyektor portabel resolusi tinggi yang dapat dipinjam untuk presentasi luar ruangan.', NULL, 'aktif'),
('f0000001-0000-4000-8000-000000000010', 'Ruang Teater Bahasa', 'Ruang Kelas', 'Fakultas Ilmu Budaya (FIB)', 60, 'Ruang audio visual khusus pementasan, pemutaran film edukasi, dan seminar bahasa.', NULL, 'Nonaktif');

INSERT INTO reservations (rsv_id, user_id, fac_id, rsv_date, start_time, end_time, rsv_status, purpose, created_at, cancelled_at, cancelled_by, cancel_reason) VALUES
('r0000001-0000-4000-8000-000000000001', 'a0000001-0000-4000-8000-000000000001', 'f0000001-0000-4000-8000-000000000003', '2026-10-15', '09:00:00', '11:00:00', 'disetujui', 'Diskusi kelompok tugas rekayasa perangkat lunak', '2026-10-09 10:00:00', NULL, NULL, NULL),
('r0000001-0000-4000-8000-000000000002', 'a0000001-0000-4000-8000-000000000001', 'f0000001-0000-4000-8000-000000000005', '2026-10-18', '13:00:00', '15:00:00', 'disetujui', 'Pengerjaan modul praktikum basis data lanjut', '2026-10-09 11:30:00', NULL, NULL, NULL),
('r0000001-0000-4000-8000-000000000003', 'a0000001-0000-4000-8000-000000000001', 'f0000001-0000-4000-8000-000000000004', '2026-10-11', '08:00:00', '10:00:00', 'disetujui', 'Rapat panitia inaugurasi mahasiswa baru', '2026-10-08 14:00:00', NULL, NULL, NULL),
('r0000001-0000-4000-8000-000000000004', 'a0000001-0000-4000-8000-000000000001', 'f0000001-0000-4000-8000-000000000007', '2026-10-08', '10:00:00', '12:00:00', 'disetujui', 'Latihan rutin tim basket fakultas', '2026-10-05 09:00:00', NULL, NULL, NULL),
('r0000001-0000-4000-8000-000000000005', 'a0000001-0000-4000-8000-000000000001', 'f0000001-0000-4000-8000-000000000001', '2026-10-12', '13:00:00', '17:00:00', 'ditolak', 'Seminar talkshow kewirausahaan digital', '2026-10-08 09:00:00', '2026-10-09 14:00:00', 'b0000001-0000-4000-8000-000000000001', 'Kapasitas dan peruntukan ruangan tidak sesuai dengan estimasi peserta'),
('r0000001-0000-4000-8000-000000000006', 'a0000001-0000-4000-8000-000000000001', 'f0000001-0000-4000-8000-000000000006', '2026-10-14', '08:00:00', '11:00:00', 'ditolak', 'Uji coba konfigurasi routing jaringan', '2026-10-08 10:30:00', '2026-10-09 16:30:00', 'b0000001-0000-4000-8000-000000000002', 'Fasilitas laboratorium sedang dalam pemeliharaan berkala'),
('r0000001-0000-4000-8000-000000000007', 'a0000001-0000-4000-8000-000000000001', 'f0000001-0000-4000-8000-000000000002', '2026-10-13', '09:00:00', '11:00:00', 'dibatalkan', 'Briefing panitia lomba fotografi', '2026-10-07 08:00:00', '2026-10-08 10:00:00', 'a0000001-0000-4000-8000-000000000001', 'Kegiatan briefing dibatalkan oleh ketua pelaksana'),
('r0000001-0000-4000-8000-000000000008', 'a0000001-0000-4000-8000-000000000001', 'f0000001-0000-4000-8000-000000000003', '2026-10-10', '14:00:00', '16:00:00', 'dibatalkan', 'Bimbingan kelompok persiapan kompetisi', '2026-10-06 13:00:00', '2026-10-07 15:00:00', 'b0000001-0000-4000-8000-000000000001', 'Jadwal ruangan dialihkan untuk rapat pimpinan universitas'),
('r0000001-0000-4000-8000-000000000009', 'a0000001-0000-4000-8000-000000000001', 'f0000001-0000-4000-8000-000000000004', '2026-10-05', '08:00:00', '10:00:00', 'kadaluarsa', 'Kelas pengganti mata kuliah matematika diskrit', '2026-10-03 16:00:00', NULL, NULL, NULL),
('r0000001-0000-4000-8000-000000000010', 'a0000001-0000-4000-8000-000000000001', 'f0000001-0000-4000-8000-000000000005', '2026-10-06', '13:00:00', '15:00:00', 'kadaluarsa', 'Simulasi praktikum pemrograman berorientasi objek', '2026-10-04 11:00:00', NULL, NULL, NULL),
('r0000001-0000-4000-8000-000000000011', 'a0000001-0000-4000-8000-000000000002', 'f0000001-0000-4000-8000-000000000001', '2026-10-12', '08:00:00', '11:00:00', 'disetujui', 'Workshop pengembangan aplikasi web modern', '2026-10-08 09:00:00', NULL, NULL, NULL),
('r0000001-0000-4000-8000-000000000012', 'a0000001-0000-4000-8000-000000000003', 'f0000001-0000-4000-8000-000000000001', '2026-10-14', '13:00:00', '16:00:00', 'disetujui', 'Seminar nasional kewirausahaan mahasiswa', '2026-10-08 10:00:00', NULL, NULL, NULL),
('r0000001-0000-4000-8000-000000000013', 'a0000001-0000-4000-8000-000000000002', 'f0000001-0000-4000-8000-000000000001', '2026-10-16', '09:00:00', '12:00:00', 'disetujui', 'Pelatihan public speaking dan kepemimpinan BEM', '2026-10-09 08:30:00', NULL, NULL, NULL),
('r0000001-0000-4000-8000-000000000014', 'a0000001-0000-4000-8000-000000000003', 'f0000001-0000-4000-8000-000000000001', '2026-10-19', '10:00:00', '12:00:00', 'proses pengajuan', 'Gladi bersih pentas seni tahunan mahasiswa', '2026-10-10 09:00:00', NULL, NULL, NULL),
('r0000001-0000-4000-8000-000000000015', 'a0000001-0000-4000-8000-000000000002', 'f0000001-0000-4000-8000-000000000001', '2026-10-21', '13:00:00', '15:30:00', 'disetujui', 'Kuliah tamu pakar kecerdasan buatan dari industri', '2026-10-09 14:00:00', NULL, NULL, NULL),
('r0000001-0000-4000-8000-000000000016', 'a0000001-0000-4000-8000-000000000003', 'f0000001-0000-4000-8000-000000000001', '2026-10-23', '08:30:00', '11:30:00', 'disetujui', 'Kompetisi debat bahasa inggris antar fakultas', '2026-10-09 15:00:00', NULL, NULL, NULL),
('r0000001-0000-4000-8000-000000000017', 'a0000001-0000-4000-8000-000000000002', 'f0000001-0000-4000-8000-000000000003', '2026-10-13', '10:00:00', '12:00:00', 'disetujui', 'Diskusi pengerjaan proyek akhir kecerdasan buatan', '2026-10-08 11:00:00', NULL, NULL, NULL),
('r0000001-0000-4000-8000-000000000018', 'a0000001-0000-4000-8000-000000000003', 'f0000001-0000-4000-8000-000000000005', '2026-10-15', '14:00:00', '16:00:00', 'disetujui', 'Praktikum kelompok pemodelan basis data terdistribusi', '2026-10-09 13:00:00', NULL, NULL, NULL);

INSERT INTO reports (rep_id, reported_at, category, rep_desc, rep_status, user_id, fac_id, resolution_note, closed_at) VALUES
('p0000001-0000-4000-8000-000000000001', '2026-10-09 10:15:00', 'kerusakan peralatan', 'Remote pendingin ruangan tidak merespons dan temperatur tidak dapat disetel.', 'baru', 'a0000001-0000-4000-8000-000000000001', 'f0000001-0000-4000-8000-000000000001', NULL, NULL),
('p0000001-0000-4000-8000-000000000002', '2026-10-08 14:20:00', 'kelistrikan', 'Saklar lampu utama di sisi kanan ruangan berderik dan lampu berkedip terus-menerus.', 'diproses', 'a0000001-0000-4000-8000-000000000001', 'f0000001-0000-4000-8000-000000000003', NULL, NULL),
('p0000001-0000-4000-8000-000000000003', '2026-10-07 09:00:00', 'kerusakan peralatan', 'Monitor PC nomor 12 tidak menampilkan tampilan dan tercium bau kabel panas.', 'selesai', 'a0000001-0000-4000-8000-000000000001', 'f0000001-0000-4000-8000-000000000005', 'Unit monitor dan kabel power telah diganti dengan unit cadangan yang berfungsi normal.', '2026-10-08 11:30:00'),
('p0000001-0000-4000-8000-000000000004', '2026-10-06 11:45:00', 'kerusakan bangunan', 'Baut pengait papan ring basket longgar dan papan bergetar saat digunakan.', 'selesai', 'a0000001-0000-4000-8000-000000000001', 'f0000001-0000-4000-8000-000000000007', 'Pengencangan baut struktur dan penggantian bracket pengaman papan pantul telah selesai dilakukan.', '2026-10-07 14:00:00'),
('p0000001-0000-4000-8000-000000000005', '2026-10-09 16:30:00', 'kebersihan', 'Lantai ruangan kotor berdebu setelah kegiatan seminar hari sebelumnya.', 'baru', 'a0000001-0000-4000-8000-000000000001', 'f0000001-0000-4000-8000-000000000002', NULL, NULL),
('p0000001-0000-4000-8000-000000000006', '2026-10-08 13:10:00', 'lainnya', 'Kabel HDMI proyektor portabel hilang saat pengembalian barang.', 'ditolak', 'a0000001-0000-4000-8000-000000000001', 'f0000001-0000-4000-8000-000000000009', NULL, NULL),
('p0000001-0000-4000-8000-000000000007', '2026-10-09 11:00:00', 'kelistrikan', 'Colokan stopkontak panggung utama tidak mengalirkan arus listrik.', 'baru', 'a0000001-0000-4000-8000-000000000002', 'f0000001-0000-4000-8000-000000000001', NULL, NULL),
('p0000001-0000-4000-8000-000000000008', '2026-10-08 15:30:00', 'kerusakan bangunan', 'Pintu masuk laboratorium seret dan sulit ditutup rapat.', 'diproses', 'a0000001-0000-4000-8000-000000000002', 'f0000001-0000-4000-8000-000000000006', NULL, NULL),
('p0000001-0000-4000-8000-000000000009', '2026-10-07 14:15:00', 'kerusakan peralatan', 'Konektor kabel LAN pada meja praktikum nomor 5 patah penguncinya.', 'selesai', 'a0000001-0000-4000-8000-000000000002', 'f0000001-0000-4000-8000-000000000005', 'Kabel patch cord RJ45 telah dikrimping ulang dan koneksi internet kembali normal.', '2026-10-09 16:00:00'),
('p0000001-0000-4000-8000-000000000010', '2026-10-08 10:40:00', 'kerusakan peralatan', 'Mikrofon nirkabel panggung aula menghasilkan suara dengung keras.', 'diproses', 'a0000001-0000-4000-8000-000000000003', 'f0000001-0000-4000-8000-000000000001', NULL, NULL),
('p0000001-0000-4000-8000-000000000011', '2026-10-05 08:30:00', 'kelistrikan', 'Lampu penerangan bagian tengah ruang kelas mati total.', 'selesai', 'a0000001-0000-4000-8000-000000000003', 'f0000001-0000-4000-8000-000000000004', 'Dua unit lampu tabung LED telah diganti baru oleh teknisi sarpras.', '2026-10-06 10:00:00'),
('p0000001-0000-4000-8000-000000000012', '2026-10-09 13:50:00', 'kebersihan', 'Jaring pembatas lapangan futsal tertempel sampah sisa pertandingan.', 'baru', 'a0000001-0000-4000-8000-000000000003', 'f0000001-0000-4000-8000-000000000008', NULL, NULL);
