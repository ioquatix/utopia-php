<?

function mfmt ($amnt) {
 if (!is_numeric ($amnt))
  return ($amnt);
// return money_format("$%.2n", $amnt);
 return "$" . number_format ($amnt, 2);
}

function pfmt ($amnt) {
 return ($amnt * 100.0) . "%";
}

function linkencode ($lnk) {
 return $lnk;
}

$o = new override_node ("root", '<?echo $data?>');
$o = new override_node ("b", '<b><?echo $data?></b>');
$o = new override_node ("i", '<i><?echo $data?></i>');
$o = new override_node ("note", '<span class="note"><?echo $data?></span>');
$o = new override_node ("money", '<span<?=($data<0)?\' style="color: red;"\':\'\'?>><?=mfmt($data)?></span>');
$o = new override_node ("percent", '<span<?=($data<0)?\' style="color: red;"\':\'\'?>><?=pfmt($data)?></span>');
$o = new override_node ("br", "<br />");
$o = new override_node ("title", "<div class=\"title\" style=\"text-align: <?echo isset(\$attributes[\"align\"])?
\$attributes[\"align\"]:\"\"?>;\">\n<?echo \$data?></div>\n");
$o = new override_node ("subtitle", "<div class=\"subtitle\" style=\"<?echo isset(\$attributes[\"align\"])?\"text-align: \".\$attributes[\"align\"].\";\":\"\"?>\">\n<?echo \$data?></div>\n");
$o = new override_node ("paragraph", "<div class=\"paragraph\" style=\"<?echo isset(\$attributes[\"align\"])?\"text-align: \".\$attributes[\"align\"].\";\":\"\"?>\">\n<?echo \$data?></div>\n");
$o = new override_node ("code", "<pre class=\"code\" style=\"<?echo isset(\$attributes[\"align\"])?\"text-align: \".\$attributes[\"align\"].\";\":\"\"?>\">\n<?echo \$data?></pre>\n");
$o = new override_node ("detail", "<span class=\"detail\">\n<?echo \$data?></span>\n");
$o = new override_node ("link", "<a href=\"<?echo linkencode(\$attributes[\"to\"])?>\"><?echo \$data?></a>");

?>