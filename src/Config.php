<?php

namespace Shopwwi\WebmanSocialite;

use ArrayAccess;
use JsonSerializable;

class Config implements ArrayAccess, JsonSerializable
{
    protected array $config;

    /**
     * @param  array  $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function get(string $key,  $default = null)
    {
        $config = $this->config;

        if (isset($config[$key])) {
            return $config[$key];
        }

        foreach (\explode('.', $key) as $segment) {
            if (! \is_array($config) || ! \array_key_exists($segment, $config)) {
                return $default;
            }
            $config = $config[$segment];
        }

        return $config;
    }

    public function set(string $key, $value): array
    {
        $keys = \explode('.', $key);
        $config = &$this->config;

        while (\count($keys) > 1) {
            $key = \array_shift($keys);
            if (! isset($config[$key]) || ! \is_array($config[$key])) {
                $config[$key] = [];
            }
            $config = &$config[$key];
        }

        $config[\array_shift($keys)] = $value;

        return $config;
    }

    public function has(string $key): bool
    {
        return (bool) $this->get($key);
    }

    public function offsetExists( $offset): bool
    {
        if(! \is_string($offset)) throw new Exceptions\InvalidArgumentException('The $offset must be type of string here.');

        return \array_key_exists($offset, $this->config);
    }

    public function offsetGet( $offset)
    {
        if(! \is_string($offset)) throw new Exceptions\InvalidArgumentException('The $offset must be type of string here.');

        return $this->get($offset);
    }

    public function offsetSet( $offset,  $value): void
    {
        if(! \is_string($offset)) throw new Exceptions\InvalidArgumentException('The $offset must be type of string here.');

        $this->set($offset, $value);
    }

    public function offsetUnset( $offset): void
    {
        if(! \is_string($offset)) throw new Exceptions\InvalidArgumentException('The $offset must be type of string here.');

        $this->set($offset, null);
    }

    public function jsonSerialize(): array
    {
        return $this->config;
    }

    public function __toString(): string
    {
        return \json_encode($this, \JSON_UNESCAPED_UNICODE) ?: '';
    }
}
