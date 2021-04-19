<?php 
require_once($_SERVER['DOCUMENT_ROOT'].'/config.php');

// set timezone to get easter_date correct
date_default_timezone_set('Europe/London');

//Prepare database table
$connection = new mysqli($servername, $username, $password, $dbname);
$query="TRUNCATE `presenter`.`thisyeardates`";
$result = $connection->query($query);


function addtodb($datename,$thisyeardate, $lectionaryid, $priority="999", $type="" ){
global $servername, $username, $password, $dbname;
$connection = new mysqli($servername, $username, $password, $dbname);
$query="INSERT INTO `thisyeardates` (`thisyeardate_id`, `datename`, `type`, `thisyeardate`, `lectionary_id`, `item_priority`) VALUES (NULL, '".$datename."', '".$type."', '".$thisyeardate."', '".$lectionaryid."', '".$priority."')";

$result = $connection->query($query);	
}
	
function isthissunday($datex,$yearx){
	$datestring=$yearx.$datex;
	$issunday="no";
	$datestamp=strtotime($datestring);
	if(date("D",$datestamp)=="Sun")$issunday="yes";
	return $issunday;
}	
	
	function findsunday($datex,$yearx){
//find the the sunday on or first sunday after this date
	$finddate=$yearx.$datex;
	//echo $finddate;
	$findsundaystamp=strtotime($finddate);
	if(date("D",$findsundaystamp)=="Sun")$returndatestamp=$findsundaystamp;
	$findsundaystamp=strtotime('+1 day', $findsundaystamp);
	if(date("D",$findsundaystamp)=="Sun")$returndatestamp=$findsundaystamp;
	$findsundaystamp=strtotime('+1 day', $findsundaystamp);
	if(date("D",$findsundaystamp)=="Sun")$returndatestamp=$findsundaystamp;
	$findsundaystamp=strtotime('+1 day', $findsundaystamp);
	if(date("D",$findsundaystamp)=="Sun")$returndatestamp=$findsundaystamp;
	$findsundaystamp=strtotime('+1 day', $findsundaystamp);
	if(date("D",$findsundaystamp)=="Sun")$returndatestamp=$findsundaystamp;
	$findsundaystamp=strtotime('+1 day', $findsundaystamp);
	if(date("D",$findsundaystamp)=="Sun")$returndatestamp=$findsundaystamp;
	$findsundaystamp=strtotime('+1 day', $findsundaystamp);
	if(date("D",$findsundaystamp)=="Sun")$returndatestamp=$findsundaystamp;
	return $returndatestamp;
	}



// based on:
// https://www.churchofengland.org/prayer-and-worship/worship-texts-and-resources/common-worship/churchs-year/lectionary
//// https://www.churchofengland.org/prayer-and-worship/worship-texts-and-resources/common-worship/churchs-year/calendar
// https://www.churchofengland.org/prayer-and-worship/worship-texts-and-resources/common-worship/churchs-year/rules



// Check if the form is submitted 
if ( isset( $_GET['year'] ) ) {
$year = $_GET['year']; 
}else{
	$year=2021;
}

?>
<form>

  <input type="text" id="year" name="year" value="<?php echo $year; ?>">
  <input type="submit" value="Submit">
</form>
<?php


$mydate=$year."-04-07";
//$mydate="2017-01-26";//dec 2016 christmas is sunday
//$mydate="2019-01-26";//jan 2019 epiphany on sunday
$mydatestamp=strtotime($mydate);

//first move to next sunday If its not sunday today)
$searchtimestamp=findsunday("-04-07",$year);


// now find the Advent Sunday that starts this liturgical year
$adventstamp=findsunday("-11-27",$year);
if($adventstamp>$searchtimestamp){
	$year=$year-1;
	$adventstamp=findsunday("-11-27",$year);
}
// now for season timestamps:

$mainyear=$year+1;
$epiphanystamp=strtotime($mainyear."-01-06"); //christmas season up to $epiphanystamp
$candlemassstamp=strtotime($mainyear."-02-02"); //epiphany season up to $candlemassstamp
$easterstamp=easter_date($mainyear); //lent up to easter
$palmsundaystamp=0;
$secondsundayofeaster=0;
$beforeadventstamp=findsunday("-10-30",$mainyear);  //4th sunday before advent or all saints day


