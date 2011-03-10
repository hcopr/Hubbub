<?


?>
<br/>
<h2><?= l10n('yahoo.signing.in') ?>...</h2>
<a href="<?= actionUrl('index', 'settings') ?>" class="btn"><?= l10n('cancel') ?></a>
<script>
  document.location.href = '<?= $this->model->openid->authUrl() ?>';
</script>