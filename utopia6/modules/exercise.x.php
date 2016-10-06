<?

function exercise_list ($db, $day, $t = "exercise") {
 return $db->query ("select * from $t where dayofmonth(created) = dayofmonth(now() - interval $day day)");
}

function exercise_format ($type, $amount) {
 if ($type == "running" || $type == "walking" || $type == "skating")
  return "$type for $amount km";
 else if ($type == "weighs")
  return "measured weight is $amount kg";
 else
  return "$amount $type";
}

function exercise_recent ($database, $days, $table = "exercise") {
 return $database->query ("select * from $table where created > (now() - interval $days day)");
}

?>