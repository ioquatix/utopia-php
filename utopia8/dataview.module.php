<?

ld ('input', 'time', 'words');

class datalist {
 function __construct ($from, $indexed = false) {
  $this->list = $from;
  $this->first = true;
  reset ($this->list);
  if ($this->indexed) {
   $nlist = array();
   foreach ($this->list as $k)
    $nlist[$k] = words::proper($k);
   $this->list = $nlist;
  }
 }

 function next () {
  if ($this->first) {
   $this->first = false;
   return count ($this->list) > 0;
  }
  return next ($this->list);
 }

 function __get ($n) {
  if ($n=='id')
   return key ($this->list);
  else
   return current ($this->list);
 }
}

class viewformatter {
 var $group = 'group';
 function __construct ($title=True) {
  $this->title = $title;
 }

 function begin ($r, $fields) {
  if ($this->title)
   return '<'.$this->group.'><header>'.words::proper($fields[0]->table).'</header>';
  else
   return '<'.$this->group.'>';
 }

 function end ($r, $fields) {
  return '</'.$this->group.'>';
 }

 function item ($name, $value) {
  return '<item name="'.$name.'">'.$value.'</item>';
 }

 function viewint ($f, $v) {
  return $this->item (words::proper($f->name), $v);
 }

 function viewreal ($f, $v) {
  return $this->viewint ($f, $v);
 }

 function viewstring ($f, $v) {
  return $this->item (words::proper($f->name), input::text ($v));
 }

 function viewtext ($f, $v) {
  return $this->item (words::proper($f->name), input::process ($v));
 }

 function viewblob ($f, $v) {
  return $this->viewtext ($f, $v);
 }

 function viewdate ($f, $v) {
  return $this->item (words::proper($f->name), time::now()->fromsql ($v)->todate());
 }

 function viewdatetime ($f, $v) {
  return $this->viewdate ($f, $v);
 }
}

class editformatter extends viewformatter {
 var $group = 'group';
 
 function __construct ($title=True, $cols=array(), $cd = true, $g = 'group') {
  parent::__construct ($title);
  $this->columns = $cols;
  $this->can_delete = $cd;
  $this->group = $g;
 }

 function end ($r, $fields) {
  if ($r->id) {
   if ($this->can_delete)
    return '<bar><hidden name="id">'.$r->id.'</hidden><submit tabindex="1" name="delete">Delete</submit><submit tabindex="0" name="update">Update</submit></bar></'.$this->group.'>';
   else
    return '<bar><hidden name="id">'.$r->id.'</hidden><submit name="update">Update</submit></bar></'.$this->group.'>';
  } else
   return '<bar><hidden name="id">'.$r->id.'</hidden><submit name="new">New</submit></bar></'.$this->group.'>';
 }
 
 function __columnor ($n, $inpt, $v=null) {
  if (!array_key_exists($n, $this->columns))
   return $inpt;
  $b = '<select name="'.$n.'">';
  $row = &$this->columns[$n];
  while ($row->next()) {
   $b .= '<option value="'.$row->id.'"'.($row->id==$v?' selected="true"':'').'>'.$row->name.'</option>';
  }
  return $b . '</select>';
 }
 
 function viewint ($f, $v) {
  return $this->item (words::proper ($f->name), $this->__columnor($f->name, '<string name="'.$f->name.'">'.input::text ($v).'</string>', $v));
 }

 function viewstring ($f, $v) {
  return $this->item (words::proper ($f->name), $this->__columnor($f->name, '<string name="'.$f->name.'">'.input::text ($v).'</string>', $v));
 }
 
 function viewtext ($f, $v) {
  return $this->item (words::proper ($f->name), $this->__columnor($f->name, '<text name="'.$f->name.'">'.input::text ($v).'</text>', $v));
 }

 function viewdate ($f, $v) {
//  console::log ('zone', time::getzone());
//  console::log ('$v', $v . ' => ' . time::now()->fromsql ($v)->todate());
//  console::log ('DATE', time::now()->fromsql($v)->to(time::DATE));
  return $this->item (words::proper ($f->name), '<string name="'.$f->name.'">'.input::text (time::now()->fromsql($v)->to(time::DATE)).'</string>');
 }

 function viewdatetime ($f, $v) {
  return $this->item (words::proper ($f->name), '<string name="'.$f->name.'">'.input::text (time::now()->fromsql($v)->to(time::DATETIME)).'</string>');
 }

 function viewtime ($f, $v) {
  return $this->item (words::proper ($f->name), '<string name="'.$f->name.'">'.input::text (time::now()->fromsql($v)->to(time::TIME)).'</string>');
 }
}

class dataview {
 var $key = 'id';

 function __construct ($record, $ac=true) {
  $this->record = $record;
  $this->autocommit = $ac;
 }

 const DELETE = 1;
 const UPDATE = 2;
 const CREATE = 3;
 const NOTHING = 0;
 const INSERT = 0;

 function commit ($what) {
  switch ($what) {
   case dataview::DELETE:
    $this->record->delete();
    return OKAY;
   case dataview::UPDATE:
    $id = $this->record->id;
    $this->record->update();
    $this->record->select ($id);
    return OKAY;
   case dataview::CREATE:
    $id = $this->record->insert();
    $this->record->select ($id);
    return OKAY;
   case dataview::INSERT:
    return OKAY;
   default:
  }
 }

 function process ($request, $specific=null) {
  if ($request->has ($this->key) && is_numeric($request->__get ($this->key))) {
   if ($this->record->__get ($this->key) != $request->__get ($this->key))
    $this->record->select ($request->__get ($this->key)); 
   if ($request->has ('update')) {
    $this->record->copy ($request->all());
    if ($this->autocommit) $this->commit (dataview::UPDATE);
    return dataview::UPDATE;
   } else if ($request->has ('delete')) {
    if ($this->autocommit) $this->commit (dataview::DELETE);
    return dataview::DELETE;
   }
  } else if ($request->has ('new')) {
   $this->record->copy ($request->all());
   if ($this->autocommit) $this->commit (dataview::CREATE);
   return dataview::CREATE;
  }
  $this->record->copy ($request->all());
  return dataview::INSERT;
 }

 function view ($formatter=null, $ignore=array('id'), $special=array()) {
  if ($formatter==null) $formatter = new viewformatter();
  $fields = $this->record->fields ();
  $buffer = $formatter->begin ($this->record, $fields);
  foreach ($fields as $k => $f) {
   if (array_search ($f->name, $ignore) !== false)
    continue;
   if (($cb = $special[$f->name]) !== null) $buffer .= call ($cb, $f, $this->record);
   else $buffer .= call (array ($formatter, 'view' . $f->type), $f, $this->record->__get ($f->name));
  }
  $buffer .= $formatter->end ($this->record, $this->record);
  return $buffer;
 }

}

?>
