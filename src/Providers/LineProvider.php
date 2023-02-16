<?php
/**
 *-------------------------------------------------------------------------s*
 * Line Provider
 *-------------------------------------------------------------------------h*
 * @copyright  Copyright (c) 2015-2023 Shopwwi Inc. (http://www.shopwwi.com)
 *-------------------------------------------------------------------------o*
 * @license    http://www.shopwwi.com        s h o p w w i . c o m
 *-------------------------------------------------------------------------p*
 * @link       http://www.shopwwi.com by 无锡豚豹科技
 *-------------------------------------------------------------------------w*
 * @since      ShopWWI智能管理系统
 *-------------------------------------------------------------------------w*
 * @author      8988354@qq.com TycoonSong
 *-------------------------------------------------------------------------i*
 */

namespace Shopwwi\WebmanSocialite\Providers;

use Shopwwi\WebmanSocialite\AbstractUser;
use Shopwwi\WebmanSocialite\Exceptions;
use Shopwwi\WebmanSocialite\Contracts;

/**
 * @see https://developers.line.biz/en/docs/line-login/integrate-line-login/ [Integrating LINE Login with your web app]
 */
class LineProvider extends AbstractProvider
{
    public const NAME = 'line';

    protected string $baseUrl = 'https://api.line.me/oauth2/';

    protected string $version = 'v2.1';

    protected array $scopes = ['profile'];

    protected function getAuthUrl(): string
    {
        $this->state = $this->state ?: \md5(\uniqid(Contracts\SHOPWWI_SOC_STATE, true));

        return $this->buildAuthUrlFromBase('https://access.line.me/oauth2/'.$this->version.'/authorize');
    }

    protected function getTokenUrl(): string
    {
        return $this->baseUrl.$this->version.'/token';
    }

    /**
     * @param string $code
     * @return array
     */
    protected function getTokenFields(string $code): array
    {
        return parent::getTokenFields($code) + [Contracts\SHOPWWI_SOC_GRANT_TYPE => Contracts\SHOPWWI_SOC_AUTHORIZATION_CODE];
    }

    /**
     * @param string $token
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getUserByToken(string $token): array
    {
        $response = $this->getHttpClient()->get(
            'https://api.line.me/v2/profile',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$token,
                ],
            ]
        );

        return $this->fromJsonBody($response);
    }

    /**
     * @param array $user
     * @return Contracts\User
     */
    protected function mapUserToObject(array $user): Contracts\User
    {
        return new AbstractUser([
            Contracts\SHOPWWI_SOC_ID => $user['userId'] ?? null,
            Contracts\SHOPWWI_SOC_NAME => $user['displayName'] ?? null,
            Contracts\SHOPWWI_SOC_NICKNAME => $user['displayName'] ?? null,
            Contracts\SHOPWWI_SOC_AVATAR => $user['pictureUrl'] ?? null,
            Contracts\SHOPWWI_SOC_EMAIL => null,
        ]);
    }
}
