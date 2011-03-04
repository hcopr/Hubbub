<div class="login_pane" id="bubble_items"><?

if($_SESSION['msg'])
{
  ?><div class="banner fail">
    <?= htmlspecialchars($_SESSION['msg']) ?>
  </div><?
  unset($_SESSION['msg']);
}
?>
<? if($_REQUEST['msg'] != '') print('<div class="banner">'.htmlspecialchars($_REQUEST['msg']).'</div>'); ?>
	
	<div class="dynamic_box action_tile bubble padded_extra paragraph">
	   
    <h2 style="text-align: center">Sign in with your</h2>
    <div style="text-align: center">
      <? if($GLOBALS['config']['twitter']['api_key'] != '') { ?><a href="<?= actionUrl('twitter', 'signin') ?>"><img src="themes/default/buttons/greenbtn-twitter.png"/></a><? } ?>
      <? if($GLOBALS['config']['facebook']['app_id'] != '') { ?><a href="<?= actionUrl('fb', 'signin') ?>"><img src="themes/default/buttons/greenbtn-facebook.png"/></a><? }  ?>    
      <a href="<?= actionUrl('google', 'signin') ?>"><img src="themes/default/buttons/greenbtn-google.png"/></a>
      <a href="<?= actionUrl('yahoo', 'signin') ?>"><img src="themes/default/buttons/greenbtn-yahoo.png"/></a>
    </div>
    <br/>
    <?= l10n('signin.balloon') ?>
	
	  <? if(isset($_REQUEST['identity']) || isset($_REQUEST['oauth'])) print('<img src="themes/default/ajax-loader.gif"/>'); ?>
	</div>
	
	<div class="dynamic_box action_tile bubble padded_extra paragraph">
	  <h2 style="text-align: center">Sign in with email</h2>
	
	  <?
	  $ef = new CQForm('emailsignin');
	  $ef
	    ->add('string', 'email')
	    ->add('password', 'password')
	    ->display();	  
	  
	  ?>
	  
	</div>

	<div class="dynamic_box action_tile bubble clear padded_extra">
    <h2>About Hubbub</h2>
    <div class="paragraph">
  		<a href="http://hubbub.at">Hubbub</a> is an endeavour to take social networking on the internet beyond a pathological dependency on
  		monolithic sites and to give control over personal data back to the user.
    </div>
  </div>
  
	<div class="dynamic_box action_tile bubble clear padded_extra">
    <h2>What's New?</h2>
    <div class="paragraph">
  		<?
  		$news = $this->model->getNews();
  		if(sizeof($news['items']) == 0)
  		{
  		  ?>There are no site news right now.<? 
      }
  		else
  		{
  		  foreach($news['items'] as $item)
  		  {
  		    ?><div>
            <b><a href="<?= $item['url'] ?>" target="_blank"><?= htmlspecialchars($item['caption']) ?></a></b><br/>
            <?= htmlspecialchars($item['text']) ?> <span class="infomarker">- <?= ageToString($item['date']) ?></span>
          </div><? 
        }
      }  		
  		?>
    </div>
  </div>
  
	<div class="dynamic_box action_tile bubble clear padded_extra">
		<div class="paragraph">
		  <h2>Pretentious Citation</h2>
  		<em><a href="http://en.wikipedia.org/wiki/Social_network">so·cial net·work</a>:</em>
  		a social structure made up of individuals (or organizations) called "nodes", which are tied (connected) by one or more specific types of interdependency, such as friendship, kinship, common interest, financial exchange, dislike, sexual relationships, or relationships of beliefs, knowledge or prestige.
    </div>
  </div>
  
  
  
</div>

<script>
  $(window).load(function() {
    $('#bubble_items').masonry({});
    });
</script>