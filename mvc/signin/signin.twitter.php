<?


?>
<br/>
<h2><?= l10n('twitter.signing.in') ?>...</h2>
<a href="<?= actionUrl('index', 'settings') ?>" class="btn"><?= l10n('cancel') ?></a>
<!--<a href="<?= $this->model->oAuthSignin() ?>">Continue</a>-->
<script>
	document.location.href = '<?= $this->model->oAuthSignin() ?>';
</script>