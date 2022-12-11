<?

class browser {
 const UNKNOWN = 0;
 const MSIE = 1;
 const GECKO = 2;
 const SAFARI = 4;

 const SEARCHBOT = 10;
 const WEBBROWSER = 11;
 
 static function agent ($useragent = null) {
  if ($useragent === null)
   $useragent = $_SERVER["HTTP_USER_AGENT"];
 
  if (strpos ($useragent, "MSIE") !== false)
   return browser::MSIE;
  else if (strpos ($useragent, "Safari") !== false)
   return browser::SAFARI;
  else if (strpos ($useragent, "Gecko") !== false)
   return browser::GECKO;
  else
   return browser::UNKNOWN;
 }

 static function client ($useragent = null) {
  if ($useragent === null)
   $useragent = $_SERVER["HTTP_USER_AGENT"];

  if (stripos ($useragent, "bot") !== false)
   return browser::SEARCHBOT;
  else if (stripos ($useragent, "slurp") !== false)
   return browser::SEARCHBOT;
  else if (stripos ($useragent, "search") !== false)
   return browser::SEARCHBOT;
  else if (stripos ($useragent, "ia_archiver") !== false)
   return browser::SEARCHBOT;
  else if (trim($useragent) == "")
   return browser::UNKNOWN;
  else
   return browser::WEBBROWSER;
   
 }
} 

?>
