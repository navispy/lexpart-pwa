<?php

//header('Content-Type: text/html; charset=UTF-8');

$ID = $_POST['ID'];
$pages = $_POST['pages'];

include('setup.php');

$schemaID = "JurBot";
setupSchema($schemaID);

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Expires: " . date("r"));

$pages = mysqli_escape_string($connection, $pages);
$query = "UPDATE __catalog49 SET ContentX='$pages' WHERE ID=$ID";

$result = mysqli_query($connection, $query)
        or die(mysqli_error($connection));



echo(json_encode($ID));
