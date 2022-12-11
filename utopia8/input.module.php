<?

function istag ($tag, $type) {
 return preg_match ($type, $tag);
}

function tagname ($tag) {
 $r = preg_split('/<\/?([A-Za-z:][A-Za-z0-9_]*)[^>]*>/ims', $tag, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
 return $r[0];
}

function splittags ($input) {
 return preg_split('/(<[^<>]+>)/ims', $input, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
}

function stripalltags ($input) {
 return preg_replace('/<[^>]*>/ims', '',  $input);
}

class input {
 static function proper ($p) {
  console::log ('depreciated', 'input::proper -> words::proper');
  ld ('words');
  return words::proper ($p);
 }

 static function encode ($d) {
  return base64_encode ($d);
 }

 static function decode ($d) {
  return base64_decode ($d);
 }

 const ANY_TAG = '/<[^>]*>/ims';
 const START_TAG = '/<([A-Za-z:][A-Za-z0-9_]*)([\s]*)(([\s]*)([A-Za-z][A-Za-z0-9_]*)([\s]*)=([\s]*)(("([^"]*)")|(\'([^\']*)\')))*>/ims';
 const COMPLETE_TAG = '/<([A-Za-z:][A-Za-z0-9_]*)([\s]*)(([\s]*)([A-Za-z][A-Za-z0-9_]*)([\s]*)=([\s]*)(("([^"]*)")|(\'([^\']*)\')))*([\s]*)\/>/ims';
 const END_TAG = '/<\/([A-Za-z\:][A-Za-z0-9_]*)>/ims';
 
 private $tags = array ();

 function __construct () {
  $p = func_get_args ();
  if (count($p))
   $this->tags = $p;
 }
 
 function transform ($input) {
  return input::process ($input, $this->tags);
 }
 
 //strip all tags except for those in $tags
 static function strip ($input, $tags = array ('p', 'br', 'b', 'i', 'a')) {
  $input = splittags ($input);
  $output = '';
  foreach ($input as $i) {
   if (istag ($i, input::ANY_TAG)) {
    if (array_search (tagname($i), $tags) !== false)
     $output .= $i;
    else continue;
   } else
    $output .= $i;
  }
  return $output;
 }

 static $unicode = true;

 static function text ($input) {
  $input = html_entity_decode ($input, ENT_QUOTES);
  //if (input::$unicode == true) return htmlentities ($input, ENT_QUOTES);
  $input = str_replace (array(chr(145), chr(146), chr(147), chr(148), chr(150)), array("'", "'", '&quot;', '&quot;', '&mdash;'), $input);
  $input = htmlentities ($input, ENT_QUOTES);
  return $input;
 }
 
 static function validate ($input) {
  $input = splittags ($input);
  $stack = array ();
  $output = '';
  
  
  foreach ($input as $i) {
   if (istag ($i, input::COMPLETE_TAG)) {
    $output .= $i;
   } else if (istag ($i, input::START_TAG)) {
    $output .= $i;
	array_push ($stack, tagname ($i));
   } else if (istag ($i, input::END_TAG)) {
	if (($c = tagname($i)) != end($stack)) {
	 if (($indx = array_search ($c, $stack)) !== false) {
	  while ($indx < count($stack)) {
	   $output .= '</'.array_pop($stack).'>';
	  }
	 } else
	  $output .= input::text($i);
	} else {
	 array_pop ($stack);
	 $output .= $i;
	}
   } else {
    $output .= input::text ($i);
   }
  }
  $end = '';
  foreach ($stack as $i)
   $end = "</$i>" . $end;
   
  return $output . $end;
 }
 
 static function format ($input) {
  $input = trim ($input);
  $input = str_replace ("\r", '', $input);
  $input = '<p>' . str_replace ("\n\n", "</p><p>", $input) . '</p>';
  return str_replace ("\n", "<br />", $input);
 }

 static function process ($input, $tags = array('p', 'br', 'i', 'b', 'a')) {
  return input::validate (input::format (input::strip ($input, $tags)));
 }

 static function clean ($input, $tags = array ('p', 'br', 'i', 'b', 'a')) {
  return input::validate (input::strip ($input, $tags));
 }

 static function preformat ($input) {
  $input = trim ($input);
  $input = input::strip ($input);
  return str_replace ("\n", "<br />", $input);
 }

};

?>
