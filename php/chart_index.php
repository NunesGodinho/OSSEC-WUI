<?php

/*
 * Copyright (c) 2019 António 'Tó' Godinho <to@isec.pt>.
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 * 
 * TODO: Clean UP unused code
 * 
 */

require "./config.php";

// FILTER BEGIN
require './top.php';


## filter criteria 'level'
if (isset($_GET['level']) && preg_match("/^[0-9]+$/", $_GET['level'])) {
    $inputlevel = $_GET['level'];
} else {
    $inputlevel = $glb_level;
}
$filterlevel = "";
$query = "SELECT distinct(level) FROM signature ORDER BY level";
$stmt = $pdo->prepare($query);
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $selected = "";
    if ($row['level'] == $inputlevel) {
        $selected = " SELECTED";
    }
    $filterlevel .= "<option value='" . $row['level'] . "'" . $selected . ">" . $row['level'] . " +</option>";
}

## filter from
if (isset($_GET['hours']) && preg_match("/^[0-9]+$/", $_GET['hours'])) {
    $inputhours = $_GET['hours'];
} else {
    $inputhours = $glb_hours;
}

## filter category
if (isset($_GET['category']) && preg_match("/^[0-9]+$/", $_GET['category'])) {
    $inputcategory = $_GET['category'];
    $wherecategory = " AND category.cat_id=" . $inputcategory . " ";
} else {
    $inputcategory = "";
    $wherecategory = " ";
}
$query = "SELECT *
	FROM category
	ORDER BY cat_name";
$filtercategory = "";
$stmt = $pdo->prepare($query);
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $selected = "";
    if ($row['cat_id'] == $inputcategory) {
        $selected = " SELECTED";
    }
    $filtercategory .= "<option value='" . $row['cat_id'] . "'" . $selected . ">" . $row['cat_name'] . "</option>";
}


## filter
$radiosource = "";
$radiopath = "";
$radiolevel = "";
$radiorule_id = "";
if (isset($_GET['field']) && $_GET['field'] == 'path') {
    $radiopath = "checked";
} elseif (isset($_GET['field']) && $_GET['field'] == 'level') {
    $radiolevel = "checked";
} elseif (isset($_GET['field']) && $_GET['field'] == 'rule_id') {
    $radiorule_id = "checked";
} elseif (isset($_GET['field']) && $_GET['field'] == 'source') {
    $radiosource = "checked";
} else {
    if ($glb_graphbreakdown == "source") {
        $radiosource = "checked";
    } elseif ($glb_graphbreakdown == "path") {
        $radiopath = "checked";
    } elseif ($glb_graphbreakdown == "level") {
        $radiolevel = "checked";
    } elseif ($glb_graphbreakdown == "rule_id") {
        $radiorule_id = "checked";
    } else {
        # default source
        $radiosource = "checked";
    }
}

// FILTER END


if ($glb_debug == 1) {
    $starttime_indexchart = microtime();
    $startarray_indexchart = explode(" ", $starttime_indexchart);
    $starttime_indexchart = $startarray_indexchart[1] + $startarray_indexchart[0];
}

$mainstring = "";
$keyprepend = "";
$notrepresented = array();

# counting in hours/days may get slow on larger databases, so grouping is done in blocks of 10^x seconds
if ($inputhours < 4) {
    $substrsize = 8;
    $zeros = "00";
} elseif ($inputhours < 48) {
    $substrsize = 7;
    $zeros = "000";
} else {
    $substrsize = 6;
    $zeros = "0000";
}

# to make the graph plot position in the middle of the time field..
$halfperiod = intval("1" . $zeros) / 2;

# To filter on 'Category' (SSHD) extra table needs adding, but they slow down the query for other things, so lets only put them into the SQL if needed....
if (strlen($wherecategory) > 1) {
    $wherecategory_tables = ", signature_category_mapping, category";
    $wherecategory_and = "and alert.rule_id=signature_category_mapping.rule_id
        and signature_category_mapping.cat_id=category.cat_id";
} else {
    $wherecategory_tables = "";
    $wherecategory_and = "";
}

