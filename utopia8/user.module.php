<?

class user {
 static function make_password ($len) {
  $salt = 'abcdefghijklmnopqrstuvwxyz0123456789-';
  srand ((double)microtime()*1000000);
  $pass = '';
  while ($len--) {
   $pass .= substr ($salt, rand() % 33, 1);
  }
  return $pass;
 }

 function __construct ($ds, $username = "email", $password = "password", $user = "user") {
  $this->ds = $ds;
  $this->username = $username;
  $this->password = $password;
  $this->user = $user;
 }

 function verify ($username, $password) {
  $username = pt($username);
  
  $q = $this->ds->query ("select * from {$this->user} where {$this->username} = $username limit 1");

  if ($q->next()) {
   if ($q->__get ($this->password) == $password)
    return $q->id;
   else
    return -1; //incorrect password
  }

  return -2; //no such user
 }
}

?>
