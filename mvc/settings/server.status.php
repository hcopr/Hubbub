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
if(file_exists('log/cron.last.log'))
{
  $btype = 'smallwin';
  $lastPing = filectime('log/cron.last.log');
  $lastPingText = ageToString($lastPing, 'very recently');
}
else
{
  $btype = 'fail';
  $lastPingText = '(no ping detected)'; 
}  
?><div class="banner <?= $btype ?>">
  Last ping: <?= $lastPingText ?>
</div><?
$ping_status = ob_get_clean();


ob_start();
?><div class="banner" style="color: gray">
  S3 not supported yet
</div><?
$s3_status = ob_get_clean();


?>