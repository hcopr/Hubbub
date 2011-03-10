<table width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td class="smalltext" width="*"><?= l10n('email.address') ?></td>
    <td class="smalltext" width="*"><?= l10n('email.password') ?></td>
    <td width="50"></td>
  </tr>
  <tr>
    <td class="simple_border"><input type="text" id="email" placeholder="<?= l10n('email.address.placeholder') ?>" style="border: none; width: 96%;"
      onkeypress="if(event.keyCode == 13) loginWithEmail();"/></td>
    <td class="simple_border"><input type="password" id="password" placeholder="<?= l10n('email.password.placeholder') ?>" style="border: none; width: 96%;" 
      onkeypress="if(event.keyCode == 13) loginWithEmail();"/></td>
    <td><input type="button" value="OK" onclick="loginWithEmail();"/></td>
  </tr>
</table>  
<script>
  
  function loginWithEmail()
  {
    $('#signinresult').html('<img src="themes/default/ajax-loader.gif"/> <?= l10n('email.signing.in') ?>...');
    var email = $('#email').val();
    var password = $('#password').val();
    var mode = $('input:radio[name=signin_mode]:checked').val();
    $.post('<?= actionUrl('ajax_do', 'signin') ?>', { 'email' : email, 'password' : password, 'mode' : mode, 'method' : 'email' }, function(data) {
      $('#signinresult').html(data.html);
      if(data.url && data.url != '') document.location.href = data.url;
      }, 'json');
  }
  
</script>