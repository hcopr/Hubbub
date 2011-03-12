<?php
/**
 * Author: udo.schroeter@gmail.com
 * Project: Hubbub2
 * Description: some general convenience functions and wrappers
 */

/* inits the profiler that allows performance measurement */
$GLOBALS['profiler_last'] = getDefault($GLOBALS['profiler_start'], microtime());
define('URL_CA_SEPARATOR', '-');

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

/* this should be part of PHP actually */
function inStr($haystack, $needle)
{
  return(!(stripos(trim($haystack), trim($needle)) === false));
}

function strStartsWith($haystack, $needle)
{
  return(substr(strtolower($haystack), 0, strlen($needle)) == strtolower($needle));
}

function strEndsWith($haystack, $needle)
{
  return(substr(strtolower($haystack), -strlen($needle)) == strtolower($needle));
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
    $_SERVER['REMOTE_ADDR'].' '.$_SERVER['HTTP_HOST'].' '.$uri.' '.$_SESSION['uid'].
    ' '.session_id().' '.date('Y-m-d H:i:s').' '.profiler_microtime_diff(microtime(), $GLOBALS['profiler_start']).
    '  '.trim($content)."\r\n");
}	

/* logs an error, duh */
function logError($logfile, $msg, $level = 0)
{
  if($GLOBALS['nolog']) return;
  $trace = $msg."\r\n";
  if($logfile != 'notrace')
  {
    ob_start();
  	debug_print_backtrace();
  	$trace .= "<<<\r\n".ob_get_clean().">>>\r\n";
  }
  logToFile('log/error.log', $trace);
  
	if($level >= 10 || $logfile == 'display')
	{
    print('<div class="banner">'.$msg.'</div>');
		if($level >= 20) die();
	}
}

/* takes a query string or request_uri and parses it for parameters */
function interpretQueryString($qs)
{
  $uri = parse_url('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
  
  if($uri['query'] != '') 
  {
    parse_str($uri['query'], $_REQUEST_new);
    $_REQUEST = array_merge($_REQUEST, $_REQUEST_new);
  }
  if(substr($_SERVER['REQUEST_URI'], 0, 1) == '?')
  {
    $segments = explode('&', $uri['query']);
    $path = $segments[0];
  }
  else
  {
    $path = substr($uri['path'], 1);  
  }
  $call = explode(URL_CA_SEPARATOR, $path);
  if(!array_search($path, array('robots.txt', 'favicon.ico')) === false) return;

  $_REQUEST['controller'] = getDefault($call[0], cfg('service.defaultcontroller'));
  $_REQUEST['action'] = getDefault($call[1], cfg('service.defaultaction'));
}

/* makes an URL calling a specific controller with a specific action */
function actionUrl($action = '', $controller = '', $params = array(), $fullUrl = false)
{ 
  $p = '';
  $controller = getDefault($controller, $_REQUEST['controller']);
  $action = getDefault($action, $_REQUEST['action']);
  if (!is_array($params)) $params = stringParamsToArray($params);
  if(sizeof($params) > 0) 
  {
    // prevent cookies from appearing in the server log by accident
    foreach(array('session-key', session_id()) as $k) 
      if(isset($params[$k])) unset($params[$k]);
    $pl = http_build_query($params);
    $p = '?'.$pl;
    $pn = '&'.$pl;
  }   
  if($fullUrl)
  {
    $base = cfg('service.base');
    if(trim($base) == '') $base = 'http://'.cfg('service.server');
    if(substr($base, -1) != '/') $base .= '/';
  }
  if($GLOBALS['config']['service']['url_rewrite'])
  {
    $url = $controller.($action == 'index' ? '' : URL_CA_SEPARATOR.$action).$p; 
    return($base.$url);
  }
  else 
  {
    $url = '?'.$controller.($action == 'index' ? '' : URL_CA_SEPARATOR.$action).$pn; 
    return(getDefault($base, './').$url);
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

/* issues a HTTP redirect immediately */
function redirect($tourl)
{
	header('location: '.$tourl);
	die();
} 

/* get a dump string of an array, for debugging purposes only! */
function dumpArray($array)
{
  ob_start();
  print_r($array);
  return (str_replace("\n", "\r\n", ob_get_clean()));
}

function file_get_fromurl($url, $post = array(), $timeout = 2)
{
  $fle = cqrequest($url, $post, $timeout);
  return($fle['body']);	
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

/* convert an SQL timestamp into a human-friendly output */
function SqlCoolTime($raw)
{
	if($raw == '0000-00-00 00:00:00') return('(n/a)');
  return(ageToString(timestampToDate($raw)));
}

/* cool date */
function CoolDate($unixDate)
{
  return(ageToString($unixDate)); 
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
	return(preg_replace('/[^a-z|0-9|\_|\.]*/','', strtolower($raw)));
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
