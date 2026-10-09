<?php

class facility_model{
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Ambil semua fasilitas, lengkap dengan hitungan frekuensi reservasi & frekuensi laporan kerusakan
     * @return array daftar fasilitas (tiap elemen = array asosiatif satu baris)
     */
    function getAllFacilities()
    {
        $query = "SELECT
                    f.fac_id,
                    f.fac_name,
                    f.type,
                    f.location,
                    f.capacity,
                    f.fac_desc,
                    f.fac_status,
                    (SELECT COUNT(*) FROM reservations r
                     WHERE r.fac_id = f.fac_id) AS rsv_count,
                    (SELECT COUNT(*) FROM reports rp
                     WHERE rp.fac_id = f.fac_id) AS report_count
                  FROM facilities f
                  ORDER BY f.fac_name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ambil satu fasilitas berdasarkan fac_id
     * @param string $id
     * @return array|null null kalau gak ketemu
     */
    function getFacilityById($id)
    {
        $query = "SELECT * FROM facilities WHERE fac_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        $facility = $stmt->fetch(PDO::FETCH_ASSOC);
        return $facility ?: null;
    }

    /**
     * Insert fasilitas baru. Status baru selalu "Aktif"
     * @return bool berhasil/tidak
     */
    function createFacility($name, $type, $location, $capacity, $desc)
    {
        $query = "INSERT INTO facilities(fac_name, type, location, capacity, fac_desc, fac_status)
                  VALUES (?, ?, ?, ?, ?, 'Aktif')";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([$name, $type, $location, $capacity, $desc]);
    }

    /**
     * Update data fasilitas yang sudah ada
     * @return bool berhasil/tidak
     */
    function updateFacility($id, $name, $type, $location, $capacity, $desc, $status)
    {
        $query = "UPDATE facilities
                  SET fac_name = ?, type = ?, location = ?, capacity = ?, fac_desc = ?, fac_status = ?
                  WHERE fac_id = ?";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([$name, $type, $location, $capacity, $desc, $status, $id
        ]);
    }

    /**
     * Hapus fasilitas berdasarkan fac_id
     * @return bool berhasil/tidak
     */
    function deleteFacility($id)
    {
        $query = "DELETE FROM facilities WHERE fac_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Ganti status fasilitas (dipakai buat nonaktifkan/aktifkan kembali)
     * @return bool berhasil/tidak
     */
    function setFacilityStatus($id, $status)
    {
        $query = "UPDATE facilities SET fac_status = ? WHERE fac_id = ?";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([$status, $id]);
    }

    /**
     * Hitung jumlah fasilitas berdasarkan status tertentu (mis. 'Aktif')
     * @return int
     */
    function countFacilitiesByStatus($status)
    {
        $query = "SELECT COUNT(*) AS total FROM facilities WHERE fac_status = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$status]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) $row['total'];
    }

    // Ambil Fasilitas dengan limit
    public function getFac_limit($limit, $offset){
        $query = "SELECT fac_id, fac_name, type, location, capacity, fac_status FROM facilities LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Menghitung total semua fasilitas yang ada
    public function getTotalFacilities(){
            $query = "SELECT COUNT(*) as total FROM facilities";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data['total'] ?? 0;
    }

    // Ambil Fasilitas dengan limit dan filter
    public function getFac_limit_filtered($limit, $offset, $tipe_fac, $lokasi_fac, $kapasitas_fac){
        $query = "SELECT fac_id, fac_name, type, location, capacity, fac_status FROM facilities";
    
        $conditions = [];
        $params = [];
        if (!empty($tipe_fac)) {
            $conditions[] = "type = ?";
            $params[] = $tipe_fac;
        }
        if (!empty($lokasi_fac)) {
            $conditions[] = "location = ?";
            $params[] = $lokasi_fac;
        }
        if (!empty($kapasitas_fac)) {
            $conditions[] = "capacity >= ?"; 
            $params[] = $kapasitas_fac;
        }

        if (count($conditions) > 0) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        $query .= " LIMIT ? OFFSET ?";

        $stmt = $this->conn->prepare($query);

        // Bind parameter
        $paramIndex = 1;
        foreach ($params as $val) {
            $stmt->bindValue($paramIndex, $val, PDO::PARAM_STR);
            $paramIndex++;
        }

        // 5. Bind LIMIT & OFFSET wajib
        $stmt->bindValue($paramIndex, (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue($paramIndex + 1, (int) $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

    // Total data-data fasilitas, sudah difilter, halaman: ajukan_reservasi
    public function countFac_filtered($tipe_fac = null, $lokasi_fac = null, $kapasitas_fac = null){
        $query = "SELECT COUNT(*) AS total FROM facilities";

        $conditions = [];
        $params = [];

        if (!empty($tipe_fac)) {
            $conditions[] = "type = ?";
            $params[] = $tipe_fac;
        }

        if (!empty($lokasi_fac)) {
            $conditions[] = "location = ?";
            $params[] = $lokasi_fac;
        }

        if (!empty($kapasitas_fac)) {
            $conditions[] = "capacity >= ?";
            $params[] = $kapasitas_fac;
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) ($data['total'] ?? 0);
    }

    // Ambil semua fasilitas yang statusnya
    public function getFacilitiesbyStatus($status) {

        $query = "SELECT *
                FROM facilities
                WHERE fac_status = ?
                ORDER BY fac_name";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$status]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mengambil semua fasilitas yang aktif, dengan filter opsional
    public function getActiveFac_limit_filtered($limit, $offset, $tipe_fac, $lokasi_fac, $kapasitas_fac) {
        $query = "SELECT * FROM facilities WHERE fac_status = 'aktif'";

        $params = [];

        if ($tipe_fac !== '') {
            $query .= " AND type = :tipe_fac";
            $params[':tipe_fac'] = $tipe_fac;
        }

        if ($lokasi_fac !== '') {
            $query .= " AND location = :lokasi_fac";
            $params[':lokasi_fac'] = $lokasi_fac;
        }

        if ($kapasitas_fac !== '') {
            $query .= " AND capacity >= :kapasitas_fac";
            $params[':kapasitas_fac'] = $kapasitas_fac;
        }

        $query .= " ORDER BY fac_name
                    LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Menghitung jumlah fasilitas aktif dengan filter opsional
    public function countActiveFac_filtered($tipe_fac, $lokasi_fac, $kapasitas_fac) {
        $query = "SELECT COUNT(*)
                FROM facilities
                WHERE fac_status = 'aktif'";

        $params = [];

        if ($tipe_fac !== '') {
            $query .= " AND type = :tipe_fac";
            $params[':tipe_fac'] = $tipe_fac;
        }

        if ($lokasi_fac !== '') {
            $query .= " AND location = :lokasi_fac";
            $params[':lokasi_fac'] = $lokasi_fac;
        }

        if ($kapasitas_fac !== '') {
            $query .= " AND capacity >= :kapasitas_fac";
            $params[':kapasitas_fac'] = $kapasitas_fac;
        }

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        return $stmt->fetchColumn();
    }

    // halaman: reservasi_saya
    // Ambil detail fasilitas berdasarkan fac_id, tanpa mengambil fac_id, fac_name, dan location
    // untuk Pop Up detail reservasi
    public function getDetailFasilitas_rsv_saya($fac_id) {
        $query = "SELECT type, capacity, fac_desc, fac_status
                  FROM facilities
                  WHERE fac_id = :fac_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fac_id', $fac_id, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}