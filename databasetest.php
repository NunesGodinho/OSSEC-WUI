<?php
/*
 * Copyright (c) 2019 António 'Tó' Godinho <to@isec.pt>.
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */

# see if database is populated correctly, if not then JS alert to user.
require "./db_ossec.php";

$query = "SELECT count(id) as res_count FROM alert";
$stmt = $pdo->prepare($query);
$stmt->execute();
if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch();
    if (!$row['res_count'] > 0) {
        echo "alert(\"Connected to database ok, but no alerts found. Ensure OSSEC is logging to your database.\");";
    }
} else {
    echo "alert(\"Problems checking database for information\");";
}

$query = "SELECT count(id) as res_count FROM data";
$stmt = $pdo->prepare($query);
$stmt->execute();
if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch();
    if (!$row['res_count'] > 0) {
        echo "alert(\"Connected to database ok, but no data found. Ensure OSSEC is logging to your database.\");";
    }
} else {
    echo "alert(\"Problems checking database for information\");";
}

$query = "SELECT count(id) as res_count FROM location";
$stmt = $pdo->prepare($query);
$stmt->execute();
if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch();
    if (!$row['res_count'] > 0) {
        echo "alert(\"Connected to database ok, but no data found. Ensure OSSEC is logging to your database.\");";
    }
} else {
    echo "alert(\"Problems checking database for information\");";
}
