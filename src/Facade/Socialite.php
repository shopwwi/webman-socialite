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

namespace Shopwwi\WebmanSocialite\Facade;

use Closure;
use Shopwwi\WebmanSocialite\Config;
use Shopwwi\WebmanSocialite\Contracts\Provider;
use Shopwwi\WebmanSocialite\SocialiteManager;

/**
 * @method static mixed driver($driver = null)
 * @method static mixed config(Config $config)
 * @method static mixed extend(string $name, Closure $callback)
 * @method static Provider with(array $parameters)
 * @method static Provider scopes(array $scopes)
 * @method static Provider redirect(?string $redirectUrl = null)
 * @method static Provider userFromCode(string $code)
 * @method static Provider userFromToken(string $token)
 * @method static Provider withState(string $state)
 * @method static Provider withScopeSeparator(string $scopeSeparator)
 * @method static Provider getClientId()
 * @method static Provider getClientSecret()
 * @method static Provider withRedirectUrl(string $redirectUrl)
 * @see SocialiteManager
 */
class Socialite
{
    protected static $_instance = null;

    public static function instance()
    {
        if (!static::$_instance) {
            static::$_instance = new SocialiteManager();
        }
        return static::$_instance;
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        return static::instance()->{$method}(... $arguments);
    }
}