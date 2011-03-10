<table width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td class="smalltext" width="*"><?= l10n('openid.url') ?></td>
    <td width="50"></td>
  </tr>
  <tr>
    <td class="simple_border"><input type="text" id="openidurl" 
      value="<?= htmlspecialchars(getDefault($_SESSION['myopenidurl'])) ?>"
      placeholder="<?= l10n('openid.placeholder') ?>" style="border: none; width: 98%;" onkeypress="if(event.keyCode == 13) loginWithOpenID();"/></td>
    <td><input type="submit" value="OK" onclick="loginWithOpenID();"/></td>
  </tr>
</table>  
<script>
  
  function loginWithOpenID()
  {
    $('#signinresult').html('<img src="themes/default/ajax-loader.gif"/> '.l10n('openid.signing.in').'...');
    var openid = $('#openidurl').val();
    var mode = $('input:radio[name=signin_mode]:checked').val();
    $.post('<?= actionUrl('ajax_do', 'signin') ?>', { 'openid' : openid, 'mode' : mode, 'method' : 'openid' }, function(data) {
      $('#signinresult').html(data.html);
      if(data.url && data.url != '') document.location.href = data.url;
      }, 'json');
  }
  
</script>