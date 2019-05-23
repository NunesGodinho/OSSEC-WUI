<?php
require './top.php';
require "./config.php";

###  Get the criteria from the URL, these are used to populate the graph, and to populate the filter options further down


$where = "";

# input<var> = the raw GET
# filter<var> = for repopulating the filter toolbar
# where = the cumulative sql command
## filter criteria 'levelmin' and 'levelmax' 
if (isset($_GET['levelmin']) && preg_match("/^[0-9]+$/", $_GET['levelmin'])) {
    $inputlevelmin = $_GET['levelmin'];
    $where .= "AND signature.level>=" . $inputlevelmin . " ";
} else {
    $inputlevelmin = "";
    $where .= "";
}
if (isset($_GET['levelmax']) && preg_match("/^[0-9]+$/", $_GET['levelmax'])) {
    $inputlevelmax = $_GET['levelmax'];
    $where .= "AND signature.level<=" . $inputlevelmax . " ";
} else {
    $inputlevelmax = "";
    $where .= "";
}
$query = "SELECT distinct(level) FROM signature ORDER BY level";
$stmt = $pdo->prepare($query);
$stmt->execute();
$filterlevelmin = "";
$filterlevelmax = "";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $selectedmin = "";
    $selectedmax = "";
    if ($row['level'] == $inputlevelmin) {
        $selectedmin = " SELECTED";
    }
    if ($row['level'] == $inputlevelmax) {
        $selectedmax = " SELECTED";
    }
    $filterlevelmin .= "<option value='" . $row['level'] . "'" . $selectedmin . ">>=" . $row['level'] . "</option>";
    $filterlevelmax .= "<option value='" . $row['level'] . "'" . $selectedmax . "><=" . $row['level'] . "</option>";
}


## filter from
if (isset($_GET['from']) && preg_match("/^[0-9\ ]+$/", $_GET['from'])) {
    $inputfrom = $_GET['from'];
    $filterfrom = $inputfrom;
    $f = explode(" ", $inputfrom);
    $sqlfrom = mktime(substr($f[0], 0, 2), substr($f[0], 2, 4), 0, substr($f[1], 2, 2), substr($f[1], 0, 2), substr($f[1], 4, 2));
    $where .= "AND alert.timestamp>=" . $sqlfrom . " ";
    //echo "58 => " . print_r($inputfrom) . " " .print_r($sqlfrom); // Godinho
} else {
    $sqlfrom = "";
    $inputfrom = "";
    $filterfrom = $inputfrom;
    $where .= "";
}

## filter to
if (isset($_GET['to']) && preg_match("/^[0-9\ ]+$/", $_GET['to'])) {
    $inputto = $_GET['to'];
    $filterto = $inputto;
    $t = explode(" ", $inputto);
    $sqlto = mktime(substr($t[0], 0, 2), substr($t[0], 2, 4), 0, substr($t[1], 2, 2), substr($t[1], 0, 2), substr($t[1], 4, 2));
    $lastgraphplot = $sqlto;
    $where .= "AND alert.timestamp<=" . $sqlto . " ";
    //echo "74 - " . print_r($inputto) . " " .print_r($sqlto); // Godinho
} else {
    $sqlto = "";
    $inputto = "";
    $filterto = $inputto;
    $where .= "";
}


## filter criteria 'source'
if (isset($_GET['source']) && strlen($_GET['source']) > 0) {
    $inputsource = quote_smart($_GET['source']);
    $where .= "AND location.name like '%" . $inputsource . "%' ";
} else {
    $inputsource = "";
    $where .= "";
}
$query = "SELECT distinct(substring_index(substring_index(name, ' ', 1), '->', 1)) as dname FROM location ORDER BY dname";
$filtersource = "";

$stmt = $pdo->prepare($query);
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $selected = "";
    if ($row['dname'] == $inputsource) {
        $selected = " SELECTED";
    }
    $filtersource .= "<option value='" . $row['dname'] . "'" . $selected . ">" . $row['dname'] . "</option>";
}

