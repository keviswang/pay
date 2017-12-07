## 特点
- 命名不那么乱七八糟
- 隐藏开发者不需要关注的细节
- 根据支付宝、微信最新 API 开发而成
- 高度抽象的类，免去各种拼json与xml的痛苦
- 符合 PSR 标准，你可以各种方便的与你的框架集成
- 文件结构清晰易理解，可以随心所欲添加本项目中没有的支付网关
- 方法使用更优雅，不必再去研究那些奇怪的的方法名或者类名是做啥用的


## 运行环境
- PHP 5.6+
- composer


## 支持的支付网关

由于各支付网关参差不齐，所以我们抽象了两个方法 `driver()`，`gateway()`。

两个方法的作用如下：

`driver()` ： 确定支付平台，如 `alipay`,`wechat`;  

`gateway()`： 确定支付网关。通过此方法，确定支付平台下的支付网关。例如，支付宝下有 「电脑网站支付」，「手机网站支付」，「APP 支付」三种支付网关，通过传入 `web`,`wap`,`app` 确定。

详细思路可以查看源代码。

### 1、支付宝

- 电脑支付
- 手机网站支付
- APP 支付
- 刷卡支付
- 扫码支付

SDK 中对应的 driver 和 gateway 如下表所示：  

| driver | gateway |   描述       |
| :----: | :-----: | :-------:   |
| alipay | web     | 电脑支付     |
| alipay | wap     | 手机网站支付  |
| alipay | app     | APP 支付  |
| alipay | pos     | 刷卡支付  |
| alipay | scan    | 扫码支付  |
| alipay | transfer    | 帐户转账（可用于平台用户提现）  |
  
### 2、微信

- 公众号支付
- 小程序支付
- H5 支付
- 扫码支付
- 刷卡支付
- APP 支付

SDK 中对应的 driver 和 gateway 如下表所示：

| driver | gateway |   描述     |
| :----: | :-----: | :-------: |
| wechat | mp      | 公众号支付  |
| wechat | miniapp | 小程序支付  |
| wechat | wap     | H5 支付    |
| wechat | scan    | 扫码支付    |
| wechat | pos     | 刷卡支付    |
| wechat | app     | APP 支付  |
| wechat | transfer     | 企业付款  |

## 支持的方法

所有网关均支持以下方法

- pay(array $config_biz)  
说明：支付接口  
参数：数组类型，订单业务配置项，包含 订单号，订单金额等  
返回：mixed  详情请看「支付网关配置说明与返回值」一节。 

- refund(array|string $config_biz, $refund_amount = null)  
说明：退款接口  
参数：`$config_biz` 为字符串类型仅对`支付宝支付`有效，此时代表订单号，第二个参数为退款金额。  
返回：mixed  退款成功，返回 服务器返回的数组；否则返回 false；  

- close(array|string $config_biz)  
说明：关闭订单接口  
参数：`$config_biz` 为字符串类型时代表订单号，如果为数组，则为关闭订单业务配置项，配置项内容请参考各个支付网关官方文档。  
返回：mixed  关闭订单成功，返回 服务器返回的数组；否则返回 false；  

- find(string $out_trade_no)  
说明：查找订单接口  
参数：`$out_trade_no` 为订单号。  
返回：mixed  查找订单成功，返回 服务器返回的数组；否则返回 false；  

- verify($data, $sign = null)  
说明：验证服务器返回消息是否合法  
参数：`$data` 为服务器接收到的原始内容，`$sign` 为签名信息，当其为空时，系统将自动转化 `$data` 为数组，然后取 `$data['sign']`。  
返回：mixed  验证成功，返回 服务器返回的数组；否则返回 false；  


## 安装
```shell
composer require keviswang/pay
```

## 使用说明

### 0、一个完整的例子:

```php
<?php

namespace App\Http\Controllers;

use Yansongda\Pay\Pay;
use Illuminate\Http\Request;

class PayController extends Controller
{
    protected $config = [
        'wechat' => [
            'app_id' => 'wxb3f6xxxxxxxxxx',
            'mch_id' => '1457xxxxx2',
            'notify_url' => 'http://yansongda.cn/wechat_notify.php',
            'key' => 'mF2suE9sU6Mk1Cxxxxxxxxxx45',
            'cert_client' => './apiclient_cert.pem',
            'cert_key' => './apiclient_key.pem',
        ],
    ];

    public function index()
    {
        $config_biz = [
            'out_trade_no' => 'e2',
            'total_fee' => '1', // **单位：分**
            'body' => 'test body',
            'spbill_create_ip' => '8.8.8.8',
            'openid' => 'onkVf1FjWS5SBIihS-123456_abc',
        ];

        $pay = new Pay($this->config);

        return $pay->driver('wechat')->gateway('mp')->pay($config_biz);
    }

    public function notify(Request $request)
    {
        $pay = new Pay($this->config);
        $verify = $pay->driver('wechat')->gateway('mp')->verify($request->getContent());

        if ($verify) {
            file_put_contents('notify.txt', "收到来自微信的异步通知\r\n", FILE_APPEND);
            file_put_contents('notify.txt', '订单号：' . $verify['out_trade_no'] . "\r\n", FILE_APPEND);
            file_put_contents('notify.txt', '订单金额：' . $verify['total_fee'] . "\r\n\r\n", FILE_APPEND);
        } else {
            file_put_contents(storage_path('notify.txt'), "收到异步通知\r\n", FILE_APPEND);
        }

        echo "success";
    }
}

```

### 1、微信 - 公众号支付

#### 最小配置参数
```php
<?php

$config = [
    'wechat' => [
        'app_id' => '',             // 公众号APPID
        'mch_id' => '',             // 微信商户号
        'notify_url' => '',
        'key' => '',                // 微信支付签名秘钥
        'cert_client' => './apiclient_cert.pem',        // 客户端证书路径，退款时需要用到
        'cert_key' => './apiclient_key.pem',            // 客户端秘钥路径，退款时需要用到
    ],
];

$config_biz = [
    'out_trade_no' => '',           // 订单号
    'total_fee' => '',              // 订单金额，**单位：分**
    'body' => '',                   // 订单描述
    'spbill_create_ip' => '',       // 支付人的 IP
    'openid' => '',                 // 支付人的 openID
];
```

#### 所有配置参数
所有参数均为官方标准参数，无任何差别。[点击这里](https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_1 '微信支付官方文档') 查看官方文档。
```php
<?php

$config = [
    'wechat' => [
        'endpoint_url' => 'https://apihk.mch.weixin.qq.com/', // optional, default 'https://api.mch.weixin.qq.com/'
        'app_id' => '',             // 公众号APPID
        'mch_id' => '',             // 微信商户号
        'notify_url' => '',
        'key' => '',                // 微信支付签名秘钥
        'cert_client' => './apiclient_cert.pem',        // 客户端证书路径，退款时需要用到
        'cert_key' => './apiclient_key.pem',            // 客户端秘钥路径，退款时需要用到
    ],
];

$config_biz = [
    'out_trade_no' => '',           // 订单号
    'total_fee' => '',              // 订单金额，**单位：分**
    'body' => '',                   // 订单描述
    'spbill_create_ip' => '',       // 支付人的 IP
    'openid' => '',                 // 支付人的 openID
    
    // 自定义参数，可以为终端设备号(门店号或收银设备ID)，PC网页或公众号内支付可以传"WEB"
    'device_info' => '',
    
    // 商品详细描述，对于使用单品优惠的商户，改字段必须按照规范上传，详见“单品优惠参数说明”
    'detail' => '',
    
    // 附加数据，在查询API和支付通知中原样返回，可作为自定义参数使用。
    'attach' => '',
    
    // 符合ISO 4217标准的三位字母代码，默认人民币：CNY，详细列表请参见货币类型
    'fee_type' => '',
    
    // 订单生成时间，格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010。其他详见时间规则
    'time_start' => '',
    
    // 订单失效时间，格式为yyyyMMddHHmmss，如2009年12月27日9点10分10秒表示为20091227091010。其他详见时间规则注意：最短失效时间间隔必须大于5分钟
    'time_expire' => '',
    
    // 订单优惠标记，使用代金券或立减优惠功能时需要的参数，说明详见代金券或立减优惠
    'goods_tag' => '',
    
    // trade_type=NATIVE时（即扫码支付），此参数必传。此参数为二维码中包含的商品ID，商户自行定义。
    'product_id' => '',
    
    // 上传此参数no_credit--可限制用户不能使用信用卡支付
    'limit_pay' => '',
    
    // 该字段用于上报场景信息，目前支持上报实际门店信息。该字段为JSON对象数据，对象格式为{"store_info":{"id": "门店ID","name": "名称","area_code": "编码","address": "地址" }} ，字段详细说明请点击行前的+展开
    'scene_info' => '',
];
```

