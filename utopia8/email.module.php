<?


class email {
 static function check_address ($a) {
  if ( !eregi("^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$", $a) )
   return -1;
  else {
   $domain = explode ("@", $a);
   $domain = $domain[1] . ".";
		
   if (gethostbyname($Domain) == $Domain)
    return 1;
  }
  return 0;
 }

 static function sendto ($to, $from, $replyto, $subject, $msg) {
  return mail ($to, $subject, $msg, ''
			 . ($from!='' ? "From: $from\r\n" : '')
			 . ($replyto!='' ? "Reply-To: $replyto\r\n" : '')
			 . "X-Mailer: PHP/" . phpversion());
 }
}
?>
