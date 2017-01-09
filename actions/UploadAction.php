<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\attachment\actions;

use Yii;
use yii\base\Action;
use yii\web\Response;
use yii\web\HttpException;
use yii\web\UploadedFile;

/**
 * Class UploadAction
 * @package yuncms\attachment\actions
 */
class UploadAction extends Action
{
    /**
     * @var string file input name.
     */
    public $inputName = 'file';

    /**
     * @var callable success callback with signature: `function($filename, $params)`
     */
    public $onComplete;

    /**
     * Initializes the action and ensures the temp path exists.
     */
    public function init()
    {
        parent::init();
        Yii::$app->response->format = Response::FORMAT_JSON;
        $this->tempPath = Yii::getAlias($this->tempPath);
        if (!is_dir($this->tempPath)) {
            FileHelper::createDirectory($this->tempPath, $this->dirMode, true);
        }
    }

    /**
     * Runs the action.
     * This method displays the view requested by the user.
     * @throws HttpException if the view is invalid
     */
    public function run()
    {
        $uploadedFile = UploadedFile::getInstanceByName($this->inputName);
    }
}