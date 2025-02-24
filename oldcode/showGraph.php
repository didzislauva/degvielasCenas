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

function __construct($marka,$adreses) 
	{  
	$this->mysqlUser="";
	$this->mysqlPassw="";
	$this->mysqlHost="";
	$this->mysqlDB="";
	$this->mysqlTable="Z_didzis_gas_test";
	
	$this->marka=$marka;
	$this->adreses=explode(",",$adreses);
	$this->values = array();
	$this->popValues=array();
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

	//iterâcija no pirmâ atgrieztâ ieraksta lîdz 
	//echo $this->values[0][0];
	
	//$firstDay=strtotime($this->values[0][0]);
	//$lastDay=strtotime($this->values[count($this->values)-1][0]);
	
	// ja starp n un n+1 ierakstu ir brîvas dienas, tad populate brîvajâs dienâs n vçrtîbu
	// else ja brîvas dienas ir nulle, tad ir ik katru dienu veikts mçrîjums vai arî vairâki mçrîjumi. populate tikai tad, ja dienu atðíirîba ir >0

	// iterating over the array elements.
	// conditional loop creates inside the cycle - day count between this and next element. can be done without conditional, btw.
	// if there are days without values between n and n+1 elements, then we need to populate the value in these days without values.
	// else if there are no days without values=> daysBetween=0, then take the value from 
	// in this case i am not interpolating, but it is possible to interpolate values lineary here.
	//
	// $this->values[0][0]="2012-01-20 09:40:00";
	// $this->values[1][0]="2012-01-21 09:40:00";
	// $this->values[2][0]="2012-01-25 09:40:00";
	// $this->values[3][0]="2012-01-26 09:40:00";
	// $this->values[4][0]="2012-01-29 09:40:00";
	
	// $this->values[0][1]=0;
	// $this->values[1][1]=2;
	// $this->values[2][1]=4;
	// $this->values[3][1]=6;
	// $this->values[4][1]=8;
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
	array_push($this->popValues,array($this->values[count($this->values)-1][0],$this->values[count($this->values)-1][1])); //adding the last element in the populated array	
	
		// Need to continue last price till the now()
	$daysBetween=(int)date("d",strtotime($this->now())-strtotime($this->popValues[count($this->popValues)-1][0]));
	
	$tDiena=strtotime($this->popValues[count($this->popValues)-1][0]);
	
	for ($i=0;$i<$daysBetween-1;$i++)
		{
		$tDiena=$tDiena+86000;
		//echo date("Y-m-d",$tDiena);
		array_push($this->popValues,array(date("Y-m-d",$tDiena),$this->popValues[count($this->popValues)-1][1]));	
		}
		//array_pop($this->popValues);
	}//ef
	
function now()
		{
		return date("Y-m-d H:i:s", time());  
		}//ef
		
function createDataForJQPlot()
	{
	foreach ($this->popValues as $value)
		{
		echo "['".$value[0]."', ". $value[1]."],\n";
		}
	}//ef
//end class
}











$marka="E95";
$adreses="Trest,DÄrzciema iela 127";

$dcm=new degvielasCenuMonitorings($marka,$adreses);
$dcm->mySqlConnect();
$dcm->QueryDB();
$dcm->populateDaily();

//$dcm->createDataForJQPlot();

//die;

//var_dump($dcm->popValues);
//var_dump($dcm->values);

//var_dump ($dcm->getAdreses());



?>
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
  
var line1=[

    ///*
	
	//*/
	<?php
	$dcm->createDataForJQPlot();	
	?>
  ];
  
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
					formatString:'%b %#d, %Y',
					}
				},

			yaxis:
			{
				tickOptions:
					{
					formatString:'%.3f LVL',
					} //,
			//min:0.8,
			//max:1.
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
?>
<body>
<div id=chart1 style="height:200px;width:640px;">
</div>
