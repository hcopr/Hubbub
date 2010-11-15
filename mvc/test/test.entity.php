<h3>Creating and finding</h3>
<?
  
	$newUserId = 'HTT_'.substr(md5(time()), 0, 5);
	$erec = array('server' => $_SERVER['HTTP_HOST'], 'user' => $newUserId, 'url' => $_SERVER['HTTP_HOST'].'/'.$newUserId);
	$ne = new HubbubEntity();
	?><div>Test Entity: <?= json_encode($erec) ?></div><?
	$ne->create($erec, true);
  tlog($ne->ds['_key'] > 0, 'HubbubEntity::create()', 'created', 'failed, no key assigned'); 

  $found = HubbubEntity::findEntityGlobal(array('server' => $erec['server'], 'user' => $erec['user']));
  tlog($found['_key'] > 0, 'HubbubEntity::findEntityGlobal(server, user)', 'found', 'not found'); 
  tlog($found['_key'] == $ne->ds['_key'], 'HubbubEntity::findEntityGlobal(server, user) key value', 'correct', 'not valid'); 	
	
  tlog(!HubbubEntity::isNameAvailable($erec['user']), 'HubbubEntity::isNameAvailable(user)', 'OK', 'name not found'); 
	
?>