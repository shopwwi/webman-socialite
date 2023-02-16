<?php
/**
 *-------------------------------------------------------------------------s*
 * OpenWeWork Provider
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

use GuzzleHttp\Exception\GuzzleException;
use Shopwwi\WebmanSocialite\AbstractUser;
use Shopwwi\WebmanSocialite\Exceptions;
use Shopwwi\WebmanSocialite\Contracts;

/**
 * @link https://open.work.weixin.qq.com/api/doc/90001/90143/91120
 */
class OpenWeWorkProvider extends AbstractProvider
{
    public const NAME = 'open-wework';

    protected bool $detailed = false;

    protected bool $asQrcode = false;

    protected string $userType = 'member';

    protected string $lang = 'zh';

    protected ?string $suiteTicket = null;

    protected ?int $agentId = null;

    protected ?string $suiteAccessToken = null;

    protected string $baseUrl = 'https://qyapi.weixin.qq.com';

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        if ($this->getConfig()->has('base_url')) {
            $this->baseUrl = $this->getConfig()->get('base_url');
        }
    }

    /**
     * @param int $agentId
     * @return $this
     */
    public function withAgentId(int $agentId): self
    {
        $this->agentId = $agentId;

        return $this;
    }

    /**
     * @return $this
     */
    public function detailed(): self
    {
        $this->detailed = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function asQrcode(): self
    {
        $this->asQrcode = true;

        return $this;
    }

    /**
     * @param string $userType
     * @return $this
     */
    public function withUserType(string $userType): self
    {
        $this->userType = $userType;

        return $this;
    }

    /**
     * @param string $lang
     * @return $this
     */
    public function withLang(string $lang): self
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * @param string $code
     * @return Contracts\User
     * @throws Exceptions\AuthorizeFailedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function userFromCode(string $code): Contracts\User
    {
        $user = $this->getUser($this->getSuiteAccessToken(), $code);

        if ($this->detailed) {
            $user = \array_merge($user, $this->getUserByTicket($user['user_ticket']));
        }

        return $this->mapUserToObject($user)->setProvider($this)->setRaw($user);
    }

    /**
     * @param string $suiteTicket
     * @return $this
     */
    public function withSuiteTicket(string $suiteTicket): self
    {
        $this->suiteTicket = $suiteTicket;

        return $this;
    }

    /**
     * @param string $suiteAccessToken
     * @return $this
     */
    public function withSuiteAccessToken(string $suiteAccessToken): self
    {
        $this->suiteAccessToken = $suiteAccessToken;

        return $this;
    }

    /**
     * @throws Exceptions\InvalidArgumentException
     */
    public function getAuthUrl(): string
    {
        $queries = \array_filter([
            'appid' => $this->getClientId(),
            Contracts\SHOPWWI_SOC_REDIRECT_URI => $this->redirectUrl,
            Contracts\SHOPWWI_SOC_RESPONSE_TYPE => Contracts\SHOPWWI_SOC_CODE,
            Contracts\SHOPWWI_SOC_SCOPE => $this->formatScopes($this->scopes, $this->scopeSeparator),
            Contracts\SHOPWWI_SOC_STATE => $this->state,
            'agentid' => $this->agentId,
        ]);

        if ($this->asQrcode) {
            $queries = array_filter([
                'appid' => $queries['appid'] ?? $this->getClientId(),
                'redirect_uri' => $queries['redirect_uri'] ?? $this->redirectUrl,
                'usertype' => $this->userType,
                'lang' => $this->lang,
                'state' => $this->state,
            ]);

            return \sprintf('https://open.work.weixin.qq.com/wwopen/sso/3rd_qrConnect?%s', http_build_query($queries));
        }

        return \sprintf('https://open.weixin.qq.com/connect/oauth2/authorize?%s#wechat_redirect', \http_build_query($queries));
    }

    /**
     * @throws Exceptions\MethodDoesNotSupportException
     */
    protected function getUserByToken(string $token): array
    {
        throw new Exceptions\MethodDoesNotSupportException('Open WeWork doesn\'t support access_token mode');
    }

    /**
     * @return string
     * @throws Exceptions\AuthorizeFailedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getSuiteAccessToken(): string
    {
        return $this->suiteAccessToken ?? $this->suiteAccessToken = $this->requestSuiteAccessToken();
    }

    /**
     * @throws Exceptions\AuthorizeFailedException|GuzzleException
     */
    protected function getUser(string $token, string $code): array
    {
        $responseInstance = $this->getHttpClient()->get(
            $this->baseUrl.'/cgi-bin/service/getuserinfo3rd',
            [
                'query' => \array_filter(
                    [
                        'suite_access_token' => $token,
                        Contracts\SHOPWWI_SOC_CODE => $code,
                    ]
                ),
            ]
        );

        $response = $this->fromJsonBody($responseInstance);

        if (($response['errcode'] ?? 1) > 0 || (empty($response['UserId']) && empty($response['openid']))) {
            throw new Exceptions\AuthorizeFailedException((string) $responseInstance->getBody(), $response);
        } elseif (empty($response['user_ticket'])) {
            $this->detailed = false;
        }

        return $response;
    }

    /**
     * @throws Exceptions\AuthorizeFailedException
     * @throws GuzzleException
     */
    protected function getUserByTicket(string $userTicket): array
    {
        $responseInstance = $this->getHttpClient()->post(
            $this->baseUrl.'/cgi-bin/service/auth/getuserdetail3rd',
            [
                'query' => [
                    'suite_access_token' => $this->getSuiteAccessToken(),
                ],
                'json' => [
                    'user_ticket' => $userTicket,
                ],
            ],
        );

        $response = $this->fromJsonBody($responseInstance);

        if (($response['errcode'] ?? 1) > 0 || empty($response['userid'])) {
            throw new Exceptions\AuthorizeFailedException((string) $responseInstance->getBody(), $response);
        }

        return $response;
    }

    /**
     * @param array $user
     * @return Contracts\User
     */
    protected function mapUserToObject(array $user): Contracts\User
    {
        return new AbstractUser($this->detailed ? [
            Contracts\SHOPWWI_SOC_ID => $user['userid'] ?? $user['UserId'] ?? null,
            Contracts\SHOPWWI_SOC_NAME => $user[Contracts\SHOPWWI_SOC_NAME] ?? null,
            Contracts\SHOPWWI_SOC_AVATAR => $user[Contracts\SHOPWWI_SOC_AVATAR] ?? null,
            'gender' => $user['gender'] ?? null,
            'corpid' => $user['corpid'] ?? $user['CorpId'] ?? null,
            'open_userid' => $user['open_userid'] ?? null,
            'qr_code' => $user['qr_code'] ?? null,
        ] : [
            Contracts\SHOPWWI_SOC_ID => $user['userid'] ?? $user['UserId'] ?? $user['OpenId'] ?? $user['openid'] ?? null,
            'corpid' => $user['CorpId'] ?? null,
            'open_userid' => $user['open_userid'] ?? null,
        ]);
    }

    /**
     * @throws Exceptions\AuthorizeFailedException
     * @throws GuzzleException
     */
    protected function requestSuiteAccessToken(): string
    {
        $responseInstance = $this->getHttpClient()->post(
            $this->baseUrl.'/cgi-bin/service/get_suite_token',
            [
                'json' => [
                    'suite_id' => $this->config->get('suite_id') ?? $this->config->get('client_id'),
                    'suite_secret' => $this->config->get('suite_secret') ?? $this->config->get('client_secret'),
                    'suite_ticket' => $this->suiteTicket,
                ],
            ]
        );

        $response = $this->fromJsonBody($responseInstance);

        if (isset($response['errcode']) && $response['errcode'] > 0) {
            throw new Exceptions\AuthorizeFailedException((string) $responseInstance->getBody(), $response);
        }

        return $response['suite_access_token'];
    }

    /**
     * @return string
     */
    protected function getTokenUrl(): string
    {
        return '';
    }
}
