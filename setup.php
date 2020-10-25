<?php


error_reporting(E_ERROR | E_PARSE);

//include_once('error_reporting.php');

//require_once('fpdf/fpdf.php');
//require_once('fpdi/fpdi.php');
//require_once('PDF_MC_Table.php');


//Google Drive Support for Aermec
//require_once 'vendor/autoload.php';


//define('APPLICATION_NAME', 'Drive API PHP Quickstart');
//define('CREDENTIALS_PATH', 'credentials/drive-php-quickstart.json');
//define('CLIENT_SECRET_PATH', 'client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at ~/.credentials/drive-php-quickstart.json
//define('SCOPES', implode(' ', array(
//    Google_Service_Drive::DRIVE)
//));

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */

$connection = null;

function getResourcesByTime($id, $timeX, $timeY, $dimensions, $resources, $connection){
    //getting resources by dimensions/values
    //id - register ID
    
    $set = "";
    $fields = "";
    $delim = "";
    foreach($dimensions as $dim){

        $set .= $delim . "$dim[0] = $dim[1]";
        $delim = " AND ";
    }
    
    $set .= " AND TimeX + TimeY < " . ($timeX + $timeY) . " ORDER BY TimeX DESC, TimeY DESC LIMIT 1";
    
    $delim = "";
    foreach($resources as $res){

        $fields .= $delim . "$res";
        $delim = ",";
    }    
    
    $query = "SELECT $fields FROM __register$id" . "b WHERE $set";
    
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);    
    
    $row = mysqli_fetch_array($result);
    return $row;
}


function getStockByTime($id, $timeX, $timeY, $dimensions, $resources, $connection){
    //getting resources by dimensions/values
    //id - register ID
    
    $set = "";
    $fields = "";
    $delim = "";
    foreach($dimensions as $dim){

        $set .= $delim . "$dim[0] = $dim[1]";
        $delim = " AND ";
    }
    
    $set .= " AND TimeX + TimeY < " . ($timeX + $timeY);
    
    $delim = "";
    foreach($dimensions as $dim){

        $fields .= $delim . $dim[0];
        $delim = ",";
    }    

    $delim = ",";
    foreach($resources as $res){

        $fields .= $delim . "$res";
        $delim = ",";
    }    

    
    $query = "SELECT $fields FROM __register$id" . "b WHERE $set";
    
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);    
    
    $recs = [];
    while($row = mysqli_fetch_array($result)){
        $recs[] = $row;
    }
    return $recs;
}
function updateFutureBalances($id, $timeX, $timeY, $dimensions, $resources, $values, $connection){
    //values contains current balance
    $movements = getBalanceRegisterFutureRec($id, $timeX, $timeY, $dimensions, $connection);
    
    foreach($movements as $mov){

        
        $timeX = $mov["TimeX"];
        $timeY = $mov["TimeY"];

        
        //clearBalanceRegister($id, $timeX, $timeY);
        
        foreach($resources as $res){
            
            
            $values[$res] = $values[$res] + $mov[$res];
        }
        


        $values[0] = $timeX;
        $values[1] = $timeY;
        updateRegisterBalance($id, $timeX, $timeY, $dimensions, $resources, $values, $connection);
    }
    
}

function updateRegisterBalance($id, $timeX, $timeY, $dimensions, $resources, $values, $connection){
    $set = "";
    $fields = "TimeX, TimeY";
    $delim = ",";
    foreach($dimensions as $dim){
        $fields .= $delim . "$dim[0]";
    }
    
    foreach($resources as $res){
        $fields .= $delim . "$res";
        $delim = ",";
    }    

    $set = "";
    $delim = "";
    foreach($values as $val){
        $set .= $delim . "'$val'";
        $delim = ",";
    }
    
    $condition = "TimeX = $timeX AND TimeY = $timeY";
    $delim = " AND ";
    foreach($dimensions as $dim){
        $condition .= $delim . "$dim[0] = '$dim[1]'";
    }    
    

    //delete an old result if any
    $query = "DELETE FROM  __register$id" . "b WHERE $condition";
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);        

    //update register
    $query = "INSERT INTO  __register$id" . "b ($fields) VALUES ($set)";

    $result = mysqli_query($connection, $query)
            or die(mysqli_errno($connection) . " query: " . $query);        
    
}

