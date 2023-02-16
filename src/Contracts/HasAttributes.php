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


trait HasAttributes
{
    protected array $attributes = [];

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $name,  $default = null)
    {
        return $this->attributes[$name] ?? $default;
    }

    public function setAttribute(string $name,  $value): self
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    public function merge(array $attributes): self
    {
        $this->attributes = \array_merge($this->attributes, $attributes);

        return $this;
    }

    public function offsetExists( $offset): bool
    {
        return \array_key_exists($offset, $this->attributes);
    }

    public function offsetGet( $offset)
    {
        return $this->getAttribute($offset);
    }

    public function offsetSet( $offset,  $value): void
    {
        $this->setAttribute($offset, $value);
    }

    public function offsetUnset( $offset): void
    {
        unset($this->attributes[$offset]);
    }

    public function __get(string $property)
    {
        return $this->getAttribute($property);
    }

    public function toArray(): array
    {
        return $this->getAttributes();
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function toJSON(): string
    {
        $result = \json_encode($this->getAttributes(), JSON_UNESCAPED_UNICODE);
        if(false === $result) throw new \Exception('Cannot Processing this instance as JSON stringify.');

        return $result;
    }
}