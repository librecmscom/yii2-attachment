<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\attachment\widgets;

use Yii;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\widgets\InputWidget;
use yii\helpers\ArrayHelper;
use yuncms\attachment\FileUploadAsset;

/**
 * Class FileUpload
 * @package yuncms\attachment\widgets
 */
class FileUpload extends InputWidget
{
    /**
     * @var array 插件参数
     * @see https://github.com/blueimp/jQuery-File-Upload/wiki/Options
     */
    public $clientOptions = [];

    /**
     * 输入框参数
     * @var array
     */
    public $inputOptions = [];

    /**
     * @var array 事件处理
     */
    public $events = [];

    /**
     * 初始化该组件
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (!isset ($this->options ['id'])) {
            $this->options ['id'] = $this->getId();
        }
        if (isset($this->options['class'])) {
            $this->options['class'] .= ' sr-only';
        } else {
            $this->options['class'] = 'sr-only';
        }
        $this->inputOptions = array_merge([
            'class' => 'form-control',
        ], $this->inputOptions);

        $this->clientOptions = array_merge([
            'url' => Url::toRoute(['/attachment/upload/upload']),
            'type' => 'POST',
            'dataType' => 'json',
            //允许上传的文件数量
            'limitMultiFileUploads' => 1,
            //允许上传的文件字节
            'limitMultiFileUploadSize' => '',
            'acceptFileTypes' => '/(\.|\/)(gif|jpe?g|png)$/i',
            //'maxFileSize' => Yii::$app->getModule('attachment')->getMaxUploadSize() * 1024 * 1024,
            'autoUpload' => true,
            'formData' => []
        ], $this->clientOptions);
        $this->clientOptions['formData'][Yii::$app->request->csrfParam] = Yii::$app->request->csrfToken;

        $this->events = ArrayHelper::merge([
            'fileuploaddone' => new JsExpression("function (e, data){jQuery('#{$this->options['id']}').val(data.result.url);}"),
        ], $this->events);
    }

    /**
     * run
     */
    public function run()
    {
        echo Html::beginTag('div', ['class' => 'input-group']);
        if ($this->hasModel()) {
            echo Html::activeTextInput($this->model, $this->attribute, $this->inputOptions);
        } else {
            echo Html::textInput($this->name, $this->value, $this->inputOptions);
        }
        $fileID = $this->options ['id'] . '_file';
        echo Html::beginTag('span', ['class' => 'input-group-btn']);
        echo Html::beginTag('span', ['class' => 'btn btn-success fileinput-button']);
        //<i class="glyphicon glyphicon-plus"></i>
        echo Html::tag('i', '', ['class' => 'glyphicon glyphicon-plus']);
        //<span>选择文件...</span>
        echo Html::tag('span', Yii::t('attachment', 'Select file'));
        //<input id="fileupload" type="file" name="files[]">
        echo Html::fileInput($fileID, '', ['id' => $fileID]);
        echo Html::endTag('span');
        echo Html::endTag('span');
        echo Html::endTag('div');
        FileUploadAsset::register($this->view);

        $events = '';
        foreach ($this->events as $event => $callback) {
            $events .= ".bind('$event',{$callback})";
        }
        $events .= ';';

        $clientOptions = Json::encode($this->clientOptions);
        $this->view->registerJs("jQuery('#{$fileID}').fileupload($clientOptions)$events");

    }
}