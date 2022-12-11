<?

class currency {
 static $kinds = array (
  'UKD' => array ('symbol' => '&pound;', 'name' => 'British Pound'),
  'NZD' => array ('symbol' => 'NZ$', 'name' => 'New Zealand Dollar'),
  'USD' => array ('symbol' => 'US$', 'name' => 'United States Dollar')
 );

 protected $cid = '';

 function __construct ($type) {
  $this->cid = $type;
 }

 function symbol () {
  return currency::$kinds [$this->cid] ['symbol'];
 }

 function name () {
  return currency::$kinds [$this->cid] ['name'];
 }

 function id () {
  return $this->cid;
 }
}

?>
