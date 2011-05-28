<?php
/**
 * Author: udo.schroeter@gmail.com
 * Project: Hubbub2
 * Description: some general convenience functions and wrappers
 */

/* inits the profiler that allows performance measurement */
error_reporting(E_ALL ^ E_NOTICE);
$GLOBALS['profiler_last'] = getDefault($GLOBALS['profiler_start'], microtime());
define('URL_CA_SEPARATOR', '-');

/* retrieve a config value (don't use $GLOBALS['config'] directly if possible) */
function cfg($name, $default = null)
{
	$vr = &$GLOBALS['config'];
	foreach(explode('/', $name) as $ni) 
	  if(is_array($vr)) $vr = &$vr[$ni]; else $vr = '';
	$vr = getDefault($vr, $default);
	return($vr);
}

/* returns an object from the global context (such as an instantiated model) */
function object($name)
{
	return($GLOBALS['obj'][$name]);
}

function cache_connect()
{
  if(!cfg('memcache/enabled')) return(false);
  if(isset($GLOBALS['obj']) && $GLOBALS['obj']['memcache']) return(true);
  
  $mcUrl = explode(':', cfg('memcache/server'));
  $mc = null;
  
  $GLOBALS['errorhandler_ignore'] = true;
  $mc = @memcache_pconnect($mcUrl[0], $mcUrl[1]+0);
  $GLOBALS['errorhandler_ignore'] = false;
  
  if($mc === false)
  {
    $GLOBALS['config']['memcache']['enabled'] = false;
    $GLOBALS['errors']['memcache'] = 'Could not connect to memcache server '.cfg('memcache/server');
    logError('', 'memcache: could not connect to server '.cfg('memcache/server'));
    return(false);
  }  
  
  $GLOBALS['obj']['memcache'] = $mc;
  return(true);
}

function cache_delete($key)
{
  if(!cache_connect()) return(false);
  return(memcache_delete(object('memcache'), $GLOBALS['config']['service']['server'].'/'.$key));
}

function cache_get($key)
{
  if(!cache_connect()) return(false);
  return(memcache_get(object('memcache'), $GLOBALS['config']['service']['server'].'/'.$key));
}

function cache_set($key, $value)
{
  if(!cache_connect()) return(false);
  $key = $GLOBALS['config']['service']['server'].'/'.$key;
  $op = memcache_replace(object('memcache'), $key, $value, MEMCACHE_COMPRESSED, 60*60);
  if($op == false)
    memcache_add(object('memcache'), $key, $value, MEMCACHE_COMPRESSED, 60*60);
}

function cache_region($key, $generateFunction)
{
  $out = cache_get($key);
  if($out === false)
  {
    ob_start();
    $generateFunction();
    $out = ob_get_clean();
    cache_set($key, $out);    
  }
  print($out);
}

function cache_data($key, $generateFunction)
{
  $result = cache_get($key);
  if($result === false)
  {
    $result = $generateFunction();
    cache_set($key, json_encode($result));    
  }
  else
  {
    $result = json_decode($result, true);
  }
  return($result);
}

function l10n($s, $silent = false)
{
  $lout = $GLOBALS['l10n'][$s];
  if(isset($lout)) 
    return($lout);
  else if($silent === true)
    return('');
  else
    return('['.$s.']');
}

function l10n_load($filename_base)
{
  if(isset($GLOBALS['l10n_files'][$filename_base])) return;
  $lang_try = array();
  $usr = object('user');
  if($usr != null) $lang = $usr->lang; 
  if($lang != '') $lang_try[] = $lang;
  $lang_try[] = 'en';
  foreach($lang_try as $ls)
  {
    $lang_file = $filename_base.'.'.$ls.'.cfg';
    if(file_exists($lang_file))
    {
	    foreach(stringsToStringlist(file($lang_file)) as $k => $v) 
	      $GLOBALS['l10n'][$k] = $v;
	    $GLOBALS['l10n_files'][$filename_base] = $lang_file;
	    if(cfg('l10ndebug') == true) $GLOBALS['l10n_files_last'] = $lang_file;
    }
  }
}


function randomHashId()
{
  return(md5($GLOBALS['config']['service']['salt'].time().rand(1, 100000)));
}

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
  @WriteToFile($filename,
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
  $path = '';
  if($uri['query'] != '') 
  {
    parse_str($uri['query'], $_REQUEST_new);
    $_REQUEST = array_merge($_REQUEST, $_REQUEST_new);
    $firstPart = CutSegment('&', $uri['query']);
    if(!$GLOBALS['config']['service']['url_rewrite'] && !inStr($firstPart, '=')) $path = $firstPart;
  }
  if($GLOBALS['config']['service']['url_rewrite'])
    $path = substr($uri['path'], 1);  

  $call = explode(URL_CA_SEPARATOR, $path);
  if(!array_search($path, array('robots.txt', 'favicon.ico')) === false) return;
  foreach(explode('/', $call[0]) as $ctrPart)
    if(trim($ctrPart) != '') $controllerPart = $ctrPart;

  $_REQUEST['controller'] = getDefault($controllerPart, cfg('service/defaultcontroller'));
  unset($call[0]);
  $_REQUEST['action'] = getDefault(implode(URL_CA_SEPARATOR, $call), cfg('service/defaultaction'));
  
}

