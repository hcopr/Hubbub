<?php
/**
 * Author: udo.schroeter@gmail.com
 * Project: Hubbub2
 * Description: some general convenience functions and wrappers
 */

/* inits the profiler that allows performance measurement */
$GLOBALS['profiler_last'] = getDefault($GLOBALS['profiler_start'], microtime());

/* makes a commented profiler entry */ 
function profile_point($text)
{
  $thistime = microtime();
  $GLOBALS['profiler_log'][] = profiler_microtime_diff($thistime, $GLOBALS['profiler_start']).' ('.profiler_microtime_diff($thistime, $GLOBALS['profiler_last']).') :: '.$text;
  $GLOBALS['profiler_last'] = $thistime;
}

/* subtracts to profiler timestamps and returns miliseconds */
function profiler_microtime_diff(&$b, &$a)
{
  list($a_dec, $a_sec) = explode(" ", $a);
  list($b_dec, $b_sec) = explode(" ", $b);
  return number_format(1000*($b_sec - $a_sec + $b_dec - $a_dec), 3);
}

/* wrapper that searches an array for an entry */
function isInArray(&$array, $entry)
{
  return(array_search($entry, $array) === false); 
}

/* retrieves a list of files from a directory */
function file_list($dir)
{
  $result = array();
  if(is_dir($dir) && $handle = opendir($dir))
  {
    while(($file = readdir($handle)) !== false)
      if(substr($file, 0, 1)!='.' && $file != "Thumbs.db"/*pesky windows, images..*/)
        $result[$dir.$file] = $file;
    closedir($handle);
  }
  return($result);
}

/* open new file (overwrite if it already exists) */
function newFile($filename, $content)
{
  if(file_exists($filename)) unlink($filename);
  WriteToFile($filename, $content); 
}

/* append any string to the given file */
function WriteToFile($filename, $content)
{
  if (is_array($content)) $content = dumpArray($content);
  $open = fopen($filename, 'a+');
  fwrite($open, $content);
  fclose($open);
  chmod($filename, 0775);
}

// standard logging function (please log only to the log/ folder)
// - error logs should begin with the prefix "err."
// - warning logs should begin with the prefix "warn."
// - notice logs should begin with the prefix "notice."
function logToFile($filename, $content, $clearfile = false)
{
  global $profiler_report, $profiler_time_start, $profiler_last;
  if ($clearfile) @unlink($filename);
  if (is_array($content)) $content = dumpArray($content);
	$uri = $_SERVER['REQUEST_URI'];
	if(stristr($uri, 'password') != '') $uri = '***';
  WriteToFile($filename,
    '<log client="'.
    $_SERVER['REMOTE_ADDR'].'" server="'.$_SERVER['SERVER_NAME'].'" uri="'.$uri.'" user="'.$_SESSION[$config['user.sessionid']].
    '" session="'.session_id().'" timestamp="'.date('Y-m-d H:i:s').'" exec="'.profiler_microtime_diff(microtime(), $profiler_time_start).'">'."\n".
    '  '.trim($content)."\r\n".
    "</log>\r\n\r\n");
}	

/* reconstruct the current URL, plus optional paradmeters */
function selfUrl($options = array())
{
  $params = array();
	$session_name = session_name();
  
	foreach($_REQUEST as $k => $v)
	  if($k != '' && !$_SESSION[$k] && !$_COOKIE[$k] && $k != $session_name 
      && $k != 'controller' && $k != 'action'
			&& $k != 'c' && $k != 'a') $params[$k] = $v;
	foreach($options as $k => $v)
	  $params[$k] = $v;
	
  return(actionUrl($_REQUEST['action'], $_REQUEST['controller'], $params));
}

/* takes a query string or request_uri and parses it for parameters */
function interpretQueryString($qs)
{
  $qs = getDefault($_SERVER['QUERY_STRING'], substr($_SERVER['REQUEST_URI'], 1));
  while($qs[0] == '?' || $qs[0] == '/') $qs = substr($qs, 1);
  $call = explode('-', CutSegment('?', $qs));
  if(stristr($call[0], '/') != '') CutSegment('/', $call[0]);
  parse_str($qs, $rq);
  foreach($rq as $k => $v) $_REQUEST[$k] = $v;    
  $_REQUEST['controller'] = getDefault($call[0], cfg('service.defaultcontroller'));
  $_REQUEST['action'] = getDefault($call[1], cfg('service.defaultaction'));
}

