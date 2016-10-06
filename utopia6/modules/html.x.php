<?

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

function meco ($attr, $fr, $as) {
 if (isset($attr[$fr]))
  return " $as=\"".$attr[$fr].'"';
 else
  return "";
}

function ntxt ($Text) {
 $Text = ereg_replace ("\r?\n", "<br/>\n", $Text);
 $Text = ereg_replace ("<br/>\n<br/>\n", "\n</paragraph><paragraph>\n", $Text);
 return "<paragraph>\n" . $Text . "\n</paragraph>";
}

function mfmt ($amnt) {
 if (!is_numeric ($amnt))
  return ($amnt);
// return money_format("$%.2n", $amnt);
 return "$" . number_format ($amnt, 2);
}

function pfmt ($amnt) {
 return ($amnt * 100.0) . "%";
}

function linkto ($lnk) {
 if (isset ($lnk["node"])) {
  $n = $lnk["node"]; 
  unset ($lnk["node"]);
  return lnk ($n, $lnk);
 } else if (isset($lnk["to"]))
  return $lnk["to"];
 else
  return "#";
}

$o = new override_node ("root", '<?echo $data?>');
$o = new override_node ("b", '<b><?echo $data?></b>');
$o = new override_node ("i", '<i><?echo $data?></i>');
$o = new override_node ("note", '<span class="note"><?echo $data?></span>');
$o = new override_node ("money", '<span<?=($data<0)?\' style="color: red;"\':\'\'?>><?=mfmt($data)?></span>');
$o = new override_node ("percent", '<span<?=($data<0)?\' style="color: red;"\':\'\'?>><?=pfmt($data)?></span>');
$o = new override_node ("br", "<br />");
$o = new override_node ("title", "<div class=\"title\" style=\"<?echo isset(\$attributes[\"align\"])?\"text-align: \".\$attributes[\"align\"].\";\":\"\"?>\">\n<?echo \$data?></div>\n");
$o = new override_node ("subtitle", "<div class=\"subtitle\" style=\"<?echo isset(\$attributes[\"align\"])?\"text-align: \".\$attributes[\"align\"].\";\":\"\"?>\">\n<?echo \$data?></div>\n");
$o = new override_node ("paragraph", "<div class=\"paragraph\" style=\"<?echo isset(\$attributes[\"align\"])?\"text-align: \".\$attributes[\"align\"].\";\":\"\"?>\">\n<?echo \$data?></div>\n");
$o = new override_node ("block", "<div class=\"block\" style=\"<?echo isset(\$attributes[\"align\"])?\"text-align: \".\$attributes[\"align\"].\";\":\"\"?>\">\n<?echo \$data?></div>\n");
$o = new override_node ("code", "<pre class=\"code\" style=\"<?echo isset(\$attributes[\"align\"])?\"text-align: \".\$attributes[\"align\"].\";\":\"\"?>\">\n<?echo \$data?></pre>\n");
$o = new override_node ("detail", "<span class=\"detail\">\n<?echo \$data?></span>\n");
$o = new override_node ("link", "<a href=\"<?=linkto(\$attributes)?>\"><?echo \$data?></a>");
mkn ('quote', '<div class="quote"><?=$data?></div>');

//
//Columns...
//

$o = new override_node ("columns", '<table width="100%" cellpadding="0" cellspacing="0">
 <tr>
 <? foreach ($attributes as $a) { ?>
 <td width="50%" class="paragraph" style="vertical-align: top;"><?echo $a?></td>
 <? } ?>
 </tr>
</table>');

//
//Images..
//

$o = new override_node ("image", '<?
global $path;
if (!isset($attributes["path"]))
    $attributes["path"] = $path;
  
if (isset($attributes["src"]))
 $src = $attributes["src"];
else
 $src = "." . $attributes["path"] . "/" . $attributes["name"];
  

if ($attributes["align"] == "left" || $attributes["align"] == "right")
    echo \'<img alt="'.$attributes["title"].'" src="\'.$src.\'" style="float: \'.$attributes["align"].\'" />\';
else
   echo \'<img alt="'.$attributes["title"].'" src="\'.$src.\'" />\';
?>');

//
//Forms...
//

$o = new override_node ("option", 
'<option value="<?echo $attributes["value"]?>"<?echo isset($attributes["selected"])?" selected":""?>><?echo $data?></option>');
$o = new override_node ("form", "<form<?=meco(\$attributes, \"name\", \"id\")?> action=\"<?echo \$attributes[\"action\"]?>\" method=\"post\"><div style=\"display: inline;\">\n<?echo \$data?>\n</div></form>\n");
$o = new override_node ("input", 
'<?if ($attributes["type"] == "string") {
?><input class="text" size="<?echo $attributes["width"]>10?$attributes["width"]:10?>" name="<?echo $attributes["name"]?>" value="<?echo $attributes["value"]?>" /><?
}else if ($attributes["type"] == "password") {
?><input class="text" type="password" name="<?echo $attributes["name"]?>" value="<?echo $attributes["value"]?>" /><?
}else if ($attributes["type"] == "submit") {
?><input class="button" type="submit" name="<?echo $attributes["name"]?>" value="<?echo $attributes["value"]?>" /><?
}else if ($attributes["type"] == "reset") {
?><input class="button" type="reset" name="<?echo $attributes["name"]?>" value="<?echo $attributes["value"]?>" /><?
}else if ($attributes["type"] == "hidden") {
?><input type="hidden" name="<?echo $attributes["name"]?>" value="<?echo $attributes["value"]?>" /><?
}else if ($attributes["type"] == "select") {
?><select name="<?echo $attributes["name"]?>">
<?echo $data?>
</select><?
}else if ($attributes["type"] == "text") {
?><textarea wrap="virtual" name="<?echo $attributes["name"]?>" rows="12" cols="80"><?echo $attributes["value"].$data?></textarea><?
}else if ($attributes["type"] == "checkbox") {
?><input type="checkbox" name="<?=$attributes["name"]?>" value="<?=$attributes["value"]?>"<?=(isset($attributes["checked"])?" checked":"")?> /><?
}?>');


//
// Records..
//
global $alt;
$alt = false;

$o = new override_node ("recordset", 
'<table class="recordset" width="100%">
<?
global $alt;
$alt = false;
echo "<tr class=\"recordset_header\">";
$sizes = explode (":", $attributes["sizes"]);
$alignments = explode (":", $attributes["alignments"]);
$header = explode (":", $attributes["indicies"]);
$c = 0;
while (count($header) > $c) {
 echo "<td style=\"width: $sizes[$c]; text-align: $alignments[$c];\">".$header[$c]."</td>";
 ++$c;
}
echo "</tr>";
echo $data;
?>
</table>');

$o = new override_node ("record", 
'<? global $alt;
$alignments = explode(":", $attributes["alignments"]);
echo "<tr class=\"recordset_record".($alt?"":"_alt")."\">";
$alt = !$alt;
$c = 0;
foreach (array_keys($attributes) as $col) {
 if ($col == "alignments")
  continue;
 echo "<td style=\"vertical-align: top; text-align: ".$alignments[$c].";\">" . $attributes[$col] . "</td>";
 ++$c;
}
?></tr>');

?>