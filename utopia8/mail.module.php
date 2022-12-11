<?

class chunk {
 var $headers = array (
  'Content-Type' => 'text/plain',
  'Content-Transfer-Encoding' => '7bit',
 );
 
 var $parts = array ();
  //array_push ($chunk->parts, new chunk());
 var $data = '';
 var $boundary = 'mail-boundary-marker.';

 function __construct () {
  $this->boundary .= md5(uniqid());
 }

 static function format_headers ($headers) {
  $header = '';
  foreach ($headers as $k=>$v) {
   $header .= $k.': ' . $v . "\r\n";
  }
  return $header;
 }
 
 function writeheaders () {
  return chunk::format_headers ($this->headers);
 }
 
 function writebody () {
  $buffer = trim($this->data) . "\r\n\r\n";

  if (count ($this->parts)) {
   foreach ($this->parts as $c) {
    $buffer .= '--' . $this->boundary . "\r\n";
    $buffer .= trim($c->writeheaders()) . "\r\n\r\n";
    $buffer .= trim($c->writebody()) . "\r\n\r\n";
   }
   $buffer .= '--' . $this->boundary . '--';
  }
  
  return $buffer;
 }
};

class attachment extends chunk {
 var $type;
 var $encoding;
 var $disposition;
 var $data;
 var $contentid;
  
 function __construct ($type='text/plain', $encoding='quoted-printable', $disposition='inline', $data='') {
  $this->type = $type;
  $this->encoding = $encoding;
  $this->disposition = $disposition;
  $this->data = $data;
 }
 
 function url () {
  if (!$this->contentid) $this->contentid = md5(uniqid());
  return 'cid:'.$this->contentid;
 }
 
 function writeheaders () {
  $a = array ('Content-Type' => $this->type,
   'Content-Transfer-Encoding' => $this->encoding,
   'Content-Disposition' => $this->disposition);
  if ($this->contentid) $a['Content-ID'] = $this->contentid;
  return chunk::format_headers($a);
 }
 
 static function fromfile ($file, $attached=true, $bn='', $type='') {
  if (!$type) $type = file_content_type ($file);
  if ($attached) $type .= '; name="'.($bn?$bn:basename($file)).'";';
  
  $data = chunk_split(base64_encode(file_get_contents($file)));
  return new attachment($type, 'base64', $attached?'attachment':'inline', $data);
 }
 
 function plaintext ($data) {
  $this->encoding = '7bit'; //utf-8?
  $this->data = $data;
 }
 
 function b64encode ($data) {
  $this->encoding = 'base64';
  $this->data = chunk_split (base64_encode($data));
 }
}

class mail extends chunk {
 function __construct ($to, $subject, $from=null) {
  parent::__construct();
  $this->data = 'This is a mime-encoded message.';

  $this->headers['To'] = $to;
  $this->headers['Subject'] = $subject;
  if ($from) $this->headers['From'] = $from;
  
  $this->headers['X-Mailer'] = 'PHP/'.phpversion();
  $this->headers['Content-Type'] = 'multipart/mixed; boundary="'.$this->boundary.'";';
  $this->headers['MIME-Version'] = '1.0';
 }
 
 function send () {
  //a bit of a hack ~_~
  $to = $this->headers['To'];
  $subject = $this->headers['Subject'];
  $this->headers['To'] = $this->headers['Subject'] = '';
  
  $this->headers = array_filter ($this->headers); //remove all empty headers
  
  return mail ($to, $subject, $this->writebody(), $this->writeheaders());
 }

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
};

?>
