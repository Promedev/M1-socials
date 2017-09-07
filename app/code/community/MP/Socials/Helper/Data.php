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
    use MP_Socials_Trait;

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
     * @var array
     */
    protected $authProviders = [
        self::GOOGLE_PROVIDER,
        self::FACEBOOK_PROVIDER,
        self::TWITTER_PROVIDER,
        self::LINKEDIN_PROVIDER
    ];

    /**
     * @todo Not implemented
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * Connect by auth ID
     *
     * @param Mage_Customer_Model_Customer|Varien_Object $customer
     * @param string $authId
     * @param mixed $authToken
     * @return $this
     */
    public function connectByAuthId($customer, $authId, $authToken)
    {
        $object = $this->getSocialModel($this->authProvider)->loadByAuthId($authId);
        $object->setAuthId($authId)
            ->setCustomerId($customer->getId())
            ->setAuthToken($authToken)
            ->save();

        $this->getCustomerSession()->setCustomerAsLoggedIn($customer);

        return $this;
    }

    /**
     * Connect by creating account
     *
     * @param string $email
     * @param string $firstname
     * @param string $lastname
     * @param string $authId
     * @param mixed $authToken
     * @param array $extra
     * @return $this
     */
    public function connectByCreatingAccount($email, $firstname, $lastname, $authId, $authToken, array $extra = [])
    {
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer');
        $customer->setStore($this->getStore());
        $customer->setData('email', $email);
        $customer->setData('firstname', $firstname);
        $customer->setData('lastname', $lastname);
        $customer->setPassword($customer->generatePassword(10));

        foreach ($extra as $key => $value) {
            $customer->setData($key, $value);
        }

        $customer->setData('confirmation', null);
        $customer->save();
        $customer->sendNewAccountEmail('confirmed', '', $customer->getStore()->getId());

        $this->getSocialModel($this->authProvider)->loadByAuthId($authId)
            ->setCustomerId($customer->getId())
            ->setAuthId($authId)
            ->setAuthToken($authToken)
            ->save();

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
     * Get customer collection auth ID
     *
     * @param string $authId
     * @return Mage_Customer_Model_Resource_Customer_Collection
     */
    public function getCustomersByAuthId($authId)
    {
        /** @var MP_Socials_Model_Resource_Social_Collection $socials */
        $socials = Mage::getResourceModel('mp_socials/social_collection');
        $socials->addFieldToFilter('auth_provider', $this->authProvider);
        $socials->addFieldToFilter('auth_id', $authId);

        $customerIds = [];

        /** @var MP_Socials_Model_Social $social */
        foreach ($socials as $social) {
            $customerIds[] = $social->getCustomerId();
        }

        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer');

        /** @var Mage_Customer_Model_Resource_Customer_Collection $collection */
        $collection = $customer->getCollection()
            ->addAttributeToFilter('entity_id', ['in' => $customerIds])
            ->setPageSize(1);

        if ($customer->getSharingConfig()->isWebsiteScope()) {
            $collection->addAttributeToFilter('website_id', $this->getWebsiteId());
        }

        return $collection;
    }

    /**
     * Get customer collection by email
     *
     * @param string $email
     * @return Mage_Customer_Model_Resource_Customer_Collection
     */
    public function getCustomersByEmail($email)
    {
        /**
         * @var Mage_Customer_Model_Customer $customer
         * @var Mage_Customer_Model_Resource_Customer_Collection $collection
         */
        $customer   = Mage::getModel('customer/customer');
        $collection = $customer->getCollection()->addFieldToFilter('email', $email)->setPageSize(1);

        if ($customer->getSharingConfig()->isWebsiteScope()) {
            $collection->addAttributeToFilter('website_id', $this->getWebsiteId());
        }

        if ($this->getCustomerSession()->isLoggedIn()) {
            $collection->addFieldToFilter('entity_id', ['neq' => $this->getCustomerSession()->getCustomerId()]);
        }

        return $collection;
    }

    /**
     * Get proper dimensions picture url
     *
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
     * Disconnect customer from current auth provider
     *
     * @param Mage_Customer_Model_Customer|Varien_Object $customer
     * @return $this
     */
    public function disconnect($customer)
    {
        /**
         * @var MP_Socials_Model_Social $object
         * @var MP_Socials_Model_Info $info
         */
        $object = $this->getSocialModel($this->authProvider)->loadByCustomerId($customer->getId());
        $info   = Mage::getSingleton(sprintf('mp_socials/%s_info/', $object->getAuthProvider()));

        if (!$info instanceof MP_Socials_Model_Info) {
            Mage::throwException($this->__('Class "%s" not found.', MP_Socials_Model_Info::class));
        }

        try {
            $info->setAccessToken($object->getAuthToken());
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
            . $object->getAuthId();

        if (file_exists($pictureFilename)) {
            @unlink($pictureFilename);
        }

        $object->delete();

        return $this;
    }

    /**
     * Validate customer info
     *
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
     * Add CSRF code in customer session
     * For security reasons
     *
     * @return $this
     */
    public function addCsrf()
    {
        $uniqueHash = Mage::helper('core')->uniqHash($this->getWebsiteId() . '_' . $this->getStoreId() . '_');
        $this->getCustomerSession()->setData($this->authProvider . '_csrf', $uniqueHash);

        return $this;
    }

    /**
     * Get CSRF code
     *
     * @return string
     */
    public function getCsrf()
    {
        return $this->getCustomerSession()->getData($this->authProvider . '_csrf');
    }

    /**
     * Set auth redirect url
     *
     * @param string $redirectUrl
     * @return $this
     */
    public function setAuthRedirectUrl($redirectUrl)
    {
        $this->getCustomerSession()->setData(self::AUTH_REDIRECT_URL_KEY, $redirectUrl);

        return $this;
    }

    /**
     * Get auth redirect url
     *
     * @return string
     */
    public function getAuthRedirectUrl()
    {
        return $this->getCustomerSession()->getData(self::AUTH_REDIRECT_URL_KEY);
    }

    /**
     * Get available social options
     * This method will be used by third-party extensions
     *
     * @return array
     */
    public function getSocialOptions()
    {
        $options = [];

        foreach ($this->authProviders as $authProvider) {
            $config = Mage::getStoreConfig('mp_socials/' . $authProvider);

            if (((bool) $config['review_enabled']) !== true) {
                continue;
            }

            $options[$authProvider] = $config;
        }

        return $options;
    }

    /**
     * Get HTTP request
     *
     * @return Mage_Core_Controller_Request_Http
     */
    public function getRequest()
    {
        return Mage::app()->getRequest();
    }

    /**
     * Get HTTP response
     *
     * @return Zend_Controller_Response_Http
     */
    public function getResponse()
    {
        return Mage::app()->getResponse();
    }

    /**
     * Get current store
     *
     * @param null|integer $storeId
     * @return Mage_Core_Model_Store
     */
    public function getStore($storeId = null)
    {
        return Mage::app()->getStore($storeId);
    }

    /**
     * Get current store id
     *
     * @return integer
     */
    public function getStoreId()
    {
        return $this->getStore()->getId();
    }

    /**
     * Get current website id
     *
     * @param null|integer $websiteId
     * @return Mage_Core_Model_Website
     */
    public function getWebsite($websiteId = null)
    {
        return Mage::app()->getWebsite($websiteId);
    }

    /**
     * Get current website id
     *
     * @return int
     */
    public function getWebsiteId()
    {
        return $this->getWebsite()->getId();
    }

    /**
     * Get customer session object
     *
     * @return Mage_Customer_Model_Session
     */
    public function getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Get core session object
     *
     * @return Mage_Core_Model_Session
     */
    public function getCoreSession()
    {
        return Mage::getSingleton('core/session');
    }
}
