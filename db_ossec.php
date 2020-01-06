<?php
/*
 * Copyright (c) 2019 António 'Tó' Godinho <to@isec.pt>.
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */

if(!defined('DB_USER_O')) {
    define('DB_USER_O', 'root');
}
if(!defined('DB_PASSWORD_O')) {
    define('DB_PASSWORD_O', 'Passw0rd');
}
if(!defined('DB_HOST_O')) {
    define('DB_HOST_O', 'localhost');
}
if(!defined('DB_NAME_O')) {
    define('DB_NAME_O', 'ossecdb');
}

try {
    $pdo = new PDO('mysql:host=' . DB_HOST_O . ';dbname=' . DB_NAME_O . ';charset=utf8', DB_USER_O, DB_PASSWORD_O);
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
    exit();
}