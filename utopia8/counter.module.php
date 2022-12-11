<?

ld ("server", "datasource");

/*
create table counter (
 id bigint unsigned not null auto_increment primary key,
 name varchar(128),
 hits int unsigned
)
*/

class counter {
 var $name;
 var $dr;
 
 function counter ($name, $database, $table = "counter") {
  $this->name = $name;
  $this->dr = $database->record ($table);
 }
 
 function update () {
  $this->dr->clear();
  $this->dr->select ($this->name, "name");
  if ($this->dr->id == false) {
   $this->dr->name = $this->name;
   $this->dr->hits = "1";
   $this->dr->insert ();
  } else {
   $this->dr->hits += 1;$this->dr->hits + 1;
   $this->dr->update ();
  }
  return $this->dr->hits;
 }
 
 function count () {
  $this->dr->clear();
  $this->dr->select ($this->name, "name");
  if ($this->dr->id == false)
   return 0;
  else
   return $this->dr->hits;
 }
}

/*
create table visitor (
 id bigint unsigned not null auto_increment primary key,
 first bigint unsigned null,
 kind varchar(128),
 at datetime,
 session varchar(32),
 agent text,
 host text,
 ip text,
 request text,
 referer text
)
*/

class visitor {
 var $kind;
 var $dr;
 
 function visitor ($kind, $database, $table = "visitor") {
  $this->kind = $kind;
  $this->db = $database;
  $this->dr = $database->record ($table);
 }
 
 function update () {
  ld ('server');

  $this->dr->clear ();
  $this->dr->kind = $this->kind;
  
  $this->dr->session = server::$persistent->data;
  if (server::has ("visitor.first"))
   $this->dr->first = server::get ('visitor.first');
  $this->dr->agent = $_SERVER['HTTP_USER_AGENT'];
  $this->dr->host = gethostbyaddr ($_SERVER['REMOTE_ADDR']);
  $this->dr->ip = gethostbyname ($_SERVER['REMOTE_ADDR']);
  $this->dr->request = $_SERVER['REQUEST_URI'];
  $h = getallheaders();
  $this->dr->referer = $h['Referer'];
  $this->dr->__set ("at", time::now()->tosql());
  
  $id = $this->dr->insert ();
  if (!server::has ("visitor.first")) {
   $this->dr->first = $id;
   $this->dr->update ();
   server::$transient->__set('visitor.first', $id);
  }
 }
 
 function count ($duration, $unique = true) {
  $unique = ($unique?"distinct":"");
  $c = $this->db->query ("select $unique first from ".$this->dr->table." where at > now() - interval $duration and kind = " . pt ($this->kind));
  return $c->count();
 }
}

?>
