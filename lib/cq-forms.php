<?php

class CQForm
{
  function CQForm($name = 'unnamed', $fopt = array())
  {
    global $config;
    $this->name = $name;
    $this->elements = array();
    $this->presentationDir = $GLOBALS['app.basepath'].'lib/predef/';
    $this->presentationName = getDefault($config['site.formlayout'], 'tableform');
    $this->l10nBundle = $config['defaultl10n'];
    $this->add('start', $name, $name, array());
    $this->params = array();
    $this->params['checksum'] = md5(time());
    $this->submitted = ($_REQUEST['formsubmit'] == $name);
    $this->packStart = '<div>';
    $this->packEnd = '</div>';
    $this->packCaption = '<br/>';
    $this->defaultIdFieldname = 'id';
    $this->mandatoryMarker = ' <span class="form-mandatory">*</span>';
    $this->infoMarker = ' <a title="$" class="form-info">i</a>';
    $this->formOptions = $fopt;
  }

  function updateDataset()
  {
    if ($this->tableName != '')
    {
      $this->getData();
      DB_UpdateDataset($this->tableName, $this->ds);
    }
    else
    {
      logError('form', 'CQForm::updateDataset() unspecified database table');
    }
  }
  
  function validateDS(&$ds)
  {
    global $config;
    $errors = array();
    $controller = &$config['currentcontroller'];
    include_once($this->presentationDir.$this->presentationName.'.php');
    foreach ($this->elements as $e)
    {
      $value = $ds[$e['name']];
      switch ($e['validate'])
      {
        case('notempty'): {
          if (trim($value) == '') $errors[$e['name']] = 
            $controller->l10n('field.cannotbeempty', $this->l10nBundle); 
          break;
        }
        case('email'): {
          if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", 
            $value)) $errors[$e['name']] = 
              $controller->l10n('field.invalidemail', $this->l10nBundle); 
          break;
        }
      }
    }
    return($errors);
  }

  function getData()
  {
    global $config;
    $this->ds = array();
    $this->errors = array();
    $controller = &$config['currentcontroller'];
    include_once($this->presentationDir.$this->presentationName.'.php');
    foreach ($this->elements as $e)
    {
      $dFunction = $this->presentationName.'_'.$e['type'].'_save';
      if (is_callable($dFunction))
      {
        $value = $dFunction($e);
				switch($e['filter'])
				{
					case('safe'): {
						$value = safeName($value);
						break;
					}
				}
        $this->ds[$e['name']] = $value;
        switch ($e['validate'])
        {
          case('notempty'): {
            if (trim($value) == '') 
						{
							if(is_object($GLOBALS['currentcontroller'])) 
                $this->errors[$e['name']] = $GLOBALS['currentcontroller']->l10n('field.cannotbeempty'); 
							else
                $this->errors[$e['name']] = 'this field cannot be empty';
						}
            break;
          }
          case('email'): {
            if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", 
              $value)) $this->errors[$e['name']] = 
                $controller->l10n('field.invalidemail', $this->l10nBundle); 
            break;
          }
        }
      }
    }      
    return($this->ds);
  }
  
  function addFromDataset($ds, $table = null, $params = array())
  {
    if (!is_array($params)) 
	  $params = stringParamsToArray($params);
    $info = array();
    if ($table != null)
    {
      $ftypemap = array(
        'int' => 'string',
        'string' => 'string',
        'blob' => 'text',
        'text' => 'text',
        'date' => 'string',
        'datetime' => 'string',
        );
      $info = DB_ListFields($table);
      $keys = DB_GetKeys($table);
      $this->params[$this->defaultIdFieldname] = $ds[$keys[0]]; 
      $this->tableName = $table;
    }
	
    if (isset($params['show'])) 
      foreach(explode(';', $params['show']) as $se)
	      $visible[$se] = true;
    else
      foreach ($info as $se => $fld)
	      $visible[$se] = true;
		
    $ctr = 0;
    $caption = explode(';', $params['caption']);
    
    foreach ($visible as $k => $enabled)
    {
      $fld = $info[$k];
      $v = $ds[$k];
      $fieldType = getDefault($ftypemap[$fld['type']], 'string');
      if ($keys[0] == $k) $fieldType = 'readonly';
      $this->add($fieldType, $k, getDefault($caption[$ctr], $k));
      $this->ds[$k] = $v;
      $ctr++;      
    }
    
    foreach ($info as $k => $fld) if (!$visible[$k])
    {
      $this->add('hidden', $k, $k);
      $this->ds[$k] = $ds[$k];
    }
    
    $this->add('submit', 'submitbtn', 'Save');
  }

  function add($type, $name = null, $caption = '', $properties = array())
  {
    if (is_array($caption))
    {
      $properties = $caption;
      $caption = ''; 
    }
    if(is_object($GLOBALS['currentcontroller'])) $l10nCaption = $GLOBALS['currentcontroller']->l10n($name, '', true);
    $properties['caption'] = getDefault($caption, $l10nCaption);
    $properties['name'] = $name;
    $properties['type'] = getDefault($type, 'string');
    $elname = md5($name); $ectr = 1;
    if (isset($this->elements[$elname]))
    {
      while (isset($this->elements[$elname.$ectr]))
        $ectr++;
      $elname = $elname.$ectr;
    }
    if (isset($properties['textoptions']))
      foreach(explode(getDefault($properties['textoptions.separator'], ';'), $properties['textoptions']) as $opt) $properties['options'][trim($opt)] = $opt;
    $this->elements[$elname] = $properties;
  }

  function display($opt = array())
  {
    if ($this->hidden) return;
    if ($this->formClosed != true)
    {
      $this->params['formsubmit'] = $this->name;
      $this->params['controller'] = getDefault($this->controller, $_REQUEST['controller']);
      $this->params['action'] = getDefault($this->action, $_REQUEST['action']);
      $this->add('end', $name, $name, array('params' => $this->params));
      $this->formClosed = true;
    }
    include_once($this->presentationDir.$this->presentationName.'.php');
    $templateInitFunction = $this->presentationName.'_init';
    if (is_callable($templateInitFunction)) $templateInitFunction($this);
    foreach ($this->elements as $e)
    {
      $sessionFieldName = $this->name.'-'.$e['name'];
      $e['pure-caption'] = $e['caption'];
      $e['caption'] .= $this->packCaption;
      if ($e['validate'] == 'notempty' && isset($this->mandatoryMarker))
        $e['caption'] = $e['caption'].$this->mandatoryMarker;
      if (trim($e['info']) != '')
        $e['infomarker'] = str_replace('$', $e['info'], $this->infoMarker);
      print($this->packStart);
      print(getDefault($opt['field-start']));
      $dFunction = $this->presentationName.'_'.$e['type'];
      if ($e['sessiondefault'] == true)
        $e['default'] = getDefault($_SESSION[$sessionFieldName], $e['default']);
      $e['value'] = getDefault($this->ds[$e['name']], $e['default']);
      if ($e['sessiondefault'] == true)
        $_SESSION[$sessionFieldName] = $e['value'];
      $e['error'] = $this->errors[$e['name']];
      if (is_callable($dFunction))
        $dFunction($e, $this);
      else
        logError('form', 'CQForm: unknown form element type "'.$e['type'].'"');
      print(getDefault($opt['field-end']));
      print($this->packEnd);
    }
  }
}

?>
