<?
$GLOBALS['errorhandler_ignore'] = true;

$link = mysql_connect($_REQUEST['host'], $_REQUEST['user'], $_REQUEST['password']);
if($link) mysql_select_db($_REQUEST['database']);

$icon = 'database';

$_SESSION['installer']['database'] = $_REQUEST;
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
    $msg .= '<br/>There were some errors installing the tables.<br/>
      <input type="button" value="Retry" onclick="checkDBFields(\'installtables\');"/>
      <input type="button" value="Ignore and Continue &gt;" onclick="document.location.href=\'?p=step2\';"/>';
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
      <input type="button" value="Continue with Step 2 &gt;" onclick="checkDBFields(\'installtables\');"/>';
    $icon = 'database';
  }
  else
  {
    $msg = 'Could not create "'.htmlspecialchars($_REQUEST['database']).'", please check whether you have sufficient rights. Alternatively, you can also create this database with PhpMyAdmin or from the management console of your webserver.
      <input type="button" value="Retry Creating '.htmlspecialchars($_REQUEST['database']).'" onclick="checkDBFields(\'createdb\');"/>';
    $icon = 'error';    
  }
}
else switch(mysql_errno())
{
  case('2003'): 
  case('2005'): 
  {
    $msg = 'No active MySQL server found at address "'.htmlspecialchars($_REQUEST['host']).'"';
    $icon = 'daemons';
    break;
  }
  case('1045'): 
  case('1044'): 
  {
    $msg = 'Please enter the correct username and/or password';
    $icon = 'error';
    break;
  }
  case('1049'): 
  {
    $msg = '<b style="color: green;">Connection established</b>!<br/> This database does not exist yet. Do you want to create it? <br/>
      <input type="button" value="Create '.htmlspecialchars($_REQUEST['database']).'" onclick="checkDBFields(\'createdb\');"/>';
    $icon = 'kexi';
    break;
  }
  case('0'): 
  {
    $msg = '<b style="color: green;">Ready to install!</b><br/>
      <input type="button" value="Continue with Step 2 &gt;" onclick="checkDBFields(\'installtables\');"/>';
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