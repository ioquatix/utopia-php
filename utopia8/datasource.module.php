<?

function pt ($v) {
    return "'". addslashes($v) ."'"; 
}

function ut ($v) {
 if ($v[0] == "'" or $v[0] == '"')
  return stripslashes(substr($v, 1, strlen($v) - 2));
 return stripslashes($v); 
}

class datasource {
 const ACCESS_DENIED = -1;
 
 private $user;
 private $pass;
 private $host;
 private $database;
 
 public $link = null;
 
 function __construct ($host = null, $user = null, $pass = null, $database = null) {
  if ($host !== null) $this->host = $host;
  if ($user !== null) $this->user = $user;
  if ($pass !== null) $this->pass = $pass;
  if ($database !== null) $this->database = $database;
 }
 
 function connect () {
  if (is_resource($this->link)) return;

  //fixed returning same connection
  $this->link = mysql_connect ($this->host, $this->user, $this->pass, true);

  if (!is_resource($this->link))
   console::log ("database", "mysql_connect {$this->user}@{$this->host} failed");
    
  if ($this->database and !@mysql_select_db($this->database, $this->link))
   console::log ("database", "mysql_select_db {$this->database} failed");

  if (defined('DATABASE_DEBUG')) console::log ("database", $this->link);
  return $this->is_connected();
 }

 function select ($db = null) {
  if ($db !== null)
   $this->database = $db;
  if ($this->database and !@mysql_select_db($this->database, $this->link))
   console::log ("database", "mysql_select_db {$this->database} failed");
 }

 function count ($table) {
  $q = $this->query ("select count(*) as count from $table");
  $q->next();
  return $q->count;
 }

 function is_connected () {
  if (!$this->link)
   return FAILED;
  else return OKAY;
 }
 
 function cursor () {
  $this->connect();
  return new cursor ($this);
 }
 
 function query ($q) {
  $c = $this->cursor();
  $c->query ($q);
  return $c;
 }
 
 function record ($t) {
  $this->connect();
  $r = new record ($this);
  $r->form ($t);
  return $r;
 }

 function errno () {
  if (mysql_errno($this->link) == 1044)
   return datasource::ACCESS_DENIED;
  
  console::log ('database', $this->error());
  
  return FAILED;
 }

 function error ($txt = false) {
  return mysql_errno($this->link) . "/" . mysql_error($this->link);
 }
 
 function last_insert_id () {
  $c = $this->query ("select last_insert_id() as id");
  $c->next();
  return $c->id;
 }
}

class cursor {
 function debug () {
  print_r ($this->record);
 }

 protected $record;
 protected $last_insert_id = -1;
 public $database = null;
 protected $row = 0;
 
 protected $query;
 
 function __construct ($database) {
  $this->database = $database;
 }

 function __get ($n) {
  return $this->record[$n];
 }
 
 function __set ($n, $v) {
  if (array_key_exists ($n, $this->record))
   return $this->record[$n] = $v;
 }
 
 function all () {
  return $this->record;
 }
 
 function query ($q) {
  if (defined('DATABASE_DEBUG')) console::log ("database", "{$this->database->link} query: $q");
  $this->query = @mysql_query($q, $this->database->link);
  
  if (!$this->query)
   return $this->database->errno();

  $this->row = 0;
  return OKAY;
 }

 function next () {
  $this->record = @mysql_fetch_array($this->query, MYSQL_ASSOC);
  $this->row += 1;

  return is_array($this->record);
 }

 function hasnext () { // does this work right? ob1?
  return $this->row != $this->length();
 }

 function current () {
  return $this->row;
 }
 
 function length () {
  console::log ('cursor::length', 'deprecated');
  return @mysql_num_rows ($this->query);
 }

 function count () {
  return @mysql_num_rows ($this->query);
 }

 function fields ($spec = null) {//turmoil
  $h = array();
  for ($i = 0; $i < mysql_num_fields($this->query); ++$i) {
   if ($spec)
    $h[$i] = mysql_fetch_field ($this->query, $i)->$spec;
   else
    $h[$i] = mysql_fetch_field ($this->query, $i);
  }
  return $h;
 }

 function copy ($ar) {
  while (list ($k, $v) = each ($ar))
   $this->__set ($k, $v);
 }
};

class record extends cursor {
 static public $tables = array();
 //read only
 var $table = '';
 var $key = '';
 
 function fields () {
  $q = $this->database->query ('select * from '. $this->table .' limit 1');
  return $q->fields();
 }
 
 function form ($t) {
  if (!isset(record::$tables[$t])) {
   $q = new cursor ($this->database);
   $q->query ("describe $t");
   $primary_key = ''; $fields = array();
   
   while ($q->next()) {
    if ($q->Key == 'PRI')
     $primary_key = $q->Field;
    $fields[$q->Field] = $q->Default;
   }
   
   record::$tables[$t] = array ($primary_key, $fields);
  }
  
  $this->table = $t;
  $this->key = record::$tables[$t][0];
  $this->record = record::$tables[$t][1];
 }
 
 function clear () {
  $this->record = record::$tables[$this->table][1];
 }

 //if $at is positive 0 -> n
 //select limit $at, 1
 //if $at is negative, select the last record
 //return $at
 //searches for the $at record
 function find ($at) {
  $q = $this->database->query ("select count(*) as count from $this->table");
  $q->next();
  if ($at < 0)
   $at = $q->count - 1;
  if ($at >= $q->count)
   $at = 0;
  
  $this->query ("select * from $this->table order by $this->key limit $at, 1");
  $this->next ();
  return $at;
 }

 function where ($id=null) {
  if ($id == null) $id = $this->record[$this->key];
  
  $q = $this->database->query (
  "select count(*) as count from $this->table where $this->key < ".$id);
  $q->next();
  return $q->count;
 }
 
 function delete ($id = null) {
  if ($id === null) $id = $this->record[$this->key];
  $this->query ("delete from $this->table where $this->key = " . pt($id));
 }
 
 function select ($id, $key=null) {
  if (!$key) $key = $this->key;
  $this->query ("select * from $this->table where $key = " . pt ($id));
  if ($this->next ())
   return $id;
  //else
  $this->clear ();
  return null;
 }
 
 function update () {
  $r = array_map (pt, $this->record); unset ($r[$this->key]);
  $q = "update $this->table set ";
  
  $p = "";
  foreach ($r as $k => $v) {
   $q .= $p . "$k = $v";
   $p = ", ";
  }
   
  $q .= " where $this->key = " . pt($this->record[$this->key]);
  $this->query ($q);
 }

 function insert () {
  $r = array_map (pt, $this->record); unset ($r[$this->key]);

  $this->query ("insert into $this->table (" . 
   join(array_keys($r), ", ") . ") values (" .
   join(array_values($r), ", ") . ")");

  $this->record[$this->key] = $this->database->last_insert_id();
  return $this->record[$this->key];
 } 
 
 function search ($ignore = null, $ignore_empty = true) {
  if ($ignore == null) $ignore = array();
  
  $q = "select * from $this->table where ";
  $prp = "";
  foreach ($this->record as $k => $v) {
   if (($v == '' & $ignore_empty) | array_key_exists ($k, $ignore)) continue;
   
   $q .= $prp . $k .'=' . pt($v);
   
   $prp = " and ";    
  }

  return $this->query ($q);
 }
};

?>
