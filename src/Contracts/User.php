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
const SHOPWWI_SOC_ID = 'id';
const SHOPWWI_SOC_NAME = 'name';
const SHOPWWI_SOC_NICKNAME = 'nickname';
const SHOPWWI_SOC_EMAIL = 'email';
const SHOPWWI_SOC_AVATAR = 'avatar';

interface User
{


    public function getId();

    public function getNickname(): ?string;

    public function getName(): ?string;

    public function getEmail(): ?string;

    public function getAvatar(): ?string;

    public function getAccessToken(): ?string;

    public function getRefreshToken(): ?string;

    public function getExpiresIn(): ?int;

    public function getProvider(): Provider;

    public function setRefreshToken(?string $refreshToken): self;

    public function setExpiresIn(int $expiresIn): self;

    public function setTokenResponse(array $response): self;

    public function getTokenResponse();

    public function setProvider(Provider $provider): self;

    public function getRaw(): array;

    public function setRaw(array $user): self;

    public function setAccessToken(string $token): self;
}