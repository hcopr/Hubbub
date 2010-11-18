<?
$GLOBALS['errorhandler_ignore'] = true;

$icon = 'ktip';

$sbase = $_REQUEST['serverurl'];

/*
if(!is_callable('json_encode'))
  $msg .= '<div class="red">✘ &nbsp; please install JSON support</div>';
else
  $msg .= '<div class="green">✔ &nbsp; Installing...</div>';
*/  
$c = $_SESSION['installer'];

switch($_REQUEST['part'])
{
  case(0): {
    $msg .= '<div class="green">✔ &nbsp; Installing...</div>';
    break;
  }
  case(1): {
    $cfg = '<?php

$GLOBALS[\'config\'][\'db\'] = array(
  \'host\' => \''.$c['database']['host'].'\',
  \'user\' => \''.$c['database']['user'].'\',
  \'password\' => \''.$c['database']['password'].'\',
  \'database\' => \''.$c['database']['database'].'\',
  \'prefix\' => \'h2_\',  
  );

$GLOBALS[\'config\'][\'service\'][\'adminpw\'] = \''.$c['admin_password'].'\';
$GLOBALS[\'config\'][\'service\'][\'server\'] = \''.$c['server_base'].'\';
$GLOBALS[\'config\'][\'service\'][\'url_rewrite\'] = '.($c['enable_rewrite'] ? 'true' : 'false').';

/** Twitter Connector:
 * Uncomment this to allow login via Twitter. If you want your users to be able
 * to sign on with Twitter, go to https://api.twitter.com/oauth/authorize to 
 * get the necessary keys. You also need to enter a CALLBACK URL, which must be
 * http://yourdomain/subdirectory/signin-index (where "yourdomain" is your domain name,
 * "/subdirectory" is the name of the subdirectory where you installed Hubbub if
 * it\'s not in the root folder, and "/signin-index" must be left intact.
 **/

/*$GLOBALS[\'config\'][\'twitter\'] = array(
  \'api_key\' => \'\', 
  \'consumer_key\' => \'\',
  \'consumer_secret\' => \'\',
  );*/

/** Facebook Connector:
 * Uncomment this to allow login via Facebook. First, go to http://developers.facebook.com/setup/
 * and set up a new application account. As the site name, you can enter anything like "Hubbub on example.com".
 * Site URL will be the URL of your Hubbub instance, like for example "http://gapmind.net". Enter the App
 * keys in here:
 */

/*$GLOBALS[\'config\'][\'facebook\'] = array(
  \'app_id\' => \'\', 
  \'app_secret\' => \'\',
  );*/
?>';
    $cfgFileName = 'conf/default.php';
    if(!file_exists($cfgFileName))
    {      
      @chmod('conf', 0760);
      @WriteToFile($cfgFileName, $cfg);
      $cfgWritable = trim(implode('', file($cfgFileName))) == trim($cfg);
      if($cfgWritable)
        $msg .= '<div class="green">✔ &nbsp; Config file written</div>
          <input type="button" value="Access your Hubbub instance" onclick="document.location.href=\'/\';"/>';
      else
        $msg .= '<div class="red">✘ &nbsp; Error: could not write configuration file</div>
          <input type="button" value="Retry" onclick="document.location.href=\'?p=step3\';"/>';
    }
    else
    {
      $msg .= '<div class="red">✘ &nbsp; Configuration file already exists</div>
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