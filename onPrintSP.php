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
function saveDocument(&$values) {
    global $connection;

    $date = date("Y-m-d");
    $creditor = $values["Creditor"];
    $debtor = $values["Debtor"];

    $answer = $values["answer"];

    $document = "";
    $document = "Регистрация ИП";


    $documentID = $values["catalogPKValue"];

    $valuesJSON = json_encode($values);
    $valuesFixed = mysqli_real_escape_string($connection, $valuesJSON);

    if ($documentID == -1) {
        $query = "INSERT INTO __document33(`Date`,
			`Type`,
			`Creditor`,
			`Debtor`,
			`Answers`,
			`Document`) VALUES ('$date', '29', '$creditor', '$debtor', '$valuesFixed','$document')";
    } else {
        $query = "UPDATE __document33
	   		SET `Creditor` = '$creditor',
	 		`Debtor` = '$debtor',
			`Answers` = '$valuesFixed',
			`Document` = '$document'
	 		WHERE ID=$documentID";
    }


//mysqli_set_charset($connection, 'utf8');

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
                'CONTACT_PERSON' => $values["Q_04"] . " " . $values["Q_05"], // Ф.И.О. контактного лица
                'CONTACT_PHONE' => $values["Q_25"], //Контактный телефон
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

function onPrint00($values, $fileuuid) {

    //global $connection;

    $docID = $values["DocumentID"];

    $capital = $values["capital"];
    $values["strCapital"] = num2str($capital);



    $capital01 = $values["Q_19"];
    $values["strQ_19"] = num2str($capital01);

    $capital02 = $values["Q_20"];
    $values["strQ_20"] = num2str($capital02);



    $templates = ["files/RegSP00/application00.docx"];

    printApplication00($values, "application", $templates[0]);


    ////////////////////////////////////////////////////////
    $zip = new ZipArchive;
    if ($zip->open("print/$fileuuid.zip", ZipArchive::CREATE) === TRUE) {
        // Add files to the zip file
        $zip->addFile("files/Памятка.docx", "!!! memo.docx");
        $zip->addFile("print/application$docID.docx", "application.docx");

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

