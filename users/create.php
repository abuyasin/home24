<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//error_reporting(0);
require_once '../config/constants.php';
require_once '../config/db.php';
require_once '../common/functions.php';

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
$full_name = $_POST['full_name'] ?? NULL;
$email = $_POST['email'] ?? NULL;
$pass = $_POST['password'] ?? NULL;



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
//echo(checkTokenValidity($token));
if (!checkTokenValidity($token, $db)) {
    $response['state'] = "fail";
    $response['error']['code'] = 7;
    $response['error']['msg'] = "Not a valid Token!!!";
    $response['response'] = NULL;

    http_response_code(401);
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
    $response['error']['code'] = 8;
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
    $response['error']['code'] = 9;
    $response['error']['msg'] = "Invalid email address";
    $response['response'] = NULL;
    echo json_encode($response);
    exit;
}

if ($full_name == NULL) {
    http_response_code(400);
    http_response_code();
    header('Content-Type: application/json');

    $response['state'] = "error";
    $response['error']['code'] = 10;
    $response['error']['msg'] = "Please enter full name ";
    $response['response'] = NULL;
    echo json_encode($response);
    exit;
}

if ($pass == NULL) {
    http_response_code(400);
    http_response_code();
    header('Content-Type: application/json');

    $response['state'] = "error";
    $response['error']['code'] = 11;
    $response['error']['msg'] = "Please enter password";
    $response['response'] = NULL;
    echo json_encode($response);
    exit;
}

if (checkPassword($pass) == FALSE) {
    http_response_code(400);
    http_response_code();
    header('Content-Type: application/json');

    $response['state'] = "error";
    $response['error']['code'] = 12;
    $response['error']['msg'] = "Password length must be greater or equal eight "
            . "character in length, must inculde a number and must incude a alphabet";
    $response['response'] = NULL;
    echo json_encode($response);
    exit;
}

/** Password encryption * */
$salt = SALT;
$pass = crypt($pass, $salt);
$pass = substr($pass, 30);
//var_dump($db);

$token = alphaNumeric(TOKEN_LENGTH);
/* * * prepare the SQL statement ** */
$sql = "INSERT INTO users SET email = :email, full_name = :full_name, passw = :passw, token = :token";
$stmt = $db->prepare($sql);

/* * * bind the paramaters ** */
$stmt->bindParam(':email', $email, PDO::PARAM_STR, 100);
$stmt->bindParam(':full_name', $full_name, PDO::PARAM_STR, 100);
$stmt->bindParam(':passw', $pass, PDO::PARAM_STR, 100);
$stmt->bindParam(':token', $token, PDO::PARAM_STR, 50);

/* * * execute the prepared statement ** */
$st = $stmt->execute();

$userId = $db->lastInsertId();

/** close the db connection* */
require_once '../config/dbclose.php';

if ($userId > 0) {
    $response['state'] = "success";
    $response['error']['code'] = NULL;
    $response['error']['msg'] = NULL;
    $response['response']['id'] = $userId;

    http_response_code(201);
    http_response_code();
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} else {
    $response['state'] = "fail";
    $response['error']['code'] = 13;
    $response['error']['msg'] = "User creation failed! may be user already exists.";
    $response['response'] = NULL;

    http_response_code(409); // Need to confirm about the http response code
    http_response_code();
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}