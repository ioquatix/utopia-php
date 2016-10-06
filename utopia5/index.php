<?

include("utopia5/utopia.php");
$start_time = gtime ("micro");

$node = new node ($_GET["node"]);
$tmp = explode ("/", $node->address);

while (count($tmp) > 0) {
 @include_once ("." . join("/", $tmp) . "/" . "node.header");
 array_pop ($tmp);
}

$buffer = $node->render("./", array_merge($_GET, $_POST, $_COOKIE), "");
$end_time = gtime ("micro");
global $__dump_hit, $__dump_miss;
echo str_replace ("[%stats%]", number_format(($end_time - $start_time), 4), $buffer);

?>