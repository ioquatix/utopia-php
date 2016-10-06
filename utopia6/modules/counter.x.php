<?

ldmod ("session");

function hit_update (&$db, $session) {
 $session = prt($session);
 $agent = prt(gvar("server", "HTTP_USER_AGENT"));
 $host = prt(gethostbyname(gvar("server", "REMOTE_ADDR")));
 $request = prt(gvar("server", "REQUEST_URI"));
 $referer = prt(gvar("header", "Referer"));

 $db->query ("insert into hit values ('', now(), $session, $agent, $host, $request, $referer)");
}

function hit_simple_statistics (&$db) {
 $c = $db->query ("select distinct session from hit where at > now() - interval 30 day");
 $v30 = $c->number_of_records();
 $c = $db->query ("select distinct session, remote_host from hit where at > now() - interval 24 hour");
 $v24 = $c->number_of_records();
 return $v24 . " visitor".($v24!=1?"s":"")." in the last 24 hours, $v30 in the last month";
}

function hit_simple_node () {
 global $database;
 global $session;
 $sid = prt($session->id());
 $host = prt(gethostbyname(gvar("server", "REMOTE_ADDR")));
 $request = prt(gvar("server", "REQUEST_URI"));
 $q = $database->query ("select * from hit where session = $sid and request_uri = $request and remote_host = $host and at > now() - interval 24 hour");
 if (!$q->next_record()) //if we are just a simple hit counter dont worry about repeats..
  hit_update($database, $session->id());
 return hit_simple_statistics ($database);
}

$o = new override_node ("hit", '<?echo hit_simple_node();?>');

?>