<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/* * Open the db connection* */
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

if ($method !== "GET") {
    $response['state'] = "error";
    $response['error']['code'] = 16;
    $response['error']['msg'] = "Only GET method is allowed";
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

$id = $_GET['id'] ?? NULL;

if ($id != NULL) {
    if (!filter_var($id, FILTER_VALIDATE_INT)) {
        $response['state'] = "error";
        $response['error']['code'] = 14;
        $response['error']['msg'] = "please enter valid id for a user";
        $response['response'] = NULL;

        http_response_code(400);
        http_response_code();
        header('Content-Type: application/json');

        echo json_encode($response);
        exit;
    }
}


/* * * prepare the SQL statement ** */
$sql = "SELECT full_name, email FROM users";
$stmt = $db->prepare($sql);

if ($id != NULL) {
    $sql .= " WHERE id=:id";
    $stmt = $db->prepare($sql);
    /*     * * bind the paramaters ** */
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
}


/* * * execute the prepared statement ** */
$stmt->execute();

$totalRows = $stmt->rowCount();

/* * * fetch the results ** */
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
//var_dump($result);

/** close the db connection* */
require_once '../config/dbclose.php';

if ($totalRows == 0) {
    $response['state'] = "fail";
    $response['error']['code'] = 15;
    $response['error']['msg'] = "No user found";
    $response['response'] = NULL;

    http_response_code(404);
    http_response_code();
    header('Content-Type: application/json');

    echo json_encode($response);
    exit;
} else {
    $response['state'] = "success";
    $response['error']['code'] = NULL;
    $response['error']['msg'] = NULL;
    $response['response'] = $result;

    http_response_code(200);
    http_response_code();
    header('Cache-Control: max-age=600');//600 seconds, its not good fit here, 
                                         //just used it for the question no 5 of this task. 
    header('Content-Type: application/json');

    echo json_encode($response);
    exit;
}