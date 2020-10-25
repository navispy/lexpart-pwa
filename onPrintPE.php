<?php

//header("Content-Type: text/html; charset=Windows-1251");



include('setup.php');
include('accountancy.php');


require_once 'phpdocx/classes/CreateDocx.inc';

define('FPDF_FONTPATH', 'ufpdf/font/');
include_once('ufpdf/ufpdf2.php');
////////////////////////////////////////////////////////////////////////////


date_default_timezone_set('Europe/London');


/** PHPExcel_IOFactory */
require_once 'PHPExcel/Classes/PHPExcel/IOFactory.php';


////////////////////////////////////////////////////////////////////////////

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Expires: " . date("r"));

setupSchema('JurBot');
//include('code.php');
$calcTime = $_POST['calcTime'];
$script = rawurldecode($_POST['registerScript']);

$catalogItemValuesJSON = $_POST['catalogItemValues'];
$valuesJSON = $_POST['values'];

$messagesJSON = $_POST['messages'];



$documentID = $_POST['documentID'];
$schemaID = $_POST['schemaID'];

$catalogPKField = $_POST['catalogPKField'];
$catalogPKValue = $_POST['catalogPKValue'];

$fileuuid = $_POST['fileuuid'];

//setupSchema($schemaID);

$catalogItemValues = json_decode($catalogItemValuesJSON, true);
$values = json_decode($valuesJSON, true);

$messages = json_decode($messagesJSON, true);

//logMessage("sky9001.txt", $documentID);
//$i=2;
function getPhoneBreakDown($strPhone){
    $breakdown = array();

    $fixed = "";
    $len = strlen($strPhone);
    for($i=0; $i<$len; $i++){
        $char = substr($strPhone, $i, 1);
        if(is_numeric($char)){
            $fixed .= $char;
        }
    }

    if(strlen($fixed) != 12){
        $breakdown = ["", ""];    
    } else {
        $code = substr($fixed, 0, 5);
        $number = substr($fixed, 5, 7);
        $breakdown = [$code, $number];
    }

    return $breakdown;
}

function saveDocument(&$values) {
    global $connection;

    $date = date("Y-m-d");
    $creditor = $values["Creditor"];
    $debtor = $values["Debtor"];

    $answer = $values["answer"];

    $document = "";
    $document = "Регистрация ЧУП";


    $documentID = $values["catalogPKValue"];

    $valuesJSON = json_encode($values);
    $valuesFixed = mysqli_real_escape_string($connection, $valuesJSON);

    if ($documentID == -1) {
        $query = "INSERT INTO __document33(`Date`,
			`Type`,
			`Creditor`,
			`Debtor`,
			`Answers`,
			`Document`) VALUES ('$date', '28', '$creditor', '$debtor', '$valuesFixed','$document')";
    } else {
        $query = "UPDATE __document33
	   		SET `Creditor` = '$creditor',
	 		`Debtor` = '$debtor',
			`Answers` = '$valuesFixed',
			`Document` = '$document'
	 		WHERE ID=$documentID";
    }

    $result = mysqli_query($connection, $query)
            or die(mysqli_error($connection));

    if ($documentID == -1) {
        $documentID = mysqli_insert_id($connection);
    }

    $values["DocumentID"] = $documentID;
}

function sendFilesToAlfaBank($values, $fileuuid) {
    $request = curl_init('https://alfa-biz.by/dunamic_pages/menu_4475.php');

    $cFile = curl_file_create("print/$fileuuid.zip");
// send a file
    curl_setopt($request, CURLOPT_POST, true);
//curl_setopt($request, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt(
            $request,
            CURLOPT_POSTFIELDS,
            array(
                'FORM_ACTION' => '/dunamic_pages/menu_4475.php',
                'bxajaxid' => '0665be17d108e7fc7021fe1e1852a8f0',
                'bxajaxid' => '0665be17d108e7fc7021fe1e1852a8f0',
                'CHECK_MODE' => '0',
                'ACTION_MODE' => 'DEFAULT',
                'PAN_EXIST_PART' => 'Y', // У партнера есть УН
                'PAN_PARTNERS' => '193399949', // УНП Партнера
                'PAN_EXIST' => 'N', // у клиента есть УНП
                'PAN' => '', // УНП клиента
                'CONTACT_PERSON' => $values["Q_68"] . " " . $values["Q_69"], // Ф.И.О. контактного лица
                'CONTACT_PHONE' => $values["Q_90"], //Контактный телефон
                'DOP_INFO' => '', // Дополнительная информация
                'LOAD_FILE' => $cFile, // Прикрепить
                'I_AGREE' => 'Y') // Я согласен с условиями обработки данных
    );

// output the response
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);

    curl_close($request);
}

function fixValues($docx, &$values) {

    $vars = $docx->getTemplateVariables();

    $docVars = $vars["document"];
    foreach ($docVars as $var) {
        $val = $values[$var];

        if (strpos($var, 'BLOCK_') !== false) {
            continue;
        }

        $values[$var] = $val == null ? "" : $val;
    }
}

function printApplication00($values, $docName, $template) {
    $docID = $values["DocumentID"];

    $docx = new CreateDocxFromTemplate($template);
    fixValues($docx, $values);

    $options = array('parseLineBreaks' => true);

    $docx->replaceVariableByText($values, $options);
    $docx->createDocx("print/$docName$docID");
}

function printCharter00($values, $docName, $template) {
    $docID = $values["DocumentID"];

    $docx = new CreateDocxFromTemplate($template);
    fixValues($docx, $values);

    $options = array('parseLineBreaks' => true);
    try {
        $docx->replaceVariableByText($values, $options);
    } catch (Exception $e) {

        logMessage("erika.txt", $e->getMessage());
    }

    $docx->createDocx("print/$docName$docID");
}

