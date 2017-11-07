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
 * Class MP_Socials_Model_Linkedin_Info
 *
 * @category   MP
 * @package    MP_Socials
 * @author     Merchant Protocol Team <info@merchantprotocol.com>
 */
class MP_Socials_Model_Linkedin_Info extends MP_Socials_Model_Info
{
    /**
     * @var string
     */
    protected $requestUri = '/people';

    /**
     * @var array
     */
    protected $requestParams = array(
        '~' => ''
    );

    /**
     * @var array
     */
    protected $requestFields = array(
        'id',
        'first-name',
        'last-name',
        'email-address',
        'picture-url',
        'public-profile-url',
        'site-standard-profile-request'
    );

    /**
     * @var array
     */
    protected $responseMap = array(
        'email'       => 'emailAddress',
        'firstname'   => 'firstName',
        'lastname'    => 'lastName',
        'profile_url' => 'publicProfileUrl',
        'picture_url' => 'pictureUrl'
    );


    /**
     * MP_Socials_Model_Linkedin_Info constructor
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        $this->client = Mage::getSingleton('mp_socials/linkedin_oauth2_client');
    }
}
