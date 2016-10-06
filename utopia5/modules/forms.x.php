<?

$o = new override_node ("option", 
'<option value="<?echo $attributes["value"]?>"<?echo isset($attributes["selected"])?" selected":""?>><?echo $data?></option>');
$o = new override_node ("form", "<form action=\"<?echo \$attributes[\"action\"]?>\" method=\"post\">\n<?echo \$data?>\n</form>\n");
$o = new override_node ("input", 
'<?if ($attributes["type"] == "string") {
?><input width="<?echo $attributes["width"]>10?$attributes["width"]:10?>" name="<?echo $attributes["name"]?>" value="<?echo $attributes["value"]?>"><?
}else if ($attributes["type"] == "password") {
?><input type="password" name="<?echo $attributes["name"]?>" value="<?echo $attributes["value"]?>"><?
}else if ($attributes["type"] == "submit") {
?><input type="submit" name="<?echo $attributes["name"]?>" value="<?echo $attributes["value"]?>"><?
}else if ($attributes["type"] == "hidden") {
?><input type="hidden" name="<?echo $attributes["name"]?>" value="<?echo $attributes["value"]?>"><?
}else if ($attributes["type"] == "select") {
?><select name="<?echo $attributes["name"]?>">
<?echo $data?>
</select><?
}else if ($attributes["type"] == "text") {
?><textarea wrap="virtual" name="<?echo $attributes["name"]?>" rows=\"8\" cols=\"60\"><?echo $attributes["value"].$data?></textarea><?
}else if ($attributes["type"] == "checkbox") {
?><input type="checkbox" name="<?=$attributes["name"]?>" value="<?=$attributes["value"]?>"<?=(isset($attributes["checked"])?" checked":"")?>><?
}?>');

?>