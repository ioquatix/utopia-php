<?

class user {
	var $database;
	var $user_table = "user";
	var $name = "email";
	var $password = "password";
	var $key = "id";

	function user ($d) {
		$this->database = $d;
	}

	function verify ($name, $password)
	{
		$db = new $this->database->cursor();

		$db->query ("select * from $this->user_table where $this->name = " . prt($name));

		if (!$db->next_record())
			return -1;
		
		if ($Db->Record[$this->Password] == $Password)
			return $Db->Record[$this->Key];
			
		return -2;
	}

	function change_password ($key, $password)
	{
		$db = new $this->database->cursor();

		$db->query ("update $this->user_table set $this->password = ".prt($password)." where $this->name = ". prt ($key));
	}

	function search ($key, $query = null)
	{
		$db = new $this->database;

		if ($query == null)
			$query = $this->name;

		$db->query ("select * from $this->user_table where $query = ". prt($Key));
		return $db;
	}
}

?>
