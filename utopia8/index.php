<?
global $start_time;
list ($s, $us) = explode (" ", microtime());
$start_time = (float)$us + (float)$s;

include ("utopia8/utopia.php");

//temporary fix for for broken apache configurations
$request_uri = $_SERVER['REQUEST_URI'];
$scpos = strpos ($request_uri, ";");
if ($scpos !== false) $request_uri = substr ($request_uri, 0, $scpos);

$_REQUEST['request'] = urldecode($request_uri);
request::root ($_REQUEST['request'], $_REQUEST);

request::finish ();
?>
