<?

ld ('path');

class upload {

 static function move ($name, $to, $filename=null) {
  if ($filename == null) $filename = $_FILES[$name]['name'];
  
  if (($pn = path::filename ($to)) != '') {$to = path::directory ($to); $filename = $pn;};
  
  if (!isset($_FILES[$name])) return false;
  if (!move_uploaded_file ($_FILES[$name]['tmp_name'],
      $to . $filename)) {
   return false;
  }
  return true;
 }

 static function tmpname ($name) {
  return $_FILES[$name]['tmp_name'];
 }

 static function name ($name) {
  return $_FILES[$name]['name'];
 }

 static function has ($name) {
  return isset ($_FILES[$name]) and upload::size ($name) != 0;
 }

 static function metatype ($name) {
  return file_content_type ($_FILES[$name]['tmp_name']);
 }

 static function size ($name) {
  return $_FILES[$name]['size'];
 }
}

?>
