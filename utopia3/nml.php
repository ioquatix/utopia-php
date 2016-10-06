<?

function nml_render ($Data, &$Node) {
    $Values = array();
    $Index = array();

    $Data = str_replace ("&", "&amp;", $Data);

	$P = xml_parser_create();
	xml_parser_set_option ($P, XML_OPTION_CASE_FOLDING, 0);
	xml_parse_into_struct ($P, "<root>" . $Data . "</root>", $Values, $Index);
	xml_parser_free($P);

	$Tmp = "";

	for ($C = 1; $C < (count($Values) - 1);)
		$Tmp .= __nml_execute_tag ($Values, $C, $Node);

	return $Tmp;
}

function __nml_render ($T, $P, &$N) {
    $Node = new node ($N->Address . "/" . $T);
    return $Node->render ($P);    
}
   
function __nml_execute_tag ($Values, &$C, &$Node) {
	if ($C >= count ($Values))
		return "";
    
	if ($Values[$C]["type"] == "complete")
		return __nml_render ($Values[$C++]["tag"], array(), $Node);
    
   	if ($Values[$C]["type"] == "cdata" || $Values[$C]["type"] == "close")
		return $Values[$C]["value"] . __nml_execute_tag ($Values, ++$C, $Node);
		
	$Template = $Values[$C]["tag"];
	if ($Values[$C]["type"] == "open")
		$Parameters = __nml_execute_body ($Values[$C]["level"], $Values, ++$C, $Node);
	else
		++$C;
	
	return __nml_render ($Template, $Parameters, $Node);
}

function __nml_execute_body ($Lvl, $Values, &$C, &$Node) {
    $Parameters = array();
    $Tag = "";

	while ($C < count ($Values)) {
		if ($Values[$C]["type"] == "cdata")
			while ($Values[$C]["type"] == "cdata") {
				$Parameters[$Tag] .= ltrim($Values[$C]["value"]);
				++$C;
			}

        if ($Values[$C]["level"] == $Lvl && $Values[$C]["type"] == "close") {
    		++$C;
			return $Parameters;
		}
		
		if ($Values[$C]["type"] == "close")
			++$C;

		if (($Values[$C]["level"] - 1) == $Lvl)
			$Tag = $Values[$C]["tag"];

		if (($Values[$C]["type"] == "complete" || $Values[$C]["type"] == "open") && $Values[$C]["level"] - 1 == $Lvl) {
			$Parameters[$Tag] .= $Values[$C]["value"];
			++$C;
		}
		
		if (($Values[$C]["type"] == "open" || $Values[$C]["type"] == "complete") && $Values[$C]["level"] == ($Lvl + 2))
			$Parameters[$Tag] .= trim(__nml_execute_tag ($Values, $C, $Node));

	}

	++$C;
    return $Parameters;
}

?>
