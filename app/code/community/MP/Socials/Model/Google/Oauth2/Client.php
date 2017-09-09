<?php
/**
 * Merchant Protocol
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Merchant Protocol Commercial License (MPCL 1.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://merchantprotocol.com/commercial-license/
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@merchantprotocol.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to http://www.merchantprotocol.com for more information.
 *
 * @category   MP
 * @package    MP_Socials
 * @copyright  Copyright (c) 2006-2017 Merchant Protocol LLC. and affiliates (https://merchantprotocol.com/)
 * @license    https://merchantprotocol.com/commercial-license/  Merchant Protocol Commercial License (MPCL 1.0)
 */

/**
 * Class MP_Socials_Model_Google_Oauth2_Client
 *
 * @category   MP
 * @package    MP_Socials
 * @author     Merchant Protocol Team <info@merchantprotocol.com>
 */
class MP_Socials_Model_Google_Oauth2_Client extends MP_Socials_Model_Oauth2_Client
{
    /**
     * @var string
     */
    protected $redirectUriRoute = 'socials/google/connect';

    /**
     * @var string
     */
    protected $xmlPathEnabled      = 'mp_socials/google/client_enabled';
    protected $xmlPathClientId     = 'mp_socials/google/client_id';
    protected $xmlPathClientSecret = 'mp_socials/google/client_secret';

    /**
     * @var string
     */
    protected $oauth2ServiceUri = 'https://www.googleapis.com/oauth2/v2';
    protected $oauth2AuthUri    = 'https://accounts.google.com/o/oauth2/auth';
    protected $oauth2TokenUri   = 'https://accounts.google.com/o/oauth2/token';
    protected $oauth2RevokeUri  = 'https://accounts.google.com/o/oauth2/revoke';

    /**
     * @var array
     */
    protected $scope = [
        'https://www.googleapis.com/auth/userinfo.profile',
        'https://www.googleapis.com/auth/userinfo.email'
    ];

    /**
     * @var string
     */
    protected $access = 'offline';

    /**
     * @var string
     */
    protected $prompt = 'auto';

    /**
     * @return string
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * @param string $access
     */
    public function setAccess($access)
    {
        $this->access = $access;
    }

    /**
     * @return string
     */
    public function getPrompt()
    {
        return $this->prompt;
    }

    /**
     * @param string $prompt
     */
    public function setPrompt($prompt)
    {
        $this->access = $prompt;
    }

    /**
     * @param string $token
     */
    public function setAccessToken($token)
    {
        $this->token = json_decode($token);
    }

    /**
     * @param null|string $code
     * @return string
     */
    public function getAccessToken($code = null)
    {
        if (empty($this->token)) {
            $this->fetchAccessToken($code);
        } else if ($this->isAccessTokenExpired()) {
            $this->refreshAccessToken();
        }

        return json_encode($this->token);
    }

    /**
     * @return string
     */
    public function createAuthUrl()
    {
        return $this->oauth2AuthUri
            . '?response_type='   . 'code'
            . '&redirect_uri='    . $this->getRedirectUri()
            . '&client_id='       . $this->getClientId()
            . '&scope='           . implode(' ', $this->getScope())
            . '&state='           . $this->getState()
            . '&access_type='     . $this->getAccess()
            . '&approval_prompt=' . $this->getPrompt();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function revokeToken()
    {
        if (empty($this->token)) {
            throw new Exception($this->helper()->__('No access token available.'));
        }

        if (empty($this->token->refresh_token)) {
            throw new Exception($this->helper()->__('No refresh token, nothing to revoke.'));
        }

        $this->httpRequest(
            $this->oauth2RevokeUri,
            Zend_Http_Client::POST,
            [
                'token' => $this->token->refresh_token
            ]
        );
    }

    /**
     * @param string $code
     * @return void
     * @throws Exception
     */
    protected function fetchAccessToken($code)
    {        
        if (!$code) {
            throw new Exception($this->helper()->__('Unable to retrieve access code.'));
        }
        
        $response = $this->httpRequest(
            $this->oauth2TokenUri,
            Zend_Http_Client::POST,
            [
                'code'          => $code,
                'redirect_uri'  => $this->getRedirectUri(),
                'client_id'     => $this->getClientId(),
                'client_secret' => $this->getClientSecret(),
                'grant_type'    => 'authorization_code'
            ]
        );

        $response->created = time();

        $this->token = $response;
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function refreshAccessToken()
    {
        if (empty($this->token->refresh_token)) {
            throw new Exception($this->helper()->__('No refresh token, unable to refresh access token.'));
        }

        $response = $this->httpRequest(
            $this->oauth2TokenUri,
            Zend_Http_Client::POST,
            [
                'client_id'     => $this->getClientId(),
                'client_secret' => $this->getClientSecret(),
                'refresh_token' => $this->token->refresh_token,
                'grant_type'    => 'refresh_token'
            ]
        );

        $this->token->access_token = $response->access_token;
        $this->token->expires_in   = $response->expires_in;
        $this->token->created      = time();
    }

    /**
     * If the token is set to expire in the next 30 seconds
     *
     * @return bool
     */
    protected function isAccessTokenExpired()
    {
        $expired = ($this->token->created + ($this->token->expires_in - 30)) < time();

        return $expired;
    }
}
