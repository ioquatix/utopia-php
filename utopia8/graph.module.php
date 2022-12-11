<?

ld ('canvas');

class linearaxis {
 var $orientation = graph::LEFT;
 var $bottom = 0.0;
 var $top = 10.0;
 var $resolution = 1.0;
 var $label = "linear axis";

 function draw ($canvas, $dim, $graph) {
  $text = new text ($this->label);
  $text->between = 0.5;
  if ($this->orientation == graph::LEFT) {
   $text->angle = 90;
   $text->position_start = text::BOTTOM_LEFT;
   $text->position_end = text::BOTTOM_RIGHT;
   $size = $text->draw ($canvas, 10, $dim['top'] + (($dim['bottom'] - $dim['top']) / 2), $canvas->color (222, 222, 222));
  } else if ($this->orientation == graph::RIGHT) {
   $text->angle = -90;
   $size = $text->draw ($canvas, $dim['right'] - 10, $dim['top'] + (($dim['bottom'] - $dim['top']) / 2), $canvas->color (222, 222, 222));
  }
 }
}

class graph {
 const LEFT = 1;
 const RIGHT = 2;
 const TOP = 3;
 const BOTTOM = 4;
 
 var $stylesheet;
 var $axes = array();
 var $datasets = array();
 var $shell;

 var $title = "Graph";

 function __construct ($width, $height, $styles) {
  $this->width = $width;
  $this->height = $height;
  $this->stylesheet = $styles;

  $this->shell = $this;
 }

 function draw ($canvas, $dim, $graph) {
  $text = new text ($this->title);
  $text->between = 0.5; //center text
  $size = $text->draw ($canvas, $canvas->width / 2, 5, $canvas->color (255, 255, 255));

  $dim['top'] += $size['height'];
  return $dim;
 }

 function render () {
  $dim = array ('top' => 0, 'left' => 0, 'right' => $this->width, 'bottom' => $this->height);

  $c = canvas::create ($this->width, $this->height);

  //draw title part
  $dim = $this->shell->draw ($c, $dim, $this);
  
  //draw axes
  foreach ($this->axes as $axis) {
   $dim = $axis->draw ($c, $dim, $this);
  }

  //draw data
  foreach ($this->datasets as $ds) {
   $ds->draw ($c, $dim, $this);
  }
 
  return $c;
 }
}

?>
