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
 * Class MP_Socials_Helper_Facebook
 *
 * @category   MP
 * @package    MP_Socials
 * @author     Merchant Protocol Team <info@merchantprotocol.com>
 */
class MP_Socials_Helper_Facebook extends MP_Socials_Helper_Data
{
    /**
     * @const string
     */
    const AUTH_PROVIDER = 'facebook';

    /**
     * @const string
     */
    const ACCOUNT_CSRF        = 'facebook_csrf';
    const ACCOUNT_ID_FIELD    = 'social_facebook_id';
    const ACCOUNT_TOKEN_FIELD = 'social_facebook_token';

    /**
     * @const string
     */
    protected $authProvider      = self::AUTH_PROVIDER;
    protected $accountIdField    = self::ACCOUNT_ID_FIELD;
    protected $accountTokenField = self::ACCOUNT_TOKEN_FIELD;

    /**
     * @param MP_Socials_Model_Facebook_Info $info
     * @return void
     */
    public function validate($info)
    {
        if (!$info->getFirstName()) {
            Mage::throwException(
                $this->__('Sorry, could not retrieve your account first name. Please try again.')
            );
        }

        if (!$info->getLastName()) {
            Mage::throwException(
                $this->__('Sorry, could not retrieve your account last name. Please try again.')
            );
        }
    }
}
