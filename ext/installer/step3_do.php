<?
$GLOBALS['errorhandler_ignore'] = true;

$icon = 'ktip';

$sbase = $_REQUEST['serverurl'];


if(!is_callable('json_encode'))
  $msg .= '<div class="red">✘ &nbsp; please install JSON support</div>';
  
$c = $_SESSION['installer'];

switch($_REQUEST['part'])
{
  case(0): {
    $msg .= '<div class="green">✔ &nbsp; Installing...</div>';
    break;
  }
  case(1): {
    $cv = array();
    foreach($c['database'] as $k => $v) $cv['db_'.$k] = $v;
    foreach($c as $k => $v) if(!is_array($v)) $cv[$k] = $v;
    $cv['enable_rewrite'] = ($c['enable_rewrite'] ? 'true' : 'false');
    $cv['ping_password'] = md5($c['server_base'].time());
    $myUserName = trim(shell_exec('whoami'));
    $myUserName = getDefault($myUserName, 'root');
    $tmplFile = file_get_contents('ext/installer/example.com.php');
    foreach($cv as $k => $v) $tmplFile = str_replace('_'.$k.'_', $v, $tmplFile);    

    $cfgFileName = 'conf/default.php';
    if(!file_exists($cfgFileName))
    {      
      @chmod('conf', 0777);
      WriteToFile($cfgFileName, $tmplFile);
      $cfgWritable = trim(file_get_contents($cfgFileName)) == trim($tmplFile);
      if($cfgWritable)
        $msg .= '<div class="green">✔ &nbsp; Config file written</div>
          <br/>
          '.l10n('cron.setup').'
          <pre>* * * * * '.$myUserName.' php -f '.$GLOBALS['APP.BASEDIR'].'/cron.php > /dev/null 2>&1</pre>
          <a href="ext/installer/cronhelp.php" target="_blank">&gt; More information / help</a><br/>
          <br/>
          <br/>
          <input type="button" value="Access your Hubbub instance" onclick="document.location.href=\'/\';"/>';
      else
        $msg .= '<div class="red">✘ &nbsp; Error: could not write configuration file</div>
          <input type="button" value="Retry" onclick="document.location.href=\'?p=step3\';"/>';
    }
    else
    {
      $msg .= '<div class="red">✘ &nbsp; Configuration file already exists</div>
        <br/>
        <input type="button" value="Access your Hubbub instance" onclick="document.location.href=\'/\';"/>';
    }
    break;
  }
}

?>

<?= $msg ?>

<script>
  $("button, input:submit, input:button, a.btn").button();
  <?
  if($_REQUEST['part'] < 1)
  {
  ?>
  $.post('?p=step3_do', {'part' : <?= $_REQUEST['part']+1 ?> }, function(data)
    {
      $('#inst_log').append(data);        
    }
  );
  <?
  }
  ?>
</script>
<?
print(ob_get_clean());
die();
?>