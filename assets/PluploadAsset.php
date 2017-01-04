<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\attachment\assets;

use Yii;
use yii\web\AssetBundle;

/**
 * Class PluploadAsset
 * @package yuncms\attachment\assets
 */
class PluploadAsset extends AssetBundle
{
    /**
     * @var string 源代码路径
     */
    //public $sourcePath = '@bower/plupload/js';

    /**
     * @var array 包含的JS
     */
    public $js = [
        '//cdn.bootcss.com/plupload/2.1.9/plupload.full.min.js',
    ];

    /**
     * @var array 定义依赖
     */
    public $depends = [
        'yii\web\JqueryAsset',
    ];

    public function init()
    {
        parent::init();
        $this->js[] = '//cdn.bootcss.com/plupload/2.1.9/i18n/' . str_replace('-', '_', Yii::$app->language) . '.js';
    }
}