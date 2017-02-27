<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\attachment\components;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yuncms\attachment\components\Uploader;

/**
 * Class UploadController
 * @package yuncms\attachment\controllers
 *
 * @property \yuncms\attachment\Module $module
 */
class UploadController extends Controller
{
    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'upload' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['upload', 'ueditor', 'um-upload', 'sn-upload', 'editor-md', 'plupload'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function actions()
    {
        return [
            'ueditor' => [
                'class' => 'xutl\ueditor\UEditorAction',
            ],
            'um-upload' => [
                'class' => 'xutl\umeditor\UMeditorAction',
            ],
            'sn-upload' => [
                'class' => 'xutl\summernote\SummerNoteAction',
            ],
            'editor-md' => [
                'class' => 'xutl\editormd\MarkdownAction',
            ],
            'plupload' => [
                'class' => 'xutl\plupload\PluploadAction',
                'onComplete' => function ($filename, $params) {
                    $uploader = new Uploader([
                        'fileField' => $filename,
                        'config' => [
                            'maxFiles' => 1,
                            'extensions' => $this->module->fileAllowFiles,
                            'maxSize' => $this->module->getMaxUploadByte(),
                            'checkExtensionByMimeType' => false,
                        ],
                    ]);
                    $uploader->saveLocal();
                    return $uploader->getFileInfo();
                }
            ],
        ];
    }

    /**
     * Ajax
     */
    public function actionUpload()
    {
        return $this->render('upload', [

        ]);

        print_r(Yii::$app->request->getRawBody());
    }
}