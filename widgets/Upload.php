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
use yii\helpers\ArrayHelper;
use yii\widgets\InputWidget;
use yuncms\attachment\ModuleTrait;
use xutl\plupload\PluploadAsset;

/**
 * Class Upload
 * @package yuncms\attachment\widgets
 */
class Upload extends InputWidget
{
    use ModuleTrait;

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
        if (!isset ($this->options ['class'])) {
            $this->options ['class'] = 'form-control';
        }
        $this->registerClientScript();
    }

    /**
     * 执行上传
     */
    public function run()
    {
        $options = Json::encode($this->clientOptions);
        echo Html::beginTag('div', ['class' => 'input-group']);
        if ($this->hasModel()) {
            echo Html::activeInput('text', $this->model, $this->attribute, $this->options);
        } else {
            echo Html::input('text', $this->name, $this->value, $this->options);
        }
        echo Html::tag('span', $this->renderToggleButton(), ['class' => 'input-group-btn']);
        echo Html::endTag('div');

        $id = md5($this->options ['id']);
        $events = '';
        foreach ($this->events as $event => $callback) {
            $events .= "uploader_{$id}.bind('$event', $callback);";
        }
        $this->view->registerJs("var uploader_{$id} = new plupload.Uploader($options);uploader_{$id}.init();$events");
    }

    /**
     * 注册客户端脚本
     */
    protected function registerClientScript()
    {
        $this->toggleButton = ArrayHelper::merge([
            'id' => "upload_{$this->options ['id']}_browse",
            'class' => 'btn btn-default',
            'label' => Html::tag('span', '', ['class' => 'glyphicon glyphicon-circle-arrow-up']) . ' ' . Yii::t('attachment', 'Select Files'),
        ], $this->toggleButton);

        if (!isset($this->errorContainer))
            $this->errorContainer = "upload_{$this->options ['id']}_em";

        if (!isset($this->clientOptions['multipart_params']))
            $this->clientOptions['multipart_params'] = [];

        $this->clientOptions['multipart_params'][Yii::$app->request->csrfParam] = Yii::$app->request->csrfToken;

        $bundle = PluploadAsset::register($this->getView());

        $this->clientOptions = ArrayHelper::merge([
            'file_data_name' => 'file',
            'filters' => [
                'mime_types' => [
                    ['title' => 'Image files', 'extensions' => $this->getModule()->imageAllowFiles],
                    ['title' => 'Files', 'extensions' => $this->getModule()->fileAllowFiles],
                    ['title' => 'Video files', 'extensions' => $this->getModule()->videoAllowFiles],
                ],
                'max_file_size' => $this->getModule()->getMaxUploadSize() . 'mb',
                'prevent_duplicates' => true
            ],
            'multi_selection' => false,
            'browse_button' => $this->toggleButton ['id'],
            'url' => Url::toRoute('/attachment/upload/plupload'),
            'container' => $this->options ['id'],
            'runtimes' => 'gears,html5,flash,silverlight,browserplus',
            'flash_swf_url' => "{$bundle->baseUrl}/Moxie.swf",
            'silverlight_xap_url' => "{$bundle->baseUrl}/Moxie.xap",
            'max_file_size' => $this->getModule()->getMaxUploadSize() . 'mb',
            'chunk_size' => $this->getModule()->getMaxUploadSize() . 'mb',
            'error_container' => "#{$this->errorContainer}",
        ], $this->clientOptions);
        //用户选择文件时触发
        $this->events = ArrayHelper::merge([
            'FilesAdded' => new JsExpression("function (uploader,files){uploader.start();}"),
            'FileUploaded' => new JsExpression("function (uploader,files, responseObject){var response = JSON.parse(responseObject.response);jQuery('#{$this->options['id']}').val(response.url);}"),
            'Error' => new JsExpression("function(uploader,errObject){
            if(errObject.code == -200) {var response = JSON.parse(errObject.response);alert(response.message);} else {alert(errObject.message);}console.log(errObject);}"),
        ], $this->events);
    }

    /**
     * 渲染触发按钮
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
}