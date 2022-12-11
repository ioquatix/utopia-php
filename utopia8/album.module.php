<?

class album {
 static function init () {
  request::handler ('.album', array ('album', 'picture'));
 }

 static $sizes = array (
  'tiny' => array ('y' => 80),
  'small' => array ('y' => 120),
  'medium' => array ('y' => 360),
  'big' => array ('y' => 640),
  'large' => array ('y' => 800),
  'huge' => array ('y' => 1024),
  'original' => array ('y' => 10240)
 );

 static function picture ($node, $request) {
  $filename = path::name($request->file);
  $path = path::directory ($request->file);
  
  if (!album::is_image ($request->file)) {console::log ('album', $request->file . " is not an image file"); return FAILED;}
  
  $p = strrpos ($filename, ".");
  $name = substr ($filename, 0, $p);
  $ext = substr ($filename, $p); //includes dot

  $newname = $path . '._' . $name . '.' . $request->size . $ext;
  $size = album::$sizes[$request->size];

  if (is_array ($size)) {
   if (!is_file ('./' . $path . $newname)) album::thumbnail ('./' . $request->file, './' . $newname, $size);
  
   $n = new node ($newname);
   
   request::passthrough ($n, $request);
  }
 }

 static function thumbnail ($src, $dst, $size) {
  $type = exif_imagetype ($src);
  $img = null;
  if ($type == IMAGETYPE_JPEG)
   $img = imagecreatefromjpeg ($src);
  else if ($type == IMAGETYPE_PNG)
   $img = imagecreatefrompng ($src);

  if ($img == null) {
   console::log ('album::thumbnail', 'could not load ' . $src);
   return FAILED;
  }

  if (!isset ($size['y'])) {
   $size['y'] = ($size['x'] / imagesx($img)) * imagesy($img);
  } else if (!isset ($size['x'])) {
   $size['x'] = ($size['y'] / imagesy($img)) * imagesx($img);
  }

  //only make a thumbnail if the size is smaller
  if (imagesx ($img) > $size['x'] or imagesy ($img) > $size['y']) {
   $thumb = imagecreatetruecolor ($size['x'], $size['y']);
   imagecopyresampled ($thumb, $img, 0, 0, 0, 0, $size['x'], $size['y'], imagesx($img), imagesy($img));
   imagejpeg ($thumb, $dst, 85);
   chmod ($dst, 0777);
   return OKAY;
  } else if (!is_file ($dst)) { //make a symlink instead
   symlink (path::name($src), $dst);
  }
 }
 
 static function is_image ($name) {
  if (eregi ("(.jpg|.jpeg|.png)$", $name))
   return true;
  
  return false;
 }

}

new template ("thumbnail", '<img align="<?=$attributes["align"]?>" src="/<?=lnk("picture.album", array("file" => $attributes["name"], "size" => $attributes["size"]))?>" />');

?>
