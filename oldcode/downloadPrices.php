<?php
class buildingPage{

private $dcm;

function __construct() 
	{
	include_once ("degvielasCenuMonitorings.php");	
	$this->dcm=new degvielasCenuMonitorings($marka,$adreses);
	$this->dcm->mySqlConnect();
	}
	
function buildForm(){
	$this->dcm->populateAddresses();
	}
	
function downloadData($marka,$adrese){

	$this->dcm->marka=$marka;
	
	$adrese=explode(",",$adrese);
	$adreses= $adrese[0].",".$adrese[1];
	
	$this->dcm->adreses[0]=$adrese[0];
	$this->dcm->adreses[1]=$adrese[1];
	#echo $adrese[0];
	header('content-type: text/plain');
	header('Content-Disposition: attachment; filename='.$marka.'.tabDelimitedTxt.xls');
	header('Pragma: no-cache');
	echo $marka."\t".$adrese[0].",".$adrese[1]."\n";
	
	$this->dcm->selectAll();
	}
}



$marka=@$_POST['marka'];
$adrese=@$_POST['station'];
$dbp=new buildingPage();


if (isset($marka) and isset($adrese)){
	$dbp->downloadData($marka,$adrese);
	}

else{
	?>


<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	
	<style type="text/css">
		textarea {
		
		height: 5em;
		}

		input, select, textarea {
		font-size: 60%;
		}
	</style>
	
	</head>
	<body>
		<form action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>" method="post" name="formasName2">
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
			</select>
			
			<select name="station" onChange="document.forms['formasName2'].submit()">
				<option>
					  select station
				</option>
				
				<?php
				$dbp->buildForm();
				?>
			</select>	
		</form>
	</body>
</html>
	<?php
		}
?>

