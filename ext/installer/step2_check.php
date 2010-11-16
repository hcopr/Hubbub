<?
$GLOBALS['errorhandler_ignore'] = true;

$icon = 'ktip';

$sbase = $_REQUEST['serverurl'];

$capInfo = cqrequest('http://'.$sbase.'?p=checkenv1');
$capData = json_decode(trim($capInfo['body']), true);
$serverReachable = $capData['p'] == 'checkenv1';

$capInfo = cqrequest('http://'.$sbase.'checkenv1');
$capData = json_decode(trim($capInfo['body']), true);
$prettyUrls = $capData['controller'] == 'checkenv1';

@unlink('.htaccess');
@WriteToFile('.htaccess', '<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase '.$_REQUEST['path'].'
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule . index.php [L]
</IfModule>');

$version = explode('.', phpversion());
if($version[0] > 4)
  $msg .= '<div class="green">✔ &nbsp; PHP version check ('.phpversion().')</div>';
else
  $msg .= '<div class="gray">✘ &nbsp; please check PHP version (5.1 or greater required but '.phpversion().' installed)</div>';

if(!is_callable('json_encode'))
  $msg .= '<div class="red">✘ &nbsp; please install JSON support</div>';

if(!is_callable('curl_init'))
  $msg .= '<div class="red">✘ &nbsp; please install cURL</div>';

if($serverReachable)
  $msg .= '<div class="green">✔ &nbsp; Server address OK</div>';
else
  $msg .= '<div class="red">✘ &nbsp; Server not reachable</div>';

if($prettyUrls)
  $msg .= '<div class="green">✔ &nbsp; "Pretty" URLs are supported</div>';
else
  $msg .= '<div class="gray">✘ &nbsp;  "Pretty" URLs not supported</div>';

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