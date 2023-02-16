<?php

namespace Shopwwi\WebmanSocialite\Providers;

use Shopwwi\WebmanSocialite\AbstractUser;
use Shopwwi\WebmanSocialite\Exceptions;
use Shopwwi\WebmanSocialite\Contracts;

/**
 * @see https://open.douyin.com/platform/resource/docs/openapi/account-permission/toutiao-get-permission-code
 */
class TouTiaoProvider extends DouYinProvider
{
    public const NAME = 'toutiao';

    protected string $baseUrl = 'https://open.snssdk.com';

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->baseUrl.'/oauth/authorize/');
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
        ]);
    }
}
