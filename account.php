<?php

require_once './common/functions.php';

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

$token = $_SERVER['HTTP_TOKEN'] ?? NULL;

if ($token == NULL) {
    $response['state'] = "error";
    $response['error']['code'] = 5;
    $response['error']['msg'] = "please enter autherization token";
    $response['response'] = NULL;

    http_response_code(400);
    http_response_code();
    header('Content-Type: application/json');

    echo json_encode($response);
    exit;
}

/* * Open the db connection* */
require_once './config/db.php';

/* * * prepare the SQL statement ** */
$sql = "SELECT * FROM users WHERE token = :token";
$stmt = $db->prepare($sql);

/* * * bind the paramaters ** */
$stmt->bindParam(':token', $token, PDO::PARAM_STR, 50);

/* * * execute the prepared statement ** */
$stmt->execute();

/* * * fetch the results ** */
$result = $stmt->fetch(PDO::FETCH_ASSOC);


/** close the db connection* */
require_once './config/dbclose.php';


$userId = $result['id'] ?? NULL;
if ($userId == NULL) {
    $response['state'] = "fail";
    $response['error']['code'] = 6;
    $response['error']['msg'] = "Invalid or unauthorized token!!!";
    $response['response'] = NULL;

    http_response_code(401);
    http_response_code();
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

//echo $current_timestamp = date("Y-m-d H:i:s");
//echo $result['token_expiry'];
$time1 = strtotime($result['token_expiry']);
//echo date("Y-m-d H:i:s");
$time2 = strtotime(date("Y-m-d H:i:s"));
$diff = $time1 - $time2;

if ($diff > 0) {
    $response['state'] = "success";
    $response['error']['code'] = NULL;
    $response['error']['msg'] = NULL;
    $response['response'] = $result;

    http_response_code(200);
    http_response_code();
    header('Content-Type: application/json');
    
    echo json_encode($response);
    exit;
} else {
    $response['state'] = "fail";
    $response['error']['code'] = 7;
    $response['error']['msg'] = "Token Expired!!!";
    $response['response'] = NULL;

    http_response_code(401);
    http_response_code();
    header('Content-Type: application/json');
    
    echo json_encode($response);
    exit;
}