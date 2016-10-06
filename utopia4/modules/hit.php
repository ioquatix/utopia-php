<?

ldmod ("more");

function hit_update (&$cursor, $session) {
 $session = prt($session);
 $agent = prt(gvar("server", "HTTP_USER_AGENT"));
 $host = prt(gethostbyname(gvar("server", "REMOTE_ADDR")));
 $request = prt(gvar("server", "REQUEST_URI"));
 $referer = prt(gvar("header", "Referer"));

 $cursor->query ("insert into hit values ('', now(), $session, $agent, $host, $request, $referer)");
}

function hit_simple_statistics (&$cursor) {
 $cursor->query ("select distinct session from hit where at > now() - interval 30 day");
 $v30 = $cursor->number_of_records();
 $cursor->query ("select distinct session, remote_host from hit where at > now() - interval 24 hour");
 $v24 = $cursor->number_of_records();
 return $v24 . " visitor".($v24!=1?"s":"")." in the last 24 hours, $v30 in the last month";
}

?>