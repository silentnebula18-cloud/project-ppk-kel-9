<?php
require_once __DIR__ . '/../../config/database.php';

class DashboardController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        // 1. Fetch Reservasi Diproses ('proses pengajuan')
        $queryPending = "SELECT r.rsv_id, r.rsv_date, r.start_time, r.end_time, 
                                u.username, f.fac_name 
                         FROM reservations r 
                         JOIN users u ON r.user_id = u.user_id 
                         JOIN facilities f ON r.fac_id = f.fac_id 
                         WHERE r.rsv_status = 'proses pengajuan' 
                         ORDER BY r.rsv_date ASC, r.start_time ASC";
        $stmtPending = $this->db->prepare($queryPending);
        $stmtPending->execute();
        $pendingReservations = $stmtPending->fetchAll(PDO::FETCH_ASSOC);

        // 2. Fetch Laporan ('baru', 'diproses')
        $queryReports = "SELECT rp.rep_id, rp.category, rp.rep_desc, rp.rep_status, 
                                u.username, f.fac_name 
                         FROM reports rp 
                         JOIN users u ON rp.user_id = u.user_id 
                         JOIN facilities f ON rp.fac_id = f.fac_id 
                         WHERE rp.rep_status IN ('baru', 'diproses') 
                         ORDER BY rp.reported_at DESC";
        $stmtReports = $this->db->prepare($queryReports);
        $stmtReports->execute();
        $reports = $stmtReports->fetchAll(PDO::FETCH_ASSOC);

        // 3. Fetch Reservasi Diterima ('disetujui' dan tanggal >= hari ini)
        $queryApproved = "SELECT r.rsv_id, r.rsv_date, r.start_time, r.end_time, 
                                 u.username, f.fac_name 
                          FROM reservations r 
                          JOIN users u ON r.user_id = u.user_id 
                          JOIN facilities f ON r.fac_id = f.fac_id 
                          WHERE r.rsv_status = 'disetujui' AND r.rsv_date >= CURDATE() 
                          ORDER BY r.rsv_date ASC, r.start_time ASC";
        $stmtApproved = $this->db->prepare($queryApproved);
        $stmtApproved->execute();
        $approvedReservations = $stmtApproved->fetchAll(PDO::FETCH_ASSOC);

        // Render file view di views/petugas/dashboard.php
        require_once __DIR__ . '/../../views/petugas/dashboard.php';
    }
}