<?php
$email = $_POST['email'] ?? NULL;
$pass = $_POST['password'] ?? NULL;

$response = [
    'state' => '',
    'error' => [
        'code' => '',
        'msg' => '',
    ],
    'response' => []
];

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== "POST") {
    $response['state'] = "error";
    $response['error']['code'] = 16;
    $response['error']['msg'] = "Only POST method is allowed";
    $response['response'] = NULL;

    http_response_code(405);
    http_response_code();
    header('Content-Type: application/json');

    echo json_encode($response);
    exit;
}

if ($email == NULL) {
    http_response_code(400);
    http_response_code();
    header('Content-Type: application/json');

    $response['state'] = "error";
    $response['error']['code'] = 1;
    $response['error']['msg'] = "Please enter email address";
    $response['response'] = NULL;
    echo json_encode($response);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    http_response_code();
    header('Content-Type: application/json');

    $response['state'] = "error";
    $response['error']['code'] = 2;
    $response['error']['msg'] = "Invalid email address";
    $response['response'] = NULL;
    echo json_encode($response);
    exit;
}

if ($pass == NULL) {
    http_response_code(400);
    http_response_code();
    header('Content-Type: application/json');

    $response['state'] = "error";
    $response['error']['code'] = 3;
    $response['error']['msg'] = "Please enter password";
    $response['response'] = NULL;
    echo json_encode($response);
    exit;
}
/** Password encryption * */
require_once './config/constants.php';
$salt = SALT;
$pass = crypt($pass, $salt);
$pass = substr($pass, 30);

/* * Open the db connection* */
require_once './config/db.php';

/* * * prepare the SQL statement ** */
$sql = "SELECT * FROM users WHERE email = :email AND passw = :passw";
$stmt = $db->prepare($sql);

/* * * bind the paramaters ** */
$stmt->bindParam(':email', $email, PDO::PARAM_STR, 100);
$stmt->bindParam(':passw', $pass, PDO::PARAM_STR, 100);

/* * * execute the prepared statement ** */
$stmt->execute();

/* * * fetch the results ** */
$result = $stmt->fetchObject();
//var_dump($result);

$userId = $result->id ?? NULL;
if ($userId == NULL) {
    /** close the db connection* */
    require_once './config/dbclose.php';

    http_response_code(401);
    http_response_code();
    header('Content-Type: application/json');

    $response['state'] = "fail";
    $response['error']['code'] = 4;
    $response['error']['msg'] = "Invalid email and/or password!!!";
    $response['response'] = NULL;
    echo json_encode($response);
    exit;
}

require_once './common/functions.php';
/* * Generate token* */
$token = alphaNumeric(TOKEN_LENGTH);

/* * Generate token expiry time* */
$token_expiry_timestamp = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + TOKEN_EXPIRY);

$sql2 = "UPDATE users SET token='$token', token_expiry='$token_expiry_timestamp' WHERE id=$userId";
$stmt2 = $db->prepare($sql2);

/* * * execute the prepared statement ** */
$count = $stmt2->execute();

/** close the db connection* */
require_once './config/dbclose.php';

if ($count == 1) {
    $response['state'] = "success";
    $response['error']['code'] = NULL;
    $response['error']['msg'] = NULL;
    $response['response']['token'] = $token;
    $response['response']['token_expiry'] = $token_expiry_timestamp;

    http_response_code(200);
    http_response_code();
    header('Content-Type: application/json');

    echo json_encode($response);
    exit;
}