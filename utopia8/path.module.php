<?

class is_type {
 var $types = array();
 
 function __construct ($ts) {
  $this->types = explode (",", $ts); 
 }
  
 function __apply ($name) {
  $p = extn($name);
  if (in_array ($p[1], $this->types))
   return true;
  return false;
 }
}

class path {
 static function findfile ($name) {
  $incpaths_str = ini_get ('include_path');
  $incpaths = explode (":", $incpaths_str);
 
  foreach ($incpaths as $p) {
   $file = $p . '/' . $name;
   if (is_readable ($file)) return $file;
  }
  return null;
 }

 static function translate ($path, $base = '') {
  if ($path[0] == '*')
   return $path;
  else if ($path[0] == '/')
   return path::file(path::resolve ($path));
  else return path::file(path::resolve ($path, $base));
 }

 static function resolve ($path, $base = '') {
  if (substr($base, -1) == '/')
    $base = substr($base, 0, -1);
  
  $base = explode('/', $base);
  $path = explode('/', $path);

  if (end($path) != '.' && end($path) != '..')
   $name = '/' . array_pop ($path);
  else
   $name = '/';

  $path = array_diff($path, array('', '.'));
  
  while ($dir = array_shift($path)) {
   if ($dir == '..') {
    array_pop($base);
    continue;
   }
   $base[] = $dir;
  }
  
  return implode('/', $base).$name;
 }

 function directory ($a) {
  return substr ($a, 0, strrpos ($a, "/") + 1);
 }

 static function file ($addr) {
  $p = strrpos ($addr, '/');
  if ($p == (strlen ($addr)-1))
   return substr($addr, 0, $p);
  else return $addr;
 }

 //removes a path element
 static function chop ($addr, $p = 1) {
  $a = explode ('/', $addr);
  while (--$p >= 0  and count ($a))
   array_pop ($a);
  return join ($a, "/");
 }

 //removes a directory element
 static function snip ($a, $p = 1) {
  $s = strrpos ($a, '/');
  if ($s == 0) return null; //if no directory to chop
  $n = substr ($a, $s);
  $a = substr ($a, 0, $s);
  return path::chop ($a, $p) . $n;
 }
 
 static function compare ($addr1, $addr2) {
  return substr_count ($addr1, "/") - substr_count ($addr2, "/");
 }
 
 static function ext ($f) {
  console::log ('deprecated', 'path::ext');
  return array_reverse(explode('.', $f));
 }

 static function extn ($f) {
  return substr ($f, strrpos($f, '.'));
 }

 static function index ($addr, $filter = "is_dir") {
  
  $r = array();
  if ($handle = @opendir("." . $addr))
   while (false !== ($file = readdir($handle))) {
    if ($file == ".." || $file == ".")
     continue;

    if (!strncmp ( basename ($file) , "." , 1 ))
     continue;

    if (call ($filter, "." . $addr . $file))
     array_push ($r, $file);
   } else
    console::log ("path::index", "failed to open " . $addr);
  return $r;
 }

 static function name ($addr) {
  if ($addr == "") return "";
  $n = substr ($addr, strrpos ($addr, "/") + 1);
  return $n != ""?$n:path::name (substr ($addr, 0, -1));
 }

 static function filename ($addr) {
  if ($addr == '') return '';
  return substr ($addr, strrpos ($addr, '/') + 1);
 }

//use to find recursive files within a filesystem
//$path = "/a/b/c/d/my"
//while ($path = find ($path, is_file)) {
// echo $path;
// $path = pchop ($path); //start looking from one path down
//}
// -> '/a/b/c/d/my' '/a/b/c/my' '/my', for example
 static function find ($a, $f) {
  $path = explode ('/', $a);
  $name = array_pop ($path);
 
  while (sizeof($path) > 0) {
   $file = '.' . join('/', $path) . '/' . $name;
   if (call ($f, $file)) break;
  
   array_pop ($path);
  }
 
  if (sizeof($path) == 0) return null;
  return join('/', $path) . '/' . $name;
 }

 static function make ($path, $mode = 0755) {
  if (is_dir($path) || $path == '')
   return;
    
  path::make (dirname($path)); 
  mkdir ($path, $mode);
 }

}

?>
