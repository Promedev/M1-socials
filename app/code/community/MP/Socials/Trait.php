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
 * Trait MP_Socials_Trait
 *
 * @category   MP
 * @package    MP_Socials
 * @author     Merchant Protocol Team <info@merchantprotocol.com>
 */
trait MP_Socials_Trait
{
    /**
     * Get social modal object
     *
     * @param null|string $authProvider
     * @return MP_Socials_Model_Social
     */
    public function getSocialModel($authProvider = null)
    {
        $object = Mage::getModel('mp_socials/social')
            ->setStore($this->helper()->getStore())
            ->setAuthProvider($authProvider);

        return $object;
    }

    /**
     * Get social helper object
     *
     * @param null|string $authProvider
     * @return MP_Socials_Helper_Data
     * @throws Exception
     */
    public function getSocialHelper($authProvider = null)
    {
        $helper = Mage::helper(sprintf('mp_socials/%s', $authProvider));

        if (!$helper instanceof MP_Socials_Helper_Data) {
            throw new Exception($this->__('Provider helper not found.'));
        }

        return $helper;
    }

    /**
     * Get data helper object
     *
     * @return MP_Socials_Helper_Data
     */
    public function helper()
    {
        return Mage::helper('mp_socials');
    }
}
