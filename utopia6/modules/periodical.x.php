<?

define ("TIME_HOUR", 3600);
define ("TIME_DAY", TIME_HOUR * 24);
define ("TIME_WEEK", TIME_DAY * 7);

function periodical_format ($c, $formatter_data, $formatter_time, $col="created", $divisor=3600) {
$t = 0;
while ($c->next_record()) {
 $p = $c->record[$col] / $divisor
 if ((int)$p != $t) {
  call_user_func ($formatter_time, $c);
  $t = (int)$p;
 }
 call_user_func ($formatter_data, $c);
 }
}

?>