function getFutureBalances($id, $timeX, $timeY, $dimensions, $connection){
    $condition = "TimeX + TimeY > $timeX + $timeY";
    $delim = " AND ";

    foreach($dimensions as $dim){
        $condition .= $delim . "$dim[0] = '$dim[1]'";
    }       
    ////////////////////////////////////////////////////
    $movements = array();
    
    $query = "SELECT * FROM __register$id" . "b WHERE $condition";
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);
    
    while ($row = mysqli_fetch_array($result)) {
        $movements[] = $row;
    }
    
    
    return $movements;
}

function rollbackRegisterBalance($id, $timeX, $timeY, $dimensions, $resources, $values, $connection){    
    $balances = getFutureBalances($id, $timeX, $timeY, $dimensions, $connection);
    foreach($balances as $balance){
        
        
        $futureX = $balance["TimeX"];
        $futureY = $balance["TimeY"];
        
        $onHand 	= $balance["OnHand"] - $values[2];
        $commited 	= $balance["Commited"] - $values[3];
        $onOrder	= $balance["OnOrder"] - $values[4];
	$directShip     = $balance["DirectShip"] - $values[5];
        
        $part = $dimensions[0][1];
        $warehouse = $dimensions[1][1];
        
        $futureValues = array($futureX, $futureY, $part, $warehouse, $onHand, $commited, $onOrder, $directShip);
        
        updateRegisterBalance($id, $futureX, $futureY, $dimensions, $resources, $futureValues, $connection);
        
    }
    
}


function rebuildRegisterBalance($id, $timeX, $timeY, $dimensions, $resources, $values, $connection){    
    $balances = getFutureBalances($id, $timeX, $timeY, $dimensions, $connection);
    foreach($balances as $balance){
        $futureX = $balance["TimeX"];
        $futureY = $balance["TimeY"];    
        
        $onHand 	= $balance["OnHand"] + $values[2];
        $commited 	= $balance["Commited"] + $values[3];
        $onOrder	= $balance["OnOrder"] + $values[4];
	$directShip     = $balance["DirectShip"] + $values[5];
        
        $part = $dimensions[0][1];
        $warehouse = $dimensions[1][1];
        
        $futureValues = array($futureX, $futureY, $part, $warehouse, $onHand, $commited, $onOrder, $directShip);

        updateRegisterBalance($id, $futureX, $futureY, $dimensions, $resources, $futureValues, $connection);
        
    }
    
}

function getSavTimeX($id, $docTypeID, $docID, $resource, $connection){
    $query = "SELECT TimeX FROM __register$id" . "a "
            ."WHERE DocTypeID = $docTypeID AND DocID = $docID AND $resource <> 0";
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);
    
    if ($row = mysqli_fetch_array($result)) {
        return $row["TimeX"];
    } else {
        return -1;
    }

}

function getSavTimeX_Positive($id, $docTypeID, $docID, $resource, $connection){
    $query = "SELECT TimeX FROM __register$id" . "a "
            ."WHERE DocTypeID = $docTypeID AND DocID = $docID AND $resource > 0";
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);
    
    if ($row = mysqli_fetch_array($result)) {
        return $row["TimeX"];
    } else {
        return -1;
    }

}

function getSavTimeX_Negative($id, $docTypeID, $docID, $resource, $connection){
    $query = "SELECT TimeX FROM __register$id" . "a "
            ."WHERE DocTypeID = $docTypeID AND DocID = $docID AND $resource < 0";
    
logMessage("bel8.txt", $query);
    
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);
    
    if ($row = mysqli_fetch_array($result)) {
        return $row["TimeX"];
    } else {
        return -1;
    }

}

