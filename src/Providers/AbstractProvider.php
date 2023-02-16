<?php
/**
 *-------------------------------------------------------------------------s*
 *
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

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Utils;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use Shopwwi\WebmanSocialite\Config;
use Shopwwi\WebmanSocialite\Exceptions;
use Shopwwi\WebmanSocialite\Contracts;

abstract class AbstractProvider implements Contracts\Provider
{
    public const NAME = null;

    protected ?string      $state = null;

    protected Config       $config;

    protected ?string      $redirectUrl;

    protected array        $parameters = [];

    protected array        $scopes = [];

    protected string       $scopeSeparator = ',';

    protected GuzzleClient $httpClient;

    protected array        $guzzleOptions = [];

    protected int          $encodingType = PHP_QUERY_RFC1738;

    protected string       $expiresInKey = Contracts\SHOPWWI_SOC_EXPIRES_IN;

    protected string       $accessTokenKey = Contracts\SHOPWWI_SOC_ACCESS_TOKEN;

    protected string       $refreshTokenKey = Contracts\SHOPWWI_SOC_REFRESH_TOKEN;

    public function __construct(array $config)
    {
        $this->config = new Config($config);
        // set scopes
        if ($this->config->has('scopes') && is_array($this->config->get('scopes'))) {
            $this->scopes = $this->getConfig()->get('scopes');
        } elseif ($this->config->has(Contracts\SHOPWWI_SOC_SCOPE) && is_string($this->getConfig()->get(Contracts\SHOPWWI_SOC_SCOPE))) {
            $this->scopes = [$this->getConfig()->get(Contracts\SHOPWWI_SOC_SCOPE)];
        }

        // normalize Contracts\SHOPWWI_SOC_CLIENT_ID
        if (! $this->config->has(Contracts\SHOPWWI_SOC_CLIENT_ID)) {
            $id = $this->config->get(Contracts\SHOPWWI_SOC_APP_ID);
            if (null != $id) {
                $this->config->set(Contracts\SHOPWWI_SOC_CLIENT_ID, $id);
            }
        }

        // normalize Contracts\SHOPWWI_SOC_CLIENT_SECRET
        if (! $this->config->has(Contracts\SHOPWWI_SOC_CLIENT_SECRET)) {
            $secret = $this->config->get(Contracts\SHOPWWI_SOC_APP_SECRET);
            if (null != $secret) {
                $this->config->set(Contracts\SHOPWWI_SOC_CLIENT_SECRET, $secret);
            }
        }

        // normalize 'redirect_url'
        if (! $this->config->has('redirect_url')) {
            $this->config->set('redirect_url', $this->config->get('redirect'));
        }
        $this->redirectUrl = $this->config->get('redirect_url');
    }

    abstract protected function getAuthUrl(): string;

    abstract protected function getTokenUrl(): string;

    abstract protected function getUserByToken(string $token): array;

    abstract protected function mapUserToObject(array $user): Contracts\User;

    /**
     * @param string|null $redirectUrl
     * @return string
     */
    public function redirect(?string $redirectUrl = null): string
    {
        if (! empty($redirectUrl)) {
            $this->withRedirectUrl($redirectUrl);
        }

        return $this->getAuthUrl();
    }

    /**
     * @param string $code
     * @return Contracts\User
     * @throws Exceptions\AuthorizeFailedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function userFromCode(string $code): Contracts\User
    {
        $tokenResponse = $this->tokenFromCode($code);
        $user = $this->userFromToken($tokenResponse[$this->accessTokenKey]);

        return $user->setRefreshToken($tokenResponse[$this->refreshTokenKey] ?? null)
            ->setExpiresIn($tokenResponse[$this->expiresInKey] ?? null)
            ->setTokenResponse($tokenResponse);
    }

    /**
     * @param string $token
     * @return Contracts\User
     */
    public function userFromToken(string $token): Contracts\User
    {
        $user = $this->getUserByToken($token);

        return $this->mapUserToObject($user)->setProvider($this)->setRaw($user)->setAccessToken($token);
    }

    /**
     * @param string $code
     * @return array
     * @throws Exceptions\AuthorizeFailedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function tokenFromCode(string $code): array
    {
        $response = $this->getHttpClient()->post(
            $this->getTokenUrl(),
            [
                'form_params' => $this->getTokenFields($code),
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]
        );

        return $this->normalizeAccessTokenResponse($response->getBody());
    }

    /**
     * @throws Exceptions\MethodDoesNotSupportException
     */
    public function refreshToken(string $refreshToken)
    {
        throw new Exceptions\MethodDoesNotSupportException('refreshToken does not support.');
    }

    /**
     * @param string $redirectUrl
     * @return Contracts\Provider
     */
    public function withRedirectUrl(string $redirectUrl): Contracts\Provider
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    /**
     * @param string $state
     * @return Contracts\Provider
     */
    public function withState(string $state): Contracts\Provider
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @param array $scopes
     * @return Contracts\Provider
     */
    public function scopes(array $scopes): Contracts\Provider
    {
        $this->scopes = $scopes;

        return $this;
    }

    /**
     * @param array $parameters
     * @return Contracts\Provider
     */
    public function with(array $parameters): Contracts\Provider
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @param string $scopeSeparator
     * @return Contracts\Provider
     */
    public function withScopeSeparator(string $scopeSeparator): Contracts\Provider
    {
        $this->scopeSeparator = $scopeSeparator;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getClientId(): ?string
    {
        return $this->config->get(Contracts\SHOPWWI_SOC_CLIENT_ID);
    }

    /**
     * @return string|null
     */
    public function getClientSecret(): ?string
    {
        return $this->config->get(Contracts\SHOPWWI_SOC_CLIENT_SECRET);
    }

    /**
     * @return GuzzleClient
     */
    public function getHttpClient(): GuzzleClient
    {
        return $this->httpClient ?? new GuzzleClient($this->guzzleOptions);
    }

    /**
     * @param array $config
     * @return Contracts\Provider
     */
    public function setGuzzleOptions(array $config): Contracts\Provider
    {
        $this->guzzleOptions = $config;

        return $this;
    }

    /**
     * @return array
     */
    public function getGuzzleOptions(): array
    {
        return $this->guzzleOptions;
    }

    /**
     * @param array $scopes
     * @param string $scopeSeparator
     * @return string
     */
    protected function formatScopes(array $scopes, string $scopeSeparator): string
    {
        return \implode($scopeSeparator, $scopes);
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
            Contracts\SHOPWWI_SOC_REDIRECT_URI => $this->redirectUrl,
        ];
    }

    /**
     * @param string $url
     * @return string
     */
    protected function buildAuthUrlFromBase(string $url): string
    {
        $query = $this->getCodeFields() + ($this->state ? [Contracts\SHOPWWI_SOC_STATE => $this->state] : []);

        return $url.'?'.\http_build_query($query, '', '&', $this->encodingType);
    }

    /**
     * @return array
     */
    protected function getCodeFields(): array
    {
        $fields = \array_merge(
            [
                Contracts\SHOPWWI_SOC_CLIENT_ID => $this->getClientId(),
                Contracts\SHOPWWI_SOC_REDIRECT_URI => $this->redirectUrl,
                Contracts\SHOPWWI_SOC_SCOPE => $this->formatScopes($this->scopes, $this->scopeSeparator),
                Contracts\SHOPWWI_SOC_RESPONSE_TYPE => Contracts\SHOPWWI_SOC_CODE,
            ],
            $this->parameters
        );

        if ($this->state) {
            $fields[Contracts\SHOPWWI_SOC_STATE] = $this->state;
        }

        return $fields;
    }

    /**
     * @throws Exceptions\AuthorizeFailedException
     */
    protected function normalizeAccessTokenResponse($response): array
    {
        if ($response instanceof StreamInterface) {
            $response->tell() && $response->rewind();
            $response = (string) $response;
        }

        if (\is_string($response)) {
            $response = Utils::jsonDecode($response, true);
        }

        if (! \is_array($response)) {
            throw new Exceptions\AuthorizeFailedException('Invalid token response', [$response]);
        }

        if (empty($response[$this->accessTokenKey])) {
            throw new Exceptions\AuthorizeFailedException('Authorize Failed: '.Utils::jsonEncode($response, \JSON_UNESCAPED_UNICODE), $response);
        }

        return $response + [
                Contracts\SHOPWWI_SOC_ACCESS_TOKEN => $response[$this->accessTokenKey],
                Contracts\SHOPWWI_SOC_REFRESH_TOKEN => $response[$this->refreshTokenKey] ?? null,
                Contracts\SHOPWWI_SOC_EXPIRES_IN => \intval($response[$this->expiresInKey] ?? 0),
            ];
    }

    /**
     * @param MessageInterface $response
     * @return array
     */
    protected function fromJsonBody(MessageInterface $response): array
    {
        $result = Utils::jsonDecode((string) $response->getBody(), true);
        if(! \is_array($result)){
            throw new Exceptions\InvalidArgumentException('Decoded the given response payload failed.');
        }
        return $result;
    }
}