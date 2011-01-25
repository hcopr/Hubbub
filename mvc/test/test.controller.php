<?php
/**
 * a set of test routines that can be run automatically
 **/

class TestController extends HubbubController
{
	function __init()
	{
    access_policy('auth');
    $this->menu = $this->makeMenu('index,message,vis,code');
		$this->invokeModel('friends');
		$this->invokeModel('profile');
		$this->invokeModel('endpoint');
	}
	
	function index() { }
	
	function entity() { }
	
	function message() { }
  
  function vis() { }
  
  function code() { }
  
	function clearAllTables()
	{
		$this->skipView = true;
		foreach(explode(',', 'auditlog,connections,entities,idaccounts,index,index_servers,messages,nvstore,servers,users,votes') as $table)
		  DB_Update('TRUNCATE '.getTableName($table));
	}
	
}

// logs an individual test's result
function tlog($condition, $context, $successMsg, $failMsg)
{
  global $testArray;
  ob_start();
	if($condition)
  {
    ?><div style="color: green">✔ <?= $context.' - '.$successMsg ?></div><?
  }
	else
  {
    ?><div style="color: red; font-weight: bold;">✘ <?= $context.' - '.$failMsg ?></div><?
  }
  $testArray[] = array('result' => $condition, 'text' => ob_get_clean());
}

// ends a group of tests
function tsection_end()
{
  global $testArray, $sectionId, $testCaption;
  $failCount = 0; foreach($testArray as $tline) if(!$tline['result']) $failCount++;
  $sectionId++;
  ?>
  <h3 style="margin-bottom: 2px; padding: 6px; background: #eee; cursor:pointer;" onclick="if($('#section_container_<?= $sectionId ?>').css('display')=='block') $('#section_container_<?= $sectionId ?>').css('display', 'none'); else $('#section_container_<?= $sectionId ?>').css('display', 'block');">
  <? if($failCount > 0) print('<span style="color: red">✘ '.$testCaption.' - FAILED TESTS: '.$failCount.'</span>'); 
    else print('<span style="color: green">✔ '.$testCaption.' - '.sizeof($testArray).' Test'.(sizeof($testArray)==1?'':'s').' OK</span>');?>    
  </h3>
  <div style="padding: 6px; margin-top: -6px; <? if($failCount==0) print('display:none'); ?>" id="section_container_<?= $sectionId ?>"><?
  foreach($testArray as $tline)
    print($tline['text']);
  ?></div><script>
    
  </script><?
}

// starts a new group of tests
function tsection($caption)
{
  global $testArray, $testCaption;
  if(sizeof($testArray)>0) tsection_end();
  $testArray = array();
  $testCaption = $caption;
}

?>