function getSavTimeY($id, $docTypeID, $docID, $resource, $connection){
    $query = "SELECT TimeY FROM __register$id" . "a "
            ."WHERE DocTypeID = $docTypeID AND DocID = $docID AND $resource <> 0";
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);
    
    if ($row = mysqli_fetch_array($result)) {
        return $row["TimeY"];
    } else {
        return -1;
    }    
}


function getSavTimeY_Positive($id, $docTypeID, $docID, $resource, $connection){
    $query = "SELECT TimeY FROM __register$id" . "a "
            ."WHERE DocTypeID = $docTypeID AND DocID = $docID AND $resource > 0";
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);
    
    if ($row = mysqli_fetch_array($result)) {
        return $row["TimeY"];
    } else {
        return -1;
    }    
}

function getSavTimeY_Negative($id, $docTypeID, $docID, $resource, $connection){
    $query = "SELECT TimeY FROM __register$id" . "a "
            ."WHERE DocTypeID = $docTypeID AND DocID = $docID AND $resource < 0";
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);
    
    if ($row = mysqli_fetch_array($result)) {
        return $row["TimeY"];
    } else {
        return -1;
    }    
}



function getNextTimeY($id, $timeX, $connection){
    $query = "SELECT MAX(TimeY) AS MAXY FROM __register$id" . "a "
            ."WHERE TimeX = $timeX "
            ."HAVING MAX(TimeY) IS NOT NULL";
    
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);
    
    if ($row = mysqli_fetch_array($result)) {
        return $row["MAXY"] + 1;
    } else {
        return 0;
    }    
}


function addBalanceRegisterRec($id, $docTypeID, $docID, $dimensions, $resources, $values, $connection){
    $fields = "DocTypeID, DocID";
    $delim = ",";
    foreach($dimensions as $dim){

        $fields .= $delim . "$dim";
        $delim = ",";
    }
    
    foreach($resources as $res){

        $fields .= $delim . "$res";
        $delim = ",";
    }    
    
    $set = "$docTypeID, $docID";
    $delim = ",";
    foreach($values as $val){
        $set .= $delim . "'$val'";
        $delim = ",";
    }    
    
    
    $query = "INSERT INTO __register$id" . "a ($fields) VALUES ($set)";
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);
    
    
}

function addBalanceRegisterRecBatch($id, $docTypeID, $docID, $dimensions, $resources, $recs, $connection) {
    if(count($recs) == 0)
        return;
    
    $fields = "DocTypeID, DocID";
    $delim = ",";
    foreach ($dimensions as $dim) {

        $fields .= $delim . "$dim";
        $delim = ",";
    }

    foreach ($resources as $res) {

        $fields .= $delim . "$res";
        $delim = ",";
    }

    $set = "";
    $delim = "";
    foreach ($recs as $rec) {
        $set .= $delim . "($docTypeID, $docID";

        foreach ($rec as $val) {
            $set .= ", '$val'";
        }

        $set .= ")";
        $delim = ",";
    }


    $query = "INSERT INTO __register$id" . "a ($fields) VALUES $set";
    
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);
}

function clearBalanceRegisterRec($id, $docTypeID, $docID, $connection){
    $movements = array();
    $query = "SELECT * FROM __register$id" . "a WHERE DocTypeID = $docTypeID AND DocID = $docID";
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);
    
    while ($row = mysqli_fetch_array($result)) {
        $movements[] = $row;
    }
    
    ///////////////////////////////////////////////////////////////////////////////////////////
    
    $query = "DELETE FROM __register$id" . "a WHERE DocTypeID = $docTypeID AND DocID = $docID";
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);
    
    return $movements;
}


function getBalanceRegisterRec($id, $docTypeID, $docID, $connection){
    $movements = array();
    $query = "SELECT * FROM __register$id" . "a WHERE DocTypeID = $docTypeID AND DocID = $docID";
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);
    
    while ($row = mysqli_fetch_array($result)) {
        $movements[] = $row;
    }
   
    return $movements;
}

