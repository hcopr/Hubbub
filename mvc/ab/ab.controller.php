<?php

class AbController extends HubbubController
{
	function __init()
	{
    access_policy('auth');
    #$this->menu = $this->makeMenu('index,add,ab,rcv', $countArray);
		$this->invokeModel();
    $this->myEntity = $this->user->selfEntity();
	}
	
	function index()
	{

	}
	
  function ajax_abstatus()
  {
    $this->skipView = false;
    $this->myEntry = $this->model->ABGetEntry($this->myEntity['url']); 
    $this->reqStatus = h2_nv_retrieve('abreq/'.$this->myEntity['_key']);
  }
	
	function ajax_abnew()
	{
	  access_policy('write');
	  $this->skipView = false;
    $this->myEntry = $this->model->ABNewEntry($this->myEntity, $_REQUEST['comment']); 
  }
  
  function ajax_abremove()
  {
    access_policy('write');
	  $this->skipView = false;
    $this->myEntry = $this->model->ABRemoveEntry($this->myEntity); 
  }
	
}

?>