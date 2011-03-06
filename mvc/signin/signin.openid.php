<?

$url = trim($this->model->openIdAuthUrl());
if($url == '') $url = actionUrl('index', 'signin', array('msg' => 'Error signing in with OpenID, please contact your administrator.'));

?>
<br/>
<h2>Signing in with OpenID...</h2>
<a href="<?= actionUrl('index', 'signin') ?>" class="btn">Cancel</a>
<script>
  document.location.href = '<?= $url ?>';
</script>