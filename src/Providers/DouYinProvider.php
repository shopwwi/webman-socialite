<?php
/**
 *-------------------------------------------------------------------------s*
 * DingTalk Provider
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
 * @see http://open.douyin.com/platform
 * @see https://developer.open-douyin.com/docs/resource/zh-CN/dop/ability/user-management/get-user-info-solution
 */
class DouYinProvider extends AbstractProvider
{
    public const NAME = 'douyin';

    protected string $baseUrl = 'https://open.douyin.com';

    protected array $scopes = ['user_info'];

    protected ?string $openId;

    /**
     * @return string
     */
    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->baseUrl.'/platform/oauth/connect/');
    }

    /**
     * @return array
     */
    public function getCodeFields(): array
    {
        return [
            'client_key' => $this->getClientId(),
            Contracts\SHOPWWI_SOC_REDIRECT_URI => $this->redirectUrl,
            Contracts\SHOPWWI_SOC_SCOPE => $this->formatScopes($this->scopes, $this->scopeSeparator),
            Contracts\SHOPWWI_SOC_RESPONSE_TYPE => Contracts\SHOPWWI_SOC_CODE,
        ];
    }

    /**
     * @return string
     */
    protected function getTokenUrl(): string
    {
        return $this->baseUrl.'/oauth/access_token/';
    }

    /**
     * @param string $code
     * @return array
     * @throws Exceptions\AuthorizeFailedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function tokenFromCode(string $code): array
    {
        $response = $this->getHttpClient()->get(
            $this->getTokenUrl(),
            [
                'query' => $this->getTokenFields($code),
            ]
        );

        $body = $this->fromJsonBody($response);

        if (empty($body['data'] ?? null) || ($body['data']['error_code'] ?? -1) != 0) {
            throw new Exceptions\AuthorizeFailedException('Invalid token response', $body);
        }

        $this->withOpenId($body['data'][Contracts\SHOPWWI_SOC_OPEN_ID]);

        return $this->normalizeAccessTokenResponse($body['data']);
    }

    /**
     * @param string $code
     * @return array
     */
    protected function getTokenFields(string $code): array
    {
        return [
            'client_key' => $this->getClientId(),
            Contracts\SHOPWWI_SOC_CLIENT_SECRET => $this->getClientSecret(),
            Contracts\SHOPWWI_SOC_CODE => $code,
            Contracts\SHOPWWI_SOC_GRANT_TYPE => Contracts\SHOPWWI_SOC_AUTHORIZATION_CODE,
        ];
    }

    /**
     * @param string $token
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getUserByToken(string $token): array
    {
        $userUrl = $this->baseUrl.'/oauth/userinfo/';

        if (empty($this->openId)) {
            throw new Exceptions\InvalidArgumentException('please set the `open_id` before issue the API request.');
        }

        $response = $this->getHttpClient()->get(
            $userUrl,
            [
                'query' => [
                    Contracts\SHOPWWI_SOC_ACCESS_TOKEN => $token,
                    Contracts\SHOPWWI_SOC_OPEN_ID => $this->openId,
                ],
            ]
        );

        $body = $this->fromJsonBody($response);

        return $body['data'] ?? [];
    }

    /**
     * @param array $user
     * @return Contracts\User
     */
    protected function mapUserToObject(array $user): Contracts\User
    {
        return new AbstractUser([
            Contracts\SHOPWWI_SOC_ID => $user[Contracts\SHOPWWI_SOC_OPEN_ID] ?? null,
            Contracts\SHOPWWI_SOC_NAME => $user[Contracts\SHOPWWI_SOC_NICKNAME] ?? null,
            Contracts\SHOPWWI_SOC_NICKNAME => $user[Contracts\SHOPWWI_SOC_NICKNAME] ?? null,
            Contracts\SHOPWWI_SOC_AVATAR => $user[Contracts\SHOPWWI_SOC_AVATAR] ?? null,
            Contracts\SHOPWWI_SOC_EMAIL => $user[Contracts\SHOPWWI_SOC_EMAIL] ?? null,
        ]);
    }

    /**
     * @param string $openId
     * @return $this
     */
    public function withOpenId(string $openId): self
    {
        $this->openId = $openId;

        return $this;
    }
}