// Find liturgical year from advent onwards A B or C
$remainder=($year %3);
if($remainder==0)$lityear="A";
if($remainder==1)$lityear="B";
if($remainder==2)$lityear="C";

echo "Liturgical year: ".$lityear."<br><br>";

$searchtimestamp=$adventstamp;
addtodb("Advent Sunday",date("Y-m-d",$searchtimestamp ),10,10,"Principal Feast");

//Deal with Andrew as a Festival may not be celebrated on Sundays in Advent, Lent or Eastertide. Festivals coinciding with a Principal Feast or Principal Holy Day are transferred to the first available day.

if (isthissunday("-11-30",$year)=="no"){
addtodb("Andrew",$year."-11-30",101,10,"Festival");
}else{
addtodb("Andrew (from Nov 30)",$year."-12-01",101,10,"Festival");	
}


// sundays after advent 

$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Advent 2",date("Y-m-d", $searchtimestamp),11,10);

$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Advent 3",date("Y-m-d", $searchtimestamp),12,10);

$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Advent 4",date("Y-m-d", $searchtimestamp),13,10);

//other days up to end of the year 

addtodb("Christmas Eve",$year."-12-24",101,10);
addtodb("Christmas Day",$year."-12-25",14,10,"Principal Feast");
addtodb("Stephen",$year."-12-26",15,10,"Festival");
addtodb("John",$year."-12-27",16,10,"Festival");
addtodb("The Holy Innocents",$year."-12-28",17,10,"Festival");

	
$searchtimestamp=findsunday("-12-26",$year);
addtodb("First Sunday of Christmas",date("Y-m-d", $searchtimestamp),13,10);

// Second sunday of Christmas only if before 6th Jan
$secondsundaychristmas=strtotime('+1 week', $searchtimestamp);
if (date("d", $searchtimestamp)<"6"){
addtodb("Second Sunday of Christmas",date("Y-m-d", $secondsundaychristmas),13,10);
}

$year= $year+1; // we have moved up a year

addtodb("Naming and Circumcision of Jesus",$year."-01-01",19,10,"Festival");

addtodb("Epiphany",$year."-01-06",101,10,"Principal Feast <br>");

//  If the Epiphany (6 January) falls on a weekday it may, for pastoral reasons, be celebrated on the Sunday falling between 2 and 8 January inclusive. 
$transferbaptismchrist=0;

if (isthissunday("-01-06",$year)=="no"){
	
$searchtimestamp=findsunday("-01-02",$year);
addtodb("Epiphany (from 6th Jan)",date("Y-m-d", $searchtimestamp),13,10,"Principal Feast");
//If, for pastoral reasons, the Epiphany is celebrated on Sunday 7 or 8 January, The Baptism of Christ is transferred to Monday 8 or 9 January.

if (date("d", $searchtimestamp)=="07"){

$transferbaptismchrist=1;

}
if (date("d", $searchtimestamp)=="08"){
$transferbaptismchrist=1;


}

}


if (isthissunday("-01-06",$year)=="yes"){
//6 January is a Sunday
$searchtimestamp=findsunday("-01-07",$year);
addtodb("Second Sunday of Epiphany - The Baptism of Christ",date("Y-m-d", $searchtimestamp),13,10,"Festival");
$searchtimestamp=strtotime('+1 week', $searchtimestamp);

$candlemasstamp=strtotime($year."-02-02");

if (strtotime('+1 week', $searchtimestamp)<=$candlemasstamp){
addtodb("Third Sunday of Epiphany",date("Y-m-d", $searchtimestamp),13,10);
}

if (strtotime('+1 week', $searchtimestamp)<=$candlemasstamp){
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Fourth Sunday of Epiphany",date("Y-m-d", $searchtimestamp),13,10);
}

}
else
{
//6 January is not a Sunday
$searchtimestamp=findsunday("-01-07",$year);

//The Baptism of Christ must be transferred if Epiphany is celebrated on Sunday 7 or 8 January but otherwise may not be transferred  
if ($transferbaptismchrist==1){
addtodb("First Sunday of Epiphany - The Baptism of Christ (if Epiphany was celebrated on 6th Jan)",date("Y-m-d", $searchtimestamp),13,11);
$baptismtimestamp=strtotime('+1 day', $searchtimestamp);
addtodb("The Baptism of Christ (if Epiphany was celebrated on 7th or 8th Jan)",date("Y-m-d", $baptismtimestamp),13,10,"Festival");
}
else
{	
addtodb("First Sunday of Epiphany - The Baptism of Christ",date("Y-m-d", $searchtimestamp),13,10,"Festival");
}

$candlemasstamp=strtotime($year."-02-02");

if (strtotime('+1 week', $searchtimestamp)<=$candlemasstamp){
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Second Sunday of Epiphany",date("Y-m-d", $searchtimestamp),13,10);
}

if (strtotime('+1 week', $searchtimestamp)<=$candlemasstamp){
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Third Sunday of Epiphany",date("Y-m-d", $searchtimestamp),13,10);
}

if (strtotime('+1 week', $searchtimestamp)<=$candlemasstamp){
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Fourth Sunday of Epiphany",date("Y-m-d", $searchtimestamp),13,10);
}	
}



