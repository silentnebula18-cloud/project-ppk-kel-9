<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(response_error(':/'));
    exit;
}

$jsoninput = file_get_contents('php://input');
$data = json_decode($jsoninput, true);

if ($data){
    if (key_exists('email', $data) and key_exists('username', $data) and key_exists('password', $data)){
        $email = $data['email'] ;
        $username = $data['username'] ;
        $password = $data['password'] ;

        if (validate_password($password) and validate_username($username, $pdo) and validate_email($email, $pdo)){
            update_database($pdo, $email, $username, $password);
            $response = response_success();
        }else{
            $response = response_error(":/");
        }


    }else{
        $response = response_error(":/");
    }

} else {
    $response = response_error("No data received or invalid JSON.");
}

echo json_encode($response);

function validate_password($password){
    $passwordLength = mb_strlen($password, 'UTF-8'); 
    return $passwordLength >= 8 and preg_match('/[0-9]/', $password) and preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password);
}

function validate_username($username, $pdo){
    $sql = "SELECT 1 FROM unverified_acc WHERE unv_username = ?
            UNION
            SELECT 1 FROM users WHERE username = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $username]);
    if ($stmt->fetch()) {
        return false; 
    }
    return true;
}

function validate_email($email, $pdo){
    $sql = "SELECT 1 FROM unverified_acc WHERE unv_email = ?
            UNION
            SELECT 1 FROM users WHERE email = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email, $email]);
    if ($stmt->fetch()) {
        return false; 
    }
    return true;
}

function response_success(){
    return ['status' => 'success'];
}

function response_error($msg){
    return ['status' => 'error', 'message' => $msg];
}

function update_database($pdo, $email, $username, $password) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO unverified_acc (unv_username, unv_password, unv_email) 
            VALUES (?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$username, $hashed_password, $email]);
}
?>