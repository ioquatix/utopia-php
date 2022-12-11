<?

ld ('server', 'path');

new template ('emote', '<img src="view.icon;file=<?=$request->data?>" alt="<?=$request->data?>" style="vertical-align: bottom" />');

class icon {
 static $path = 'emotes/';

 function view ($request) {
  $fn = icon::$path . $request->file;
  if (($p = path::findfile ($fn . '.gif')) == null)
   $p = path::findfile ($fn . '.png');
  if ($p != null) {
   header ("Content-Type: ". file_content_type ($p));
   $file = fopen ($p, "r");
   fpassthru ($file);
   flush();
  }
 }

}

security::raise ('call.icon.view');
request::handler ('.icon', array('security','call'));

?>
