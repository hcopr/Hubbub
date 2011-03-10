<?

$url = trim($this->model->openIdAuthUrl());
if($url == '') $url = actionUrl('index', 'signin', array('msg' => l10n('google.fail')));

?>
<br/>
<h2><?= l10n('google.signing.in') ?>...</h2>
<a href="<?= actionUrl('index', 'settings') ?>" class="btn"><?= l10n('cancel') ?></a>
<script>
  document.location.href = '<?= $url ?>';
</script>