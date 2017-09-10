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
 * Class MP_Socials_Block_Button
 *
 * @method $this setAuthProvider(string $value)
 *
 * @category   MP
 * @package    MP_Socials
 * @author     Merchant Protocol Team <info@merchantprotocol.com>
 */
class MP_Socials_Block_Button extends Mage_Core_Block_Template
{
    use MP_Socials_Trait;

    /**
     * @var MP_Socials_Model_Oauth2_Client
     */
    protected $client;

    /**
     * MP_Socials_Block_Button constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('mp/socials/button.phtml');
    }

    /**
     * Get auth provider
     *
     * @return string
     */
    public function getAuthProvider()
    {
        $authProvider = trim(strtolower($this->_getData('auth_provider')));

        if (!$authProvider) {
            Mage::throwException($this->__('Auth Provider is empty.'));
        }

        return $authProvider;
    }

    /**
     * Get button url
     *
     * @return string
     */
    public function getButtonUrl()
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Get button title
     *
     * @return string
     */
    public function getButtonTitle()
    {
        return $this->helper()->getTitle();
    }

    /**
     * Get hex or rgb color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->helper()->getButtonColor();
    }

    /**
     * Get icon class
     *
     * @return string
     */
    public function getIconClass()
    {
        return $this->helper()->getIconClass();
    }

    /**
     * Get social helper
     *
     * @return MP_Socials_Helper_Data|Mage_Core_Helper_Abstract
     */
    public function helper()
    {
        return Mage::helper('mp_socials/' . $this->getAuthProvider());
    }

    /**
     * Prepare block to HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->client = Mage::getModel(sprintf('mp_socials/%s_oauth2_client', $this->getAuthProvider()));

        if (!$this->client instanceof MP_Socials_Model_Oauth2_Client || !$this->client->isEnabled()) {
            return '';
        }

        /**
         * CSRF for security reasons
         */
        $this->helper()->addCsrf();
        $this->helper()->setAuthRedirectUrl(Mage::helper('core/url')->getCurrentUrl());
        $this->client->setState($this->helper()->getCsrf());

        return parent::_toHtml();
    }

    /**
     * Get store config data
     *
     * @param string $property
     * @return string
     */
    protected function getConfig($property)
    {
        return $this->helper()->getConfig($property);
    }
}
