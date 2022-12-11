<?

function coerce ($val, $type="string") {
 if ($type == "integer") {
  return intval(ereg_replace ("[^0-9]", "", $val));
 } else if ($type == "real") {
  return floatval(ereg_replace ("[^0-9]", "", $val));
 } else if ($type == "id") {
  $val = intval(ereg_replace ("[^0-9]", "", $val));
  if ($val < 0) return 0; else return $val;
 } else if ($type == "string") {
  return strval ($val);
 }
}

$class_path = "utopia8/";
$class_version = "module";
function ld () {
 global $class_path, $class_version;
 $args = func_get_args();
 foreach($args as $c) {
  $c = $class_path . strtolower($c) . '.' . $class_version . ".php";
  require_once($c);
 }
}

function file_content_type ($file) {
 return exec (console::debug("file -bi $file"));
}

//not good implementation.. use escapeshellargs or something
if (!function_exists ("mime_content_type")) {
 function mime_content_type ($file) {file_content_type ($file);}
}

function call ($p) {
 $args = func_get_args();
 array_shift ($args);
 if (is_object ($p))
  return call_user_func_array (array($p, "__apply"), $args);
 else {
  return call_user_func_array ($p, $args);
 }
}

function keys () {
 $p = func_get_args();
 $p = array_flip ($p);
 return $p;
}

//error handling
define ('OKAY', 1);
define ('FAILED', 0);

function okay ($e) {
 if ($e > 0)
  return true;
 return false;
}

function failed ($e) {
 if ($e <= 0)
  return true;
 return false;
}

//debugging
define ('DEBUG', 1);
define ('UTOPIA_DEBUG', 0 | DEBUG);

class console {
 public static $standard = null;
 var $buffer = '';
 
 static function debug ($v) {
  console::log ('debug', $v);
  return $v;
 }
 
 static function log ($where, $what) {
  if (!console::$standard) { 
   console::$standard = new console;
   if (UTOPIA_DEBUG) request::handle_finish (array (console::$standard, 'dump'));
  }
  
  console::$standard->write ($where . ': ' . $what);
  error_log ($where . ': ' . $what);
 }
 
 function __construct ($file = '') {
  if ($file)
   $this->file = $file;
 }
 
 function write ($msg) {
  $this->buffer .= $msg . "\n";
  error_log ($msg);
 }
 
 function dump () {
  if ($this->buffer != "") {
   ld ('input');
   return input::format ($this->buffer);
  }
 }
}

class document {
 static private $types = array();
 static function link ($to, $args = array()) {
  if (strncmp ($to, "./", 2) == 0) {
   $dif = substr_count (request::$node->address, '/') - substr_count (node::$current->path, '/');
   $to = str_repeat ('../', $dif) . substr($to, 2);
  }
  //fix for broken browsers (ie, internet explorer)
  if (substr($to, -1, 1) == '.') {
   $to = substr ($to, 0, strlen ($to) -1) . 'index';
  }
  while (list($k, $v) = each ($args))
    $a .= ";" . $k . "=" . str_replace (array('%2F', '%40'), array('/', '@'), urlencode ($v));
  if ($to == '') $to = "./";
  return $to . $a;
 } 

 static function postprocess ($buffer) {
  if (preg_match('/(error<\/b>:)(.*?)(<br)/ism', $buffer, $regs)) { 
   $error = $regs[2];
   return request::error (request::PARSE_ERROR, $error . ' in ' . node::$current->file());
  }
  return $buffer;
 }

 static function begin () {
  $a = func_get_args(); array_push (document::$types, $a);
  ob_start(array('document', 'postprocess'));
 }

 static function end ($buffer=null) {
  $t = array_pop (document::$types);
  $n = array_shift ($t);
  array_unshift ($t, ob_get_clean());
  return call_user_func_array ($n, $t);
 }
}

function xml ($buf, $name='root') {
 $r = new parser;
 return $r->transform (request::$node->address, $buf, $name);
}