function getBalanceRegisterFutureRec($id, $timeX, $timeY, $dimensions, $connection){
    $condition = "TimeX + TimeY > $timeX + $timeY";
    $delim = " AND ";

    foreach($dimensions as $dim){
        $condition .= $delim . "$dim[0] = '$dim[1]'";
    }    
    
    $movements = array();
    $query = "SELECT * FROM __register$id" . "a WHERE $condition ORDER BY TimeX ASC, TimeY ASC";
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);
    
    while ($row = mysqli_fetch_array($result)) {
        $movements[] = $row;
    }
   
    return $movements;
}

function clearBalanceRegister($id, $timeX, $timeY, $connection){
    $query = "DELETE FROM __register$id" . "b WHERE ID > 0 AND TimeX = $timeX AND TimeY = $timeY";
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);
}

function clearBalanceRegisterRec_Negative($id, $docTypeID, $docID, $resource, $connection){
    $query = "DELETE FROM __register$id" . "a WHERE DocTypeID = $docTypeID AND DocID = $docID AND $resource < 0";
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);
}

function clearBalanceRegisterRec_Positive($id, $docTypeID, $docID, $resource, $connection){
    $query = "DELETE FROM __register$id" . "a WHERE DocTypeID = $docTypeID AND DocID = $docID AND $resource > 0";
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);
}

function getFolderID($service, $folderName){
    
    $optParams = array(
      'q' => "name='$folderName' and mimeType='application/vnd.google-apps.folder'", 
      'pageSize' => 1000,
      'fields' => 'nextPageToken, files(id, name)'
    );
    $results = $service->files->listFiles($optParams);
    
    return count($results->getFiles()) != 0 ? $results->getFiles()[0]->getId() : -1;
}

function createFolder($service, $folderName, $parentFolderID) {
    
    $fileMetadata = new Google_Service_Drive_DriveFile(array(
        'name' => "$folderName",
        'mimeType' => 'application/vnd.google-apps.folder',
        'parents' => array($parentFolderID)));
    $file = $service->files->create($fileMetadata, array(
        'fields' => 'id'));
    return $file->id;
}

function getClient() {
    $client = new Google_Client();
    $client->setApplicationName(APPLICATION_NAME);
    $client->setScopes(SCOPES);
    $client->setAuthConfig(CLIENT_SECRET_PATH);
    $client->setAccessType('offline');

    // Load previously authorized credentials from a file.
    $credentialsPath = CREDENTIALS_PATH;
    if (file_exists($credentialsPath)) {
        $accessToken = json_decode(file_get_contents($credentialsPath), true);
    } else {
        // Request authorization from the user.
        $authUrl = $client->createAuthUrl();
        printf("Open the following link in your browser:\n%s\n", $authUrl);
        print 'Enter verification code: ';
        $authCode = trim(fgets(STDIN));

        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

        // Store the credentials to disk.
        if (!file_exists(dirname($credentialsPath))) {
            mkdir(dirname($credentialsPath), 0700, true);
        }
        file_put_contents($credentialsPath, json_encode($accessToken));
        printf("Credentials saved to %s\n", $credentialsPath);
    }
    $client->setAccessToken($accessToken);

    // Refresh the token if it's expired.
    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
    }
    return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path) {
    $homeDirectory = getenv('HOME');
    if (empty($homeDirectory)) {
        $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
    }
    return str_replace('~', realpath($homeDirectory), $path);
}
/////////////////////////////////////////////////////////////////////////////////////
function setupSchema($schemaID) {
    global $connection;
    
    //$username="wordpress_user";
    //$password="51v0Eipt37609138vU843S85n";

    //$server="localhost";

    $username="root";
    $password="DellOptiplexGL5100";

    $server="localhost";
    
    $connection = mysqli_connect($server, $username, $password, $schemaID);
    if (!$connection) {
        die('Can\'t connect : ' . mysqli_error($connection));
    }

mysqli_set_charset($connection, 'utf8');

}


