<?

class SigninModel extends HubbubModel
{
  function getNews()
  {
    $newsFeedUrl = 'http://hubbub.at/news.json';
    $news = h2_nv_retrieve($newsFeedUrl);
    if(sizeof($news) == 0 || $news['date'] < time() - 60*60*24)
    {
      $feed = HubbubEndpoint::request($newsFeedUrl, array('version' => cfg('service.version'))); 
      $news = json_decode($feed['body'], true);
      h2_nv_store($newsFeedUrl, $news);
    }
    return($news);
  }
  
	function initOAuth()
	{
    require('ext/oauth/epi_curl.php');
    require('ext/oauth/epi_oauth.php');
    require('ext/oauth/epi_twitter.php');
	}
	
	function oAuthSignin()
	{
    $this->initOAuth();    
		$twitterObj = new EpiTwitter(cfg('twitter.consumer_key'), cfg('twitter.consumer_secret'));
		return($twitterObj->getAuthenticateUrl().'&oauth_callback='.urlencode(actionUrl('index', 'signin', array(), true)));
	}
	
	function getAccount($type, $url = null)
	{
	  $match = array('ia_type' => $type);
	  if($url != null) $match['ia_url'] = $url;
		return(DB_GetDatasetMatch('idaccounts', $match));
	}
	
	function newAccount(&$ads)
	{
		if($ads['ia_key'] > 0) 
		{
			// if this account is already connected
		  $_SESSION['uid'] = $ads['ia_user'];
			return(false);
    }
		else if($_SESSION['uid'] > 0)
		{
			// if we're still logged in, add this to an existing account!
			$ads['ia_user'] = $_SESSION['uid'];
      DB_UpdateDataset('idaccounts', $ads);
      $this->redirectOverride = array('auth', 'settings');
      return(false);			
		}
		else
		{
	    $uds = array('u_name' => '',);
	    $ukey = DB_UpdateDataset('users', $uds);      
	    $_SESSION['uid'] = $ukey;
	    $ads['ia_user'] = $ukey;
	    $ads['ia_key'] = DB_UpdateDataset('idaccounts', $ads);
	    return(true);
		}
	}
	
	function completeOAuth($token)
  {
	  // there are some bugs in lightopenid that make this necessary
	  ob_start();
    $result = '';
    $this->initOAuth();
    $twitterObj = new EpiTwitter(cfg('twitter.consumer_key'), cfg('twitter.consumer_secret'));
		$twitterObj->setToken($token);
    $token = $twitterObj->getAccessToken();
		$twitterObj->setToken($token->oauth_token, $token->oauth_token_secret);
		
		$ads = $this->getAccount('twitter', $token->oauth_token.':'.$token->oauth_token_secret);
    $twitterInfo= $twitterObj->get_accountVerify_credentials();
    $twitterInfo->response; 
		$ads['ia_properties'] = json_encode($twitterInfo->response);
		$this->newAccount($ads);
    h2_nv_store('twitterinfo/'.$ads['ia_key'], $twitterInfo->response);
    h2_nv_store('twitterinfo', $twitterInfo->response);
    $this->openid_error = ob_get_clean();
    return($url);
  }
	
 function initOpenId($identity)
  {
    require('ext/lightopenid/openid.php');
    $this->openid = new LightOpenID;
    $this->openid->identity = $identity;
		$this->openid->required = array('namePerson/friendly', 'contact/email', 'namePerson/first', 'namePerson/last', 'birthDate', 'person/gender', 'contact/country/home', 'pref/language', 'pref/timezone');
  }
	
	function openIdAuthUrl()
	{
	  // there are some bugs in lightopenid that make this necessary
	  ob_start();
	  try
	  {
      $url = $this->openid->authUrl();
    } catch (Exception $e) { 
      logError('', $e->getMessage());
    }
    $this->openid_error = ob_get_clean();
    return($url);
  }
	
  function completeOpenID(&$openid)
  {
  	$ads = $this->getAccount('openid', $openid->identity);
		$attr = $openid->getAttributes();
		$ads['ia_properties'] = json_encode($attr);
		$this->newAccount($ads);
    h2_nv_store('openid/'.$ads['ia_key'], $attr);
    h2_nv_store('openid', $attr);
  }
	
}

?>