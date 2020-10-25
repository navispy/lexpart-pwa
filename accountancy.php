<?php


function getRowcount($text, $width=55) {
    $rc = 0;
    $line = explode("\n", $text);
    foreach($line as $source) {
        $rc += intval((strlen($source) / $width) +1);
    }
    return $rc;
}

function __rus($num) {
    return iconv('UTF-8', 'windows-1251', $num);
}

function num2str($num) {
	$nul='ноль';
	$ten=array(
		array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
		array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
	);
	$a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
	$tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
	$hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
	$unit=array( // Units
		array('копейка' ,'копейки' ,'копеек',	 1),
		array('белорусский рубль'   ,'белорусского рубля'   ,'белорусских рублей'    ,0),
		array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
		array('миллион' ,'миллиона','миллионов' ,0),
		array('миллиард','милиарда','миллиардов',0),
	);
	//
	list($rub,$kop) = explode('.',sprintf("%015.2f", floatval($num)));
	$out = array();
	if (intval($rub)>0) {
		foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
			if (!intval($v)) continue;
			$uk = sizeof($unit)-$uk-1; // unit key
			$gender = $unit[$uk][3];
			list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
			// mega-logic
			$out[] = $hundred[$i1]; # 1xx-9xx
			if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
			else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
			// units without rub & kop
			if ($uk>1) $out[]= morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
		} //foreach
	}
	else $out[] = $nul;
	$out[] = morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
	$out[] = $kop.' '.morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
	return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
}

/**
 * Склоняем словоформу
 * @ author runcore
 */
function morph($n, $f1, $f2, $f5) {
	$n = abs(intval($n)) % 100;
	if ($n>10 && $n<20) return $f5;
	$n = $n % 10;
	if ($n>1 && $n<5) return $f2;
	if ($n==1) return $f1;
	return $f5;
}

function registerRec($Dt, $D1, $D2, $D3, $Ct, $C1, $C2, $C3, $QTY, $SUM){
    $Dt = str_replace("'", "", $Dt);
    $D1 = str_replace("'", "", $D1);
    $D2 = str_replace("'", "", $D2);
    $D3 = str_replace("'", "", $D3);
    $Ct = str_replace("'", "", $Ct);
    $C1 = str_replace("'", "", $C1);
    $C2 = str_replace("'", "", $C2);
    $C3 = str_replace("'", "", $C3);
    
    $query = "INSERT INTO fact_accountancy ( `Dt`, `D1`, `D2`, `D3`, `Ct`, `C1`, `C2`, `C3`, `QTY`, `SUM` ) "
            . "VALUES ('$Dt', '$D1', '$D2', '$D3', '$Ct', '$C1', '$C2', '$C3', $QTY, $SUM)";
    
    $sql = mysqli_query($connection, $query)
            or die(mysqli_error($connection));
}


function updateDocumentTotal($documentTypeID, $documentID, $totalField, $total){
    $query = "UPDATE __document$documentTypeID SET $totalField=$total WHERE ID=$documentID";
    
    $sql = mysqli_query($connection, $query)
            or die(mysqli_error($connection));
    
}
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

