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
 * Class MP_Socials_Helper_Data
 *
 * @category   MP
 * @package    MP_Socials
 * @author     Merchant Protocol Team <info@merchantprotocol.com>
 */
class MP_Socials_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @const string
     */
    const GOOGLE_PROVIDER   = 'google';
    const FACEBOOK_PROVIDER = 'facebook';
    const TWITTER_PROVIDER  = 'twitter';
    const LINKEDIN_PROVIDER = 'linkedin';

    /**
     * @const string
     */
    const AUTH_REDIRECT_URL_KEY = 'auth_redirect_url';
    
    /**
     * @var string
     */
    protected $authProvider;

    /**
     * @const string
     */
    protected $accountIdField;

    /**
     * @const string
     */
    protected $accountTokenField;

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * @return $this
     */
    public function addCsrf()
    {
        $this->getCustomerSession()->setData($this->authProvider . '_csrf', md5(uniqid(rand(), true)));

        return $this;
    }

    /**
     * @return string
     */
    public function getCsrf()
    {
        return $this->getCustomerSession()->getData($this->authProvider . '_csrf');
    }

    /**
     * @param Mage_Customer_Model_Customer|Varien_Object $customer
     * @param string $accountId
     * @param mixed $token
     * @return $this
     */
    public function connectByAccountId(
        $customer,
        $accountId,
        $token
    ) {
        $customer->setData($this->accountIdField, $accountId);
        $customer->setData($this->accountTokenField, serialize($token));
        $customer->save();

        $this->getCustomerSession()->setCustomerAsLoggedIn($customer);

        return $this;
    }

    /**
     * @param string $email
     * @param string $firstname
     * @param string $lastname
     * @param string $accountId
     * @param mixed $token
     * @param array $extra
     * @return $this
     */
    public function connectByCreatingAccount(
        $email,
        $firstname,
        $lastname,
        $accountId,
        $token,
        array $extra = []
    ) {
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer');
        $customer->setStore(Mage::app()->getStore());
        $customer->setData('email', $email);
        $customer->setData('firstname', $firstname);
        $customer->setData('lastname', $lastname);
        $customer->setData($this->accountIdField, $accountId);
        $customer->setData($this->accountTokenField, serialize($token));
        $customer->setPassword($customer->generatePassword(10));

        foreach ($extra as $key => $value) {
            $customer->setData($key, $value);
        }

        $customer->setData('confirmation', null);
        $customer->save();
        $customer->sendNewAccountEmail('confirmed', '', $customer->getStore()->getId());

        $this->getCustomerSession()->setCustomerAsLoggedIn($customer);

        return $this;
    }

    /**
     * @param Mage_Customer_Model_Customer|Varien_Object $customer
     * @return $this
     */
    public function loginByCustomer($customer)
    {
        if ($customer->getData('confirmation')) {
            $customer->setData('confirmation', null);
            $customer->save();
        }

        $this->getCustomerSession()->setCustomerAsLoggedIn($customer);

        return $this;
    }

    /**
     * @param string $accountId
     * @return Mage_Customer_Model_Resource_Customer_Collection
     */
    public function getCustomersByAccountId($accountId)
    {
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer');

        /** @var Mage_Customer_Model_Resource_Customer_Collection $collection */
        $collection = $customer->getCollection()
            ->addAttributeToSelect($this->accountTokenField)
            ->addAttributeToFilter($this->accountIdField, $accountId)
            ->setPageSize(1);

        if ($customer->getSharingConfig()->isWebsiteScope()) {
            $collection->addAttributeToFilter('website_id', $this->getStoreId());
        }

        return $collection;
    }

    /**
     * @param string $email
     * @return Mage_Customer_Model_Resource_Customer_Collection
     */
    public function getCustomersByEmail($email)
    {
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer');

        /** @var Mage_Customer_Model_Resource_Customer_Collection $collection */
        $collection = $customer->getCollection()
            ->addFieldToFilter('email', $email)
            ->setPageSize(1);

        if ($customer->getSharingConfig()->isWebsiteScope()) {
            $collection->addAttributeToFilter('website_id', $this->getStoreId());
        }

        if ($this->getCustomerSession()->isLoggedIn()) {
            $collection->addFieldToFilter('entity_id', ['neq' => $this->getCustomerSession()->getCustomerId()]);
        }

        return $collection;
    }

    /**
     * @param string $accountId
     * @param string $pictureUrl
     * @return null|string
     */
    public function getProperDimensionsPictureUrl($accountId, $pictureUrl)
    {
        $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)
            . 'mp'
            . '/'
            . 'socials'
            . '/'
            . $this->authProvider
            . '/'
            . $accountId;

        $filename = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA)
            . DS
            . 'mp'
            . DS
            . 'socials'
            . DS
            . $this->authProvider
            . DS
            . $accountId;

        $directory = dirname($filename);

        if (!file_exists($directory) || !is_dir($directory)) {
            if (!@mkdir($directory, 0777, true)) {
                return null;
            }
        }

        if (!file_exists($filename)
            || (file_exists($filename) && (time() - filemtime($filename) >= 3600))
        ) {
            $client = new Zend_Http_Client($pictureUrl);
            $client->setStream();
            $response = $client->request(Zend_Http_Client::GET);

            stream_copy_to_stream($response->{'getStream'}(), fopen($filename, 'w'));

            $imageObj = new Varien_Image($filename);
            $imageObj->constrainOnly(true);
            $imageObj->keepAspectRatio(true);
            $imageObj->keepFrame(false);
            $imageObj->resize(150, 150);
            $imageObj->save($filename);
        }

        return $url;
    }

    /**
     * @param Mage_Customer_Model_Customer|Varien_Object $customer
     * @return $this
     */
    public function disconnect($customer)
    {
        /** @var MP_Socials_Model_Info $info */
        $info = Mage::getSingleton('mp_socials/' . $this->authProvider . '_info');

        try {
            $info->setAccessToken(unserialize($customer->getData($this->accountTokenField)));
            $info->disconnect();
        } catch (Exception $e) {}

        $pictureFilename = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA)
            . DS
            . 'mp'
            . DS
            . 'socials'
            . DS
            . $this->authProvider
            . DS
            . $customer->getData($this->accountIdField);

        if (file_exists($pictureFilename)) {
            @unlink($pictureFilename);
        }

        $customer->setData($this->accountIdField, null);
        $customer->setData($this->accountTokenField, null);
        $customer->save();

        return $this;
    }

    /**
     * @param MP_Socials_Model_Facebook_Info $info
     * @return $this
     */
    public function validate($info)
    {
        if (!$info->getFirstName()) {
            Mage::throwException($this->__('Could not retrieve your account first name.'));
        }

        if (!$info->getLastName()) {
            Mage::throwException($this->__('Could not retrieve your account last name.'));
        }

        if (!$info->getEmail()) {
            Mage::throwException($this->__('Could not retrieve your account email.'));
        }

        return $this;
    }

    /**
     * @param string $redirectUrl
     * @return $this
     */
    public function setAuthRedirectUrl($redirectUrl)
    {
        $this->getCustomerSession()->setData(self::AUTH_REDIRECT_URL_KEY, $redirectUrl);

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthRedirectUrl()
    {
        return $this->getCustomerSession()->getData(self::AUTH_REDIRECT_URL_KEY);
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return Mage::app()->getStore()->getId();
    }

    /**
     * @return Mage_Customer_Model_Session
     */
    public function getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * @return Mage_Core_Model_Session
     */
    public function getCoreSession()
    {
        return Mage::getSingleton('core/session');
    }
}
