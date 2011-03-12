<?

include_once('lib/special-io.php');

function getConfigData()
{
  $cfgCategory = 'config-edit';
  include('conf/default.php');
  return($GLOBALS[$cfgCategory]); 
}

function setConfigData($cfg)
{
  $tmplFile = '<? $GLOBALS[$cfgCategory] = json_decode(\''.json_format(json_encode($cfg)).'\', true); ?>';
  $cfgFileName = 'conf/default.php';
  @chmod('conf', 0777);
  if(file_exists($cfgFileName)) unlink($cfgFileName);
  WriteToFile($cfgFileName, $tmplFile);
}

?>