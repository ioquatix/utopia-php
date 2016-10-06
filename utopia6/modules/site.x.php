<?

ldmod ("object");

global $site;
$site = new dataobject ("configuration", $_SERVER["SERVER_NAME"]);

function site_save () {
 global $site;
 $site->save();
}

register_finish_function (site_save);

?>