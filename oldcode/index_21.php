<?php
$site=@$_POST['name'];
if (!isset($site)) {$site="e95";}
if($site=="e95"){$site="http://www.gudriem.lv/degviela?lng=lv&ci=&st=&co=&t=&fc=&o=1";}
elseif($site=="e98"){$site="http://www.gudriem.lv/degviela?lng=lv&ci=&st=&co=&t=&fc=&o=5";}
elseif($site=="dd"){$site="http://www.gudriem.lv/degviela?lng=lv&ci=&st=&co=&t=&fc=&o=9";}
else {$site="http://www.gudriem.lv/degviela?lng=lv&ci=&st=&co=&t=&fc=&o=1";}
?>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<style type="text/css">
      html { height: 100% }
      body { height: 100%; margin: 0; padding: 0 }
      #map_canvas { height: 100%; top:0px}
      
      #menu {
        position: fixed;
	_position:absolute;
	_top:expression(eval(document.body.scrollTop));
	left:80px;
	top:0px;
    background-color: #ffffff }
    </style>
    <!--
	<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=">
    -->
    
    <script type="text/javascript" src="//maps.googleapis.com/maps/api/js?sensor=false"></script>
    </script>
</head>
<?php

//this lib kicks donkeys AKA rocks
//http://simplehtmldom.sourceforge.net/
include_once('simple_html_dom.php');
//////////////////////////////////////

//$html= file_get_html('http://www.gudriem.lv/degviela?lng=lv&ci=&st=&co=&t=&fc=&o=1');
$html= file_get_html($site);

$v = $html->find('table[id=tresult]');


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


?>

<body onload="initialize()">
    <div id="map_canvas" style="width:100%; height:100%"></div>






<?php	
$i=0;

	  
	foreach ($w as $e){
		$adress= $e->find('span');
		$adreses= explode(",",$adress[0]->plaintext);
		
		$allPrices=$e->find('td[class=price]');
		
		$nosaukums=$e->find('img');
		
		//$nosaukums=$nosaukums[0]->alt;
		
		
		
		foreach ($adreses as $adrese){
			$adrese=str_replace("Adreses","",$adrese);			
			$allStations[$i][0]=trim($adrese);
			$allStations[$i][1]=trim($allPrices[0]->plaintext);
			$allStations[$i][1]=str_replace("-","'nav'",$allStations[$i][1]);
			$allStations[$i][2]=trim($allPrices[1]->plaintext);
			$allStations[$i][2]=str_replace("-","'nav'",$allStations[$i][2]);
			$allStations[$i][3]=trim($allPrices[2]->plaintext);
			$allStations[$i][3]=str_replace("-","'nav'",$allStations[$i][3]);
			$allStations[$i][4]=trim($nosaukums[0]->alt);
			$i++;
		}
	}
	
?>


<script type="text/javascript">

////////////////////////////
//defining global variables
////////////////////////////
  var geocoder;
  var map;
  var marker = new Array(); //where markers are stored
  var bounds; //for extending when populates markers
  
  
  

  function initialize() {
  // creating objects
    geocoder = new google.maps.Geocoder();  //initialize uses codeAddress function and thus codeAddress can access geocoder object
	bounds= new google.maps.LatLngBounds(); //the same with bounds.
	infowindow = new google.maps.InfoWindow({content: "holding..."}); //and with infoWindow.
	
    //var latlng = new google.maps.LatLng(); //setting default latlng for map, maybe not necessary.
    var myOptions = {		//setting default options
      zoom: 11,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions); //creating The Map.

	<?php
	$i=1;
	array_shift($allStations);
	foreach ($allStations as $adrese) { //this php part populates function codeAddress with previously fetched information
		if ($i<=10)	{
			echo "codeAddress(\"".$adrese[0].", Rīga, Latvija\",$i,$adrese[1],$adrese[2],$adrese[3],'$adrese[4]');\n";
			
			}
		$i++;
	}
	?>
}
//this is where initialize ends. As it uses function codeAddress - so here it is:

  function codeAddress(address,markerNr,e95,e98,dd,nosaukums) {  
    geocoder.geocode( { 'address': address}, function(results, status) {//the address has been passed as street name and number and
      if (status == google.maps.GeocoderStatus.OK) {	    			//it should be geocoded through geocoder.
            marker[markerNr] = new google.maps.Marker({					//the array with marker objects has been created.
		  <?php	//this part chooses correct icon type
		  
		  if ($site=="http://www.gudriem.lv/degviela?lng=lv&ci=&st=&co=&t=&fc=&o=1"){echo "icon: 'icons/gas95_";}
		  elseif($site=="http://www.gudriem.lv/degviela?lng=lv&ci=&st=&co=&t=&fc=&o=5"){echo "icon: 'icons/gas98_";}
		  elseif($site=="http://www.gudriem.lv/degviela?lng=lv&ci=&st=&co=&t=&fc=&o=9"){echo"icon: 'icons/gasdd_";}
		  else{echo"icon: 'icons/gas95',";}
		  ?>'+markerNr+'.png',
            map: map, 
            position: results[0].geometry.location,	//where to put the marker
	    title: nosaukums+" (" + address + "); 95`ais - " + e95 + " LVL/l; 98`ais - " + e98 + " LVL/l; dīzelis - " + dd + " LVL/l."
        });	
		
		google.maps.event.addListener(marker[markerNr], 'click', function() { //each marker must listen for click and if clicked it should fire a infowindow with custom content
		infowindow.setContent("<h2>"+nosaukums+"</h2> (" + address + "); <br/><b>E95</b> - " + e95 + " LVL/l;<br/> <b>E98</b> - " + e98 + " LVL/l; <br/><b>Petrol</b> - " + dd + " LVL/l.");
		infowindow.open(map,marker[markerNr]);
		});
				
		bounds.extend(marker[markerNr].position);		//and all the time when markers has been populated, the bounds are extending and
		map.fitBounds(bounds);							//finally the map is centered
	    
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  }
	
	  
    </script>
	
<div id="menu">	
<form action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>" method="post" name="formasName">

<select name="name" onChange="document.forms['formasName'].submit()">
<option>
      select gas type
</option>
<option>
      e95
</option>
<option>
      e98
</option>
<option>
      dd
</option>
</select>
</form>
 </div>

</body>



</html>







