<?php session_start();

	require_once("OAuth.php");

	$key = /* Your consumer key */;
	$secret = /* Your consumer secret */;
	$base_url = /* The base URL of your website */;
	$request_token_endpoint = 'http://api.mapmyfitness.com/3.1/oauth/request_token';
	$access_token_endpoint = 'http://api.mapmyfitness.com/3.1/oauth/access_token';
	$authorize_endpoint = "https://www.mapmyfitness.com/oauth/authorize";
	
	function doHttpRequest($urlreq)
	{
		$ch = curl_init();

		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, "$urlreq");
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

		// grab URL and pass it to the browser
		$request_result = curl_exec($ch);
	
		// close cURL resource, and free up system resources
		curl_close($ch);

   		return $request_result;
	}
	
	if (!isset($_REQUEST['token']) && $_SESSION['state'] == 1) $_SESSION['state'] = 0;
	if ($_SESSION['state'] == 2) $_SESSION['state'] = 0;
	
   	$consumer = new OAuthConsumer($key, $secret, NULL); 
   	$parsed = parse_url($request_token_endpoint);
   	$params = array();
   	parse_str($parsed['query'], $params);

   	$tok_req = OAuthRequest::from_consumer_and_token($consumer, NULL, "GET", $request_token_endpoint, $params);
   	$tok_req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, NULL);
   
   
   	if ($_SESSION['state'] == 0) {
        $req_token = doHttpRequest ($tok_req->to_url());
        parse_str ($req_token,$tokens);
        $oauth_token = $tokens['oauth_token'];
        $oauth_token_secret = $tokens['oauth_token_secret'];
    	$callback_url = "$base_url/authenticate.php?key=$key&token=$oauth_token&token_secret=$oauth_token_secret&endpoint="
                    . urlencode($authorize_endpoint);
        $auth_url = $authorize_endpoint . "?oauth_token=$oauth_token&oauth_callback=".urlencode($callback_url);
        $_SESSION['state'] = 1;
        header("Location: $auth_url");

   } else if ($_SESSION['state'] == 1) {
       
    	$temp_token = $_REQUEST['token'];
    	$temp_token_secret = $_REQUEST['token_secret'];
    	$auth_token = new OAuthConsumer($temp_token, $temp_token_secret);
    	$access_token_req = new OAuthRequest("GET", $access_token_endpoint);
    	$access_token_req = $access_token_req->from_consumer_and_token($consumer,
                $auth_token, "GET", $access_token_endpoint);

    	$access_token_req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(),$consumer,
                $auth_token);

		$after_access_request = doHttpRequest($access_token_req->to_url());
		parse_str($after_access_request,$access_tokens);

		$access_token = new OAuthConsumer($access_tokens['oauth_token'], $access_tokens['oauth_token_secret']);

		//Request information about the user using the provided tokens
		$userInfo_req = $access_token_req->from_consumer_and_token($consumer,
                $access_token, "GET", "http://api.mapmyfitness.com/3.1/users/get_user?[API Authentication]&o=xml");

		$userInfo_req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(),$consumer,$access_token);

		$after_request = doHttpRequest($userInfo_req->to_url());

		$userData = parseXMLData($after_request);

		$_SESSION['first_name'] = $userData[13]['value'];

		if ($_SESSION['first_name'] == NULL)
		{
    		$_SESSION['state'] = 2;
		}	
		header("Location: index.php");	
	}
	
	function parseXMLData ($xmlData)
	{
   		$p = xml_parser_create();
   		xml_parse_into_struct($p, $xmlData, $vals, $index);
   		xml_parser_free($p);
   		return $vals;
	}

?>