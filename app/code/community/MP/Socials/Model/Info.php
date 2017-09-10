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
 * Abstract Class MP_Socials_Model_Info
 *
 * @method string getFirstname()
 * @method string getEmail()
 * @method string getLastname()
 * @method string getDob()
 * @method string getGender()
 *
 * @category   MP
 * @package    MP_Socials
 * @author     Merchant Protocol Team <info@merchantprotocol.com>
 */
abstract class MP_Socials_Model_Info extends Varien_Object
{
    use MP_Socials_Trait;

    /**
     * @var MP_Socials_Model_Facebook_Oauth2_Client
     */
    protected $client;

    /**
     * @var string
     * @var string
     * @var array
     * @var array
     */
    protected $requestUri    = '';
    protected $requestMethod = Zend_Http_Client::GET;
    protected $requestParams = [];
    protected $requestFields = [];

    /**
     * @var array
     */
    protected $responseMap = [];

    /**
     * Get client
     *
     * @return MP_Socials_Model_Facebook_Oauth2_Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set client
     *
     * @param MP_Socials_Model_Oauth2_Client $client
     */
    public function setClient(MP_Socials_Model_Oauth2_Client $client)
    {
        $this->client = $client;
    }

    /**
     * Set access token
     *
     * @param string $token
     */
    public function setAccessToken($token)
    {
        $this->client->setAccessToken($token);
    }

    /**
     * Get access token
     *
     * @return stdClass
     */
    public function getAccessToken()
    {
        return $this->client->getAccessToken();
    }

    /**
     * Get request params
     *
     * @return array|stdClass
     */
    public function getRequestParams()
    {
        return $this->requestParams;
    }

    /**
     * Get request fields
     *
     * @return array
     */
    public function getRequestFields()
    {
        return $this->requestFields;
    }

    /**
     * Connect with the social network
     *
     * @return $this
     */
    public function connect()
    {
        try {
            $response = $this->client->api(
                $this->requestUri,
                $this->requestMethod,
                $this->getRequestParams(),
                $this->getRequestFields()
            );

            foreach ($response as $key => $value) {
                $key = array_search($key, $this->responseMap) ?: $key;
                $this->setData($key, $value);
            }

            foreach ($this->responseMap as $locKey => $extKey) {
                $extKeys = explode('/', $extKey);

                if (count($extKeys) <= 1) {
                    continue;
                }

                $extValue = clone $response;

                foreach ($extKeys as $item) {
                    $extValue = $extValue->{$item};
                }

                $this->setData($locKey, $extValue);
            }
        } catch (MP_Socials_Model_Oauth2_Exception $e) {
            $this->exception($e);
        } catch (Exception $e) {
            $this->exception($e);
        }

        return $this;
    }

    /**
     * Disconnect from the social network
     *
     * @return $this
     */
    public function disconnect()
    {
        return $this;
    }

    /**
     * Exception treatment
     *
     * @param Exception $e
     * @return $this
     */
    protected function exception($e)
    {
        if ($e instanceof MP_Socials_Model_Oauth2_Exception) {
            $this->getCoreSession()->addNotice($e->getMessage());

            return $this;
        }

        $this->getCoreSession()->addError($e->getMessage());

        return $this;
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     */
    protected function isLoggedIn()
    {
        return $this->getCustomerSession()->isLoggedIn();
    }

    /**
     * Get customer model object
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function getCustomer()
    {
        return $this->getCustomerSession()->getCustomer();
    }

    /**
     * Get customer session object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function getCustomerSession()
    {
        return $this->helper()->getCustomerSession();
    }

    /**
     * Get core session object
     *
     * @return Mage_Core_Model_Session
     */
    protected function getCoreSession()
    {
        return $this->helper()->getSession();
    }
}
