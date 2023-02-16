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

namespace Shopwwi\WebmanSocialite\Contracts;


const SHOPWWI_SOC_CLIENT_ID = 'client_id';
const SHOPWWI_SOC_CLIENT_SECRET = 'client_secret';
const SHOPWWI_SOC_RESPONSE_TYPE = 'response_type';
const SHOPWWI_SOC_SCOPE = 'scope';
const SHOPWWI_SOC_STATE = 'state';
const SHOPWWI_SOC_REDIRECT_URI = 'redirect_uri';
const SHOPWWI_SOC_ERROR = 'error';
const SHOPWWI_SOC_ERROR_DESCRIPTION = 'error_description';
const SHOPWWI_SOC_ERROR_URI = 'error_uri';
const SHOPWWI_SOC_GRANT_TYPE = 'grant_type';
const SHOPWWI_SOC_CODE = 'code';
const SHOPWWI_SOC_ACCESS_TOKEN = 'access_token';
const SHOPWWI_SOC_TOKEN_TYPE = 'token_type';
const SHOPWWI_SOC_EXPIRES_IN = 'expires_in';
const SHOPWWI_SOC_USERNAME = 'username';
const SHOPWWI_SOC_PASSWORD = 'password';
const SHOPWWI_SOC_REFRESH_TOKEN = 'refresh_token';
const SHOPWWI_SOC_AUTHORIZATION_CODE = 'authorization_code';
const SHOPWWI_SOC_CLIENT_CREDENTIALS = 'client_credentials';

interface Provider
{
    public function redirect(?string $redirectUrl = null): string;

    public function userFromCode(string $code): User;

    public function userFromToken(string $token): User;

    public function withRedirectUrl(string $redirectUrl): self;

    public function withState(string $state): self;

    /**
     * @param  string[]  $scopes
     */
    public function scopes(array $scopes): self;

    public function with(array $parameters): self;

    public function withScopeSeparator(string $scopeSeparator): self;

    public function getClientId(): ?string;

    public function getClientSecret(): ?string;
}