function numberPages($pdf, $totalX, $totalY, $r, $g, $b) {
    $pages = $pdf->pages;
    $numPages = count($pages);
    $curPage = 1;
    for ($i = 1; $i <= $numPages; $i++) {
        $pdf->page = $i;
        

        

        //$pdf->SetXY($totalX, $totalY);

        //if ($curPage == 1) {
            //$pdf->Cell(60, 6, $numPages, 'LRTB', 0, 'L', false);
        //}

        //$pdf->SetFont('Arial');
        $pdf->SetTextColor($r, $g, $b);

        $pdf->SetXY($totalX, $totalY);
        $pdf->SetAutoPageBreak(false);
        //$pdf->SetFont('Arial', '', 10);
        $pdf->Write(10, "Page $curPage of $numPages");
        //$pdf->SetFont('Arial', '', 10);
        //$this->Output();
        $curPage++;
    }
}

ini_set('max_execution_time', 3600); 


function esc($st, $connection) {
    return mysqli_real_escape_string($connection, $st);
}

function getLookupMatchingIDs($lookupTable, $lookupTextField, $lookupValueField, $search, $connection) {
    $isExternal = getLookupValue("dim_catalogs", "IsExternal", "Name", $lookupTable, $connection);
    $isExternal = !( ($isExternal == null) ||
                     ($isExternal == 0));    
    
    if($isExternal){
        $externalSchema = getLookupValue("dim_catalogs", "ExternalSchema", "Name", $lookupTable, $connection);
        $lookupTable = $externalSchema.$lookupTable;
    }    
    $query = "SELECT $lookupValueField FROM $lookupTable WHERE $lookupTextField LIKE '%$search%'";
    


    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: $query");

    $IDs = "";
    $delim = "";
    while ($row = mysqli_fetch_array($result)) {
        $value = $row[$lookupValueField];
        $IDs .= $delim . $value;
        $delim = ",";
    }

    return $IDs == "" ? "null" : $IDs;
}

function getLookupValues($lookupTable, $lookupTextField, $lookupValueField, $connection) {
    $isExternal = getLookupValue("dim_catalogs", "IsExternal", "Name", $lookupTable, $connection);
    $isExternal = !( ($isExternal == null) ||
                     ($isExternal == 0));    
    
    if($isExternal){
        $externalSchema = getLookupValue("dim_catalogs", "ExternalSchema", "Name", $lookupTable, $connection);
        $lookupTable = $externalSchema.$lookupTable;
    }
    
    
    
    $query = "SELECT $lookupTextField, $lookupValueField FROM $lookupTable";


    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: $query");

    $lookupValues = array();
    while ($row = mysqli_fetch_array($result)) {
        $textField = $row[$lookupTextField];
        $lookupValues[$row[$lookupValueField]] = array(
            "TextField" => $textField,
            "ValueField" => $row[$lookupValueField]);
    }


    return $lookupValues;
}

