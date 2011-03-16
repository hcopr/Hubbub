<?

$params['ia_recovery'] = randomHashId();
DB_UpdateDataset('idaccounts', $params);

$subject = 'Recover Your Hubbub Password';

?>Dear Hubbub user,

you (or someone using your email address) requested that your Hubbub
password on server <?= cfg('service/server') ?> be reset. 

Please click on the following link to change it:

<?= actionUrl('reset', 'signin', array('i' => $params['ia_recovery']), true) ?> 

If you did not request a reset, simply ignore this message. The 
request was made from the following IP address:

<?= $_SERVER['REMOTE_HOST'] ?> 

