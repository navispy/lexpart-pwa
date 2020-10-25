<?php

//header("Content-Type: text/html; charset=Windows-1251");

include('setup.php');
include('accountancy.php');


require_once 'phpdocx/classes/CreateDocx.inc';

define('FPDF_FONTPATH', 'ufpdf/font/');
include_once('ufpdf/ufpdf2.php');
////////////////////////////////////////////////////////////////////////////


date_default_timezone_set('Europe/London');

setupSchema('JurBot');

/** PHPExcel_IOFactory */
require_once 'PHPExcel/Classes/PHPExcel/IOFactory.php';

function saveDocument(&$values) {
    global $connection;

    $date = date("Y-m-d");
    $creditor = $values["Creditor"];
    $debtor = $values["Debtor"];

    $answer = $values["answer"];

    $document = "";
    $document = "Регистрация ООО";


    $documentID = $values["catalogPKValue"];

    $valuesJSON = json_encode($values);
    $valuesFixed = mysqli_real_escape_string($connection, $valuesJSON);

    if ($documentID == -1) {
        $query = "INSERT INTO __document33(`Date`,
			`Type`,
			`Creditor`,
			`Debtor`,
			`Answers`,
			`Document`) VALUES ('$date', '27', '$creditor', '$debtor', '$valuesFixed','$document')";
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

////////////////////////////////////////////////////////////////////////////

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Expires: " . date("r"));

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

function sendFilesToAlfaBank_1($values, $fileuuid) {
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
                'PAN_EXIST_PART' => 'Y', // У партнера есть УНП
                'PAN_PARTNERS' => '193399949', // УНП Партнера
                'PAN_EXIST' => 'N', // у клиента есть УНП
                'PAN' => '', // УНП клиента
                'NAME_COM_CL' => $values["Q_03"],//наименование компании
                'CONTACT_PERSON' => $values["Q_68"] . " " . $values["Q_69"], // Ф.И.О. контактного лица
                'CONTACT_PHONE' => $values["Q_90"], //Контактный телефон
                'DOP_INFO' => '', // Дополнительная информация
                'LOAD_FILE' => $cFile, // Прикрепить
                'I_AGREE' => 'Y') // Я согласен с условиями обработки данных
    );

// output the response
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);

    $content  = curl_exec($request); 

    curl_close($request);

return $content;
}

