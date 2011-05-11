<?

$url = trim($this->model->openIdAuthUrl());
if($url == '') $url = actionUrl('index', 'signin', array('msg' => l10n('google.fail')));

$_SESSION['stopdebug'] = true;

?>
<br/>
<h2><?= l10n('google.signing.in') ?>...</h2>
<img src="themes/default/ajax-loader.gif" align="absmiddle"/>
<!--<a href="<?= $url ?>" class="btn"><?= l10n('signin') ?></a>-->
<a href="<?= actionUrl('index', 'settings') ?>" class="btn"><?= l10n('cancel') ?></a>
<script>
  setTimeout(function(){document.location.href = '<?= $url ?>';}, 100);
</script>
<!--<pre>
<?

$u = parse_url($url);
parse_str($u['query'], $r);
print_r($u);
print_r($r);
print_r($_SESSION);

?>
</pre>-->