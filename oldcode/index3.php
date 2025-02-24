<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<style type="text/css">
      html { height: 100% }
      body { height: 100%; margin: 0; padding: 0 }
      #map_canvas { height: 100% }
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

$html= file_get_html('http://www.gudriem.lv/degviela?lng=lv&ci=&st=&co=&t=&fc=&o=1');

$v = $html->find('table[id=tresult]');



	foreach($v[0]->find('script,a') as $e) {
		$e->outertext= '';
		}
		
//        foreach($v[0]->find('img')as $e){
//	    $e->outertext=;
		
//        }
	foreach ($v[0]->find('span') as $e){
		$e->outertext= "<span>".$e->plaintext."</span>" ;
		}
		

	foreach($v[0]->find('div[class=tooltip_small]') as $e) {
		$e->style= '';
		}
		
	


      //echo $v[0];
 
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
			echo trim($adrese)." ".trim($allPrices[0]->plaintext)." ".trim($allPrices[1]->plaintext). " ".trim($allPrices[2]->plaintext)."</br> \n";
			$i++;
		}
		//echo $adreses." ".$allPrices[0]." ".$allPrices[1]. " ".$allPrices[2]."</br>";
	//echo $e."\n";
	}
?>


<script type="text/javascript">
  var geocoder;
  var map;
  var marker = new Array();
  var infowindow = null;
  
  function initialize() {
    geocoder = new google.maps.Geocoder();
    var latlng = new google.maps.LatLng(56.397, 23.644);
    var myOptions = {
      zoom: 11,
      //center: latlng,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
	<?php
	
	$i=0;
	array_shift($allStations);
	foreach ($allStations as $adrese) {
		if ($i<10)	{
		    //echo "infowindow[$i] = new google.maps.InfoWindow({content: '$i'});\n";
			//echo "codeAddress(\"".$adrese[0].", Rīga, Latvija,\",$i,$adrese[1],$adrese[2],$adrese[3]);\n";
			echo "codeAddress(\"".$adrese[0].", Rīga, Latvija\",$i,$adrese[1],$adrese[2],$adrese[3],'$adrese[4]');\n";
			}
		$i++;
	}
	?>
  }


  

  function codeAddress(address,markerNr,e95,e98,dd,nosaukums) {  

    
    geocoder.geocode( { 'address': address}, function(results, status) {
	
      if (status == google.maps.GeocoderStatus.OK) {
        
	    map.setCenter(results[0].geometry.location);
            marker = new google.maps.Marker({
            map: map, 
            position: results[0].geometry.location,
			title: nosaukums+" (" + address + "); 95`ais - " + e95 + "; 98`ais - " + e98 + "; dīzelis - " + dd
        });
	    
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  }
	
	  
    </script>
	
	


</body>











