<?php

/*
 * This script returns outputTables in JSON format
 */

include('setup.php');



$calcTime = $_POST['calcTime'];
$schemaID = $_POST['schemaID'];

$objectType = $_POST['objectType'];
$objectID = $_POST['objectID'];

$mask = $_POST['mask'];
$fields = $_POST['fields'];
$lookupTextField = $_POST['lookupTextField'];

setupSchema($schemaID);

$query = "SELECT $fields FROM __$objectType$objectID WHERE UPPER($lookupTextField) LIKE UPPER('$mask%') ORDER BY $lookupTextField LIMIT 1";



$result = mysqli_query($connection, $query)
        or die(mysqli_error($connection));

$nRow = 0;

$bestMatch = "";
if ($row = mysqli_fetch_array($result)) {
    $bestMatch = $row[$lookupTextField];
    $bestID = $row["ID"];
    $bestRow = $row;
    $nRow++;
}

echo('{"calcTime":' . json_encode($calcTime) . ',');
echo('"bestID":' . json_encode($bestID) . ',');
echo('"bestMatch":' . json_encode($bestMatch) . ',');
echo('"bestRow":' . json_encode($bestRow) . '}');

?>