$wopday1=$year."-01-18";
$wopstamp=strtotime($wopday1);

addtodb("Week of Prayer for Christian Unity - Day one",date("Y-m-d", $wopstamp),13,10);
$wopstamp=strtotime('+1 day', $wopstamp);
addtodb("Week of Prayer for Christian Unity - Day two",date("Y-m-d", $wopstamp),13,10);
$wopstamp=strtotime('+1 day', $wopstamp);
addtodb("Week of Prayer for Christian Unity - Day three",date("Y-m-d", $wopstamp),13,10);
$wopstamp=strtotime('+1 day', $wopstamp);
addtodb("Week of Prayer for Christian Unity - Day four",date("Y-m-d", $wopstamp),13,10);
$wopstamp=strtotime('+1 day', $wopstamp);
addtodb("Week of Prayer for Christian Unity - Day five",date("Y-m-d", $wopstamp),13,10);
$wopstamp=strtotime('+1 day', $wopstamp);
addtodb("Week of Prayer for Christian Unity - Day six",date("Y-m-d", $wopstamp),13,10);
$wopstamp=strtotime('+1 day', $wopstamp);
addtodb("Week of Prayer for Christian Unity - Day seven",date("Y-m-d", $wopstamp),13,10);
$wopstamp=strtotime('+1 day', $wopstamp);
addtodb("Week of Prayer for Christian Unity - Day eight",date("Y-m-d", $wopstamp),13,10);


addtodb("The Presentation of Christ in the Temple - Candlemas",$year."-02-02",101,10,"Festival");
//  The Presentation of Christ in the Temple (Candlemas) is celebrated either on 2 February or on the Sunday falling between 28 January and 3 February. Candlemas may be celebrated on the Sunday falling between 28 January and 3 February
if (isthissunday("-02-02",$year)=="no"){
$searchtimestamp=findsunday("-01-28",$year);	
addtodb("The Presentation of Christ in the Temple - Candlemas (from 2nd Feb)",date("Y-m-d", $searchtimestamp),101,10,"Festival"); 
}

//Ordinary Time1 before lent. This begins on the day following $candlemassstamp -the Presentation of Christ


//find Fifth Sunday before Lent

$searchtimestamp=$easterstamp;
$searchtimestamp=strtotime('-11 weeks', $searchtimestamp);

if ($searchtimestamp>$candlemassstamp){
addtodb("Fifth Sunday before Lent",date("Y-m-d", $searchtimestamp),13,10);
}

$searchtimestamp=strtotime('+1 week', $searchtimestamp);
if ($searchtimestamp>$candlemassstamp){
addtodb("Fourth Sunday before Lent",date("Y-m-d", $searchtimestamp),13,10);
}

$searchtimestamp=strtotime('+1 week', $searchtimestamp);
if ($searchtimestamp>$candlemassstamp){
addtodb("Third Sunday before Lent",date("Y-m-d", $searchtimestamp),13,10);
}

$searchtimestamp=strtotime('+1 week', $searchtimestamp);
if ($searchtimestamp>$candlemassstamp){
addtodb("Second Sunday before Lent",date("Y-m-d", $searchtimestamp),13,10);
}

$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Sunday next before Lent",date("Y-m-d", $searchtimestamp),13,10);

$searchtimestamp=strtotime('+3 days', $searchtimestamp);
addtodb("Ash Wednesday",date("Y-m-d", $searchtimestamp),13,10,"Principal Holy Day");

