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
use yii\web\Response;
use yii\web\UploadedFile;
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
                        'actions' => ['upload', 'ueditor', 'um-upload', 'sn-upload', 'editor-md', 'dialog','multiple-upload'],
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
        ];
    }

    /**
     * 显示Jquery Ui上传页面
     * @param string $callback 回调方法
     * @param int $fileCount 允许上传的文件数量
     * @return string
     */
    public function actionDialog($callback, $fileCount = 1)
    {
        $this->layout = false;
        return $this->render('dialog', [
            'callback' => $callback,
            'inputName' => 'file',
            'maxFileCount' => $fileCount,
            //分片大小
            'chunkSize' => $this->module->getMaxUploadSize() . 'mb',
            //最大上传大小
            'maxFileSize' => $this->module->getMaxUploadSize() . 'mb',
        ]);
    }

    /**
     * 文件上传
     * @return array|mixed|null
     * @throws \yii\base\InvalidConfigException
     */
    public function actionMultipleUpload()
    {
        $uploadedFile = UploadedFile::getInstanceByName($this->inputName);
        $params = Yii::$app->request->getBodyParams();
        $filename = $this->module->getUnusedPath($this->tempPath . DIRECTORY_SEPARATOR . $uploadedFile->name);
        $isUploadComplete = ChunkUploader::process($uploadedFile, $filename);
        if ($isUploadComplete) {
            if (Yii::$app->hasModule('attachment')) {
                $config = [
                    'maxFiles' => 1,
                    'extensions' => Yii::$app->getModule('attachment')->imageAllowFiles,
                    'checkExtensionByMimeType' => true,
                    'mimeTypes' => 'image/*',
                    "maxSize" => Yii::$app->getModule('attachment')->getMaxUploadByte(),
                ];

                $uploader = new Uploader([
                    'fileField' => $filename,
                    'config' => $config,
                ]);
                $uploader->saveLocal();
                $res = $uploader->getFileInfo();
                if ($res['state'] == 'SUCCESS') {
                    return [
                        "originalName" => $res['original'],
                        "name" => $res['title'],
                        "url" => $res['url'],
                        "size" => $res['size'],
                        "type" => $res['type'],
                        "state" => 'SUCCESS'
                    ];
                } else {
                    return [
                        "state" => Yii::t('app', 'File save failed'),
                    ];
                }
            } else {
                return [
                    'filename' => $filename,
                    'params' => $params,
                ];
            }
        }
        return null;
    }
}