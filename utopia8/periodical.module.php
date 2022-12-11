<?

class periodical {
 const HOUR = 3600;
 const DAY = 86400;
 const WEEK = 604800;

 static function format ($c, $fdata, $formatter_time, $col="created", $dv=3600, $offs=null) {
  //if ($offs === null)
   //$offs = time::zoneoffset();
  $buf = '';
  $t = -1;
  while ($c->next()) {
   $coltime = time::now()->fromsql ($c->$col);
   $p = (int)(strtotime($c->$col) / $dv);
   if ($p != $t) {
    $buf .= call ($formatter_time, $c, "");
    $t = $p;
   }
   $buf .= call ($fdata, $c, "");
  }
  return $buf;
 }


}

?>
