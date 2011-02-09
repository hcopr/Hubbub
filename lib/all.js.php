<?
/**
 * Author: udo.schroeter@gmail.com
 * Project: Hubbub2
 * Description: combines JavaScript files for output to optimize browser loading
 */

header('content-type: text/javascript; charset=UTF-8');
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Expires: " . gmdate("D, d M Y H:i:s", time()+60*60*4) . " GMT");
ob_start("ob_gzhandler");

foreach(array('../ext/jq/jquery-1.5.js',
  '../ext/jqueryui/js/jquery-ui-1.8.4.custom.min.js',
  '../ext/jqueryui/masonry.min.js',
  '../ext/jq/jquery.autogrow.js',
  'hubbub.js') as $inc)
{
  ?> 
  /* <?= $inc ?> */
  <? include($inc); 
}

?>  

