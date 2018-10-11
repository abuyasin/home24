<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function alphaNumeric($length) {
    $chars = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    $clen = strlen($chars) - 1;
    $id = '';

    for ($i = 0; $i < $length; $i++) {
        $id .= $chars[mt_rand(0, $clen)];
    }
    return ($id);
}

function checkPassword($pwd) {
    if (strlen($pwd) < 8) {
        return FALSE; // must be greater or equal 8 in length
    }

    if (!preg_match("#[0-9]+#", $pwd)) {
        return FALSE; // mustinclude one number
    }

    if (!preg_match("#[a-zA-Z]+#", $pwd)) {
        return FALSE; // must include one letter
    }

    return TRUE;
}

function checkTokenValidity($token, $db) {
    /*     * Open the db connection* */
//    require_once '../config/db.php';

    /*     * * prepare the SQL statement ** */
    $sql = "SELECT * FROM users WHERE token = :token";
    $stmt = $db->prepare($sql);

    /*     * * bind the paramaters ** */
    $stmt->bindParam(':token', $token, PDO::PARAM_STR, 50);

    /*     * * execute the prepared statement ** */
    $stmt->execute();

    /*     * * fetch the results ** */
    $result = $stmt->fetch(PDO::FETCH_ASSOC);


    /** close the db connection* */
    require_once '../config/dbclose.php';


    $userId = $result['id'] ?? NULL;
    if ($userId == NULL) {
        return FALSE;
    }

    $time1 = strtotime($result['token_expiry']);
    $time2 = strtotime(date("Y-m-d H:i:s"));
    $diff = $time1 - $time2;

    if ($diff > 0) {
        return TRUE;
    } else {
        return FALSE;      
    }
    
    return FALSE;
}
