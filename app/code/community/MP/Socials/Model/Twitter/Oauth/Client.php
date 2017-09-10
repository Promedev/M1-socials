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
 * Class MP_Socials_Model_Twitter_Oauth2_Client
 *
 * @category   MP
 * @package    MP_Socials
 * @author     Merchant Protocol Team <info@merchantprotocol.com>
 */
class MP_Socials_Model_Twitter_Oauth2_Client extends MP_Socials_Model_Oauth2_Client
{
    /**
     * @var string
     */
    protected $redirectUriRoute = 'socials/twitter/connect';

    /**
     * @var string
     */
    protected $xmlPathEnabled      = 'mp_socials/twitter/client_enabled';
    protected $xmlPathClientId     = 'mp_socials/twitter/client_id';
    protected $xmlPathClientSecret = 'mp_socials/twitter/client_secret';

    /**
     * @var string
     */
    protected $oauth2ServiceUri = 'https://api.twitter.com/1.1';
    protected $oauth2AuthUri    = 'https://api.twitter.com/oauth';
    protected $oauth2TokenUri   = 'https://api.twitter.com/oauth/request_token';

    /**
     * @var Zend_Oauth_Token_Access
     */
    protected $token;

    /**
     * @var Zend_Oauth_Consumer
     */
    protected $client;

    /**
     * @var mixed
     */
    protected $requestToken;

    /**
     * MP_Socials_Model_Twitter_Oauth2_Client constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->client = new Zend_Oauth_Consumer(
            [
                'callbackUrl'    => $this->getRedirectUri(),
                'siteUrl'        => $this->oauth2AuthUri,
                'authorizeUrl'   => $this->oauth2AuthUri . '/authenticate',
                'consumerKey'    => $this->getClientId(),
                'consumerSecret' => $this->getClientSecret()
            ]
        );
    }

    /**
     * @return string
     */
    public function createAuthUrl()
    {
        return $this->oauth2AuthUri
            . '/authenticate'
            . '?oauth_token=' . $this->getRequestToken();
    }

    /**
     * @return string
     * @throws Exception
     */
    public function fetchRequestToken()
    {
        if (!$requestToken = $this->client->getRequestToken()) {
            throw new Exception($this->helper()->__('Unable to retrieve request token.'));
        }

        $this->getSession()->setData('twitter_request_token', $requestToken);

        return $this->requestToken = $requestToken->getToken();
    }

    /**
     * @param string $code
     * @return mixed|null
     * @throws Exception
     */
    protected function fetchAccessToken($code)
    {
        if (!$params = $this->helper()->getRequest()->getParams()) {
            throw new Exception($this->helper()->__('Unable to retrieve access code.'));
        }

        $requestToken = $this->getSession()->getData('twitter_request_token');

        if (!$token = $this->client->getAccessToken($params, $requestToken)) {
            throw new Exception($this->helper()->__('Unable to retrieve access code.'));
        }

        $this->getSession()->unsetData('twitter_request_token');

        return $this->token = $token;
    }

    /**
     * @return string
     */
    public function getRequestToken()
    {
        if (empty($this->requestToken)) {
            $this->fetchRequestToken();
        }

        return $this->requestToken;
    }

    /**
     * @param string $uri
     * @param string $method
     * @param array $params
     * @param array $fields
     * @return mixed
     * @throws Exception
     * @throws MP_Socials_Model_Oauth2_Exception
     */
    protected function httpRequest($uri, $method = Zend_Http_Client::GET, $params = [], $fields = [])
    {
        /** @var Zend_Oauth_Client $client */
        $client = $this->token->getHttpClient(
            [
                'callbackUrl'    => $this->getRedirectUri(),
                'siteUrl'        => $this->oauth2AuthUri,
                'consumerKey'    => $this->getClientId(),
                'consumerSecret' => $this->getClientSecret()
            ],
            $uri,
            $this->requestConfig
        );

        switch ($method) {
            case Zend_Http_Client::GET:
                $client->setParameterGet($params);
                break;
            case Zend_Http_Client::POST:
                $client->setParameterPost($params);
                break;
            case Zend_Http_Client::DELETE:
                $client->setParameterGet($params);
                break;
            default:
                throw new Exception($this->helper()->__('Required HTTP method is not supported.'));
        }

        $this->response        = $client->request($method);
        $this->responseDecoded = json_decode($this->response->getBody());

        $this->responseTreatment();

        return $this->responseDecoded;
    }
}
