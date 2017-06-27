<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

if($_SERVER["REQUEST_METHOD"] == "GET" && $_GET["street"]!="" && $_GET["city"]!="" && $_GET["state"]!="Select your state..." && $_GET["degree"]!="")
{
//retrieve all the form field values

   $street=$_GET["street"];
   $city=$_GET["city"];
   $state=$_GET["state"];
   $degree=$_GET["degree"];
  
//removing all whitespaces from the values

$street = preg_replace('/\s+/', '', $street);
$city = preg_replace('/\s+/', '', $city);
$state = preg_replace('/\s+/', '', $state);
$degree = preg_replace('/\s+/', '', $degree);
$degreeunit = "";

//setting the value for units to be used in second web service url

if($degree == "fahrenheit")
$degreeunit = "us";
else
$degreeunit = "si";

$apikey="AIzaSyD83Haa76LbJWyiI31fs8yA10fohjMFh1s";

//without key

//$address="".$street.",".$city.",".$state;

//with key

$address="".$street.",".$city.",".$state."&key=".$apikey;

// constructing the google geocode url by appending the address string from above

$geourl= "https://maps.googleapis.com/maps/api/geocode/xml?address=".$address;

//use below line when using with key,dont use curl for with key
$xmlContent=file_get_contents($geourl);


// Create cUrl object to grab XML content using $geourl
/*
$c = curl_init();

curl_setopt($c, CURLOPT_URL, $geourl);

curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
 
$xmlContent = trim(curl_exec($c));
 
curl_close($c);
*/

// Create SimpleXML object from XML Content

$xmlObject =  simplexml_load_string($xmlContent);

// get the latitude & longitude from the XML Object

 $lat = $xmlObject->result->geometry->location->lat;
 $lng = $xmlObject->result->geometry->location->lng;

//construct the web service url for forecast.io api with latitude,longitude and degree values

$response = file_get_contents("https://api.forecast.io/forecast/0a3a387d94fcc6e92f32118932e764e8/$lat,$lng?units=$degreeunit&exclude=flags");

//$json = json_decode($response,true);

//$json = json_encode($json);

echo $response;

}
?>