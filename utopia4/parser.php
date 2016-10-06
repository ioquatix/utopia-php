<?

$debg = 0;
function debug ($txt = '') {
	global $debg;
	echo "*** mark $debg -- $txt<br>";
	++$debg;
}

class node {
    var $node;
    var $name;
    var $address;
    var $path;
    
    // <absolute address>
    function aaddr () {
        return $this->path;
    }
    
    // <relative address>
    function raddr () {
         return $this->node;
    }

    function node ($Addr) {
        $Parts = explode ("/", $Addr);
        $Name = array_pop ($Parts);
        if (trim($Name) == "")
            $Name = "index";
        
        $this->name = $Name;
        $this->node = implode ("/", $Parts) . "/" . $Name;
      
        do {
            $this->address = implode ("/", $Parts) . "/$Name";
            $this->path = implode("/", $Parts) . "/$Name/index";
            if (is_file ("." . $this->path . ".node"))
                break;

            $this->path = $this->address;
            if (is_file ("." . $this->path . ".node"))
                break;
            array_pop ($Parts);
        } while (count($Parts) > 0);
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
            $buffer = $r->transform($this->address, $buffer);
        }
        if ($type == "debug") {
        	$buffer = "<pre>" . ntxt(htxt ($buffer)) . "</pre>";
        }
        return $buffer;
    }
};

$__node_address_cache = array();
class parser {
    var $parser;
    var $parent = null;
    var $name;
    var $attributes;
    var $data;
    var $root;

    function render () {
        global $__node_address_cache, $__node_hit, $__node_miss;
        $Addr = "";
        if ($this->name == "self")
            $Addr = $this->root;
        else
            $Addr = $this->root . "/" . $this->name;
        if (isset ($__node_address_cache[$Addr])) {
         $node = $__node_address_cache[$Addr];
        } else {
         $node =& new node ($Addr);
         $__node_address_cache[$Addr] =& $node;
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
};

?>
