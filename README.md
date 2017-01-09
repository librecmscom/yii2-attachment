# yii2-attachment
适用于YII2的附件管理模块,主要是附件的统一保存,获取,暂时未做入库保存部分。

安装
----

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yuncms/yii2-attachment
```

or add

```
"yuncms/yii2-attachment": "~1.0.0"
```

to the require section of your `composer.json` file.

##配置迁移

````
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
			//自动应答
            'interactive' => 0,
			//命名空间
			'migrationNamespaces' => [
                'yuncms\attachment\migrations',
                //etc..
            ],
        ],
    ],
````

````
./yii migrate/up
````

##模块配置

````
#定义语言包配置
'components' => [
    'i18n' => [
        'translations' => [
            'attachment' => [
                'class' => 'yii\i18n\PhpMessageSource',
                'basePath' => '@yuncms/attachment/messages',
            ],
        ]
    ]
],
'modules' => [
    'attachment' => [
        'class' => 'yuncms\attachment\Module',
        //etc..
    ],
]
````