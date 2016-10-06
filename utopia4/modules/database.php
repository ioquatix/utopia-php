<?

// <protect && unprotect>
// protect a string (for, say, insertion into database query)
function prt ($Var) {
    return "'". addslashes($Var) ."'"; 
}

function uprt ($Var) {
    return stripslashes(substr($Var, 1, strlen($Var) - 2));
}


function kimplode ($ar) {
 $b = "";
 $c = 0;
 foreach ($ar as $v) {
  $b .= "<:key$c>" . $v . "</:key$c>";
  ++$c;
 }
 return $b;
}

define ("DATABASE_OKAY", 0);
define ("DATABASE_UNKNOWN_ERROR", -1);
define ("DATABASE_ACCESS_DENIED", -2);

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
   $Header[$Cursor->Record["Field"]] = array("Default" => $Cursor->Record["Default"], "Type" => $Cursor->Record["Type"]);
   if ($Cursor->Record["Key"] == "PRI")
    array_push ($Keys, $Cursor->Record["Field"]);
   array_push ($Fields, $Cursor->Record["Field"]);
  }
  return array("Types" => $Header, "Table" => $Table, "Keys" => $Keys, "Fields" => $Fields);
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

 var $Query;

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
 var $Table;
 var $Key;
 var $Data;

 function datarecord ($database, $TableName) {
  $Table = $database->describe_table ($TableName);
  $this->database = $database;
  $this->Table = $Table["Table"];
  $this->Key = $Table["Keys"][0];
  foreach ($Table["Fields"] as $Field) {
   $this->Data[$Field] = prt($Table["Types"][$Field]["Default"]);
  }
 }

 function set ( $Key, $Val ) {
  if (!isset($this->Data[$Key]))
   return;

  $this->Data[$Key] = prt ($Val);
 }

 function get ( $Key ) {
  if (!isset($this->Data[$Key]))
   return;

  return uprt ($this->Data[$Key]);
 }

 function is_set ( $Key ) {
  return isset($this->Data[$Key]);
 }

 function insert () {
  $Cursor = $this->database->cursor();

  $Query = "insert into $this->Table (" . 
   join(array_keys($this->Data), ", ") . ") values (" .
   join(array_values($this->Data), ", ") . ")";
	
  $Cursor->query ($Query);

  return $this->database->last_insert_id();
 }

 function delete ($Id = null) {
  $Cursor = $this->database->cursor();

  if ($Id == null)
   $Id = $this->Data[$this->Key];

  $Query = "delete from $this->Table where $this->Key = " . $Id;

  $Cursor->query ($Query);
 }

 function update () {
  $Cursor = $this->database->cursor();

  $Query = "replace into $this->Table (" . 
   join(array_keys($this->Data), ", ") . ") values (" .
   join(array_values($this->Data), ", ") . ")";

  $Cursor->query ($Query);

  return $this->database->last_insert_id();
 }

 function select ( $Val, $Key = null, $Protect = true) {
  $Cursor = $this->database->cursor();

  if ($Key == null)
   $Key = $this->Key;

  if ($Protect)
   $Val = prt ($Val);

  $Cursor->query ("select * from $this->Table where $Key = $Val");
	
  if ($Cursor->next_record())
   return $db->Record;
  return null;
 }

 function update_data ($R) {
  if (!is_array($R))
   return;
	
  foreach ($this->Data as $K => $V)
   $this->Data[$K] = prt($R[$K]);
 }
};

?>