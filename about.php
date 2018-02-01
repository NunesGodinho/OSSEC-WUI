<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
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
    <link href="./css/bootstrap.min.css" rel="stylesheet">
    <link href="./css/style.css" rel="stylesheet" type="text/css"/>
    <link href="./css/sticky-footer.css" rel="stylesheet">
</head>
<body>
<?php includeme("./header.php"); ?>

<div class='clr'></div>

<div class='top10header'>About</div>
<div class="introbody">= Information about AnaLogi =<br><br>

    'Analytical Log Interface' built to sit on top of OSSEC (built on OSSEC 2.6)<br><br>

    Written for inhouse analysis work, released under GPL to give something back.<br><br>

    AnaLogi was built for OSSEC 2.6 and requires 0 modifications to OSSEC or the
    database schema that ships with OSSEC. AnaLogi requires a Webserver sporting
    PHP and MySQL.

    Available from:
    https://github.com/downloads/ECSC/analogi/
</div>

<div class='top10header'>To say Thanks</div>
<div class="introbody">AnaLogi has no real tracking of how many people use it (no 1px images in the code etc).<br><br>If
    you would like to say thanks and show me that this project was worth releasing please click the following
    link.<br><a href='http://www.ecsc.co.uk/analogi.html'>AnaLogi</a> at ECSC (I check the logs time to time for hits)
</div>

<div class='top10header'>FAQ</div>
<div class="introbody">
    All tweakable parts of AnaLogi are stored in config.php
    <br><br>
    Tweakable bits of the interface are displayed as <span class='tw'>such</span>
    <br><br>
</div>

<div class='top10header'>Latest Version</div>
<div class="introbody">The latest Version can be found <a href='https://github.com/ECSC/analogi/downloads'>here</a>
</div>

<div class='top10header'>Wiki</div>
<div class="introbody">Click <a href='https://github.com/ECSC/analogi/wiki'>here</a> (wip)</div>

<div class='top10header'>Links</div>
<div class="introbody">
    In no particular order

    <li>http://www.ossec.net
    <li>http://www.amazon.com/OSSEC-Host-Based-Intrusion-Detection-Guide/dp/159749240X
    <li>https://groups.google.com/forum/?fromgroups#!forum/ossec-list
    <li>http://ddpbsd.blogspot.co.uk/
    <li>http://dcid.me/blog
    <li>http://www.immutablesecurity.com/index.php/tag/ossec/


</div>


<div class='clr'></div>
<?php
include 'footer.php';
?>
