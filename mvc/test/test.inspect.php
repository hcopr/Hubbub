<?

$ds = DB_GetDataset('messages', $_REQUEST['id']);
$data = HubbubMessage::unpackData($ds);

$data['created'] = $data['created'].' ('.gmdate('Y-m-d H:i:s', $data['created']).')';
$data['changed'] = $data['changed'].' ('.gmdate('Y-m-d H:i:s', $data['changed']).')';

?>
<h2>Inspect Message <?= $_REQUEST['id'] ?></h2>
<pre><?
  print('Message Data '); print_r($data);
  $ds['m_data'] = strlen($ds['m_data']).' bytes';
  print('Local Storage '); print_r($ds);
  ?></pre>