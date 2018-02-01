<nav class="navbar navbar-inverse navbar-expand-lg navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <a href="./index.php?" class="navbar-brand text-primary">OSSEC - WUI</a>
        </div>
        <ul class="nav navbar-nav navbar-right">
            <?php
            if (isset($glb_ossecdb) && count($glb_ossecdb) > 1) {
                ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                       aria-expanded="false">Dropdown <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <form action='./index.php'>
                            <select name='glb_ossecdb'
                                    onchange='document.cookie = \"ossecdbjs=\"+glb_ossecdb.options[selectedIndex].value ; location.reload(true)'>
                                <?php
                                foreach ($glb_ossecdb as $name => $file) {
                                    if ($_COOKIE['ossecdbjs'] == $name) {
                                        $glb_ossecdb_selected = " SELECTED ";
                                    } else {
                                        $glb_ossecdb_selected = "";
                                    }
                                    $glb_ossecdb_option .= "<option value='" . $name . "' " . $glb_ossecdb_selected . " >" . $name . " (" . DB_NAME_O . ", " . DB_HOST_O . ")</option>";
                                }
                                echo $glb_ossecdb_option;
                                ?>
                            </select>
                        </form>
                    </ul>
                </li>
                <?php
            }
            ?>
            <li><a href="./index.php?">Home</a></li>
            <li><a href="./newsfeed.php?">NewsFeed</a></li>
            <li><a href="./massmonitoring.php?">Mass Monitoring</a></li>
            <li><a href="./detail.php?from=<?php echo date("Hi dmy", (time() - (3600 * 24 * 30))) ?>">Detail</a></li>
            <li><a href="./ip_info.php?">IP Info</a></li>
            <li><a href="#"
                   onclick='alert("Warning : Due to the complexity of the code, this page may take a few minute to load."); window.location = "./management.php"'>Management</a>
            </li>
            <li role="separator" class="divider"></li>
            <li><a href="./about.php">About</a></li>
        </ul>
    </div>
</nav>