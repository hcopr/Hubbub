<?

  $this->loadl10n('templates/emaillogin');
  include_once('lib/cq-forms.php');

?>
<div>
  <input type="radio" id="chkmodel" name="mode" value="x" onclick="$('#mode_login').css('display', 'block');$('#mode_signup').css('display', 'none');" checked/> 
    <label for="chkmodel">Log in to my existing account</label>
</div>
<div>
  <input type="radio" id="chkmodes" name="mode" value="n" onclick="$('#mode_login').css('display', 'none');$('#mode_signup').css('display', 'block');"/> 
    <label for="chkmodes">Create a new account</label>
</div>
<br/>
<div id="mode_login">
<?

  $lf = new CQForm('emllogin', array('ajax' => 'js_submitLogin'));
  $lf->add('string', 'email');
  $lf->add('password', 'password');
  $lf->add('submit', 'Log In');
  $lf->display();
  
?>  
</div>
<div id="mode_signup" style="display: none;">
<?

  $su = new CQForm('emlsignup', array('ajax' => 'js_submitSignup'));
  $su->add('string', 'eml');
  $su->add('passworddouble', 'pwd');
  $su->add('submit', 'Create Account');
  $su->display();
  
?>  
</div>
<br/>
<div id="emllogin_result">

</div>
<script>
  function js_submitLogin(param)
  {
	  $.post('<?= actionUrl('ajax_emaillogin', 'signin') ?>', { 'email' : param.email, 'password' : param.password }, function(data) {
		  $('#emllogin_result').html(data);
		  });
  }

  function js_submitSignup(param)
  {

  }
</script>