## filter criteria 'path'
if (isset($_GET['path']) && strlen($_GET['path']) > 0) {
    $inputpath = quote_smart($_GET['path']);
    $where .= "AND location.name like '%" . $inputpath . "%' ";
} else {
    $inputpath = "";
    $where .= "";
}
$query = "SELECT distinct(substring_index(name,'->',-1)) as dname FROM location ORDER BY dname;";
$filterpath = "";
$stmt = $pdo->prepare($query);
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $selected = "";
    if ($row['dname'] == $inputpath) {
        $selected = " SELECTED";
    }
    $filterpath .= "<option value='" . $row['dname'] . "'" . $selected . ">" . $row['dname'] . "</option>";
}


## filter rule_id
if (isset($_GET['rule_id']) && preg_match("/^[0-9,\ ]+$/", $_GET['rule_id'])) {
    $inputrule_id = $_GET['rule_id'];
    $filterule_id = $inputrule_id;

    $inputrule_id_array = preg_split('/,/', $inputrule_id);

    $where .= "AND (1=0 ";
    $noterule_id = "";
    foreach ($inputrule_id_array as $value) {
        if (strlen($value) > 0) {
            $where .= "OR alert.rule_id=" . $value . " ";
        }

        $query = "select signature.description from signature where rule_id=" . $value;

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $noterule_id .= "<span style='font-weight:bold;' >Rule " . $value . "</span>: " . $row['description'] . "<br/>";
    }
    $where .= ")";
} else {
    $inputrule_id = "";
    $filterule_id = $inputrule_id;
    $where .= "";
    $noterule_id = "";
}


### filter input 'datamatch'
# Current opinion is that this does not have to be 'safe' as we trust users who can access this
if (isset($_GET['datamatch']) && strlen($_GET['datamatch']) > 0) {
    $inputdatamatch = $_GET['datamatch'];
    $filterdatamatch = $inputdatamatch;
    $where .= "AND data.full_log like '%" . quote_smart($inputdatamatch) . "%' ";
} else {
    $inputdatamatch = "";
    $filterdatamatch = $inputdatamatch;
}

### filter input 'dataexclude'
# Current opinion is that this does not have to be 'safe' as we trust users who can access this
if (isset($_GET['dataexclude']) && strlen($_GET['dataexclude']) > 0) {
    $inputdataexclude = $_GET['dataexclude'];
    $filterdataexclude = $inputdataexclude;
    $where .= "AND data.full_log not like '%" . quote_smart($inputdataexclude) . "%' ";
} else {
    $inputdataexclude = "";
    $filterdataexclude = $inputdataexclude;
}


### filter input 'datamatch'
if (isset($_GET['ipmatch']) && !empty($_GET['ipmatch']) && preg_match("/^[0-9\.]*$/", $_GET['ipmatch'])) {
    $inputipmatch = $_GET['ipmatch'];
    $filteripmatch = $inputipmatch;
    $where .= "AND inet_ntoa(alert.src_ip) like '" . quote_smart($inputipmatch) . "%' ";
} else {
    $inputipmatch = "";
    $filteripmatch = $inputipmatch;
}

### filter input 'rulematch'
# Current opinion is that this does not have to be 'safe' as we trust users who can access this
if (isset($_GET['rulematch']) && strlen($_GET['rulematch']) > 0) {
    $inputrulematch = $_GET['rulematch'];
    $filterrulematch = $inputrulematch;
    $where .= "AND signature.description like '%" . quote_smart($inputrulematch) . "%' ";
} else {
    $inputrulematch = "";
    $filterrulematch = $inputrulematch;
}


### filter limit
if (isset($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] < 1000) {
    $inputlimit = $_GET['limit'];
} else {
    $inputlimit = $glb_detailtablelimit;
}


