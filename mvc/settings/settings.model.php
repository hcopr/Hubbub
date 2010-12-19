<?

class SettingsModel extends HubbubModel
{	
	function CheckNewUrl($newUrl)
	{
    require_once('lib/hubbub2_loadurl.php');
    // let's see if there is a valid entity record at this url
		$ne = hubbub2_loadurl($newUrl);
		if(sizeof($ne) == 0)
		{
      $result = array('result' => 'fail', 'reason' => 'entity_not_found');
		}
		else if($ne['user'] == '' || $ne['server'] == '')
		{
			$result = array('result' => 'fail', 'reason' => 'invalid_entity');
		}
		else 
		{
			$userEntity = HubbubEntity::ds2array($GLOBALS['obj']['user']->selfEntity());
			$localEntity = HubbubEntity::findEntity($ne);
			$result = array('entity' => $ne, 'match' => $localEntity);
			if(sizeof($localEntity) == 0 || $localEntity['user'] != $userEntity['user'])
			{
				$result['result'] = 'fail';
				$result['reason'] = 'entity_mismatch';
			}
			else 
			{
				$result['result'] = 'OK';
			}
		}
		return($result);
	}
	
	function ChangeMyUrl($newUrl)
	{
		$result = $this->CheckNewUrl($newUrl);
		if($result['result'] == 'OK')
		{
			$ds = $GLOBALS['obj']['user']->selfEntity();
			$ds['url'] = $newUrl;
			DB_UpdateDataset('entities', $ds);
			return(true);
		}
	}
}


?>