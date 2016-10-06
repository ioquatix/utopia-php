<?

require_once ("utopia6/utopia.php");
$start_time = mtime ();

$node = new node ("/" . $_GET["request"]);
global $__node_address; $__node_address = $node->address;
$tmp = explode ("/", $node->address);

while (count($tmp) > 0) {
 if (is_file ("./" . join("/", $tmp) . "/" . "node.header"))
 include_once ("./" . join("/", $tmp) . "/" . "node.header");
 array_pop ($tmp);
}

//$buffer = 
$node->render("./", array_merge($_GET, $_POST, $_COOKIE), "");
$end_time = mtime ();
//echo str_replace ("[%stats%]", number_format(($end_time - $start_time), 4), $buffer);

?>