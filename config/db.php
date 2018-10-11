<?php
/*** mysql hostname ***/
$hostname = 'localhost';

/*** mysql username ***/
$username = 'root';

/*** mysql password ***/
$password = '';

/*** mysql dbname ***/
$dbname = 'library';

try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
//    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
}
catch(PDOException $e)
{
    echo $e->getMessage();
}
