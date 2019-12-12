# redPay
微信sdk支付

## 第一步
https://github.com/ 前往github创建一个仓库

## 第二步
git clone 把代码客隆到本地 然后运行 composer init 初始化composer

## 第三步
在生成的composer.json中添加 一下代码
```
	"autoload": {
        "psr-4": {
            "RedPay\\WechatPay\\": "src/WechatPay"
        }
    }
```
注意：
-- psr-4冒号前 表示的是命民空间。后面是具体的路径

