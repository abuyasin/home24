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

if ($method !== "DELETE") {
    $response['state'] = "error";
    $response['error']['code'] = 16;
    $response['error']['msg'] = "Only DELETE method is allowed";
    $response['response'] = NULL;

    http_response_code(405);
    http_response_code();
    header('Content-Type: application/json');

    echo json_encode($response);
    exit;
}

$_DELETE = array();
parse_str(file_get_contents('php://input'), $_DELETE);


$token = $_SERVER['HTTP_TOKEN'] ?? NULL;
$id = $_DELETE['id'] ?? NULL;

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

/* * * prepare the SQL statement ** */
$sql = "DELETE from users WHERE id = :id";
$stmt = $db->prepare($sql);

/* * * bind the paramaters ** */
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
    $response['error']['msg'] = "No user found";
    $response['response'] = NULL;

    http_response_code(404); // Need to confirm about the http response code, this code could be 204 or 202
    http_response_code();
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}