<?php

mysql_connect("", "", "") or die(mysql_error());
mysql_select_db("intoone_intoone") or die(mysql_error());

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
			$E95=$allStations[$j][1]=str_replace("-",0,$allStations[$j][1]);
			
			$allStations[$j][2]=trim($allPrices[1]->plaintext);
			$E98=$allStations[$j][2]=str_replace("-",0,$allStations[$j][2]);
			
			$allStations[$j][3]=trim($allPrices[2]->plaintext);
			$DD=$allStations[$j][3]=str_replace("-",0,$allStations[$j][3]);
			
			$allStations[$j][4]=trim($allPrices[3]->plaintext);
			$GAS=$allStations[$j][4]=str_replace("-",0,$allStations[$j][4]);
			
			$firma=$allStations[$j][5]=trim($nosaukums[0]->alt);
			//echo $adrese."   ".$allStations[$j][4]."<br />";
			//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>kverijs pa chetri..................////////////////////////////
			$queryValues=$queryValues."(
(SELECT Z_didzis_gas_markas.id from Z_didzis_gas_markas where Z_didzis_gas_markas.marka='E95'),
'$E95',
(SELECT Z_didzis_gas_stacijas.id from Z_didzis_gas_stacijas where Z_didzis_gas_stacijas.adrese='$adrese_' and Z_didzis_gas_stacijas.stacija='$firma'),
NOW()
),
(
(SELECT Z_didzis_gas_markas.id from Z_didzis_gas_markas where Z_didzis_gas_markas.marka='E98'),
'$E98',
(SELECT Z_didzis_gas_stacijas.id from Z_didzis_gas_stacijas where Z_didzis_gas_stacijas.adrese='$adrese_' and Z_didzis_gas_stacijas.stacija='$firma'),
NOW()
),
(
(SELECT Z_didzis_gas_markas.id from Z_didzis_gas_markas where Z_didzis_gas_markas.marka='Petrol'),
'$DD',
(SELECT Z_didzis_gas_stacijas.id from Z_didzis_gas_stacijas where Z_didzis_gas_stacijas.adrese='$adrese_' and Z_didzis_gas_stacijas.stacija='$firma'),
NOW()
),
(
(SELECT Z_didzis_gas_markas.id from Z_didzis_gas_markas where Z_didzis_gas_markas.marka='Gas'),
'$GAS',
(SELECT Z_didzis_gas_stacijas.id from Z_didzis_gas_stacijas where Z_didzis_gas_stacijas.adrese='$adrese_' and Z_didzis_gas_stacijas.stacija='$firma'),
NOW()
),
";
			//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>kverijs pa chetri..................////////////////////////////
			$j++;
			}

		}	
	$i++;
	}
}

$queryValues = substr($queryValues, 0, -3); 	

$queryValues= str_replace(")(","),\n(",$queryValues);

$queryValues = "INSERT INTO Z_didzis_gas(marka, cena, stacija, date)
values".
$queryValues;

$result = mysql_query($queryValues);
//echo "<br />".$queryValues;	

?>