/* makes hidden field form params from current URL */
function selfFormParams($options = array(), $unset = array(), $onlyStandardParams = true)
{
  global $site;
  if ($onlyStandardParams == true)
  {
    foreach ($_REQUEST as $k => $v)
    if ($k != 'PHPSESSID' && $k != '/~' && $k != 'cmd' && $v != '')
      $optList[$k] = $v;
  }
  else
  {
    $optList['site'] = $_REQUEST['site'];
    $optList['node'] = $_REQUEST['node'];
  }
  foreach ($options as $k => $v)
      $optList[$k] = $v;
  foreach ($unset as $k)
      unset($optList[$k]);
  foreach ($optList as $k => $v)
      print('<input type="hidden" name="'.$k.'" value="'.$v.'"/>');
}

/* logs an error, duh */
function logError($logfile, $msg, $level = 0)
{
  $trace = $msg;
  ob_start();
	debug_print_backtrace();
	$trace .= "\r\n\r\n".ob_get_clean();

  logToFile('log/'.$logfile.'.'.$_ENV['models']['user']->id.'.log', $trace);
  
	if($level >= 10 || $logfile == 'display')
	{
    print('<div class="banner">'.$msg.'</div>');
		if($level >= 20) die();
	}
}

/* templated mail sending function */
function send_mail($to, $template, $params = array())
{
  mail($to, $subject, execTemplate($template, $params), $headers);
}

/* makes an URL calling a specific controller with a specific action */
function actionUrl($action = '', $controller = '', $params = array())
{ 
  $p = '';
  if (!is_array($params)) $params = stringParamsToArray($params);
  $controller = getDefault($controller, $_REQUEST['controller']);
  $action = getDefault($action, $_REQUEST['action']);
  if(sizeof($params) > 0) $p = '?'.http_build_query($params);  
  if($GLOBALS['config']['service']['url_rewrite'])
  {
    return(cfg('service.base').$controller.'-'.$action.$p);
  }
  else 
  {
    return(cfg('service.base').'?'.$controller.'-'.$action.$p);
  }  
}

/* internal function needed to parse parameters in the form of "p1=bla,p2=blub" into a proper array */
function stringParamsToArray($paramStr)
{
  $result = array();
  foreach(explode(',', $paramStr) as $line)
  {
    $k = CutSegment('=', $line);
    $result[$k] = $line;	
  }
  return($result);
}

/* executes a php page with $params as local variables (be careful!) */
function execTemplate($templateFileName, $params = array())
{
  foreach ($params as $k => $v) $$k = $v;
  ob_start();
  include($templateFileName);
  return(ob_get_clean());
}

/* returns the first entry of an array (workaround to some PHP array wackyness) */
function getFirst($array)
{
	foreach(func_get_args() as $a)
		if($a != null && trim($a) != '') return($a);
}

/* if $def1 is empty, return $def2 */
function GetDefault(&$def1, $def2 = '', $zero_ok = false)
{
  //if ($def=='0' && $zero_ok) return($def1);
  if (!isset($def1) || $def1=='')
  {
    if (!isset($def2))
      return '';
    else
      return $def2;
  }
  else
  {
    return $def1;
  }
}

/* cut $cake at the first occurence of $segdiv, returns the slice */
function CutSegmentEx($segdiv, &$cake, &$found)
{
  $p = strpos($cake, $segdiv);
  if ($p === false)
  {
    $result = $cake;
    $cake = '';
    $found = false;
  }
  else
  {
    $result = substr($cake, 0, $p);
    $cake = substr($cake, $p + strlen($segdiv));
    $found = true;
  }
  return $result;
}

/* like CutSegmentEx(), but doesn't carry the $found result flag */
function CutSegment($segdiv, &$cake)
{
  return(CutSegmentEx($segdiv, $cake, $found));
}

