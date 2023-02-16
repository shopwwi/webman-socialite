<?php
/**
 *-------------------------------------------------------------------------s*
 * Lark Provider
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

/**
 * @see https://open.larksuite.com/document/ukTMukTMukTM/uITNz4iM1MjLyUzM
 */
class LarkProvider extends FeiShuProvider
{
    public const NAME = 'lark';

    protected string $baseUrl = 'https://open.larksuite.com/open-apis';
}
