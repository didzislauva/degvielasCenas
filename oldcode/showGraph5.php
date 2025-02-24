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
	
	$this->marka=$marka;
	$this->adreses=explode(",",$adreses);
	$this->values = array();
	$this->popValues=array();
	$this->stationHeaders=array();
	}//ef
	
function mySqlConnect() 
	{
	mysql_connect($this->mysqlHost, $this->mysqlUser,$this->mysqlPassw);// or throw new Exception('cannot connect mysql.');
	mysql_select_db($this->mysqlDB);// or throw new Exception('cannot select db.');
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
	$result=mysql_query($query);
	
	while($row = mysql_fetch_array($result))
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
	
function populateDaily()
	{

	//echo count($this->values);
	
	for ($i=0; $i<=count($this->values)-2; $i++)
		{
		$days=(date("d",strtotime($this->values[$i+1][0]))-date("d",strtotime($this->values[$i][0])))-1; //day count between this and next element
		if ($days>0)
			{
			$tDiena=strtotime($this->values[$i][0]);
			for ($j=0;$j<=$days;$j++)
				{			
				
				//echo date("d-m-Y",$tDiena);
				array_push($this->popValues,array(date("Y-m-d",$tDiena),$this->values[$i][1]));
				$tDiena=$tDiena+86000;
				//$k=$k+1; since we have array_push, no need for element counter. array_push can be used for multidimensional arrays
				}
			}
		else
			{
			array_push($this->popValues,array($this->values[$i][0],$this->values[$i][1]));
			//$k=$k+1;
			}//fi		
		}//rof
	//adding the last element from db
	array_push($this->popValues,array($this->values[count($this->values)-1][0],$this->values[count($this->values)-1][1])); //adding the last element in the populated array	
	
	// Need to continue last price till the now()
	$daysBetween=(int)date("d",strtotime($this->now())-strtotime($this->popValues[count($this->popValues)-1][0]));
	
	$tDiena=strtotime($this->popValues[count($this->popValues)-1][0]);
	
	for ($i=0;$i<$daysBetween-1;$i++)	//-1 because of we dont want the one before last point yesterday, but the last point - the current time with last value
		{
		$tDiena=$tDiena+86000;
		array_push($this->popValues,array(date("Y-m-d",$tDiena),$this->popValues[count($this->popValues)-1][1]));	
		}
	array_push($this->popValues,array(date("Y-m-d H:i:s",strtotime($this->now())),$this->popValues[count($this->popValues)-1][1]));
	}//ef
	
function now()
		{
		return date("Y-m-d H:i:s", time());  
		}
		
function createDataForJQPlot()
	{
	foreach ($this->popValues as $value)
		{
		echo "['".$value[0]."', ". $value[1]."],\n";
		}
	}//ef
	
function populateAddresses()
	{
	$query="Select * from Z_didzis_gas_stacijas
order by stacija asc";
	$result=mysql_query($query);
	while($row = mysql_fetch_array($result))
		{
		echo "<option>".$row[1].",".$row[2]."</option>";
		}
	
	}//ef

function populateAddresses2()
	{
		$query="Select * from Z_didzis_gas_stacijas
order by stacija asc";
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
	
function populateAddressesText() //need repair. maybe.
	{
	$query="Select * from Z_didzis_gas_stacijas
order by stacija asc";
	$result=mysql_query($query);
	$i=0;
	$j=0;
	while($row=mysql_fetch_array($result))
		{
		$currentFirma=$row[1]; //jaaapvieno ar regionu.
		if (($oldFirma!=$currentFirma) and ($i>0))
			{
			echo "</div>\n<div class='adreses' id=adrese_".($j)." hidden style=\"position:absolute;top:280px;\">\n";
			echo $row[1]."<br/>";
			echo "<input type=\"checkbox\" name=\"adrese\" value=\"$row[1],$row[2],$row[3]\" />".$row[2]."<br/>\n";
			$oldFirma=$currentFirma;
			array_push($this->stationHeaders,array($row[1],$j));
			$i+=1;
			$j+=1;
			}
		elseif ($i==0)
			{
			
			echo "<div class='adreses' id=adrese_".($j)." hidden style=\"position:absolute;top:280px;\">\n";
			echo $row[1]."<br/>";
			echo "<input type=\"checkbox\" name=\"adrese\" value=\"$row[1],$row[2],$row[3]\" />".$row[2]."<br/>\n";
			$oldFirma=$currentFirma;
			array_push($this->stationHeaders,array($row[1],$j)); //jaaapievieno regions
			$i+=1;
			$j+=1;
			}
		else
			{
			echo "<input type=\"checkbox\" name=\"adrese\" value=\"$row[1],$row[2],$row[3]\"/>".$row[2]."<br/>\n";
			$oldFirma=$currentFirma;
			$i+=1;
			}
		}
	echo "</div>\n";
	
	}//ef

function populateStHeader()
	{
	foreach ($this->stationHeaders as $header)
		{
		echo "<a href=# id='stationHeader_$header[1]'>$header[0]</a> ";
		}
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






$marka=@$_POST['marka'];
if (!isset($marka)) {$marka="E95";}

$station=$_POST['station'];
if (!isset($station)) {$station="Trest,Satekles iela 2";}

//$marka="E95";
$adreses=$station;

$dcm=new degvielasCenuMonitorings($marka,$adreses);
$dcm->mySqlConnect();
$dcm->QueryDB();
$dcm->populateDaily();
$dcm->populateAddresses2();

//$dcm->populateAddresses2();
//var_dump($dcm->stationHeaders);


//die;
?>
<html>
<head>
<style type="text/css">
      html { height: 100% }
      body { height: 100%; margin: 0; padding: 0 }
      #map_canvas { height: 100%; top:0px}
      
      #menu {
        position: fixed;
	_position:absolute;
	_top:expression(eval(document.body.scrollTop));
	background-color: Transparent; 
	left:0px;
	top:0px;
     }
	
	#menu2 {
        position: fixed;
	_position:absolute;
	_top:expression(eval(document.body.scrollTop));
	background-color: Transparent; 
	left:120px;
	top:0px;
    }
	
	#addressContainer {
	position:fixed
	top:240px;
	}
	
	#chart1 {
	height:200px;
	width:640px;
	margin-top:20px
	}
    </style>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="excanvas.js"></script><![endif]-->
