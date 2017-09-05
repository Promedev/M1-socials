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
 * Class MP_Socials_Model_Resource_Setup
 *
 * @category   MP
 * @package    MP_Socials
 * @author     Merchant Protocol Team <info@merchantprotocol.com>
 */
class MP_Socials_Model_Resource_Setup extends Mage_Eav_Model_Entity_Setup
{
    /**
     * @const string
     */
    const CUSTOMER_ENTITY = 'customer';

    /**
     * @var array
     */
    protected $customerAttributes = [];

    /**
     * @param array $customerAttributes
     * @return $this
     */
    public function setCustomerAttributes($customerAttributes)
    {
        $this->customerAttributes = $customerAttributes;

        return $this;
    }

    /**
     * @return Mage_Eav_Model_Entity_Setup
     */
    public function installCustomerAttributes()
    {
        foreach ($this->customerAttributes as $code => $attr) {
            $this->addAttribute(self::CUSTOMER_ENTITY, $code, $attr);
        }

        return $this;
    }

    /**
     * @return Mage_Eav_Model_Entity_Setup
     */
    public function removeCustomerAttributes()
    {
        foreach ($this->customerAttributes as $code => $attr) {
            $this->removeAttribute(self::CUSTOMER_ENTITY, $code);
        }

        return $this;
    }
}
