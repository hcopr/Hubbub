<?

function get_facebook_cookie($app_id, $application_secret) {
  $args = array();
  parse_str(trim($_COOKIE['fbs_' . $app_id], '\\"'), $args);
  ksort($args);
  $payload = '';
  foreach ($args as $key => $value) {
    if ($key != 'sig') {
      $payload .= $key . '=' . $value; 
    }
  }
  if (md5($payload . $application_secret) != $args['sig']) {
    return array();
  }
  return $args;
}

$cookie = get_facebook_cookie(cfg('facebook/app_id'), cfg('facebook/app_secret'));

?>

<span id="fb-root"></span>
<script src="http://connect.facebook.net/en_US/all.js"></script>
<script>

  FB.init({appId: '<?= cfg('facebook/app_id') ?>', status: true, cookie: true, xfbml: true});
  FB.Event.subscribe('auth.login', function(response) {
        window.location.reload();
      });
  
  function do_fb_login()
  {
    FB.login();   
  }

</script>
<?
#print_r($cookie);
if($cookie['access_token'])
{
  $userdata_pic = json_decode(file_get_fromurl('https://graph.facebook.com/me?fields=picture&access_token='.$cookie['access_token']), true);
  $userdata = json_decode(file_get_fromurl('https://graph.facebook.com/me?access_token='.$cookie['access_token']), true);
  $userdata['picture'] = $userdata_pic['picture'];
  $ads = $this->model->getAccount('fb', $userdata['id']);
	$ads['ia_properties'] = json_encode($userdata['name']);
  $this->model->newAccount($ads);
  h2_nv_store('fb.basic/'.$ads['ia_key'], $userdata);
  h2_nv_store('fb.basic', $userdata);
  $this->user->login();
  ?><script>
  document.location.href = '<?= actionUrl('index', 'home') ?>'; 
  </script><?
}

?>
<br/>
<h2><?= l10n('fb.signing.in') ?>...</h2>
<div class="balloonhelp"><?= l10n('fb.balloon') ?></div>
<br/>
<br/>
<fb:login-button></fb:login-button>
&nbsp;&nbsp;<a href="<?= actionUrl('index', 'settings') ?>" class="btn"><?= l10n('cancel') ?></a>
