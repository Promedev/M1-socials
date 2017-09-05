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
 * Abstract Class MP_Socials_Model_Oauth2_Client
 *
 * @category   MP
 * @package    MP_Socials
 * @author     Merchant Protocol Team <info@merchantprotocol.com>
 */
abstract class MP_Socials_Model_Oauth2_Client extends Varien_Object
{
    use MP_Socials_Trait;

    /**
     * @var string
     */
    protected $redirectUriRoute;

    /**
     * @var string
     */
    protected $xmlPathEnabled;

    /**
     * @var string
     */
    protected $xmlPathClientId;

    /**
     * @var string
     */
    protected $xmlPathClientSecret;

    /**
     * @var string
     */
    protected $oauth2ServiceUri;

    /**
     * @var string
     */
    protected $oauth2AuthUri;

    /**
     * @var string
     */
    protected $oauth2TokenUri;

    /**
     * @var Zend_Http_Response
     */
    protected $response;

    /**
     * @var mixed
     */
    protected $responseDecoded;

    /**
     * @var string
     */
    protected $state = '';

    /**
     * @var array
     */
    protected $scope = [];

    /**
     * @var
     */
    protected $token;

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) $this->getStoreConfig($this->xmlPathEnabled);
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->getStoreConfig($this->xmlPathClientId);
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->getStoreConfig($this->xmlPathClientSecret);
    }

    /**
     * @return string
     */
    public function getRedirectUri()
    {
        return Mage::getUrl($this->redirectUriRoute);
    }

    /**
     * @return mixed
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @param string $token
     */
    public function setAccessToken($token)
    {
        $this->token = $token;

        $this->extendAccessToken();
    }

    /**
     * @param null|string $code
     * @return mixed|null
     * @throws Exception
     */
    public function getAccessToken($code = null)
    {
        if (!empty($code)) {
            return $this->fetchAccessToken($code);
        }

        if (!empty($this->token)) {
            return $this->token;
        }

        throw new Exception($this->helper()->__('Unable to proceed without an access token.'));
    }

    /**
     * @return string
     */
    public function createAuthUrl()
    {
        return $this->oauth2AuthUri . '?'
            . 'client_id='     . $this->getClientId()
            . '&redirect_uri=' . $this->getRedirectUri()
            . '&state='        . $this->getState()
            . '&scope='        . implode(',', $this->getScope());
    }

    /**
     * @param string $endpoint
     * @param string $method
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function api($endpoint, $method = Zend_Http_Client::GET, $params = [])
    {
        if (empty($this->token)) {
            throw new Exception($this->helper()->__('Unable to proceed without an access token.'));
        }

        $url      = $this->oauth2ServiceUri . $endpoint;
        $method   = strtoupper($method);
        $params   = array_merge(['access_token' => $this->token->access_token], $params);
        $response = $this->httpRequest($url, $method, $params);

        return $response;
    }

    /**
     * @param string $code
     * @return mixed|null
     */
    protected function fetchAccessToken($code)
    {
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

        $this->token = $response;

        $this->extendAccessToken();

        return $this->token;
    }

    /**
     * @return mixed|null
     * @throws Exception
     */
    public function extendAccessToken()
    {
        if (empty($this->token)) {
            throw new Exception($this->helper()->__('No token set, nothing to extend.'));
        }

        /**
         * Expires not set or expires over two hours means long lived token
         */
        if (!property_exists($this->token, 'expires') || $this->token->expires > 7200) {
            /**
             * Long lived token, no need to extend
             */
            return null;
        }

        $response = $this->httpRequest(
            $this->oauth2TokenUri,
            'GET',
            [
                'client_id'         => $this->getClientId(),
                'client_secret'     => $this->getClientS,
                'fb_exchange_token' => $this->token->access_token,
                'grant_type'        => 'fb_exchange_token'
            ]
        );

        return $this->token = $response;
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    protected function httpRequest($url, $method = Zend_Http_Client::GET, $params = [])
    {
        $client = new Zend_Http_Client($url, ['timeout' => 60]);

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

    /**
     * @return void
     * @throws Exception
     * @throws MP_Socials_Model_Oauth2_Exception
     */
    protected function responseTreatment()
    {
        if (!$this->response->isError()) {
            return;
        }

        $status = $this->response->getStatus();

        if (($status == 400 || $status == 401)) {
            $message = (isset($this->responseDecoded->error->message))
                ? $this->responseDecoded->error->message
                : $this->helper()->__('Unspecified OAuth error occurred.');

            throw new MP_Socials_Model_Oauth2_Exception($message);
        }

        throw new Exception($this->helper()->__('HTTP error %d occurred while issuing request.', $status));
    }

    /**
     * @param string $xmlPath
     * @return string
     */
    protected function getStoreConfig($xmlPath)
    {
        return Mage::getStoreConfig($xmlPath, $this->getStoreId());
    }

    /**
     * @return int
     */
    protected function getStoreId()
    {
        return Mage::app()->getStore()->getId();
    }
}