<?php
/**
 *-------------------------------------------------------------------------s*
 * GitHub Provider
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

class GitHubProvider extends AbstractProvider
{
    public const     NAME = 'github';

    protected array  $scopes = ['read:user'];

    protected string $scopeSeparator = ' ';

    /**
     * @return string
     */
    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase('https://github.com/login/oauth/authorize');
    }

    /**
     * @return string
     */
    protected function getTokenUrl(): string
    {
        return 'https://github.com/login/oauth/access_token';
    }

    /**
     * @param string $token
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getUserByToken(string $token): array
    {
        $userUrl = 'https://api.github.com/user';

        $response = $this->getHttpClient()->get(
            $userUrl,
            $this->createAuthorizationHeaders($token)
        );

        $user = $this->fromJsonBody($response);

        if (\in_array('user:email', $this->scopes)) {
            $user[Contracts\SHOPWWI_SOC_EMAIL] = $this->getEmailByToken($token);
        }

        return $user;
    }

    /**
     * @param string $token
     * @return string
     */
    protected function getEmailByToken(string $token): string
    {
        $emailsUrl = 'https://api.github.com/user/emails';

        try {
            $response = $this->getHttpClient()->get(
                $emailsUrl,
                $this->createAuthorizationHeaders($token)
            );
        } catch (\Throwable $e) {
            return '';
        }

        foreach ($this->fromJsonBody($response) as $email) {
            if ($email['primary'] && $email['verified']) {
                return $email[Contracts\SHOPWWI_SOC_EMAIL];
            }
        }

        return '';
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
     * @param string $token
     * @return array[]
     */
    protected function createAuthorizationHeaders(string $token): array
    {
        return [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => \sprintf('token %s', $token),
            ],
        ];
    }
}
