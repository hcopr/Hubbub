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
    include_once('lib/special-io.php');
    $_SESSION['installer']['cfg']['cron']['password'] = md5(time());
    $myUserName = trim(shell_exec('whoami'));
    $myUserName = getDefault($myUserName, 'root');
    
    $tmplFile = '<? $GLOBALS["config"] = json_decode(\''.json_format(json_encode($_SESSION['installer']['cfg'])).'\', true); ?>';
    $cfgFileName = 'conf/default.php';
    if(!file_exists($cfgFileName))
    {      
      @chmod('conf', 0777);
      WriteToFile($cfgFileName, $tmplFile);
      $cfgWritable = trim(file_get_contents($cfgFileName)) == trim($tmplFile);
      if(!$_SESSION['installer']['cfg']['cron']['remote_svc']) $cronInfo = l10n('cron.setup').'
          <pre>* * * * * '.$myUserName.' php -f '.$GLOBALS['APP.BASEDIR'].'/cron.php > /dev/null 2>&1</pre>
          <a href="ext/installer/cronhelp.php" target="_blank">&gt; More information / help</a><br/>';
      if($cfgWritable)
        $msg .= '<div class="green">✔ &nbsp; Config file written</div>
          <br/>
          '.$cronInfo.'
          <br/>
          <br/>
          <input type="button" value="Access your Hubbub instance" onclick="document.location.href=\'/\';"/>';
      else
        $msg .= '<div class="red">✘ &nbsp; Error: could not write configuration file</div>
          <input type="button" value="Retry" onclick="document.location.href=\'?p=exec\';"/>';
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
  $.post('?p=exec_do', {'part' : <?= $_REQUEST['part']+1 ?> }, function(data)
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