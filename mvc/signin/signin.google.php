<?


?>
<br/>
<h2>Signing in with Google...</h2>
<a href="<?= actionUrl('index', 'signin') ?>" class="btn">Cancel</a>
<script>
  document.location.href = '<?= $this->model->openid->authUrl().''
	// '&openid.ns.ext1=http%3A%2F%2Fopenid.net%2Fsrv%2Fax%2F1.0&openid.ext1.mode=fetch_request&openid.ext1.type.email=http%3A%2F%2Faxschema.org%2Fcontact%2Femail&openid.ext1.type.country=http%3A%2F%2Faxschema.org%2Fcontact%2Fcountry%2Fhome&openid.ext1.type.language=http%3A%2F%2Faxschema.org%2Fpref%2Flanguage&openid.ext1.type.firstName=http%3A%2F%2Faxschema.org%2FnamePerson%2Ffirst&openid.ext1.type.lastName=http%3A%2F%2Faxschema.org%2FnamePerson%2Flast&openid.ext1.required=email%2Ccountry%2Clanguage%2CfirstName%2ClastName'
	 ?>';
</script>