<?

function ldmod ($name, $version = "x") {
 require_once ("utopia5/modules/$name.$version.php");
}

ldmod ("functions");
include ("parser.php");

?>