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
 * “第三方个人应用”获取用户信息
 *
 * @see https://ding-doc.dingtalk.com/doc#/serverapi3/mrugr3
 *
 * 暂不支持“第三方企业应用”获取用户信息
 * @see https://ding-doc.dingtalk.com/doc#/serverapi3/hv357q
 */
class DingTalkProvider extends AbstractProvider
{
    public const NAME = 'dingtalk';

    protected string $getUserByCode = 'https://oapi.dingtalk.com/sns/getuserinfo_bycode';

    protected array $scopes = ['snsapi_login'];

    protected string $scopeSeparator = ' ';

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase('https://oapi.dingtalk.com/connect/qrconnect');
    }

    /**
     * @throws Exceptions\InvalidArgumentException
     */
    protected function getTokenUrl(): string
    {
        throw new Exceptions\InvalidArgumentException('not supported to get access token.');
    }

    /**
     * @throws Exceptions\InvalidArgumentException
     */
    protected function getUserByToken(string $token): array
    {
        throw new Exceptions\InvalidArgumentException('Unable to use token get User.');
    }

    /**
     * @param array $user
     * @return Contracts\User
     */
    protected function mapUserToObject(array $user): Contracts\User
    {
        return new AbstractUser([
            Contracts\SHOPWWI_SOC_NAME => $user['nick'] ?? null,
            Contracts\SHOPWWI_SOC_NICKNAME => $user['nick'] ?? null,
            Contracts\SHOPWWI_SOC_ID => $user[Contracts\SHOPWWI_SOC_OPEN_ID] ?? null,
            Contracts\SHOPWWI_SOC_EMAIL => null,
            Contracts\SHOPWWI_SOC_AVATAR => null,
        ]);
    }

    protected function getCodeFields(): array
    {
        return array_merge(
            [
                'appid' => $this->getClientId(),
                Contracts\SHOPWWI_SOC_GRANT_TYPE => Contracts\SHOPWWI_SOC_AUTHORIZATION_CODE,
                Contracts\SHOPWWI_SOC_CODE => $this->formatScopes($this->scopes, $this->scopeSeparator),
                Contracts\SHOPWWI_SOC_REDIRECT_URI => $this->redirectUrl,
            ],
            $this->parameters
        );
    }

    public function getClientId(): ?string
    {
        return $this->getConfig()->get(Contracts\SHOPWWI_SOC_APP_ID)
            ?? $this->getConfig()->get('appid')
            ?? $this->getConfig()->get('appId')
            ?? $this->getConfig()->get(Contracts\SHOPWWI_SOC_CLIENT_ID);
    }

    public function getClientSecret(): ?string
    {
        return $this->getConfig()->get(Contracts\SHOPWWI_SOC_APP_SECRET)
            ?? $this->getConfig()->get('appSecret')
            ?? $this->getConfig()->get(Contracts\SHOPWWI_SOC_CLIENT_SECRET);
    }

    protected function createSignature(int $time): string
    {
        return \base64_encode(\hash_hmac('sha256', (string) $time, (string) $this->getClientSecret(), true));
    }

    /**
     * @see https://ding-doc.dingtalk.com/doc#/personnal/tmudue
     *
     * @throws Exceptions\BadRequestException
     */
    public function userFromCode(string $code): Contracts\User
    {
        $time = (int) \microtime(true) * 1000;

        $responseInstance = $this->getHttpClient()->post($this->getUserByCode, [
            'query' => [
                'accessKey' => $this->getClientId(),
                'timestamp' => $time,
                'signature' => $this->createSignature($time),
            ],
            'json' => ['tmp_auth_code' => $code],
        ]);
        $response = $this->fromJsonBody($responseInstance);

        if (0 != ($response['errcode'] ?? 1)) {
            throw new Exceptions\BadRequestException((string) $responseInstance->getBody());
        }

        return new AbstractUser([
            Contracts\SHOPWWI_SOC_NAME => $response['user_info']['nick'],
            Contracts\SHOPWWI_SOC_NICKNAME => $response['user_info']['nick'],
            Contracts\SHOPWWI_SOC_ID => $response['user_info'][Contracts\SHOPWWI_SOC_OPEN_ID],
        ]);
    }
}
