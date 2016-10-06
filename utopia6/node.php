<?

class node {
 var $node;
 var $name;
 var $address;
 var $path;

 function aaddr () {return $this->path;}
 function raddr () {return $this->node;}

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
   if (is_file ("." . $this->path . ".php"))
    break;

   $this->path = $this->address;
   if (is_file ("." . $this->path . ".php"))
    break;
   array_pop ($parts);
  } while (count($parts) > 0);
 }

 function render ($root, $attributes, $data, $type="html") {
  $buffer = "";
  ob_start();
  eval ("?>" . read ($this->path . ".php") . "<?");
  $buffer = ob_get_contents();
  ob_end_clean();
  if ($type == "ml") {
   $r = &new parser;
   flush();
   $buffer = $r->transform($this->address, $buffer);
  }
  if ($type == "debug") {
  	$buffer = "<pre>" . ntxt(htxt ($buffer)) . "</pre>";
  }
  return $buffer;
 }
}

$parser_overrides = array();

function mkn ($n, $v) {
 $o = &new override_node ($n, $v);
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

?>