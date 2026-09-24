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
    if (key_exists('username', $data) and key_exists('password', $data)){
        $username = $data['username'] ;
        $password = $data['password'] ;

        $match_password = validate_password($username, $password, $pdo);
        if ($match_password){
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

    if ($user){
        return $user;
    }
    return false;
}

function validate_password($username, $password, $pdo){
    $user = validate_username($username, $pdo);
    if ($user) {
        $match = password_verify($password, $user["password"]);
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
