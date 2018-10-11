<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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

if ($method !== "PUT") {
    $response['state'] = "error";
    $response['error']['code'] = 16;
    $response['error']['msg'] = "Only PUT method is allowed";
    $response['response'] = NULL;

    http_response_code(405);
    http_response_code();
    header('Content-Type: application/json');

    echo json_encode($response);
    exit;
}

$_PUT = array();
parse_str(file_get_contents('php://input'), $_PUT);

$token = $_SERVER['HTTP_TOKEN'] ?? NULL;
$id = $_PUT['id'] ?? NULL;
$full_name = $_PUT['full_name'] ?? NULL;
$email = $_PUT['email'] ?? NULL;
$pass = $_PUT['password'] ?? NULL;

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

if ($id == NULL) {
    $response['state'] = "error";
    $response['error']['code'] = 17;
    $response['error']['msg'] = "please enter id of a user";
    $response['response'] = NULL;

    http_response_code(400);
    http_response_code();
    header('Content-Type: application/json');

    echo json_encode($response);
    exit;
}

if ($id != NULL) {
    if (!filter_var($id, FILTER_VALIDATE_INT)) {
        $response['state'] = "error";
        $response['error']['code'] = 18;
        $response['error']['msg'] = "Invalid id for a user";
        $response['response'] = NULL;

        http_response_code(400);
        http_response_code();
        header('Content-Type: application/json');

        echo json_encode($response);
        exit;
    }
}

if ($email == NULL) {
    http_response_code(400);
    http_response_code();
    header('Content-Type: application/json');

    $response['state'] = "error";
    $response['error']['code'] = 19;
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
    $response['error']['code'] = 20;
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
    $response['error']['code'] = 21;
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
    $response['error']['code'] = 22;
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
    $response['error']['code'] = 23;
    $response['error']['msg'] = "Password length must be greater or equal eight "
            . "character in length, must inculde a number and must incude a alphabet";
    $response['response'] = NULL;
    echo json_encode($response);
    exit;
}

/** Password encryption * */
require_once '../config/constants.php';
$salt = SALT;
$pass = crypt($pass, $salt);
$pass = substr($pass, 30);

/* * Open the db connection* */
require_once '../config/db.php';

/* * * prepare the SQL statement ** */
$sql = "UPDATE users SET email = :email, full_name = :full_name, passw = :passw WHERE id = :id";
$stmt = $db->prepare($sql);

/* * * bind the paramaters ** */
$stmt->bindParam(':email', $email, PDO::PARAM_STR, 100);
$stmt->bindParam(':full_name', $full_name, PDO::PARAM_STR, 100);
$stmt->bindParam(':passw', $pass, PDO::PARAM_STR, 100);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);

/* * * execute the prepared statement ** */
$stmt->execute();
$numofEffRows = $stmt->rowCount();

if ($numofEffRows === 1) {
    $response['state'] = "success";
    $response['error']['code'] = NULL;
    $response['error']['msg'] = NULL;
    $response['response']['id'] = $id;

    http_response_code(200);
    http_response_code();
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} 
else {
    $response['state'] = "fail";
    $response['error']['code'] = 24;
    $response['error']['msg'] = "No new updates or No user found";
    $response['response'] = NULL;

    http_response_code(409); // Need to confirm about the http response code, this code could be 204 or 202
    http_response_code();
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}