# The graph data 'series' can be broken down in several ways
# graphheightmultiplier is just a tweak as some fields are generally longer than others.
if ((isset($_GET['field']) && $_GET['field'] == 'path') || (!isset($_GET['field']) && $glb_graphbreakdown == "path")) {

    $graphheightmultiplier = 5;
    $keyprepend = "";
    $querychart = "select (concat(substring(alert.timestamp, 1, $substrsize), '$zeros')+" . $halfperiod . ") as res_time, count(alert.id) as res_cnt, SUBSTRING_INDEX(location.name, '->', -1) as res_field
		from alert, location, signature " . $wherecategory_tables . "
		where signature.level>=$inputlevel
		and alert.location_id=location.id
		and alert.rule_id=signature.rule_id
		" . $wherecategory_and . "
		and alert.timestamp>" . (time() - ($inputhours * 3600)) . "
		" . $wherecategory . " 
		" . $glb_notrepresentedwhitelist_sql . "
		group by substring(alert.timestamp, 1, $substrsize), SUBSTRING_INDEX(location.name, '->', -1)
		order by substring(alert.timestamp, 1, $substrsize), SUBSTRING_INDEX(location.name, '->', -1)";
} elseif ((isset($_GET['field']) && $_GET['field'] == 'level') || (!isset($_GET['field']) && $glb_graphbreakdown == "level")) {
    $graphheightmultiplier = 2;
    $keyprepend = "Lvl: ";
    $querychart = "select (concat(substring(alert.timestamp, 1, $substrsize), '$zeros')+" . $halfperiod . ") as res_time, count(alert.id) as res_cnt, signature.level as res_field
		from alert, location, signature " . $wherecategory_tables . "
		where signature.level>=$inputlevel
		and alert.location_id=location.id
		and alert.rule_id=signature.rule_id
		" . $wherecategory_and . "
		and alert.timestamp>" . (time() - ($inputhours * 3600)) . "
		" . $wherecategory . " 
		" . $glb_notrepresentedwhitelist_sql . "
		group by substring(alert.timestamp, 1, $substrsize), signature.level
		order by substring(alert.timestamp, 1, $substrsize), signature.level";
} elseif ((isset($_GET['field']) && $_GET['field'] == 'rule_id') || (!isset($_GET['field']) && $glb_graphbreakdown == "rule_id")) {
    $graphheightmultiplier = 8;
    $keyprepend = "";
    $querychart = "select (concat(substring(alert.timestamp, 1, $substrsize), '$zeros')+" . $halfperiod . ") as res_time, count(alert.id) as res_cnt, CONCAT(alert.rule_id, ' ', signature.description) as res_field
		from alert, location, signature " . $wherecategory_tables . "
		where signature.level>=$inputlevel
		and alert.location_id=location.id
		and alert.rule_id=signature.rule_id
		" . $wherecategory_and . "
		and alert.timestamp>" . (time() - ($inputhours * 3600)) . "
		" . $wherecategory . " 
		" . $glb_notrepresentedwhitelist_sql . "
		group by substring(alert.timestamp, 1, $substrsize), alert.rule_id
		order by substring(alert.timestamp, 1, $substrsize), alert.rule_id";
} else {
    # Default is source

    $graphheightmultiplier = 1;
    $keyprepend = "";
    $querychart = "select (concat(substring(alert.timestamp, 1, $substrsize), '$zeros')+" . $halfperiod . ") as res_time, count(alert.id) as res_cnt, SUBSTRING_INDEX(SUBSTRING_INDEX(location.name, ' ', 1), '->', 1) as res_field
		from alert, location, signature " . $wherecategory_tables . "
		where signature.level>=$inputlevel
		and alert.location_id=location.id
		and alert.rule_id=signature.rule_id
		" . $wherecategory_and . "
		and alert.timestamp>" . (time() - ($inputhours * 3600)) . "
		" . $wherecategory . " 
		" . $glb_notrepresentedwhitelist_sql . "
		group by substring(alert.timestamp, 1, $substrsize), SUBSTRING_INDEX(location.name, ' ', 1)
		order by substring(alert.timestamp, 1, $substrsize), SUBSTRING_INDEX(location.name, ' ', 1)";
}

$stmt = $pdo->prepare($querychart);
$stmt->execute();

$tmpdate = "";
$timegrouping = array();
$arraylocations = array();
$arraylocationsunique = array();

$mainstring = "var chartData = [
	";

$first = 0;
$datafound = 0;

## Informal note, I hate this section of code, it will be rewritten.
while ($rowchart = $stmt->fetch()) {

    $datafound = 1;

    # We have data, so empty the var on this load
    $glb_nodatastring = "";

    # XXX Compile a list of all hosts, maybe a better way to do this than have an array the size of the alert table
    $fieldname = substr(preg_replace($glb_hostnamereplace, "", $rowchart['res_field']), 0, 35);
    if (strlen($fieldname) == 35) {
        $fieldname .= "...";
    }

    array_push($arraylocations, $fieldname);


    # for the first run, this needs setting
    if ($first == 0) {
        $first = 1;
        $tmpdate = intval($rowchart['res_time']);
    }

    # This alert is a new time 'group'...
    if ($tmpdate != $rowchart['res_time'] && $rowchart['res_time'] > 1) {
        # ...so what we have compiled needs to go to 'mainstring' (remember to use tmpdate, not the latest row time)
        $mainstring .= '		{"date": "' . date("Y", $tmpdate) . '-' . date("m", $tmpdate) . '-' . date("j", $tmpdate) . ', ' . date("G", $tmpdate) . ':' . date("i", $tmpdate) . '",';

        foreach ($timegrouping as $key => $val) {
            #append this location to array
            $mainstring .= "'" . $key . "': " . $val . ", ";
        }

        $mainstring = substr($mainstring, 0, -2);
        $mainstring .= "},
	";

        # clear the array we have used to collect counts for a specific time 'group'
        unset($timegrouping);

        # reset the working time 'group' so the next if will be fired and we start collecting for the next time 'group'
        $tmpdate = $rowchart['res_time'];
    }

    # Oh look, this alert matches the time 'group' we are collecting for.
    if ($rowchart['res_time'] == $tmpdate) {
        $timegrouping[$fieldname] = $rowchart['res_cnt'];
    }
}


