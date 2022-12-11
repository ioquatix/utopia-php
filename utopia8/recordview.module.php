<?

class recordview {

 	function __construct ($kind, $format) {
		$this->kind = $kind;
		$this->format = $format;
	}
   
   	function view ($fmt, $rs) {
     	$buf = "";
		foreach ($fmt as $col) {
			echo "<:$col>".$rs->__get($col)."</:$col>\n";
		}
	}

};
/*
	function view ($rs) {
	    $indicies = implode (":", $this->indicies);
		$alignments = implode (":", $this->alignments);
   		echo "<{$this->kind} indicies='{$indicies}' alignments='{$aligns}'>";
		
		while ($rs->next()) {
         	
		}

		echo "</{$this->kind}>";
	}

};
  */
?>
