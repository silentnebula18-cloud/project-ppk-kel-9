<?php
header('Content-Type: application/json');

$unverified_users_raw = file_get_contents('../../database_mockup/unverified_acc.json');
$unverified_users = json_decode($unverified_users_raw, true) ?? [];

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

        if (validate_password($password) and validate_username($username, $unverified_users) and validate_email($email, $unverified_users)){
            update_database($unverified_users, $email, $username, $password);
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

function validate_username($username, $unverified_users){
    $search_user = search($unverified_users, "username", $username);
    if ($search_user[0]){
        return false;
    }
    return true;
}

function validate_email($email, $unverified_users){
    $search_user = search($unverified_users, "email", $email);
    if ($search_user[0]){
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

function update_database($unverified_users, $email, $username, $password) {
    $next_id = 0;
    if (!empty($unverified_users)) {
        $last_user = end($unverified_users);
        $next_id = isset($last_user['id']) ? $last_user['id'] + 1 : count($unverified_users);
    }

    $unverified_users[] = [
        'id'       => $next_id,
        'username' => $username,
        'password' => $password,
        'role'     => 'user',
        'email'    => $email
    ];

    $encoded = json_encode($unverified_users, JSON_PRETTY_PRINT);
    file_put_contents("../../database_mockup/unverified_acc.json", $encoded, LOCK_EX);
}

function search($obj, $key, $value){
    for($i = 0; $i < count($obj); $i++){
        if ($obj[$i][$key] == $value){
            return [true, $obj[$i]['id'], $obj[$i][$key]];
        };
    };
    return [false,-1, -1];
}
?>