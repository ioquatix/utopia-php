<?

// <protect && unprotect>
// protect a string (for, say, insertion into database query)
function prt ($Var) {
    return "'". addslashes($Var) ."'"; 
}

function uprt ($Var) {
 if ($Var[0] == "'" or $Var[0] == '"')
  return stripslashes(substr($Var, 1, strlen($Var) - 2));
  return stripslashes($Var); 
}


function kimplode ($ar) {
 $b = "";
 $c = 0;
 foreach ($ar as $v) {
  $b .= "<:key$c>" . htxt($v) . "</:key$c>";
  ++$c;
 }
 return $b;
}

define ("DATABASE_OKAY", 0);
define ("DATABASE_UNKNOWN_ERROR", -1);
define ("DATABASE_ACCESS_DENIED", -2);

function dbget (&$db, $t, $n, $v, $a) {
 $v = prt($v);
 $q = $db->query ("select * from $t where $n = $v");
 if ($q->next_record())
  return $q->record[$a];
 return "";
}

class database {
 var $user = "";
 var $pass = "";
 var $host = "";
 var $namespace = "";

 var $link = null;

 function database ($host = null, $user = null, $pass = null, $namespace = null) {
  if ($host !== null)
   $this->host = $host;
  if ($user !== null)
   $this->user = $user;
  if ($pass !== null)
   $this->pass = $pass;
  if ($namespace !== null)
   $this->namespace = $namespace;
 }

 function is_connected () {
  return $this->link != null;
 }

 function error () {
  if (mysql_errno() == 1044)
   return DATABASE_ACCESS_DENIED;
  else
   return DATABASE_UNKNOWN_ERROR;
 }

 function connect ($host = null, $user = null, $pass = null, $namespace = null) {
  if ($user !== null)
   $this->user = $user;
  if ($pass !== null)
   $this->pass = $pass;
  if ($host !== null)
   $this->host = $host;
  if ($namespace !== null)
   $this->namespace = $namespace;

  $this->link = @mysql_pconnect ($this->host, $this->user, $this->pass);
  
  if (!$this->link)
   return $this->error ();
  
  return $this->select($this->namespace);
 }

 function cursor () {
  if (!$this->link)
   $this->connect();

  return new cursor ($this);
 }

 function query ($str) {
  $c = $this->cursor();
  $c->query ($str);
  return $c;
 }

 function select ($db) {
  if (!@mysql_select_db($db, $this->link))
   return $this->error ();
  else {
   //$Cursor = $this->cursor();
   //if (!$Cursor->query ("show tables"))
   // return $this->error ();
   //$this->namespace = $db;
   return DATABASE_OKAY;
  }
 }
 
 function affected_rows() {
  return @mysql_affected_rows($this->link);
 }

 function describe_table ($Table) {
  $Cursor = $this->cursor();
  $Cursor->query ("describe $Table");
  $Header = array(); $Keys = array();
  $Fields = array();
  while ($Cursor->next_record()) {
   $Header[$Cursor->record["field"]] = array("default" => $Cursor->record["Default"], "type" => $Cursor->record["Type"]);
   if ($Cursor->record["Key"] == "PRI")
    array_push ($Keys, $Cursor->record["Field"]);
   array_push ($Fields, $Cursor->record["Field"]);
  }
  return array("types" => $Header, "table" => $Table, "keys" => $Keys, "fields" => $Fields);
 }

 function last_insert_id () {
  $Cursor = $this->cursor();
  $Cursor->query ("select last_insert_id()");
  $Cursor->next_record();
  return $Cursor->Record["last_insert_id()"];
 }

 function error_text () {
  return mysql_errno() . "/" . mysql_error();
 }
};

class cursor {
 var $database;
 var $record = null;
 var $row = 0;

 var $query;

 function cursor ($db) {
  $this->database = $db;
 }

