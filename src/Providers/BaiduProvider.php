<?php
/**
 *-------------------------------------------------------------------------s*
 * Baidu Provider
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
 * @see https://developer.baidu.com/wiki/index.php?title=docs/oauth [OAuth 2.0 授权机制说明]
 */
class BaiduProvider extends AbstractProvider
{
    public const NAME = 'baidu';

    protected string $baseUrl = 'https://openapi.baidu.com';

    protected string $version = '2.0';

    protected array $scopes = ['basic'];

    protected string $display = 'popup';

    /**
     * @param string $display
     * @return $this
     */
    public function withDisplay(string $display): self
    {
        $this->display = $display;

        return $this;
    }

    /**
     * @param array $scopes
     * @return $this
     */
    public function withScopes(array $scopes): self
    {
        $this->scopes = $scopes;

        return $this;
    }

    /**
     * @return string
     */
    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->baseUrl.'/oauth/'.$this->version.'/authorize');
    }

    /**
     * @return array
     */
    protected function getCodeFields(): array
    {
        return [
            Contracts\SHOPWWI_SOC_RESPONSE_TYPE => Contracts\SHOPWWI_SOC_CODE,
            Contracts\SHOPWWI_SOC_CLIENT_ID => $this->getClientId(),
            Contracts\SHOPWWI_SOC_REDIRECT_URI => $this->redirectUrl,
            Contracts\SHOPWWI_SOC_SCOPE => $this->formatScopes($this->scopes, $this->scopeSeparator),
            'display' => $this->display,
        ] + $this->parameters;
    }

    /**
     * @return string
     */
    protected function getTokenUrl(): string
    {
        return $this->baseUrl.'/oauth/'.$this->version.'/token';
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
     * @param string $token
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getUserByToken(string $token): array
    {
        $response = $this->getHttpClient()->get(
            $this->baseUrl.'/rest/'.$this->version.'/passport/users/getInfo',
            [
                'query' => [
                    Contracts\SHOPWWI_SOC_ACCESS_TOKEN => $token,
                ],
                'headers' => [
                    'Accept' => 'application/json',
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
            Contracts\SHOPWWI_SOC_ID => $user['userid'] ?? null,
            Contracts\SHOPWWI_SOC_NICKNAME => $user['realname'] ?? null,
            Contracts\SHOPWWI_SOC_NAME => $user['username'] ?? null,
            Contracts\SHOPWWI_SOC_EMAIL => '',
            Contracts\SHOPWWI_SOC_AVATAR => $user['portrait'] ? 'http://tb.himg.baidu.com/sys/portraitn/item/'.$user['portrait'] : null,
        ]);
    }
}
