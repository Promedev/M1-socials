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
 * Abstract Class MP_Socials_Helper_Data
 *
 * @category   MP
 * @package    MP_Socials
 * @author     Merchant Protocol Team <info@merchantprotocol.com>
 */
abstract class MP_Socials_Controller_Abstract extends Mage_Core_Controller_Front_Action
{
    use MP_Socials_Trait;

    /**
     * Connect account action
     *
     * @return void
     */
    public function connectAction()
    {
        try {
            $this->connectCallback();
        } catch (Exception $e) {
            $this->getCoreSession()->addError($e->getMessage());
        }

        $this->loginPostRedirect();
    }

    /**
     * Connect callback action
     *
     * @return void
     */
    abstract public function connectCallback();

    /**
     * Disconnect account action
     *
     * @return void
     */
    public function disconnectAction()
    {
        $customer = $this->getCustomerSession()->getCustomer();

        try {
            $this->disconnectCallback($customer);
        } catch (Exception $e) {
            $this->getCoreSession()->addError($e->getMessage());
        }

        $this->loginPostRedirect();
    }

    /**
     * Disconnect callback action
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return void
     */
    abstract public function disconnectCallback($customer);

    /**
     * Define target URL and redirect customer after logging in
     *
     * @return void
     */
    protected function loginPostRedirect()
    {
        /**
         * @var Mage_Customer_Model_Session $session
         * @var Mage_Core_Helper_Data $coreHelper
         * @var Mage_Customer_Helper_Data $customerHelper
         */
        $session        = $this->helper()->getCustomerSession();
        $coreHelper     = Mage::helper('core');
        $customerHelper = Mage::helper('customer');

        if (!$session->getData('before_auth_url') || $session->getData('before_auth_url') == Mage::getBaseUrl()) {
            /**
             * Set default URL to redirect customer to
             */
            $session->setBeforeAuthUrl($this->helper()->getAuthRedirectUrl());

            /**
             * Redirect customer to the last page visited after logging in
             */
            if ($session->isLoggedIn()) {
                $redirectToDashboard = Mage::getStoreConfigFlag(
                    Mage_Customer_Helper_Data::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD
                );

                if (!$redirectToDashboard) {
                    $referer = $this->getRequest()->getParam(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME);
                    
                    if ($referer) {
                        /**
                         * Rebuild referer URL to handle the case when SID was changed
                         */
                        $referer = Mage::getModel('core/url')->getRebuiltUrl($coreHelper->urlDecode($referer));

                        if ($this->_isUrlInternal($referer)) {
                            $session->setBeforeAuthUrl($referer);
                        }
                    }
                } else if ($session->getData('after_auth_url')) {
                    $session->setBeforeAuthUrl($session->getData('after_auth_url', true));
                }
            } else {
                $session->setBeforeAuthUrl($customerHelper->getLoginUrl());
            }
        } else if ($session->getData('before_auth_url') == $customerHelper->getLogoutUrl()) {
            $session->setBeforeAuthUrl($customerHelper->getDashboardUrl());
        } else {
            if (!$session->getData('after_auth_url')) {
                $session->setAfterAuthUrl($session->getData('before_auth_url'));
            }

            if ($session->isLoggedIn()) {
                $session->setBeforeAuthUrl($session->getData('after_auth_url', true));
            }
        }

        $this->_redirectUrl($session->getData('before_auth_url', true));
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
        return $this->helper()->getCoreSession();
    }
}