function getDetailValues($PK, $detailTable, $detailTableFK, $detailFields, &$detailValuesHash, $connection) {
    $detailTable = strtolower($detailTable);
    
    $detailValues = array();

    $strFields = "";
    $strDelim = "";
    foreach ($detailFields as $field) {
        if ($field["FieldName"] == "RowNum")
            continue;
        $strFields .= $strDelim . "`" . $field["FieldName"] . "`";
        $strDelim = ",";
    }

    $query = "SELECT $strFields FROM $detailTable WHERE $detailTableFK = $PK";

    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: $query");

    //$fields = explode(",", $detailFields);
    $rowNum = 1;
    while ($row = mysqli_fetch_array($result)) {
        $row["RowNum"] = $rowNum;
        foreach ($detailFields as $field) {
            $fieldName = $field["FieldName"];
            $fieldType = $field["FieldType"];
            if ($fieldType == 3) {
                $lookupTable = $field["LookupTable"];
                $catalogTable = getCatalogTable($lookupTable, $connection);

                $lookupValueField = $field["LookupValueField"];
                $lookupTextField = $field["LookupTextField"];
                $fieldValue = $row[trim($fieldName)];

                $row[trim($fieldName)] = $fieldValue;
                $row[trim($fieldName) . "_Text"] = getLookupValue($catalogTable, $lookupTextField, $lookupValueField, $fieldValue, $connection);
            } else {
                $fieldValue = $row[trim($fieldName)];
                $row[trim($fieldName)] = $fieldValue;
            }
        }
        $detailValues[] = $row;
        $detailValuesHash["row" . $rowNum] = $row;
        $rowNum++;
    }

    return $detailValues;
}
function getPhysicalDocuments($documentName, $whereClause, $orderByClause, &$physicalDocuments, &$physicalDocumentsHash, $needDetails=1, $connection) {
    $search = "";
    $logicalDocumentID = getLookupValue("dim_documents", "ID", "Name", $documentName, $connection);
//get controls
    $fieldsList = "*";
    $query = "SELECT $fieldsList FROM dim_documents WHERE ID = $logicalDocumentID";
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection));

    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection));
    $row = mysqli_fetch_array($result);

    $physicalDocumentFieldsJSON = $row["Fields"];
    $physicalDocumentControls = json_decode($physicalDocumentFieldsJSON, true);

    array_unshift($physicalDocumentControls, array(
        "ID" => 0,
        "FieldName" => "RowNum",
        "FieldText" => "Row Num",
        "FieldType" => 1,
        "LookupValues" => "",
        "ManyToManyValues" => array(),
        "IsHex" => false,
        "Visible" => 0,
        "MidTable" => "",
        "MidField1" => "",
        "MidField2" => "",
        "MidField3" => "",
        "AdminField" => "",
        "AdminValue" => "",
        "LeftTable" => "",
        "LeftField" => "",
        "DetailTable" => "",
        "DetailTableFK" => "",
        "DetailFields" => array()));

   
    foreach ($physicalDocumentControls as &$row2) {

        if ($row2["FieldName"] == "RowNum")
            continue;
        
        $controlID = $row2["ID"];

        $midTable = "";
        $midField1 = "";
        $midField2 = "";
        $midField3 = "";
        $adminField = "";
        $adminValue = "";
        $leftTable = "";
        $leftField = "";

        $detailTable = "";
        $detailTableFK = "";
        $detailFields = array();


        $lookupValues = array();
        $manyToManyValues = array();

        if ($row2["FieldType"] == 3) {
            $catalogTable = getCatalogTable($row2["LookupTable"], $connection);
            
            if (trim($catalogTable) == "") {
                $catalogTable = getDocumentTable($row2["LookupTable"], $connection);
            }

            $searchLookupIDs = getLookupMatchingIDs($catalogTable, $row2["LookupTextField"], $row2["LookupValueField"], $search, $connection);
            $searchFilter .= $searchDelim . $row2["FieldName"] . " IN ( $searchLookupIDs ) ";
            $searchDelim = " OR ";

            $lookupValues = getLookupValues($catalogTable, $row2["LookupTextField"], $row2["LookupValueField"], $connection);
        } else if ($row2["FieldType"] == 4 || $row2["FieldType"] == 5) {
            //$manyToManyValues = getManyToManyValues($row2["ID"], $midTable, $midField1, $midField2, $midField3, $adminField, $adminValue, $leftTable, $leftField);
        } else if ($row2["FieldType"] == 6) {
            $detailTable = "__document$logicalDocumentID" . "_" . $row2["FieldName"];
            $detailTable = strtolower($detailTable);
            $detailTableFK = "__document$logicalDocumentID" . "ID";

            array_unshift($row2["DetailFields"], array(
                "ID" => 0,
                "ControlID" => $controlID,
                "FieldName" => "RowNum",
                "FieldText" => "Row Num",
                "FieldType" => 1,
                "LookupValues" => "",
                "LookupTable" => "",
                "LookupValueField" => "",
                "LookupTextField" => "",
                "Visible" => 0,
                "IsHex" => 0,
                "OnChangeEvent" => "",
                "HasSelectionButton" => false,
                "OnStartSelect" => ""
            ));
            //getOneToManyValues($row["ID"], $detailTable, $detailTableFK, $detailFields);
        }

        $row2["LookupValues"] = $lookupValues;
        $row2["ManyToManyValues"] = $manyToManyValues;
        $row2["MidTable"] = $midTable;
        $row2["MidField1"] = $midField1;
        $row2["MidField2"] = $midField2;
        $row2["MidField3"] = $midField3;
        $row2["AdminField"] = $adminField;
        $row2["AdminValue"] = $adminValue;
        $row2["LeftTable"] = $leftTable;
        $row2["LeftField"] = $leftField;
        $row2["DetailTable"] = $detailTable;
        $row2["DetailTableFK"] = $detailTableFK;
    }


    $result = mysqli_query($connection, "SELECT *, 0 AS IsAdmin FROM __document$logicalDocumentID $whereClause $orderByClause")
            or die(mysqli_error($connection));

    $rowNum = 1;
    $manyValues = array();
    while ($row = mysqli_fetch_array($result)) {
        foreach ($physicalDocumentControls as $physicalDocumentControl) {
            $fieldName = $physicalDocumentControl["FieldName"];
            $fieldType = $physicalDocumentControl["FieldType"];

            if ($fieldType == 6) {
                $PK = "ID"; //getLookupValue("__dim_catalogs", "PK", "ID", $catalogID);

                $detailTable = $physicalDocumentControl["DetailTable"];
                $detailTableFK = $physicalDocumentControl["DetailTableFK"];

                $controlID = $physicalDocumentControl["ID"];
                $detailFields = $physicalDocumentControl["DetailFields"];
                $detailValuesHash = array();
                
                $isAdmin = false;
                $row[$fieldName] = $needDetails == 1 ? getDetailValues($row[$PK], $detailTable, $detailTableFK, $detailFields, $detailValuesHash, $connection) : array();
                $row[$fieldName . "Hash"] = $needDetails == 1 ? $detailValuesHash : array();
                $row["IsAdmin"] = $isAdmin ? 1 : 0;
            } /* else {
              $rowFixed = iconv('windows-1252', 'UTF-8', $row[trim($fieldName)]);
              $row[trim($fieldName)] = iconv('windows-1252', 'UTF-8', $rowFixed);
              } */

            if ($fieldType == 3) {
                $fieldValue = $row[trim($fieldName)];
                $lookupTable = $physicalDocumentControl["LookupTable"];
                $catalogTable = getCatalogTable($lookupTable, $connection);
                if (trim($catalogTable) == "")
                    $catalogTable = getDocumentTable($lookupTable, $connnection);
                $lookupValueField = $physicalDocumentControl["LookupValueField"];
                $lookupTextField = $physicalDocumentControl["LookupTextField"];
                $row[trim($fieldName) . "_Text"] = getLookupValue($catalogTable, $lookupTextField, $lookupValueField, $fieldValue, $connection);
            }
        }

        $row["RowNum"] = $rowNum;

        $physicalDocuments[] = $row;
        $physicalDocumentsHash[$row["ID"]] = $row;
        $rowNum++;
    }
}

