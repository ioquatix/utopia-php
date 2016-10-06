<?

include("nml.php");
include("db.php");
include("email.php");

// <path chop>
// cut a number of directories off a path
function pchop ($Addr, $P = 1) {
    $A = explode ("/", $Addr);
    while (--$P >= 0)
    	array_pop ($A);
    return join ($A, "/");
}

// <path compare>
// return the differnce between two paths
function pcmp ($Addr1, $Addr2) {
    $T = substr_count ($Addr1, "/") - substr_count ($Addr2, "/");
    if ($T < 0)
        return -$T;
    return $T;
}

// <force directory>
// creates all directories in a path that do not exist
function fdir ($Path, $Mode = 0755) {
    if (is_dir($Path) || strlen($Path) == 0)
        return;
    
    fdir (dirname($Path));
    mkdir ($Path, $Mode);
}

// <format node attribute>
// format a node attribute in a way such that it can be parsed as nml
function fnattr ($Req, $Name, $Attr = null) {
    if ($Attr == null)
        $Attr = $Name;
    return "<$Attr>".$Req[$Name]."</$Attr>";    
}

// <html redirect>
// redirect using a header
function hredirect ($To) {
    header ("Location: $To");
}

// <index directory>
// returns all files within a dir that match a function
function idir ($Addr, $Filter = "is_dir", $Dotf = true) {
    $R = array();
	if ($handle = @opendir("." . $Addr))
		while (false !== ($file = readdir($handle))) {
    		if ($file == ".." || $file == ".")
  			continue;

			if ( strncmp ( basename ($file) , "." , 1 ) == 0 && $Dotf)
				continue;
	
			if (eval($Filter . '("." . $Addr . $file)'))
                array_push ($R, $file);
        }
    return $R;
}

// <find file>
//
function ffile ($Addr, $F, $A = null) {
	$Tmp = explode ("/", $Addr());
	$File = "";

	while (sizeof($Tmp) > 0) {
		$File = "." . join("/", $Tmp) . "/" . $FD;

		if (is_file ($File) || is_dir ($File))
			break;
			
		array_pop ($Tmp);	
	}
		
	if (sizeof($Tmp) == 0)
		return "";
	return $File;
}

// <interpret time>
// interpret a given variable as a time
// if not a time, return current time
function itime ($Time) {
    if (is_string ($Time))
        return strtottime ($Time);
    else if (is_integer ($Time))
        return $Time;
    else
        return time ();
}

// <array copy & apply>
// copy from src items listed in dst applying any functions
// mentioned in dst also
function acopy (&$Dst, $Param, $Src) {
    while (list ($K, $V) = each ($Param))
        if ($Src->is_set ($K))
            $Dst[$K] = eval ($V.'($Src->get($K));');
};

// <hypertext link>
// write a link to another page somewhere
// with a list of arguments
function hlink ($To, $Args = array()) {
    $To .= "?";
    $Argstr = "";
    
    while (list($K, $V) = each ($Args))
        $Argstr .= "&" . $K . "=" . $V;
    
    return $To . substr($Argstr, 1, strlen($Argstr) - 1);
        
};

// <node link>
// return a html link to another node
function nlink ($To, $Args = array()) {
    $Args["node"] = $To;
    return hlink ("./", $Args);
}

// <format time>
// format a given time as a human readble string
// various different formats given
function ftime ($Time, $Format = "medium") {
    if ($Format == "short")
        return date ("Y-m-d H:i:s", $Time);
    else if ($Format == "long")
        return date ("l dS of F Y h:i:s A", $Time);
    else if ($Format == "sql")
        return date ("YmdHis", $Time);
    else //if ($Format == "medium")
        return date("F j, Y, g:i a", $Time);
}

// <get time>
// a lil helper function for getting various pieces of info
// based on the current time
function gtime ($Type = "second") {
    if ($Type == "micro") {
        list ($S, $U) = explode (" ", microtime());
        return (float)$U + (float)$S;
    } else //if ($Type == "second")
        return time();
}

// <html encode>
// encode text as html protected letters
$__htxt_Trans = null;
function htxt ($Text, $Encode = true) {
    global $__htxt_Trans;
    if ($__htxt_Trans == null)
        $__htxt_Trans = get_html_translation_table (HTML_ENTITIES);
    
    if ($Encode)
        return strtr ($Text, $__htxt_Trans);
    else
        return strtr ($Text, array_flip ($__htxt_Trans));
}

// <protect && unprotect>
// protect a string (for, say, insertion into database query)
function prt ($Var) {
    return "'". addslashes($Var) ."'";
        
}

function uprt ($Var) {
    return stripslashes(substr($Var, 1, strlen($Var) - 2));
}

// <dump>
// return the contents of a file
$__dump_Cache = array();
function dump ($File) {
    global $__dump_Cache;
    if (isset($__dump_Cache[$File]))
    	return $__dump_Cache[$File];
    $__dump_Cache[$File] = implode ("", file ("." . $File));
    return $__dump_Cache[$File];
};

// <random key>
// return a random md5 key (useful for session hashing, etc)
function rkey () {
    srand ((double)microtime()*10000000);
    return md5(rand(0, 32000));
};


// <get variable>
// return a variable of certain type
function gvar ($Type, $Name) {
    if ($Type == "header") {
        $H = getallheaders ();
        return $H[$Name];
    } else if ($Type == "server")
        return $_SERVER[$Name];
    else //if ($Type == "request") {
        $V = array_merge ($_GET, $_POST, $_COOKIE);
        return $V[$Name];
        
}

// <server>
// a simple class for handling sessions
class server {
    
    function server () {
        ini_set("session.name", "session-id");
        ini_set("session.use_only_cookies", 1);
	ini_set("session.cookie_lifetime", gtime() + 3600*24*4); //4 days 
	session_start();
	header ("Cache-control: private");

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
};

$Server = new server();

class node {
    var $Node;
    var $Name;
    var $Address;
    var $Path;
    
    // <absolute address>
    function aaddr () {
        return $this->Path;
    }
    
    // <relative address>
    function raddr () {
         return $this->Node;
    }

    function node ($Addr) {
        $Parts = explode ("/", $Addr);
        $Name = array_pop ($Parts);
        if (trim($Name) == "")
            $Name = "index";
        
        $this->Name = $Name;
        $this->Node = implode ("/", $Parts) . "/" . $Name;
      
        do {
            $this->Address = implode ("/", $Parts) . "/$Name";
            $this->Path = implode("/", $Parts) . "/$Name/index";
//            echo ">" . $this->Address . " " . $this->Path . ".node<br>";
            if (is_file ("." . $this->Path . ".node"))
                break;

            $this->Path = $this->Address;
//            echo ">" . $this->Address . " " . $this->Path . ".node<br>";
            if (is_file ("." . $this->Path . ".node"))
                break;
            array_pop ($Parts);
        } while (count($Parts) > 0);

        //echo "<b>" . $this->Address . " " . $this->Path . ".node</b><br>";

        //echo "<br>";
    }

    function render ($Request) {   
        $Type = "html";
        $Buffer = "";
        global $Server;
        ob_start();
        eval ("?>" . dump ($this->Path . ".node") . "<?");
        $Buffer = ob_get_contents();
        ob_end_clean();
        if ($Type == "nml")
            return nml_render($Buffer, $this);
        else //if ($Type == "html")
            return $Buffer;
    }

};

?>
