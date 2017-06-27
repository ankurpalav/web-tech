<html>
<head>
<style>
table{
border:3px solid black;
width:500px;
height:200px;
}
</style>
<script>
function check()
{
	var street= document.myform.street.value;
	var city= document.myform.city.value;
	var state= document.myform.state.value;
	var degree= document.myform.degree.value;
	
	if(street.trim()==""){
	alert("please enter value for street");
	return false;
	}
	

	else if(city.trim()==""){
	alert("please enter value for city");
	return false;
	}
	
	else if(state=="Select your state..."){
	alert("please enter value for state");
	return false;
	}

	
	else if(degree==""){
	alert("please enter value for degree");
	return false;
	}
	return true;
}
function clearData()
{
	document.myform.street.value="";
	document.myform.city.value="";
	document.myform.state.value="Select your state...";
	document.myform.degree.value="fahrenheit";
	document.getElementById("b").innerHTML="";
}
</script>
</head>
<body>
<?php

$street = $city = $state = $degree ="";

if($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["street"]!="" && $_POST["city"]!="" && $_POST["state"]!="Select your state..." && $_POST["degree"]!="")
{
//retrieve all the form field values

   $street=$_POST["street"];
   $city=$_POST["city"];
   $state=$_POST["state"];
   $degree=$_POST["degree"];
   
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

$json = json_decode($response,true);

$temperature =round($json['currently']['temperature']);
$timezone=$json['timezone'];
$summary = $json['currently']['summary'];
$icon = $json['currently']['icon'];
$precipIntensity = $json['currently']['precipIntensity'];
$precipProbability = $json['currently']['precipProbability'];
$windSpeed =round($json['currently']['windSpeed']);
$dewPoint = round($json['currently']['dewPoint']);
$humidity = $json['currently']['humidity'];
$visibility = round($json['currently']['visibility']);
$sunriseTime = $json['daily']['data'][0]['sunriseTime'];
$sunsetTime = $json['daily']['data'][0]['sunsetTime'];

// set the time zone

date_default_timezone_set("$timezone");

$src='';
switch ($icon) {
    case "clear-day":
        $src='http://cs-server.usc.edu:45678/hw/hw6/images/clear.png';
        break;
    case "clear-night":
        $src='http://cs-server.usc.edu:45678/hw/hw6/images/clear_night.png';
        break;
    case "rain":
        $src='http://cs-server.usc.edu:45678/hw/hw6/images/rain.png';
        break;
    case "snow":
        $src='http://cs-server.usc.edu:45678/hw/hw6/images/snow.png';
        break;
    case "sleet":
        $src='http://cs-server.usc.edu:45678/hw/hw6/images/sleet.png';
        break;
    case "wind":
        $src='http://cs-server.usc.edu:45678/hw/hw6/images/wind.png';
        break;
    case "fog":
        $src='http://cs-server.usc.edu:45678/hw/hw6/images/wind.png';
        break;
    case "cloudy":
        $src='http://cs-server.usc.edu:45678/hw/hw6/images/cloudy.png';
        break;
    case "partly-cloudy-day":
        $src='http://cs-server.usc.edu:45678/hw/hw6/images/cloud_day.png';
        break;
    case "partly-cloudy-night":
        $src='http://cs-server.usc.edu:45678/hw/hw6/images/cloud_night.png';
        break;

}

if($degree == "celsius")
{
	$precipIntensity=$precipIntensity/25.4;
}


//function for setting the precipitation Intensity

 function isBetween($x, $lower, $upper) {
  return $lower <= $x && $x < $upper;
 }

if(isBetween($precipIntensity,0,0.002)) {
  
	$precipIntensity="None";
} 
else if(isBetween($precipIntensity,0.002,0.017)) {

  	$precipIntensity="Very Light";
}
else if(isBetween($precipIntensity,0.017,0.1)) {

  	$precipIntensity="Light";
}
else if(isBetween($precipIntensity,0.1,0.4)) {

  	$precipIntensity="Moderate";
}
else
{
	$precipIntensity="Heavy";
}

//setting the symbol for temperature,windspeed & visibility

if($degree == "fahrenheit")
{ $temperature = $temperature." &#8457;";
 $windSpeed=$windSpeed." mph";
 $visibility=$visibility." mi";
 $dewPoint=$dewPoint." &#8457;";
}
else 
{
 $temperature = $temperature." &#8451;";
 $windSpeed=$windSpeed." mts/s";
 $visibility=$visibility." km";
 $dewPoint=$dewPoint." &#8451;";
}

//setting proper values for different parameters

$precipProbability=($precipProbability*100)."%";

$humidity=($humidity*100)."%";

}
?>
<center></br></br><caption><h1>Forecast Search</h1></caption>
<table frame="box"cellpadding="10px">
<tr>
<td>
<form name="myform" method="post" onsubmit="return check();" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">

