<?

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