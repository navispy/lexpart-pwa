<?php

//header('Content-Type: text/html; charset=UTF-8');

$ID = $_GET['ID'];

include('setup.php');

$schemaID = "JurBot";
setupSchema($schemaID);

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Expires: " . date("r"));


$query = "SELECT ContentX FROM __catalog49 WHERE ID=$ID";
$result = mysqli_query($connection, $query)
        or die(mysqli_error($connection));

$content = "";
if($row = mysqli_fetch_array($result)) {
    $content = $row["ContentX"];
}

echo(json_encode($content));
