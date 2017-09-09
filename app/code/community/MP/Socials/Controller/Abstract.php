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
     * @const string
     */
    protected $authProvider;

    /**
     * Get data helper object
     *
     * @return MP_Socials_Helper_Data|Mage_Core_Helper_Abstract
     */
    public function helper()
    {
        return Mage::helper(sprintf('mp_socials/%s', $this->authProvider));
    }

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
            $this->getSession()->addError($e->getMessage());
        }

        $this->loginPostRedirect();
    }

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
            $this->getSession()->addError($e->getMessage());
        }

        $this->loginPostRedirect();
    }

    /**
     * Connect account action
     *
     * @return $this
     * @throws Exception
     */
    public function connectCallback()
    {
        $errorCode = $this->getRequest()->getParam('error');
        $code      = $this->getRequest()->getParam('code');
        $state     = $this->getRequest()->getParam('state');

        /**
         * @var MP_Socials_Helper_Data $helper
         * @var MP_Socials_Model_Info $info
         * @var string $title
         */
        $helper = $this->helper();
        $info   = $helper->getInfoModel();
        $title  = $helper->getTitle();

        if (!($errorCode || $code) && !$state) {
            throw new Exception($this->__('Access denied.'));
        }

        if (!$state || $state != $this->helper()->getCsrf()) {
            throw new Exception($this->__('CSRF invalid.'));
        }

        if ($errorCode) {
            throw new Exception($this->__('Sorry, "%s" error occurred. Please try again.', $errorCode));
        }

        if (!$code) {
            throw new Exception($this->__('Code invalid.'));
        }

        /**
         * @var mixed $token
         */
        $token = $info->getClient()->getAccessToken($code);

        /**
         * Connect with the social network
         */
        $info->connect();

        $customersByAuthId = $helper->getCustomersByAuthId($info->getId());

        if ($this->getCustomerSession()->isLoggedIn()) {
            if ($customersByAuthId->getSize()) {
                throw new Exception(
                    $this->__('Your %s account is already connected to one of our store accounts.', $title)
                );
            }

            /** @var Mage_Customer_Model_Customer $customer */
            $customer = $this->getCustomerSession()->getCustomer();
            $helper->connectByAuthId($customer, $info->getId(), $token);

            $this->getSession()->addSuccess(
                $this->__(
                    'Your %s account is now connected to your store account. ' .
                    'You can login using our %s Login button or using store account credentials you will ' .
                    'receive to your email address.',
                    $title,
                    $title
                )
            );

            return $this;
        }

        if ($customersByAuthId->getSize()) {
            /** @var Mage_Customer_Model_Customer $customer */
            $customer = $customersByAuthId->getFirstItem();
            $helper->loginByCustomer($customer);

            $this->getSession()->addSuccess(
                $this->__('You have successfully logged in using your %s account.', $title)
            );

            return $this;
        }

        /** @var Mage_Customer_Model_Resource_Customer_Collection $customersByEmail */
        $customersByEmail = $helper->getCustomersByEmail($info->getEmail());

        if ($customersByEmail->getSize()) {
            /** @var Mage_Customer_Model_Customer $customer */
            $customer = $customersByEmail->getFirstItem();
            $helper->connectByAuthId($customer, $info->getId(), $token);

            $this->getSession()->addSuccess(
                $this->__(
                    'We have discovered you already have an account at our store. Your %s account is ' .
                    'now connected to your store account.',
                    $title
                )
            );

            return $this;
        }

        $helper->validate($info);
        $helper->connectByCreatingAccount($info, $token);

        $this->getSession()->addSuccess(
            $this->__(
                'Your %s account is now connected to your new user account at our store. ' .
                'Now you can login using our %s Login button.',
                $title,
                $title
            )
        );

        return $this;
    }

    /**
     * Disconnect action
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return void
     */
    public function disconnectCallback($customer)
    {
        /**
         * @var MP_Socials_Helper_Data $helper
         * @var string $title
         */
        $helper = $this->helper();
        $title  = $helper->getTitle();

        $helper->disconnect($customer);

        $this->getSession()->addSuccess(
            $this->__('You have successfully disconnected your %s account from our store account.', $title)
        );
    }

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
    protected function getSession()
    {
        return $this->helper()->getSession();
    }
}
