<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/validate_session.php';

class DashboardController {
    private $db;

    public function __construct() {
        check_session_valid('petugas');
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        $queryPending = "SELECT r.rsv_id, r.rsv_date, r.start_time, r.end_time, 
                                u.username, f.fac_name, f.fac_status 
                         FROM reservations r 
                         JOIN users u ON r.user_id = u.user_id 
                         JOIN facilities f ON r.fac_id = f.fac_id 
                         WHERE r.rsv_status = 'proses pengajuan' 
                         ORDER BY r.rsv_date ASC, r.start_time ASC";
        $stmtPending = $this->db->prepare($queryPending);
        $stmtPending->execute();
        $pendingReservations = $stmtPending->fetchAll(PDO::FETCH_ASSOC);

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

        $queryApproved = "SELECT r.rsv_id, r.rsv_date, r.start_time, r.end_time, 
                                 u.username, f.fac_name, f.fac_status 
                          FROM reservations r 
                          JOIN users u ON r.user_id = u.user_id 
                          JOIN facilities f ON r.fac_id = f.fac_id 
                          WHERE r.rsv_status = 'disetujui' AND r.rsv_date >= CURDATE() 
                          ORDER BY (f.fac_status IN ('Nonaktif', 'Dalam perbaikan', 'nonaktif', 'dalam perbaikan')) DESC,
                                   r.rsv_date ASC, r.start_time ASC";
        $stmtApproved = $this->db->prepare($queryApproved);
        $stmtApproved->execute();
        $approvedReservations = $stmtApproved->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../views/petugas/dashboard.php';
    }
}