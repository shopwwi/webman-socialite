<?php
/**
 *-------------------------------------------------------------------------s*
 * Tapd Provider
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

use Psr\Http\Message\StreamInterface;
use Shopwwi\WebmanSocialite\AbstractUser;
use Shopwwi\WebmanSocialite\Exceptions;
use Shopwwi\WebmanSocialite\Contracts;

/**
 * @see https://www.tapd.cn/help/show#1120003271001000708
 */
class TapdProvider extends AbstractProvider
{
    public const NAME = 'tapd';

    protected string $baseUrl = 'https://api.tapd.cn';

    /**
     * @return string
     */
    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->baseUrl.'/quickstart/testauth');
    }

    /**
     * @return string
     */
    protected function getTokenUrl(): string
    {
        return $this->baseUrl.'/tokens/request_token';
    }

    /**
     * @return string
     */
    protected function getRefreshTokenUrl(): string
    {
        return $this->baseUrl.'/tokens/refresh_token';
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
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Basic '.\base64_encode(\sprintf('%s:%s', $this->getClientId(), $this->getClientSecret())),
            ],
            'form_params' => $this->getTokenFields($code),
        ]);

        return $this->normalizeAccessTokenResponse($response->getBody());
    }

    /**
     * @param string $code
     * @return array
     */
    protected function getTokenFields(string $code): array
    {
        return [
            Contracts\SHOPWWI_SOC_GRANT_TYPE => Contracts\SHOPWWI_SOC_AUTHORIZATION_CODE,
            Contracts\SHOPWWI_SOC_REDIRECT_URI => $this->redirectUrl,
            Contracts\SHOPWWI_SOC_CODE => $code,
        ];
    }

    /**
     * @param string $refreshToken
     * @return array
     */
    protected function getRefreshTokenFields(string $refreshToken): array
    {
        return [
            Contracts\SHOPWWI_SOC_GRANT_TYPE => Contracts\SHOPWWI_SOC_REFRESH_TOKEN,
            Contracts\SHOPWWI_SOC_REDIRECT_URI => $this->redirectUrl,
            Contracts\SHOPWWI_SOC_REFRESH_TOKEN => $refreshToken,
        ];
    }

    /**
     * @param string $refreshToken
     * @return array
     * @throws Exceptions\AuthorizeFailedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function tokenFromRefreshToken(string $refreshToken): array
    {
        $response = $this->getHttpClient()->post($this->getRefreshTokenUrl(), [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Basic '.\base64_encode(\sprintf('%s:%s', $this->getClientId(), $this->getClientSecret())),
            ],
            'form_params' => $this->getRefreshTokenFields($refreshToken),
        ]);

        return $this->normalizeAccessTokenResponse((string) $response->getBody());
    }

    /**
     * @param string $token
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getUserByToken(string $token): array
    {
        $response = $this->getHttpClient()->get($this->baseUrl.'/users/info', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return $this->fromJsonBody($response);
    }

    /**
     * @throws Exceptions\BadRequestException
     */
    protected function mapUserToObject(array $user): Contracts\User
    {
        if (! isset($user['status']) && $user['status'] != 1) {
            throw new Exceptions\BadRequestException('用户信息获取失败');
        }

        return new AbstractUser([
            Contracts\SHOPWWI_SOC_ID => $user['data'][Contracts\SHOPWWI_SOC_ID] ?? null,
            Contracts\SHOPWWI_SOC_NICKNAME => $user['data']['nick'] ?? null,
            Contracts\SHOPWWI_SOC_NAME => $user['data'][Contracts\SHOPWWI_SOC_NAME] ?? null,
            Contracts\SHOPWWI_SOC_EMAIL => $user['data'][Contracts\SHOPWWI_SOC_EMAIL] ?? null,
            Contracts\SHOPWWI_SOC_AVATAR => $user['data'][Contracts\SHOPWWI_SOC_AVATAR] ?? null,
        ]);
    }

    /**
     * @throws Exceptions\AuthorizeFailedException
     */
    protected function normalizeAccessTokenResponse( $response): array
    {
        if ($response instanceof StreamInterface) {
            $response->rewind();
            $response = (string) $response;
        }

        if (\is_string($response)) {
            $response = \json_decode($response, true) ?? [];
        }

        if (! \is_array($response)) {
            throw new Exceptions\AuthorizeFailedException('Invalid token response', [$response]);
        }

        if (empty($response['data'][$this->accessTokenKey] ?? null)) {
            throw new Exceptions\AuthorizeFailedException('Authorize Failed: '.\json_encode($response, JSON_UNESCAPED_UNICODE), $response);
        }

        return $response + [
            Contracts\SHOPWWI_SOC_ACCESS_TOKEN => $response['data'][$this->accessTokenKey],
            Contracts\SHOPWWI_SOC_REFRESH_TOKEN => $response['data'][$this->refreshTokenKey] ?? null,
            Contracts\SHOPWWI_SOC_EXPIRES_IN => \intval($response['data'][$this->expiresInKey] ?? 0),
        ];
    }
}
