<?


//special iterator

function joinrows ($c, $f, $s='') {
 return call($f, new attributes($c->all())) . ($c->next()?$s.joinrows($c, $f, $s):'');
}

class process {
 static function buffer ($bfr, $args = array(), $t = raw) {  
  $p = new process;
  $p->buffer = $bfr;
  $p->args = $args;
  $p->type = $t;
  return $p;
 }
 
 static function node ($name, $args = array()) {
  $p = new process;
  $p->name = $name;
  $p->args = $args;
  return $p;
 }
 
 function __apply ($request) {
  if (isset($this->type)) {
   //$request->merge ($this->args);
   document::begin ($this->type);
   eval ('?>' . $this->buffer . '<?');
   return document::end ();
  } else {
   return parser::process (request::$node->address, $this->name, 
                           array_merge ($this->args, $request->all()),
                           $request->data);
  }
 }
}

?>