### filter alet 'categories'
if (isset($_GET['category']) && preg_match("/^[0-9]+$/", $_GET['category'])) {
    $inputcategory = $_GET['category'];
    $filtercagetory = $inputcategory;
    $where .= " AND category.cat_id=" . $inputcategory . " ";
    $wherecategory_tables = ", signature_category_mapping, category";
    $wherecategory_and = "and alert.rule_id=signature_category_mapping.rule_id
        and signature_category_mapping.cat_id=category.cat_id";
} else {
    $inputcategory = "";
    $wherecategory = " ";
    $wherecategory_tables = "";
    $wherecategory_and = "";
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
        <?php
        include "page_refresh.php";
        ?>
        <link href="./css/bootstrap.css" rel="stylesheet">
        <link href="./css/custom.min.css" rel="stylesheet" type="text/css"/>

        <script src="./js/amcharts4/core.js" type="text/javascript"></script>
        <script src="./js/amcharts4/charts.js" type="text/javascript"></script>
        <script src="./js/amcharts4/themes/animated.js" type="text/javascript"></script>

        <style type="text/css">
            #chartdiv {
                width: 100%;
                height: 500px;
            }
        </style>

        <script type="text/javascript">
            function setCookie(c_name, value, exdays) {
            var exdate = new Date();
            exdate.setDate(exdate.getDate() + exdays);
            var c_value = escape(value) + ((exdays == null) ? "" : "; expires=" + exdate.toUTCString());
            document.cookie = c_name + "=" + c_value;
        }

        function get_cookies_array() {
            var cookies = {};
            if (document.cookie && document.cookie != '') {
                var split = document.cookie.split(';');
                for (var i = 0; i < split.length; i++) {
                    var name_value = split[i].split("=");
                    name_value[0] = name_value[0].replace(/^ /, '');
                    cookies[decodeURIComponent(name_value[0])] = decodeURIComponent(name_value[1]);
                }
            }
            return cookies;
        }

            function databasetest() {
            <!--  If no data, alerts will be created in here  -->
            <?php
            #includeme('./databasetest.php')
            include './databasetest.php'
            ?>
            }

            <?php
            include './php/chart_detail.php';
            echo $mainstring;
            ?>

am4core.ready(function () {

                    var chart;
            var valueAxis;
            // Create chart instance
            chart = am4core.create("chartdiv", am4charts.XYChart);
            am4core.useTheme(am4themes_animated);
            // Increase contrast by taking evey second color
            chart.colors.step = 2;
            // Create Category axes
            var categoryAxis = chart.xAxes.push(new am4charts.DateAxis());
            categoryAxis.dateFormats.setKey("hour", "YYYY-M-dd, H:mm");
            chart.dateFormatter.dateFormat = "YYYY-M-dd, H:mm";
            chart.dateFormatter.inputDateFormat = "YYYY-M-dd, H:mm";
            // Create Value axis series

            valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
            //valueAxis.renderer.opposite = true;
            //valueAxis.width = 0;
            valueAxis.renderer.line.strokeOpacity = 1;
            //valueAxis.cursorTooltipEnabled = false;// TODO
            //create series
            <?php echo $series; ?>

            // Add legend
            chart.legend = new am4charts.Legend();
            /* Create a cursor */
            chart.cursor = new am4charts.XYCursor();
            chart.cursor.xAxis = categoryAxis;
            chart.cursor.fullWidthLineX = true;
            chart.cursor.lineX.strokeWidth = 0;
            chart.cursor.lineX.fill = am4core.color("#000");
            chart.cursor.lineX.fillOpacity = 0.1;
            //	chart.cursor.behavior = "selectX";
            chart.cursor.behavior = "zoomX";
            chart.cursor.lineY.disabled = true;
            chart.exporting.menu = new am4core.ExportMenu();
            chart.exporting.filePrefix= "PT";
            chart.data = chartData;
        }); // end am4core.ready()
        </script>

    </head>