#### 返回值
- pay()  
类型：array  
说明：返回用于 微信内H5调起支付 的所需参数数组。后续调用不在本文档讨论范围内，具体请 [参考这里](https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=7_7&index=6)。

后续调用举例：  

```html
<script type="text/javascript">
        function onBridgeReady(){
            WeixinJSBridge.invoke(
                'getBrandWCPayRequest', {
                    "appId":"<?php echo $pay['appId']; ?>",     //公众号名称，由商户传入     
                    "timeStamp":"<?php echo $pay['timeStamp']; ?>",         //时间戳，自1970年以来的秒数     
                    "nonceStr":"<?php echo $pay['nonceStr']; ?>", //随机串     
                    "package":"<?php echo $pay['package']; ?>",     
                    "signType":"<?php echo $pay['signType']; ?>",         //微信签名方式：     
                    "paySign":"<?php echo $pay['paySign']; ?>" //微信签名 
                },
                function(res){     
                    if(res.err_msg == "get_brand_wcpay_request:ok" ) {}     // 使用以上方式判断前端返回,微信团队郑重提示：res.err_msg将在用户支付成功后返回    ok，但并不保证它绝对可靠。 
                }
            ); 
        }

        $(function(){
            $('#pay').click(function(){
                if (typeof WeixinJSBridge == "undefined"){
                   if( document.addEventListener ){
                       document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
                   }else if (document.attachEvent){
                       document.attachEvent('WeixinJSBridgeReady', onBridgeReady); 
                       document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
                   }
                }else{
                   onBridgeReady();
                }
            })
        });
</script>
```

### 8、微信 - 小程序支付

#### 最小配置参数
```php
<?php

$config = [
    'wechat' => [
        'miniapp_id' => '',             // 小程序APPID
        'mch_id' => '',             // 微信商户号
        'notify_url' => '',
        'key' => '',                // 微信支付签名秘钥
        'cert_client' => './apiclient_cert.pem',        // 客户端证书路径，退款时需要用到
        'cert_key' => './apiclient_key.pem',            // 客户端秘钥路径，退款时需要用到
    ],
];

$config_biz = [
    'out_trade_no' => '',           // 订单号
    'total_fee' => '',              // 订单金额，**单位：分**
    'body' => '',                   // 订单描述
    'spbill_create_ip' => '',       // 支付人的 IP
    'openid' => '',                 // 支付人的 openID
];
```

#### 所有配置参数
由于「小程序支付」和「公众号支付」都使用的是 JSAPI，所以，除了 APPID 一个使用的是公众号的 APPID 一个使用的是 小程序的 APPID 以外，该网关所有参数和 「公众号支付」 相同，具体请 [参考这里](https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_1)。

#### 返回值
- pay()  
类型：array  
说明：返回用于 小程序调起支付API 的所需参数数组。后续调用不在本文档讨论范围内，具体请 [参考这里](https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=7_7&index=3)。

### 9、微信 - H5 支付
#### 最小配置参数

```php
<?php

$config = [
    'wechat' => [
        'app_id' => '',             // 微信公众号 APPID
        'mch_id' => '',             // 微信商户号
        'return_url' => '',         // *此配置选项可选*，注意，该跳转 URL 只有跳转之意，没有同步通知功能
        'notify_url' => '',
        'key' => '',                // 微信支付签名秘钥
        'cert_client' => './apiclient_cert.pem',        // 客户端证书路径，退款时需要用到
        'cert_key' => './apiclient_key.pem',            // 客户端秘钥路径，退款时需要用到
    ],
];

$config_biz = [
    'out_trade_no' => '',           // 订单号
    'total_fee' => '',              // 订单金额，**单位：分**
    'body' => '',                   // 订单描述
    'spbill_create_ip' => '',       // 支付人的 IP
];
```

