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

 return [
     'enable' => true,
     'driver' => [
         'qq' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\QQProvider::class,
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'wechat' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\WeChatProvider::class,
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
             // 开放平台 - 第三方平台所需
             'component' => [
                 // or 'app_id', 'component_app_id' as key
                 'id' => 'component-app-id',
                 // or 'app_token', 'access_token', 'component_access_token' as key
                 'token' => 'component-access-token',
             ]
         ],
         'weibo' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\WeiboProvider::class,
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'taobao' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\QQProvider::class,
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'alipay' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\AlipayProvider::class,
             // 这个键名还能像官方文档那样叫做 'app_id'
             'client_id' => 'your-app-id',

             // 请根据官方文档，在官方管理后台配置 RSA2
             // 注意： 这是你自己的私钥
             // 注意： 不允许私钥内容有其他字符
             // 建议： 为了保证安全，你可以将文本信息从磁盘文件中读取，而不是在这里明文
             'rsa_private_key' => runtime_path(''),

             // 确保这里的值与你在服务后台绑定的地址值一致
             // 这个键名还能像官方文档那样叫做 'redirect_url'
             'redirect' => 'http://your-callback-url',

             // 沙箱模式接入地址见 https://opendocs.alipay.com/open/220/105337#%E5%85%B3%E4%BA%8E%E6%B2%99%E7%AE%B1
             'sandbox' => false,
         ],
         'coding' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\CodingProvider::class,
             'team_url' => 'https://{your-team}.coding.net',
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'dingtalk' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\DingTalkProvider::class,
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'baidu' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\BaiduProvider::class,
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'azure' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\AzureProvider::class,
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'douban' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\DoubanProvider::class,
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'douyin' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\DouYinProvider::class,
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'facebook' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\FacebookProvider::class,
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'feishu' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\FeiShuProvider::class,
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
             // 如果你想使用使用内部应用的方式获取 app_access_token
             // 对这个键设置了 'internal' 值那么你已经开启了内部应用模式
             'app_mode' => 'internal'
         ],
         'figma' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\FigmaProvider::class,
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'gitee' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\GiteeProvider::class,
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'github' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\GitHubProvider::class,
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'google' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\GoogleProvider::class,
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'lark' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\LarkProvider::class,
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
             // 如果你想使用使用内部应用的方式获取 app_access_token
             // 对这个键设置了 'internal' 值那么你已经开启了内部应用模式
             'app_mode' => 'internal'
         ],
         'line' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\LarkProvider::class,
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'linkedin' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\LinkedinProvider::class,
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'openwework' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\OpenWeWorkProvider::class,
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'outlook' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\OutlookProvider::class,
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'qcloud' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\QCloudProvider::class,
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'tapd' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\TapdProvider::class,
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'toutiao' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\TouTiaoProvider::class,
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'wework' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\WeWorkProvider::class,
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'xigua' => [
             'provider' => \Shopwwi\WebmanSocialite\Providers\XiGuaProvider::class,
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
     ]
 ];
