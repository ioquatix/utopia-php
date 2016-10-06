<?

define ("DATABASE_OKAY", 0);
define ("DATABASE_UNKNOWN_ERROR", -1);
define ("DATABASE_ACCESS_DENIED", -2);

class database {
 var $User = "";
 var $Pass = "";
 var $Host = "";
 var $Namespace = "";

 var $Link = null;

 function database ($Host = null, $User = null, $Pass = null, $Namespace = null) {
  if ($Host !== null)
   $this->Host = $Host;
  if ($User !== null)
   $this->User = $User;
  if ($Pass !== null)
   $this->Pass = $Pass;
  if ($Namespace !== null)
   $this->Namespace = $Namespace;
 }

 function is_connected () {
  return $this->Link != null;
 }

 function error () {
  if (mysql_errno() == 1044)
   return DATABASE_ACCESS_DENIED;
  else
   return DATABASE_UNKNOWN_ERROR;
 }

 function connect ($Host = null, $User = null, $Pass = null, $Namespace = null) {
  if ($User !== null)
   $this->User = $User;
  if ($Pass !== null)
   $this->Pass = $Pass;
  if ($Host !== null)
   $this->Host = $Host;
  if ($Namespace !== null)
   $this->Namespace = $Namespace;

  $this->Link = @mysql_pconnect ($this->Host, $this->User, $this->Pass);
  
  if (!$this->Link)
   return $this->error ();
  
  return $this->select($this->Namespace);
 }

 function cursor () {
  if (!$this->Link)
   $this->connect();

  return new cursor ($this);
 }

 function select ($Db) {
  if (!@mysql_select_db($Db, $this->Link))
   return $this->error ();
  else {
   $Cursor = $this->cursor();
   if (!$Cursor->query ("show tables"))
    return $this->error ();
   $this->Namespace = $Db;
   return DATABASE_OKAY;
  }
 }
 
 function affected_rows() {
  return @mysql_affected_rows($this->Link);
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
  return $Cursor->Record["last_insert_id"];
 }
};

class cursor {
 var $Database;
 var $Record = null;
 var $Row = 0;

 var $Query;

 function cursor ($Database) {
  $this->Database = $Database;
 }

 function query($Query) {
  if ($Query == "")
   return null;

  $this->Query = @mysql_query($Query,$this->Database->Link);

  if (!$this->Query)
   return $this->Database->error();

  $this->Row = 0;
  return DATABASE_OKAY;
 }

 function next_record() {
  $this->Record = @mysql_fetch_array($this->Query, MYSQL_ASSOC);
  $this->Row += 1;

  return is_array($this->Record);
 }

 function number_of_records () {
  return @mysql_num_rows ($this->Query);
 }

 function number_of_fields () {
  return @mysql_num_fields ($this->Query);
 }
 
};

class datarecord {
 var $Database;
 var $Table;
 var $Key;
 var $Data;

 function datarecord ($Database, $TableName) {
  $Table = $Database->describe_table ($TableName);
  $this->Database = $Database;
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
  $Cursor = $this->Database->cursor();

  $Query = "insert into $this->Table (" . 
   join(array_keys($this->Data), ", ") . ") values (" .
   join(array_values($this->Data), ", ") . ")";
	
  $Cursor->query ($Query);

  return $this->Database->last_insert_id();
 }

 function delete ($Id = null) {
  $Cursor = $this->Database->cursor();

  if ($Id == null)
   $Id = $this->Data[$this->Key];

  $Query = "delete from $this->Table where $this->Key = " . $Id;

  $Cursor->query ($Query);
 }

 function update () {
  $Cursor = $this->Database->cursor();

  $Query = "replace into $this->Table (" . 
   join(array_keys($this->Data), ", ") . ") values (" .
   join(array_values($this->Data), ", ") . ")";

  $Cursor->query ($Query);

  return $this->Database->last_insert_id();
 }

 function select ( $Val, $Key = null, $Protect = true) {
  $Cursor = $this->Database->cursor();

  if ($Key == null)
   $Key = $this->Key;

  if ($Protect)
   $Val = prt ($Val);

  $Cursor->query ("select * from $this->Table where $Key = $Val");
	
  if ($Cursor->next_record())
   return $Db->Record;
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
