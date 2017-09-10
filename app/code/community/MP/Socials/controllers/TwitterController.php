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
 * Class MP_Socials_TwitterController
 *
 * @category   MP
 * @package    MP_Socials
 * @author     Merchant Protocol Team <info@merchantprotocol.com>
 */
class MP_Socials_TwitterController extends MP_Socials_Controller_Abstract
{
    /**
     * @const string
     */
    protected $authProvider = MP_Socials_Helper_Twitter::AUTH_PROVIDER;

    /**
     * Connect account action
     *
     * @return $this
     * @throws Exception
     */
    public function connectCallback()
    {
        $denied = $this->getRequest()->getParam('denied');
        $code   = $this->getRequest()->getParam('oauth_token');

        /**
         * @var MP_Socials_Helper_Data $helper
         * @var MP_Socials_Model_Info $info
         * @var string $title
         */
        $helper = $this->helper();
        $info   = $helper->getInfoModel();
        $title  = $helper->getTitle();

        if (!($params = $this->getRequest()->getParams())
            || !($requestToken = $this->getSession()->getData('twitter_request_token'))
        ) {
            throw new Exception($this->__('Request token invalid.'));
        }

        if ($denied) {
            throw new Exception($this->__('Access denied.'));
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
}
