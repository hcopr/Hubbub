<?


?>
<br/>
<h2>Signing in with Twitter...</h2>
<a href="<?= actionUrl('index', 'signin') ?>" class="btn">Cancel</a>
<script>
	document.location.href = '<?= $this->model->oAuthSignin() ?>';
</script>