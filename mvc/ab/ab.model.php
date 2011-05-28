<?

class AbModel extends HubbubModel 
{

	function ABGetEntry($searchText)
	{
	  $result = cqrequest('http://hubbub.at/ab', array('mode' => 'url', 'q' => $searchText));
	  return($result['data']['items'][0]);
  }
	
  function ABNewEntry($entityDS, $commentText)
  {
    $entityDS['comment'] = $commentText;
    $entityDS['email'] = md5($this->user->ds['u_email']);
    $entityInfo = json_encode($entityDS);
    // this is important: we need to prepare the endpoint API to give out
    // the confirmation before we send the request to the AB, because the AB
    // may hold the request until it's completed ITS confirmation request 
    // back to us!
    h2_nv_store('abreq/'.$entityDS['_key'], array(
      'abrequest' => 'pending', 
      'checksum' => md5($entityInfo)));
    // now, make the request to add our info to the AB
    $result = cqrequest('http://hubbub.at/ab', array(
      'mode' => 'new', 
      'entity' => $entityInfo, 
      'callback' => actionUrl('abconfirm', 'endpoint', array(), true)));
    return($result['data']);
  }
	
}



?>