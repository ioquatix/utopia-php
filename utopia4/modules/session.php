<?

// <server>
// a simple class for handling sessions
class server {
    
    function server () {
       	header ("Cache-control: private");
        ini_set("session.name", "session-id");
        ini_set("session.use_only_cookies", 1);
       	ini_set("session.cookie_lifetime", gtime() + 3600*24*4); //4 days 
        session_start();


        $this->set ("server.last_visit", gtime ());
        if ($this->is_set ("server.total_visits"))
         $this->set ("server.total_visits", 1 + $this->get("server.total_visits"));
        else
         $this->set ("server.total_visits", 1);
    }

    function un_set ($Name) {
        unset ($_SESSION[$Name]);
    }

    function is_set ($Name) {
        return isset ($_SESSION[$Name]);
    }

    function set ($Name, $Value) {
        $_SESSION[$Name] = $Value;
    }

    function get ($Name) {
        return $_SESSION[$Name];
    }
    
    // <rewrite attribute>
    function fnattr ($Name, $Tn = null) {
        return fnattr ($_SESSION, $Name, $Tn);
    }
    
    function id () {
    	return session_id();
    }
};

global $session;
$session = new server();

?>
