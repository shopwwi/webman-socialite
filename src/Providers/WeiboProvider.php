<?php
/**
 *-------------------------------------------------------------------------s*
 * Weibo Provider
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
 * @see http://open.weibo.com/wiki/%E6%8E%88%E6%9D%83%E6%9C%BA%E5%88%B6%E8%AF%B4%E6%98%8E [OAuth 2.0 授权机制说明]
 */
class WeiboProvider extends AbstractProvider
{
    public const NAME = 'weibo';

    protected string $baseUrl = 'https://api.weibo.com';

    protected array $scopes = [Contracts\SHOPWWI_SOC_EMAIL];

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->baseUrl.'/oauth2/authorize');
    }

    protected function getTokenUrl(): string
    {
        return $this->baseUrl.'/2/oauth2/access_token';
    }

    /**
     * @param string $code
     * @return array
     */
    protected function getTokenFields(string $code): array
    {
        return parent::getTokenFields($code) + [
            Contracts\SHOPWWI_SOC_GRANT_TYPE => Contracts\SHOPWWI_SOC_AUTHORIZATION_CODE,
        ];
    }

    /**
     * @throws Exceptions\InvalidTokenException
     */
    protected function getUserByToken(string $token): array
    {
        $uid = $this->getTokenPayload($token)['uid'] ?? null;

        if (empty($uid)) {
            throw new Exceptions\InvalidTokenException('Invalid token.', $token);
        }

        $response = $this->getHttpClient()->get($this->baseUrl.'/2/users/show.json', [
            'query' => [
                'uid' => $uid,
                Contracts\SHOPWWI_SOC_ACCESS_TOKEN => $token,
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        return $this->fromJsonBody($response);
    }

    /**
     * @throws Exceptions\InvalidTokenException
     */
    protected function getTokenPayload(string $token): array
    {
        $response = $this->getHttpClient()->post($this->baseUrl.'/oauth2/get_token_info', [
            'query' => [
                Contracts\SHOPWWI_SOC_ACCESS_TOKEN => $token,
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        $response = $this->fromJsonBody($response);

        if (empty($response['uid'] ?? null)) {
            throw new Exceptions\InvalidTokenException(\sprintf('Invalid token %s', $token), $token);
        }

        return $response;
    }

    /**
     * @param array $user
     * @return Contracts\User
     */
    protected function mapUserToObject(array $user): Contracts\User
    {
        return new AbstractUser([
            Contracts\SHOPWWI_SOC_ID => $user[Contracts\SHOPWWI_SOC_ID] ?? null,
            Contracts\SHOPWWI_SOC_NICKNAME => $user['screen_name'] ?? null,
            Contracts\SHOPWWI_SOC_NAME => $user[Contracts\SHOPWWI_SOC_NAME] ?? null,
            Contracts\SHOPWWI_SOC_EMAIL => $user[Contracts\SHOPWWI_SOC_EMAIL] ?? null,
            Contracts\SHOPWWI_SOC_AVATAR => $user['avatar_large'] ?? null,
        ]);
    }
}
