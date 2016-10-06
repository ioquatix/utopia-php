<?

function album_dump_thumb ($src) {
 header ("Content-Type: image/jpg");
 $file = fopen ("./" . $src, "r");
 fpassthru ($file);
 flush();
}

function album_make_thumb ($src, $dst, $sa, $sm) {
 $src = "./" . $src;
 $dst = "./" . $dst;
 $type = exif_imagetype ($src);
 $img = null;
 if ($type == IMAGETYPE_JPEG)
  $img = imagecreatefromjpeg ($src);
 else if ($type == IMAGETYPE_PNG)
  $img = imagecreatefrompng ($src);
 
 if ($img == null)
  return null; //couldnt load image
 
 if ($sa == "x")
  $size = array ("x" => $sm, "y" => ($sm / imagesx($img)) * imagesy($img));
 else
  $size = array ("y" => $sm, "x" => ($sm / imagesy($img)) * imagesx($img));

 $thumb = imagecreatetruecolor ($size["x"], $size["y"]);
 imagecopyresampled ($thumb, $img, 0, 0, 0, 0, $size["x"], $size["y"], imagesx($img), imagesy($img));
 imagejpeg ($thumb, $dst, 85);
 chmod ($dst, 0664);
 return true;
}

function album_is_image ($Name) {
 if (eregi ("(.jpg|.jpeg|.png)$", $Name))
  return true;
 
 return false;
}

$o = new override_node ("thumbnail", '<?echo lnk("/picture", array("thumb" => $attributes["name"], "size" => $attributes["size"]))?>');

?>