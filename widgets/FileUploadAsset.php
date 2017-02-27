<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\attachment\widgets;

use yii\web\AssetBundle;

class FileUploadAsset extends AssetBundle
{
    public $sourcePath = '@yuncms/attachment/widgets/assets';

    public $css = [
        'css/jquery.fileupload.css',
        'css/jquery.fileupload-ui.css'
    ];

    public $js = [
        'js/jquery.iframe-transport.js',
        'js/jquery.fileupload.js'
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\jui\JuiAsset'
    ];
}