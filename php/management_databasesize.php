<?php
### Odds and sods
$query = "SELECT table_schema as 'Database', sum( data_length + index_length ) / 1024 / 1024 as 'Size' 
	FROM information_schema.TABLES 
	WHERE table_schema='ossec' 
	GROUP BY table_schema";
if ($glb_debug == 1) {
    $databaseinMB = $query;
} else {
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $databaseinMB = number_format(floor($row['Size'])) . " MB";
}

$query = "SELECT count(id) as rows from alert";
if ($glb_debug == 1) {
    $databaseinrows = $query;
} else {
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $databaseinrows = number_format($row['rows']);
}
?>

<div style="padding:10px;">
    <table>
        <?php
        if ($glb_debug == 1) {
            echo "<tr><td><div style='font-size:24px; color:red;'>Debug</div></td></tr>";
        }
        ?>

        <tr>
            <th>Database Size</th>
            <th>Database Alert Count</th>
        </tr>
        <tr>
            <td style="padding:8px"><?php echo $databaseinMB ?></td>
            <td style="padding:8px"><?php echo $databaseinrows ?></td>
        </tr>
    </table>
</div>
