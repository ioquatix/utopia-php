<?

ld ('path');

class text {
 const BOTTOM_LEFT = 0;
 const BOTTOM_RIGHT = 2;
 const TOP_RIGHT = 4;
 const TOP_LEFT = 6;
 var $position_start = text::TOP_LEFT;
 var $between = 0.0; //distance between $position_start and $position_end
 var $position_end = text::TOP_RIGHT;

 function __construct ($string, $size = 10, $font = "arial", $angle = 0) {
  $this->text = $string;
  $this->size = $size;
  $this->font = path::findfile ("fonts/" . $font . '.ttf');
  $this->angle = $angle;
 }
 
 function size () {
  return imagettfbbox ($this->size, $this->angle, $this->font, $this->text);
 }
 
 function update ($text) {
  $this->text = $text;
 }
 
 function draw ($c, $x, $y, $color, $antialiased=true) {
  $bbox = $this->size();
  if ($antialiased == false) $color = $color * -1;
  imagettftext ($c->img, $this->size, $this->angle,
    $x - (($bbox[$this->position_start] * (1.0 - $this->between)) + ($bbox[$this->position_end] * $this->between)), 
    $y - (($bbox[$this->position_start + 1] * (1.0 - $this->between)) + ($bbox[$this->position_end + 1] * $this->between)), 
    $color, $this->font, $this->text);
 }
}

class canvas {
 var $width, $height;
 var $img;
 
 function color ($r = 0, $g = 0, $b = 0, $a = 0) {
  // function color ($rgb_array);
  if (is_array ($r)) {
   $rgb_array = $r;
   $r = $rgb_array[0];
   $g = $rgb_array[1];
   $b = $rgb_array[2];
   if (count($rgb_array) > 3) $a = $rgb_array[3];
  }
  if ($a == 0)
   return imagecolorallocatealpha ($this->img, $r, $g, $b, $a);
  else
   return imagecolorallocate ($this->img, $r, $g, $b);
 }
 
 static function load ($filename) {
  $image_types = array ('1' => 'imagecreatefromgif', '2' => 'imagecreatefromjpeg', '3' => 'imagecreatefrompng', 16 => 'imagecreateromxbm');
  $img = $image_types[exif_imagetype ($filename)] ($filename);
  $c = new canvas ($img);
 }
 
 static function create ($width, $height) {
  $c = new canvas (null);
  $c->width = $width; $c->height = $height;
  $c->reset();
  return $c;
 }
 
 function __construct ($img) {
  $this->img = $img;
 }
 
 function write ($format, $file) {
  $function = "image" . $format;
  return $function ($this->img, $file);
 }
 
 function passthrough ($format) {
  $function = "image" . $format;
  return $function ($this->img);
 }
 
 function reset () {
  if ($this->img) imagedestroy ($this->img);
  $this->img = imagecreatetruecolor ($this->width, $this->height);
 }
 
 function resize ($nw, $nh) {
  $c = canvas::create ($nw, $nh);
  imagecopyresampled ($c->img, $this->img, 0, 0, 0, 0, $nw, $nh, $this->width, $this->height);
  return $c;
 }
 
 function rectangle ($x1, $y1, $x2, $y2, $border, $background=null) {
  if ($background !== null)
   imagefilledrectangle ($this->img, $x1, $y1, $x2, $y2, $background);
  if ($border !== null)
   imagerectangle ($this->img, $x1, $y1, $x2, $y2, $border);
 }

 function line ($x1, $y1, $x2, $y2, $color) {
  imageline ($this->img, $x1, $y1, $x2, $y2, $color);
 }
};

?>
