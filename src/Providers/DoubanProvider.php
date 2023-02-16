<?php
/**
 *-------------------------------------------------------------------------s*
 * Douban Provider
 *-------------------------------------------------------------------------h*
 * @copyright  Copyright (c) 2015-2022 Shopwwi Inc. (http://www.shopwwi.com)
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
 * @see http://developers.douban.com/wiki/?title=oauth2 [使用 OAuth 2.0 访问豆瓣 API]
 */
class DoubanProvider extends AbstractProvider
{
    public const NAME = 'douban';

    /**
     * @return string
     */
    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase('https://www.douban.com/service/auth2/auth');
    }

    /**
     * @return string
     */
    protected function getTokenUrl(): string
    {
        return 'https://www.douban.com/service/auth2/token';
    }

    /**
     * @param  string  $token
     * @param  ?array  $query
     */
    protected function getUserByToken(string $token, ?array $query = []): array
    {
        $response = $this->getHttpClient()->get('https://api.douban.com/v2/user/~me', [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
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
            Contracts\SHOPWWI_SOC_NICKNAME => $user[Contracts\SHOPWWI_SOC_NAME] ?? null,
            Contracts\SHOPWWI_SOC_NAME => $user[Contracts\SHOPWWI_SOC_NAME] ?? null,
            Contracts\SHOPWWI_SOC_AVATAR => $user[Contracts\SHOPWWI_SOC_AVATAR] ?? null,
            Contracts\SHOPWWI_SOC_EMAIL => null,
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

    /**
     * @param string $code
     * @return array
     * @throws Exceptions\AuthorizeFailedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function tokenFromCode(string $code): array
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'form_params' => $this->getTokenFields($code),
        ]);

        return $this->normalizeAccessTokenResponse($response->getBody());
    }
}
