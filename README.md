# webman-socialite

webman-socialite 是一个 [OAuth2](https://oauth.net/2/) 社会化认证工具。 它借鉴于 [laravel/socialite](https://github.com/laravel/socialite) ，你可以轻易的运行在webman的laravel/tp/yii等项目中使用它

 [!['Latest Stable Version'](https://poser.pugx.org/shopwwi/webman-socialite/v/stable.svg)](https://packagist.org/packages/shopwwi/webman-socialite) [!['Total Downloads'](https://poser.pugx.org/shopwwi/webman-socialite/d/total.svg)](https://packagist.org/packages/shopwwi/webman-socialite) [!['License'](https://poser.pugx.org/shopwwi/webman-socialite/license.svg)](https://packagist.org/packages/shopwwi/webman-socialite)


该工具现已支持平台有：Facebook，Github，Google，Linkedin，Outlook，QQ，TAPD，支付宝，淘宝，百度，钉钉，微博，微信，抖音，飞书，Lark，豆瓣，企业微信，腾讯云，Line，Gitee，Coding。

如果你觉得方便了你，可以为我点个小星星[点击这里 :heart:](https://github.com/shopwwi/webman-socialite)


# 安装

```
composer require shopwwi/webman-socialite
```
# 配置
在使用 `Socialite` 之前，您还需要为应用程序使用的 `OAuth` 服务添加凭据。 这些凭证应该放在你的 `config/plugin/shopwwi/socialite/app.php` 配置文件中，并且应该使用密钥 facebook，twitter，linkedin，google，github，gitlab 或 bitbucket， 取决于您的应用程序所需的提供商。 例如：

```php
   'driver' => [
      ...
      'qq' => [
          'provider' => \Shopwwi\WebmanSocialite\Providers\QqProvider::class,
          'client_id' => '',
          'client_secret' => '',
          'redirect' => 'http://your-callback-url',
      ],
      ...
   ]
```

## 使用方法

接下来，就要对用户认证了！这需要两个路由：一个路由用于把用户重定向到 OAuth provider，另一个则用于在认证完成后接收相应 `provider` 的回调请求。可以通过 `Socialite facade` 的方式来访问 Socialite：

```php
<?php

namespace app\controller\auth;

use support\Request;
use Shopwwi\WebmanSocialite\Facade\Socialite;

class QqController
{
    /**
     * 将用户重定向到 QQ 的授权页面
     *
     * @return 
     */
    public function redirect(Request $request)
    {
        $redirect = Socialite::driver('qq')->redirect();
        return redirect($redirect);
    }

    /**
     * 从 QQ 获取用户信息
     *
     */
    public function callback(Request $request)
    {
         $code = $request->input('code');
         
         $qqUser = Socialite::driver('qq')->userFromCode($code);
         
         //示例
         $user = User::where('qq_id', $qqUser->id)->first();
         if ($user) {
           $user->update([
               'qq_token' => $qqUser->access_token,
               'qq_refresh_token' => $qqUser->refresh_token,
           ]);
         } else {
           $user = User::create([
               'name' => $qqUser->name,
               'email' => $qqUser->email,
               'qq_id' => $qqUser->id,
               'qq_token' => $qqUser->access_token,
               'qq_refresh_token' => $qqUser->refresh_token,
           ]);
         }
          Auth::login($user);
          return redirect('/index');
    }
}
```

## 配置

### 扩展自定义服务提供程序

你可以很轻松的对socialite进行扩展来满足你不同的第三方登入需求

1.直接在config配置里添加你的应用驱动

```php
'driver' => [
    ..
    'line' => [
        'provider' => \app\provider\LineProvider::class, 
        'client_id' => 'your-app-id',
        'client_secret' => 'your-app-secret',
        'redirect' => 'http://your-callback-url',
    ],
    ..
];

$socialite = Socialite::driver('line')->redirect();
   
```

2.使用闭包函数进行扩展

```php
$config = [
    'line' =>[
        'provider' => 'line',
        'client_id' => 'your-app-id',
        'client_secret' => 'your-app-secret',
        'redirect' => 'http://your-callback-url',
    ]   
];
$socialite = Socialite::config(new \Shopwwi\WebmanSocialite\Config($config))->extend('line', function(array $config) {
    return new LineProvider($config);
})->driver('line')->redirect();

// 下面直接注入也是可以的哈
$config = [
    'line' =>[
        'provider' => \app\provider\LineProvider::class,
        'client_id' => 'your-app-id',
        'client_secret' => 'your-app-secret',
        'redirect' => 'http://your-callback-url',
    ]   
];
$socialite = Socialite::config(new \Shopwwi\WebmanSocialite\Config($config))->driver('line')->redirect();

```

3.接下来为 `AppleProvider` 设置实际操作方法：

你的自定义服务提供类必须实现`\Shopwwi\WebmanSocialite\Contracts\Provider` 接口
```php
    namespace app\provider;
    class LineProvider implements \Shopwwi\WebmanSocialite\Contracts\Provider
    {
        //...
    }

```
下面的示例继承了 `\Shopwwi\WebmanSocialite\Providers\AbstractProvider` 大多数逻辑都是实现好了的 微调即可
```php
    namespace app\provider;
    use Shopwwi\WebmanSocialite\Providers\AbstractProvider;
    use Shopwwi\WebmanSocialite\Contracts;
    use Shopwwi\WebmanSocialite\AbstractUser;
    class LineProvider extends AbstractProvider
    {
        public const NAME = 'line';
    
        protected string $baseUrl = 'https://api.line.me/oauth2/';
    
        protected string $version = 'v2.1';
    
        protected array $scopes = ['profile'];
    
        protected function getAuthUrl(): string
        {
            $this->state = $this->state ?: \md5(\uniqid(Contracts\SHOPWWI_SOC_STATE, true));
    
            return $this->buildAuthUrlFromBase('https://access.line.me/oauth2/'.$this->version.'/authorize');
        }
    
        protected function getTokenUrl(): string
        {
            return $this->baseUrl.$this->version.'/token';
        }
    
        /**
         * @param string $code
         * @return array
         */
        protected function getTokenFields(string $code): array
        {
            return parent::getTokenFields($code) + [Contracts\SHOPWWI_SOC_GRANT_TYPE => Contracts\SHOPWWI_SOC_AUTHORIZATION_CODE];
        }
    
        /**
         * @param string $token
         * @return array
         * @throws \GuzzleHttp\Exception\GuzzleException
         */
        protected function getUserByToken(string $token): array
        {
            $response = $this->getHttpClient()->get(
                'https://api.line.me/v2/profile',
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer '.$token,
                    ],
                ]
            );
    
            return $this->fromJsonBody($response);
        }
    
        /**
         * @param array $user
         * @return Contracts\User
         */
        protected function mapUserToObject(array $user): Contracts\User
        {
            return new AbstractUser([
                Contracts\SHOPWWI_SOC_ID => $user['userId'] ?? null,
                Contracts\SHOPWWI_SOC_NAME => $user['displayName'] ?? null,
                Contracts\SHOPWWI_SOC_NICKNAME => $user['displayName'] ?? null,
                Contracts\SHOPWWI_SOC_AVATAR => $user['pictureUrl'] ?? null,
                Contracts\SHOPWWI_SOC_EMAIL => null,
            ]);
        }
    }
```


## 平台

不同的平台有不同的配置方法，为了确保工具的正常运行，所以请确保你所使用的平台的配置都是如期设置的。

### [支付宝](https://opendocs.alipay.com/open/200/105310#s2)

请按如下方式配置

```php
$code = \request()->input('code');
$user = Socialite::driver('alipay')->userFromCode($code);

// 详见文档后面 "User interface"
$user->getId();        
$user->getNickname();
$user->getUsername();
$user->getName();
...
```

### [钉钉](https://ding-doc.dingtalk.com/doc#/serverapi3/mrugr3)

如文档所示

> 注意：该工具仅支持 QR code 连接到第三方网站，用来获取用户信息（openid， unionid 和 nickname）

```php

$code = \request()->input('code');
$user = Socialite::driver('dingtalk')->userFromCode($code);

// 详见文档后面 "User interface"
$user->getId();       
$user->getNickname();  
$user->getUsername(); 
$user->getName(); 
...
```

### [抖音](https://open.douyin.com/platform/doc/OpenAPI-oauth2)


```php

$code = \request()->input('code');
$user = Socialite::driver('douyin')->userFromCode($code);
// 通过access_token获取用户信息时，需先设置openId
$openId = '4154d454..5561';
$token = '';
$user = Socialite::driver('douyin')->withOpenId($openId)->userFromToken($token);

```

### [头条](https://open.douyin.com/platform/resource/docs/develop/permission/toutiao-or-xigua/OAuth2.0/)


```php
$code = \request()->input('code');
$user = Socialite::driver('toutiao')->userFromCode($code);
// 通过access_token获取用户信息时，需先设置openId
$openId = '4154d454..5561';
$token = '';
$user = Socialite::driver('toutiao')->withOpenId($openId)->userFromToken($token);

```

### [西瓜](https://open.douyin.com/platform/resource/docs/develop/permission/toutiao-or-xigua/OAuth2.0/)


```php

$code = \request()->input('code');
$user = Socialite::driver('toutiao')->userFromCode($code);
//通过access_token获取用户信息时，需先设置openId
$openId = '4154d454..5561';
$token = '';
$user = Socialite::driver('toutiao')->withOpenId($openId)->userFromToken($token);

```


### [百度](https://developer.baidu.com/wiki/index.php?title=docs/oauth)

其他配置没啥区别，在用法上，可以很轻易的选择重定向登录页面的模式，通过 `withDisplay()`

- **page：**全屏形式的授权页面 (默认)，适用于 web 应用。
- **popup:** 弹框形式的授权页面，适用于桌面软件应用和 web 应用。
- **dialog:** 浮层形式的授权页面，只能用于站内 web 应用。
- **mobile:** Iphone/Android 等智能移动终端上用的授权页面，适用于 Iphone/Android 等智能移动终端上的应用。
- **tv:** 电视等超大显示屏使用的授权页面。
- **pad:** IPad/Android 等智能平板电脑使用的授权页面。

```php
$authUrl = Socialite::driver('baidu')->withDisplay('mobile')->redirect();

```

`popup` 模式是工具内默认的使用模式。`basic` 是默认使用的 scopes 值。

### [飞书](https://open.feishu.cn/document/ukTMukTMukTM/uITNz4iM1MjLyUzM)

通过一些简单的方法配置  app_ticket 就能使用内部应用模式

```php

$code = \request()->input('code');
$user = Socialite::driver('feishu')->withInternalAppMode()->userFromCode($code);

$appTicket = '';
$code = \request()->input('code');
$user = Socialite::driver('feishu')->withDefaultMode()->withAppTicket($appTicket)->userFromCode($code);

```

### [Lark](https://open.larksuite.com/document/ukTMukTMukTM/uITNz4iM1MjLyUzM)

通过一些简单的方法配置  app_ticket 就能使用内部应用模式

```php

$code = \request()->input('code');
$user = Socialite::driver('lark')->withInternalAppMode()->userFromCode($code);

$appTicket = '';
$code = \request()->input('code');
$user = Socialite::driver('lark')->withDefaultMode()->withAppTicket($appTicket)->userFromCode($code);


```

### [淘宝](https://open.taobao.com/doc.htm?docId=102635&docType=1&source=search)

其他配置与其他平台的一样，你能选择你想要展示的重定向页面类型通过使用 `withView()`

```php
$authUrl = Socialite::driver('taobao')->withView('wap')->redirect();
```

`web` 模式是工具默认使用的展示方式， `user_info` 是默认使用的 scopes 范围值。

### [微信](https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/Official_Accounts/official_account_website_authorization.html)

我们支持开放平台代表公众号进行第三方平台网页授权。

你只需要像下面这样输入你的配置。官方账号不需要授权。
```php
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
```


### [Coding](https://coding.net/help/openapi#oauth)

您需要额外配置 `team_url` 为您的团队域名



## 其他一些技巧

### 访问作用域

在重定向用户之前，你还可以使用 `scopes` 方法在请求中添加其他「作用域」。此方法将覆盖所有现有的作用域：

```php

$redirect = Socialite::driver('qq')->scopes(['scope1', 'scope2'])->redirect();

```

### 回调链接

你也可以动态设置 `redirect_uri` ，你可以使用以下方法来改变 `redirect_uri` URL:

```php
$url = 'your callback url.';

Socialite::driver('qq')->redirect($url);
// or
Socialite::driver('qq')->withRedirectUrl($url)->redirect();
```

### State

你的应用程序可以使用一个状态参数来确保响应属于同一个用户发起的请求，从而防止跨站请求伪造 (CSFR) 攻击。当恶意攻击者欺骗用户执行不需要的操作 (只有用户有权在受信任的 web 应用程序上执行) 时，就会发生 CSFR 攻击，所有操作都将在不涉及或警告用户的情况下完成。

这里有一个最简单的例子，说明了如何提供状态可以让你的应用程序更安全。在本例中，我们使用会话 ID 作为状态参数，但是您可以使用您想要为状态创建值的任何逻辑。

### 带着 `state` 参数的重定向

```php
<?php
session_start();
 
// Assign to state the hashing of the session ID
$state = hash('sha256', session_id());

$url = Socialite::driver('qq')->withState($state)->redirect();

return redirect($url); 
```

### 检验回调的 `state`

一旦用户授权你的应用程序，用户将被重定向回你的应用程序的 redirect_uri。OAuth 服务器将不加修改地返回状态参数。检查 redirect_uri 中提供的状态是否与应用程序生成的状态相匹配：

```php
<?php
session_start();
 
$state = \request()->input('state');
$code = \request()->input('code');
 
// Check the state received with current session id
if ($state != hash('sha256', session_id())) {
    exit('State does not match!');
}
$user = Socialite::driver('qq')->userFromCode($code);

// authorized
```

[查看更多关于 `state` 参数的文档](https://auth0.com/docs/protocols/oauth2/oauth-state)

### 可选参数

许多 OAuth providers 支持重定向请求中的可选参数。 要在请求中包含任何可选参数，请使用关联数组调用 `with` 方法：

```php
$response = Socialite::driver('qq')->with(['hd' => 'example.com'])->redirect();
```


## 返回用户信息

### 标准的 user api：

```php
$user = Socialite::driver('qq')->userFromCode($code);
```

你可以像这样以数组键的形式获取 user 属性：

```php
$user['id']; 
$user['nickname'];
$user['name']; 
$user['email'];
...
```

或者使用该 `User` 对象的方法：

```php

$user->getId();
$user->getNickname();
$user->getName();
$user->getEmail();
$user->getAvatar();
$user->getRaw();
$user->getAccessToken(); 
$user->getRefreshToken();
$user->getExpiresIn();
$user->getTokenResponse();

```

###  从 OAuth API 响应中取得原始数据

`$user->getRaw()` 方法会返回一个 **array**。

### 当你使用 userFromCode() 想要获取 token 响应的原始数据

`$user->getTokenResponse()` 方法会返回一个 **array** 里面是响应从获取 token 时候 API 返回的响应。

> 注意：当你使用 `userFromCode()` 时，这个方法只返回一个 **有效的数组**，否则将返回 **null**，因为 `userFromToken() ` 没有 token 的 HTTP 响应。

### 通过 access token 获取用户信息

```php
$accessToken = 'xxxxxxxxxxx';
$user = $socialite->userFromToken($accessToken);
```

# 参照

- [Alipay - 用户信息授权](https://opendocs.alipay.com/open/289/105656)
- [DingTalk - 扫码登录第三方网站](https://ding-doc.dingtalk.com/doc#/serverapi3/mrugr3)
- [Google - OpenID Connect](https://developers.google.com/identity/protocols/OpenIDConnect)
- [Github - Authorizing OAuth Apps](https://developer.github.com/apps/building-oauth-apps/authorizing-oauth-apps/)
- [Facebook - Graph API](https://developers.facebook.com/docs/graph-api)
- [Linkedin - Authenticating with OAuth 2.0](https://developer.linkedin.com/docs/oauth2)
- [微博 - OAuth 2.0 授权机制说明](http://open.weibo.com/wiki/%E6%8E%88%E6%9D%83%E6%9C%BA%E5%88%B6%E8%AF%B4%E6%98%8E)
- [QQ - OAuth 2.0 登录 QQ](http://wiki.connect.qq.com/oauth2-0%E7%AE%80%E4%BB%8B)
- [腾讯云 - OAuth2.0](https://cloud.tencent.com/document/product/306/37730#.E6.8E.A5.E5.85.A5.E8.85.BE.E8.AE.AF.E4.BA.91-oauth)
- [微信公众平台 - OAuth 文档](http://mp.weixin.qq.com/wiki/9/01f711493b5a02f24b04365ac5d8fd95.html)
- [微信开放平台 - 网站应用微信登录开发指南](https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1419316505&token=&lang=zh_CN)
- [微信开放平台 - 代公众号发起网页授权](https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1419318590&token=&lang=zh_CN)
- [企业微信 - OAuth 文档](https://open.work.weixin.qq.com/api/doc/90000/90135/91020)
- [企业微信第三方应用 - OAuth 文档](https://open.work.weixin.qq.com/api/doc/90001/90143/91118)
- [豆瓣 - OAuth 2.0 授权机制说明](http://developers.douban.com/wiki/?title=oauth2)
- [抖音 - 网站应用开发指南](http://open.douyin.com/platform/doc)
- [飞书 - 授权说明](https://open.feishu.cn/document/ukTMukTMukTM/uMTNz4yM1MjLzUzM)
- [Lark - 授权说明](https://open.larksuite.com/document/ukTMukTMukTM/uMTNz4yM1MjLzUzM)
- [Tapd - 用户授权说明](https://www.tapd.cn/help/show#1120003271001000093)
- [Line - OAuth 2.0](https://developers.line.biz/en/docs/line-login/integrate-line-login/)
- [Gitee - OAuth文档](https://gitee.com/api/v5/oauth_doc#/)


# License

MIT
