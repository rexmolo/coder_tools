ECstore 一个微信扫码事件分离出来的代码

## 介绍

把微信扫码事件抽离了出来所有文件的位置`weixin/lib/event `

虽然抽离的不是很好，但是至少以后再写会比较方便，业务逻辑也比较好的隔离开了，抽离出来之后，在 weichat.php 的 scan方法中 是这样使用的

```

//加载事件处理配置, 所有事件可以自行根据需要写在event中
$eventAction = kernel::single('weixin_event_action');
$eventAction->handle($postData);
```

注意生成二维码的时候要这样

```
$qr = $qrCode->get_ticket_qrcode("coupon_{$id}");
```

实际上coupon，可以认为是一种场景，在下面的配置文件里面有体现，逻辑代码会根据这个coupon从配置里面获取到需要进行处理的场景业务逻辑入口类



实际上，这样抽离出来之后，不需要继续在这个scan方法中叠加代码了。👇会介绍如何使用这个代码

## **文件和目录结构如下：**

event    

​	-- config 

​		 processer.php   配置    

​	-- handler   放置自己的事件处理器    

​	-- helper    帮助函数目录    

​	-- interface 接口目录    

​	-- action.php  事件执行入口文件



## **配置文件**

首先你要在 processer.php 定义一个 key => vlaue， 类似下面这样

```PHP

//key 是场景，value中包含了 你的业务逻辑入口函数
return $processor = [
   'coupon' => [
      '_func'  => 'execute',
      'params' => '',
      '_class' => 'weixin_event_handler_coupon_action'
   ]
];
```

## 如何创建事件处理器 

handler 目录下面写你自己的业务逻辑，建议直接新建一个文件夹，这样如果你的业务逻辑包含多个类的时候，不至于搞的那么乱

#### 在handler目录创建事件处理器的几个点：

1. 必须实现接口` weixin_event_interface_event`

1. 在构造函数中可以接收到微信抛过来的数据，类似下面这样

```PHP
public function __construct( $weChatPostData ) {
   $this->weChatPostData   = $weChatPostData;
   $this->context['couponId'] = $this->helper->getSceneId( $this->weChatPostData['EventKey'] ,$this->weChatPostData['Event']);
}
```

3. 如果你有一些需要自己定义的参数，可以写在配置文件professor中的params中，当程序运行的时候你可以从$data中获取到

```PHP
public function execute( $data ) {}
```

当微信扫码的时候就会触发，所有事件的业务逻辑。你也可以调整 processer.php 中的配置的先后顺序从而使业务逻辑的执行顺序更换