<?php
include_once ("degvielasCenuMonitorings.php");

$array=array();
$jqFullMasivs=array();
$jqAddressMasivs=array();

if(isset($_POST['ChosenGraphs']))
{
	$redirekts=true;
	for ($i=0; $i<count($_POST['adrese']);$i++) 
	{
		array_push($array,$_POST['adrese'][$i]);
	}
	

	
	$marka=@$_POST['marka'];

	if (!isset($marka) or $marka=='select gas type') 
	{
		$marka="E95";
	}

	if (count($array)==0)
		{
		$array[0]="Trest,Satekles iela 2";
		}
	
	foreach ($array as $adreses)
		{

		
		$adrese=explode(",",$adreses);
		$adreses= $adrese[0].",".$adrese[1];
		$dcm=new degvielasCenuMonitorings($marka,$adreses);
		$dcm->mySqlConnect();
		$dcm->QueryDB();
		$dcm->populateDaily();
		$dcm->populateAddresses2();
		array_push($jqFullMasivs,$dcm->jqArray);
		array_push($jqAddressMasivs,$adreses);
		
		//$dcm->mySqlConnectClose(); //vajag tādu vispār?
		
		}

//var_dump($jqFullMasivs);
	


}
else
{
$redirekts = false;
$marka="E95";
$station="Trest,Satekles iela 2";

$adreses=$station;
$dcm=new degvielasCenuMonitorings($marka,$adreses);
$dcm->mySqlConnect();
$dcm->QueryDB();
$dcm->populateDaily();
$dcm->populateAddresses2();
$jqAddressMasivs[0]=$adreses;
array_push($jqFullMasivs,$dcm->jqArray);
//$dcm->populateAddresses2();
//
//die;

}

//var_dump($jqFullMasivs);




//die;

//$marka="E95";

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
	margin-top:20px ;
	margin-left: auto ;
	margin-right: auto ;
	}
	
	#errorDiv {
	margin-top:25px;
	margin-left:15px;
	font-family:"Verdana", Times, serif;
	font-size:10px;
	}
	
	#nav{
	width:640px;
	margin-left: auto ;
	margin-right: auto ;
	font-family:"Verdana", Times, serif;
	font-size:11px;
	}
	
	.widener {
	
	width:20px;
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


<script class="code" type="text/javascript">
$(document).ready(function()
{

<?php
$dcm->populateStHeaderJavascript();
$dcm->populateStClosingJavascript();
?>

$("#show").click(function () {	
						$("#addressContainer").show("fast");
					 });


<?php
if ($dcm->jqValidCount>1)
{
	
	/////////////////
	//
	// generate data SERIES
	//
	/////////////////
	$dataCounter=0;
		foreach ($jqFullMasivs as $array3D)
			{
			foreach ($array3D as $array2D)
				{
				echo "var data".$dataCounter."=[\n";
				foreach ($array2D as $value)
					{
					echo "['".$value[0]."', ".$value[1]."],\n";
					}
				echo "];\n";
				}
			$dataCounter+=1;
			}
			
	////////////////
	///
	/// generate data series NAME
	///
	////////////////

	$tmpArray=array();
	for ($i=0;$i<$dataCounter;$i++)
		{
		array_push($tmpArray, "data".$i);
		}
	$dataString=implode(',',$tmpArray);
	$dataString="[".$dataString."],";


	?>

	var plot1 = $.jqplot('chart1', <?php echo $dataString;?>
		{
			title:<?php echo "'Pilnīgs BETA cenu salīdzināšanai - ".$marka."'"?>,
			series:
			[
				
				////////////////////
				//
				// php generated series name. works only, if legend is enabled.
				//
				////////////////////
				<?php
				$tmpArray=array();
				for ($i=0;$i<$dataCounter;$i++)
					{
					array_push($tmpArray, "{label:'".$jqAddressMasivs[$i]."'}");
					}
				$labelString=implode(',',$tmpArray);
				
				echo $labelString;
				?>
				
			],
			legend:
				{
				show:true,
				location:'nw'
				},
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
				sizeAdjust: 7.5,
				showLabel:true //,
				//formatString: '%s - %d. X, %d Y'
			},

			cursor: 
			{
				show: false
			}
		});
	});
	</script>
	</head>
	<div id=chart1>
	<?php
}
else
{
	?>
	});
	</script>
	</head>
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


	<div id=nav>
	<form action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>" method="post" name="marka">
	<br />
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
				  Diesel
			</option>
			<option>
				  Gas
			</option>
		</select> <input type=submit name="ChosenGraphs" value="Show graph"/><br />
    
	 <?php
	 $dcm->populateStHeader();
	 $dcm->populateAddressesText();
	 ?>
	 <br />
	 <br />
	</form>
	</div>
 
</body>