function sendFilesToAlfaBank_N($values, $fileuuid) {
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

function printApplication($values, $docName, $template) {
    $docID = $values["DocumentID"];

    $docx = new CreateDocxFromTemplate($template);
    fixValues($docx, $values);

    $options = array('parseLineBreaks' => true);

    $docx->replaceVariableByText($values, $options);
    $docx->createDocx("print/$docName$docID");
}

function printApplication00($values, $docName, $template) {
    $docID = $values["DocumentID"];

    $docx = new CreateDocxFromTemplate($template);
    fixValues($docx, $values);

    $options = array('parseLineBreaks' => true);

    $docx->replaceVariableByText($values, $options);
    $docx->createDocx("print/$docName$docID");
}

function printCharter($values, $docName, $template) {
    //- $Q_25$ $Q_26$ $Q_27$, $Q_30$ $Q_31$, выдан $Q_32$, орган выдачи: $Q33$ действителен до  $Q_35$  , идентификационный № $Q_34$, место регистрации: $Q_36$ $Q_37$ $Q_38$  $Q_39$$Q_40$$Q_41$$Q_42$$Q_43$ $Q_44$$Q_45$ ;
    //-  $Q_48$ регистрационный номер $Q_49$, $Q_50$ $Q_51$ $Q_52$ $Q_53$ $Q_54$ $Q_55$ $Q_56$  $Q_57$ $Q_58$ $Q_59$ $Q_60$

    $docID = $values["DocumentID"];

    $docx = new CreateDocxFromTemplate($template);
    $options = array('parseLineBreaks' => true);

////////////////////////////////////////////////////
    $individualQuestionIDs = ["Q_24", "Q_25", "Q_26", "Q_27", "Q_28", "Q_29", "Q_30", "Q_31", "Q_32", "Q_33", "Q_34", "Q_35", "Q_36", "Q_37", "Q_38", "Q_39", "Q_40", "Q_41", "Q_42", "Q_43", "Q_44", "Q_45", "Q_46", "Q_47", "Q_47A", "Q_47B", "Q_47C"];

    $num = floatval($values["pq01"]);
    $recs = [];
    $shares = [];
    $breakdwn = [];
    for ($i = 1; $i <= $num; $i++) {
        //foreach($individualQuestionIDs as $id){
        //logMessage("test909.txt", json_encode($id));	
        $Q_24 = $values["Q_24_" . $i];
        $Q_25 = $values["Q_25_" . $i];
        $Q_26 = $values["Q_26_" . $i];
        $Q_27 = $values["Q_27_" . $i];
        $Q_28 = $values["Q_28_" . $i];
        $Q_29 = $values["Q_29_" . $i];
        $Q_30 = $values["Q_30_" . $i];
        $Q_31 = $values["Q_31_" . $i];
        $Q_32 = $values["Q_32_" . $i];
        $Q_33 = $values["Q_33_" . $i];
        $Q_34 = $values["Q_34_" . $i];
        $Q_35 = $values["Q_35_" . $i];
        $Q_36 = $values["Q_36_" . $i];
        $Q_37 = $values["Q_37_" . $i];
        $Q_38 = $values["Q_38_" . $i];
        $Q_39 = $values["Q_39_" . $i];
        $Q_40 = $values["Q_40_" . $i];
        $Q_41 = $values["Q_41_" . $i];
        $Q_42 = $values["Q_42_" . $i];
        $Q_43 = $values["Q_43_" . $i];
        $Q_44 = $values["Q_44_" . $i] == null ? "" : $values["Q_44_" . $i];
        $Q_45 = $values["Q_45_" . $i];
        $Q_46 = $values["Q_46_" . $i];
        $Q_47 = $values["Q_47_" . $i];
        $Q_47A = $values["Q_47A_" . $i];
        $Q_47B = $values["Q_47B_" . $i];
        $Q_47C = $values["Q_47C_" . $i];

        $capital = $Q_47A + $Q_47B;
        $strCapital = num2str($capital);

        $str47A = num2str($Q_47A);
        $str47B = num2str($Q_47B);


        $address = $values["Q_NATURALFOUNDERADDRESS_$i"];

        $individual = "- " . $Q_25 . " " . $Q_26 . " " . $Q_27 . ", " . $Q_30 . " " . $Q_31 . ", выдан " . $Q_32 . ", орган выдачи: " . $Q_33 . " действителен до " . $Q_35 . ", идентификационный № " . $Q_34 . ", место регистрации: $address";

        $share = "$Q_25 $Q_26 $Q_27 - $Q_47C %, стоимостью  $capital ( $strCapital);";

        $detail = "$Q_25 $Q_26 $Q_27 - вклад в денежной форме в размере $Q_47A  ( $str47A ), в неденежной форме $Q_47B ( $str47B );";


        $recs[] = array("individual" => $individual);
        $shares[] = array("individual_share" => $share);
        $breakdown[] = array("capital_breakdown" => $detail);
        //}
    }



    $docx->replaceTableVariable($recs);
    $docx->replaceTableVariable($shares);
    $docx->replaceTableVariable($breakdown);

    fixValues($docx, $values);
    $docx->replaceVariableByText($values, $options);

////////////////////////////////////////////////////	



    $docx->createDocx("print/$docName$docID");
}

function printCharter00($values, $docName, $template) {

    $docID = $values["DocumentID"];

    $docx = new CreateDocxFromTemplate($template);
    fixValues($docx, $values);

    $options = array('parseLineBreaks' => true);

    $docx->replaceVariableByText($values, $options);
    ////////////////////////////////////////////////
    $num = floatval($values["pq01"]);

    if ($num == 0) {
        $docx->deleteTemplateBlock("INDIVIDUAL");
    }

    $num = floatval($values["pq02"]);

    if ($num == 0) {
        $docx->deleteTemplateBlock("ENTITY");
    }

    $docx->replaceVariableByText(["BLOCK_INDIVIDUAL" => ""], $options);
    $docx->replaceVariableByText(["BLOCK_ENTITY" => ""], $options);

    ////////////////////////////////////////////////


    $docx->createDocx("print/$docName$docID");
}

function printContract($values, $docName, $template) {
    $docID = $values["DocumentID"];

    $docx = new CreateDocxFromTemplate($template);
    fixValues($docx, $values);

    $options = array('parseLineBreaks' => true);

    $docx->replaceVariableByText($values, $options);
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

function printOrder($values, $docName, $template) {
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

function printPageA($values, $docName, $template) {
    $individualQuestionIDs = ["Q_24", "Q_25", "Q_26", "Q_27", "Q_28", "Q_29", "Q_30", "Q_31", "Q_32", "Q_33", "Q_34", "Q_35", "Q_36", "Q_37", "Q_38", "Q_39", "Q_40", 
    "Q_41",
    "Q_42",
    "Q_43", "Q_44", "Q_45", "Q_46", "Q_47", "Q_47A", "Q_47B", "Q_47C"];

    $num = floatval($values["pq01"]);

    if ($num <= 0) {
        return;
    }

    $docID = $values["DocumentID"];

    for ($i = 1; $i <= $num; $i++) {
        foreach ($individualQuestionIDs as $id) {

            $values[$id] = $values[$id . "_$i"];
        }
 
        $values["Q_41_city_type"] = $values["Q_41_$i" . "_city_type"];
        $values["Q_41_city"] = $values["Q_41_$i" . "_city"];

        $values["Q_42_street_type"] = $values["Q_42_$i" . "_street_type"];
        $values["Q_42_street"] = $values["Q_42_$i" . "_street"];

        $total = $values["Q_47A"] + $values["Q_47B"];
        $values["total"] = number_format($total, 2, ".", " ");

        $phone01 = $values["Q_46"];
        $phoneBreakdown01 = getPhoneBreakDown($phone01);

        $values["Q_46_Code"] = $phoneBreakdown01[0];
        $values["Q_46_Number"] = $phoneBreakdown01[1];        

        $docx = new CreateDocxFromTemplate($template);
        fixValues($docx, $values);

        $options = array('parseLineBreaks' => true);

        $docx->replaceVariableByText($values, $options);
        $docx->createDocx("print/$docName$docID-$i");
    }
}

function printPageA00($values, $docName, $template) {
    $individualQuestionIDs = ["Q_24", "Q_25", "Q_26", "Q_27", "Q_28", "Q_29", "Q_30", "Q_31", "Q_32", "Q_33", "Q_34", "Q_35", "Q_36", "Q_37", "Q_38", "Q_39", "Q_40", "Q_41", "Q_42", "Q_43", "Q_44", "Q_45", "Q_46", "Q_47", "Q_47A", "Q_47B", "Q_47C"];

    $num = floatval($values["pq01"]);

    if ($num <= 0) {
        return;
    }

    $docID = $values["DocumentID"];

    //for ($i = 1; $i <= $num; $i++) {
        //foreach ($individualQuestionIDs as $id) {

            //$values[$id] = $values[$id . "_$i"];
        //}

        $i=1;
        $values["total"] = $values["Q_47A"] + $values["Q_47B"];

        $docx = new CreateDocxFromTemplate($template);
        fixValues($docx, $values);

        $options = array('parseLineBreaks' => true);

        $docx->replaceVariableByText($values, $options);
        $docx->createDocx("print/$docName$docID-$i");
    //}
}

function printPageB($values, $docName, $template) {
    $entityQuestionsIDs = ["Q_48", "Q_49", "Q_50", "Q_51", "Q_52", "Q_53", "Q_54", "Q_55", "Q_56", "Q_57", "Q_58", "Q_59", "Q_60", "Q_61", "Q_62", "Q_63", "Q_64", "Q_64A"];

    $num = floatval($values["pq02"]);

    if ($num <= 0) {
        return;
    }

    $docID = $values["DocumentID"];

    for ($i = 1; $i <= $num; $i++) {
        foreach ($entityQuestionsIDs as $id) {
            $values[$id] = $values["$id_$i"];
        }


        $docx = new CreateDocxFromTemplate($template);
        fixValues($docx, $values);

        $options = array('parseLineBreaks' => true);

        $docx->replaceVariableByText($values, $options);
        $docx->createDocx("print/$docName$docID-$i");
    }
}

function printPageB00($values, $docName, $template) {
    $entityQuestionsIDs = ["Q_48", "Q_49", "Q_50", "Q_51", "Q_52", "Q_53", "Q_54", "Q_55", "Q_56", "Q_57", "Q_58", "Q_59", "Q_60", "Q_61", "Q_62", "Q_63", "Q_64", "Q_64A"];

    $num = floatval($values["pq02"]);

    if ($num <= 0) {
        return;
    }

    $docID = $values["DocumentID"];

    //for ($i = 1; $i <= $num; $i++) {
        //foreach ($entityQuestionsIDs as $id) {
            //$values[$id] = $values["$id_$i"];
        //}

        $i=1;
        $docx = new CreateDocxFromTemplate($template);
        fixValues($docx, $values);

        $options = array('parseLineBreaks' => true);

        $docx->replaceVariableByText($values, $options);
        $docx->createDocx("print/$docName$docID-$i");
    //}
}

function printProtocol($values, $docName, $template) {
    $docID = $values["DocumentID"];

    $docx = new CreateDocxFromTemplate($template);
    fixValues($docx, $values);

    $options = array('parseLineBreaks' => true);

    $docx->replaceVariableByText($values, $options);

    $num = floatval($values["pq01"]);
    $recs = [];
    for ($i = 1; $i <= $num; $i++) {
        $Q_24 = $values["Q_24_" . $i];
        $Q_25 = $values["Q_25_" . $i];
        $Q_26 = $values["Q_26_" . $i];
        $Q_27 = $values["Q_27_" . $i];
        $Q_28 = $values["Q_28_" . $i];
        $Q_29 = $values["Q_29_" . $i];
        $Q_30 = $values["Q_30_" . $i];
        $Q_31 = $values["Q_31_" . $i];
        $Q_32 = $values["Q_32_" . $i];
        $Q_33 = $values["Q_33_" . $i];
        $Q_34 = $values["Q_34_" . $i];
        $Q_35 = $values["Q_35_" . $i];
        $Q_36 = $values["Q_36_" . $i];
        $Q_37 = $values["Q_37_" . $i];
        $Q_38 = $values["Q_38_" . $i];
        $Q_39 = $values["Q_39_" . $i];
        $Q_40 = $values["Q_40_" . $i];
        $Q_41 = $values["Q_41_" . $i];
        $Q_42 = $values["Q_42_" . $i];
        $Q_43 = $values["Q_43_" . $i];
        $Q_44 = $values["Q_44_" . $i];
        $Q_45 = $values["Q_45_" . $i];
        $Q_46 = $values["Q_46_" . $i];
        $Q_47 = $values["Q_47_" . $i];
        $Q_47A = $values["Q_47A_" . $i];
        $Q_47B = $values["Q_47B_" . $i];
        $Q_47C = $values["Q_47C_" . $i];

        $individual = "- " . $Q_25 . " " . $Q_26 . " " . $Q_27 . ", " . $Q_30 . " " . $Q_31 . ", выдан " . $Q_32 . ", орган выдачи: " . $Q_33 . " действителен до " . $Q_35 . ", идентификационный № " . $Q_34 . ", место регистрации: " . $Q_36 . " " . $Q_37 . " " . $Q_38 . " " . $Q_39 . " " . $Q_40 . " " . $Q_41 . " " . $Q_42 . " " . $Q_43 . " " . $Q_44 . " " . $Q_45 . ";";

        $recs[] = array("individual" => $individual);
    }



    $docx->replaceTableVariable($recs);


    $docx->createDocx("print/$docName$docID");
}

function printDecision00($values, $docName, $template) {
    $docID = $values["DocumentID"];

    $docx = new CreateDocxFromTemplate($template);
    fixValues($docx, $values);

    $options = array('parseLineBreaks' => true);

    $docx->replaceVariableByText($values, $options);

    $num = floatval($values["pq01"]);
    $recs = [];
    //for ($i = 1; $i <= $num; $i++) {
        $Q_24 = $values["Q_24"];
        $Q_25 = $values["Q_25"];
        $Q_26 = $values["Q_26"];
        $Q_27 = $values["Q_27"];
        $Q_28 = $values["Q_2"];
        $Q_29 = $values["Q_29"];
        $Q_30 = $values["Q_30"];
        $Q_31 = $values["Q_31"];
        $Q_32 = $values["Q_32"];
        $Q_33 = $values["Q_33"];
        $Q_34 = $values["Q_34"];
        $Q_35 = $values["Q_35"];
        $Q_36 = $values["Q_36"];
        $Q_37 = $values["Q_37"];
        $Q_38 = $values["Q_38"];
        $Q_39 = $values["Q_39"];
        $Q_40 = $values["Q_40"];
        $Q_41 = $values["Q_41"];
        $Q_42 = $values["Q_42"];
        $Q_43 = $values["Q_43"];
        $Q_44 = $values["Q_44"];
        $Q_45 = $values["Q_45"];
        $Q_46 = $values["Q_46"];
        $Q_47 = $values["Q_47"];
        $Q_47A = $values["Q_47A"];
        $Q_47B = $values["Q_47B"];
        $Q_47C = $values["Q_47C"];

        $individual = "- " . $Q_25 . " " . $Q_26 . " " . $Q_27 . ", " . $Q_30 . " " . $Q_31 . ", выдан " . $Q_32 . ", орган выдачи: " . $Q_33 . " действителен до " . $Q_35 . ", идентификационный № " . $Q_34 . ", место регистрации: " . $Q_36 . " " . $Q_37 . " " . $Q_38 . " " . $Q_39 . " " . $Q_40 . " " . $Q_41 . " " . $Q_42 . " " . $Q_43 . " " . $Q_44 . " " . $Q_45 . ";";

        $recs[] = array("individual" => $individual);
    //}

    $docx->replaceTableVariable($recs);
    $docx->createDocx("print/$docName$docID");
}

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

function onPrint00($values, $fileuuid) {

    global $connection;

    $docID = $values["DocumentID"];


    $phone01 = $values["Q_46"];
    $phone02 = $values["Q_90"];

    $phone03 = $values["Q_18"];

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

    $templates = ["files/RegLLC00/application00 ver 2.docx",
        "files/RegLLC00/charter00.docx",
        "files/RegLLC00/contract00.docx",
        "files/RegLLC00/order00.docx",
        "files/RegLLC00/pageA00.docx",
        "files/RegLLC00/pageB00.docx",
        "files/RegLLC00/decision00.docx"];

    printApplication00($values, "application", $templates[0]);
    printCharter00($values, "charter", $templates[1]);
    printContract00($values, "contract", $templates[2]);
    printOrder00($values, "order", $templates[3]);
    printPageA00($values, "pageA", $templates[4]);
    printPageB00($values, "pageB", $templates[5]);
    printDecision00($values, "decision", $templates[6]);

    $num1 = floatval($values["pq01"]);
    $num2 = floatval($values["pq02"]);


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

        for ($i = 1; $i <= $num1; $i++) {
            $zip->addFile("print/pageA$docID-$i.docx", "page A ($i).docx");
        }

        for ($j = 1; $j <= $num2; $j++) {
            $zip->addFile("print/pageB$docID-$j.docx", "page B ($j).docx");
        }


        // All files are added, so close the zip file.
        $zip->close();
    }
}

function onPrint10($values, $fileuuid) {

    global $connection;

    $docID = $values["DocumentID"];


    //$phone01 = $values["Q_46"];
    $phone02 = $values["Q_90"];
    $phone03 = $values["Q_18"];

//$phoneBreakdown01 = getPhoneBreakDown($phone01);
$phoneBreakdown02 = getPhoneBreakDown($phone02);
$phoneBreakdown03 = getPhoneBreakDown($phone03);

//$values["Q_46_Code"] = $phoneBreakdown01[0];
//$values["Q_46_Number"] = $phoneBreakdown01[1];

$values["Q_90_Code"] = $phoneBreakdown02[0];
$values["Q_90_Number"] =$phoneBreakdown02[1];

$values["Q_18_Code"] = $phoneBreakdown03[0];
$values["Q_18_Number"] =$phoneBreakdown03[1];    

    //$capital = $values["capital"];
    //$values["strCapital"] = num2str($capital);

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


    $templates = ["files/RegLLC10/application ver 2.docx",
        "files/RegLLC10/charter ver 2.docx",
        "files/RegLLC10/contract.docx",
        "files/RegLLC10/order.docx",
        "files/RegLLC10/pageA ver 2.docx",
        "files/RegLLC10/pageB.docx",
        "files/RegLLC10/protocol ver 2.docx"];

    printApplication($values, "application", $templates[0]);
    printCharter($values, "charter", $templates[1]);
    printContract($values, "contract", $templates[2]);
    printOrder($values, "order", $templates[3]);
    printPageA($values, "pageA", $templates[4]);
    printPageB($values, "pageB", $templates[5]);
    printProtocol($values, "protocol", $templates[6]);

    $num1 = floatval($values["pq01"]);
    $num2 = floatval($values["pq02"]);


    ////////////////////////////////////////////////////////
    $zip = new ZipArchive;
    if ($zip->open("print/$fileuuid.zip", ZipArchive::CREATE) === TRUE) {
        // Add files to the zip file
        $zip->addFile("files/Памятка.docx", "!!! memo.docx");
        $zip->addFile("print/application$docID.docx", "application.docx");
        $zip->addFile("print/charter$docID.docx", "charter.docx");
        $zip->addFile("print/contract$docID.docx", "contract.docx");
        $zip->addFile("print/order$docID.docx", "order.docx");
        $zip->addFile("print/protocol$docID.docx", "protocol.docx");

        for ($i = 1; $i <= $num1; $i++) {
            $zip->addFile("print/pageA$docID-$i.docx", "page A ($i).docx");
        }

        for ($j = 1; $j <= $num2; $j++) {
            $zip->addFile("print/pageB$docID-$j.docx", "page B ($j).docx");
        }


        // All files are added, so close the zip file.
        $zip->close();
    }
}

function onPrint($values, $fileuuid) {

    saveDocument($values);
    $numIndividuals = floatval($values["pq01"]);
    $numEntities = floatval($values["pq02"]);
    $totalFounders = $numIndividuals + $numEntities;

    if ($totalFounders > 1) {
        onPrint10($values, $fileuuid);
        //sendFilesToAlfaBank_N($values, $fileuuid);        
    } else {
        onPrint00($values, $fileuuid);
        $content = [];//sendFilesToAlfaBank_1($values, $fileuuid);        
    }

    return $content;
}



$content = onPrint($values, $fileuuid);

//$i = getVal();

$serverName = $_SERVER['SERVER_NAME'];
$scriptName = $_SERVER["SCRIPT_NAME"];

echo('{"calcTime":' . json_encode($calcTime) . ',');
echo('"serverName":' . json_encode($serverName) . ',');
echo('"scriptName":' . json_encode($scriptName) . ',');
echo('"content":' . json_encode($content) . '}');

?>

