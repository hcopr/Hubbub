<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml"> 
  <head>
    <title><?php echo cfg('page.title', 'unnamed').' | '.cfg('service.name', 'unnamed service') ?></title>
    <script type="text/javascript" src="lib/all.js.php"></script>   
    <link type="text/css" rel="stylesheet" href="ext/jqueryui/css/flick/jquery-ui-1.8.4.custom.css"/> 
    <link type="text/css" rel="stylesheet" href="themes/default/default.css"/> 
		<link rel="icon" type="image/png" href="themes/default/logo.png"/>
    <base href="<?php echo cfg('page.base') ?>"> 
  </head>
  <body>
    <? if($GLOBALS['content']['pane'])
      {
        ?><div class="springpane_back"></div><?
      } ?>
  	<noscript><div></div><p>Attention: Javascript is disabled on your browser. Hubbub does not work without Javascript. Please enable it before you proceed.</p></noscript>
    <div id="bgbar"></div>
    <table width="1024" cellspacing="0" cellpadding="0" align="center">
      <tr>
        <td colspan="3">
          <?
          if($_SESSION['uid'] > 0)
          {
          	?><div id="mainmenu"><?
            $menu['home'] = $GLOBALS['l10n']['stream'];
            $menu['profile'] = $GLOBALS['l10n']['profile'];
            $menu['friends'] = $GLOBALS['l10n']['friends'];
            $menu['settings'] = $GLOBALS['l10n']['settings'];
						foreach($menu as $url => $caption)
						{
							$class = ''; if(object('controller')->name == $url) $class = 'active';
		          ?><a class="<?= $class ?>" href="<?= actionUrl('index', $url) ?>"><?= $caption ?></a><?
            }
	          ?><a href="<?= actionUrl('logout', 'signin') ?>"><img src="img/endturn.png" align="absmiddle" title="<?= $GLOBALS['l10n']['logout'] ?>"/></a>
	          </div><?
          }
          ?>
          <h1><a href="<?= actionUrl('index', 'home') ?>">hubbub<sup>v2</sup></a>
					  · <span><?= object('user')->ds['u_name'] ?></span>
					</h1>
				</td>
      </tr>
			<?
			if($GLOBALS['content']['pane'])
			{
				?><tr><td colspan="10"><div class="springpane"><?= $GLOBALS['content']['pane'] ?></div></td></tr><?
			}
			?>
      <tr>
      	<?
				if(sizeof($GLOBALS['submenu']) > 0)
				{
				?>
        <td width="160" valign="top">
        <div id="submenu"><?
        foreach($GLOBALS['submenu'] as $item)
        {
        	$class = ''; if ($item['action'] == object('controller')->lastAction) $class = 'active';
          ?><div class="<?= $class ?>"><a href="<?= $item['url'] ?>"><?= htmlspecialchars($item['caption']) ?></a></div><?
        }
        ?><div class="submenushadow"></div></div>
        </td>
        <?
				}
				?><td width="*" valign="top">
          <div id="content"><?php echo $GLOBALS['content']['main'] ?></div>
        </td>
        <!--<td width="200" valign="top">
          <span style="color:gray"><?php
            $ctxFile = 'mvc/'.$_REQUEST['controller'].'/contextbar.'.$_REQUEST['action'].'.php';
            if(file_exists($ctxFile)) include($ctxFile);
          ?></span>
        </td>-->
      </tr>
      <tr>
        <td colspan="3">
          <div class="foottext smalltext">You are using the <b><?= $_SERVER['SERVER_NAME'] ?></b> server, which is part of the
			      <a href="http://hubbub.at" target="_blank">Hubbub</a>
			      federated social network 
			      | <a href="http://hubbub.at/faq" target="_blank">FAQ</a>
			      | <a href="http://hubbub.at/about" target="_blank">Project Info</a>
          </div>
        </td>
      </tr>
    </table>
    <script>
      $("button, input:submit, input:button, a.btn").button();
    </script>
  </body>
</html>