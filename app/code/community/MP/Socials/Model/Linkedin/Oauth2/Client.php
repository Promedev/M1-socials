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
 * Class MP_Socials_Model_Linkedin_Oauth2_Client
 *
 * @category   MP
 * @package    MP_Socials
 * @author     Merchant Protocol Team <info@merchantprotocol.com>
 */
class MP_Socials_Model_Linkedin_Oauth2_Client extends MP_Socials_Model_Oauth2_Client
{
    /**
     * @var string
     */
    protected $redirectUriRoute = 'socials/linkedin/connect';

    /**
     * @var string
     */
    protected $xmlPathEnabled      = 'mp_socials/linkedin/client_enabled';
    protected $xmlPathClientId     = 'mp_socials/linkedin/client_id';
    protected $xmlPathClientSecret = 'mp_socials/linkedin/client_secret';

    /**
     * @var string
     */
    protected $oauth2ServiceUri = 'https://api.linkedin.com/v1';
    protected $oauth2AuthUri    = 'https://www.linkedin.com/uas/oauth2/authorization';
    protected $oauth2TokenUri   = 'https://www.linkedin.com/uas/oauth2/accessToken';

    /**
     * @var array
     */
    protected $scope = [
        'r_basicprofile',
        'r_emailaddress'
    ];

    /**
     * @return array
     */
    public function getAccessParams()
    {
        return [
            'oauth2_access_token' => $this->token->access_token,
            'format'              => 'json'
        ];
    }

    /**
     * @param null|string $code
     * @return string
     */
    public function getAccessToken($code = null)
    {
        if (empty($this->token)) {
            $this->fetchAccessToken($code);
        }

        return json_encode($this->token);
    }
}
