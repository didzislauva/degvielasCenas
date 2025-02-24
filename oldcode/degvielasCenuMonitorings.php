<?php
class degvielasCenuMonitorings
{
private $mysqlTable;
private $mysqlPassw;
private $mysqlHost;
private $mysqlDB;

public $marka;
public $adreses;
public $values;
public $popValues;
public $stationHeaders;


function __construct($marka,$adreses) 
	{  
	$this->mysqlUser="";
	$this->mysqlPassw="";
	$this->mysqlHost="";
	$this->mysqlDB="";
	$this->mysqlTable="";
	
	//clean user request
	$adreses=htmlspecialchars($adreses);	
	$marka=htmlspecialchars($marka);
	
	
	$this->marka=$marka;
	$this->adreses=explode(",",$adreses);
	$this->values = array();
	$this->popValues=array();
	$this->stationHeaders=array();
	$this->jqArray=array();
	$this->jqValidCount=0;
	
	}//ef


function mySqlConnect() 
	{

	$link =@mysql_connect($this->mysqlHost, $this->mysqlUser,$this->mysqlPassw);
	if ($link)
		{
		
		$link=mysql_select_db($this->mysqlDB);
		if ($link)
			{
	//sanitize user input for mysql
			$this->marka=mysql_real_escape_string($this->marka);
			$this->adreses[0]=mysql_real_escape_string($this->adreses[0]);
			$this->adreses[1]=mysql_real_escape_string($this->adreses[1]);
			return true;
			}
		else 
			{
		
			return false;
			}
		}
	else 
		{
		echo "<!-- ERROR MSG: Impossible to connect to mysql database. #39@dcm-->\n";
		return false;
		}
	}//ef

function getMarka()
	{
	return $this->marka;
	//ef
	}
	
function getAdreses()
	{	
	return $this->adreses;
	//ef
	}

function QueryDB()
	{
	$nosaukums=$this->adreses[0];
	$adrese=$this->adreses[1];
	$query="SELECT  `date`,  `cena`, Z_didzis_gas_stacijas.stacija, Z_didzis_gas_stacijas.adrese, Z_didzis_gas_markas.marka
FROM $this->mysqlTable

LEFT JOIN Z_didzis_gas_stacijas 
ON Z_didzis_gas_stacijas.id=Z_didzis_gas_test.stacija

Left JOIN Z_didzis_gas_markas
on Z_didzis_gas_markas.id=Z_didzis_gas_test.marka

where Z_didzis_gas_markas.marka='$this->marka' 
and Z_didzis_gas_stacijas.stacija='$nosaukums' 
and Z_didzis_gas_stacijas.adrese='$adrese'
and Z_didzis_gas_test.date> FROM_DAYS(TO_DAYS(NOW())-60)

order by date asc";
	@$result=mysql_query($query);
	
	
	while($row = @mysql_fetch_array($result))
		{
		//$row[0] datums
		//$row[1] cena
		//$row[2] nosaukums
		//$row[3] adrese
		//$row[4]
		//$this->values[$row[0]]=(double)$row[1];
		array_push($this->values,array($row[0],$row[1]));
		}
		
	}//ef

function daysBetweenDate($startDate,$endDate)
	{
		$now = strtotime($startDate);
		$your_date = strtotime($endDate);
		$datediff = abs($now - $your_date);	
		$daysBetween=round($datediff/(60*60*24));
		return $daysBetween;
	}//ef
	
function populateDaily()
	{

	if (count($this->values)>0)
		{
	
		for ($i=0; $i<=count($this->values)-2; $i++)
			{
			
			$days= $this->daysBetweenDate($this->values[$i][0],$this->values[$i+1][0]);
			
			///////////////////
			//
			// 0)iepusho i`tā mysql ieraksta vērtību konkrētajā laikā pilnajā listē.
			//
			///////////////////
			array_push($this->popValues,array($this->values[$i][0],$this->values[$i][1]));
			
			///////////////////
			//
			// 1)nosacījums, kas izveido starpdienu vērtības starp pirmo un pēdējo ierakstu (neieskaitot pēdējo). 
			// papildina pilno listi. nav taisno leņķu.
			//
			///////////////////
			if ($days>0)
				{
				$tDiena=strtotime($this->values[$i][0]);
				for ($j=0;$j<=$days-1;$j++)
					{
					$tDiena=$tDiena+86000;
					array_push($this->popValues,array(date("Y-m-d 00:00:00",$tDiena),$this->values[$i][1]));
					}
				}
			else
				{
				array_push($this->popValues,array($this->values[$i][0],$this->values[$i][1]));
				
				}//fi		
			}//rof
			
			//////////////
			//
			// 2)pievieno pēdējo mysql ierakstu pilnajā listē. Tas var būt nedēļu vecs kopš šī brīža.
			//
			//////////////
		
		array_push($this->popValues,array($this->values[count($this->values)-1][0],$this->values[count($this->values)-1][1])); //adding the last element in the populated array	
			
			///////////////
			//
			// 3)turpina papildināt pilnajā listē ikdienas vērtības līdz šim brīdim sākot no pēdējā mysql ieraksta.
			//
			///////////////
		
		$daysBetween= $this->daysBetweenDate($this->now(),$this->popValues[count($this->popValues)-1][0]);
		
		$tDiena=strtotime($this->popValues[count($this->popValues)-1][0]);
		
		for ($i=0;$i<$daysBetween;$i++)	//-1 because of we dont want the one before last point yesterday, but the last point - the current time with last value
			{
			$tDiena=$tDiena+86000;
			array_push($this->popValues,array(date("Y-m-d 00:00:00",$tDiena),$this->popValues[count($this->popValues)-1][1]));	
			}
		array_push($this->popValues,array(date("Y-m-d H:i:s",strtotime($this->now())),$this->popValues[count($this->popValues)-1][1]));
		
		$grupa=false;
		$innerCounter=0;
		
		///////////////////////
		// (4) IZVEIDO 3D masīvu $this->jqArray();
		// cikls, kurš sadala rindu pa grupām. sadalošais elements - nulle. Ja ir viena vai vairākas nulles, tās 
		// definē grupas robežas. rindā [0, 4, 5, 4, 5, 3, 0, 0, 0, 2, 3] tiek pārvērsta uz [[4,5,4,5,3],[2,3]]
		// 1) sagatavo masīvu priekš jqplot
		//
		///////////////////////
		foreach ($this->popValues as $value)		
			{
			if (!$value[1]==0)
				{
				if($grupa==false)	//first switch from false
					{
					$grupaN=$grupaN+1;
					}
				$grupa=true;
				$this->jqArray[$grupaN-1][$innerCounter][0]=$value[0];
				$this->jqArray[$grupaN-1][$innerCounter][1]=$value[1];
				$innerCounter=$innerCounter+1;
				$this->jqValidCount+=1;
				}
			else
				{
				$grupa=false;
				$innerCounter=0;
				} //fi	
			} //rof


			
		}		
	}//ef
	
function selectAll(){
		$this->marka=mysql_real_escape_string($this->marka);
		$this->adreses[0]=mysql_real_escape_string($this->adreses[0]);
		$this->adreses[1]=mysql_real_escape_string($this->adreses[1]);
		$name= $this->adreses[0];
		$address=$this->adreses[1];
	
		
		$query="SELECT  `date`,  `cena`, Z_didzis_gas_stacijas.stacija, Z_didzis_gas_stacijas.adrese, Z_didzis_gas_markas.marka
				FROM $this->mysqlTable

				LEFT JOIN Z_didzis_gas_stacijas 
				ON Z_didzis_gas_stacijas.id=Z_didzis_gas_test.stacija

				Left JOIN Z_didzis_gas_markas
				on Z_didzis_gas_markas.id=Z_didzis_gas_test.marka

				where Z_didzis_gas_markas.marka='$this->marka' 
				and Z_didzis_gas_stacijas.stacija='$name' 
				and Z_didzis_gas_stacijas.adrese='$address'
				

				order by date asc";
		
		$result=mysql_query($query);
		while($row = mysql_fetch_array($result))
		{
		//$row[0] datums
		//$row[1] cena
		//$row[2] nosaukums
		//$row[3] adrese
		//$row[4]
		//$this->values[$row[0]]=(double)$row[1];
		echo $row[0]."\t".$row[1]."\n";
		}
		
		
		}
	
	
function now() //wtf funkcija? :D
		{
		return date("Y-m-d H:i:s", time());  
		}
		
function createDataForJQPlot()
	{	
	/////////////////////////////////
	//
	// 1) uzraksta saģenerēto masīvu. principā, iepriekšējo varētu likt iekš $this->popValues metodes.
	//???????vajag salabot pārtrauktas datu rindas attēlošanu. vairākas series?
	//
	/////////////////////////////////
	if (count($this->jqArray)>0)
		{
		foreach ($this->jqArray as $array1)
			{
			
			//echo "[\n";
			foreach ($array1 as $array2)
				{
				echo "['".$array2[0]."', ". $array2[1]."],\n";
				//$this->validCount=+1;
				}
			//echo "]\n";
			}
		}

	}//ef


	
function populateAddresses()
	{
	
	$query="Select * from Z_didzis_gas_stacijas order by stacija,adrese asc";
	$result=mysql_query($query);
	//checks if query returns valid result
	if ($result)
		{
		while($row = mysql_fetch_array($result))
			{
			echo "<option>".$row[1].",".$row[2]."</option>\n\t\t";
			}
		}
	else
		{
		echo "<option>cannot load stations</option>";
		}
	}//ef

function populateAddresses2()
	{
		$query="Select * from Z_didzis_gas_stacijas
order by stacija,adrese asc";
	$result=mysql_query($query);
	
	$i=0;
	$j=0;
	while($row=mysql_fetch_array($result))
		{
		$currentFirma=$row[1]; //jaaapvieno ar regionu.
		if (($oldFirma!=$currentFirma) and ($i>0))
			{
			//echo "</div>\n<div class='adreses' id=adrese_".($j)." hidden style=\"position:absolute;top:280px;\>\n";
			//echo $row[1]."<br/>";
			//echo "<input type=\"checkbox\" name=\"adrese\" value=\"$row[1],$row[2],$row[3]\" />".$row[2]."<br/>\n";
			$oldFirma=$currentFirma;
			array_push($this->stationHeaders,array($row[1],$j));
			$i+=1;
			$j+=1;
			}
		elseif ($i==0)
			{
			
			//echo "<div class='adreses' id=adrese_".($j)." hidden style=\"position:absolute;top:250px;\">\n";
			//echo $row[1]."<br/>";
			//echo "<input type=\"checkbox\" name=\"adrese\" value=\"$row[1],$row[2],$row[3]\" />".$row[2]."<br/>\n";
			$oldFirma=$currentFirma;
			array_push($this->stationHeaders,array($row[1],$j)); //jaaapievieno regions
			$i+=1;
			$j+=1;
			}
		else 
			{
			//echo "<input type=\"checkbox\" name=\"adrese\" value=\"$row[1],$row[2],$row[3]\"/>".$row[2]."<br/>\n";
			$oldFirma=$currentFirma;
			$i+=1;
			}
		}
	

	}
/////////////////////////
//
// funkcija, kas ĢENERĒ VAIRĀKU staciju izvēli. 
// 
//
/////////////////////////
function populateAddressesText() //need repair. maybe.
	{
	$query="Select * from Z_didzis_gas_stacijas
order by stacija,adrese asc";
	$result=mysql_query($query);
	$i=0;
	$j=0;
	while($row=mysql_fetch_array($result))
		{
		$currentFirma=$row[1]; //jaaapvieno ar regionu.
		if (($oldFirma!=$currentFirma) and ($i>0))
			{														//style should be added seperately
			echo "</div>\n<div class='adreses' id=adrese_".($j)."  style=\"position:absolute;top:300px;display:none;\">\n\n";
			echo $row[1]."<br/>";
			echo "	<input type=\"checkbox\" name=\"adrese[]\" value=\"$row[1],$row[2],$row[3]\" />".$row[2]."<br/>\n";
			$oldFirma=$currentFirma;
			array_push($this->stationHeaders,array($row[1],$j));
			$i+=1;
			$j+=1;
			}
		elseif ($i==0)
			{
			
			echo "<div class='adreses' id=adrese_".($j)."  style=\"position:absolute;top:280px;display:none;\">\n";
			echo $row[1]."<br/>";
			echo "		<input type=\"checkbox\" name=\"adrese[]\" value=\"$row[1],$row[2],$row[3]\" />".$row[2]."<br/>\n";
			$oldFirma=$currentFirma;
			array_push($this->stationHeaders,array($row[1],$j)); //jaaapievieno regions
			$i+=1;
			$j+=1;
			}
		else
			{
			echo "		<input type=\"checkbox\" name=\"adrese[]\" value=\"$row[1],$row[2],$row[3]\"/>".$row[2]."<br/>\n";
			$oldFirma=$currentFirma;
			$i+=1;
			}
		}
	echo "</div>\n";
	
	}//ef

function populateStHeader()
	{
	echo "\n<!-- Station header generation-->\n";
	foreach ($this->stationHeaders as $header)
		{
		echo "<a href=# id='stationHeader_$header[1]'>$header[0]</a>\n";
		}
	echo "<!-- End Station header generation-->\n\n";

	}//ef

	
function populateStHeaderJavascript()
	{
	foreach ($this->stationHeaders as $header)
		{
			echo '$("#stationHeader_'.$header[1].'").click(function () {$(".adreses").hide("fast"); $("#adrese_'.$header[1].'").show("fast"); });'."\n";
		}
	}
	
function populateStClosingJavascript()
	{
	foreach ($this->stationHeaders as $header)
		{
			echo '$("#stationHeaderC_'.$header[1].'").click(function () {$("#adrese_'.$header[1].'").hide("fast"); });'."\n";
		}
	}//ef
	

//end class
}
?>