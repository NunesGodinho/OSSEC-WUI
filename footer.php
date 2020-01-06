<footer class="footer">
    <div class="col-lg-12" style="height: 100%; text-align: center; vertical-align: middle;">
        <p style="margin-bottom: 20px; margin-top: 20px;">
            <a href="http://www2.isec.pt/~to">© 2019 - António Godinho</a>
            <?php
            if ($glb_debug == 1) {
                $endtime = microtime();
                $endarray = explode(" ", $endtime);
                $endtime = $endarray[1] + $endarray[0];
                $totaltime = $endtime - $starttime;
                $totaltime = round($totaltime, 2);
                echo "&nbsp;|&nbsp;<span class='tiny'>" . $totaltime . "s</span>";
            }
            ?>
        </p>
    </div>
</footer>
</div>
<script src="js/jquery-3.4.1.min.js"></script>
<script src="js/jquery-migrate-1.4.1.min.js"></script>
<script src="../js/bootstrap.min.js"></script>