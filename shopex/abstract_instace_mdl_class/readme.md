
使用方法，直接copy到data目录下面，新建一个 `abstract` 方法，类名字自己改一下，data下面的文件直接继承一下


#### 例如实例一个 `refund_apply`
```
//实例化mdl
$this->instanceMdl('refund_apply', $appId);

//使用
$this->mdlS['refund_apply']->getRow();
```

##### 实例多个 mdl
```
//同一个app下的多个mdl
$this->instanceMdl(['image'=>['image', 'image_attach']]);

//多个app
$this->instanceMdl(['app1'=>['image', 'image_attach'], 'app2'=>['mdl-1', 'mdl-2']]);


数组的 key 代表 appid，value 代表mdl
```
