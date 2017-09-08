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
 * Class MP_Socials_Helper_Google
 *
 * @category   MP
 * @package    MP_Socials
 * @author     Merchant Protocol Team <info@merchantprotocol.com>
 */
class MP_Socials_Helper_Google extends MP_Socials_Helper_Data
{
    /**
     * @const string
     */
    const AUTH_PROVIDER = 'google';

    /**
     * @const string
     */
    protected $authProvider = self::AUTH_PROVIDER;
    
    /**
     *
     * @return Ambigous <multitype:, mixed>
     */
    public function getPlaceId()
    {
        return $this->getConfig('place_id');
    }
    
    /**
     *
     * @return string
     */
    public function getReviewLink()
    {
        return $this->escapeUrl('https://search.google.com/local/writereview?placeid='.$this->getPlaceId());
    }
}