# We have to run this cycle one more time to process the last row

if ($tmpdate > 1) {
    $mainstring .= '		{"date": "' . date("Y", $tmpdate) . '-' . date("m", $tmpdate) . '-' . date("j", $tmpdate) . ', ' . date("G", $tmpdate) . ':' . date("i", $tmpdate) . '",';

    foreach ($timegrouping as $key => $val) {
        #append this location to array
        $mainstring .= "'" . $key . "': " . $val . ",";
    }

    # the last date point on the graph becomes the last data, so if no data the graph effectively stalls. Adding an empty entry at the end will keep the graph up to date.
    $timedown = time();


    //$mainstring .= '},
    //			{"date": "' . date("Y", $timedown) . '-' . date("m", $timedown) . '-' . date("j", $timedown) . ', ' . date("G", $timedown) . ':' . date("i", $timedown) . '", \'now\':1, ';
    //$mainstring .= '}, ';
    # Clean the variable
    $mainstring = substr($mainstring, 0, -2);
    $mainstring .= "},
	";
}

# dump what we have collected
$mainstring = substr($mainstring, 0, -3);
$mainstring .= "
		];";


$nochartdata = "";
if ($glb_debug == 1) {
    $nochartdata .= "<div style='font-size:24px; color:red;'>Debug</div>";
    $nochartdata .= $querychart;

    $endtime_indexchart = microtime();
    $endarray_indexchart = explode(" ", $endtime_indexchart);
    $endtime_indexchart = $endarray_indexchart[1] + $endarray_indexchart[0];
    $totaltime_indexchart = $endtime_indexchart - $starttime_indexchart;
    $nochartdata .= "<div>Took " . round($totaltime_indexchart, 1) . " seconds</div>";
} elseif ($datafound == 0) {

    //echo $mainstring;
    # See if there was data, if not then drop some test output to the main chartdiv, just for happiness
    # 1 mysql module isntalled?
    # 2 mysql connectable?
    # 3 database look like it has right schema?
    # 4 any data in there?

    $nochartdata = "";
    $problem = 0;

    if (fextension_loaded('pdo_mysql')) {
        $sqlmodule = "yes";
    } else {
        $problem = 1;
        $sqlmodule = "no!<br/>";
        $sqlmodule .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fix - https://www.google.pt/search?q=pdo_mysql";
    }

    try {
        $pdo = new PDO('mysql:host=' . DB_HOST_O . ';dbname=' . DB_NAME_O . ';charset=utf8', DB_USER_O, DB_PASSWORD_O);
        $mysqlconnect = "yes";

        $sql = 'SELECT 1 from agent';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $sql = 'SELECT 1 from alert';
        $stmt2 = $pdo->prepare($sql);
        $stmt2->execute();

        $sql = 'SELECT 1 from category';
        $stmt3 = $pdo->prepare($sql);
        $stmt3->execute();

        $sql = 'SELECT 1 from data';
        $stmt4 = $pdo->prepare($sql);
        $stmt4->execute();

        $sql = 'SELECT 1 from location';
        $stmt5 = $pdo->prepare($sql);
        $stmt5->execute();

        $sql = 'SELECT 1 from server';
        $stmt6 = $pdo->prepare($sql);
        $stmt6->execute();

        $sql = 'SELECT 1 from signature';
        $stmt7 = $pdo->prepare($sql);
        $stmt7->execute();

        $sql = 'SELECT 1 from signature_category_mapping';
        $stmt8 = $pdo->prepare($sql);
        $stmt->execute();

        if (checkSchema('agent') && checkSchema('alert') && checkSchema('category') && checkSchema('data') && checkSchema('location') &&
                checkSchema('server') && checkSchema('signature') && checkSchema('signature_category_mapping')) {
            $databaseschema = "yes";
        } else {
            $problem = 1;
            $databaseschema = "no!<br/>";
            $databaseschema .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fix - Import the MySQL schema that comes with OSSEC";
        }

        if (checktable('alert') && checktable('data') && checktable('location') && checktable('signature')) {
            $anydata = "yes";
        } else {
            $problem = 1;
            $anydata = "no!<br/>";
            $anydata .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fix - Ensure agents are logging data.";
        }

        if ($problem == 0) {
            $nochartdata .= "<div>No data found, but everything checks out ok. Try broadening your search parameters.</div>";
        } else {
            $nochartdata = "
		<div style='font-size:24px; color:red;'>No Chart Data Found</div>
		<div style='padding-bottom:10px;'>There is no data available for this query, running diagnostics...</div>
		<div>Test 1 - Can PHP detect MySQL module? - " . $sqlmodule . "</div>
		<div>Test 2 - Can PHP connect to your MySQL? - " . $mysqlconnect . "</div>
		<div>Test 3 - Does your database have correct schema? - " . $databaseschema . "</div>
		<div>Test 4 - Is there any data in your database? - " . $anydata . "</div>";
        }
    } catch (PDOException $e) {
        $problem = 1;
        $mysqlconnect = "no!<br/>";
        $mysqlconnect .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fix - ";
    }
}

