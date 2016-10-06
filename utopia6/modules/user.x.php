<?

class user {
 var $database;
 var $user_table = "user";
 var $name = "email";
 var $detail_key = "name";
 var $detail_value = "value";
 var $password = "password";
 var $key = "id";

 function user ($d) {
  $this->database = $d;
 }

 function verify ($name, $password) {
  if ($name == "")
   return -3;
  
  if ($password == "")
   return -4;
 
  $q = $this->database->query ("select * from $this->user_table where $this->name = " . prt($name));

  if (!$q->next_record())
   return -1;
  
  if ($q->record[$this->password] == $password)
   return $q->record[$this->key];
   
  return -2;
 }

 function change_password ($key, $password) {
  $this->database->query ("update $this->user_table set $this->password = ".prt($password)." where $this->name = ". prt ($key));
 }
}

?>