<script language="javascript" type="text/javascript" src="javacharts/jquery.min.js"></script>
<script language="javascript" type="text/javascript" src="javacharts/jquery.jqplot.min.js"></script>

<script type="text/javascript" src="javacharts/plugins/jqplot.highlighter.min.js"></script>
<script type="text/javascript" src="javacharts/plugins/jqplot.cursor.min.js"></script>
<script type="text/javascript" src="javacharts/plugins/jqplot.dateAxisRenderer.min.js"></script>

<link rel="stylesheet" type="text/css" href="javacharts/jquery.jqplot.css" />




<?php


if (count($dcm->popValues)>1)
{
?>

<script class="code" type="text/javascript">
//javascript becames generated if values are more than 1.
$(document).ready(function()
{



<?php
$dcm->populateStHeaderJavascript();
$dcm->populateStClosingJavascript();
?>

$("#show").click(function () {	
						$("#addressContainer").show("fast");
					 });  

var line1=[

    
	<?php
	$dcm->createDataForJQPlot();	
	?>
	
  ];
  
var line2=[3,5,7,9];
  
var plot1 = $.jqplot('chart1', [line1], 
	{
		title:'<?php  echo $dcm->adreses[0].", ".$dcm->adreses[1].", ".$dcm->marka; ?>',
		axes:
		{
				xaxis:
				{
				renderer:$.jqplot.DateAxisRenderer,
				tickOptions:
					{
					formatString:'%b %#d, %Y'
					}
				},

			yaxis:
				{
					tickOptions:
						{
						formatString:'%.3f LVL'
						} //,
				//min:0.8,
				//max:1.2
				}
		},
		  
		highlighter: 
		{
			show: true,
			sizeAdjust: 7.5
		},

		cursor: 
		{
			show: false
		}
	});
});
</script>
<?php
}
else
{
?>
<script class="code" type="text/javascript">
$(document).ready(
function()
	{
	$("#chart1").update("aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa");
	//alert("asd");
	}
)
</script>
</head>
<?php
}
?>
<body>
	<div id=chart1 >
	</div>


	<div id="addressContainer" > 
 
	 <?php
	 $dcm->populateStHeader();
	 $dcm->populateAddressesText();
	 ?>
 
	</div> <!--  END address container -->
 
</body>
