<?

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

// <path chop>
// cut a number of directories off a path
function pchop ($Addr, $P = 1) {
 $A = explode ("/", $Addr);
 while (--$P >= 0)
  array_pop ($A);
 return join ($A, "/");
}

function pname ($Addr) {
 $A = explode ("/", $Addr);
 $str = array_pop ($A);
 if ($str == "")
  $str = array_pop ($A);
 return $str;
}

function pnorm ($addr) {
 $A = explode ("/", $addr);
 $str = array_pop ($A);
 if ($str == "")
  $str = array_pop ($A);
 if ($str != "index")
  return implode ("/", $A) . "/$str";
 return implode ("/", $A);
}

// <path compare>
// return the differnce between two paths
function pcmp ($Addr1, $Addr2) {
 return substr_count ($Addr1, "/") - substr_count ($Addr2, "/");
}

// <force directory>
// creates all directories in a path that do not exist
function fdir ($Path, $Mode = 0755) {
 if (is_dir($Path) || strlen($Path) == 0)
  return;
    
 fdir (dirname($Path));
 mkdir ($Path, $Mode);
}

// <dump>
// return the contents of a file
$__dump_cache = array();
function dump ($File, $inc = false) {
 global $__dump_cache, $__dump_miss, $__dump_hit;
 if (isset($__dump_cache[$File])) {
  return $__dump_cache[$File]; 
 }
 if ($inc)
  $f = file ($File, 1);
 else
  $f = file ("./" . $File, 0);
 if (!$f)
  return "";
 $__dump_cache[$File] = implode ("", $f);
 return $__dump_cache[$File];
}

function write ($File, $str) {
 $file = fopen ("." . $File, "w");
 fwrite ($file, $str);
 fclose($file);
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

// <index directory>
// returns all files within a dir that match a function
function is_node ($name) {
 if (eregi ("(.node)$", $name))
  return true;
 
 return false;
}

function idir ($Addr, $Filter = "is_dir", $Dotf = false) {
 $R = array();
 if ($handle = @opendir("." . $Addr))
  while (false !== ($file = readdir($handle))) {
   if ($file == ".." || $file == ".")
    continue;

   if ( strncmp ( basename ($file) , "." , 1 ) == 0 && !$Dotf)
    continue;

   if (call_user_func ($Filter, "." . $Addr . "/" . $file))
    array_push ($R, $file);
  }
 return $R;
}

// <redirect>
// redirect using a header
function redir ($To, $Args = array(), $end = true) {
 $To = lnk ($To, $Args);
 header ("Location: $To");
 if ($end)
  end;
}

// <node link>
// return a html link to another node
function lnk ($NTo, $Args = array()) {
 $To .= "./?";
 $Argstr = "";
 $Args["node"] = $NTo;
 $Pre = "";
        
 while (list($K, $V) = each ($Args)) {
  $V = rawurlencode ($V);
   $Argstr .= $Pre . $K . "=" . $V;
   $Pre = ";";
 }
    
 return $To . $Argstr;
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

function ntxt ($Text) {
 $Text = ereg_replace ("\r?\n", "<br/>\n", $Text);
 $Text = ereg_replace ("<br/>\n<br/>\n", "\n</paragraph><paragraph>\n", $Text);
 return "<paragraph>\n" . $Text . "\n</paragraph>";
}

// <interpret time>
// interpret a given variable as a time
// if not a time, return current time
function itime ($Time) {
 if (is_string ($Time) && strlen ($Time))
  return strtotime ($Time);
 else if (is_integer ($Time))
  return $Time;
 else
  return time ();
}

// <format time>
// format a given time as a human readble string
// various different formats given
define ("TIME_TINY", 0);
define ("TIME_SHORT", 1);
define ("TIME_MEDIUM", 2);
define ("TIME_LONG", 3);
define ("TIME_SQL", 4);

function ftime ($Time, $Format = TIME_MEDIUM) {
 $Time = itime ($Time);
 if ($Format == TIME_TINY)
  return date ("j/n", $Time);
 else if ($Format == TIME_SHORT)
  return date ("j/n G:ia", $Time);
 else if ($Format == TIME_LONG)
  return date ("l jS of F Y h:i:s A", $Time);
 else if ($Format == TIME_SQL)
  return date ("YmdHis", $Time);
 else //if ($Format == TIME_MEDIUM)
  return date("F j, Y, g:i a", $Time);
}

function fgmtime ($Time, $Format = TIME_MEDIUM) {
 $Time = itime ($Time);
 if ($Format == TIME_TINY)
  return gmdate ("j/n", $Time);
 else if ($Format == TIME_SHORT)
  return gmdate ("j/n G:ia", $Time);
 else if ($Format == TIME_LONG)
  return gmdate ("l jS of F Y h:i:s A", $Time);
 else if ($Format == TIME_SQL)
  return gmdate ("YmdHis", $Time);
 else //if ($Format == TIME_MEDIUM)
  return gmdate("F j, Y, g:i a", $Time);
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

// <random key>
// return a random md5 key (useful for session hashing, etc)
function rkey () {
 srand ((double)microtime()*10000000);
 return md5(rand(0, 32000));
}

?>
