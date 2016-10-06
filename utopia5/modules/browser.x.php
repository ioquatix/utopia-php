<?

define (BROWSER_UNKNOWN, 0);
define (BROWSER_MSIE, 1);
define (BROWSER_GECKO, 2);
define (BROWSER_SAFARI, 4);

function browser ($useragent = null) {
 if ($useragent === null)
  $useragent = gvar("server", "HTTP_USER_AGENT");
 
 if (strpos ($useragent, "MSIE") !== false)
  return BROWSER_MSIE;
 else if (strpos ($useragent, "Safari") !== false)
  return BROWSER_SAFARI;
 else if (strpos ($useragent, "Gecko") !== false)
  return BROWSER_GECKO;
 else
  return BROWSER_UNKNOWN;
}

?>