<?

class words {
 static function proper ($name) {
  $name = str_replace ("_", " ", $name);
  return ucwords ($name);
 }

 static function improper ($name) {
  $name = str_replace (" ", "_", $name);
  return $name;
 }

 static function pluralize ($int, $word, $s, $m) {
  if ($int == 1)
   return $word . $s;
  else
   return $word . $m;
 }

 static function humanize ($bytes, $precision = 2, $names = array ('B', 'KB', 'MB', 'GB', 'TB')) {
  if (!is_numeric($bytes) || $bytes < 0)
   return null;
       
   for ($level = 0; $bytes >= 1024; $level++)
    $bytes /= 1024;
   
   return round ($bytes, $precision) . ' ' . $names[$level];
 }
}

?>
