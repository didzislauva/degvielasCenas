<?php

include_once ("degvielasCenuMonitorings.php");


$station="Trest,Satekles iela 2";
$marka="E95";

$dcm=new degvielasCenuMonitorings($marka,$station);
$dcm->mySqlConnect();
$dcm->QueryDB();
$dcm->populateDaily();
$dcm->populateAddresses2();
var_dump ($dcm->marka);
var_dump($dcm->values);
var_dump($dcm->popValues);
?>