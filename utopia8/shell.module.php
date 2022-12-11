<?

class shell {
 
 function __construct ($proc) {
  $desc = array (
    0 => array ('pipe', 'r'),
    1 => array ('pipe', 'w'),
    2 => array ('file', '/tmp/php-exec-error.log', 'a')
  );
 
  $this->pipes = null;
  $this->process = proc_open ($proc, $desc, $this->pipes);
 }

 function write ($data) {
  fwrite ($this->pipes[0], $data);
 }

 function finish () {
  fclose ($this->pipes[0]);
  
  $buf = '';
  while (!feof($this->pipes[1]))
   $buf .= fgets($this->pipes[1], 1024);

  fclose ($this->pipes[1]);

  if (($ret = proc_close ($this->process)) == 0)
   return $buf;
  else {
   console::log ('execute', 'process failed to execute correctly ' . $ret);
   return FAILED;
  }
 }

 static function open ($cmd) {
  return new shell ($cmd);
 }
}

//escapeshellcmd($file)
//

?>
