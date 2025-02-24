<?php


class cenuApdeits
{


var $lastData;	//fetched last entries from db
var $newData;	//fetched current data from homepage www.gudriem.lv
var $dataForUpdate;	//calculated price changes - data array
var $addresses;	//unique address



function mySqlConnect($ip,$user,$password,$db) 
	{
		try {
			mysql_connect($ip, $user, $password); # or throw new Exception('cannot connect mysql.');
			mysql_select_db($db);// or throw new Exception('cannot select db.');
			print "connected successfully";
			}
		catch(Exception $e) {
			print "unable to connect db";
		}
	//function end
	}
	
function __construct() 
	{
    $this->lastData = array();
	$this->newData = array();
	$this->dataForUpdate = array();
	$this->addresses = array();
	
	}
	
	
function fetchPreviousData()
	{
		$lastDayQuery="SELECT  Z_didzis_gas_test.date,  Z_didzis_gas_test.cena, Z_didzis_gas_stacijas.stacija, Z_didzis_gas_stacijas.adrese, Z_didzis_gas_markas.marka, Z_didzis_gas_test.id
FROM Z_didzis_gas_test

LEFT JOIN Z_didzis_gas_stacijas 
ON Z_didzis_gas_stacijas.id=Z_didzis_gas_test.stacija

Left JOIN Z_didzis_gas_markas
on Z_didzis_gas_markas.id=Z_didzis_gas_test.marka

WHERE Z_didzis_gas_test.isLast='1'
";
		
		$result = mysql_query($lastDayQuery);// or throw new Exception('cannot make query.');
		if (!$result) {
		die('Invalid query: ' . mysql_error());
}
		$i=0;
		while ($row = mysql_fetch_array($result))
			{
				// $time = strtotime( $value[0] );												//jasalabo
				// $myDate = date( 'Y-m-d H:i:s', $time );
				// $this->lastData[$i][0]=$myDate;	//datums
				
				$this->lastData[$i][0]=$row[0];	//datums
				$this->lastData[$i][1]=$row[1];	//cena
				$this->lastData[$i][2]=$row[2];	//firma
				$this->lastData[$i][3]=$row[3];	//adrese
				$this->lastData[$i][4]=$row[4];	//marka
				$this->lastData[$i][5]=$row[5]; //id
				//echo $this->lastData[$i][5];
			  $i++;
			
			}
		
	//function end
	}
		
function fetchNewData()
	{
		include_once('simple_html_dom.php');
		$j=0;		//station ID
		$page=1;	//page counter
		$v=1;		//array with table count. previously defined just for entering the while loop.
		
		$ja=0;

		while (count($v)>0){
			$site="http://www.gudriem.lv/degviela?lng=lv&ci=&st=&co=&t=&fc=&o=1&p=$page"; 

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
				
				foreach ($w as $e){
					$adress= $e->find('span');
					$adreses= explode(",",$adress[0]->plaintext);
					
					$allPrices=$e->find('td[class=price]');
					
					$allDates=$e->find('td[class=date]');
					
					$nosaukums=$e->find('img');
		
					foreach ($adreses as $adrese){
						$adrese=str_replace("Adreses","",$adrese);			
						$adrese_=$allStations[$j][0]=trim($adrese);
						
						$this->addresses[$j][0]=$adrese_;
											
						$datums=trim($allDates[0]->plaintext);					
						//
						
						$allStations[$j][1]=trim($allPrices[0]->plaintext);
						$cenas[0]=$allStations[$j][1]=str_replace("-",0,$allStations[$j][1]);	//
											
						$allStations[$j][2]=trim($allPrices[1]->plaintext);
						$cenas[1]=$allStations[$j][2]=str_replace("-",0,$allStations[$j][2]);
						
						$allStations[$j][3]=trim($allPrices[2]->plaintext);
						$cenas[2]=$allStations[$j][3]=str_replace("-",0,$allStations[$j][3]);
						
						$allStations[$j][4]=trim($allPrices[3]->plaintext);
						$cenas[3]=$allStations[$j][4]=str_replace("-",0,$allStations[$j][4]);
							
						$firma=$allStations[$j][5]=trim($nosaukums[0]->alt);
						$this->addresses[$j][1]=$firma;
										
						for($i=0;$i<4;$i++)
							{
							$this->newData[$ja][0]=$datums;
							$this->newData[$ja][1]=$cenas[$i];
							$this->newData[$ja][2]=$firma;
							$this->newData[$ja][3]=$adrese_;
							
							switch($i)
								{
								case 0:
									$this->newData[$ja][4]="E95";	//marka
									break;
								case 1:
									$this->newData[$ja][4]="E98";	//marka
									break;
								case 2:
									$this->newData[$ja][4]="Diesel";	//marka
									break;
								case 3:
									$this->newData[$ja][4]="Gas";	//marka
									break;
								}
							$ja++;
							}
						
						$j++;
						}

				}	
			$page++;
			}
		}
		
