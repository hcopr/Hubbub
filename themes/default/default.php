<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml"> 
  <head>
    <title><?php echo l10n(cfg('page/title', 'unnamed'), true).' &middot; '.cfg('service/name', 'Hubbub') ?></title>
    <script type="text/javascript" src="lib/all.js.php"></script>   
    <link type="text/css" rel="stylesheet" href="themes/default/all.css.php?scheme=<?= cfg('theme/colorscheme', cfg('theme/defaultcolor', 'default')) ?>"/> 
		<link rel="icon" type="image/png" href="img/hubbub-logofarb32.png"/>
  </head>
  <body>
  	<noscript><div></div><p>Attention: Javascript is disabled on your browser. Hubbub does not work without Javascript. Please enable it before you proceed.</p></noscript>
    <div id="bgbar">

          <?
          if($_SESSION['uid'] > 0)
          {
            $uname = object('user')->ds['u_name'];
            if(trim($uname) == '') $uname = l10n('profile');
          	?><div id="mainmenu"><?
          	foreach(explode(',', cfg('service/menu')) as $url)
          	{
          	  $caption = l10n($url);
          	  if($url == 'profile') $caption = h2_make_excerpt($uname, 16);
          	  else if($url == 'home') $caption = '<span class="hubbub_logo">hubbub</span>';
							$class = ''; if(object('controller')->name == $url) $class = 'active';
		          ?><a class="<?= $class ?>" href="<?= actionUrl('index', $url) ?>"><?= $caption ?></a><?
            }
            
	          ?><a style="float: right;" href="<?= actionUrl('logout', 'signin') ?>"><img src="img/endturn.png" align="absmiddle" title="<?= $GLOBALS['l10n']['logout'] ?>"/></a><?

            foreach(explode(',', cfg('service/sysmenu')) as $url)
          	{
          		$class = ''; if(object('controller')->name == $url) $class = 'active';
		          ?><a style="float: right;" class="<?= $class ?>" href="<?= actionUrl('index', $url) ?>"><?= l10n($url) ?></a><?
            }
            
	          ?></div><?
          }

          if(isset($GLOBALS['page.h1'])) print('<div id="mainmenu"><a>'.$GLOBALS['page.h1'].'</a></div>');
          ?>
              
    </div>
			
  	<? if(sizeof($GLOBALS['submenu']) > 0) { ?>
      <div id="submenu"><?
      foreach($GLOBALS['submenu'] as $item)
      {
      	$class = ''; if ($item['action'] == object('controller')->lastAction) $class = 'active';
      	if($item['type'] == 'header')
      	{
          ?><div style="margin: 8px; margin-top: 16px; margin-bottom: 2px;" class="smalltext"><?= htmlspecialchars($item['caption']) ?></a></div><?
        }
        else
        {
          ?><div class="<?= $class ?>"><a href="<?= $item['url'] ?>"><?= htmlspecialchars($item['caption']) ?></a></div><?
        }
      }
    ?><div class="submenushadow"></div><?
    if(sizeof($GLOBALS['subcat']))
    {
      ?><div class="subcat"><?
      print(implode(' ', $GLOBALS['subcat']));
      ?></div><?
    }    
    ?></div><? } ?>
			        
    <div id="content_outer">
      <div id="content"><?php echo $GLOBALS['content.startuperrors'].$GLOBALS['content']['main'] ?></div>
    </div>

    <div class="footer">
      <div class="foottext smalltext">You are using the <b><?= $_SERVER['HTTP_HOST'] ?></b> server, which is part of the
	      <a href="http://hubbub.at" target="_blank">Hubbub</a>
	      federated social network 
	      | <a href="http://hubbub.at/faq" target="_blank">FAQ</a>
	      | <a href="http://hubbub.at/about" target="_blank">Project Info</a>
      </div>
    </div>

    <script>
      $("button, input:submit, input:button, a.btn").button(); 
      $(window).load(function() { $('.masonry_container').masonry({}); });
    </script>
  </body>
  <? profile_point('page template'); ?>
  <!-- <?= print_r($GLOBALS['profiler_log']); ?> -->
</html>