<?php
/**
 * Author: udo.schroeter@gmail.com
 * Project: Hubbub2
 * Description: provides the CQForm object that abstracts HTML forms
 */

class CQForm
{
  function CQForm($name = 'unnamed', $fopt = array())
  {
    global $config;
    $this->name = $name;
    $this->elements = array();
    $this->presentationDir = $GLOBALS['app.basepath'].'lib/predef/';
    $this->presentationName = getDefault($config['site.formlayout'], 'tableform');
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
              $this->errors[$e['name']] = l10n('field.cannotbeempty'); 
						}
            break;
          }
          case('email'): {
            if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", 
              $value)) $this->errors[$e['name']] = l10n('field.invalidemail'); 
            break;
          }
        }
      }
    }      
    return($this->ds);
  }
  
  function add($type, $name = null, $properties = array())
  {
    if (!is_array($properties))
      $properties = stringParamsToArray($properties);
    if($properties['caption'] == '') $properties['caption'] = l10n($name, true);
    if($properties['caption'] == '') $properties['caption'] = '['.trim($name).']';
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
    return($this);
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
    return($this);
  }
}

?>
