<div class="login_pane"><?
$srv = getDefault($_SERVER['HTTP_HOST'], l10n('this.server'));
$srvName = strtoupper(substr($srv, 0, 1)).substr($srv, 1);
if($_SESSION['msg'])
{
  ?><div class="banner fail">
    <?= htmlspecialchars($_SESSION['msg']) ?>
  </div><?
  unset($_SESSION['msg']);
}
$GLOBALS['page.h1'] = l10n('hubbub.server');
?>
<? if($_REQUEST['msg'] != '') print('<div class="banner">'.htmlspecialchars($_REQUEST['msg']).'</div>'); ?>
<table width="900" align="center">
  <tr>
    <td>
    
    <h2><?= $srvName ?> <?= l10n('hubbub.server') ?></h2>
    
    <div id="bubble_items">
    
      <div class="paragraph padded_extra" style="width: 500px">      
        <a href="http://hubbub.at">Hubbub</a> <?= l10n('hubbub.is') ?>
      </div>
      
      <div class="paragraph padded_extra" style="width: 500px">      
        <? 
          if($GLOBALS['config']['twitter']['enabled'] === true) $signInLinks[] = '<a href="'.actionUrl('twitter', 'signin').'">Twitter</a>';
          if($GLOBALS['config']['facebook']['enabled'] === true) $signInLinks[] = '<a href="'.actionUrl('fb', 'signin').'">Facebook</a>';
          $signInLinks[] = '<a href="'.actionUrl('google', 'signin').'">Google</a>';
          $signInLinks[] = '<a href="'.actionUrl('yahoo', 'signin').'">Yahoo</a>';
          $signInLinks[] = '<a onclick="$(\'#signinform\').html($(\'#signinform_openid\').html());">OpenID</a>';
          $signInLinks[] = '<a onclick="$(\'#signinform\').html($(\'#signinform_email\').html());">Email</a>';
          print('Use: '.implode(' &middot; ', $signInLinks));
        ?>
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
          <input type="radio" name="signin_mode" value="existing" id="mode_existing" checked="true"/> <label for="mode_existing"><?= l10n('mode.signin') ?></label><br/>
          <input type="radio" name="signin_mode" value="new" id="mode_newuser"/> <label for="mode_newuser"><?= l10n('mode.create') ?></label>
        </div>
        <div id="signinresult">
          
        </div>
      </div>
    
    </div>
    
<!--    
<div id="bubble_items">

	<div class="dynamic_box action_tile bubble padded_extra paragraph">
    <h2 style="text-align: center">Connect with...</h2>
    You can use other websites to log into your Hubbub server, just click on a button that you like:
    <br/>
    <br/>
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
	  <h2 style="text-align: center">Sign In</h2>
	  <?
	  
	  $ef = new CQForm('emailsignin', array('style-td-caption' => 'width: 80px;', 'placeholders' => 'auto'));
	  $ef
	    ->add('string', 'email')
	    ->add('password', 'password')
	    ->add('submit', 'signin')
	    ->display();	  
	   
	  ?>
	  
	</div>

	<div class="dynamic_box action_tile bubble padded_extra paragraph">
	  <h2 style="text-align: center">New Account</h2>
	  Register for a new account on this server:<br/>
	  <br/>
	  <?
	  
	  $ef = new CQForm('emailsignin', array('style-td-caption' => 'width: 80px;', 'placeholders' => 'auto'));
	  $ef
	    ->add('string', 'email')
	    ->add('submit', 'signin')
	    ->display();	  
	  
	  ?>
	  
	</div>

 	<div class="dynamic_box action_tile bubble clear padded_extra">
    <h2><?= strtoupper(substr($srv, 0, 1)).substr($srv, 1) ?></h2>
    <div class="paragraph">
    <? include('mvc/signin/server.'.cfg('service.type', 'private').'.php'); ?>
    </div>
  </div>

	<div class="dynamic_box action_tile bubble clear padded_extra">
    <h2>About Hubbub</h2>
    <div class="paragraph">
  		<a href="http://hubbub.at">Hubbub</a> is an endeavour to take social networking on the internet beyond a pathological dependency on
  		monolithic sites and to give control over personal data back to the user.
    </div>
  </div>
  
  		<?
  		$news = $this->model->getNews();
  		if(sizeof($news['items']) == 0)
  		{

      }
  		else
  		{
  		  foreach($news['items'] as $item)
  		  {
  		    ?><div class="dynamic_box action_tile bubble clear padded_extra">
            <h2><a href="<?= $item['url'] ?>" target="_blank"><?= htmlspecialchars($item['caption']) ?></a></h2>
            <div class="paragraph">
              <?= htmlspecialchars($item['text']) ?> <span class="infomarker">- <?= ageToString($item['date']) ?></span>
            </div>
          </div><? 
        }
      }  		
  		?>
  
	<div class="dynamic_box action_tile bubble clear padded_extra">
    <h2>Take the Tour</h2>
    <div class="paragraph">
      Welcome and thanks for trying out Hubbub, the open social network. 
      You can jump right in if you create an account on this or any other Hubbub server.
      If you're unsure what to expect, take a few moments and enjoy the guided tour to get up to speed.<br/>
      <a href="http://hubbub-project.org/tour" target="_blank">
        <img src="img/pointlesspics/frankfurt_skyline.jpg"/>
        Take the tour now &gt;
      </a>
    </div>
  </div>  
</div>-->
        
    </td>
  </tr>
</table>	
</div>

<script>
  $(window).load(function() {
    $('#bubble_items').masonry({});
    });
</script>