<?php
include_once ("degvielasCenuMonitorings.php");

$marka=@$_POST['marka'];
if (!isset($marka)) {$marka="E95";}

$station=$_POST['station'];
if (!isset($station)) 
	{
		$station="Trest,Satekles iela 2";
	}
else
	{
	echo $station;
	}



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
<!DOCTYPE html>

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

<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="javacharts/excanvas.js"></script><![endif]-->

<script language="javascript" type="text/javascript" src="javacharts/jquery.min.js" ></script>
<script language="javascript" type="text/javascript" src="javacharts/jquery.jqplot.min.js" ></script>

<script type="text/javascript" src="javacharts/plugins/jqplot.highlighter.min.js" ></script>
<script type="text/javascript" src="javacharts/plugins/jqplot.cursor.min.js" ></script>
<script type="text/javascript" src="javacharts/plugins/jqplot.dateAxisRenderer.min.js" ></script>

<link rel="stylesheet" type="text/css" href="javacharts/jquery.jqplot.css" />

<title>Gasoline prices</title>


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
<?php
}
?>
</head >
<body>
	<div id=chart1 >
	</div>


	
	<form action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>" method="post" name="formasName2">
	<div>
		<select name="marka">
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
				  Petrol
			</option>
		</select> 
	</div>
	 <?php
	 $dcm->populateStHeader();
	 $dcm->populateAddressesText();
	 ?>
	 <input type="hidden"  name="marka" value="<?php echo $marka?>" />
	 <input type=submit name="ChosenGraphs" value="Show graph"/>
	</form>
	
 
</body>
