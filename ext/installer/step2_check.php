<?
$GLOBALS['errorhandler_ignore'] = true;

$icon = 'ktip';

$sbase = $_REQUEST['serverurl'];

$capInfo = cqrequest('http://'.$sbase.'/?p=checkenv1');
$capData = json_decode(trim($capInfo['body']), true);
$serverReachable = $capData['p'] == 'checkenv1';

$capInfo = cqrequest('http://'.$sbase.'/checkenv1');
$capData = json_decode(trim($capInfo['body']), true);
$prettyUrls = $capData['controller'] == 'checkenv1';

@unlink('.htaccess');
@WriteToFile('.htaccess', '<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase '.$_REQUEST['path'].'/
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule . index.php [L]
</IfModule>

AddHandler php5-script .php');

$_SESSION['installer']['server_base'] = $_REQUEST['serverurl'];
$_SESSION['installer']['admin_password'] = $_REQUEST['adminpw'];
$_SESSION['installer']['remote_cron_svc'] = $_REQUEST['pingsvc'];

$_SESSION['installer']['cfg']['service']['server'] = $_REQUEST['serverurl'];
$_SESSION['installer']['cfg']['service']['adminpw'] = $_REQUEST['adminpw'];
unset($_SESSION['installer']['cfg']['cron']);
$_SESSION['installer']['cfg']['ping']['remote'] = $_REQUEST['pingsvc'] == 'Y';
$_SESSION['installer']['cfg']['ping']['password'] = getDefault($_SESSION['installer']['cfg']['ping']['password'], randomHashId());
$_SESSION['installer']['cfg']['ping']['server'] = getDefault($_SESSION['installer']['cfg']['ping']['server'], 'http://ping.openfu.net');

if($_REQUEST['pingsvc'] == 'Y')
{
  newFile('conf/pingpassword', $_SESSION['installer']['cfg']['ping']['password']);
  $pingRequest = cqrequest($_SESSION['installer']['cfg']['ping']['server'], array('origin' => 'http://'.$_SESSION['installer']['server_base'].'/cron.php', 'request' => 'activate', 
    'password' => $_SESSION['installer']['cfg']['ping']['password']), 2);   
  unlink('conf/pingpassword');
  if($pingRequest['data']['result'] == 'OK')
  {
    $msg .= '<div class="green">✔ &nbsp; Joined remote ping service ('.$_SESSION['installer']['cfg']['ping']['server'].')</div>';
    $pingStatus = $pingRequest['data'];
    $pingStatus['server'] = $pingServer;
  }
  else
  {
    $pingStatus = array();
    $msg .= '<div class="red">✘ &nbsp; Could not establish a connection with ping server ('.$pingRequest['data']['reason'].')</div>';
  }
}
else
{
  $pingStatus = array();
  $msg .= '<div class="gray">✔ &nbsp; Please remember to add an entry to your crontab later</div>';
}
#h2_nv_store('ping/status', $pingStatus);

@chmod('conf', 0775);
@unlink('conf/probe');
@WriteToFile('conf/probe', 'test');
$cfgWritable = trim(implode('', file('conf/probe'))) == 'test';
@unlink('conf/probe');

$version = explode('.', phpversion());
if($version[0] > 4 && $version[1] > 2)
  $msg .= '<div class="green">✔ &nbsp; PHP version 5.3 or greater ('.phpversion().')</div>';
else
  $msg .= '<div class="gray">✘ &nbsp; please check PHP version (5.3 or greater required but '.phpversion().' installed)</div>';

if(!is_callable('json_encode'))
  $msg .= '<div class="red">✘ &nbsp; please install JSON support</div>';

if(!is_callable('curl_init'))
  $msg .= '<div class="red">✘ &nbsp; please install cURL</div>';

if($serverReachable)
  $msg .= '<div class="green">✔ &nbsp; Server address OK</div>';
else
  $msg .= '<div class="red">✘ &nbsp; Server not reachable</div>';

if(!$cfgWritable)
  $msg .= '<div class="red">✘ &nbsp; must have write access to conf/ directory</div>';

if(!file_exists('static') && !mkdir('static', 0775, true))
  $msg .= '<div class="red">✘ &nbsp; Could not create the static/ directory (insufficient rights)</div>';

if(!file_exists('log') && !mkdir('log', 0775, true))
  $msg .= '<div class="red">✘ &nbsp; Could not create the log/ directory (insufficient rights)</div>';

if($prettyUrls) {
  $msg .= '<div class="green">✔ &nbsp; "Pretty" URLs are supported</div>';
  $_SESSION['installer']['enable_rewrite'] = true;
  $_SESSION['installer']['cfg']['service']['url_rewrite'] = true;

} else {
  $msg .= '<div class="gray">✘ &nbsp;  "Pretty" URLs not supported</div>';
  $_SESSION['installer']['enable_rewrite'] = false;
  $_SESSION['installer']['cfg']['service']['url_rewrite'] = false;
}

?>
<table width="100%"><tr>
  <td valign="top" width="64"><img src="img/<?= $icon ?>.png"/></td>
  <td>&nbsp;</td>
  <td><?= $msg ?><br/><?
  
  if(stristr($msg, 'class="red"') == '')
  {
    ?><input type="button" value="Looks Good, Finish Install &gt;" onclick="document.location.href='?p=exec';"/><? 
  }
  
  ?></td>
</tr></table>

<script>
  $("button, input:submit, input:button, a.btn").button();
</script>
<?
print(ob_get_clean());
die();
?>