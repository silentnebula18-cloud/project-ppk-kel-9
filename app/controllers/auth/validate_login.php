<?php
header('Content-Type: application/json');
// SET RESPONSE KE JS, BENTUKNYA JSON

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(response_error(':/'));
    exit;
}

require_once __DIR__ . '/../../../config/db_connect.php';

// Database Connect
$dbInstance = new db_connect(); 
$pdo = $dbInstance->getConnection();


$jsoninput = file_get_contents('php://input');
$data = json_decode($jsoninput, true);
//hasilnya menjadi associative array PHP. 

if ($data){
    // cek apakah $data ada (ada isinya, bukan null atau kosong atau array kosong atau 0)
    if (key_exists('username', $data) and key_exists('password', $data)){
        $username = $data['username'] ;
        $password = $data['password'] ;

        $match_password = validate_password($username, $password, $pdo);
        if ($match_password){
            session_start();

            $_SESSION["user_id"] = $match_password["user_id"];
            $_SESSION["username"] = $match_password["username"];
            $_SESSION["email"] = $match_password["email"];
            $_SESSION["role"] = $match_password["role"];
            
            $response = response_success(["user" => $match_password]);
        }else{
            if (check_unverified($username, $password, $pdo)){
                $response = response_error("masih unverified");
            }else{
                $response = response_error("invalid credential");
            }
        }
    }else{
        $response = response_error("err");
    }
}else{
    $response = response_error("err");

}

echo json_encode($response);

function validate_username($username, $pdo){
    $sql = "SELECT user_id, username, password, email, role FROM users WHERE username = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    // Mengambil satu baris data pertama hasil query.

    if ($user){
        return $user;
    }
    return false;
}

function validate_password($username, $password, $pdo){
    $user = validate_username($username, $pdo);
    if ($user) {
        $match = password_verify($password, $user["password"]);
        // password_verify() -> fungsi bawaan php
        if ($match){
            return ["user_id"=>$user["user_id"], "username"=>$user["username"], "email"=>$user["email"], "role"=>$user["role"]];
        }
    }
    return false;
}

function check_unverified($username, $password, $pdo){
    $sql = "SELECT unv_password FROM unverified_acc WHERE unv_username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $unv_user = $stmt->fetch();

    // ditemukan/ ada datanya dan verify
    if ($unv_user && password_verify($password, $unv_user['unv_password'])) {
        return true;
    }
    return false;
}

function response_success(array $additional_info = []){
    $success = ['status' => 'success'];
    if ($additional_info){
        $success = array_merge($success, $additional_info);
    }
    return $success;
}

function response_error($msg){
    return ['status' => 'error', 'message' => $msg];
}
?>
