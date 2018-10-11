<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* * * close the database connection ** */
try {
    $db = null;
} catch (PDOException $e) {
    echo $e->getMessage();
}