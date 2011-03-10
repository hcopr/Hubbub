<?

$url = trim($this->model->openIdAuthUrl());
if($url == '') $url = actionUrl('index', 'signin', array('msg' => l10n('openid.fail')));

?>
<br/>
<h2><?= l10n('openid.signing.in') ?>...</h2>
<a href="<?= actionUrl('index', 'signin') ?>" class="btn"><?= l10n('cancel') ?></a>
<script>
  document.location.href = '<?= $url ?>';
</script>