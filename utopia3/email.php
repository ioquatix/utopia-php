<?
function email_check_address ($Addr) {
	if ( !eregi("^[a-z0-9]+([_\\.-][a-z0-9]+)*"
	          . "@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$",
		    $Addr) )
	{
		return -1;
	}
	else
	{
		$Domain = explode ("@", $Addr);
		$Domain = $Domain[1] . ".";
		
		if (gethostbyname($Domain) == $Domain)
		{
			return -2;
		}
	}
	return 0;
}

function email_send ($To, $From, $ReplyTo, $Subject, $Msg) {
	return mail ($To, 
			 $Subject, $Message,
			   "From: $From\r\n"
			 . "Reply-To: $ReplyTo\r\n"
			 . "X-Mailer: PHP/" . phpversion());
}
?>