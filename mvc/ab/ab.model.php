<?

class AbModel extends HubbubModel 
{

	function ABGetEntry($searchText)
	{
	  $result = cqrequest('http://hubbub.at/ab', array('mode' => 'url', 'q' => $searchText));
	  return($result['data']['items'][0]);
  }

  function ReqPrepareEndpoint($checkSum)
  {
    // this is important: we need to prepare the endpoint API to give out
    // the confirmation before we send the request to the AB, because the AB
    // may hold the request until it's completed ITS confirmation request 
    // back to us!
    h2_nv_store('abreq/'.$entityDS['_key'], array(
      'abrequest' => 'pending', 
      'checksum' => $checkSum));
  }
	
	function RemoteRegister($res, $entityDS, $active = 'yes')
	{
    $abData = h2_nv_retrieve('abdata');
    $abData['active'] = $active;
    $abData['receipt'] = $res['receipt'];
    $abData['updated'] = time();
    $abData['entry'] = $entityDS;
    h2_nv_store('abdata', $abData);	  
  }
  
  function RemoteUnregister($res, $entityDS)
  {
    $this->RemoteRegister($res, $entityDS, 'no'); 
  }
  
  function MakeRequest($mode, $entityInfo)
  {
    $result = cqrequest('http://hubbub.at/ab', array(
      'mode' => $mode, 
      'entity' => $entityInfo, 
      'callback' => actionUrl('abconfirm', 'endpoint', array(), true)));    
    return($result['data']);
  }
	
	function ABRemoveEntry($entityDS)
	{
    $entityInfo = json_encode($entityDS);
    $this->ReqPrepareEndpoint(md5($entityInfo));
    $result = $this->makeRequest('delete', $entityInfo);
    if($result['result'] = 'OK')
      $this->RemoteUnRegister($result['data'], $entityDS);
    return($result);
  }
	
  function ABNewEntry($entityDS, $commentText)
  {
    $entityDS['comment'] = $commentText;
    $entityDS['email'] = md5($this->user->ds['u_email']);
    $entityInfo = json_encode($entityDS);
    $this->ReqPrepareEndpoint(md5($entityInfo));
    $result = $this->MakeRequest('new', $entityInfo);
    if($result['result'] = 'OK')
      $this->RemoteRegister($result['data'], $entityDS);
    return($result);
  }
	
}



?>