/* makes an URL calling a specific controller with a specific action */
function actionUrl($action = '', $controller = '', $params = array(), $fullUrl = false)
{ 
  $p = '';
  $controller = getDefault($controller, $_REQUEST['controller']);
  if(isset($GLOBALS['subcontrollers'][$controller]))
    $controller = $GLOBALS['subcontrollers'][$controller].URL_CA_SEPARATOR.$controller;
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
    if(trim($base) == '') $base = 'http://'.cfg('service/server');
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
function getDefault($array)
{
	foreach(func_get_args() as $a)
		if($a != null && $a != '') return($a);
	return('');
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
  header('X-Redirect-From: '.$_SERVER['REQUEST_URI']);
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

function http_parse_request_ex($result, $headerMode = true)
{
  $resHeaders = array();
  $resBody = array();
  
  foreach(explode("\n", $result) as $line)
  {
    if($headerMode)
    {
      if(strStartsWith($line, 'HTTP/'))
      {
        $httpInfoRecord = explode(' ', trim($line));
        if($httpInfoRecord[1] == '100') $ignoreUntilHTTP = true;
        else 
        {
          $ignoreUntilHTTP = false;
          $resHeaders['code'] = $httpInfoRecord[1];
          $resHeaders['HTTP'] = $line;
        }
      }
      else if(trim($line) == '')
      {
        if(!$ignoreUntilHTTP) $headerMode = false;
      }
      else 
      {
        $hdr_key = trim(CutSegment(':', $line));
        $resHeaders[strtolower($hdr_key)] = trim($line); 
      }
    }
    else
    {
      $resBody[] = $line; 
    }    
  }

  $body = trim(implode("\n", $resBody));
  $data = json_decode($body, true);

  return(array(
    'result' => $resHeaders['code'],
    'headers' => $resHeaders,
    'data' => $data,
    'body' => $body));
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
  
  // this is a workaround for a parameter bug/feature that prevents params starting with an @ from working correctly
  foreach($post as $k => $v) if(substr($v, 0, 1) == '@') $post[$k] = '\\'.$v;

  if(sizeof($post)>0) curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
  if($headerMode) curl_setopt($ch, CURLOPT_HEADER, 1);  
  curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); 
  curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);  // RETURN THE CONTENTS OF THE CALL
  $result = str_replace("\r", '', curl_exec($ch));
  curl_close($ch);
  return(http_parse_request_ex($result));
}

function cqmrequest($rq_array, $post = array(), $timeout = 1, $headerMode = true, $onlyHeaders = false)
{
  $rq = array();
  $content = array();
  $active = null;
  $idx = 0;
  $multi_handler = curl_multi_init();
  
  // configure each request
  foreach($rq_array as $rparam) if(trim($rparam['url']) != '')
  {
    $idx++;
    $channel = curl_init();
    curl_setopt($channel, CURLOPT_URL, $rparam['url']);
    $combinedParams = $post;
    if(is_array($rparam['params'])) $combinedParams = array_merge($rparam['params'], $post);
    #logToFile('log/multi.req.log', 'PARAMS: '.dumpArray($combinedParams));
    if(sizeof($combinedParams)>0) 
    {
      curl_setopt($channel, CURLOPT_POST, 1); 
      curl_setopt($channel, CURLOPT_POSTFIELDS, $combinedParams);
    }
    curl_setopt($channel, CURLOPT_HEADER, 1); 
    curl_setopt($channel, CURLOPT_TIMEOUT, $timeout); 
    curl_setopt($channel, CURLOPT_RETURNTRANSFER, 1);
    curl_multi_add_handle($multi_handler, $channel);
    $rq[$idx] = array($channel, $rparam);
  }
  
  if(sizeof($rq) == 0) return(array());
  
  // execute
  do {
      $mrc = curl_multi_exec($multi_handler, $active);
  } while ($mrc == CURLM_CALL_MULTI_PERFORM);
  
  // wait for return
  while ($active && $mrc == CURLM_OK) {
    if (curl_multi_select($multi_handler) != -1) {
        do {
            $mrc = curl_multi_exec($multi_handler, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    }
  }
  
  // cleanup
  foreach($rq as $idx => $rparam)
  {
    $result = http_parse_request_ex(curl_multi_getcontent($rparam[0]));
    $result['param'] = $rparam[1];
    $content[] = $result;
    curl_multi_remove_handle($multi_handler, $channel);
  }
  
  curl_multi_close($multi_handler);
  
  return($content);  
}

/* makes a Unix timestamp human-friendly, web-trendy and supercool */
function ageToString($unixDate, $new = 'new', $ago = 'ago')
{
  if($unixDate == 0) return('-');
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
    $result = gmdate(getDefault(cfg('service/dateformat-week'), 'l · H:i'), $unixDate);
  else if ($difference < $oneDay*365)
    $result = gmdate(getDefault(cfg('service/dateformat-year'), 'M dS · H:i'), $unixDate);
  else
    $result = date(getDefault(cfg('service/dateformat'), 'd. M Y · H:i'), $unixDate);
  return($result);
}

/* gets the real offset between server time and GMT */
function get_user_timeoffset()
{
	return(date('H')-gmdate('H'));
}

function dateToString($unixDate)
{
  return(date(cfg('service/dateformat'), $unixDate));
}

function dateTimeToString($unixDate)
{
  return(date(cfg('service/dateformat').' '.cfg('service/timeformat'), $unixDate));
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
