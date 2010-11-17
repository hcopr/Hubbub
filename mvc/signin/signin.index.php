<?
$footer = array();
ob_start();
?>	
	<div class="balloonhelp">
	  <?= $this->l10n('signin.balloon') ?>
	</div>
	
	<h3>It's easy to get started on Hubbub. Connect with</h3>
	
	<div>
	  <? if($GLOBALS['config']['twitter']['api_key'] != '') { ?><a href="<?= actionUrl('twitter', 'signin') ?>"><img src="themes/default/buttons/greenbtn-twitter.png"/></a><? } ?>
    <? if($GLOBALS['config']['facebook']['app_id'] != '') { ?><a href="<?= actionUrl('fb', 'signin') ?>"><img src="themes/default/buttons/greenbtn-facebook.png"/></a><? }  ?>    
    <a href="<?= actionUrl('google', 'signin') ?>"><img src="themes/default/buttons/greenbtn-google.png"/></a>
    <a href="<?= actionUrl('yahoo', 'signin') ?>"><img src="themes/default/buttons/greenbtn-yahoo.png"/></a>
	</div>
	
	<br/>
	
	<?
	if(isset($_REQUEST['identity']) || isset($_REQUEST['oauth'])) print('<img src="themes/default/ajax-loader.gif"/>');
	?>
	<!--You can also <a href="">login with OpenID</a> or by <a href="">email</a>.-->
	
<?
$GLOBALS['content']['pane'] = ob_get_clean();

if($_SESSION['msg'])
{
  ?><div class="banner fail">
    <?= $_SESSION['msg'] ?>
  </div><?
  unset($_SESSION['msg']);
}

?>
<div id="more">
	<div class="pretentious_citation">
		<em><a href="http://en.wikipedia.org/wiki/Social_network">so·cial net·work</a>:</em>
		a social structure made up of individuals (or organizations) called "nodes", which are tied (connected) by one or more specific types of interdependency, such as friendship, kinship, common interest, financial exchange, dislike, sexual relationships, or relationships of beliefs, knowledge or prestige.
	</div>
	<div class="paragraph">
		<a href="http://hubbub.at">Hubbub</a> is an endeavour to take social networking on the internet beyond a pathological dependency on
		monolithic sites and to give control over personal data back to the user.
  </div>
	<div class="paragraph">
		<?= implode(' ', $footer) ?>
	</div>
</div>
