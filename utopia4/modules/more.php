<?
// <array copy & apply>
// copy from src items listed in dst applying any functions
// mentioned in dst also
function acopy (&$Dst, $Param, $Src) {
    while (list ($K, $V) = each ($Param))
        if (isset ($Src[$K]))
            $Dst[$K] = eval ($V.'($Src[$K]);');
}

// <format node attribute>
// format a node attribute in a way such that it can be parsed as nml
function fnattr ($Req, $Name, $Attr = null) {
    if (!isset($Req[$Name]))
     return;
    if ($Attr == null)
        $Attr = $Name;
    return "<$Attr>".$Req[$Name]."</$Attr>";    
}

// <get variable>
// return a variable of certain type
function gvar ($Type, $Name) {
    if ($Type == "header") {
        $H = getallheaders ();
        return $H[$Name];
    } else if ($Type == "server")
        return $_SERVER[$Name];
    else //if ($Type == "request") {
        $V = array_merge ($_GET, $_POST, $_COOKIE);
        return $V[$Name];
        
}
?>