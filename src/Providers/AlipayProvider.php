<?php
/**
 *-------------------------------------------------------------------------s*
 * alipay provider
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

class AlipayProvider extends AbstractProvider
{
    public const NAME = 'alipay';

    protected string $baseUrl = 'https://openapi.alipay.com/gateway.do';

    protected string $authUrl = 'https://openauth.alipay.com/oauth2/publicAppAuthorize.htm';

    protected array $scopes = ['auth_user'];

    protected string $apiVersion = '1.0';

    protected string $signType = 'RSA2';

    protected string $postCharset = 'UTF-8';

    protected string $format = 'json';

    protected bool $sandbox = false;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->sandbox = (bool) $this->config->get('sandbox', false);
        if ($this->sandbox) {
            $this->baseUrl = 'https://openapi.alipaydev.com/gateway.do';
            $this->authUrl = 'https://openauth.alipaydev.com/oauth2/publicAppAuthorize.htm';
        }
    }

    /**
     * @return string
     */
    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->authUrl);
    }

    /**
     * @return string
     */
    protected function getTokenUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @param string $token
     * @return array
     * @throws Exceptions\BadRequestException
     * @throws GuzzleException
     */
    protected function getUserByToken(string $token): array
    {
        $params = $this->getPublicFields('alipay.user.info.share');
        $params += ['auth_token' => $token];
        $params['sign'] = $this->generateSign($params);

        $responseInstance = $this->getHttpClient()->post(
            $this->baseUrl,
            [
                'form_params' => $params,
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded;charset=utf-8',
                ],
            ]
        );

        $response = $this->fromJsonBody($responseInstance);

        if (! empty($response['error_response'] ?? null) || empty($response['alipay_user_info_share_response'] ?? [])) {
            throw new Exceptions\BadRequestException((string) $responseInstance->getBody());
        }

        return $response['alipay_user_info_share_response'];
    }

    /**
     * @param array $user
     * @return Contracts\User
     */
    protected function mapUserToObject(array $user): Contracts\User
    {
        return new AbstractUser([
            Contracts\SHOPWWI_SOC_ID => $user['user_id'] ?? null,
            Contracts\SHOPWWI_SOC_NAME => $user['nick_name'] ?? null,
            Contracts\SHOPWWI_SOC_AVATAR => $user[Contracts\SHOPWWI_SOC_AVATAR] ?? null,
            Contracts\SHOPWWI_SOC_EMAIL => $user[Contracts\SHOPWWI_SOC_EMAIL] ?? null,
        ]);
    }

    /**
     * @param string $code
     * @return array
     * @throws Exceptions\BadRequestException
     * @throws Exceptions\AuthorizeFailedException
     * @throws GuzzleException
     */
    public function tokenFromCode(string $code): array
    {
        $responseInstance = $this->getHttpClient()->post(
            $this->getTokenUrl(),
            [
                'form_params' => $this->getTokenFields($code),
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded;charset=utf-8',
                ],
            ]
        );
        $response = $this->fromJsonBody($responseInstance);

        if (! empty($response['error_response'])) {
            throw new Exceptions\BadRequestException((string) $responseInstance->getBody());
        }

        return $this->normalizeAccessTokenResponse($response['alipay_system_oauth_token_response']);
    }

    /**
     * @return array
     */
    protected function getCodeFields(): array
    {
        if (empty($this->redirectUrl)) {
            throw new Exceptions\InvalidArgumentException('Please set the correct redirect URL refer which was on the Alipay Official Admin pannel.');
        }

        $fields = \array_merge(
            [
                Contracts\SHOPWWI_SOC_APP_ID => $this->getConfig()->get(Contracts\SHOPWWI_SOC_CLIENT_ID) ?? $this->getConfig()->get(Contracts\SHOPWWI_SOC_APP_ID),
                Contracts\SHOPWWI_SOC_SCOPE => $this->formatScopes($this->scopes, $this->scopeSeparator),
                Contracts\SHOPWWI_SOC_REDIRECT_URI => $this->redirectUrl,
            ],
            $this->parameters
        );

        return $fields;
    }

    /**
     * @param string $code
     * @return array
     * @throws \Exception
     */
    protected function getTokenFields(string $code): array
    {
        $params = $this->getPublicFields('alipay.system.oauth.token');
        $params += [
            Contracts\SHOPWWI_SOC_CODE => $code,
            Contracts\SHOPWWI_SOC_GRANT_TYPE => Contracts\SHOPWWI_SOC_AUTHORIZATION_CODE,
        ];
        $params['sign'] = $this->generateSign($params);

        return $params;
    }

    /**
     * @param string $method
     * @return array
     * @throws \Exception
     */
    public function getPublicFields(string $method): array
    {
        return [
            Contracts\SHOPWWI_SOC_APP_ID => $this->getConfig()->get(Contracts\SHOPWWI_SOC_CLIENT_ID) ?? $this->getConfig()->get(Contracts\SHOPWWI_SOC_APP_ID),
            'format' => $this->format,
            'charset' => $this->postCharset,
            'sign_type' => $this->signType,
            'method' => $method,
            'timestamp' => (new \DateTime('now', new \DateTimeZone('Asia/Shanghai')))->format('Y-m-d H:i:s'),
            'version' => $this->apiVersion,
        ];
    }

    /**
     * @see https://opendocs.alipay.com/open/289/105656
     */
    protected function generateSign(array $params): string
    {
        \ksort($params);

        return $this->signWithSHA256RSA($this->buildParams($params), $this->getConfig()->get('rsa_private_key'));
    }

    /**
     * @param string $signContent
     * @param string $key
     * @return string
     */
    protected function signWithSHA256RSA(string $signContent, string $key): string
    {
        if (empty($key)) {
            throw new Exceptions\InvalidArgumentException('no RSA private key set.');
        }

        $key = "-----BEGIN RSA PRIVATE KEY-----\n".
            \chunk_split($key, 64, "\n").
            '-----END RSA PRIVATE KEY-----';

        \openssl_sign($signContent, $signValue, $key, \OPENSSL_ALGO_SHA256);

        return \base64_encode($signValue);
    }

    /**
     * @param array $params
     * @param bool $urlencode
     * @param array $except
     * @return string
     */
    public static function buildParams(array $params, bool $urlencode = false, array $except = ['sign']): string
    {
        $param_str = '';
        foreach ($params as $k => $v) {
            if (\in_array($k, $except)) {
                continue;
            }
            $param_str .= $k.'=';
            $param_str .= $urlencode ? \rawurlencode($v) : $v;
            $param_str .= '&';
        }

        return \rtrim($param_str, '&');
    }
}