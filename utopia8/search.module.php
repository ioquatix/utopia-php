<?

ld ('datasource');

class search {
 static function op2 ($q, $op, $str) {
  $str = trim ($str);
  if ($str != '') return "$q $op $str";
  else return '';
 }

 static function like ($q, $str) {
  $str = trim ($str);
  if ($str != '') return $q . ' like ' . pt ('%'.$str.'%');
  else return '';
 }

 static function equal ($q, $str) {
  if ($str) return $q . '== ' . pt ($str);
  else return '';
 }

 static function all () {
  $k = func_get_args();
  $k = array_filter ($k);
  if (count($k))
   return '(' . join ($k, ') and (') . ')';
  else return '';
 }

 static function any () {
  $k = func_get_args();
  $k = array_filter ($k);
  if (count($k))
   return '(' . join ($k, ') or (') . ')';
  else return '';
 }

 static function terms ($str, $field) {
  $strings = split ($str);
  foreach ($strings as $k=>$s) {
   $strings[$k] = search::like ($field, $s);
  }
  return '(' . join ($k, ') or (') . ')';
 }

 var $table;
 var $cursor;
 var $orderby;
 var $groupby;
 var $limit;
 var $from;
 
 function __construct ($cursor, $table) {
  $this->cursor = $cursor;
  $this->table = $table;
 }
 
 function page ($i) {
  if ($limit > 0)
   $this->from = $this->limit * $i;
 }
 
 function query ($term) {
  $q = "select * from {$this->table} where $term ";
  if ($this->orderby)
   $q .= " order by {$this->orderby} ";
   
  if ($this->groupby)
   $q .= " group by {$this->groupby} ";
   
  if ($this->limit) {
   if ($this->from)
    $q .= " limit {$this->limit}, {$this->from} ";
   else 
    $q .= " limit {$this->limit} ";
  }

  echo input::text($q);
  return $this->cursor->query ($q);
 }

}

?>
