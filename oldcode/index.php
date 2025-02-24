<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<style type="text/css">
      html { height: 100% }
      body { height: 100%; margin: 0; padding: 0 }
      #map_canvas { height: 100% }
    </style>
    <script type="text/javascript"
      src="http://maps.googleapis.com/maps/api/js?key=">
    </script>
</head>
<?php

include_once('simple_html_dom.php');

$html= file_get_html('http://www.gudriem.lv/degviela?lng=lv');

$v = $html->find('table[id=tresult]');



	foreach($v[0]->find('script,img,a') as $e) {
		$e->outertext= '';
		}
		
		
	foreach ($v[0]->find('span') as $e){
		$e->outertext= "<span>".$e->plaintext."</span>" ;
		}
		

	foreach($v[0]->find('div[class=tooltip_small]') as $e) {
		$e->style= '';
		}
		





 //echo $v[0];
 
	$w = $v[0]->find('tr');
	
	//echo $w[1];
?>

<body onload="initialize()">
    <div id="map_canvas" style="width:100%; height:100%"></div>






<?php	
$i=0;
	foreach ($w as $e){
		$adress= $e->find('span');
		$adreses= explode(",",$adress[0]->plaintext);
		
		$allPrices=$e->find('td[class=price]');
		
		foreach ($adreses as $adrese){
			$adrese=str_replace("Adreses","",$adrese);
			$allStations[$i]=trim($adrese);
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
  var marker = new array();
  
  function initialize() {
    geocoder = new google.maps.Geocoder();
    var latlng = new google.maps.LatLng(56.397, 23.644);
    var myOptions = {
      zoom: 12,
      center: latlng,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
	<?php
	
	$i=0;
	array_shift($allStations);
	foreach ($allStations as $adrese) {
		if ($i<=10)	
			echo "codeAddress(\"".$adrese.", Rīga, Latvija,\",$i)\n";
		$i++;
	}
	?>
	
  }

  function codeAddress(address,markerNr) {
  
    //var address = document.getElementById("address").value;
    geocoder.geocode( { 'address': address}, function(results, status) {
	
      if (status == google.maps.GeocoderStatus.OK) {
        
		map.setCenter(results[0].geometry.location);
        marker = new google.maps.Marker({
            map: map, 
            position: results[0].geometry.location,
			
        });
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  }
	
	  
    </script>
	
	


</body>











