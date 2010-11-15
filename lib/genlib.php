<?php

  /* File:    lib/genlib.tx: assorted general functions
   * Type:    CMS function library
   * Author:  udo.schroeter@gmail.com
   * License: commercially licensed as part of the CMS package
   * Todo:    - do we really need all of those?
   * Changes: -
   */

  // start profiler time
  #$profiler_time_start = microtime();
  
  
  $profiler_last = getDefault($profiler_time_start, microtime());
  $profiler_report = array();
  
  function profile_point($text)
  {
    global $profiler_report, $profiler_time_start, $profiler_last;
    $thistime = microtime();
    $profiler_report[] = profiler_microtime_diff($thistime,$profiler_time_start).' '.
      profiler_microtime_diff($thistime,$profiler_last).' :: '.$text;
    $profiler_last = $thistime;
  }
  
	function entityLink($ds)
	{
		return('{{'.$ds['e_type'].'|'.$ds['e_url'].'|'.$ds['e_name'].'}}');
	}
	
  function isInArray(&$array, $entry)
  {
    return(array_search($entry, $array) === false); 
  }
	
	function makeHubbubTimestamp()
	{
		return(getCurrentStamp());
	}
	
	function getCurrentStamp()
	{
    return(gmdate('Y-m-d H:i:s'));		
	}
	
  function file_list($dir)
  {
    $result = array();
    if(is_dir($dir))
    {
      if($handle = opendir($dir))
      {
        while(($file = readdir($handle)) !== false)
        {
          if(substr($file, 0, 1)!='.' && $file != "Thumbs.db"/*pesky windows, images..*/)
          {
            $result[$dir.$file] = $file;
          }
        }
        closedir($handle);
      }
    }
    return($result);
  }
  
  function memOut($bytes)
  {
    return(number_format($bytes/(1024*1024), 3).' MB');
  }
  
  // append any string to the given file
  function WriteToFile($filename, $content)
  {
    if (is_array($content)) $content = getArrayDump($content);
    $open = fopen($filename, 'a+');
    fwrite($open, $content);
    fclose($open);
    chmod($filename, 0777);
  }

  // standard logging function (please log only to the log/ folder)
  // - error logs should begin with the prefix "err."
  // - warning logs should begin with the prefix "warn."
  // - notice logs should begin with the prefix "notice."
  function logToFile($filename, $content, $clearfile = false)
  {
    global $profiler_report, $profiler_time_start, $profiler_last, $config;
    if ($clearfile) @unlink($filename);
    if (is_array($content)) $content = getArrayDump($content);
		$uri = $_SERVER['REQUEST_URI'];
		if(stristr($uri, 'password') != '') $uri = '***';
    WriteToFile($filename,
      '<log client="'.
      $_SERVER['REMOTE_ADDR'].'" server="'.$_SERVER['SERVER_NAME'].'" uri="'.$uri.'" user="'.$_SESSION[$config['user.sessionid']].
      '" session="'.session_id().'" timestamp="'.date('Y-m-d H:i:s').'" exec="'.profiler_microtime_diff(microtime(), $profiler_time_start).'">'."\n".
      '  '.trim($content)."\r\n".
      "</log>\r\n\r\n");
  }	
	
  function profiler_microtime_diff($b, $a)
  {
    list($a_dec, $a_sec) = explode(" ", $a);
    list($b_dec, $b_sec) = explode(" ", $b);
    return number_format($b_sec - $a_sec + $b_dec - $a_dec, 4);
  }

  function selflinkUrl($options = array(), $unset = array())
  {
    return(selfUrl($options, $unset, array('concat' => '&'))); 
  }

  function scriptURI()
	{
		return($_ENV['SCRIPT_URI']);
	}

  // reconstruct the current URL, plus optional paradmeters
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
  
  function cr2br($str)
  {
    return(preg_replace('#\r?\n#', '<br />', $str));
  }
  
  function parse_timestamp($ts)
  {
    #format: Y-m-d H:i:s (SQL datetime format)
    $year = CutSegment('-', $ts);
    $month = CutSegment('-', $ts);
    $day = CutSegment(' ', $ts);
    $hour = CutSegment(':', $ts);
    $minute = CutSegment(':', $ts);
    $second = $ts;
    return(mktime($hour, $minute, $second, $month, $day, $year));
  }
   
  function interpretQueryString($qs)
  {
    global $config;
    $scriptDirSeg = explode('/', $_SERVER['SCRIPT_NAME']);
    for($a = 0; $a < getDefault($_ENV['dirlevels'], 1); $a++) unset($scriptDirSeg[sizeof($scriptDirSeg)-1]);
    $scriptDir = implode('/', $scriptDirSeg);
    
    if (strlen($scriptDir)>0)
    $qs = substr($qs, strlen($scriptDir));
    
    $GLOBALS['config']['page']['base'] = 'http://'.$_SERVER['HTTP_HOST'];
    if ($_SERVER['SERVER_PORT'] != 80) $GLOBALS['config']['page']['base'] .= ':'.$_SERVER['SERVER_PORT'];
    $GLOBALS['config']['page']['base'] .= $scriptDir.'/';

    if (substr($qs, 0, 1) == '/' && substr($qs, 0, 2) != '/?')
    {
      if (strpos($qs, '?') > 0)
      {
        $sc = CutSegment('?', $qs);
        parse_str($qs, $p);
        foreach($p as $k => $v)
        $_REQUEST[$k] = $v;
        $qs = $sc;      
      }
      
      $seg = explode('-', substr($qs, 1));
      $config['url_segments'] = $seg;
      $_REQUEST['controller'] = getDefault($seg[0]);
      $_REQUEST['action'] = getDefault($seg[1]);
      unset($seg[1]);
      unset($seg[0]);
      $k = null;
      foreach ($seg as $s)
      {
        if ($k == null) 
        {
          $k = $s;  
        }
        else
        {
          $_REQUEST[$k] = $s;
          $k = null;
        }
      }
    }
  }
  
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
  
  function getServerUrl()
  {
    return('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
  }  

  function logError($logfile, $msg, $level = 0)
  {
    global $config;

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

  function banner($text)
  {
    print('<div class="banner">'.$text.'</div>');    
  }

  function send_mail($to, $template, $params = array())
  {
    global $config;
    foreach($params as $k => $v) ${'_'.$k} = $v;
    ob_start();
    include($template);
    $content = ob_get_clean();
    mail($to, $subject, $content, $headers);
  }

  function actionUrl($action = '', $controller = '', $params = array())
  {
    global $config;
    $controller = getDefault($controller, $_REQUEST['controller']);
    $controller = getDefault($controller, $config['site.defaultcontroller']);
    $action = getDefault($action, $_REQUEST['action']);
    $action = getDefault($action, $config['site.defaultaction']);    
    
    if (!is_array($params))
      $params = stringParamsToArray($params);
    
    $p = '';
    if(sizeof($params) > 0) $p = '?'.http_build_query($params);
    
    // the only reason we're including site.base here is that there's
    // a bug in IE that causes the browser to ignore the BASE tag
    // when javascript document.location is used :-(
    return($config['site.base'].$controller.'-'.$action.$p);
  }
  
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

  function execTemplate($templateFileName, $params = array())
  {
    foreach ($params as $k => $v) $$k = $v;
    ob_start();
    include($templateFileName);
    return(ob_get_clean());
  }

  function getFirst($array)
	{
		foreach(func_get_args() as $a)
			if($a != null && trim($a) != '') return($a);
	}

  // if $def1 is empty, use $def2
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
  
  // returns the path to the site's root folder
  function getBasePath($pathDepthModifier = 1)
  {
    global $config;
    $basePathToScript = $_SERVER['SCRIPT_NAME'];
    $pathInfo = $_SERVER['PATH_INFO'];
    $serverName = 'http://'.$_SERVER['SERVER_NAME'];
    $serverPort = getDefault($_SERVER['SERVER_PORT'], 80);

    $pathDepthModifier = getDefault($pathDepthModifier, 1);
    $pathInfoSegments = explode('/', $pathInfo);
    $basePathSegments = explode('/', $basePathToScript);
    $pathDepth = sizeof($pathInfoSegments)-1;
    for($cnt = 0; $cnt < $pathDepthModifier; $cnt++)
      unset($basePathSegments[sizeof($basePathSegments)-1]);
    $basePath = $serverName.':'.$serverPort.implode('/', $basePathSegments).'/';

    $config['site']['basepath'] = $basePath;
    $config['cms']['urlpath'] = $basePathToScript;
    $config['site']['server'] = $serverName;
    if ($config['site.ignoreport']) $serverPort = 80;
    $config['site']['port'] = $serverPort;
    //$_REQUEST['virtual'] = substr($pathInfo, 1);

    return($config['site']['basepath']);
  }

  // cut $cake at the first occurence of $segdiv, returns the slice
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

  // like CutSegmentEx(), but doesn't carry the $found result flag
  function CutSegment($segdiv, &$cake)
  {
    return(CutSegmentEx($segdiv, $cake, $found));
  }
  
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

  function textToStringList($text)
  {
    if (trim($text) == '') return(array());
    $list = explode("\n", $text);
    return(stringsToStringlist($list));
  }
  
  function stringlistToText($stringlist)
  {
    $list = stringlistToStrings($stringlist);
    return(implode("\n", $list));
  }

  // read a hash from a text file
  function readStringListFile($filename)
  {
    global $config;
    foreach($config['context'] as $ctx)
    {
      $fileContent = @file($ctx.$filename);
      if ($fileContent != '')
        return(stringsToStringlist($fileContent));
    }
  }
	
	// get a dump string of an array
	function getArrayDump(&$array)
	{
	  ob_start();
	  print_r($array);
	  return (str_replace("\n", "\r\n", ob_get_clean()));
	}
	
	function cqrequest($url, $post, $timeout = 2, $headerMode = true)
	{
	  $ch = curl_init();
	  $resheaders = array();
	  $resbody = array();
	  curl_setopt($ch, CURLOPT_URL, $url);
	  curl_setopt($ch, CURLOPT_POST, 1);
	
	  foreach($post as $k => $v)
	    if(substr($v, 0, 1) == '@')
	      $post[$k] = '\\'.$v;
	
	  curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	  curl_setopt($ch, CURLOPT_HEADER, 1);  
	  curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); 
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);  // RETURN THE CONTENTS OF THE CALL
	
	  $result = curl_exec($ch);
	  curl_close ($ch);
	  
	  foreach(explode(chr(13), $result) as $line)
	  {
	    $line = trim($line);
	    if($line == '')
	    {
	      if($ignoreELine != true) $headerMode = false;
	      $ignoreELine = false;
	    }
	    else if ($headerMode)
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
	    {
	      $resBody .= $line.chr(13);
	    }
	  }
	  
	  return(array(
	    'result' => $resheaders['code'],
	    'headers' => $resheaders,
	    'body' => $resbody));
	}
	
  $config['site']['dateformat'] = getDefault(
    $config['site']['dateformat'], 'H:i d.m.Y');

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

  function arrayToStringList($array)
  {
    $result = '';
    if (is_array($array))
      foreach ($array as $k => $v)
      {
        $result .= $k.'='.$v."\n";
      }
    return($result);
  }

  function DissectTimeStamp($raw)
  {
    return
      substr($raw, 6, 2).'.'.  // day
      substr($raw, 4, 2).'.'.  // month
      substr($raw, 0, 4).' '.  // year
      substr($raw, 8, 2).':'.  // hour
      substr($raw, 10, 2); // minutee
  }
  
  function SqlCoolTime($raw)
  {
  	if($raw == '0000-00-00 00:00:00') return('(n/a)');
    return(ageToString(timestampToDate($raw)));
  }
  
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
  
  function ageToString($unixDate, $new = 'new', $ago = 'ago')
  {
    global $config;
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
      $result = gmdate(getDefault($config['dateformat-week'], 'l · H:i'), $unixDate);
    else if ($difference < $oneDay*365)
      $result = gmdate(getDefault($config['dateformat-year'], 'M dS · H:i'), $unixDate);
    else
      $result = date(getDefault($config['dateformat'], 'd. M Y · H:i'), $unixDate);
    return($result);
  }
	
	function get_user_timeoffset()
	{
		return(date('H')-gmdate('H'));
	}
  
  function dateToTimestamp($unixDate)
  {
    return(date('YmdHis', $unixDate));
  }
  
  function dateToString($unixDate)
  {
    global $config;
    return(date($config['site']['dateformat'], $unixDate));
  }
  
  function timeToString($unixDate)
  {
    global $config;
    return(date($config['site']['timeformat'], $unixDate));
  }
  
  function dateTimeToString($unixDate)
  {
    global $config;
    return(date($config['site']['dateformat'].' '.$config['site']['timeformat'], $unixDate));
  }
  
  function stringToDateTime($string, $formatCode = null)
  {
    global $config;
    // list of allowed placeholders
    $placeHolders = array('Y', 'm', 'd', 'H', 'i', 's', 'j', 'y', 'n');
    // the meanings of those placeholders
    $placeHoldersMeanings = array('year', 'month', 'day', 'hour', 'minute', 'second', 'day', 'year', 'month');
    // if not formatting code is given, assume standard date + time
    if ($formatCode == null)
      $formatCode = $config['site']['dateformat'].' '.$config['site']['timeformat'];
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
    {
      $$phOrder[$k] = $v;
    }
    $result = mktime($hour, $minute, $second, $month, $day, $year);
    return($result);
  }
  
  function stringToDate($string)
  {
    return(stringToDateTime($string, $config['site']['dateformat']));
  }
  
  function stringToTime($string)
  {
    return(stringToDateTime($string, $config['site']['timeformat']));
  }
  
	function safeName($raw)
	{
		return(preg_replace('/[^a-z|0-9|\_]*/','', strtolower($raw)));
	}
	
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
