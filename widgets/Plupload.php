<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\attachment\widgets;

use Yii;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\widgets\InputWidget;
use yii\helpers\ArrayHelper;
use yii\bootstrap\BootstrapPluginAsset;
use yuncms\attachment\assets\PluploadAsset;

/**
 * Wrapper for Plupload
 * A multiple file upload utility using Flash, Silverlight, Google Gears, HTML5 or BrowserPlus.
 * @url http://www.plupload.com/
 * @version 1.0
 * @author Bound State Software
 */
class Plupload extends InputWidget
{

    /**
     * @var array the HTML attributes for the widget container tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options;

    public $clientOptions = [];

    /**
     * @var array the options for rendering the toggle button tag.
     *      The toggle button is used to toggle the visibility of the modal window.
     *      If this property is false, no toggle button will be rendered.
     *      The following special options are supported:
     *      - tag: string, the tag name of the button. Defaults to 'button'.
     *      - label: string, the label of the button. Defaults to 'Show'.
     *      The rest of the options will be rendered as the HTML attributes of the button tag.
     *      Please refer to the [Modal plugin help](http://getbootstrap.com/javascript/#modals)
     *      for the supported HTML attributes.
     */
    public $toggleButton = [];

    /**
     * @var array the options for rendering the progress tag.
     */
    public $progressOptions = false;

    /**
     * ID of the error container.
     * @var string
     */
    public $errorContainer;

    /**
     * The JavaScript event callbacks to attach to Plupload object.
     * @link http://www.plupload.com/example_events.php
     * In addition to the standard events, this widget adds a "FileSuccess"
     * event that is fired when a file is uploaded without error.
     * NOTE: events signatures should all have a first argument for event, in
     * addition to the arguments documented on the Plupload website.
     * @var array
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
        $this->toggleButton = ArrayHelper::merge([
            'id' => "plupload_{$this->options ['id']}_browse",
            'class' => 'btn btn-default',
            'label' => Html::tag('span', '', ['class' => 'glyphicon glyphicon-circle-arrow-up']) . ' ' . Yii::t('attachment', 'Select Files'),
        ], $this->toggleButton);

        if (!isset($this->errorContainer))
            $this->errorContainer = "plupload_{$this->options ['id']}_em";

        if (!isset($this->clientOptions['multipart_params']))
            $this->clientOptions['multipart_params'] = [];

        $this->clientOptions['multipart_params'][Yii::$app->request->csrfParam] = Yii::$app->request->csrfToken;

        $bundle = PluploadAsset::register($this->getView());

        /** @var \yuncms\attachment\Module $module */
        $module = Yii::$app->getModule('system');
        $attachment = $module->attachment;

        $this->clientOptions = ArrayHelper::merge([
            'file_data_name' => 'file',
            'filters' => [
                'mime_types' => [
                    ['title' => 'Image files', 'extensions' => $attachment['imageAllowFiles']],
                    ['title' => 'Files', 'extensions' => $attachment['fileAllowFiles']],
                    ['title' => 'Video files', 'extensions' => $attachment['videoAllowFiles']],
                ],
                'max_file_size' => ($attachment['maxSize'] / 1024 / 1024) . 'mb',
                'prevent_duplicates' => true
            ],
            'multi_selection' => false,
            'browse_button' => $this->toggleButton ['id'],
            'url' => Url::toRoute('/system/upload/plupload'),
            'container' => $this->options ['id'],
            'runtimes' => 'gears,html5,flash,silverlight,browserplus',
            'flash_swf_url' => "{$bundle->baseUrl}/Moxie.swf",
            'silverlight_xap_url' => "{$bundle->baseUrl}/Moxie.xap",
            'max_file_size' => ($attachment['maxSize'] / 1024 / 1024) . 'mb',
            'chunk_size' => ($attachment['maxSize'] / 1024 / 1024) . 'mb',
            'error_container' => "#{$this->errorContainer}",
        ], $this->clientOptions);
        if ($this->progressOptions !== false) {
            BootstrapPluginAsset::register($this->getView());
            $this->progressOptions = ArrayHelper::merge([
                'id' => "plupload_{$this->options ['id']}_progress",
                'class' => 'progress-bar progress-bar-info',
                'role' => 'progressbar',
                'aria-valuenow' => 0,
                'aria-valuemin' => 0,
                'aria-valuemax' => 100,
            ], $this->progressOptions);
            $this->events['BeforeUpload'] = new JsExpression("function (uploader,files){jQuery('#{$this->progressOptions['id']}').parent().show();}");
            $this->events['UploadProgress'] = new JsExpression("function (uploader,files){jQuery('#{$this->progressOptions['id']}').attr('aria-valuenow',files.percent);jQuery('#{$this->progressOptions['id']}').css('width',files.percent+'%');}");
        }
        //用户选择文件时触发
        $this->events = ArrayHelper::merge([
            'FilesAdded' => new JsExpression("function (uploader,files){uploader.start();}"),
            'FileUploaded' => new JsExpression("function (uploader,files, responseObject){var response = JSON.parse(responseObject.response);jQuery('#{$this->options['id']}').val(response.url);}"),
            'UploadComplete' => new JsExpression("function(uploader,files){jQuery('#{$this->progressOptions['id']}').parent().hide();}"),
            'Error' => new JsExpression("function(uploader,errObject){
            if(errObject.code == -200) {var response = JSON.parse(errObject.response);alert(response.message);} else {alert(errObject.message);}console.log(errObject);}"),
        ], $this->events);

    }

    public function run()
    {
        $options = Json::encode($this->clientOptions);
        $id = md5($this->options ['id']);
        $events = '';
        foreach ($this->events as $event => $callback) {
            $events .= "uploader_{$id}.bind('$event', $callback);";
        }
        $this->view->registerJs("var uploader_{$id} = new plupload.Uploader($options);uploader_{$id}.init();$events");
        echo Html::beginTag('div', ['class' => 'input-group']);
        if ($this->hasModel()) {
            echo Html::activeInput('text', $this->model, $this->attribute, $this->options);
        } else {
            echo Html::input('text', $this->name, $this->value, $this->options);
        }
        echo Html::tag('span', $this->renderToggleButton(), ['class' => 'input-group-btn']);
        echo Html::endTag('div');
        echo $this->renderProgress();

    }

    /**
     * Renders the toggle button.
     *
     * @return string the rendering result
     */
    protected function renderToggleButton()
    {
        if (($toggleButton = $this->toggleButton) !== false) {
            $tag = ArrayHelper::remove($toggleButton, 'tag', 'button');
            $label = ArrayHelper::remove($toggleButton, 'label', 'Select Files');
            if ($tag === 'button' && !isset ($toggleButton ['type'])) {
                $toggleButton ['type'] = 'button';
            }
            return Html::tag($tag, $label, $toggleButton);
        } else {
            return null;
        }
    }

    /**
     * 渲染进度条
     */
    protected function renderProgress()
    {
        if (($progressOptions = $this->progressOptions) !== false) {
            return Html::tag('div', Html::tag('div', '', $progressOptions), ['class' => 'progress', 'style' => 'height: 2px;display:none']);
        } else {
            return null;
        }
    }
}