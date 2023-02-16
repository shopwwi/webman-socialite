<?php
/**
 *-------------------------------------------------------------------------s*
 * coding provider
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

class CodingProvider extends AbstractProvider
{
    public const NAME = 'coding';

    protected string $teamUrl = ''; //https://{your-team}.coding.net

    protected array $scopes = ['user', 'user:email'];

    protected string $scopeSeparator = ',';

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $teamUrl = $this->config->get('team_url'); // https://{your-team}.coding.net

        if (! $teamUrl) {
            throw new Exceptions\InvalidArgumentException('Missing required config [team_url]');
        }

        // validate team_url
        if (filter_var($teamUrl, FILTER_VALIDATE_URL) === false) {
            throw new Exceptions\InvalidArgumentException('Invalid team_url');
        }
        $this->teamUrl = rtrim($teamUrl, '/');
    }

    /**
     * @return string
     */
    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase("$this->teamUrl/oauth_authorize.html");
    }

    /**
     * @return string
     */
    protected function getTokenUrl(): string
    {
        return "$this->teamUrl/api/oauth/access_token";
    }

    /**
     * @param string $code
     * @return array
     */
    protected function getTokenFields(string $code): array
    {
        return [
            Contracts\SHOPWWI_SOC_CLIENT_ID => $this->getClientId(),
            Contracts\SHOPWWI_SOC_CLIENT_SECRET => $this->getClientSecret(),
            Contracts\SHOPWWI_SOC_CODE => $code,
            Contracts\SHOPWWI_SOC_GRANT_TYPE => Contracts\SHOPWWI_SOC_AUTHORIZATION_CODE,
        ];
    }

    /**
     * @param string $token
     * @return array
     * @throws Exceptions\BadRequestException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getUserByToken(string $token): array
    {
        $responseInstance = $this->getHttpClient()->get(
            "$this->teamUrl/api/me",
            [
                'query' => [
                    'access_token' => $token,
                ],
            ]
        );

        $response = $this->fromJsonBody($responseInstance);

        if (empty($response[Contracts\SHOPWWI_SOC_ID])) {
            throw new Exceptions\BadRequestException((string) $responseInstance->getBody());
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
            Contracts\SHOPWWI_SOC_NICKNAME => $user[Contracts\SHOPWWI_SOC_NAME] ?? null,
            Contracts\SHOPWWI_SOC_NAME => $user[Contracts\SHOPWWI_SOC_NAME] ?? null,
            Contracts\SHOPWWI_SOC_EMAIL => $user[Contracts\SHOPWWI_SOC_EMAIL] ?? null,
            Contracts\SHOPWWI_SOC_AVATAR => $user[Contracts\SHOPWWI_SOC_AVATAR] ?? null,
        ]);
    }
}