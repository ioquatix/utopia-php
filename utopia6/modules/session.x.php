<?

ldmod ("object");

function session_save () {
 global $server, $session;
 $server->set ("server.last_visit", time ());

 $server->save();
 $session->save();
}

global $server, $session;
$server = new dataobject ("persistent", $_COOKIE["persistent-session-id"]);
setcookie ( "persistent-session-id", $server->id(), time() + 3600 * 24 * 48 , "/");

if ($server->is_set ("server.total_visits"))
 $server->set ("server.total_visits", 1 + $server->get("server.total_visits"));
else
 $server->set ("server.total_visits", 1);
         
$session = new dataobject ("transient", $_COOKIE["transient-session-id"]);
setcookie ( "transient-session-id", $session->id(), 0 , "/");

register_finish_function (session_save);

?>