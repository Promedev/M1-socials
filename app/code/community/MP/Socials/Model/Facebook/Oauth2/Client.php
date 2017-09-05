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
 * Class MP_Socials_Model_Facebook_Oauth2_Client
 *
 * @category   MP
 * @package    MP_Socials
 * @author     Merchant Protocol Team <info@merchantprotocol.com>
 */
class MP_Socials_Model_Facebook_Oauth2_Client extends MP_Socials_Model_Oauth2_Client
{
    /**
     * @var string
     */
    protected $redirectUriRoute = 'socials/facebook/connect';

    /**
     * @var string
     */
    protected $xmlPathEnabled      = 'mp_socials/facebook/enabled';
    protected $xmlPathClientId     = 'mp_socials/facebook/client_id';
    protected $xmlPathClientSecret = 'mp_socials/facebook/client_secret';

    /**
     * @var string
     */
    protected $oauth2ServiceUri = 'https://graph.facebook.com';
    protected $oauth2AuthUri    = 'https://graph.facebook.com/oauth/authorize';
    protected $oauth2TokenUri   = 'https://graph.facebook.com/oauth/access_token';

    /**
     * @var array
     */
    protected $scope = [
        'public_profile',
        'email',
        'user_birthday'
    ];

    /**
     * @return void
     */
    protected function responseTreatment()
    {
        /*
         * Per http://tools.ietf.org/html/draft-ietf-oauth-v2-27#section-5.1
         * Facebook should return data using the "application/json" media type.
         * Facebook violates OAuth2 specification and returns string. If this
         * ever gets fixed, following condition will not be used anymore.
         */
        if (empty($this->responseDecoded)) {
            $responseParsed = [];
            parse_str($this->response->getBody(), $responseParsed);
            $this->responseDecoded = json_decode(json_encode($responseParsed));
        }

        parent::responseTreatment();
    }
}