function getLookupValue($lookupTable, $lookupField, $field, $value, $connection) {
    $query = "SELECT `$lookupField` FROM $lookupTable WHERE `$field` = '$value'";
    
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);
    if ($row = mysqli_fetch_array($result)) {
        return $row[$lookupField];
    } else
        return '';
}

function getLookupValueX($lookupTable, $lookupField, $field, $value, $connection) {
    $query = "SELECT `$lookupField` FROM $lookupTable WHERE `$field` = '$value'";

    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);
    if ($row = mysqli_fetch_array($result)) {
        return $row[$lookupField];
    } else
        return '';
}

function getDocumentTable($document, $connection){
    $table = "";
    //$catalog = iconv('windows-1252', 'UTF-8', $catalog);
    $query = "SELECT ID FROM dim_documents WHERE Name = '$document'";
    
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);
    if ($row = mysqli_fetch_array($result)) {
        $table = "__document" . $row["ID"];
    }

    return $table;
}

function getCatalogTable($catalog, $connection){
    $table = "";
    //$catalog = iconv('windows-1252', 'UTF-8', $catalog);
    $query = "SELECT ID, TableName FROM dim_catalogs WHERE Name = '$catalog'";
    
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);
    if ($row = mysqli_fetch_array($result)) {
        
        if($row["TableName"] != ""){
            $table = $row["TableName"];
        } else {
            $table = "__catalog" . $row["ID"];
        }
    }

    return $table;
}