function plaintext ($b) { ld('input'); return '<pre>' . input::text ($b) . '</pre>'; }
function raw ($b) { return $b; }
function none () { return ''; }

class node {
 var $name; // the absolute name of the node /a/'name'
 var $address; // the absolute address of the node '/a/node'
 var $path; // the actual location of the file
 var $extn = ".node"; // the type of node we are looking at
 
 function file () {return $this->path . $this->extn;}
 function directory () {return substr ($this->path, 0, strrpos ($this->path, '/'));}
 function filename () {return substr ($this->path, strrpos ($this->path, '/')) . $this->extn;}

 function __construct ($addr) {
  $parts = explode ('/', $addr);
  $name = array_pop ($parts);
  if (trim($name) == '')
   $name = "index";
  else {
   $p = strrpos ($name, '.');
   if ($p !== false) {
    $this->extn = substr ($name, $p);
    $name = substr ($name, 0, $p);
   }
  }
   
  $this->name = $name;

  $this->address = implode ("/", $parts) . "/" . $name;
      
  do {
   $this->path = implode ("/", $parts) . "/$name";
   if (@is_file ("." . $this->path . $this->extn))
    break;
   array_pop ($parts);
  } while (count($parts) > 0);
 }

 function type () {
  if (isset ($this->metatype) and $this->metatype != '') return $this->metatype;
  if ($this->extn == ".node") return "text/utopia";
  return mime_content_type ("." . $this->path . $this->extn);
 }

 function data () {
  if (is_file ('./' . $this->path . $this->extn))
   return implode (file ('./' . $this->path . $this->extn));
  
  request::error (request::FILE_ERROR, "Could not find node $this->path");
  return null;
 }

 function render ($request) {
  if (($data = $this->data()) !== null) {
   node::$current = $this;
   eval ("?>" . $data . "<?");
   //if there was an error (parse|fatal) we will
   //call the ob handler and die (ie, not get here)
   return document::end();
  } //else
  return '';
 }
 
 public static $current = null;
}

class attributes {
 public $values;
 public $data;

 function clear ($v = array(), $d = '') {
  $this->values = $v;
  $this->data = $d;
 }

 function __construct ($v = array(), $d = '', $merge = null) {
  $this->values = $v;
  $this->data = $d;
  if ($merge != null)
   $this->merge ($merge->all());
 }

 function get ($n) {
  return $this->values [$n];
 }

 function __get ($n) {
  return $this->values[$n];
 }
 
 function set ($n, $v) {
  return $this->values [$n] = $v;
 }
 
 function __set ($n, $v) {
  return $this->values [$n] = $v;
 }
 
 function has ($n, $v = true, $nv = false) {
  if (isset ($this->values[$n]))
   return $v;
 }
 
 function un ($n) {
  unset ($this->values[$n]);
 }
 
 function &all() {
  return $this->values;
 }
 
 function merge ($ar) {
  $this->values = array_merge ($this->values, $ar);
 }
}

class request {
 const FILE_ERROR = -2;
 const FATAL_ERROR = -3;
 const PARSE_ERROR = -4;
 const MARKUP_ERROR = -6;
 const ACCESS_ERROR = -8;
 
 static $error_names = array (-2 => 'file not found', -3 => 'fatal php', -4 => 'code parse', -6 => 'xml', -8 => 'access rights');

 static private $error_handlers = array ();
 static private $default_error_handler = array ('request', 'handle_error');
 
 //manipulate error handlers
 static function error_handler ($name, $what = null) {
  if ($name)
   request::$error_handlers[$name] = $what;
  else
   request::$default_error_handler = $what;
 }
 
 //default error handler
 static function handle_error ($kind, $err) {
  $msg = '<p class="error">' . 'A '. request::$error_names[$kind].' error occurred:</p>';
  $msg .= '<p class="error">' . $err . '</p>';
  if (node::$current)
  $msg .= '<p class="error"> while processing ' . node::$current->address . '</p>';
  $msg .= '<p class="error">' . console::$standard->dump() . '</p>';
  if ($kind == request::ACCESS_ERROR) {
   request::finish();
  }
  return $msg;
 }
 
