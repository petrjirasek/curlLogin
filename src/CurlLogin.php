<?php

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

        if ($this->userAgent)
            curl_setopt($this->ch, CURLOPT_USERAGENT, $this->userAgent);

        if ($this->referer)
        curl_setopt($this->ch, CURLOPT_REFERER, $this->referer);

        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->prepareParameters($login, $password));
        curl_setopt($this->ch, CURLOPT_POST, 1);

        $this->isLoggedIn = true;

        return curl_exec ($this->ch);
    }

    /**
     * Prepares parameters.
     * @param string login
     * @param string password
     * @return string
     */
    private function prepareParameters($login, $password) {
        return "&" . $this->loginInputName . "=".$login."&" .$this->passwordInputName. "=".$password;
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



