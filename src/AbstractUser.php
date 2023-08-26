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

namespace Shopwwi\WebmanSocialite;


class AbstractUser implements \Shopwwi\WebmanSocialite\Contracts\User
{
    use \Shopwwi\WebmanSocialite\Contracts\HasAttributes;

    public function __construct(array $attributes, protected ?Contracts\Provider $provider = null)
    {
        $this->attributes = $attributes;
    }

    public function getId()
    {
        return $this->getAttribute(Contracts\SHOPWWI_SOC_ID) ?? $this->getEmail();
    }

    public function getNickname(): ?string
    {
        return $this->getAttribute(Contracts\SHOPWWI_SOC_NICKNAME) ?? $this->getName();
    }

    public function getName(): ?string
    {
        return $this->getAttribute(Contracts\SHOPWWI_SOC_NAME);
    }

    public function getEmail(): ?string
    {
        return $this->getAttribute(Contracts\SHOPWWI_SOC_EMAIL);
    }

    public function getAvatar(): ?string
    {
        return $this->getAttribute(Contracts\SHOPWWI_SOC_AVATAR);
    }

    public function setAccessToken(string $value): self
    {
        $this->setAttribute(Contracts\SHOPWWI_SOC_ACCESS_TOKEN, $value);

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->getAttribute(Contracts\SHOPWWI_SOC_ACCESS_TOKEN);
    }

    public function setRefreshToken(?string $value): self
    {
        $this->setAttribute(Contracts\SHOPWWI_SOC_REFRESH_TOKEN, $value);

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->getAttribute(Contracts\SHOPWWI_SOC_REFRESH_TOKEN);
    }

    public function setExpiresIn(int $value): self
    {
        $this->setAttribute(Contracts\SHOPWWI_SOC_EXPIRES_IN, $value);

        return $this;
    }

    public function getExpiresIn(): ?int
    {
        return $this->getAttribute(Contracts\SHOPWWI_SOC_EXPIRES_IN);
    }

    public function setRaw(array $user): self
    {
        $this->setAttribute('raw', $user);

        return $this;
    }

    public function getRaw(): array
    {
        return $this->getAttribute('raw', []);
    }

    public function setTokenResponse(array $response): self
    {
        $this->setAttribute('token_response', $response);

        return $this;
    }

    public function getTokenResponse()
    {
        return $this->getAttribute('token_response');
    }

    public function jsonSerialize(): array
    {
        return $this->attributes;
    }

    public function __serialize(): array
    {
        return $this->attributes;
    }

    public function __unserialize(array $serialized): void
    {
        $this->attributes = $serialized ?: [];
    }

    /**
     * @return Contracts\Provider
     * @throws Exceptions\Exception
     */
    public function getProvider(): Contracts\Provider
    {
        if(!$this->provider) throw new Exceptions\Exception('The provider instance doesn\'t initialized correctly.');
        return  $this->provider;
    }

    /**
     * @param Contracts\Provider $provider
     * @return $this
     */
    public function setProvider(Contracts\Provider $provider): self
    {
        $this->provider = $provider;

        return $this;
    }
}