<body onload="databasetest()">
    <?php include './header.php'; ?>
        <div class="container-fluid">
            <div class="row">
                <div id="chartdiv"></div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" style="font-weight: 800">Filters</a>
                        </li>
                    </ul>
                </div>
            </div>

            <form method='GET' action='./detail.php'>
                <div class="row">
                    <div class="col-lg-2">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                RuleID
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-1">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Level Min
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-2">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                From <small>(HHMM DDMMYY)</small>
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-2">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Source
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-2">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Data Match
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-1">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                IP Match
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-1">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-2 form-group">
                        <input type='text' size='6' name='rule_id' value='<?php echo $filterule_id; ?>'
                               class="form-control input-sm"/>
                    </div>
                    <div class="col-lg-1 form-group">
                        <select name='levelmin' class="form-control input-sm">
                            <option value=''>--</option>
                            <?php echo $filterlevelmin; ?>
                        </select>
                    </div>
                    <div class="col-lg-2 form-group">
                        <input type='text' size='11' name='from' value='<?php echo $filterfrom; ?>'
                               class="form-control input-sm"/>
                    </div>
                    <div class="col-lg-2 form-group">
                        <select name='source' class="form-control input-sm">
                            <option value=''>--</option>
                            <?php echo $filtersource; ?>
                        </select>
                    </div>
                    <div class="col-lg-2 vc">
                        <input type='text' size='7' name='datamatch' value='<?php echo $filterdatamatch; ?>'
                               class="form-control input-sm"/>
                    </div>
                    <div class="col-lg-1 vc">
                        <input type='text' size='7' name='ipmatch' value='<?php echo $filteripmatch; ?>'
                               class="form-control input-sm"/>
                    </div>
                    <div class="col-lg-1 vc">
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-2">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Category
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-1">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Level Max
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-2">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                To <small>(HHMM DDMMYY)</small>
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-3">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Path
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-1">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Data Exclude
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-1">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Rule Match
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-1">
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-2 form-group">
                        <select name='category' class="form-control input-sm">
                            <option value=''>--</option>
                            <?php echo $filtercategory; ?>
                        </select>
                    </div>
                    <div class="col-lg-1 form-group">
                        <select name='levelmax' class="form-control input-sm">
                            <option value=''>--</option>
                            <?php echo $filterlevelmax; ?>
                        </select>
                    </div>
                    <div class="col-lg-2 form-group">
                        <input type='text' size='10' name='to' value='<?php echo $filterto; ?>'
                               class="form-control input-sm"/>
                    </div>
                    <div class="col-lg-3 form-group">
                        <select name='path' class="form-control input-sm">
                            <option value=''>--</option>
                            <?php echo $filterpath; ?>
                        </select>
                    </div>
                    <div class="col-lg-1 vc">
                        <input type='text' size='7' name='dataexclude' value='<?php echo $filterdataexclude; ?>'
                               class="form-control input-sm"/>
                    </div>
                    <div class="col-lg-1 vc">
                        <input type='text' size='7' name='rulematch' value='<?php echo $filterrulematch; ?>'
                               class="form-control input-sm"/>
                    </div>
                    <div class="col-lg-1 vc">
                        <input type='submit' value='..go' class="btn btn-warning"/>
                    </div>
                </div>
            </form>
            <br/>
            <div class="row">
                <div class="col-lg-12">
                    <div><?php echo $noterule_id; ?></div>
                </div>
            </div>

            <?php
            # use this to store the main table as I want the 'Common Patterns' to be at the top but it needs processing at same time
            $mainstring = "";

            # Count the queries for the last line of the table.
            $querycounttable = "SELECT count(alert.id) as res_cnt
                            FROM alert, location, signature, data " . $wherecategory_tables . "
                            WHERE 1=1
                            " . $wherecategory_and . "
                            and alert.location_id=location.id
                            and alert.rule_id=signature.rule_id
                            and alert.id=data.id
                            " . $where;
            echo $querycounttable;
            $stmt = $pdo->prepare($querycounttable);
            $stmt->execute();
            $rowcounttable = $stmt->fetch(PDO::FETCH_ASSOC);

            $resultablerows = $rowcounttable['res_cnt'];

            # Fetch the actual rows of data for the table
            $querytable = "SELECT alert.id as id, alert.rule_id as rule, signature.level as lvl, alert.timestamp as timestamp, location.name as loc, data.full_log as data, alert.src_ip as src_ip
                            FROM alert, location, signature, data " . $wherecategory_tables . "
                            WHERE 1=1
                            and alert.location_id=location.id
                            and alert.rule_id=signature.rule_id
                            and alert.id=data.id
                            " . $where . "
                            " . $wherecategory_and . "
                            ORDER BY alert.timestamp DESC
                            LIMIT " . $inputlimit;
            $stmt = $pdo->prepare($querytable);
            $stmt->execute();

            $mainstring .= "<div class='newboxes toggled'><table class='dump sortable' id='sortabletable'  style='width:100%' ><tr>
                            <th>ID</th><th>Rule</th><th>Lvl</th><th>Timestamp</th><th>Location</th><th>IP</th><th>Data</th>
                            </tr>";

            $rowcount = 0;

            # This sets up the ability to highlight keywords below
            $term = preg_replace('/\|+/', '|', trim($glb_autohighlight));
            $words = explode('|', $term);
            $highlighted = array();
            foreach ($words as $word) {
                $highlighted[] = "<span class='highlight'>" . $word . "</span>";
            }

            $mostcommonwords = array();
            $datasummary = array();

            while ($rowtable = $stmt->fetch(PDO::FETCH_ASSOC)) {

                # Dump each line to the table, be careful, this data is fromt the logs and should not be trusted
                if (isset($_GET['datamatch']) && strlen($_GET['datamatch']) > 0) {
                    $tabledata = preg_replace("/(" . $_GET['datamatch'] . ")/i", '<span style="color:red">$1</span>', htmlspecialchars($rowtable['data']));
                } else {
                    $tabledata = htmlspecialchars($rowtable['data']);
                }

                $rowcount++;
                $mainstring .= "<tr>";
                $mainstring .= "<td>" . htmlspecialchars($rowtable['id']) . "</td>";
                $mainstring .= "<td>" . htmlspecialchars($rowtable['rule']) . "</td>";
                $mainstring .= "<td>" . htmlspecialchars($rowtable['lvl']) . "</td>";
                $mainstring .= "<td>" . date($glb_detailtimestamp, $rowtable['timestamp']) . "</td>";
                $mainstring .= "<td>" . htmlspecialchars(preg_replace("/ [0-9\.]*->/", " ", $rowtable['loc'])) . "</td>";

                # See if there is an IP assigned to alert
                $datatableip = long2ip($rowtable['src_ip']);
                if ($datatableip == "0.0.0.0") {
                    $mainstring .= "<td></td>";
                } else {
                    $datatableip = preg_replace("/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/", "<a href='ip_info.php?ip=$1'>$1</a>", $datatableip);
                    $mainstring .= "<td>" . $datatableip . "</td>";
                }

                # Process the full_log data
                $data = $rowtable['data'];
                $data = htmlspecialchars($data);
                $data = preg_replace("/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/", "<a href='ip_info.php?ip=$1'>$1</a>", $data);
                $data = str_replace($words, $highlighted, $data);
                $mainstring .= "<td class='highlighted-text' style='word-wrap:break-word;'>" . $data . "</td>";
                $mainstring .= "</tr>";

                $phraseline = preg_split("/ /", $rowtable['data']);
                foreach ($phraseline as $phrase) {
                    $phrase2 = preg_replace("/=[a-zA-Z0-9\%\,\~\_\.\-]+&/", "=&", $phrase);
                    # I have this hard coded as I think it will run faster than a glb_config array foreach loop
                    if (
                            preg_match("/^http/", $phrase2) # match web sites
                            || preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/", $phrase2) # match IP addresses
                            || preg_match("/\w+\.\w+\.\w+/", $phrase2) # match... file paths?
                            || preg_match("/^[A-Z_]+\/[0-9]+$/", $phrase2) # match HTTP return codes and proxy cache peer
                    ) {
                        if (!array_key_exists($phrase2, $datasummary)) {
                            $datasummary[$phrase2] = 0;
                        } else {
                            $datasummary[$phrase2] ++;
                        }
                    }
                }
            }
            $mainstring .= "</table></div>";

            # Dump cool phrases we found!
            arsort($datasummary);
            ?>
            <div class="row toggle" id='commonpatterns'>
                <div class="col-lg-12">
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" style="font-weight: 800">Common Patterns (Matching our Regex)</a>
                        </li>
                    </ul>
                </div>
            </div>

            <?php
            //echo "<div id='commonpatterns'><div class='top10header fleft toggle'></div>
            //<div class='clr' style='border-top:20px;'>&nbsp;</div>";

            echo "<div class='newboxes toggled' id='commonpatterns' style='display: none;'>
                            <table class='dump sortable' id='sortabletable'  style='width:100%' ><tr>
                            <th>Count</th><th>Phrase</th>
                            </tr>";
            $i = 0;
            foreach ($datasummary as $key => $value) {
                if ($i < $glb_commonpatternscount) {
                    echo "<tr><td>" . number_format($value) . "</td><td><a class='numpty'>" . $key . "</a></td></tr>";
                }
                $i++;
            }
            echo "</table><div class='clr' style='border-top:20px;'>&nbsp;</div></div>";


            # Title
            # Title
            ?>
            <br/>
            <div class="row toggle" id='data'>
                <div class="col-lg-12">
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" style="font-weight: 800">Data</a>
                        </li>
                    </ul>
                </div>
            </div>
            <?php
            //echo "<div id='data'><div class='top10header toggle'>Data</div>";
            # This final line has to be a separate table for the 'sortable' to work
            echo "<table class='dump sortable' style='width:100%' >";
            if ($rowcount == 0) {
                echo "<tr><td><span style='color:red'>No data found, is your database populated?</span>.</td><td></td><td></td><td></td><td></td><td></td></tr>";
            } elseif ($rowcount == $glb_detailtablelimit) {
                echo "<tr><td colspan='6'><span style='color:red'>Search limited</span> to latest <span class='tw'>" . number_format($rowcount) . "</span> (of " . number_format($resultablerows) . ") results as per your global config. Please refine your search or increase the limit.</td></tr>";
            } else {
                echo "<tr><td colspan='6'>" . number_format($rowcount) . " records shown.</td></tr>";
            }

            $detail2csv_get = preg_replace("/.*php\?/", "", $_SERVER["REQUEST_URI"]);
            echo "<tr><td><a href='./detail2csv.php?" . $detail2csv_get . "'>Download all " . number_format($resultablerows) . " results as CSV</a></td></tr>";
            echo "</table>";

            # Now print main data table
            echo "
                            $mainstring
                    ";

            # Show the SQL?
            if ($glb_detailsql == 1) {
                #	For niceness show the SQL queries, just incase you want to dig deeper your self
                echo "<div class='clr' style='padding-bottom:20px;'></div>
                                    <div class='fleft top10header'>SQL (Chart)</div>
                                    <div class='fleft tiny' style=''>" . htmlspecialchars($querychart) . "</div>";

                echo "<div class='clr' style='padding-bottom:20px;'></div>
                                    <div class='fleft top10header'>SQL (Table)</div>
                                    <div class='fleft tiny' style=''>" . htmlspecialchars($querytable) . "</div>";
            }
            ?>
        </div>
    <div class='row'></div>

    <?php
    include './footer.php';
    ?>
    <script language="JavaScript">
    <?php
    //echo $graphheight;
    ?>
    </script>
</body>
</html>