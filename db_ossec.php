<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */

define('DB_USER_O', 'ossec_u');
define('DB_PASSWORD_O', 'KUDHRvuDXQLLN6uh');
define('DB_HOST_O', 'localhost');
define('DB_NAME_O', 'ossec');

try {
    $pdo = new PDO('mysql:host=' . DB_HOST_O . ';dbname=' . DB_NAME_O . ';charset=utf8', DB_USER_O, DB_PASSWORD_O);
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
    exit();
}