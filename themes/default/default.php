<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml"> 
  <head>
    <title><?php echo cfg('page.title', 'unnamed').' | '.cfg('service.name', 'Hubbub') ?></title>
    <script type="text/javascript" src="lib/all.js.php"></script>   
    <link type="text/css" rel="stylesheet" href="themes/default/all.css.php"/> 
		<link rel="icon" type="image/png" href="img/hubbub-logofarb32.png"/>
  </head>
  <body>
    <? if($GLOBALS['content']['pane']) { ?><div class="springpane_back"></div><? } ?>
  	<noscript><div></div><p>Attention: Javascript is disabled on your browser. Hubbub does not work without Javascript. Please enable it before you proceed.</p></noscript>
    <div id="bgbar">

          <?
          if($_SESSION['uid'] > 0)
          {
          	?><div id="mainmenu"><?
          	foreach(explode(',', 'home,profile,friends,mail,app') as $url)
          	{
          	  $caption = l10n($url);
          	  if($url == 'profile') $caption = h2_make_excerpt(object('user')->ds['u_name'], 16);
          	  else if($url == 'home') $caption = '<span class="hubbub_logo">hubbub<sup>v2</sup></span> '.$caption;
							$class = ''; if(object('controller')->name == $url) $class = 'active';
		          ?><a class="<?= $class ?>" href="<?= actionUrl('index', $url) ?>"><?= $caption ?></a><?
            }
	          ?><a style="float: right;" href="<?= actionUrl('logout', 'signin') ?>"><img src="img/endturn.png" align="absmiddle" title="<?= $GLOBALS['l10n']['logout'] ?>"/></a>
	          </div><?
          }

          if(isset($GLOBALS['page.h1'])) print('<div id="mainmenu"><a>'.$GLOBALS['page.h1'].'</a></div>');
          ?>
              
    </div>
			
  	<? if(sizeof($GLOBALS['submenu']) > 0) { ?>
      <div id="submenu"><?
      foreach($GLOBALS['submenu'] as $item)
      {
      	$class = ''; if ($item['action'] == object('controller')->lastAction) $class = 'active';
        ?><div class="<?= $class ?>"><a href="<?= $item['url'] ?>"><?= htmlspecialchars($item['caption']) ?></a></div><?
      }
    ?><div class="submenushadow"></div><?
    if(sizeof($GLOBALS['subcat']))
    {
      ?><div class="subcat"><?
      print(implode(' ', $GLOBALS['subcat']));
      ?></div><?
    }    
    ?></div><? } ?>

		<? if($GLOBALS['content']['pane']) {
			?><div class="springpane"><?= $GLOBALS['content']['pane'] ?></div><?
		} ?>
			        
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
      function apply_style()
      {
        $("button, input:submit, input:button, a.btn").button();
      }
      apply_style();
    </script>
  </body>
  <? profile_point('page template'); ?>
  <!-- <?= print_r($GLOBALS['profiler_log']); ?> -->
</html>