//First Sunday of Lent
$searchtimestamp=findsunday(date("-m-d",$searchtimestamp), $year); 
addtodb("First Sunday of Lent",date("Y-m-d", $searchtimestamp),13,10);

$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Second Sunday of Lent",date("Y-m-d", $searchtimestamp),13,10);

$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Third Sunday of Lent",date("Y-m-d", $searchtimestamp),13,10);

$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Fourth Sunday of Lent",date("Y-m-d", $searchtimestamp),13,10);
addtodb("Mothering Sunday",date("Y-m-d", $searchtimestamp),13,10);

$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Fifth Sunday of Lent",date("Y-m-d", $searchtimestamp),13,10);

$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Palm Sunday",date("Y-m-d", $searchtimestamp),13,10);

$searchtimestamp=strtotime('+1 day', $searchtimestamp);
addtodb("Monday of Holy Week",date("Y-m-d", $searchtimestamp),13,10);


$searchtimestamp=strtotime('+1 day', $searchtimestamp);
addtodb("Tuesday of Holy Week",date("Y-m-d", $searchtimestamp),13,10);
$searchtimestamp=strtotime('+1 day', $searchtimestamp);
addtodb("Wednesday of Holy Week",date("Y-m-d", $searchtimestamp),13,10);
$searchtimestamp=strtotime('+1 day', $searchtimestamp);
addtodb("Maundy Thursday",date("Y-m-d", $searchtimestamp),13,10,"Principal Holy Day");
$searchtimestamp=strtotime('+1 day', $searchtimestamp);
addtodb("Good Friday",date("Y-m-d", $searchtimestamp),13,10,"Principal Holy Day");
$searchtimestamp=strtotime('+1 day', $searchtimestamp);
addtodb("Easter Eve",date("Y-m-d", $searchtimestamp),13,10);
addtodb("Easter Vigil",date("Y-m-d", $searchtimestamp),13,10);

$searchtimestamp=strtotime('+1 day', $searchtimestamp);
addtodb("Easter Day",date("Y-m-d", $searchtimestamp),13,10,"Principal Feast");

$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Second Sunday of Easter",date("Y-m-d", $searchtimestamp),13,10);

$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Third Sunday of Easter",date("Y-m-d", $searchtimestamp),13,10);
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Fourth Sunday of Easter",date("Y-m-d", $searchtimestamp),13,10);
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Fifth Sunday of Easter",date("Y-m-d", $searchtimestamp),13,10);
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Sixth Sunday of Easter",date("Y-m-d", $searchtimestamp),13,10);

$searchtimestamp=strtotime('+39 days', $easterstamp);
addtodb("Ascension Day",date("Y-m-d", $searchtimestamp),13,10,"Principal Feast");

$searchtimestamp=findsunday(date("-m-d", $searchtimestamp),$year);
addtodb("Seventh Sunday of Easter - Sunday after Ascension Day",date("Y-m-d", $searchtimestamp),13,10);

$searchtimestamp=strtotime('+7 weeks', $easterstamp);
addtodb("Pentecost - Whit Sunday",date("Y-m-d", $searchtimestamp),13,10,"Principal Feast");

// https://www.churchofengland.org/prayer-and-worship/worship-texts-and-resources/common-worship/churchs-year/calendar



//Ordinary Time

$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Trinity Sunday",date("Y-m-d", $searchtimestamp),13,10,"Principal Feast");

$searchtimestamp=strtotime('+4 days', $searchtimestamp);
addtodb("Day of Thanksgiving for Holy Communion (Corpus Christi",date("Y-m-d", $searchtimestamp),13,10,"Festival");
//  The Thursday after Trinity Sunday may be observed as the Day of Thanksgiving for the Holy Communion (sometimes known as Corpus Christi), and may be kept as a Festival. Where the Thursday following Trinity Sunday is observed as a Festival to commemorate the Institution of the Holy Communion and that day falls on a date which is also a Festival, the commemoration of the Institution of Holy Communion shall be observed on that Thursday and the other occurring Festival shall be transferred to the first available day.