 function query($qstr) {
  if ($qstr == "")
   return null;

  $this->query = @mysql_query($qstr,$this->database->link);

  if (!$this->query)
   return $this->database->error();

  $this->row = 0;
  return DATABASE_OKAY;
 }

 function next_record() {
  $this->record = @mysql_fetch_array($this->query, MYSQL_ASSOC);
  $this->row += 1;

  return is_array($this->record);
 }

 function number_of_records () {
  return @mysql_num_rows ($this->query);
 }

 function number_of_fields () {
  return @mysql_num_fields ($this->query);
 }
 
};

class datarecord {
 var $database;
 var $table;
 var $key;
 var $data;

 function datarecord ($database, $tablename) {
  $table = $database->describe_table ($tablename);
  $this->database = $database;
  $this->table = $table["table"];
  $this->key = $table["keys"][0];
  foreach ($table["fields"] as $field) {
   $this->data[$field] = prt($table["types"][$field]["default"]);
  }
 }
 
 function clean () {
  foreach ($this->data as $K => $V)
  $this->data[$K] = prt("");
 }

 function copy ($param, $src) {
  while (list ($k, $v) = each ($param)) {
   if (isset ($src[$k])) 
    $this->set ($k, eval ("return " . $v.'($src[$k]);'));
  }
 }

 function set ( $key, $Val, $raw = false) {
  if (!isset($this->data[$key]))
   return;

  if ($raw)
   $this->data[$key] = $Val;
  else
   $this->data[$key] = prt ($Val);
 }

 function get ( $key ) {
  if (!isset($this->data[$key]))
   return;
  
  return uprt ($this->data[$key]);
 }

 function is_set ( $key ) {
  return isset($this->data[$key]);
 }

 function insert () {
  $Cursor = $this->database->cursor();

 $data = $this->data; unset ($data[$this->key]);

  $Query = "insert into $this->table (" . 
   join(array_keys($data), ", ") . ") values (" .
   join(array_values($data), ", ") . ")";
	
  $Cursor->query ($Query);

  $this->data[$this->key] = $this->database->last_insert_id();
  return $this->database->last_insert_id();
 }

 function delete ($Id = null) {
  $Cursor = $this->database->cursor();

  if ($Id == null)
   $Id = $this->data[$this->key];

  $Query = "delete from $this->table where $this->key = " . $Id;

  $Cursor->query ($Query);
 }

 function update () {
  $query = "update $this->table set ";
  
  $p = "";
   foreach ($this->data as $k => $v) {
    if ($k == $this->key) continue;
    $query .= $p . "$k = $v";
    $p = ", ";
   }
   
   $query .= " where $this->key = " . $this->data[$this->key];
   $this->database->query ($query);
 }

 function replace () {
  $Q = "replace into $this->table (" . 
   join(array_keys($this->data), ", ") . ") values (" .
   join(array_values($this->data), ", ") . ")";

   $Cursor = $this->database->query ($Q);
 }

 function select ( $Val, $key = null, $Protect = true) {
  if ($key == null)
   $key = $this->key;

  if ($Protect)
   $Val = prt ($Val);

  $c = $this->database->query ("select * from $this->table where $key = $Val");
	
  if ($c->next_record()) {
   $this->update_data ($c->record);
   return true;
  } else
   return false;
 }
 
 function search ($ignore_these = null, $ignore_empty = true) {
  if ($ignore_these == null) $ignore_these = array();
  
  $q = "select * from $this->table where ";
  $prp = "";
  foreach ($this->data as $k => $v) {
   if (trim(uprt($v)) == "" && $ignore_empty) continue;
   if (array_key_exists ($k, $ignore_these)) continue;
   
   $q .= "$prp$k = $v";
   
   $prp = ", ";    
  }

  $c = $this->database->query ($q);
  return $c;
 }
 
 function update_data ($R) {
  if (!is_array($R))
   return;
	
  foreach ($this->data as $K => $V)
   $this->data[$K] = prt($R[$K]);
 }
};

?>