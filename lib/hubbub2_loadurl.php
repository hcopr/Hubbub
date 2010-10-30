<?php 

// checks whether a given URL is a Hubbub URL and loads the entity record within it
function hubbub2_loadurl($url)
{
	$content = HubbubEndpoint::request($url);
	$html = trim($content['body']);
	$entity = array();
	// case 1, this is a json array
	if(substr($html, 0, 1) == '{')
	{
		$entity = json_decode($html);
	}
	else // if not, let's parse for a comment with an entity record in it 
	{
		if(stristr($html, '<!-- hubbub2:{') != '')
		{
			CutSegment('<!-- hubbub2:{', $html);
			$seg = '{'.trim(CutSegment('-->', $html));
			$entity = json_decode($seg, true);
		}
	}
	return($entity);
}

?>