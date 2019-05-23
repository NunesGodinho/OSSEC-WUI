<?php
/*
 * Copyright (c) 2019 António 'Tó' Godinho <to@isec.pt>.
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */
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
        function databasetest() {
        <?php
            include './databasetest.php'
        ?>
        }

        <?php
        include './php/chart_index.php';
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
            // var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
            var categoryAxis = chart.xAxes.push(new am4charts.DateAxis());
            categoryAxis.dateFormats.setKey("hour", "YYYY-M-dd, H:mm");
            chart.dateFormatter.dateFormat = "YYYY-M-dd, H:mm";
            chart.dateFormatter.inputDateFormat = "YYYY-M-dd, H:mm";
            // Create Value axis series

            valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
            //valueAxis.renderer.opposite = true;
            //valueAxis.renderer.opposite = false;
            //valueAxis.renderer.grid.template.disabled = true;
            //valueAxis.width = 0;
            //create series
            <?php echo $series; ?>

            //valueAxis settings
            valueAxis.renderer.line.strokeOpacity = 1;
            //valueAxis.renderer.line.strokeWidth = 2;
            //valueAxis.renderer.line.stroke = series.stroke;
            //valueAxis.renderer.labels.template.fill = series.stroke;
            //valueAxis.cursorTooltipEnabled = false;
            //
            // TODO
            //valueAxis.min = 99.55;
            //valueAxis.max = 99.95;

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
        <form method='GET' action='./index.php' class="form-inline">
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

                <div class="row">
                    <div class="col-lg-1">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Level
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-1">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Hours
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-4">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Graph Breakdown
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-3">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Category
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-1 vc">
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-1 form-group">
                        <select name='level' class="form-control">
                            <option value=''>--</option>
                            <?php echo $filterlevel; ?>
                        </select>
                    </div>
                    <div class="col-lg-1 form-group">
                        <input type='text' class="form-control col-lg-12" name='hours' value='<?php echo $inputhours; ?>'/>
                    </div>
                    <div class="col-lg-4 form-check-inline">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="field" id="inlineRadio1" value='source' <?php echo $radiosource; ?> />
                            <label class="form-check-label">Source</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="field" id="inlineRadio2" value='path' <?php echo $radiopath; ?> />
                            <label class="form-check-label">Path</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="field" id="inlineRadio3" value='level' <?php echo $radiolevel; ?> />
                            <label class="form-check-label">Level</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="field" id="inlineRadio3" value='rule_id' <?php echo $radiorule_id; ?> />
                            <label class="form-check-label">Rule</label>
                        </div>
                    </div>
                    <div class="col-lg-3 form-group">
                        <select name='category' class="form-control">
                            <option value=''>--</option>
                            <?php echo $filtercategory; ?>
                        </select>
                    </div>
                    <div class="col-lg-1 form-group">
                        <input type='submit' value='Filter' class="btn btn-danger"/>
                    </div>
                </div>



                <hr/>

                <div class="row">
                    <div class="col-lg-6 col-md-6 col-xs-6">
                        <?php include './php/topid.php'; ?>
                    </div>
                    <div class="col-lg-6 col-md-6 col-xs-6">
                        <?php include './php/toplocation.php'; ?>
                    </div>
                </div>

                <div class='row'></div>
        </form>
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
