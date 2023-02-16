<?php
/**
 *-------------------------------------------------------------------------s*
 * Figma Provider
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

class GiteeProvider extends AbstractProvider
{
    public const NAME = 'gitee';

    protected array $scopes = ['user_info'];

    /**
     * @return string
     */
    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase('https://gitee.com/oauth/authorize');
    }

    /**
     * @return string
     */
    protected function getTokenUrl(): string
    {
        return 'https://gitee.com/oauth/token';
    }

    /**
     * @param string $token
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getUserByToken(string $token): array
    {
        $userUrl = 'https://gitee.com/api/v5/user';
        $response = $this->getHttpClient()->get($userUrl, [
            'query' => [
                Contracts\SHOPWWI_SOC_ACCESS_TOKEN => $token,
            ],
        ]);

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
            Contracts\SHOPWWI_SOC_NICKNAME => $user['login'] ?? null,
            Contracts\SHOPWWI_SOC_NAME => $user[Contracts\SHOPWWI_SOC_NAME] ?? null,
            Contracts\SHOPWWI_SOC_EMAIL => $user[Contracts\SHOPWWI_SOC_EMAIL] ?? null,
            Contracts\SHOPWWI_SOC_AVATAR => $user['avatar_url'] ?? null,
        ]);
    }

    /**
     * @param string $code
     * @return array
     */
    protected function getTokenFields(string $code): array
    {
        return [
            Contracts\SHOPWWI_SOC_CLIENT_ID => $this->getClientId(),
            Contracts\SHOPWWI_SOC_CLIENT_SECRET => $this->getClientSecret(),
            Contracts\SHOPWWI_SOC_CODE => $code,
            Contracts\SHOPWWI_SOC_REDIRECT_URI => $this->redirectUrl,
            Contracts\SHOPWWI_SOC_GRANT_TYPE => Contracts\SHOPWWI_SOC_AUTHORIZATION_CODE,
        ];
    }
}
