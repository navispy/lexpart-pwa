<?php

/*
 * This script returns outputTables in JSON format
 */

ini_set('memory_limit', '-1');
set_time_limit(0);

include('setup.php');

$calcTime = $_POST['calcTime'];
$schemaID = $_POST['schemaID'];
$documentName = $_POST['documentName'];
$whereClause = $_POST['whereClause'];
$orderByClause = $_POST['orderByClause'];
$needDetails = $_POST['needDetails'];

$needDetails = isset($needDetails) ? $needDetails : 0;
$query = $_POST['query'];

setupSchema($schemaID);

//$physicalDocuments = array();
//$physicalDocumentsHash = array();

$result = mysqli_query($connection, $query)
        or die(mysqli_error($connection));
$numRows = mysqli_num_rows($result);

$values = $numRows > 0 ? new SplFixedArray($numRows) : array();

$nRow = 0;

while ($row = mysqli_fetch_array($result)) {
    $values[$nRow] = $row;
    $nRow++;
}

echo('{"calcTime":' . json_encode($calcTime) . ',');
echo('"physicalDocuments":' . json_encode($values) . ',');
echo('"rows":' . json_encode($numRows) . '}');
?>