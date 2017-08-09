# yii2-attachment

适用于YII2的附件管理模块,主要是附件的统一保存,获取,暂时未做入库保存部分。

[![Latest Stable Version](https://poser.pugx.org/yuncms/yii2-attachment/v/stable.png)](https://packagist.org/packages/yuncms/yii2-attachment)
[![Total Downloads](https://poser.pugx.org/yuncms/yii2-attachment/downloads.png)](https://packagist.org/packages/yuncms/yii2-attachment)
[![Reference Status](https://www.versioneye.com/php/yuncms:yii2-attachment/reference_badge.svg)](https://www.versioneye.com/php/yuncms:yii2-attachment/references)
[![Build Status](https://img.shields.io/travis/yiisoft/yii2-attachment.svg)](http://travis-ci.org/yuncms/yii2-attachment)
[![Dependency Status](https://www.versioneye.com/php/yuncms:yii2-attachment/dev-master/badge.png)](https://www.versioneye.com/php/yuncms:yii2-attachment/dev-master)
[![License](https://poser.pugx.org/yuncms/yii2-attachment/license.svg)](https://packagist.org/packages/yuncms/yii2-attachment)

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
$ composer require yuncms/yii2-attachment
```

or add

```
"yuncms/yii2-attachment": "~2.0.0"
```

to the `require` section of your `composer.json` file.

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

## License

This is released under the MIT License. See the bundled [LICENSE.md](LICENSE.md)
for details.