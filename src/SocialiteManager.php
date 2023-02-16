<?php
/**
 *-------------------------------------------------------------------------s*
 *
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

namespace Shopwwi\WebmanSocialite;

use Closure;
use Shopwwi\WebmanSocialite\Contracts;

class SocialiteManager implements Contracts\Factory
{
    protected array $resolved = [];
    protected static array $customCreators = [];

    public function __construct(array $config = [])
    {
        if(empty($config)){
            $config = \config('plugin.shopwwi.socialite.app.driver');
        }
        $this->config = new Config($config);
    }

    public function config(Config $config): self
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Get a driver instance.
     *
     * @param  string  $driver
     * @return mixed
     */
    public function driver($driver = null): Contracts\Provider
    {
        $driver = \strtolower($driver);

        if (! isset($this->resolved[$driver])) {
            $this->resolved[$driver] = $this->createDriverProvider($driver);
        }

        return $this->resolved[$driver];
    }

    /**
     * extend more driver
     * @param string $name
     * @param Closure $callback
     * @return $this
     */
    public function extend(string $name, Closure $callback): self
    {
        self::$customCreators[\strtolower($name)] = $callback;

        return $this;
    }

    /**
     * @return array
     */
    public function getResolvedProviders(): array
    {
        return $this->resolved;
    }

    /**
     * @param string $provider
     * @param array $config
     * @return Contracts\Provider
     */
    public function buildProvider(string $provider, array $config): Contracts\Provider
    {
        $instance = new $provider($config);
        if(!$instance instanceof Contracts\Provider){
          throw  new Exceptions\InvalidArgumentException("The {$provider} must be instanceof ProviderInterface.");
        }
        return $instance;
    }

    /**
     * @param $driver
     * @return Contracts\Provider
     */
    public function createDriverProvider($driver): Contracts\Provider
    {
        $config = $this->config->get($driver, []);
        $provider = $config['provider'] ?? $driver;

        if (isset(self::$customCreators[$provider])) {
            return $this->callCustomCreator($provider, $config);
        }

        if (! $this->isValidProvider($provider)) {
            throw new Exceptions\InvalidArgumentException("Provider [{$driver}] not supported.");
        }

        return $this->buildProvider( $provider, $config);
    }

    /**
     * @param string $provider
     * @return bool
     */
    private function isValidProvider(string $provider):bool
    {
        return \is_subclass_of($provider, Contracts\Provider::class);
    }

    /**
     * @param string $provider
     * @param array $config
     * @return mixed
     */
    private function callCustomCreator(string $provider,array $config): Contracts\Provider
    {
        return self::$customCreators[$provider]($config);
    }
}