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
 * Class MP_Socials_FacebookController
 *
 * @category   MP
 * @package    MP_Socials
 * @author     Merchant Protocol Team <info@merchantprotocol.com>
 */
class MP_Socials_FacebookController extends MP_Socials_Controller_Abstract
{
    /**
     * @return $this
     * @throws Exception
     */
    public function connectCallback()
    {
        $errorCode = $this->getRequest()->getParam('error');
        $code      = $this->getRequest()->getParam('code');
        $state     = $this->getRequest()->getParam('state');

        if (!($errorCode || $code) && !$state) {
            Mage::throwException($this->__('Access denied.'));
        }

        if (!$state || $state != $this->helper()->getCsrf()) {
            Mage::throwException($this->__('CSRF invalid.'));
        }

        if ($errorCode) {
            Mage::throwException($this->__('Sorry, "%s" error occurred. Please try again.', $errorCode));
        }

        if ($code) {
            /**
             * @var MP_Socials_Helper_Facebook $helper
             * @var MP_Socials_Model_Facebook_Info $info
             * @var mixed $token
             */
            $helper = Mage::helper('mp_socials/facebook');
            $info   = Mage::getModel('mp_socials/facebook_info');
            $token  = $info->getClient()->getAccessToken($code);

            $info->connect();

            $customersByFacebookId = $helper->getCustomersByAccountId($info->getId());

            if ($this->getCustomerSession()->isLoggedIn()) {
                /**
                 * Logged in user
                 */
                if ($customersByFacebookId->getSize()) {
                    /**
                     * Facebook account already connected to other account - deny
                     */
                    Mage::throwException(
                        $this->__('Your Facebook account is already connected to one of our store accounts.')
                    );
                }

                /**
                 * Connect from account dashboard - attach
                 */
                $customer = $this->getCustomerSession()->getCustomer();

                $helper->connectByAccountId($customer, $info->getId(), $token);

                $this->getCoreSession()->addSuccess(
                    $this->__(
                        'Your Facebook account is now connected to your store account. You can login using our Facebook'
                        . ' Login button or using store account credentials you will receive to your email address.'
                    )
                );

                return $this;
            }

            if ($customersByFacebookId->getSize()) {
                /**
                 * Existing connected user - login
                 */
                $customer = $customersByFacebookId->getFirstItem();

                $helper->loginByCustomer($customer);

                $this->getCoreSession()
                    ->addSuccess($this->__('You have successfully logged in using your Facebook account.'));

                return $this;
            }

            $customersByEmail = $helper->getCustomersByEmail($info->getEmail());

            if ($customersByEmail->getSize()) {
                /**
                 * Email account already exists - attach, login
                 */
                $customer = $customersByEmail->getFirstItem();

                $helper->connectByAccountId($customer, $info->getId(), $token);

                $this->getCoreSession()->addSuccess(
                    $this->__(
                        'We have discovered you already have an account at our store. Your Facebook account is ' .
                        'now connected to your store account.'
                    )
                );

                return $this;
            }

            $helper->validate($info);

            /**
             * New connection - create, attach, login
             */
            $birthday = $info->getBirthday();
            $birthday = Mage::app()->getLocale()
                ->date($birthday, null, null, false)
                ->toString(Varien_Date::DATE_INTERNAL_FORMAT);

            $gender = $info->getGender();

            switch ($gender) {
                case 'male':
                    $gender = 1;

                    break;
                case 'female':
                    $gender = 2;

                    break;
                default:
                    $gender = null;

                    break;
            }

            $helper->connectByCreatingAccount(
                $info->getEmail(),
                $info->getFirstName(),
                $info->getLastName(),
                $info->getId(),
                $token,
                [
                    'dob'    => $birthday,
                    'gender' => $gender
                ]
            );

            $this->getCoreSession()->addSuccess(
                $this->__(
                    'Your Facebook account is now connected to your new user account at our store. ' .
                    'Now you can login using our Facebook Login button.'
                )
            );
        }

        return $this;
    }

    public function disconnectCallback()
    {
        $this->getCoreSession()->addSuccess(
            $this->__('You have successfully disconnected your Facebook account from our store account.')
        );
    }
}