$searchtimestamp=strtotime('+3 days', $searchtimestamp);
addtodb("First Sunday after Trinity",date("Y-m-d", $searchtimestamp),13,10,"Ordinary Time");
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Second Sunday after Trinity",date("Y-m-d", $searchtimestamp),13,10,"Ordinary Time");
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Third Sunday after Trinity",date("Y-m-d", $searchtimestamp),13,10,"Ordinary Time");
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Fourth Sunday after Trinity",date("Y-m-d", $searchtimestamp),13,10,"Ordinary Time");
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Fifth Sunday after Trinity",date("Y-m-d", $searchtimestamp),13,10,"Ordinary Time");
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Sixth Sunday after Trinity",date("Y-m-d", $searchtimestamp),13,10,"Ordinary Time");
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Seventh Sunday after Trinity",date("Y-m-d", $searchtimestamp),13,10,"Ordinary Time");
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Eigth Sunday after Trinity",date("Y-m-d", $searchtimestamp),13,10,"Ordinary Time");
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Ninth Sunday after Trinity",date("Y-m-d", $searchtimestamp),13,10,"Ordinary Time");
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Tenth Sunday after Trinity",date("Y-m-d", $searchtimestamp),13,10,"Ordinary Time");
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Eleventh Sunday after Trinity",date("Y-m-d", $searchtimestamp),13,10,"Ordinary Time");
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Twelfth Sunday after Trinity",date("Y-m-d", $searchtimestamp),13,10,"Ordinary Time");
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Thirteenth Sunday after Trinity",date("Y-m-d", $searchtimestamp),13,10,"Ordinary Time");
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Fourteenth Sunday after Trinity",date("Y-m-d", $searchtimestamp),13,10,"Ordinary Time");
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Fifteenth Sunday after Trinity",date("Y-m-d", $searchtimestamp),13,10,"Ordinary Time");$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Sixteenth Sunday after Trinity",date("Y-m-d", $searchtimestamp),13,10,"Ordinary Time");
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Seventeenth Sunday after Trinity",date("Y-m-d", $searchtimestamp),13,10,"Ordinary Time");
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Eighteenth Sunday after Trinity",date("Y-m-d", $searchtimestamp),13,10,"Ordinary Time");
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Nineteenth Sunday after Trinity",date("Y-m-d", $searchtimestamp),13,10,"Ordinary Time");
$searchtimestamp=strtotime('+1 week', $searchtimestamp);

if($searchtimestamp<$beforeadventstamp)addtodb("Twentieth Sunday after Trinity",date("Y-m-d", $searchtimestamp),13,10,"Ordinary Time");
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
if($searchtimestamp<$beforeadventstamp)addtodb("Twenty-first Sunday after Trinity",date("Y-m-d", $searchtimestamp),13,10,"Ordinary Time");
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
if($searchtimestamp<$beforeadventstamp)addtodb("Twenty-second Sunday after Trinity",date("Y-m-d", $searchtimestamp),13,10,"Ordinary Time");
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
if($searchtimestamp<$beforeadventstamp)addtodb("Twenty-third Sunday after Trinity",date("Y-m-d", $searchtimestamp),13,10,"Ordinary Time");
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
if($searchtimestamp<$beforeadventstamp)addtodb("Twenty-fourth Sunday after Trinity",date("Y-m-d", $searchtimestamp),13,10,"Ordinary Time");
$searchtimestamp=strtotime('+1 week', $searchtimestamp);
if($searchtimestamp<$beforeadventstamp)addtodb("Twenty-fifth Sunday after Trinity",date("Y-m-d", $searchtimestamp),13,10,"Ordinary Time");


$searchtimestamp=$beforeadventstamp;
addtodb("All Saints",date("Y-m-d", $searchtimestamp),13,10,"Principal Feast");
addtodb("Fourth Sunday before Advent",date("Y-m-d", $searchtimestamp),13,10);

$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Third Sunday before Advent",date("Y-m-d", $searchtimestamp),13,10);

$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Second Sunday before Advent",date("Y-m-d", $searchtimestamp),13,10);

$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Sunday next before Advent - Christ the King",date("Y-m-d", $searchtimestamp),13,10);

$searchtimestamp=strtotime('+1 week', $searchtimestamp);
addtodb("Advent",date("Y-m-d", $searchtimestamp),13,10);

// add fixed days from Jan 1st to next advent

addtodb("The Conversion of Paul",$year."-01-25",101,10,"Festival");


