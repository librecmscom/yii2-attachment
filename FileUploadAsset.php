<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\attachment;

use yii\web\AssetBundle;

class FileUploadAsset extends AssetBundle
{
    public $sourcePath = '@vendor/yuncms/yii2-attachment/assets';

    public $css = [
        'css/jquery.fileupload.css'
    ];

    public $js = [
        'js/jquery.iframe-transport.js',
        'js/jquery.fileupload.js'
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\jui\JuiAsset',
    ];
}