		//die;
		
		
	//function end
	}
		


function in_array_r($needle, $haystack) 
	{
		foreach ($haystack as $item) 
			{
			if ($item === $needle || (is_array($item) && $this->in_array_r($needle, $item))) {
				return true;
			}
		}
		return false;
	//function end
	}	

function changes()
		{
		$i=0;
		$newMarka=0;
		$in=0;
		foreach ($this->newData as $new)
			{
			$newStationName=$this->newData[$i][2];
			$newAddress=$this->newData[$i][3];
			$newPrice=$this->newData[$i][1];
			$newMarka=$this->newData[$i][4];
			$newDate=$this->newData[$i][0];
			
			
			
			$isAddress=$this->in_array_r($newAddress,$this->lastData) ? true : false;	//is new address in old array?
			$isName=$this->in_array_r($newStationName,$this->lastData) ? true : false;	//is new name in old array?
			
			if ($isAddress and $isName)													//if both is, then return key or loop through whole array?
				{
					$k=0;
					foreach ($this->lastData as $lastDati)
						{																//each time loop through lastData array looking for
							if($lastDati[2]==$newStationName and $lastDati[3]==$newAddress and $lastDati[4]==$newMarka)
								{
									//echo $lastDati[3]."<br>\n	";
									if ($lastDati[1]<>$newPrice)
									{
										//echo $i." ".$newData[$i][0]." ".$lastDati[1]." ".$lastDati[3]." ".$lastDati[4]." ".$newPrice." ".$newStationName." ".$newAddress." ".$newMarka." ".$newDate."<br/>";
										$this->dataForUpdate[$in][0]= $newDate;	//datums
										$this->dataForUpdate[$in][1]= $newPrice;	//cena
										$this->dataForUpdate[$in][2]= $newStationName;	//firma
										$this->dataForUpdate[$in][3]= $newAddress;	//adrese
										$this->dataForUpdate[$in][4]= $newMarka;	//marka
										$this->dataForUpdate[$in][5]= $lastDati[5];		//id which becames "old") 
										//echo $lastDati[5];
										$in++;
									}
								}
						$k++;
						}
				}

			$i=$i+1;
			}
		//function end
		}
		
