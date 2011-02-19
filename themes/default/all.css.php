<?
/**
 * Author: udo.schroeter@gmail.com
 * Project: Hubbub2
 * Description: combines CSS files for output to optimize browser loading
 */

header('content-type: text/css; charset=UTF-8');
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Expires: " . gmdate("D, d M Y H:i:s", time()+60*60*8) . " GMT");
ob_start("ob_gzhandler");

define('CSS_COL_QUANTUM', 205);

include('../../ext/jqueryui/css/flick/jquery-ui-1.8.4.custom.css');
include('default.css');

?>