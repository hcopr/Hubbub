<?

  if (substr($_SERVER['REQUEST_URI'], 0, 1) == '/')
	   interpretQueryString($_SERVER['REQUEST_URI']);    
  
  ob_start();
  
  $GLOBALS['config']['page']['title'] = 'Install';
  $GLOBALS['page.h1'] = 'Hubbub 0.2 Installer';
  l10n_load('ext/installer/l10n');
  
  $installerFN = 'ext/installer/'.getDefault(getDefault($_REQUEST['p'], $_REQUEST['controller']), 'index').'.php';
  if (file_exists($installerFN))
    include($installerFN);
  else
    redirect('./');
  
  $GLOBALS['content']['main'] = '<div class="installer">'.ob_get_clean().'</div>';
  
  header('content-type: text/html;charset=UTF-8');
  include('themes/default/default.php');
  
  die();

?>