function printContract00($values, $docName, $template) {
    $docID = $values["DocumentID"];

    $docx = new CreateDocxFromTemplate($template);
    fixValues($docx, $values);

    $options = array('parseLineBreaks' => true);

    $docx->replaceVariableByText($values, $options);
    $docx->createDocx("print/$docName$docID");
}

function printOrder00($values, $docName, $template) {
    $docID = $values["DocumentID"];

    $docx = new CreateDocxFromTemplate($template);
    fixValues($docx, $values);

    $options = array('parseLineBreaks' => true);

    $docx->replaceVariableByText($values, $options);
    $docx->createDocx("print/$docName$docID");
}

function printPageA00($values, $docName, $template) {
    $docID = $values["DocumentID"];

    $docx = new CreateDocxFromTemplate($template);
    fixValues($docx, $values);

    $options = array('parseLineBreaks' => true);

    $docx->replaceVariableByText($values, $options);
    $docx->createDocx("print/$docName$docID");
}

function printPageB00($values, $docName, $template) {

    $docID = $values["DocumentID"];

    $docx = new CreateDocxFromTemplate($template);
    fixValues($docx, $values);

    $options = array('parseLineBreaks' => true);

    $docx->replaceVariableByText($values, $options);
    $docx->createDocx("print/$docName$docID");
}

function printDecision00($values, $docName, $template) {
    $docID = $values["DocumentID"];

    $docx = new CreateDocxFromTemplate($template);
    fixValues($docx, $values);

    $options = array('parseLineBreaks' => true);

    $docx->replaceVariableByText($values, $options);
    $docx->createDocx("print/$docName$docID");
}

function onPrint00($values, $fileuuid) {

    global $connection;

    $docID = $values["DocumentID"];

    //$founderType = $values["Q_23"];
    //$values["strCapital"] = num2str($capital);

    $phone01 = $values["Q_46"];
    $phone02 = $values["Q_90"];

    $phone03 = $values["Q_18"];


    //logMessage("20201014.txt", "$phone01 $phone02 $phone03");

$phoneBreakdown01 = getPhoneBreakDown($phone01);
$phoneBreakdown02 = getPhoneBreakDown($phone02);
$phoneBreakdown03 = getPhoneBreakDown($phone03);

$values["Q_46_Code"] = $phoneBreakdown01[0];
$values["Q_46_Number"] = $phoneBreakdown01[1];

$values["Q_90_Code"] = $phoneBreakdown02[0];
$values["Q_90_Number"] =$phoneBreakdown02[1];

$values["Q_18_Code"] = $phoneBreakdown03[0];
$values["Q_18_Number"] =$phoneBreakdown03[1];

    $values["capital"] = $values["Q_19"] + $values["Q_20"];

    $capital = $values["capital"];
    $values["strCapital"] = num2str($capital);

    $capital01 = $values["Q_19"];
    $values["strQ_19"] = num2str($capital01);

    $capital02 = $values["Q_20"];
    $values["strQ_20"] = num2str($capital02);

    $values["capitalFormatted"]     =  number_format($capital, 2, ".", " ");
    $values["capital01Formatted"]   =  number_format($capital01, 2, ".", " ");
    $values["capital02Formatted"]   =  number_format($capital02, 2, ".", " ");


    $templates = ["files/RegPE00/application ver 2.docx",
        "files/RegPE00/charter ver 2.docx",
        "files/RegPE00/contract.docx",
        "files/RegPE00/order.docx",
        "files/RegPE00/pageA.docx",
        "files/RegPE00/pageB.docx",
        "files/RegPE00/decision.docx"];

    printApplication00($values, "application", $templates[0]);

    printCharter00($values, "charter", $templates[1]);

    printContract00($values, "contract", $templates[2]);
    printOrder00($values, "order", $templates[3]);

    //if($founderType == "физ. лицо"){
    printPageA00($values, "pageA", $templates[4]);
    //} else if($founderType == "юр. лицо"){
    //printPageB00($values, "pageB", $templates[5]);
    //}
    printDecision00($values, "decision", $templates[6]);

    ////////////////////////////////////////////////////////
    $zip = new ZipArchive;
    if ($zip->open("print/$fileuuid.zip", ZipArchive::CREATE) === TRUE) {
        // Add files to the zip file
        $zip->addFile("files/Памятка.docx", "!!! memo.docx");
        $zip->addFile("print/application$docID.docx", "application.docx");
        $zip->addFile("print/charter$docID.docx", "charter.docx");
        $zip->addFile("print/contract$docID.docx", "contract.docx");
        $zip->addFile("print/order$docID.docx", "order.docx");
        $zip->addFile("print/decision$docID.docx", "decision.docx");

        //if ($founderType == "физ. лицо") {
        $zip->addFile("print/pageA$docID.docx", "page A.docx");
        //} else if ($founderType == "юр. лицо") {
        //    $zip->addFile("print/pageB$docID.docx", "Лист Б.docx");
        ///}
        // All files are added, so close the zip file.
        $zip->close();
    }
}

saveDocument($values);
onPrint00($values, $fileuuid);
//sendFilesToAlfaBank($values, $fileuuid);
//$i = getVal();

$serverName = $_SERVER['SERVER_NAME'];
$scriptName = $_SERVER["SCRIPT_NAME"];

echo('{"calcTime":' . json_encode($calcTime) . ',');
echo('"serverName":' . json_encode($serverName) . ',');
echo('"scriptName":' . json_encode($scriptName) . '}');
?>

