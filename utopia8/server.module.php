<?

srand ((double)microtime()*10000000);

ld ('path', 'browser');

function rkey () {
 return md5(rand(0, 32000));
}

class fileobject extends attributes {
 const OBJECT_PATH = '/var/www/object/';
 private $prefix = '';
 
 function __construct ($prefix) {
  $this->prefix = $prefix . '/';
  path::make (fileobject::OBJECT_PATH . $this->prefix);
 }
  
 function load ($id) {
  $name = fileobject::OBJECT_PATH . $this->prefix . $id;
  if (!is_file ($name))
   return false;
  $this->data = $id;
  $this->values = unserialize(implode("", file ($name)));
  return true;
 }
 
 function save () {
  if ($this->data == '') return;
  $id = $this->data;
  $name = fileobject::OBJECT_PATH . $this->prefix . $id;
  $file = fopen ($name, "w");
  fwrite ($file, serialize ($this->all()));
  fclose ($file);
  chmod ($name, 0664);
 }

 function create ($id = null) {
  if (!$id)
   $id = rkey ();
  while (is_file (fileobject::OBJECT_PATH . $this->prefix . $id))
   $id = rkey ();
  $this->data = $id;
  $this->save ();
  return $id;
 }
 
 function destroy ($id = null) {
  if ($id)
   @unlink ($f = fileobject::OBJECT_PATH . $this->prefix . $id);
  else {
   @unlink ($f = fileobject::OBJECT_PATH . $this->prefix . $this->data);
   $this->clear ();
  }
 }
}

class server {
 const TRANSIENT_COOKIE = 'transient-id';
 const PERSISTENT_COOKIE = 'persistent-id';

 static public $site;
 static public $persistent;
 static public $transient;
 
 static function initialise ($objecttype = fileobject) {
  server::$site = new $objecttype ('site'); 
  server::$persistent = new $objecttype ('persistent');
  server::$transient = new $objecttype ('transient');
  
  if (!server::$site->load ($_SERVER["SERVER_NAME"]))
   server::$site->create ($_SERVER["SERVER_NAME"]);
   
  if (!server::$persistent->load ($_COOKIE[server::PERSISTENT_COOKIE]))
   server::$persistent->create ();
  
  setcookie (server::PERSISTENT_COOKIE, server::$persistent->data, time() + 3600 * 24 * 48 , '/');
  
  //load transient data
  if (!server::$transient->load ($_COOKIE[server::TRANSIENT_COOKIE])) {
   server::$transient->create ();
   if (server::$persistent->has ('server.transient'))
    server::$transient->destroy (server::$persistent->get ('server.transient'));
  }
  
  server::$persistent->set ('server.transient', server::$transient->data);
  
  setcookie (server::TRANSIENT_COOKIE, server::$transient->data, 0, '/');
  ld ('browser');
  
  if (browser::client() == browser::WEBBROWSER)
   request::handle_finish (array('server', 'save'));
  else
   request::handle_finish (array('server', 'destroy'));
 }
 
 static function save () {
  server::$persistent->set ('server.last-visit', time());
  if (server::$persistent->has ('server.total-visits'))
   server::$persistent->set ('server.total-visits', server::$persistent->get ('server.total-visits') + 1);
  else
   server::$persistent->set ('server.total-visits', 1);
  
  server::$site->save ();
  server::$transient->save ();
  server::$persistent->save ();
 }

 static function destroy () {
  server::$persistent->destroy();
  server::$transient->destroy();
 }
 
 static function get ($name) {
  if (server::$transient->has ($name))
   return server::$transient->$name;
  else if (server::$persistent->has ($name))
   return server::$persistent->$name;
  else
   return server::$site->$name;
 }

 static function has ($name) {
  return server::$transient->has ($name) 
          || server::$persistent->has ($name)
	  || server::$site->has ($name);
 }

 static function variable ($name) {
  if (isset ($_SERVER[$name]))
   return $_SERVER[$name];
  else {
   $h = getallheaders ();
   return $h [$name];
  }
 }
}

server::initialise ();

// security levels

class security {
 static private $levels = array();
 static public $specials = array (
  'internal' => array('request', 'internal')
 );

 static function raise () {
  foreach (func_get_args() as $name)
   security::set ($name);
 }
 
 static function lower () {
  foreach (func_get_args() as $name)
   security::un ($name);
 }
 
 static function requires () {
  foreach (func_get_args() as $name)
   if (! security::has ($name))
    request::error (request::ACCESS_ERROR, "Access was denied");
   
  return true;
 }
 
 static function has ($name) {
  if (isset (security::$specials[$name]))
   return call (security::$specials[$name]);
  return server::get ('security.'.$name);
 }
 
 static function set ($name) {
  return server::$transient->set ('security.'.$name, 1);
 }
 
 static function un ($name) {
  return server::$transient->un ('security.'.$name);
 }

 static function call ($node, $request) {
  $cls = substr($node->extn, 1);
  $fn = $node->name;
  security::requires ('call.'.$cls.'.'.$fn);
  echo call (array($cls, $fn), $request);
 }
}

//default level for permissions, etc
security::raise ('default');

?>
