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

use Shopwwi\WebmanSocialite\Config;

const SHOPWWI_SOC_APP_ID = 'app_id';
const SHOPWWI_SOC_APP_SECRET = 'app_secret';
const SHOPWWI_SOC_OPEN_ID = 'open_id';
const SHOPWWI_SOC_TOKEN = 'token';
interface Factory
{

    /**
     * Get an OAuth provider implementation.
     *
     * @param  string  $driver
     */
    public function driver($driver = null);

    public function config(Config $config):self;

    public function getResolvedProviders(): array;

    public function buildProvider(string $provider, array $config): Provider;
}