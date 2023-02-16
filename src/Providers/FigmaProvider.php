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

/**
 * @see https://www.figma.com/developers/api#oauth2
 */
class FigmaProvider extends AbstractProvider
{
    public const NAME = 'figma';

    protected string $scopeSeparator = '';

    protected array $scopes = ['file_read'];

    /**
     * @return string
     */
    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase('https://www.figma.com/oauth');
    }

    /**
     * @return string
     */
    protected function getTokenUrl(): string
    {
        return 'https://www.figma.com/api/oauth/token';
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

    /**
     * @param string $code
     * @return array
     */
    protected function getTokenFields(string $code): array
    {
        return parent::getTokenFields($code) + [Contracts\SHOPWWI_SOC_GRANT_TYPE => Contracts\SHOPWWI_SOC_AUTHORIZATION_CODE];
    }

    /**
     * @return array
     */
    protected function getCodeFields(): array
    {
        return parent::getCodeFields() + [Contracts\SHOPWWI_SOC_STATE => \md5(\uniqid('state_', true))];
    }

    /**
     * @param string $token
     * @param array|null $query
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getUserByToken(string $token, ?array $query = []): array
    {
        $response = $this->getHttpClient()->get('https://api.figma.com/v1/me', [
            'headers' => [
                'Accept' => 'application/json',
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
            'username' => $user[Contracts\SHOPWWI_SOC_EMAIL] ?? null,
            Contracts\SHOPWWI_SOC_NICKNAME => $user['handle'] ?? null,
            Contracts\SHOPWWI_SOC_NAME => $user['handle'] ?? null,
            Contracts\SHOPWWI_SOC_EMAIL => $user[Contracts\SHOPWWI_SOC_EMAIL] ?? null,
            Contracts\SHOPWWI_SOC_AVATAR => $user['img_url'] ?? null,
        ]);
    }
}