Street Address:* <input type="text" name="street" value="<?php echo $street; ?>"></br></br>

City:*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="city"  value="<?php echo $city; ?>"></br></br>

State:*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<select name="state">

	<option value="Select your state..." <?php echo (isset($_POST['state']) && $_POST['state'] == 'Select your state...')?'selected="selected"':''; ?> >Select your state...</option>
	<option value="Alabama" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Alabama')?'selected="selected"':''; ?> >Alabama</option>
	<option value="Alaska" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Alaska')?'selected="selected"':''; ?> >Alaska</option>
	<option value="Arizona" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Arizona')?'selected="selected"':''; ?> >Arizona</option>
	<option value="Arkansas" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Arkansas')?'selected="selected"':''; ?> >Arkansas</option>
	<option value="California" <?php echo (isset($_POST['state']) && $_POST['state'] == 'California')?'selected="selected"':''; ?> >California</option>
	<option value="Colorado" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Colorado')?'selected="selected"':''; ?> >Colorado</option>
	<option value="Connecticut" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Connecticut')?'selected="selected"':''; ?> >Connecticut</option>
	<option value="Delaware" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Delaware')?'selected="selected"':''; ?> >Delaware</option>
	<option value="District Of Columbia" <?php echo (isset($_POST['state']) && $_POST['state'] == 'District Of Columbia')?'selected="selected"':''; ?> >District Of Columbia</option>
	<option value="Florida" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Florida')?'selected="selected"':''; ?> >Florida</option>
	<option value="Georgia" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Georgia')?'selected="selected"':''; ?> >Georgia</option>
	<option value="Hawaii" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Hawaii')?'selected="selected"':''; ?> >Hawaii</option>
	<option value="Idaho" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Idaho')?'selected="selected"':''; ?> >Idaho</option>
	<option value="Illinois" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Illinois')?'selected="selected"':''; ?> >Illinois</option>
	<option value="Indiana" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Indiana')?'selected="selected"':''; ?> >Indiana</option>
	<option value="Iowa" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Iowa')?'selected="selected"':''; ?> >Iowa</option>
	<option value="Kansas" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Kansas')?'selected="selected"':''; ?> >Kansas</option>
	<option value="Kentucky" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Kentucky')?'selected="selected"':''; ?> >Kentucky</option>
	<option value="Louisiana" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Louisiana')?'selected="selected"':''; ?> >Louisiana</option>
	<option value="Maine" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Maine')?'selected="selected"':''; ?> >Maine</option>
	<option value="Maryland" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Maryland')?'selected="selected"':''; ?> >Maryland</option>
	<option value="Massachusetts" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Massachusetts')?'selected="selected"':''; ?> >Massachusetts</option>
	<option value="Michigan" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Michigan')?'selected="selected"':''; ?> >Michigan</option>
	<option value="Minnesota" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Minnesota')?'selected="selected"':''; ?> >Minnesota</option>
	<option value="Mississippi" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Mississippi')?'selected="selected"':''; ?> >Mississippi</option>
	<option value="Missouri" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Missouri')?'selected="selected"':''; ?> >Missouri</option>
	<option value="Montana" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Montana')?'selected="selected"':''; ?> >Montana</option>
	<option value="Nebraska" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Nebraska')?'selected="selected"':''; ?> >Nebraska</option>
	<option value="Nevada" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Nevada')?'selected="selected"':''; ?> >Nevada</option>
	<option value="New Hampshire" <?php echo (isset($_POST['state']) && $_POST['state'] == 'New Hampshire')?'selected="selected"':''; ?> >New Hampshire</option>
	<option value="New Jersey" <?php echo (isset($_POST['state']) && $_POST['state'] == 'New Jersey')?'selected="selected"':''; ?> >New Jersey</option>
	<option value="New Mexico" <?php echo (isset($_POST['state']) && $_POST['state'] == 'New Mexico')?'selected="selected"':''; ?> >New Mexico</option>
	<option value="New York" <?php echo (isset($_POST['state']) && $_POST['state'] == 'New York')?'selected="selected"':''; ?> >New York</option>
	<option value="North Carolina" <?php echo (isset($_POST['state']) && $_POST['state'] == 'North Carolina')?'selected="selected"':''; ?> >North Carolina</option>
	<option value="North Dakota" <?php echo (isset($_POST['state']) && $_POST['state'] == 'North Dakota')?'selected="selected"':''; ?> >North Dakota</option>
	<option value="Ohio" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Ohio')?'selected="selected"':''; ?> >Ohio</option>
	<option value="Oklahoma" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Oklahoma')?'selected="selected"':''; ?> >Oklahoma</option>
	<option value="Oregon" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Oregon')?'selected="selected"':''; ?> >Oregon</option>
	<option value="Pennsylvania" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Pennsylvania')?'selected="selected"':''; ?> >Pennsylvania</option>
	<option value="Rhode Island" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Rhode Island')?'selected="selected"':''; ?> >Rhode Island</option>
	<option value="South Carolina" <?php echo (isset($_POST['state']) && $_POST['state'] == 'South Carolina')?'selected="selected"':''; ?> >South Carolina</option>
	<option value="South Dakota" <?php echo (isset($_POST['state']) && $_POST['state'] == 'South Dakota')?'selected="selected"':''; ?> >South Dakota</option>
	<option value="Tennessee" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Tennessee')?'selected="selected"':''; ?> >Tennessee</option>
	<option value="Texas" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Texas')?'selected="selected"':''; ?> >Texas</option>
	<option value="Utah" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Utah')?'selected="selected"':''; ?> >Utah</option>
	<option value="Vermont" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Vermont')?'selected="selected"':''; ?> >Vermont</option>
	<option value="Virginia" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Virginia')?'selected="selected"':''; ?> >Virginia</option>
	<option value="Washington" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Washington')?'selected="selected"':''; ?> >Washington</option>
	<option value="West Virgina" <?php echo (isset($_POST['state']) && $_POST['state'] == 'West Virgina')?'selected="selected"':''; ?> >West Virgina</option>
	<option value="Wisconsin" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Wisconsin')?'selected="selected"':''; ?> >Wisconsin</option>
	<option value="Wyoming" <?php echo (isset($_POST['state']) && $_POST['state'] == 'Wyoming')?'selected="selected"':''; ?> >Wyoming</option>
	</select></br></br>

