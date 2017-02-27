<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\attachment\widgets;

use Yii;
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\helpers\ArrayHelper;
use yii\widgets\InputWidget;
use yii\base\InvalidConfigException;

/**
 * Class FileUpload
 * @package yuncms\attachment\widgets
 */
class FileUpload extends InputWidget
{
    public $serverUrl = ['/attachment/upload/upload'];
    public $buttonTitle = '选择文件';
    public $labelClass = 'btn btn-default btn-upload';
    public $icon = '<span class="fa fa-upload"></span>';
    public $formData = [];

    /** @var JsExpression */
    public $done = null;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if ($this->done === null) {
            $this->done = new JsExpression('function (e, data) {}');
        }
    }

    /**
     * @return string
     */
    public function run()
    {
        $this->registerClientScript();
        if (isset($this->options['class'])) {
            $this->options['class'] .= ' sr-only';
        } else {
            $this->options['class'] = 'sr-only';
        }
        if ($this->hasModel()) {
            $this->options['name'] = $this->name;
            $input = Html::activeFileInput($this->model, $this->attribute, ArrayHelper::merge($this->options, [
                'data-url' => Url::to($this->serverUrl)
            ]));
        } else {
            $input = Html::fileInput($this->name, null, ArrayHelper::merge($this->options, [
                'data-url' => Url::to($this->serverUrl)
            ]));
        }
        return Html::label($input . $this->icon . ' ' . $this->buttonTitle, $this->options['id'], [
            'class' => $this->labelClass,
            'title' => $this->buttonTitle,
        ]);
    }

    protected function registerClientScript()
    {
        $formDate = Json::encode($this->formData, 336);
        FileUploadAsset::register($this->view);
        $script = "jQuery('#{$this->options['id']}').fileupload({ dataType: 'json',formData: {$formDate},done: {$this->done}});";
        $this->view->registerJs($script, View::POS_READY);
    }
}