#### 所有配置参数
所有配置项和前面支付网关相差不大，请[点击这里查看](https://pay.weixin.qq.com/wiki/doc/api/H5.php?chapter=9_20&index=1).

#### 返回值
- pay()  
类型：string  
说明：返回微信支付中间页网址，可直接 302 跳转。

### 10、微信 - 扫码支付
这里使用「模式二」进行扫码支付，具体请[参考这里](https://pay.weixin.qq.com/wiki/doc/api/native.php?chapter=6_5)

#### 最小配置参数

```php
<?php

$config = [
    'wechat' => [
        'app_id' => '',             // 微信公众号 APPID
        'mch_id' => '',             // 微信商户号
        'notify_url' => '',
        'key' => '',                // 微信支付签名秘钥
        'cert_client' => './apiclient_cert.pem',        // 客户端证书路径，退款时需要用到
        'cert_key' => './apiclient_key.pem',            // 客户端秘钥路径，退款时需要用到
    ],
];

$config_biz = [
    'out_trade_no' => '',           // 订单号
    'total_fee' => '',              // 订单金额，**单位：分**
    'body' => '',                   // 订单描述
    'spbill_create_ip' => '',       // 调用 API 服务器的 IP
    'product_id' => '',             // 订单商品 ID
];
```

#### 所有配置参数
所有配置项和前面支付网关相差不大，请[点击这里查看](https://pay.weixin.qq.com/wiki/doc/api/native.php?chapter=9_1)

#### 返回值
- pay()  
类型：string  
说明：返回微信支付二维码 URL 地址，可直接将此 url 生成二维码，展示给用户进行扫码支付。

### 11、微信 - 刷卡支付

#### 最小配置参数
```php
<?php

$config = [
    'wechat' => [
        'app_id' => '',             // 公众号 APPID
        'mch_id' => '',             // 微信商户号
        'key' => '',                // 微信支付签名秘钥
        'cert_client' => './apiclient_cert.pem',        // 客户端证书路径，退款时需要用到
        'cert_key' => './apiclient_key.pem',            // 客户端秘钥路径，退款时需要用到
    ],
];

$config_biz = [
    'out_trade_no' => '',           // 订单号
    'total_fee' => '',              // 订单金额，**单位：分**
    'body' => '',                   // 订单描述
    'spbill_create_ip' => '',       // 支付人的 IP
    'auth_code' => '',              // 授权码
];
```

#### 所有配置参数
该网关所有参数和其它支付网关基本相同，具体请 [参考这里](https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_10&index=1)。

#### 返回值
- pay()  
类型：array  
说明：返回用于服务器返回的数组。返回参数请 [参考这里](https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_10&index=1)。

### 12、微信 - APP 支付

#### 最小配置参数
```php
<?php

$config = [
    'wechat' => [
        'appid' => '',              // APPID
        'mch_id' => '',             // 微信商户号
        'notify_url' => '',
        'key' => '',                // 微信支付签名秘钥
        'cert_client' => './apiclient_cert.pem',        // 客户端证书路径，退款时需要用到
        'cert_key' => './apiclient_key.pem',            // 客户端秘钥路径，退款时需要用到
    ],
];

$config_biz = [
    'out_trade_no' => '',           // 订单号
    'total_fee' => '',              // 订单金额，**单位：分**
    'body' => '',                   // 订单描述
    'spbill_create_ip' => '',       // 支付人的 IP
];
```

#### 所有配置参数
该网关所有参数和其它支付网关相同相同，具体请 [参考这里](https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=9_1)。

#### 返回值
- pay()  
类型：array  
说明：返回用于 小程序调起支付API 的所需参数数组。后续调用不在本文档讨论范围内，具体请 [参考这里](https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=8_5)。

### 12、微信 - 企业付款

#### 最小配置参数
```php
<?php

$config = [
    'wechat' => [
        'appid' => '',              // APPID
        'mch_id' => '',             // 微信商户号
        'key' => '',                // 微信支付签名秘钥
        'cert_client' => './apiclient_cert.pem',        // 客户端证书路径，退款时需要用到
        'cert_key' => './apiclient_key.pem',            // 客户端秘钥路径，退款时需要用到
    ],
];

$config_biz = [
    'partner_trade_no' => '',              //商户订单号
    'openid' => '',                        //收款人的openid
    'check_name' => 'NO_CHECK',            //NO_CHECK：不校验真实姓名\FORCE_CHECK：强校验真实姓名
//    're_user_name'=>'张三',              //check_name为 FORCE_CHECK 校验实名的时候必须提交
    'amount' => 100,                       //企业付款金额，单位为分
    'desc' => '帐户提现',                  //付款说明
    'spbill_create_ip' => '192.168.0.1',  //发起交易的IP地址
];
```

#### 所有配置参数
具体请看 [官方文档](https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_2)。

#### 返回值
- pay()  
类型：array  
说明：返回用于 支付结果 的数组。具体请 [参考这里](https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_2)。


## LICENSE
MIT