/* converts an assoc array into a list of config strings */
function stringListToStrings($stringList)
{
  $result = array();
  foreach ($stringList as $k => $v)
  {
    if (trim($v) != '' && $k != '')
      $result[] = $k.'='.trim($v);
  }
  return($result);
}

/* converts a list of config strings into an associative array */
function stringsToStringlist($stringArray)
{
  $result = array();  
  if (is_array($stringArray))
    foreach ($stringArray as $line)
    {
      $key = CutSegment('=', $line);
      $result[$key] = trim($line);
    }
  return($result);
}

/* makes a string list from a block of contigious text */
function textToStringList($text)
{
  if (trim($text) == '') return(array());
  $list = explode("\n", $text);
  return(stringsToStringlist($list));
}

/* converts an associative array into a text block */
function stringlistToText($stringlist)
{
  $list = stringlistToStrings($stringlist);
  return(implode("\n", $list));
}

/* reads a text file and returns it as an assoc array */
function readStringListFile($filename)
{
  $fileContent = file($filename);
  if ($fileContent != '')
    return(stringsToStringlist($fileContent));
}

/* issues a HTTP redirect immediately */
function redirect($tourl)
{
	header('location: '.$tourl);
	die();
}

/* get a dump string of an array, for debugging purposes only! */
function dumpArray(&$array)
{
  ob_start();
  print_r($array);
  return (str_replace("\n", "\r\n", ob_get_clean()));
}

/* makes a GET or POST request to an URL */
function cqrequest($url, $post = array(), $timeout = 2, $headerMode = true, $onlyHeaders = false)
{
  $ch = curl_init();
  $resheaders = array();
  $resbody = array();
  curl_setopt($ch, CURLOPT_URL, $url);
  if(sizeof($post)>0) 
  {
    $onlyHeader = false;
    curl_setopt($ch, CURLOPT_POST, 1); 
  }
  if($onlyHeaders) curl_setopt($ch, CURLOPT_NOBODY, 1);
  
  // this is a workaround for a parameter bug that prevents params starting with an @ from working correctly
  foreach($post as $k => $v) if(substr($v, 0, 1) == '@') $post[$k] = '\\'.$v;

  if(sizeof($post)>0) curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
  if($headerMode) curl_setopt($ch, CURLOPT_HEADER, 1);  
  curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); 
  curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);  // RETURN THE CONTENTS OF THE CALL
  $result = curl_exec($ch);
  curl_close($ch);
    
  $resBody = '';
  foreach(explode(chr(13), $result) as $line)
  {
    $line = trim($line);
    if($line == '') $headerMode = false;
    if ($headerMode)
    {
      if(substr($line, 0, 4) == 'HTTP')
      {
        $proto = CutSegment(' ', $line);
        $resheaders['result'] = trim($line);
        $resheaders['code'] = CutSegment(' ', $line);
        if(substr($resheaders['code'], 0, 1) == '1') $ignoreELine = true;
      }
      else
      {
        $hkey = CutSegment(':', $line);
        $resheaders[$hkey] = trim($line);
      }
    }
    else
      $resBody .= $line.chr(13);
  }
  
  return(array(
    'result' => $resheaders['code'],
    'headers' => $resheaders,
    'body' => trim($resBody)));
}

function stringListToArray($txt)
{
  $result = array();
  $lines = explode("\n", $txt);
  if (sizeof($lines)>0)
    foreach ($lines as $line)
    {
      $line = trim($line);
      $k = CutSegment('=',$line);
      $result[$k] = $line;
    }
  return($result);
}

/* converts an array of strings into a string list */
function arrayToStringList($array)
{
  $result = '';
  if (is_array($array))
    foreach ($array as $k => $v)
      $result .= $k.'='.$v."\n";
  return($result);
}

/* convert an SQL timestamp into a human-friendly output */
function SqlCoolTime($raw)
{
	if($raw == '0000-00-00 00:00:00') return('(n/a)');
  return(ageToString(timestampToDate($raw)));
}