function checktable($table) {
    $query = "SELECT max(id) as cnt from " . $table . ";";
    $query = "SELECT 1 from " . $table . ";";
    try {
        $pdo = new PDO('mysql:host=' . DB_HOST_O . ';dbname=' . DB_NAME_O . ';charset=utf8', DB_USER_O, DB_PASSWORD_O);
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return 1;
        }
        return 0;
    } catch (PDOException $e) {
        return 0;
    }
    return 0;
}

function checkSchema($table) {
    $query = "SELECT 1 from " . $table . ";";
    try {
        $pdo = new PDO('mysql:host=' . DB_HOST_O . ';dbname=' . DB_NAME_O . ';charset=utf8', DB_USER_O, DB_PASSWORD_O);
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return 1;
        }
        return 0;
    } catch (PDOException $e) {
        return 0;
    }
    return 0;
}

$arraylocationsunique = array_unique($arraylocations);
asort($arraylocationsunique);

## Right now define each series of data with a name and settings
$graphcount = 0;
$series = "//SERIES";
if ($datafound == 1) {
    foreach ($arraylocationsunique as $i => $location) {

        if (isset($_GET['field']) && $_GET['field'] == 'level') {
            # Get a colour based on the level
            $linecolour = "graph" . $i . ".lineColor = \"" . $levelcolours["level" . $location] . "\";";
        } elseif (isset($_GET['field']) && $_GET['field'] == 'source') {
            if (isset($groupcolour[$devicegroup[$location]])) {
                if ($groupcolour[$devicegroup[$location]] <> '') {
                    # Get a colour for specific servers
                    $linecolour = "graph" . $i . ".lineColor = \"" . $groupcolour[$devicegroup[$location]] . "\";";
                } else {
                    # Get a colour for a server where you didn't specify one
                    $linecolour = "graph" . $i . ".lineColor = \"" . $randomcolour[array_rand($randomcolour)] . "\";";
                }
            }
        } else {
            # Dont specify, let amcharts choose
            $linecolour = "";
        }

        $graphcount++;
        $series .= '	// Series ' . $i . '
			var series' . $i . ' = chart.series.push(new am4charts.LineSeries());
                        //var valueAxis' . $i . ' = chart.yAxes.push(new am4charts.ValueAxis());
                        series' . $i . '.dataFields.valueY = "' . $keyprepend . $location . '";
                        series' . $i . '.dataFields.dateX = "date";
                        series' . $i . '.name = "' . $keyprepend . $location . '";
                        series' . $i . '.strokeWidth = 2;
                        series' . $i . '.tensionX = 0.8; //smoothen between 0 & 1
                        series' . $i . '.yAxis = valueAxis;
                        series' . $i . '.bullets.push(new am4charts.CircleBullet());
                        //series' . $i . '.minBulletDistance = 25;
                        series' . $i . '.tooltipText = "{name}: [bold]{valueY}[/]";
                        series' . $i . '.tooltip.background.fillOpacity = 0.6;
                        //valueAxis.renderer.line.stroke = series' . $i . '.stroke;
                        //valueAxis.renderer.labels.template.fill = series' . $i . '.stroke;
	';
        /*
          $series .= '	// valueAxis ' . $i . '
          valueAxis' . $i . '.renderer.line.strokeOpacity = 1;
          valueAxis' . $i . '.renderer.line.strokeWidth = 2;
          valueAxis' . $i . '.renderer.line.stroke = series' . $i . '.stroke;
          valueAxis' . $i . '.renderer.labels.template.fill = series' . $i . '.stroke;
          valueAxis' . $i . '.renderer.opposite = false;
          valueAxis' . $i . '.renderer.grid.template.disabled = true;
          valueAxis' . $i . '.cursorTooltipEnabled = false;
          ';
         */
        $notrepresented[$location] = 1;
    }
}
