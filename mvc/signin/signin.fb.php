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

$cookie = get_facebook_cookie(cfg('facebook.app_id'), cfg('facebook.app_secret'));

?>

<span id="fb-root"></span>
<script src="http://connect.facebook.net/en_US/all.js"></script>
<script>
  FB.init({appId: '<?= cfg('facebook.app_id') ?>', status: true, cookie: true, xfbml: true});
  FB.Event.subscribe('auth.login', function(response) {
        window.location.reload();
      });
  
  function do_fb_login()
  {
    FB.login();   
  }
</script>
<?

if($cookie['access_token'])
{
  $userdata = json_decode(file_get_contents('https://graph.facebook.com/me?fields=picture&access_token='.$cookie['access_token']), true);
  $ads = $this->model->getAccount('fb', $userdata['id']);
	$ads['ia_comments'] = $userdata['name'].' (#'.$userdata['id'].')';
  $this->model->newAccount($ads);
  h2_nv_store('fb.basic/'.$ads['ia_key'], $userdata);
  $this->user->login();
  ?><script>
  document.location.href = '<?= actionUrl('index', 'home') ?>'; 
    </script><?
}

?>
<fb:login-button></fb:login-button>
<br/>
<h2>Signing in with Facebook...</h2>
<a href="<?= actionUrl('index', 'signin') ?>" class="btn">Cancel</a>
