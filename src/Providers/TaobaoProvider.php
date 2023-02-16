<?php
/**
 *-------------------------------------------------------------------------s*
 * TaoBao Provider
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
 * @see https://open.taobao.com/doc.htm?docId=102635&docType=1&source=search [Taobao - OAuth 2.0 授权登录]
 */
class TaobaoProvider extends AbstractProvider
{
    public const NAME = 'taobao';

    protected string $baseUrl = 'https://oauth.taobao.com';

    protected string $gatewayUrl = 'https://eco.taobao.com/router/rest';

    protected string $view = 'web';

    protected array $scopes = ['user_info'];

    /**
     * @param string $view
     * @return $this
     */
    public function withView(string $view): self
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @return string
     */
    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->baseUrl.'/authorize');
    }

    /**
     * @return array
     */
    public function getCodeFields(): array
    {
        return [
            Contracts\SHOPWWI_SOC_CLIENT_ID => $this->getClientId(),
            Contracts\SHOPWWI_SOC_REDIRECT_URI => $this->redirectUrl,
            'view' => $this->view,
            Contracts\SHOPWWI_SOC_RESPONSE_TYPE => Contracts\SHOPWWI_SOC_CODE,
        ];
    }

    protected function getTokenUrl(): string
    {
        return $this->baseUrl.'/token';
    }

    /**
     * @param string $code
     * @return array
     */
    protected function getTokenFields(string $code): array
    {
        return parent::getTokenFields($code) + [
            Contracts\SHOPWWI_SOC_GRANT_TYPE => Contracts\SHOPWWI_SOC_AUTHORIZATION_CODE,
            'view' => $this->view,
        ];
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
            'query' => $this->getTokenFields($code),
        ]);

        return $this->normalizeAccessTokenResponse($response->getBody());
    }

    /**
     * @param string $token
     * @param array|null $query
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getUserByToken(string $token, ?array $query = []): array
    {
        $response = $this->getHttpClient()->post($this->getUserInfoUrl($this->gatewayUrl, $token));

        return $this->fromJsonBody($response);
    }

    /**
     * @param array $user
     * @return Contracts\User
     */
    protected function mapUserToObject(array $user): Contracts\User
    {
        return new AbstractUser([
            Contracts\SHOPWWI_SOC_ID => $user[Contracts\SHOPWWI_SOC_OPEN_ID] ?? null,
            Contracts\SHOPWWI_SOC_NICKNAME => $user['nick'] ?? null,
            Contracts\SHOPWWI_SOC_NAME => $user['nick'] ?? null,
            Contracts\SHOPWWI_SOC_AVATAR => $user[Contracts\SHOPWWI_SOC_AVATAR] ?? null,
            Contracts\SHOPWWI_SOC_EMAIL => $user[Contracts\SHOPWWI_SOC_EMAIL] ?? null,
        ]);
    }

    /**
     * @param array $params
     * @return string
     */
    protected function generateSign(array $params): string
    {
        \ksort($params);

        $stringToBeSigned = $this->getConfig()->get(Contracts\SHOPWWI_SOC_CLIENT_SECRET);

        foreach ($params as $k => $v) {
            if (! \is_array($v) && ! \str_starts_with($v, '@')) {
                $stringToBeSigned .= "$k$v";
            }
        }

        $stringToBeSigned .= $this->getConfig()->get(Contracts\SHOPWWI_SOC_CLIENT_SECRET);

        return \strtoupper(\md5($stringToBeSigned));
    }

    /**
     * @param string $token
     * @param array $apiFields
     * @return array
     * @throws \Exception
     */
    protected function getPublicFields(string $token, array $apiFields = []): array
    {
        $fields = [
            'app_key' => $this->getClientId(),
            'sign_method' => 'md5',
            'session' => $token,
            'timestamp' => (new \DateTime('now', new \DateTimeZone('Asia/Shanghai')))->format('Y-m-d H:i:s'),
            'v' => '2.0',
            'format' => 'json',
        ];

        $fields = \array_merge($apiFields, $fields);
        $fields['sign'] = $this->generateSign($fields);

        return $fields;
    }

    /**
     * @param string $url
     * @param string $token
     * @return string
     * @throws \Exception
     */
    protected function getUserInfoUrl(string $url, string $token): string
    {
        $apiFields = ['method' => 'taobao.miniapp.userInfo.get'];

        $query = \http_build_query($this->getPublicFields($token, $apiFields), '', '&', $this->encodingType);

        return $url.'?'.$query;
    }
}
