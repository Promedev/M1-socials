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
 * Class MP_Socials_Model_Twitter_Info
 *
 * @category   MP
 * @package    MP_Socials
 * @author     Merchant Protocol Team <info@merchantprotocol.com>
 */
class MP_Socials_Model_Twitter_Info extends MP_Socials_Model_Info
{
    /**
     * @var string
     */
    protected $requestUri = '/account/verify_credentials.json';

    /**
     * @var array
     */
    protected $requestParams = [
        'skip_status' => true
    ];

    /**
     * @var array
     */
    protected $responseMap = [
        'picture_url' => 'profile_image_url'
    ];

    /**
     * MP_Socials_Model_Twitter_Info constructor
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        $this->client = Mage::getSingleton('mp_socials/twitter_oauth2_client');
    }

    /**
     * Connect with the social network
     *
     * @return $this
     */
    public function connect()
    {
        parent::connect();

        /**
         * Twitter doesn't allow email access trough API
         */
        $this->setData('email', sprintf('%s@twitter-user.com', strtolower($this->getData('screen_name'))));
        $this->setData('profile_url', sprintf('https://twitter.com/%s', $this->getData('screen_name')));

        $name = explode(' ', $this->getData('name'), 2);

        if (count($name) > 1) {
            $firstname = $name[0];
            $lastname  = $name[1];
        } else {
            $firstname = $name[0];
            $lastname  = $name[0];
        }

        $this->setData('firstname', $firstname);
        $this->setData('lastname', $lastname);

        return $this;
    }
}
