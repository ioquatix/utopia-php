<?

function album_extern_picture ($attributes) {
 $file = $attributes["file"];
 if (!album_is_image($file)) raise ("not an image file");
 
if ($attributes["size"] == "tiny") {
 if (!is_file ("./" . $file . ".tiny")) {
   album_make_thumb ($file, $file . ".tiny", "y", 80);
 }
 album_dump_thumb ($file . ".tiny");
} else if ($attributes["size"] == "small") {
 if (!is_file ("./" . $file . ".small")) {
   album_make_thumb ($file, $file . ".small", "y", 120);
 }
 album_dump_thumb ($file . ".small");
} else if ($attributes["size"] == "medium") {
 if (!is_file ("./" . $file . ".medium")) {
   album_make_thumb ($file, $file . ".medium", "y", 360);
 }
 album_dump_thumb ($file . ".medium");
} else if ($attributes["size"] == "big") {
 if (!is_file ("./" . $file . ".big")) {
  album_make_thumb ($file, $file . ".big", "x", 600);
 }
 album_dump_thumb ($file . ".big");
} else if ($attributes["size"] == "huge") {
 if (!is_file ("./" . $file . ".huge")) {
  album_make_thumb ($file, $file . ".huge", "x", 1024);
 }
 album_dump_thumb ($file . ".huge");
} else {
 album_dump_thumb ($file);
}
}

function album_dump_thumb ($src) {
 header ("Content-Type: image/jpeg");
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