 //when an error occurs call this function to handle it
 static function error ($type, $msg) {
  console::log ('request', $msg . " ($type)");
  $c = request::$error_handlers[$type];
  if ($c) return call ($c, $type, $msg);
  if (request::$default_error_handler) return call (request::$default_error_handler, $type, $msg);
 }

 //request handling -\
 static function parse ($node, $request) {
  global $start_time;
  $b = $node->render($request);
  list ($s, $us) = explode (" ", microtime());
  $end_time = (float)$us + (float)$s;
  echo str_replace ("%time%", number_format(($end_time - $start_time), 4), $b);
 }

 static function passthrough ($node, $request) {
  header ("Content-Type: " . $node->type());
  //header ("Content-Length: " . filesize(".".$node->file()));
  $fh = fopen (".".$node->file(), "r");
  fpassthru ($fh);
  flush();
 }

 static function execute ($node, $request) {
  chdir ("." . $node->directory());
  include ('.' . $node->filename());
  flush();
 }

 private static $handlers = array(".node" => array('request', 'parse'), ".php" => array('request', 'execute'));
 private static $defaulthandler = array('request', 'passthrough');
 public static $node;
 private static $cache = array();

 static function include_headers ($p) {
  if ($p != '')
   request::include_headers (substr ($p, 0, strrpos ($p, "/")));
   
  if (@is_file ("./$p/node.header")) {include_once ("./$p/node.header");}
 }

 static function root ($addr, $attr) {
  request::$node = new node ($addr);
  request::include_headers (request::$node->address);
  return request::process (request::$node, new attributes($attr));
 }
 
 static function render ($addr, $req) {
  if (isset (request::$cache[$addr])) {
   return request::$cache[$addr]->render ($req);
  } else {
   request::$cache[$addr] = $n = new node ($addr);
   return $n->render ($req);
  }
 }
 
 private static $passrequests = array();
 
 static function passrequest ($tag, $attr) {
  request::$passrequests[$tag] = $attr;

 }   	 

 static function retrieverequest ($tag) {
  if (isset(request::$passrequests[$tag]))
   return request::$passrequests[$tag];
  return null; 
 }   	 
		 
 static function handler ($name, $whattodo) {
  if ($name == null)
   request::$defaulthandler = $whattodo;
  else
   request::$handlers[$name] = $whattodo;
 }
 
 static function process (&$node, $data) {
  $c = request::$handlers[strtolower($node->extn)];
  if ($c) return call ($c, $node, $data);
  if (request::$defaulthandler) return call (request::$defaulthandler, $node, $data);
 }
 
 private static $finish_handlers = array ();
 
 static function finish () {
  ob_end_flush();
  while (count (request::$finish_handlers)) {
   $f = array_pop (request::$finish_handlers);
   echo call ($f);
  }
  ini_set("mysql.trace_mode","Off");
  exit();
 }

 static function handle_finish ($f) {
  array_push (request::$finish_handlers, $f);
 }
 
 static function redirect ($to = '', $args = array(), $end = true) {
  if ($to)
   header ('Location: ' . document::link ($to, $args));
  else {
   $h = getallheaders();
   header ("Location: " . $h['Referer']);
  }
  if ($end) request::finish();
 }

 static function internal () {
  return request::$node != node::$current;
 }
}

class parser {
 static function join ($atr) {
  ld ('input');
  $r = "";
  foreach ($atr as $k => $v) {
   $v = input::text ($v);
   $r .= " $k=\"$v\"";
  }
  return $r;
 }

 private $parser;
 private $parent = null;
 private $head = null;
 private $name;
 private $attributes;
 private $data;
 private $root;

