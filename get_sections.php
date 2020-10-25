<?php

include('setup.php');

$schemaID = "JurBot";
setupSchema($schemaID);

$query = "SELECT ID, Comment FROM __catalog49 WHERE Comment LIKE 'Регистрация%'";
$result = mysqli_query($connection, $query)
        or die(mysqli_error($connection));

$forms = [];
While($row = mysqli_fetch_array($result)) {
    $forms[] = ["ID" => $row["ID"], "Name" => $row["Comment"]];
}

echo(json_encode($forms));