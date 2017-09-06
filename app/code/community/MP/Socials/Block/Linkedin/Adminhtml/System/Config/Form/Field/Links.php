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
 * Class MP_Socials_Block_Linkedin_Adminhtml_System_Config_Form_Field_Links
 *
 * @category   MP
 * @package    MP_Socials
 * @author     Merchant Protocol Team <info@merchantprotocol.com>
 */
class MP_Socials_Block_Linkedin_Adminhtml_System_Config_Form_Field_Links
    extends MP_Socials_Block_Adminhtml_System_Config_Form_Field_Links
{
    /**
     * Get auth provider link
     *
     * @return string
     */
    protected function getAuthProviderLink()
    {
        return $this->__('LinkedIn Developer Network');
    }

    /**
     * Get auth provider link href
     *
     * @return string
     */
    protected function getAuthProviderLinkHref()
    {
        return 'https://developer.linkedin.com/';
    }
}