function updateDb()
	{
		//echo count($this->dataForUpdate);
		$i=0;
		foreach ($this->dataForUpdate as $value)
			{
			$time = strtotime( $value[0] );
			$myDate = date( 'Y-m-d H:i:s', $time );
			
			
			//echo $myDate;
			$kverijs_insert[$i]="
			(
(SELECT Z_didzis_gas_markas.id from Z_didzis_gas_markas where Z_didzis_gas_markas.marka='$value[4]'),
'$value[1]',
(SELECT Z_didzis_gas_stacijas.id from Z_didzis_gas_stacijas where Z_didzis_gas_stacijas.adrese='$value[3]' and Z_didzis_gas_stacijas.stacija='$value[2]'),
'$myDate',
'1'
)";			
			$kverijs_update[$i]="($value[5])";


			//echo $kverijs[$i];
			$i++;
			}
		if (count($kverijs_insert)>0)
			{
				$kverijs_insert=implode(",",$kverijs_insert);
				$kverijs_insert = "INSERT INTO Z_didzis_gas_test(marka, cena, stacija, date, isLast)values".$kverijs_insert;
				//echo $kverijs_insert;
				
				$kverijs_update=implode(",",$kverijs_update);
				$kverijs_update="INSERT INTO Z_didzis_gas_test(id)values".$kverijs_update."
ON DUPLICATE KEY UPDATE
     isLast=0";
				
				//echo $kverijs_insert;
			$result = mysql_query($kverijs_insert);
			$result = mysql_query($kverijs_update);
			}
		
	//function end
	}

function createNewStations()
	{
	$oldStations=array();
	$query="select * from Z_didzis_gas_stacijas";
	$result = mysql_query($query);
	
	$i=0;
	while ($row = mysql_fetch_array($result))
			{
			$oldStations[$i]=$row[1].",".$row[2];
			$i++;
			}
	
	$i=0; 								//reset $i
	foreach($this->addresses as $stacija)
		{
			$newStations[$i]=$stacija[1].",".$stacija[0];
			$i++;
		}
	
																			//echo count($newStations);
	$i=0;
	$i_p=0;
	foreach ($newStations as $stacija_)
		{
		
		if (!(in_array($stacija_,$oldStations)))

			{
			$stacija=explode(",",$stacija_);
																//creates second query for inserting new stations 
			$kverijs[$i]="(													
'$stacija[0]',
'$stacija[1]')			
";
			
			foreach ($this->newData as $allNewData)								//creates first query for inserting prices in new stations first time
				{
				if ($allNewData[2]==$stacija[0] and $allNewData[3]==$stacija[1])
					{
					$time = strtotime( $allNewData[0] );
					$myDate = date( 'Y-m-d H:i:s', $time );
					$kverijs_prices[$i_p]="(
(SELECT Z_didzis_gas_markas.id from Z_didzis_gas_markas where Z_didzis_gas_markas.marka='$allNewData[4]'),
'$allNewData[1]',
(SELECT Z_didzis_gas_stacijas.id from Z_didzis_gas_stacijas where Z_didzis_gas_stacijas.adrese='$allNewData[3]' and Z_didzis_gas_stacijas.stacija='$allNewData[2]'),
'$myDate',
'1'
)";
					$i_p++;
					}
				}
			
			$i++;
			}
		}
	if (count($kverijs)>0 and count($kverijs_prices)>0)
		{
		$kverijs=implode(",",$kverijs);

		$kverijs="INSERT INTO Z_didzis_gas_stacijas(stacija, adrese)values".$kverijs;
		
		$kverijs_prices=implode(",",$kverijs_prices);

		$kverijs_prices= "INSERT INTO Z_didzis_gas_test(marka, cena, stacija, date, isLast)values".$kverijs_prices;
		//echo $kverijs_prices;
		$result = mysql_query($kverijs);
		$result = mysql_query($kverijs_prices);
		
		
		
		
		
		
		}
	//function end
	}

function asd()
	{
		foreach ($this->dataForUpdate as $value)
			{
			
			echo $value[0]." ".$value[1]." ".$value[2]." ".$value[3]." ".$value[4]." ".$value[5]." ".$value[6]."<br/>";	
	
			}
			
	//function end
	}
//class end		
}


$ip="";
$user="";
$password="";
$db="";


$apdeits = new cenuapdeits();
$apdeits-> mySqlConnect($ip,$user,$password,$db);
$apdeits->fetchPreviousData();
$apdeits->fetchNewData();
$apdeits->createNewStations();
$apdeits->changes();

//$apdeits->asd();
$apdeits->updateDb();

?>