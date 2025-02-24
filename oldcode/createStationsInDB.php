<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />


<?php

mysql_connect("", "", "") or die(mysql_error());
mysql_select_db("") or die(mysql_error());

include_once('simple_html_dom.php');

$i=1;
$v=1;
$j=0;	

while (count($v)>0){
$site="http://www.gudriem.lv/degviela?lng=lv&ci=&st=&co=&t=&fc=&o=1&p=$i"; 
//$site="http://www.gudriem.lv/degviela?lng=lv&ci=&st=&co=&t=&fc=2&o=1&p=$i";

$html= file_get_html($site);
$v = $html->find('table[id=tresult]');

$skaits=count($v);

if ($skaits>0) {
	foreach($v[0]->find('script,a') as $e) {
		$e->outertext= '';
		}
		

	foreach ($v[0]->find('span') as $e){
		$e->outertext= "<span>".$e->plaintext."</span>" ;
		}
		

	foreach($v[0]->find('div[class=tooltip_small]') as $e) {
		$e->style= '';
		}
	
 
	$w = $v[0]->find('tr');
	
	array_shift($w);
	//echo count($w)."<br>";
	

	foreach ($w as $e){
		$adress= $e->find('span');
		$adreses= explode(",",$adress[0]->plaintext);
		
		$allPrices=$e->find('td[class=price]');
		
		$nosaukums=$e->find('img');
		
		
		
		
		foreach ($adreses as $adrese){
			$adrese=str_replace("Adreses","",$adrese);			
			$adrese_=$allStations[$j][0]=trim($adrese);
			$allStations[$j][1]=trim($allPrices[0]->plaintext);
			$allStations[$j][1]=str_replace("-",0,$allStations[$j][1]);
			$allStations[$j][2]=trim($allPrices[1]->plaintext);
			$allStations[$j][2]=str_replace("-",0,$allStations[$j][2]);
			$allStations[$j][3]=trim($allPrices[2]->plaintext);
			$allStations[$j][3]=str_replace("-",0,$allStations[$j][3]);
			$allStations[$j][4]=trim($allPrices[3]->plaintext);
			$allStations[$j][4]=str_replace("-",0,$allStations[$j][4]);
			$nosaukums_=$allStations[$j][5]=trim($nosaukums[0]->alt);


			
			$queryValues=$queryValues."
('$nosaukums_',
'$adrese_'),";
			$j++;
		}
		
	}
	$i++;
	}
}

$queryValues= str_replace(")(","),\n(",$queryValues);
$queryValues = substr($queryValues, 0, -1);

$queryValues = "INSERT INTO Z_didzis_gas_stacijas(stacija,adrese)
values".
$queryValues;

echo $queryValues;
$result = mysql_query($queryValues);	


// $testQuery="INSERT INTO Z_didzis_gas_stacijas(stacija,adrese)
// values
// (
// 'Trest',
// 'Dārzciema iela 127'
// ),
// (
// 'kkas',
// 'citur'
// ),
// (
// 'Ahjk',
// 'yui'
// ),
// (
// 'Trest',
// 'Dārzciema iela 128'
// )";
// $result = mysql_query($testQuery);	
?>