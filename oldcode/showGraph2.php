<!DOCTYPE html>
<head>
<?php

include_once ("degvielasCenuMonitorings.php");

$marka=@$_POST['marka'];
if (!isset($marka)) {$marka="E95";}

$station=$_POST['station'];
if (!isset($station)) {$station="Trest,Satekles iela 2";}

if ($marka=="Diesel"){$marka="Diesel";} // in db Diesel is written as petrol


$adreses=$station;

$dcm=new degvielasCenuMonitorings($marka,$adreses); 

if ($dcm->mySqlConnect())
	{
	$dcm->QueryDB();
	$dcm->populateDaily();
	}
else
	{
	echo "<!--msql-->";
	}
//------debug////////////
//var_dump($dcm->popValues);
//$dcm->createDataForJQPlot();
//die;
?>

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
	
	#chart1 {
	height:200px;
	width:640px;
	top:20px
	}
	
	#errorDiv {
	margin-top:25px;
	margin-left:15px;
	font-family:"Verdana", Times, serif;
	font-size:10px;
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
////////////////////////
//
// Javascript OR error DIV generation
//javascript becames generated if populated values are more than 1. usually are.
//
////////////////////////

if ($dcm->jqValidCount>1)
{
?>
<script class="code" type="text/javascript">
$(document).ready(function()
{

var browserName=navigator.appName; 
	if (browserName!="Microsoft Internet Explorer")
		 {
		


		var line1=[

			///*
			// testDataSet

			//*/
			<?php
			$dcm->createDataForJQPlot();	
			?>
		  ];
		  
				 
		var plot1 = $.jqplot('chart1', [line1], 
			{
				  
				  
				  title:'<?php  
				  if ($dcm->marka=="Diesel") // in db Diesel is written as petrol
				  {
					$markaN="Diesel";
				  }
				  else
				  {
					$markaN=$dcm->marka;
				  }
				  echo $dcm->adreses[0].", ".$dcm->adreses[1].", ".$markaN; 
				  
				  ?>',
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
					//max:1.2
					}
				},
				  
				highlighter: 
				{
					show: true,
					sizeAdjust: 7.5
				},
				
				series: 
				[
					{
						showLine:false,
						markerOptions: { size: 7, style:"filledCircle" }
					}
				],
				
				
				cursor: 
				{
					show: false
				}
			});
	
		}
		
		else
		{
			alert("Fuel charts not working correctly with IE. Please, change the browser. \nCenu grafiki nestrādā īsti ar Internet Explorer. Pamēģini ar citu pārlūku.\n\nDrīz mēģināšu salabot")
		}
	}
);


</script>
</head>
<div id=chart1>
<?php
}
else
{
?>
<div id=chart1>
	<div id=errorDiv>
	<p>
	Few possible reasons fot request "<?php  echo $dcm->adreses[0].", ".$dcm->adreses[1].", ".$marka;  ?>".  <br />1)No data available yet. <br />2)No price changes since last month. <br/>3)Incorrect request. <br/>Will be fixed soon.
	</p>
	</div> <!-- error div-->

<?php
///////////////////////////
//
// end javascript and header geneartion
//
///////////////////////////
}
?>

</div> <!-- chart1 div-->

<div id="menu">	
<form action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>" method="post" name="formasName1">

<select name="marka" onChange="document.forms['formasName1'].submit()">
<option>
      select gas type
</option>
<option>
      E95
</option>
<option>
      E98
</option>
<option>
      Diesel
</option>
<option>
      Gas
</option>
</select>
<input type="hidden"  name="station" value="<?php echo $station?>" />
</form>
 </div>


<div id="menu2">	
<form action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>" method="post" name="formasName2">

<select name="station" onChange="document.forms['formasName2'].submit()">
<option>
      select station
</option>

<?php
$dcm->populateAddresses();
?>

</select>
<input type="hidden"  name="marka" value="<?php 
 if ($marka=="Diesel") {$marka="Diesel";} // in db Diesel is written as petrol
echo $marka?>" />

</form>
 </div>