<?php
/**
 *-------------------------------------------------------------------------s*
 * QQ Provider
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

use GuzzleHttp\Utils;
use Shopwwi\WebmanSocialite\AbstractUser;
use Shopwwi\WebmanSocialite\Exceptions;
use Shopwwi\WebmanSocialite\Contracts;
use Shopwwi\WebmanSocialite\Exceptions\AuthorizeFailedException;

/**
 * @see http://wiki.connect.qq.com/oauth2-0%E7%AE%80%E4%BB%8B [QQ - OAuth 2.0 登录QQ]
 */
class QQProvider extends AbstractProvider
{
    public const NAME = 'qq';

    protected string $baseUrl = 'https://graph.qq.com';

    protected array $scopes = ['get_user_info'];

    protected bool $withUnionId = false;

    /**
     * @return string
     */
    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->baseUrl.'/oauth2.0/authorize');
    }

    /**
     * @return string
     */
    protected function getTokenUrl(): string
    {
        return $this->baseUrl.'/oauth2.0/token';
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
     * @param string $code
     * @return array
     * @throws Exceptions\AuthorizeFailedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function tokenFromCode(string $code): array
    {
        $response = $this->getHttpClient()->get($this->getTokenUrl(), [
            'query' => $this->getTokenFields($code),
        ]);

        \parse_str((string) $response->getBody(), $token);

        return $this->normalizeAccessTokenResponse($token);
    }

    /**
     * @return $this
     */
    public function withUnionId(): self
    {
        $this->withUnionId = true;

        return $this;
    }

    /**
     * @param string $token
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getUserByToken(string $token): array
    {
        $response = $this->getHttpClient()->get($this->baseUrl.'/oauth2.0/me', [
            'query' => [
                Contracts\SHOPWWI_SOC_ACCESS_TOKEN => $token,
                'fmt' => 'json',
            ] + ($this->withUnionId ? ['unionid' => 1] : []),
        ]);

        $me = $this->fromJsonBody($response);

        $response = $this->getHttpClient()->get($this->baseUrl.'/user/get_user_info', [
            'query' => [
                Contracts\SHOPWWI_SOC_ACCESS_TOKEN => $token,
                'fmt' => 'json',
                'openid' => $me['openid'],
                'oauth_consumer_key' => $this->getClientId(),
            ],
        ]);

        $user = $this->fromJsonBody($response);

        if (! array_key_exists('ret', $user) || $user['ret'] !== 0) {
            throw new AuthorizeFailedException('Authorize Failed: '.Utils::jsonEncode($user, \JSON_UNESCAPED_UNICODE), $user);
        }

        return $user + [
            'unionid' => $me['unionid'] ?? null,
            'openid' => $me['openid'] ?? null,
        ];
    }

    /**
     * @param array $user
     * @return Contracts\User
     */
    protected function mapUserToObject(array $user): Contracts\User
    {
        return new AbstractUser([
            Contracts\SHOPWWI_SOC_ID => $user['openid'] ?? null,
            Contracts\SHOPWWI_SOC_NAME => $user['nickname'] ?? null,
            Contracts\SHOPWWI_SOC_NICKNAME => $user['nickname'] ?? null,
            Contracts\SHOPWWI_SOC_EMAIL => $user['email'] ?? null,
            Contracts\SHOPWWI_SOC_AVATAR => $user['figureurl_qq_2'] ?? null,
        ]);
    }
}
