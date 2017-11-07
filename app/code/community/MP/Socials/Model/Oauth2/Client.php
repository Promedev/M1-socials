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
     * @var string
     */
    protected $oauth2RevokeUri;

    /**
     * @var string
     */
    protected $state;

    /**
     * @var array
     */
    protected $scope = array();

    /**
     * @var array
     */
    protected $scopeSeparator = ',';

    /**
     * @var string
     */
    protected $access;

    /**
     * @var string
     */
    protected $prompt;

    /**
     * @var mixed
     */
    protected $token;

    /**
     * @var array
     */
    protected $requestConfig = array(
        'timeout' => 60
    );

    /**
     * @var Zend_Http_Response
     */
    protected $response;

    /**
     * @var mixed
     */
    protected $responseDecoded;

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) $this->getConfig($this->xmlPathEnabled);
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->getConfig($this->xmlPathClientId);
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->getConfig($this->xmlPathClientSecret);
    }

    /**
     * @return string
     */
    public function getRedirectUri()
    {
        return Mage::getUrl($this->redirectUriRoute);
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return implode($this->scopeSeparator, $this->scope);
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

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
     * @return void
     */
    public function setAccessToken($token)
    {
        $this->token = json_decode($token);
    }

    /**
     * @return array
     */
    public function getAccessParams()
    {
        return array('access_token' => $this->token->access_token);
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
        return $this->oauth2AuthUri
            . '?response_type='   . 'code'
            . '&redirect_uri='    . $this->getRedirectUri()
            . '&client_id='       . $this->getClientId()
            . '&scope='           . $this->getScope()
            . '&state='           . $this->getState()
            . '&access_type='     . $this->getAccess()
            . '&approval_prompt=' . $this->getPrompt();
    }

    /**
     * @param string $endpoint
     * @param string $method
     * @param array $params
     * @param array $fields
     * @return mixed
     * @throws Exception
     */
    public function api($endpoint, $method = Zend_Http_Client::GET, $params = array(), $fields = array())
    {
        if (empty($this->token)) {
            throw new Exception($this->helper()->__('Unable to proceed without an access token.'));
        }

        $url = $this->oauth2ServiceUri . $endpoint;

        if (!empty($params) && !empty($fields)) {
            foreach ($params as $key => $value) {
                $url .= '/' . $key;

                if (!empty($value)) {
                    $url .= '=' . $value;
                }
            }

            $url .= ':(' . implode(',', $fields) . ')';
        }

        $method   = strtoupper($method);
        $params   = array_merge($this->getAccessParams(), $params);
        $response = $this->httpRequest($url, $method, $params);

        return $response;
    }

    /**
     * @param string $code
     * @return mixed|null
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
            array(
                'code'          => $code,
                'redirect_uri'  => $this->getRedirectUri(),
                'client_id'     => $this->getClientId(),
                'client_secret' => $this->getClientSecret(),
                'grant_type'    => 'authorization_code'
            )
        );

        $this->token = $response;

        return $this->token;
    }

    /**
     * @param string $uri
     * @param string $method
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    protected function httpRequest($uri, $method = Zend_Http_Client::GET, $params = array())
    {
        $client = new Zend_Http_Client($uri, $this->requestConfig);

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
     * @return Mage_Core_Model_Session
     */
    protected function getSession()
    {
        return $this->helper()->getSession();
    }

    /**
     * @param string $xmlPath
     * @return string
     */
    protected function getConfig($xmlPath)
    {
        return Mage::getStoreConfig($xmlPath, $this->helper()->getStoreId());
    }
}