addtodb("Joseph of Nazareth",$year."-03-19",101,10,"Festival");
// When St Joseph’s Day falls between Palm Sunday and the Second Sunday of Easter inclusive, it is transferred to the Monday after the Second Sunday of Easter or, if the Annunciation has already been moved to that date, to the first available day thereafter.

addtodb("The Annunciation of Our Lord",$year."-03-25",101,10,"Principal Feast");
//The Annunciation falling on a Sunday must be transferred

addtodb("George",$year."-04-23",101,10,"Festival");
addtodb("Mark",$year."-04-25",101,10,"Festival");
//When St George’s Day or St Mark’s Day falls between Palm Sunday and the Second Sunday of Easter inclusive, it is transferred to the Monday after the Second Sunday of Easter. If both fall in this period, St George’s Day is transferred to the Monday and St Mark’s Day to the Tuesday. When the Festivals of George and Mark both occur in the week following Easter and are transferred in accordance with these Rules in a place where the calendar of The Book of Common Prayer is followed, the Festival of Mark shall be observed on the second available day so that it will be observed on the same day as in places following alternative authorized Calendars, where George will have been transferred to the first available free day. St Joseph, St George or St Mark falling between Palm Sunday and the Second Sunday of Easter inclusive must be transferred


addtodb("Philip and James",$year."-05-01",101,10,"Festival");
addtodb("Matthias",$year."-05-14",101,10,"Festival");
addtodb("The Visit of the Blessed Virgin Mary to Elizabeth",$year."-05-31",101,10,"Festival");
addtodb("Barnabas",$year."-06-11",101,10,"Festival");
addtodb("The Birth of John the Baptist",$year."-06-24",101,10,"Festival");
addtodb("Peter and Paul",$year."-06-29",101,10);
addtodb("Peter",$year."-06-29",101,10,"Festival");
addtodb("Thomas",$year."-07-03",101,10,"Festival");
addtodb("Mary Magdalene",$year."-07-22",101,10,"Festival");
addtodb("James",$year."-07-25",101,10,"Festival");
addtodb("The Transfiguration of Our Lord",$year."-08-06",101,10,"Festival");
addtodb("The Blessed Virgin Mary",$year."-08-15",101,10,"Festival");
//The Festival of the Blessed Virgin Mary (15 August) may, for pastoral reasons, be celebrated instead on 8 September.

addtodb("Bartholomew",$year."-08-24",101,10,"Festival");
addtodb("Holy Cross Day",$year."-09-14",101,10,"Festival");
addtodb("Matthew",$year."-09-21",101,10,"Festival");
addtodb("Michael and All Angels",$year."-09-29",101,10,"Festival");
addtodb("Luke",$year."-10-18",101,10,"Festival");
addtodb("Simon and Jude",$year."-10-28",101,10,"Festival");
addtodb("All Saints’ Day",$year."-11-01",101,10,"Festival");
// All Saints’ Day is celebrated on either 1 November or the Sunday falling between 30 October and 5 November; if the latter there may be a secondary celebration on 1 November. All Saints' Day may be celebrated on the Sunday falling between 30 October and 5 November
addtodb("Remembrance Day",$year."-11-11",101,10);
$remembrancesundaystamp=findsunday("-11-08",$year);
addtodb("Remembrance Sunday",date("Y-m-d", $remembrancesundaystamp),13,10);
addtodb("Andrew",$year."-11-30",101,10,"Festival");












$connection = new mysqli($servername, $username, $password, $dbname);
$query="SELECT *  FROM `thisyeardates` ORDER BY `thisyeardate`, `item_priority` ASC";
$result = $connection->query($query);
$myrow = $result->fetch_assoc();

$checksameday="";
do{
	
	$datex = date_create($myrow['thisyeardate']);
	$dayname= date_format($datex, 'D j M Y');
	$newweek= date_format($datex, 'D');
	if ($newweek=="Sun"){
	if ($checksameday==$dayname){
	echo "<strong>".$dayname."</strong> ".$myrow['datename']." ".$myrow['type']."<br>";
	}else{
	echo "<br><strong>".$dayname."</strong> ".$myrow['datename']." ".$myrow['type']."<br>";
	}
	}else{
		echo $dayname." ".$myrow['datename']." ".$myrow['type']."<br>";
	}
	$checksameday=$dayname;
} while ($myrow = $result->fetch_assoc());