 //takes a root address for the base of processing related tags
 //takes a name to find the current tag relative to the root
 //takes an array of attributes
 //and a character data
 static function process ($root, $name, $attributes, $data) {
  if (template::exists ($name))
   return template::render ($name, new attributes($attributes, $data, request::retrieverequest($name)));

  if (array_key_exists ($name, parser::$ignore_tags)) {
   if (is_array (parser::$ignore_tags[$name]))
    $attributes = array_merge (parser::$ignore_tags[$name], $attributes);
	
   if (trim($data))
    return '<'.$name.parser::join($attributes).'>'.$data.'</'.$name.'>';
   else
    return '<'.$name.parser::join($attributes).' />';
  }

  return request::render ($root . "/" . $name, 
                          new attributes($attributes, $data, request::retrieverequest($name)));
 }

 function render () {
  if ($this->parent && template::exists ($this->parent->name."-".$this->name))
   $name = $this->parent->name."-".$this->name;
  else $name = $this->name;

  return parser::process ($this->head->root, 
                          $name, 
			  $this->attributes, 
			  $this->data);
 }

 function tag_open($parser, $tag, $attributes) {
  $child = new parser;
  $child->parent = $this;
  $child->name = $tag;
  $child->head = $this->head;
  $child->attributes = $attributes;
  $this->head->current = $child;
  xml_set_element_handler($this->head->parser, array(&$child, 'tag_open'), array(&$child, 'tag_close'));
  xml_set_character_data_handler($this->head->parser, array(&$child, 'cdata'));
 }

 function cdata($parser, $cdata) {
  $this->data .= $cdata;
 }

 function tag_close($parser, $tag) {
  $this->data = trim ($this->data);
  if ($this->parent) {
   if ($this->name[0] == ':')
    $this->parent->attributes[substr($tag, 1)] .= $this->data;
   else
    $this->parent->data .= $this->render();
   $this->head->current = $this->parent;
   xml_set_element_handler($this->head->parser, array(&$this->parent, 'tag_open'), array(&$this->parent, 'tag_close'));
   xml_set_character_data_handler($this->head->parser, array(&$this->parent, 'cdata'));
  }
 }

 function transform($root, $text, $rt = 'root') {
  $this->head = $this;
  $this->current = $this;
  $this->parser = xml_parser_create('UTF-8');
        
  xml_parser_set_option ($this->parser, XML_OPTION_CASE_FOLDING, false);
  xml_parser_set_option ($this->parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
 
  $this->root = $root;
  $text = str_replace ('&', '&amp;', $text);
        
  xml_set_element_handler($this->parser, array(&$this, 'tag_open'), array(&$this, 'tag_close'));
  xml_set_character_data_handler($this->parser, array(&$this, 'cdata'));
  xml_parse($this->parser, "<$rt>" . $text . "</$rt>");
  xml_parser_free ($this->parser);
  //really dumb but doesn't work otherwise in some cases...>_<
  if ("$this->current" != "$this")
   request::error (request::MARKUP_ERROR, 'Could not render ' . $this->current->root . $this->current->name . ' properly because it contained invalid markup');
  return $this->current->data;
 }
 
 //template tags
 static function template ($name, $value, $t = raw) {
  new template ($name, $value, $t);
 }
 
 //passthrough tags
 private static $ignore_tags = array ();
 
 static function passthrough () {
  $a = func_get_args (); $a = array_flip ($a);
  parser::$ignore_tags = array_merge (parser::$ignore_tags, $a);
 }
 
 static function underride ($tag, $vals) {
  parser::$ignore_tags[$tag] = $vals;
 }
}

class template {
 private static $templates;
 var $buffer;
 var $type;

 function template ($name, $bfr, $t = raw) {  
  $this->buffer = $bfr;
  $this->type = $t;        

  template::$templates[$name] = &$this;
 }
 
 static function exists ($name) {
  return isset (template::$templates[$name]);
 }
 
 static function render ($name, $request) {
  document::begin (template::$templates[$name]->type);
  eval ('?>' . template::$templates[$name]->buffer . '<?');
  return document::end ();
 }
}

new template ('root', '<?=$request->data?>');
new template ('null', '');

?>
