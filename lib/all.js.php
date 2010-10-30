<?

header('content-type: text/javascript; charset=UTF-8');
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Expires: " . gmdate("D, d M Y H:i:s", time()+60*60*4) . " GMT");
ob_start("ob_gzhandler");

include('../ext/jqueryui/js/jquery-1.4.2.min.js');
include('../ext/jqueryui/js/jquery-ui-1.8.4.custom.min.js');
include('../ext/jq/jquery.autogrow.js');
include('hubbub.js');

?>  