/* convert SQL timestamp into GMT Unix timestamp */
function timestampToDate($raw)
{
  $year = substr($raw, 0, 4);
  $month = substr($raw, 5, 2);
  $day =  substr($raw, 8, 2);
  $hour = substr($raw, 11, 2);
  $minute = substr($raw, 14, 2); 
  $second = substr($raw, 17, 2);
  return(gmmktime($hour, $minute, $second, $month, $day, $year)); 
}

/* makes a Unix timestamp human-friendly, web-trendy and supercool */
function ageToString($unixDate, $new = 'new', $ago = 'ago')
{
  $result = '';
  $oneMinute = 60;
  $oneHour = $oneMinute*60;
  $oneDay = $oneHour*24;
    $difference = time() - $unixDate;
  if ($difference < $oneMinute)
    $result = $new;
  else if ($difference < $oneHour)
    $result = round($difference/$oneMinute).' min '.$ago;
  else if ($difference < $oneDay)
    $result = floor($difference/$oneHour).' h '.$ago;
  else if ($difference < $oneDay*5)
    $result = gmdate(getDefault(cfg('service.dateformat-week'), 'l · H:i'), $unixDate);
  else if ($difference < $oneDay*365)
    $result = gmdate(getDefault(cfg('service.dateformat-year'), 'M dS · H:i'), $unixDate);
  else
    $result = date(getDefault(cfg('service.dateformat'), 'd. M Y · H:i'), $unixDate);
  return($result);
}

/* gets the real offset between server time and GMT */
function get_user_timeoffset()
{
	return(date('H')-gmdate('H'));
}

function dateToString($unixDate)
{
  return(date(cfg('service.dateformat'), $unixDate));
}

function timeToString($unixDate)
{
  return(date(cfg('service.timeformat'), $unixDate));
}

function dateTimeToString($unixDate)
{
  return(date(cfg('service.dateformat').' '.cfg('service.timeformat'), $unixDate));
}

/* convoluted function that tries to parse a date into a Unix timestamp */
function stringToDateTime($string, $formatCode = null)
{
  // list of allowed placeholders
  $placeHolders = array('Y', 'm', 'd', 'H', 'i', 's', 'j', 'y', 'n');
  // the meanings of those placeholders
  $placeHoldersMeanings = array('year', 'month', 'day', 'hour', 'minute', 'second', 'day', 'year', 'month');
  // if not formatting code is given, assume standard date + time
  if ($formatCode == null)
    $formatCode = cfg('service.dateformat').' '.cfg('service.timeformat');
  // determine the order of the placeholders used in the formatting string
  for ($a = 0; $a < strlen($formatCode); $a++) 
  {
    $phPositionFound = array_search(substr($formatCode, $a, 1), $placeHolders);
    if (!($phPositionFound === false))
      $phOrder[] = $placeHoldersMeanings[$phPositionFound];
  }
  // prepare the mask for sscanf 
  $formatMask = str_replace('{*1*}', '%d', str_replace($placeHolders, '{*1*}', $formatCode));
  // extract the values from the string
  $values = sscanf($string, $formatMask);
  foreach ($values as $k => $v)
    $$phOrder[$k] = $v;
  $result = mktime($hour, $minute, $second, $month, $day, $year);
  return($result);
}

/* makes an input totally safe by only allowing a-z, 0-9, and underscore (might not work correctly) */
function safeName($raw)
{
	return(preg_replace('/[^a-z|0-9|\_]*/','', strtolower($raw)));
}

/* version of strip_tags that kills attributes, since the PHP version is horribly unsafe */
function strip_tags_attributes($string,$allowtags=NULL,$allowattributes=NULL)
{
  $string = strip_tags($string,$allowtags);
  if (!is_null($allowattributes)) {
      if(!is_array($allowattributes))
          $allowattributes = explode(",",$allowattributes);
      if(is_array($allowattributes))
          $allowattributes = implode(")(?<!",$allowattributes);
      if (strlen($allowattributes) > 0)
          $allowattributes = "(?<!".$allowattributes.")";
      $string = preg_replace_callback("/<[^>]*>/i",create_function(
          '$matches',
          'return preg_replace("/ [^ =]*'.$allowattributes.'=(\"[^\"]*\"|\'[^\']*\')/i", "", $matches[0]);'   
      ),$string);
  }
  return $string;
} 

?>
