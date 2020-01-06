<?php
/*
 * Copyright (c) 2019 António 'Tó' Godinho <to@isec.pt>.
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */
require './top.php';
?>
<!DOCTYPE html>
<html lang="pt">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <meta name="description" content="">
        <meta name="author" content="">
        <title>OSSEC WUI</title>
        <link href="./css/bootstrap.css" rel="stylesheet">
        <link href="./css/custom.min.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
        <?php includeme("./header.php"); ?>
        <div class="container-fluid">
            <div class="card border-primary mb-3">
                <div class="card-header">About</div>
                <div class="card-body">
                    <h4 class="card-title">About OSSEC-WUI</h4>
                    <p class="card-text">After the update of OSSEC to 2.9, AnaLogi stop work due to database schema changes.</p>
                    <p class="card-text">There was a necessity to upgrade the existing software to work with the newer versions.</p>
                    <p class="card-text">Many upgrade have been made the last 2 years, starting from using Bootstrap 3 and Amcharts 3 too.</p>
                    <p class="card-text">In 2019 I've upgraded to Bootstrap 4 and Amcharts 4. There are still things to be made...</p>
                </div>
            </div>
            <div class="card border-primary mb-3">
                <div class="card-header">Thanks</div>
                <div class="card-body">
                    <h4 class="card-title">Thanks</h4>
                    <p class="card-text">Thanks to the people that originally developed AnaLogi that was the base to this project.</p>
                    <p class="card-text">Thanks to OSSEC that's the reason for all this.</p>
                </div>
            </div>
        </div>
        <?php
        include './footer.php';
        ?>
    </body>
</html>
