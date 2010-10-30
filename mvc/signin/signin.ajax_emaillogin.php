<?php 

if($this->idAccount['ia_user'] > 0)
{
	$this->user->loginWithId($this->idAccount['ia_user']);
	?><div class="win">
    Welcome back! <a href="<?= actionUrl('index', 'home') ?>">Click here to enter Hubbub</a>.
    <script>
    document.location.href='<? actionUrl('index', 'home') ?>';
    </script>
  </div><?php 
}
else
{
	?><div class="fail">Login error: this email+password combination is not valid. Please check your spelling. If you do 
	  not have a Hubbub account on this server yet, please use the "<a onclick="$('#chkmodes').attr('checked', 'true');$('#mode_login').css('display', 'none');$('#mode_signup').css('display', 'block');">Create a new account</a>" option.</div><?php 
}

?>