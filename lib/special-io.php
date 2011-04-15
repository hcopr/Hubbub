<?php

/* templated mail sending func */
function send_mail($to, $template, $params = array())
{
  $srvEmail = cfg('service/email');
  if(trim($srvEmail) == '') $srvEmail = 'hubbub@'.$_SERVER['HTTP_HOST'];
  $headers = array(
    'Content-Type: text/plain; charset="utf-8"', 
    'From: '.cfg('service/server').' Hubbub Server <'.$srvEmail.'>',
    'Return-Path: '.$srvEmail,
    'Message-ID: <'.randomHashId().'-hubbub@'.$_SERVER['HTTP_HOST'].'>',
    );
  foreach ($params as $k => $v) $$k = $v;
  ob_start();
  include('templates/'.$template);
  $body = ob_get_clean();
  ini_set('mail.add_x_header', false);
  return(mail($to, $subject, $body, implode(chr(10), $headers), '-f '.$srvEmail));
}

/* executes a php page with $params as local variables (be careful!) */
function execTemplate($templateFileName, $params = array())
{
  foreach ($params as $k => $v) $$k = $v;
  ob_start();
  include($templateFileName);
  return(ob_get_clean());
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

/* retrieves a list of files from a directory */
function file_list($dir, $pattern = null, $recurse = false)
{
  $result = array();
  if(is_dir($dir) && $handle = opendir($dir))
  {
    while(($file = readdir($handle)) !== false) 
      if(substr($file, 0, 1)!='.' && $file != "Thumbs.db")
      {
        if(is_dir($dir.$file))
        {
          if($recurse === true) foreach(file_list($dir.$file.'/', $pattern, true) as $f => $sf)
            $result[$f] = $sf;
        }
        else
        {
          if($pattern == null || instr($file, $pattern))
            $result[$dir.$file] = $file;
        }
      }
    closedir($handle);
  }
  return($result);
}


/* JSON pretty print */
function json_format($json)
{
    $tab = "  ";
    $new_json = "";
    $indent_level = 0;
    $in_string = false;

    $json_obj = json_decode($json);

    if($json_obj === false)
        return false;

    $json = json_encode($json_obj);
    $len = strlen($json);

    for($c = 0; $c < $len; $c++)
    {
        $char = $json[$c];
        switch($char)
        {
            case '{':
            case '[':
                if(!$in_string)
                {
                    $new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
                    $indent_level++;
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case '}':
            case ']':
                if(!$in_string)
                {
                    $indent_level--;
                    $new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case ',':
                if(!$in_string)
                {
                    $new_json .= ",\n" . str_repeat($tab, $indent_level);
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case ':':
                if(!$in_string)
                {
                    $new_json .= ": ";
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case '"':
                if($c > 0 && $json[$c-1] != '\\')
                {
                    $in_string = !$in_string;
                }
            default:
                $new_json .= $char;
                break;                   
        }
    }

    return $new_json;
} 




?>