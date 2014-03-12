<?php
/**
 * This file contains CurlLogin class.
 *
 * @license    MIT
 * @link       https://github.com/Jirda/curlLogin
 * @author     Petr Jirasek
 */

namespace CurlLogin;

require_once "exceptions/NotLoginException.php";


class CurlLogin {

    /** @var curl session */
    private $ch;

    /** @var string path to save cookies */
    private $cookiesPath;

    /** @var  string url to login */
    private $loginUrl;

    /** @var  string referer */
    private $referer;

    /** @var  string user agent */
    private $userAgent;

    /** @var string name of input to fill in ogin (default is email) */
    private $loginInputName = "email";

    /** @var string name of input to fill in password (default is password) */
    private $passwordInputName = "password";


    function __construct($loginUrl, $cookiesPath) {
        $this->loginUrl = $loginUrl;
        $this->cookiesPath = $cookiesPath;
    }

    /**
     * Logs in and prepares session.
     * @param string login
     * @param string password
     * @return string|bool
     */
    public function login($login, $password) {

        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_URL, $this->loginUrl);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        curl_setopt($this->ch, CURLOPT_TIMEOUT, 60);
        //curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, $this->cookiesPath);
        curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->cookiesPath);
        //do we want the header in the output?
		//curl_setopt($this->ch, CURLOPT_HEADER, 1);
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);

        if ($this->userAgent)
            curl_setopt($this->ch, CURLOPT_USERAGENT, $this->userAgent);

        if ($this->referer)
	        curl_setopt($this->ch, CURLOPT_REFERER, $this->referer);
		
		//in an effort to handle sites that do not want us to login via scripts like this one we will load  page oncthee and look for things like: csrfmiddlewaretoken
		$intialPage = curl_exec($this->ch);		
		// try to find the actual login form
		if (!preg_match('/<form method="(POST|GET)"(.|\n)*?<\/form>/is', $intialPage, $form)) {
            throw new NotLoginException("Unable to find login form. Verify login url.");
		}
		//we only want our first form. shouldn't ever be more than 1.
		$form = $form[0];
		
		// find the action of the login form
		if (!preg_match('/action="([^"]+)"/i', $form, $action)) {
            //assume post url is our login url
            $post_url = $this->loginUrl;
		}
		else
		{
			$post_url = $action[1]; // this is our new post url
			//make sure it is not an empty action...
			if (empty($post_url))
				$post_url = $this->loginUrl;
		}
		
		//we need to turn this into a proper url, if it isn't
		if (!preg_match("/\b(?:(?:https?|http):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $post_url))
		{
			$result = parse_url($this->loginUrl);
			//this should now be a valid post URL.
			$post_url = $result['scheme']."://". $result['host'] . ((substr($post_url, 0, 1) != '/') ? '/' : '') . $post_url;
		}
		//update to use our post URL.
        curl_setopt($this->ch, CURLOPT_URL, $post_url);
		echo $post_url . "\n";
		
		//update the referer to be our last url, since we are trying to make it think we are logging in
        curl_setopt($this->ch, CURLOPT_REFERER, $this->loginUrl);
		
		// find all hidden fields which we need to send with our login, this includes any security tokens
		$count = preg_match_all('/<input type=("|\')hidden("|\')\s*name=("|\')([^"]*)("|\')\s*value=("|\')([^"]*)("|\') \/>/i', $form, $hiddenFields);
		$postFields = array();
		
		// turn the hidden fields into an array
		for ($i = 0; $i < $count; ++$i) {
			$postFields[$hiddenFields[4][$i]] = $hiddenFields[7][$i];
		}
		
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->prepareParameters($login, $password, $postFields));
        curl_setopt($this->ch, CURLOPT_POST, 1);
		
		//at this point we should try to validate that we are logged in somehow, but for now we will assume.
        $this->isLoggedIn = true;

        return curl_exec ($this->ch);
    }

    /**
     * Prepares parameters.
     * @param string login
     * @param string password
     * @param array additiona_parameters
     * @return string
     */
    private function prepareParameters($login, $password, $postFields = null) {
		if (empty($postFields) || !is_array($postFields))
			$postFields = array();
		$postFields[$this->loginInputName] = $login;
		$postFields[$this->passwordInputName] = $password;			
		// convert to string, this won't work as an array, form will not accept multipart/form-data, only application/x-www-form-urlencoded
		$post = '';
		foreach($postFields as $key => $value) {
			$post .= '&' . $key . '=' . urlencode($value);
		}
        return $post;
    }

    /**
     * Returns data from url.
     * @param string
     * @throws NotLoginException
     * @return string|bool
     */
    public function sendRequest($url) {
        if (!$this->ch)
            throw new NotLoginException("It is necessary to login before sending requests.");

        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_POST, 0);

        return curl_exec($this->ch);
    }

    /**
     * Returns login input name.
     * @return string login input name
     */
    public function getLoginInputName()
    {
        return $this->loginInputName;
    }

    /**
     * Sets login input name.
     * @param string $loginInputName
     */
    public function setLoginInputName($loginInputName)
    {
        $this->loginInputName = $loginInputName;
    }

    /**
     * Returns password input name.
     * @return string
     */
    public function getPasswordInputName()
    {
        return $this->passwordInputName;
    }

    /**
     * Sets password input name.
     * @param string $passwordInputName
     */
    public function setPasswordInputName($passwordInputName)
    {
        $this->passwordInputName = $passwordInputName;
    }

    /**
     * Returns user agent.
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * Sets user agent.
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    /**
     * Returns referer.
     * @return string
     */
    public function getReferer()
    {
        return $this->referer;
    }

    /**
     * Sets referer.
     * @param string $referer
     */
    public function setReferer($referer)
    {
        $this->referer = $referer;
    }
}



