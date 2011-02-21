<?

class SigninModel extends HubbubModel
{
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
		return($twitterObj->getAuthenticateUrl().'&oauth_callback='.urlencode(scriptURI()));
	}
	
	function getAccount($type, $url)
	{
		return(DB_GetDatasetMatch('idaccounts', array(
		  'ia_type' => $type,
			'ia_url' => $url,
		  )));
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
			// if we're still logged in
			$ads['ia_user'] = $_SESSION['uid'];
      DB_UpdateDataset('idaccounts', $ads);
      return(false);			
		}
		else
		{
	    $uds = array('u_name' => '',);
	    $ukey = DB_UpdateDataset('users', $uds);      
	    $_SESSION['uid'] = $ukey;
	    $ads['ia_user'] = $ukey;
	    DB_UpdateDataset('idaccounts', $ads);
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
    $ads['ia_comments'] = $twitterInfo->response['name'].' (@'.$twitterInfo->response['screen_name'].')';         
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
		$ads['ia_comments'] = trim($attr['contact/email']);
		$this->newAccount($ads);
    h2_nv_store('openid/'.$ads['ia_key'], $attr);
    h2_nv_store('openid', $attr);
  }
	
}

?>