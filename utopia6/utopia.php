<?

$__die_on_tests = true;
function test ($condition, $error) {
 global $__die_on_tests;
 if ($condition)
  raise ($error, $__die_on_tests);
}

function raise ($error, $fatal = true) {
 ob_end_clean();
 echo "<b>$error</b>";
 if ($fatal)
  finish();
}

// <path chop>
// cut a number of directories off a path
function pchop ($Addr, $P = 1) {
 $A = explode ("/", $Addr);
 while (--$P >= 0)
  array_pop ($A);
 return join ($A, "/");
}

function pname ($Addr) {
 $A = explode ("/", $Addr);
 $str = array_pop ($A);
 if ($str == "")
  $str = array_pop ($A);
 return $str;
}

function pnorm ($addr) {
 $A = explode ("/", $addr);
 $str = array_pop ($A);
 if ($str == "")
  $str = array_pop ($A);
 if ($str != "index")
  return implode ("/", $A) . "/$str";
 return implode ("/", $A);
}

// <path compare>
// return the differnce between two paths
function pcmp ($Addr1, $Addr2) {
 return substr_count ($Addr1, "/") - substr_count ($Addr2, "/");
}

// <index directory>
// returns all files within a dir that match a function
function is_node ($name) {
 if (eregi ("(.node)$", $name))
  return true;
 
 return false;
}

function idir ($Addr, $Filter = "is_dir", $Dotf = false) {
 $R = array();
 if ($handle = @opendir("." . $Addr))
  while (false !== ($file = readdir($handle))) {
   if ($file == ".." || $file == ".")
    continue;

   if ( strncmp ( basename ($file) , "." , 1 ) == 0 && !$Dotf)
    continue;

   if (call_user_func ($Filter, "." . $Addr . "/" . $file))
    array_push ($R, $file);
  }
 return $R;
}

// <force directory>
// creates all directories in a path that do not exist
function fdir ($Path, $Mode = 0755) {
 if (is_dir($Path) || strlen($Path) == 0)
  return;
    
 fdir (dirname($Path));
 mkdir ($Path, $Mode);
}

// <dump>
// return the contents of a file
$__dump_cache = array();
function read ($File, $inc = false) {
 global $__dump_cache, $__dump_miss, $__dump_hit;
  if (isset($__dump_cache[$File])) {
  return $__dump_cache[$File]; 
 }
 if ($inc)  $f = file ($File, 1); else  $f = file ("./" . $File, 0);
 if (!$f)  raise("file not found!"); $__dump_cache[$File] = implode ("", $f); return $__dump_cache[$File];
}

function write ($File, $str) { $file = fopen ("." . $File, "w"); fwrite ($file, $str); fclose($file);}

// <get time>
function mtime () {
  list ($S, $U) = explode (" ", microtime());
  return (float)$U + (float)$S;
}


// <get variable>
// return a variable of certain type
function gvar ($Type, $Name) {
    if ($Type == "header") {
        $H = getallheaders ();
        return $H[$Name];
    } else if ($Type == "server")
        return $_SERVER[$Name];
    else //if ($Type == "request") {
        $V = array_merge ($_GET, $_POST, $_COOKIE);
        return $V[$Name];       
}

// <interpret time>
// interpret a given variable as a time
// if not a time, return current time
function itime ($Time) {
 if (is_string ($Time) && strlen ($Time))
  return strtotime ($Time);
 else if (is_integer ($Time))
  return $Time;
 else
  return time ();
}

// <format time>
// format a given time as a human readble string
// various different formats given
define ("TIME_TIME", 5);
define ("TIME_TINY", 0);
define ("TIME_DATE", 0);
define ("TIME_SHORT", 1);
define ("TIME_MEDIUM", 2);
define ("TIME_LONG", 3);
define ("TIME_SQL", 4);

function ftime ($Time, $Format = TIME_MEDIUM) {
 $Time = itime ($Time);
 if ($Format == TIME_TINY)
  return date ("j/n", $Time);
 else if ($Format == TIME_TIME)
  return date ("G:ia", $Time);
 else if ($Format == TIME_SHORT)
  return date ("j/n G:ia", $Time);
 else if ($Format == TIME_LONG)
  return date ("l jS of F Y h:i:s A", $Time);
 else if ($Format == TIME_SQL)
  return date ("YmdHis", $Time);
 else //if ($Format == TIME_MEDIUM)
  return date("F j, Y, g:i a", $Time);
}

// <node link>
// return a html link to another node
function lnk ($To, $Args = array()) {
 $Argstr = "";
        
 while (list($K, $V) = each ($Args)) {
//  $V = rawurlencode ($V);
   $Argstr .= ";" . $K . "=" . $V;
 }
    
 return $To . $Argstr;
}

// <redirect>
// redirect using a header
function redir ($To, $Args = array(), $end = true) {
 $To = lnk ($To, $Args);
 header ("Location: $To");
 if ($end)
  finish();
}

// <find file>
function fpath ($Addr, $F) {
 $Tmp = explode ("/", $Addr);
 $FD = array_pop ($Tmp);
 $File = "";

 while (sizeof($Tmp) > 0) {
  $File = "." . join("/", $Tmp) . "/$FD";
  
  if (call_user_func ($F, $File))
   break;
			
  array_pop ($Tmp);	
 }
		
 if (sizeof($Tmp) == 0)
  return "";
 return $File;
}

