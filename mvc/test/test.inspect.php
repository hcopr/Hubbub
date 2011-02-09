<?

$ds = DB_GetDataset('messages', $_REQUEST['id']);
$data = HubbubMessage::unpackData($ds);

?>
<h2>Inspect Message <?= $_REQUEST['id'] ?></h2>
<pre><?
  print_r($data);
  $ds['m_data'] = strlen($ds['m_data']).' bytes';
  print_r($ds);
  ?></pre>