Degree:*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   <input type="radio" name="degree" <?php if (isset($degree) && $degree=="fahrenheit") echo "checked";?>  value="fahrenheit" checked="checked">Fahrenheit
   <input type="radio" name="degree" <?php if (isset($degree) && $degree=="celsius") echo "checked";?>  value="celsius">Celsius
</br>


&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit"value="Search"><input type="button" value="Clear" name="Clear" onclick="return clearData();"></br>


<i>* - Mandatory fields.</i></br>

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://forecast.io/">Powered by Forecast.io</a>

</form>
</td>
</tr>
</table>
</center>
<?php

//displaying the final values in table format

$output="";

if($street != "" && $city != "" && $state != "select a state.." && $degree !="")
{
 
 $output.="</br><html><body><center><table id='b' cellpadding='20px'style='align:center'><tr><th><h2 align='center'>".$summary."</h2>";
 $output.="<h2 align='center'>".$temperature."</h2>";
 $output.="<img src='$src' align='center' alt='$summary' title='$summary' width='100px'height='100px'></img></th></tr>";
 $output.="<tr><td>Precipitation: </td><td>".$precipIntensity."</td></tr>";
 $output.="<tr><td>Chance of rain: </td><td>".$precipProbability."</td></tr>";
 $output.="<tr><td>Wind Speed: </td><td>".$windSpeed."</td></tr>";
 $output.="<tr><td>Dew Point: </td><td>".$dewPoint."</td></tr>";
 $output.="<tr><td>Humidity: </td><td>".$humidity."</td></tr>";
 $output.="<tr><td>Visibility: </td><td>".$visibility."</td></tr>";
 $output.="<tr><td>Sunrise: </td><td>".date('g:i a',$sunriseTime)."</td></tr>";
 $output.="<tr><td>Sunset: </td><td>".date('g:i a',$sunsetTime)."</td></tr></table></center><noscript></body></html>";
 echo $output;
}

?>
<noscript>
</body>
</html>