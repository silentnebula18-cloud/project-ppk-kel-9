<?php
class report_model {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Simpan laporan baru
    public function create($rep_id, $category, $rep_desc, $user_id, $fac_id) {

        $sql = "INSERT INTO reports (
                    rep_id,
                    category,
                    rep_desc,
                    rep_status,
                    user_id,
                    fac_id
                ) VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $rep_id,
            $category,
            $rep_desc,
            'baru',
            $user_id,
            $fac_id
        ]);
    }

    // Ambil laporan milik user dengan filter dan pagination
    public function getByUser_filtered(
        $user_id,
        $category,
        $status,
        $limit,
        $offset
    ) {
        $sql = "SELECT
                    r.rep_id,
                    r.reported_at,
                    r.category,
                    r.rep_status,
                    f.fac_name,
                    f.location
                FROM reports r
                JOIN facilities f ON r.fac_id = f.fac_id
                WHERE r.user_id = ?";

        $params = [$user_id];

        if ($category !== '') {
            $sql .= " AND r.category = ?";
            $params[] = $category;
        }

        if ($status !== '') {
            $sql .= " AND r.rep_status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY r.reported_at DESC
                  LIMIT ? OFFSET ?";

        $params[] = (int) $limit;
        $params[] = (int) $offset;

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Hitung jumlah laporan milik user sesuai filter
    public function countByUser_filtered(
        $user_id,
        $category,
        $status
    ) {
        $sql = "SELECT COUNT(*)
                FROM reports r
                WHERE r.user_id = ?";

        $params = [$user_id];

        if ($category !== '') {
            $sql .= " AND r.category = ?";
            $params[] = $category;
        }

        if ($status !== '') {
            $sql .= " AND r.rep_status = ?";
            $params[] = $status;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    // Ambil detail satu laporan milik pengguna
    public function getDetailById($rep_id, $user_id) {
        $sql = "SELECT
                    rep_id,
                    reported_at,
                    category,
                    rep_desc,
                    rep_status,
                    resolution_note,
                    closed_at,
                    fac_id
                FROM reports
                WHERE rep_id = ?
                AND user_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$rep_id, $user_id]);

        $report = $stmt->fetch(PDO::FETCH_ASSOC);

        return $report ?: null;
    }
}
