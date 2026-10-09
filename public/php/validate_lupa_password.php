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
    if (key_exists('email', $data) and key_exists('old_password', $data) and key_exists('new_password', $data)){
        $email = $data['email'];
        $old_password = $data['old_password'];
        $new_password = $data['new_password'];

        $user = search_email($email, $pdo);
        if (!$user){
            $response = response_error("email_not_found");
        } elseif (!validate_old_password($user, $old_password)){
            $response = response_error("invalid_old_password");
        } elseif (!validate_password($new_password)){
            $response = response_error("invalid_new_password");
        } else {
            update_database($pdo, $email, $new_password);
            $response = response_success();
        }
    }else{
        $response = response_error(":/");
    }
} else {
    $response = response_error("No data received or invalid JSON.");
}

echo json_encode($response);

function search_email($email, $pdo){
    $sql = "SELECT user_id, email, password FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user){
        return $user;
    }
    return false;
}

function validate_old_password($user, $old_password){
    return password_verify($old_password, $user['password']);
}

function validate_password($password){
    $passwordLength = mb_strlen($password, 'UTF-8'); 
    return $passwordLength >= 8 and preg_match('/[0-9]/', $password) and preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password);
}

function update_database($pdo, $email, $password) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET password = ? WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$hashed_password, $email]);
}

function response_success(){
    return ['status' => 'success'];
}

function response_error($msg){
    return ['status' => 'error', 'message' => $msg];
}
?>
