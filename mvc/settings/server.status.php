<?

ob_start();
if(cfg('memcache/enabled'))
{
  cache_connect();
  if(!object('memcache'))
  {
    banner('Could not connect to memcache server: '.cfg('memcache/server'));
  }
  else
  {
    $stats = object('memcache')->getStats(); 
    print('<div class="smallwin banner">');
    print('Memcached Version: '.$stats['version'].', ');     
    print('Current Connections: '.$stats['curr_connections'].'<br/>');     
    print('Cache Size: '.number_format($stats['limit_maxbytes'] / (1024*1024), 2).' MB total, '.number_format(($stats['limit_maxbytes']-$stats['bytes']) / (1024*1024), 2).' MB free<br/>');     
    print('Hits: '.ceil($stats['get_hits'] / (1)).', Misses: '.ceil($stats['get_misses'] / (1)).', Evictions: '.ceil($stats['evictions'] / 1).'<br/>');     
    print('</div>');
  }
}
if($GLOBALS['errors']['memcache'] != '')
{
  ?><div class="fail banner">
    Error: <?= $GLOBALS['errors']['memcache'] ?>
  </div><? 
}
$memcache_status = ob_get_clean();


ob_start();
$normalUrl = cqrequest('http://'.cfg('service/server').'/?signin');
$prettyUrl = cqrequest('http://'.cfg('service/server').'/signin');
?><div class="banner">
  <? if(substr($prettyUrl['headers']['code'], 0, 1) != '4') print('<div class="smallwin">Pretty URLs supported</div>'); else print('<div class="win">Pretty URLs not supported</div>');?>
</div><?
$server_status = ob_get_clean();


ob_start();
$pingServer = cfg('ping/server');
if(!strStartsWith($pingServer, 'http://')) $pingServer = 'http://'.$pingServer;
if(file_exists('log/cron.last.log'))
{
  $btype = 'smallwin';
  $lastPing = filectime('log/cron.last.log');
  $lastPingText = 'Last ping: '.ageToString($lastPing, 'very recently');
}
else
{
  $btype = 'fail';
  $lastPingText = 'Waiting for ping from '.$pingServer.'...'; 
}  
if(cfg('ping/remote') && cfg('ping/server') != '')
{
  $pingStatus = h2_nv_retrieve('ping/status');
  if($pingStatus['server'] != $pingServer)
  {
    $pingRequest = cqrequest($pingServer, array('origin' => 'http://'.cfg('service/server').'/cron.php', 'request' => 'activate', 'password' => cfg('ping/password')), 2);   
    if($pingRequest['data']['result'] == 'OK')
    {
      $btype = 'win';
      $lastPingText = 'Connection with ping server established, waiting for ping from '.$pingServer.'...';
      $pingStatus = $pingRequest['data'];
      $pingStatus['server'] = $pingServer;
      h2_nv_store('ping/status', $pingStatus);
      @unlink('log/cron.last.log');
    }
    else
    {
      $btype = 'fail';
      $reason = $pingRequest['data']['reason'];
      $lastPingText = 'Could not establish connection with ping server. Reason: '.getDefault($reason, 'server not found');
    }
  }
}
?><div class="banner <?= $btype ?>">
  <?= $lastPingText ?>
</div><?
$ping_status = ob_get_clean();


ob_start();
?><div class="banner" style="color: gray">
  S3 not supported yet
</div><?
$s3_status = ob_get_clean();


?>