function getCatalogID($catalog, $connection){
    $ID = "";
    //$catalog = iconv('windows-1252', 'UTF-8', $catalog);
    $query = "SELECT ID FROM dim_catalogs WHERE Name = '$catalog'";
    
    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);
    if ($row = mysqli_fetch_array($result)) {
        $ID = $row["ID"];
    }

    return $ID;
}


function getCatalogLookupValue($catalog, $lookupField, $field, $value, $connection) {
    $lookupTable = getCatalogTable($catalog, $connection);
    if(trim($lookupTable) == "")
        $lookupTable = getDocumentTable($catalog, $connection);
    
    $query = "SELECT `$lookupField` FROM $lookupTable WHERE `$field` = '$value'";

    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);
    if ($row = mysqli_fetch_array($result)) {
        return $row[$lookupField];
    } else
        return '';
}


function getOneToManyValues($controlID, &$detailTable, &$detailTableFK, &$detailFields, $connection) {
    $query = "SELECT "
            . "DetailTable, "
            . "DetailTableFK "
            . "FROM "
            . "dim_document_controls_one_to_many "
            . "WHERE ControlID = " . $controlID;

    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection) . " query: " . $query);

    if ($row = mysqli_fetch_array($result)) {
        $detailTable = $row["DetailTable"];
        $detailTableFK = $row["DetailTableFK"];
        $detailFields = getDetailFields($controlID, $connection);
    }
}

function getCatalogValues($catalogID, $connection){
    
    $catalogTable = getLookupValue("dim_catalogs", "TableName", "ID", $catalogID, $connection);
    $catalogTable = $catalogTable == "" ? "__catalog$id" : $catalogTable;

    $query = "SELECT * FROM $catalogTable";
    
    $v = array();
    try{
        $result = mysqli_query($connection, $query);
                //or die(mysqli_error($connection) . " query: " . $query);
    } catch (Exception $e) {
        return $v;
    }

    while ($row = mysqli_fetch_array($result)) {    
        $v[$row["ID"]] = $row;
    }
    return $v;
}
function logMessage($logfile, $msg) {
    if (!$f = @fopen($logfile, 'a+'))
        return false;
    //fwrite($f, $msg['date'] . '#' . str_replace("\n", '##N##', serialize($msg)));
    fwrite($f, $msg . "\r\n");
    fclose($f);
    return true;
}

function fileWrite($file, $content) {
    if (!$f = @fopen($file, 'w'))
        return false;
    fwrite($f, $content);
    fclose($f);
    return true;
}


function begin($connection) {
    mysqli_query($connection, "BEGIN");
}

function commit($connection) {
    mysqli_query($connection, "COMMIT");
}

function rollback($connection) {
    mysqli_query($connection, "ROLLBACK");
}

function getEuroDate($date){
	$yy = substr($date, 0,4);
	$mm = substr($date, 5,2);
	$dd = substr($date, 8,2);

	return $dd . "." . $mm . "." . $yy; 
}

function fixExcelIndex($letterOrIndex){//for Unoversal Importer
    $conbersionTable = [];
    
    $letters = range('A', 'Z');
    
    $index = 0;
    
    foreach ($letters as $one) {
        $conbersionTable["$one"] = $index;
        $conbersionTable["$index"] = $index;
        $index++;
    }
    
    foreach ($letters as $one) {
        foreach ($letters as $two) {        
            $conbersionTable["$one$two"] = $index;
            $conbersionTable["$index"] = $index;
            $index++;
        }
    }
    
    
    $fixed = $conbersionTable[$letterOrIndex];
    return $fixed;
}

//mb_internal_encoding("UTF-8");

//mb_internal_encoding("ISO-8859-1");
//mb_http_output("pass");
//mb_http_input("pass");
//$arr[0] = "pass";
//mb_regex_encoding("EUC-JP");

//ob_start("mb_output_handler");

//mb_internal_encoding("ISO-8859-1");
//mysql_set_charset('utf8');

