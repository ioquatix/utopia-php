<?

class cvsreader {
 var $rows = array();
 var $columns = array();

 function read ($rows, $withheader=true) {
  if (!is_array ($rows))
   $rows = explode ("\n", $rows);
  
  if ($withheader)
   $this->columns = array_explode (",", array_unshift ($rows));

  foreach ($rows as $r)
   array_push ($this->rows, explode (",", $r));
 }
 
 function load ($file, $wh=true) {
  $this->read (file ($file), $wh);
 }
 
 function count () {
  return count($this->rows);
 }
 
 function query () {
  return new cvsquery ($this);
 }
}

class cvsquery {
 var $source;
 var $idx;
 
 function __construct ($source) {
  $this->source = $source;
  $this->idx = -1;
 }
 
 function __get ($k) {
  return $source->rows[$idx][$source->columns[$k]];
 }
 
 function __set ($k, $v) {
  return $source->rows[$idx][$source->columns[$k]] = $v;
 }
 
 function next () {
  if (($this->idx+1) < $this->source->count())
   return $idx++;
  else
   return false;
 }
}

?>