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
 * @method $this setButtonTitle(string $value)
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
     * Get button title
     *
     * @return string
     */
    public function getButtonTitle()
    {
        return $this->getConfig('title')
            ? $this->__('Connect with %s', $this->getConfig('title'))
            : $this->_getData('button_title');
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
     * Get hex or rgb color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->getConfig('button_color') ?: 'inherit';
    }

    /**
     * Get icon class
     *
     * @return string
     */
    public function getIconClass()
    {
        return $this->getConfig('icon_class');
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
     * Prepare block to HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->client = Mage::getSingleton(sprintf('mp_socials/%s_oauth2_client', $this->getAuthProvider()));

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
     * @param string $key
     * @return string
     */
    protected function getConfig($key)
    {
        return Mage::getStoreConfig(sprintf('mp_socials/%s/%s', $this->getAuthProvider(), $key));
    }
}
