<?
define ("FORM_STRING", 1);
define ("FORM_TEXT", 2);
define ("FORM_PASSWORD", 3);
define ("FORM_SUBMIT", 4);
define ("FORM_CHECKBOX", 5);
define ("FORM_HIDDEN", 6);
define ("FORM_SELECTBOX", 7);

function mform ($name, $items, $action) {
 $buf = "";
 $buf .= "<form action=\"$action\" method=\"post\">";
 foreach ($items as $item) {
  if ($item[0] == FORM_STRING)
   $buf .= "<div class=\"text\">".$item[2].":</div><input name=\"".$item[1]."\" class=\"text\" value=\"".$item[3]."\">";
  else if ($item[0] == FORM_HIDDEN)
   $buf .= "<input type=\"hidden\"name=\"".$item[1]."\" value=\"".$item[2]."\">";
  else if ($item[0] == FORM_PASSWORD)
   $buf .= "<div class=\"text\">".$item[2]."</div><input type=\"password\" name=\"".$item[1]."\" class=\"text\" value=\"".$item[3]."\">";
  else if ($item[0] == FORM_TEXT)
   $buf .= "<div class=\"text\" style=\"vertical-align: top;\">".$item[2].":</div><textarea wrap=\"virtual\" name=\"".$item[1]."\" class=\"text\" rows=\"6\" cols=\"40\">".$item[3]."</textarea>";
  else if ($item[0] == FORM_CHECKBOX)
   $buf .= "<div class=\"text\" style=\"vertical-align: top;\"><input type=\"checkbox\" name=\"".$item[1]."\" class=\"text\" value=\"true\" ".($item[3]?"checked":"").">".$item[2]."</div>";
  else if ($item[0] == FORM_SELECTBOX) {
   $buf .= "<div class=\"text\" style=\"vertical-align: top;\">".$item[2].":</div><select name=\"".$item[1]."\">";
   foreach (array_keys($item[3]) as $opt) {
    $buf .= "<option value=\"".$item[3][$opt].'"'.($item[3][$opt]==$item[4]?" selected":"").">".$opt."</option>";
   }
   $buf .= "</select>";
  } else if ($item[0] == FORM_SUBMIT)
   $buf .= "<div><input type=\"submit\" name=\"".$item[1]."\" value=\"".$item[2]."\" class=\"text\"></div>";
 }
 return $buf . "</form>";
}
?>