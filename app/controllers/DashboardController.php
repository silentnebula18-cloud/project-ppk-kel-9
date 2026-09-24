<?php
require_once __DIR__ . '/../../config/database.php';

class DashboardController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        //List Reservasi Diproses
        $queryPending = "SELECT r.*, u.username, f.fac_name 
                         FROM reservations r 
                         JOIN users u ON r.user_id = u.user_id 
                         JOIN facilities f ON r.fac_id = f.fac_id 
                         WHERE r.rsv_status = 'proses pengajuan' 
                         ORDER BY r.rsv_date ASC, r.start_time ASC";
        $stmtPending = $this->db->prepare($queryPending);
        $stmtPending->execute();
        $pendingReservations = $stmtPending->fetchAll(PDO::FETCH_ASSOC);

        //List Laporan
        $queryReports = "SELECT rp.*, u.username, f.fac_name 
                         FROM reports rp 
                         JOIN users u ON rp.user_id = u.user_id 
                         JOIN facilities f ON rp.fac_id = f.fac_id 
                         WHERE rp.rep_status IN ('baru', 'diproses') 
                         ORDER BY rp.reported_at DESC";
        $stmtReports = $this->db->prepare($queryReports);
        $stmtReports->execute();
        $reports = $stmtReports->fetchAll(PDO::FETCH_ASSOC);

        //List Reservasi Diterima
        $queryApproved = "SELECT r.*, u.username, f.fac_name 
                          FROM reservations r 
                          JOIN users u ON r.user_id = u.user_id 
                          JOIN facilities f ON r.fac_id = f.fac_id 
                          WHERE r.rsv_status = 'disetujui' AND r.rsv_date >= CURDATE() 
                          ORDER BY r.rsv_date ASC";
        $stmtApproved = $this->db->prepare($queryApproved);
        $stmtApproved->execute();
        $approvedReservations = $stmtApproved->fetchAll(PDO::FETCH_ASSOC);

        //Render file view dari folder views
        require_once __DIR__ . '/../../views/petugas/dashboard.php';
    }
}