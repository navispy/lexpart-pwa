<?php

include('setup.php');


$calcTime = $_GET['calcTime'];
$schemaID = $_GET['solutionID'];
$userName = $_GET['userID'];

$clientModule = "";

setupSchema($schemaID);

if ($schemaID != "PH116562_frescofisher") {
    $query = "SELECT * FROM skyforce_central.dim_configurations WHERE SchemaID='$schemaID'";


    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection));


    if ($row = mysqli_fetch_array($result)) {
        $usersTable = $row["UsersTable"];
        $userNameField = $row["UserNameField"];
        
        $clientModule = $row["ClientModule"];
        
    }
} else {
    $usersTable = "dim_users";
    $userNameField = "Name";
}


$userID = getLookupValue($usersTable, "ID", $userNameField, $userName, $connection);

$documents = array();
$documentsHash = array();

$catalogs = array();
$catalogsHash = array();


$reports = array();
$reportsHash = array();

$registers = array();
$regsitersHash = array();

$forms = array();
$formsHash = array();
    
$pages = array();
$pagesHash = array();

$menu = array();
$menuHash = array();

$query = "SELECT * FROM dim_documents";
$result = mysqli_query($connection, $query)
        or die(mysqli_error($connection));

$nFixed = 0;
while ($row = mysqli_fetch_array($result)) {
    $documents[] = $row;
    $documentsHash[$row["Name"]] = $row;
    $nFixed++;
}

$query = "SELECT * FROM dim_catalogs";
$result = mysqli_query($connection, $query)
        or die(mysqli_error($connection));

while ($row = mysqli_fetch_array($result)) {
    $catalogs[] = $row;
    //$catalogsHash["__catalog" . $row["ID"]] = $row;
    $catalogsHash[$row["Name"]] = $row;
}

$query = "SELECT * FROM dim_reports";
$result = mysqli_query($connection, $query)
        or die(mysqli_error($connection));

while ($row = mysqli_fetch_array($result)) {
    $reports[] = $row;
    $reportsHash["__report" . $row["ID"]] = $row;
}

$query = "SELECT * FROM dim_registers";
$result = mysqli_query($connection, $query)
        or die(mysqli_error($connection));

while ($row = mysqli_fetch_array($result)) {
    $registers[] = $row;
    $registersHash[$row["Name"]] = $row;
}

$query = "SELECT * FROM dim_forms";
$result = mysqli_query($connection, $query)
        or die(mysqli_error($connection));

$num = 0;
while ($row = mysqli_fetch_array($result)) {
    $forms[] = $row;
    $formsHash["__form" . $row["ID"]] = $row;
    $formsHash["__form" . $row["ID"]]["num"] = $num;
    $num++;
}

$query = "SELECT * FROM dim_pages";
$result = mysqli_query($connection, $query)
        or die(mysqli_error($connection));

$num = 0;
while ($row = mysqli_fetch_array($result)) {
    $pages[] = $row;
    $pagesHash["__page" . $row["ID"]] = $row;
    $pagesHash["__page" . $row["ID"]]["num"] = $num;
    $num++;
}

function buildMenuDetails($connection, $userID, $parentID){
    $menu = [];
    $query = "SELECT * FROM dim_menu WHERE UserID=$userID AND ParentID=$parentID ORDER BY `Order`";
    
   
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection));

    while ($row = mysqli_fetch_array($result)) {
        $menu[] = $row;
    }
    return $menu;
}

function buildMenu($connection, $userID) {
    $menu = [];
    $query = "SELECT * FROM dim_menu WHERE UserID=$userID AND ParentID=0 ORDER BY `Order`";
    
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection));

    while ($row = mysqli_fetch_array($result)) {
        $ID = $row["ID"];
        $details = buildMenuDetails($connection, $userID, $ID);
        $row["Details"] = $details;
        $menu[] = $row;
    }
    
    if(($userID != 0) && (count($menu) == 0)){
        $menu = buildMenu($connection, 0);
    }
    
    return $menu;
    
}


$menu = buildMenu($connection, $userID);

if ($schemaID != "PH116562_frescofisher") {
    $homePageForm = getLookupValue("skyforce_central.dim_configurations", "Homepage", "SchemaID", $schemaID, $connection);
    $homePageFormID = getLookupValue("dim_forms", "ID", "Name", $homePageForm, $connection);
} else {
    $homePageForm = "Home";
    $homePageFormID = 2;
}

///////////////////////////////////
echo('{"calcTime":' . json_encode($calcTime) . ',');
echo('"documents":' . json_encode($documents) . ',');
echo('"documentsHash":' . json_encode($documentsHash) . ',');
echo('"catalogs":' . json_encode($catalogs) . ',');
echo('"catalogsHash":' . json_encode($catalogsHash) . ',');
echo('"reports":' . json_encode($reports) . ',');
echo('"reportsHash":' . json_encode($reportsHash) . ',');
echo('"registers":' . json_encode($registers) . ',');
echo('"registersHash":' . json_encode($registersHash) . ',');
echo('"forms":' . json_encode($forms) . ',');
echo('"formsHash":' . json_encode($formsHash) . ',');
echo('"homePageFormID":' . json_encode($homePageFormID) . ',');
echo('"pages":' . json_encode($pages) . ',');
echo('"pagesHash":' . json_encode($pagesHash) . ',');
echo('"clientModule":' . json_encode($clientModule) . ',');
echo('"menu":' . json_encode($menu) . '}');

?>
