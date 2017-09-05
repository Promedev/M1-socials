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
 * @method string getFirstName()
 * @method string getEmail()
 * @method string getLastName()
 * @method string getBirthday()
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
     */
    protected $requestUri    = '';
    protected $requestMethod = Zend_Http_Client::GET;
    protected $requestParams = [];

    /**
     * @return MP_Socials_Model_Facebook_Oauth2_Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param MP_Socials_Model_Oauth2_Client $client
     */
    public function setClient(MP_Socials_Model_Oauth2_Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $token
     */
    public function setAccessToken($token)
    {
        $this->client->setAccessToken($token);
    }

    /**
     * @return stdClass
     */
    public function getAccessToken()
    {
        return $this->client->getAccessToken();
    }

    /**
     * @return array|stdClass
     */
    public function getRequestParams()
    {
        return $this->requestParams;
    }

    /**
     * @return $this
     */
    public function connect()
    {
        try {
            $response = $this->client->api(
                $this->requestUri,
                $this->requestMethod,
                $this->getRequestParams()
            );

            foreach ($response as $key => $value) {
                $this->{$key} = $value;
            }
        } catch (MP_Socials_Model_Oauth2_Exception $e) {
            $this->exception($e);
        } catch (Exception $e) {
            $this->exception($e);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function disconnect()
    {
        return $this;
    }

    /**
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
     * @return bool
     */
    protected function isLoggedIn()
    {
        return $this->getCustomerSession()->isLoggedIn();
    }

    /**
     * @return Mage_Customer_Model_Customer
     */
    protected function getCustomer()
    {
        return $this->getCustomerSession()->getCustomer();
    }

    /**
     * @return Mage_Customer_Model_Session
     */
    protected function getCustomerSession()
    {
        return $this->helper()->getCustomerSession();
    }

    /**
     * @return Mage_Core_Model_Session
     */
    protected function getCoreSession()
    {
        return $this->helper()->getCoreSession();
    }
}