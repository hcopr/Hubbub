<?php

class UiController extends HubbubController
{
	function __init()
	{
    access_policy('auth');
	}
	
	function index()
	{
	  
	}
	
  function ajax_loadeditor()
  {
    $attachment_types = array();
    h2_execute_event('publish_attachments_register', $attachment_types); 
    foreach($attachment_types as $att)
    {
      $id = md5($att['editor']);
      if($_REQUEST['id'] == $id)
      {
        $editorFile = 'plugins/'.$att['editor']; 
        if(file_exists($editorFile))
          include($editorFile);
        else
          print('<div class="banner">Editor not found: '.$att['caption'].'</div>');
      }
    }
  }
}

?>