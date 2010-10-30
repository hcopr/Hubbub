<?


?>
<br/>
<h2>Signing in with Yahoo...</h2>
<a href="<?= actionUrl('index', 'signin') ?>" class="btn">Cancel</a>
<script>
  document.location.href = '<?= $this->model->openid->authUrl() ?>';
</script>