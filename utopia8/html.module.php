<?

ld ('words');

parser::template ("line", '<hr style="border: 1px solid #acacac; margin-bottom:0px; margin-top: 10px;" />');

new template ("quote", '<p style="border-left: 2px solid black; margin-left: 10px; padding-left: 10px; padding-top: 4px; padding-bottom: 4px;"><?=$request->data?></p>');

parser::template ("error", '<span class="error"><?=$request->data?></span>');
parser::template ("semantic", '<span class="code" style="font-family: monospace;"><?=$request->data?></span>');

parser::passthrough ('p', 'br', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'a', 'b', 'i');

parser::passthrough ('div', 'span', 'pre');

function linkto ($lnk) {
 global $__link_prepend;
 if (isset ($lnk["node"])) {
  $n = $__link_prepend . $lnk["node"]; 
  unset ($lnk["node"]);
  return document::link ($n, $lnk);
 } else if (isset($lnk["to"]))
  return $lnk["to"];
 else {
  if (request::$node->extn == '.node')
   return document::link (request::$node->name, $lnk);
  else
   return document::link (request::$node->file(), $lnk);
 }
}

parser::template ('mailto', '<a href="mailto<?=$request->data?>"><?=$request->data?></a>');

parser::template ('link', '<a href="<?=linkto($request->all())?>"><?=$request->data?></a>');

parser::template ('image', '<?=$request->align=="center"?"<center>":""?><img style="<?=($request->align!="center"&&$request->align!="")?"float: ".$request->align:""?>" src="<?=$request->src?>" alt="<?=basename($request->src)?>" /><?=$request->align=="center"?"</center>":""?>');

//
// Records..
//
global $alt;
$alt = false;

parser::template ("recordset", 
'<table cellpadding="0" cellspacing="0"class="recordset" width="100%">
<?
global $alt;
$alt = false;
$header = explode (":", $request->indicies);
if ($request->has ("functions")) {
 echo "<tr><td class=\"functions\" colspan=\"".count($header)."\">";
 echo $request->functions; 
 echo "</td></tr>";
}
echo "<tr class=\"header\">";
$sizes = explode (":", $request->sizes);
$alignments = explode (":", $request->alignments);
$c = 0;
while (count($header) > $c) {
 echo "<td style=\"width: $sizes[$c]; text-align: $alignments[$c];\">".$header[$c]."</td>";
 ++$c;
}
echo "</tr>";
echo $request->data;
?>
</table>');

parser::template ("record", 
'<? global $alt;
$alignments = explode(":", $request->alignments);
$spans = explode (":", $request->span);
echo "<tr class=\"record".($alt?"":" alt")."\">";
$alt = !$alt;
$c = 0;
foreach ($request->all() as $col => $d) {
 if ($col == "alignments" or $col == "span")
  continue;
 echo "<td colspan=\"".$spans[$c]."\" style=\"vertical-align: top; text-align: ".$alignments[$c].";\">" . $d . "</td>";
 ++$c;
}
?></tr>');

parser::passthrough ('table', 'tr', 'td', 'th');
parser::underride ('table', array ('cellpadding' => '0', 'cellspacing' => '0', 'width' => '100%'));

function mfmt ($amnt) {
 if (!is_numeric ($amnt))
  return ($amnt);
 return "$" . number_format ($amnt, 2);
}

function pfmt ($amnt) {
 return ($amnt * 100.0) . "%";
}

new template ("money", '<span<?=($request->data<0)?\' style="color: red;"\':\'\'?>><?=mfmt($request->data)?></span>');
new template ("percent", '<span<?=($request->data<0)?\' style="color: red;"\':\'\'?>><?=pfmt($request->data)?></span>');

parser::template ('group', '<table class="group"><?=$request->data?></table>');
parser::template ('group-item', '<tr><td class="item name"style="text-align: right; vertical-align: middle;"><?=$request->name?></td><td class="item data" style="vertical-align: top;"><?=$request->data?></td></tr>');
parser::template ('group-header', '<tr><td class="header" colspan="2"><?=$request->data?></td></tr>');
parser::template ('group-bar', '<tr><td class="bar" colspan="2" style="text-align: right"><hr /><?=$request->data?></td></tr>'); 

function meco ($attr, $fr, $as) {
 if (isset($attr[$fr]))
  return " $as=\"".$attr[$fr].'"';
 else
  return "";
}

parser::underride ('form', array ('method' => 'post', 'enctype' => 'multipart/form-data'));

parser::template ('string', '<input type="text" class="text" size="<?=($request->width>=1?$request->width:20)?>" name="<?=$request->name?>" value="<?=$request->data?>" />');
parser::template ('file', '<input type="file" class="text" size="<?=($request->width>=1?$request->width:20)?>" name="<?=$request->name?>" />');
parser::template ('password', '<input type="password" class="text" size="<?=($request->width>=1?$request->width:20)?>" name="<?=$request->name?>" value="<?=$request->data?>" />');
parser::template ('submit', '<input class="button" type="submit" name="<?=preg_replace("/\s+/", "", $request->name)?>" value="<?=($request->data!=""?$request->data:words::proper($request->name))?>" />');
parser::template ('reset', '<input class="button" type="submit" name="<?=preg_replace("/\s+/", "", $request->name)?>" value="<?=($request->data!=""?$request->data:ucwords($request->name))?>" />');
parser::template ('hidden', '<input type="hidden" name="<?=$request->name?>" value="<?=$request->data?>" />');
parser::passthrough ('select');

define ('SELECTED', ' selected="true" ');

parser::template ('option', '<option value="<?=($request->has ("value")?$request->value:$request->data)?>"<?=$request->selected=="true"?" selected":""?>><?echo $request->data?></option>');
parser::template ('text', '<textarea wrap="virtual" name="<?=$request->name?>" rows="<?=$request->height?$request->height:12;?>" cols="<?=$request->width?$request->width:40;?>"><?=$request->value.$request->data?></textarea>');

define ('CHECKED', ' checked="true" ');

parser::template ('checkbox', '<label><input type="checkbox" name="<?=$request->name?>" value="<?=$request->value?>"<?=(isset($request->checked)?" checked":"")?> /> <?=$request->data?></label>');
parser::template ('togglebox', '<label><input type="checkbox" name="<?=$request->name?>" value="1"<?=($request->value==true?" checked=\"true\"":"")?> /> <?=$request->data?></label>');

// search / google

new template ("google", '<a href="http://www.google.com/search?q=<?=$request->data?>"><?=$request->data?></a>');

new template ("dictionary", '<a href="http://dictionary.reference.com/search?q=<?=$request->data?>"><?=$request->data?></a>');

// raw

class raw {
 static $tmp = array ();

 static function save ($data) {
  array_push (raw::$tmp, $data);
  return '<raw>' . (count (raw::$tmp) - 1) . '</raw>';
 }
}

new template ("raw", '<?=raw::$tmp[$request->data];?>');

// syntax highlighting


parser::template ("code", '<pre class="code"><?
if ($request->has ("language")) {
 ld ("shell");
 $proc = shell::open ("syntax " . escapeshellcmd($request->language) . " xml");
 $proc->write ($request->data);
 echo $proc->finish();
} else {
 echo $request->data;
}?></pre>');

?>