//split!!!


$__node_cache = array();
class parser {
 var $parser;
 var $parent = null;
 var $name;
 var $attributes;
 var $data;
 var $root;

 function render () {
  global $parser_overrides, $__node_cache, $disabled_overrides;
  $addr = "";
  
  if ($this->name[0] == '_')
   $this->name = substr ($this->name, 1);
  else if (isset($parser_overrides[$this->name]))
   return $parser_overrides[$this->name]->render ($this->root, $this->attributes, $this->data);

  if ($this->name == "self")
   $addr = $this->root;
  else
   $addr = $this->root . "/" . $this->name;
  if (isset ($__node_cache[$addr]))
   $node = $__node_cache[$addr];
  else {
   $node =& new node ($addr);
   $__node_cache[$addr] =& $node;
  }

  return $node->render ($this->root, $this->attributes, $this->data);
 }

 function tag_open($parser, $tag, $attributes) {
  $child = &new parser;
  $child->parser =& $this->parser;
  $child->parent =& $this;
  $child->name = $tag;
  $child->root = $this->root;
  $child->self = $this->self;
  $child->attributes = $attributes;
  xml_set_element_handler($this->parser, array(&$child, "tag_open"), array(&$child, "tag_close"));
  xml_set_character_data_handler($this->parser, array(&$child, "cdata"));
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
    $this->parent->data .= $data . $this->render();
   xml_set_element_handler($this->parser, array(&$this->parent, "tag_open"), array(&$this->parent, "tag_close"));
   xml_set_character_data_handler($this->parser, array(&$this->parent, "cdata"));
  }
 }

 function transform($root, $text) {
  $this->parser = xml_parser_create();
        
  xml_parser_set_option ($this->parser, XML_OPTION_CASE_FOLDING, false);
  
  $this->root = $root;
  $text = str_replace ("&", "&amp;", $text);
        
  xml_set_element_handler($this->parser, array(&$this, "tag_open"), array(&$this, "tag_close"));
  xml_set_character_data_handler($this->parser, array(&$this, "cdata"));
  xml_parse($this->parser, "<root>" . $text . "</root>");
    
  xml_parser_free ($this->parser);
  return $this->data;
 }
}

class node {
 var $node;
 var $name;
 var $address;
 var $path;

 function aaddr () {return $this->path;}
 function raddr () {return $this->node;}
 function paddr () {return pnorm($this->node);}

 function node ($addr) {
  $parts = explode ("/", $addr);
  $name = array_pop ($parts);
  if (trim($name) == "")
   $name = "index";
        
  $this->name = $name;
  $this->node = implode ("/", $parts) . "/" . $name;
      
  do {
   $this->address = implode ("/", $parts) . "/$name";
   $this->path = implode("/", $parts) . "/$name/index";
   if (is_file ("." . $this->path . ".node"))
    break;

   $this->path = $this->address;
   if (is_file ("." . $this->path . ".node"))
    break;
   array_pop ($parts);
  } while (count($parts) > 0);
 }

 function render ($root, $attributes, $data) {
  eval ("?>" . read($this->path . ".node") . "<?");
  return dend();
 }
}

$parser_overrides = array();

function mkn ($n, $v, $t=utopia_type_raw) {
 $o = &new override_node ($n, $v, $t);
}

class override_node {
 var $name;
 var $buffer;
 var $type;

 function override_node ($addr, $bfr, $t = utopia_type_raw) {
  global $parser_overrides;
  
  $parts = explode ("/", $addr); 
  $name = array_pop ($parts);
  if (trim($name) == "")
   return;

  $this->buffer = $bfr;
  $this->type = $t;        
  $this->name = $name;

  $parser_overrides[$name] = &$this;
 }

 function render ($root, $attributes, $data) {
 dbegin($this->type);
  eval ("?>" . $this->buffer . "<?");
  return dend();
 }
}


//parser end

function ldmod ($name, $version = "x") {
 require_once ("utopia6/modules/$name.$version.php");
}

function __cb (&$b) {
 if ($b===null)
  $b = ob_get_contents();
 ob_end_clean();
}

function utopia_type_xml ($buffer=null) {
 __cb($buffer); global $__node_address;
  ob_end_clean();
  $r = &new parser;
  return $r->transform($__node_address, $buffer);
}

function utopia_type_plaintext ($buffer=null) {
 __cb($buffer);
 $buffer = "<pre>" . htxt ($buffer) . "</pre>";
 ob_end_clean();
 return $buffer;
}

function utopia_type_raw ($buffer=null) {
 __cb($buffer);
 return $buffer;
}

function utopia_type_none ($buffer=null) {
 return "";
}

global $__dtype; $__dtype = utopia_type_raw;

function dbegin ($t, $b = true) {
 global $__dtype;
 $__dtype = $t; 
 register_postprocessor ($t);
 if ($b)
  ob_start();
}

function dend ($buffer=null) {
 global $__dtype;
  return $__dtype ($buffer);
}

?>