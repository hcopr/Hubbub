<?
$GLOBALS['errorhandler_ignore'] = true;

$link = mysql_connect($_REQUEST['host'], $_REQUEST['user'], $_REQUEST['password']);
if($link) mysql_select_db($_REQUEST['database']);

$icon = 'database';

$_SESSION['installer']['database'] = $_REQUEST;
$_SESSION['installer']['cfg']['db'] = array(
  'host' => $_REQUEST['host'],
  'user' => $_REQUEST['user'],
  'password' => $_REQUEST['password'],
  'database' => $_REQUEST['database'],
  'prefix' => 'h2_',
  );
unset($_SESSION['install']['cmd']);

if($link && $_REQUEST['cmd'] == 'installtables')
{
  $msg = '';
  foreach(explode(';', implode('', file('setup/tables.sql.txt'))) as $instruction)
  {
    if(!mysql_query($instruction) && mysql_errno() != 1065) $msg .= '<div style="color: red">'.mysql_error().' (code '.mysql_errno().')</div>';    
  }
  if($msg != '')
  {
    $msg .= '<br/>'.l10n('error.installingtables').'<br/>
      <input type="button" value="'.l10n('retry').'" onclick="checkDBFields(\'installtables\');"/>
      <input type="button" value="'.l10n('ignore.continue').' &gt;" onclick="document.location.href=\'?p=step2\';"/>';
    $icon = 'error';
  }
  else
  {
    $msg = '<script>
      document.location.href = \'?p=step2\';
      </script>'; 
    $icon = 'ksmiletris';
  }
} 
else if($link && $_REQUEST['cmd'] == 'createdb')
{
  $created = mysql_query('CREATE DATABASE `'.mysql_real_escape_string($_REQUEST['database']).'`');
  if($created)
  {
    $msg = '<b style="color: green;">Database created!</b><br/>
      <input type="button" value="'.l10n('continue.step2').' &gt;" onclick="checkDBFields(\'installtables\');"/>';
    $icon = 'database';
  }
  else
  {
    $msg = l10n('db.couldnotcreate').' "'.htmlspecialchars($_REQUEST['database']).'", '.l10n('error.dbcheck').'
      <input type="button" value="'.l10n('retry.creating').' '.htmlspecialchars($_REQUEST['database']).'" onclick="checkDBFields(\'createdb\');"/>';
    $icon = 'error';    
  }
}
else switch(mysql_errno())
{
  case('2003'): 
  case('2005'): 
  {
    $msg = l10n('error.noserver').' "'.htmlspecialchars($_REQUEST['host']).'"';
    $icon = 'daemons';
    break;
  }
  case('1045'): 
  case('1044'): 
  {
    $msg = l10n('error.username.password');
    $icon = 'error';
    break;
  }
  case('1049'): 
  {
    $msg = '<b style="color: green;">'.l10n('connection.established').'</b>!<br/> '.l10n('db.new').' <br/>
      <input type="button" value="'.l10n('create').' '.htmlspecialchars($_REQUEST['database']).'" onclick="checkDBFields(\'createdb\');"/>';
    $icon = 'kexi';
    break;
  }
  case('0'): 
  {
    $msg = '<b style="color: green;">'.l10n('ready').'</b><br/>
      <input type="button" value="'.l10n('continue.step2').' &gt;" onclick="checkDBFields(\'installtables\');"/>';
    $icon = 'database';
    break; 
  }
  default:
  {
    $msg = mysql_error().' (code '.mysql_errno().')';
    $icon = 'error';
  }
}

?>
<table width="100%"><tr>
  <td valign="top" width="64"><img src="img/<?= $icon ?>.png"/></td>
  <td>&nbsp;</td>
  <td><?= $msg ?></td>
</tr></table>
<script>
  $("button, input:submit, input:button, a.btn").button();
</script>
<?
print(ob_get_clean());
die();
?>