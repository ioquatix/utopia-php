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
   if (is_file ("." . $this->path . ".node"))
    break;

   $this->path = $this->address;
   if (is_file ("." . $this->path . ".node"))
    break;
   array_pop ($parts);
  } while (count($parts) > 0);
 }

 function render ($root, $attributes, $data) {
  $type = "html";
  $buffer = "";
  ob_start();
  eval ("?>" . dump ($this->path . ".node") . "<?");
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
$disabled_overrides = array();

function disable_override ($n) {
 global $disabled_overrides;
 $disabled_overrides[$n] = "1";
}

function enable_override ($n) {
 global $disabled_overrides;
 unset ($disabled_overrides[$n]);
}

class override_node {
 var $name;
 var $buffer;

 function override_node ($addr, $bfr) {
  $this->buffer = $bfr;
  global $parser_overrides;
  $parts = explode ("/", $addr); 
  $name = array_pop ($parts);
  if (trim($name) == "")
   $name = "index";
        
  $this->name = $name;
//  $this->node = implode ("/", $parts) . "/" . $name;  
//  $this->address = $this->node
//  $this->path = $this->node . "/index";

  $parser_overrides[$name] = &$this;
 }

 function render ($root, $attributes, $data) {
  $type = "html";
  $bfr = "";
  ob_start();
  eval ("?>" . $this->buffer . "<?");
  $bfr = ob_get_contents();
  ob_end_clean();
  if ($type == "ml") {
   $r = &new parser;
   $bfr = $r->transform("/$this->name", $bfr);
  }
  return $bfr;
 }
}

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

?>