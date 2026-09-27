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
}