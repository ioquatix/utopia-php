<?

$o = new override_node ("image", '<?
global $path;
if (!isset($attributes["path"]))
    $attributes["path"] = $path;
  
if ($attributes["align"] == "center")
    echo \'<div style="text-align: center;"><img alt="'.$attributes["title"].'" src=".\'.$attributes["path"] . "/" . $attributes["name"].\'" style="display: inline" /></div>\';
else if ($attributes["align"] == "left" || $attributes["align"] == "right")
    echo \'<img alt="'.$attributes["title"].'" src=".\'.$attributes["path"] . "/" . $attributes["name"].\'" style="float: \'.$attributes["align"].\'" />\';
else
   echo \'<img alt="'.$attributes["title"].'" src=".\'.$attributes["path"] . "/" . $attributes["name"].\'" />\';
?>');

?>