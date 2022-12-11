<?

class time {
 const TINY = 'j/n';
 const SHORT = 'j/n G:ia';
 const MEDIUM = 'F j, Y, G:ia T';
 const DAYMONTH = 'F j, Y';
 //new
 const TIME = 'G:i';
 const DATE = 'F j, Y';
 const DATETIME = 'j M, Y G:i';
 const DATETIMEYEAR = 'j/n G:i Y';
 const SQL = 'Y-m-d H:i:s';

 private static $zone = '';
 public static $zones = array (
  "New Zealand" => array ("NZT", "Pacific/Auckland"),
  "England/London" => array ("BSD", "Europe/London"),
  "Universal Time" => array ("UTC", "UTC")
 );
 
 private $ts;
 
 function __construct () {
  assert (time::$zone != '');
  $this->ts = time();
 }

 function compare ($t) {
  return $this->ts - $t->ts;
 }

 function zoneoffset () {
  return date ('Z', $this->ts);
 }
 
 static function strtotime ($t) {
  if (defined ('TIME_DEBUG')) console::log ('time::strtotime', $t . " -> " . str_replace (',', '', $t) . " -> " . strtotime (str_replace (',', '', $t)));
  return strtotime (str_replace (',', '', $t));
 }
 
 static function setzone ($z) {
  time::$zone = $z;
  putenv ("TZ=".time::$zones[$z][1]);
 }
 
 static function getzone () {
  return time::$zone;
 }

 function &fromstamp ($timestamp) {
  $this->ts = $timestamp;
  return $this;
 }

 function &from ($d='now', $tz='') {
  if ($d == '')
   return $this;
  if ($tz) {
   $otz = getenv("TZ");
   time::setzone ($tz);
   $this->ts = time::strtotime ($d);
   putenv("TZ=".$otz);
  } else
   $this->ts = time::strtotime ($d);
  return $this;
 }

 function to ($fmt, $tz='') {
  if ($tz) {
   $otz = getenv("TZ");
   time::setzone ($tz);
   $r = date ($fmt, $this->ts);
   putenv("TZ=".$otz);
   return $r;
  }
  return date ($fmt, $this->ts);
 }
 
 function __toString () {
  return $this->todate();
 }
 
 //'intelligent' output
 // includes the time if non-zero
 function totime (&$tz='') {
  $f = '';
  if (date("Y") == date("Y", $this->ts))
   $f = 'F j';
  else
   $f = 'F j, Y';
  if (date("Hi", $this->ts) != "0000")
   $f .= ', G:ia';
  if ($tz != time::$zone)
   $f .= ' T';
  return $this->to ($f, $tz);
 }

 function todate (&$tz='') {
  $f = '';
  //if (date("Y") == date("Y", $this->ts))
  // $f = 'F j';
  //else
   $f = 'F j, Y';
  if ($tz != time::$zone)
   $f .= ' T';
  return $this->to ($f, $tz);
 }
 
 //these functions above need to be fixed and also add todatetime
 
 function tosql () {
  return $this->to (time::SQL, "GMT");
 }
 
 function &fromsql ($v) {
  $this->from ($v . " GMT");
  return $this;
 }

 static function now () {
  return new time;
 }

 //maybe this has a bug crawling around in the thicket.
 static function valid ($d) {
  return time::strtotime ($d) and $d;
 }
}

?>
