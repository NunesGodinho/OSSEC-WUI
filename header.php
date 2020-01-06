<nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a href="../index.php?" class="navbar-brand">OSSEC - WUI</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">

          <ul class="nav navbar-nav ml-auto">
            <li class="nav-item"><a class="nav-link" href="./index.php?">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="./newsfeed.php?">NewsFeed</a></li>
            <li class="nav-item"><a class="nav-link" href="./massmonitoring.php?">Mass Monitoring</a></li>
            <li class="nav-item"><a class="nav-link" href="./detail.php?from=<?php echo date("Hi dmy", (time() - (3600 * 24 * 30))) ?>">Detail</a></li>
            <li class="nav-item"><a class="nav-link" href="./ip_info.php?">IP Info</a></li>
            <li class="nav-item"><a class="nav-link" href="#"
                   onclick='alert("Warning : Due to the complexity of the code, this page may take a few minute to load."); window.location = "./management.php"'>Management</a>
            </li>
            <li role="separator" class="divider"></li>
            <li><a class="nav-link" href="./about.php">About</a></li>
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
          </ul>
        </div>
    </div>
</nav>