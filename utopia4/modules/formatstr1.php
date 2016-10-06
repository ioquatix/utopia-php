<?

function mfmt ($amnt) {
 return money_format("$%.2n", $amnt);
}

function pfmt ($amnt) {
 return ($amnt * 100.0) . "%";
}

?>