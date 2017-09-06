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
 * Class MP_Socials_Model_Social
 *
 * Getters
 *
 * @method int getCustomerId()
 * @method string getAuthId()
 * @method string getAuthProvider()
 * @method bool hasAuthProvider()
 * @method int getStoreId()
 * @method int getWebsiteId()
 * @method bool hasWebsiteId()
 * @method string getCreatedAt()
 * @method string getUpdatedAt()
 *
 * Setters
 *
 * @method $this setCustomerId(int $value)
 * @method $this setAuthId(string $value)
 * @method $this setAuthProvider(string $value)
 * @method $this setStoreId(int $value)
 * @method $this setWebsiteId(int $value)
 * @method $this setCreatedAt($value)
 * @method $this setUpdatedAt($value)
 *
 * @category   MP
 * @package    MP_Socials
 * @author     Merchant Protocol Team <info@merchantprotocol.com>
 */
class MP_Socials_Model_Social extends Mage_Core_Model_Abstract
{
    use MP_Socials_Trait;

    /**
     * @const string
     */
    const ENTITY = 'social';

    /**
     * @var string
     * @var string
     */
    protected $_eventPrefix = 'socials_social';
    protected $_eventObject = 'social';

    /**
     * Initialize constructor
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        $this->_init('mp_socials/social');
    }

    /**
     * Set store to review
     *
     * @param Mage_Core_Model_Store $store
     * @return $this
     */
    public function setStore(Mage_Core_Model_Store $store)
    {
        $this->setStoreId($store->getId());
        $this->setWebsiteId($store->getWebsiteId());

        return $this;
    }

    /**
     * Set auth token
     *
     * @param mixed $authToken
     * @return $this
     */
    public function setAuthToken($authToken)
    {
        $this->setData('auth_token', Zend_Serializer::serialize($authToken));

        return $this;
    }

    /**
     * Get auth token
     *
     * @return mixed
     */
    public function getAuthToken()
    {
        return Zend_Serializer::unserialize($this->getData('auth_token'));
    }

    /**
     * Get customer by identifier
     *
     * @return Mage_Core_Model_Abstract|Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        return Mage::getSingleton('customer/customer')->load($this->getCustomerId());
    }

    /**
     * Get store
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        return $this->helper()->getStore($this->getStoreId());
    }

    /**
     * Get website
     *
     * @return Mage_Core_Model_Website
     */
    public function getWebsite()
    {
        return $this->helper()->getWebsite($this->getWebsiteId());
    }

    /**
     * Get social by customer_id
     *
     * @param int $customerId
     * @return $this
     */
    public function loadByCustomerId($customerId)
    {
        $this->getResource()->load($this, 'customer_id', $customerId);

        return $this;
    }

    /**
     * Get social by auth_id
     *
     * @param string $authId
     * @return $this
     */
    public function loadByAuthId($authId)
    {
        $this->getResource()->load($this, 'auth_id', $authId);

        return $this;
    }

    /**
     * Get social by auth_token
     *
     * @param string $authToken
     * @return $this
     */
    public function loadByAuthToken($authToken)
    {
        $this->getResource()->load($this, 'auth_token', $authToken);

        return $this;
    }
}
