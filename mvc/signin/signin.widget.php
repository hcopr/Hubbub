<?

if($_REQUEST['controller'] != 'signin') l10n_load('mvc/signin/l10n');

?>
  <div class="paragraph padded_extra" style="width: 550px">      
    <? 
      if($GLOBALS['config']['twitter']['enabled'] === true) $signInLinks[] = '<a class="btn" href="'.actionUrl('twitter', 'signin').'">Twitter</a>';
      if($GLOBALS['config']['facebook']['enabled'] === true) $signInLinks[] = '<a class="btn" href="'.actionUrl('fb', 'signin').'">Facebook</a>';
      $signInLinks[] = '<a class="btn" href="'.actionUrl('google', 'signin').'">Google</a>';
      $signInLinks[] = '<a class="btn" href="'.actionUrl('yahoo', 'signin').'">Yahoo</a>';
      $signInLinks[] = '<a class="btn" onclick="$(\'#signinform\').html($(\'#signinform_openid\').html());">OpenID</a>';
      $signInLinks[] = '<a class="btn" onclick="$(\'#signinform\').html($(\'#signinform_email\').html());">Email</a>';
      print(implode(' ', $signInLinks));
    ?><br/><br/>
    <div id="signinform">
      <? include('mvc/signin/signin.ajax_'.getDefault($_SESSION['load_signin'], 'email').'form.php'); ?>
    </div>
    <div id="signinform_email" style="display:none">
      <? include('mvc/signin/signin.ajax_emailform.php'); ?>
    </div>
    <div id="signinform_openid" style="display:none">
      <? include('mvc/signin/signin.ajax_openidform.php'); ?>
    </div>
    <div style="margin-bottom: 8px; margin-top: 4px;">
    <?
    if($_REQUEST['controller'] == 'signin')
    {
    ?>
      <input type="radio" name="signin_mode" value="existing" id="mode_existing" checked="true"/> <label for="mode_existing"><?= l10n('mode.signin') ?></label><br/>
      <input type="radio" name="signin_mode" value="new" id="mode_newuser"/> <label for="mode_newuser"><?= l10n('mode.create') ?></label>
    <?
    }
    else
    {
    ?>
      <input type="radio" name="signin_mode" value="new" id="mode_add" checked="true"/> <label for="mode_add"><?= l10n('mode.add') ?></label>
    <? 
    }
    ?>
    </div>
    <div id="signinresult">
      
    </div>
    
  </div>

