<?php
/**
 *-------------------------------------------------------------------------s*
 * Outlook Provider
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

class OutlookProvider extends AbstractProvider
{
    public const NAME = 'outlook';

    protected array $scopes = ['User.Read'];

    protected string $scopeSeparator = ' ';

    /**
     * @return string
     */
    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase('https://login.microsoftonline.com/common/oauth2/v2.0/authorize');
    }

    /**
     * @return string
     */
    protected function getTokenUrl(): string
    {
        return 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
    }

    /**
     * @param string $token
     * @param array|null $query
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getUserByToken(string $token, ?array $query = []): array
    {
        $response = $this->getHttpClient()->get(
            'https://graph.microsoft.com/v1.0/me',
            ['headers' => [
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
            Contracts\SHOPWWI_SOC_ID => $user[Contracts\SHOPWWI_SOC_ID] ?? null,
            Contracts\SHOPWWI_SOC_NICKNAME => null,
            Contracts\SHOPWWI_SOC_NAME => $user['displayName'] ?? null,
            Contracts\SHOPWWI_SOC_EMAIL => $user['userPrincipalName'] ?? null,
            Contracts\SHOPWWI_SOC_AVATAR => null,
        ]);
    }

    /**
     * @param string $code
     * @return array
     */
    protected function getTokenFields(string $code): array
    {
        return parent::getTokenFields($code) + [Contracts\SHOPWWI_SOC_GRANT_TYPE => Contracts\SHOPWWI_SOC_AUTHORIZATION_CODE];
    }
}
