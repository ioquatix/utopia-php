<?

$debg = 0;
function debug ($txt = '') {
 global $debg;
 echo "*** mark $debg -- $txt<br>";
 ++$debg;
}

?>