<?

  h2_errorhandler(-1, 'Server-specific config file missing (domain '.$_SERVER['SERVER_NAME'].')', __FILE__, 0);

?>
<br/>
<br/>
Click here to start installing Hubbub on this server: <br/>
<input type="button" value="Install Hubbub" onclick="document.location.href='?p=step1';"/>