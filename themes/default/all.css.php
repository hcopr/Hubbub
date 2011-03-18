<?
/**
 * Author: udo.schroeter@gmail.com
 * Project: Hubbub2
 * Description: combines CSS files for output to optimize browser loading
 */

header('content-type: text/css; charset=UTF-8');
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Expires: " . gmdate("D, d M Y H:i:s", time()+60*60*4) . " GMT");
ob_start("ob_gzhandler");

define('CSS_COL_QUANTUM', 205);

function dechex2($a)
{
  $result = '';
  if($a > 255) $a = 255; else if($a < 0) $a = 0;
  $result = dechex($a);
  if(strlen($result) < 2) $result = '0'.$result;
  return($result);
}

function css_color($b, $lightenBy = 0)
{
  $result = '';
  foreach($b as $c)
    $result .= dechex2($c + $lightenBy);
  return('#'.$result);    
}

function css_gradient($c1, $c2, $defaultColor = null)
{
  if($defaultColor == null) $defaultColor = $c1;
  return("
  background: $defaultColor;
  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='$c2', endColorstr='$c1'); 
  background: -webkit-gradient(linear, left top, left bottom, from($c2), to($c1)); 
  background: -moz-linear-gradient(top,  $c2,  $c1); ");
}

$defaultScheme = 'default';

$colorSchemes = array(
  'default'  => array('basecolor' => array(0x00, 0x40, 0xA0), 'linkcolor' => 0),
  'green'    => array('basecolor' => array(0x00, 0x90, 0x00), 'linkcolor' => -50),
  'orange'   => array('basecolor' => array(0xFF, 0x60, 0x00), 'linkcolor' => -50),
  'gray'     => array('basecolor' => array(0x80, 0x80, 0x80), 'linkcolor' => -50),
  'pink'     => array('basecolor' => array(0xCC, 0x50, 0xCC), 'linkcolor' => -50),
  'graphite' => array('basecolor' => array(0x60, 0x70, 0x90), 'linkcolor' => -50),
  'blue'     => array('basecolor' => array(0x00, 0x40, 0xA0), 'linkcolor' => 0),
  );

if(!isset($_REQUEST['scheme'])) $_REQUEST['scheme'] = $defaultScheme;
if(!isset($colorSchemes[$_REQUEST['scheme']])) $_REQUEST['scheme'] = $defaultScheme;

$b = $colorSchemes[$_REQUEST['scheme']]['basecolor'];

$baseColor = css_color($b, 0);
$lighterColor = css_color($b, +30);
$lighterColorHighlight = css_color($b, +70);
$veryLightColor = css_color($b, +180);
$darkerColor = css_color($b, -50);
$linkColor = css_color($b, $colorSchemes[$_REQUEST['scheme']]['linkcolor']);
$lightGrayBackground = '#f6f6f6';
$mouseOver = css_gradient($lighterColor, $lighterColorHighlight);
$btnBackground = css_gradient('#e0e0e0', '#ffffff', $lightGrayBackground);

include('../../ext/jqueryui/css/flick/jquery-ui-1.8.4.custom.css');
include('default.css');


?>