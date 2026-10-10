<?php
require_once __DIR__ . "/../../config/db_connect.php";

function getAllFacilities($conn)
{
    $sql = "SELECT
                f.fac_id,
                f.fac_name,
                f.type,
                f.location,
                f.capacity,
                f.fac_desc,
                f.fac_photo,
                f.fac_status,
                (SELECT COUNT(*) FROM reservations r WHERE r.fac_id = f.fac_id) as rsv_total,
                (SELECT COUNT(*) FROM reports rp WHERE rp.fac_id = f.fac_id) as report_total
            FROM facilities f
            ORDER BY f.fac_name ASC";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Query gagal: " . mysqli_error($conn));
    }

    $bulan = getJumlahBulanData($conn);

    $facilities = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['periode_bulan'] = $bulan;
        $row['rsv_avg'] = round($row['rsv_total'] / $bulan, 1);
        $row['report_avg'] = round($row['report_total'] / $bulan, 1);
        $facilities[] = $row;
    }
    return $facilities;
}

function getJumlahBulanData($conn)
{
    $sql = "SELECT GREATEST(1, TIMESTAMPDIFF(MONTH,
                LEAST(
                    COALESCE((SELECT MIN(rsv_date) FROM reservations), CURDATE()),
                    COALESCE((SELECT MIN(DATE(reported_at)) FROM reports), CURDATE())
                ),
                CURDATE()) + 1) AS bulan";
    $result = mysqli_query($conn, $sql);
    $row = $result ? mysqli_fetch_assoc($result) : null;

    return max(1, (int) ($row['bulan'] ?? 1));
}

function formatRataBulan($nilai)
{
    return number_format((float) $nilai, 1, ',', '.');
}

function getFacilityById($conn, $id)
{
    $stmt = mysqli_prepare($conn, "SELECT * FROM facilities WHERE fac_id = ?");
    mysqli_stmt_bind_param($stmt, "s", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $facility = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return $facility ?: null;
}

function createFacility($conn, $id, $name, $type, $location, $capacity, $desc, $photo = null)
{
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO facilities (fac_id, fac_name, type, location, capacity, fac_desc, fac_photo, fac_status)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'Aktif')"
    );
    mysqli_stmt_bind_param($stmt, "ssssiss", $id, $name, $type, $location, $capacity, $desc, $photo);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ok;
}

function updateFacility($conn, $id, $name, $type, $location, $capacity, $desc, $status, $photo = null)
{
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE facilities
         SET fac_name = ?, type = ?, location = ?, capacity = ?, fac_desc = ?, fac_status = ?,
             fac_photo = COALESCE(?, fac_photo)
         WHERE fac_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "sssissss", $name, $type, $location, $capacity, $desc, $status, $photo, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ok;
}

function deleteFacility($conn, $id)
{
    try {
        $stmt = mysqli_prepare($conn, "DELETE FROM facilities WHERE fac_id = ?");
        mysqli_stmt_bind_param($stmt, "s", $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1451) {
            return 'has_history';
        }
        throw $e;
    }
}

function setFacilityStatus($conn, $id, $status)
{
    $stmt = mysqli_prepare($conn, "UPDATE facilities SET fac_status = ? WHERE fac_id = ?");
    mysqli_stmt_bind_param($stmt, "ss", $status, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ok;
}

function countFacilitiesByStatus($conn, $status)
{
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM facilities WHERE fac_status = ?");
    mysqli_stmt_bind_param($stmt, "s", $status);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return (int) $row['total'];
}

function getRekapPerLokasi($facilities)
{
    $rekap = [];
    foreach ($facilities as $f) {
        $lok = $f['location'];
        if (!isset($rekap[$lok])) {
            $rekap[$lok] = ['location' => $lok, 'fac_count' => 0, 'rsv_total' => 0, 'report_total' => 0, 'periode' => (int) $f['periode_bulan']];
        }
        $rekap[$lok]['fac_count']++;
        $rekap[$lok]['rsv_total']    += (int) $f['rsv_total'];
        $rekap[$lok]['report_total'] += (int) $f['report_total'];
    }
    ksort($rekap);
    foreach ($rekap as &$r) {
        $r['rsv_avg']    = round($r['rsv_total'] / $r['periode'], 1);
        $r['report_avg'] = round($r['report_total'] / $r['periode'], 1);
    }
    unset($r);
    return array_values($rekap);
}

function buatUuid()
{
    $b = random_bytes(16);
    $b[6] = chr((ord($b[6]) & 0x0f) | 0x40);
    $b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($b), 4));
}

function adaFoto($path)
{
    if (empty($path) || strpos($path, '..') !== false) {
        return false;
    }
    return is_file(__DIR__ . '/../../' . ltrim($path, '/'));
}

function urlFoto($path)
{
    $path = ltrim($path, '/');
    $encoded = implode('/', array_map('rawurlencode', explode('/', $path)));
    return '../../' . $encoded . '?v=' . filemtime(__DIR__ . '/../../' . $path);
}

class facility_model {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllFacilities()
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

    public function getFacilityById($id)
    {
        $query = "SELECT * FROM facilities WHERE fac_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        $facility = $stmt->fetch(PDO::FETCH_ASSOC);
        return $facility ?: null;
    }

    public function createFacility($name, $type, $location, $capacity, $desc)
    {
        $query = "INSERT INTO facilities(fac_name, type, location, capacity, fac_desc, fac_status)
                  VALUES (?, ?, ?, ?, ?, 'Aktif')";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([$name, $type, $location, $capacity, $desc]);
    }

    public function updateFacility($id, $name, $type, $location, $capacity, $desc, $status)
    {
        $query = "UPDATE facilities
                  SET fac_name = ?, type = ?, location = ?, capacity = ?, fac_desc = ?, fac_status = ?
                  WHERE fac_id = ?";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$name, $type, $location, $capacity, $desc, $status, $id]);
    }

    public function deleteFacility($id)
    {
        $query = "DELETE FROM facilities WHERE fac_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    public function setFacilityStatus($id, $status)
    {
        $query = "UPDATE facilities SET fac_status = ? WHERE fac_id = ?";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([$status, $id]);
    }

    public function countFacilitiesByStatus($status)
    {
        $query = "SELECT COUNT(*) AS total FROM facilities WHERE fac_status = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$status]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) $row['total'];
    }

    public function getFac_limit($limit, $offset){
        $query = "SELECT fac_id, fac_name, type, location, capacity, fac_status FROM facilities LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalFacilities(){
        $query = "SELECT COUNT(*) as total FROM facilities";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data['total'] ?? 0;
    }

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

        $paramIndex = 1;
        foreach ($params as $val) {
            $stmt->bindValue($paramIndex, $val, PDO::PARAM_STR);
            $paramIndex++;
        }

        $stmt->bindValue($paramIndex, (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue($paramIndex + 1, (int) $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

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

    public function getFacilitiesbyStatus($status) {
        $query = "SELECT *
                FROM facilities
                WHERE fac_status = ?
                ORDER BY fac_name";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$status]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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