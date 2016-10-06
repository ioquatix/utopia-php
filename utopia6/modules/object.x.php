<?

function osave ($id, $data) {
$name = "/var/www/utopia-object.$id";
 $file = fopen ($name, "w"); 
 fwrite ($file, $data);
 fclose($file);
}
 
function oname ($id) {
 return "/var/www/utopia-object.$id";
}

function ounique ($id) {
 if ($id == "") return false;
 return !is_file (oname($id));
}

function oload ($id) {
 $name = "/var/www/utopia-object.$id";
 return implode("", file ($name));
}

// <random key>
// return a random md5 key (useful for session hashing, etc)
function rkey () {
 srand ((double)microtime()*10000000);
 return md5(rand(0, 32000));
}

class dataobject {
 var $attributes = array();
 var $key = ""; var $category = "";

 function dataobject ($t, $key = null) {
  $this->category = $t;
  if ($key != null && !ounique ("$t-$key")) {
   $this->key = $key;
   $this->load ();
  } else {
   if ($key != null) $this->key = $key; else $this->key = rkey();
   while (!ounique($t."-".$this->key))
    $this->key = rkey ();
  }
 }

 function get ($name) {return $this->attributes[$name];}
 function set ($name, $value) {$this->attributes[$name] = $value;}
 function un_set ($name) {unset ($this->attributes[$name]);}
 function is_set ($name) {return isset ($this->attributes[$name]);}

 function id () {return $this->key;}
 function category () {return $this->category;}
 function save () {osave ($this->category."-".$this->key, serialize ($this->attributes));}
 function load () {$this->attributes = unserialize (oload ($this->category."-".$this->key));}

}

?>