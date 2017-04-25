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
                        'actions' => ['upload', 'ueditor', 'um-upload', 'sn-upload', 'editor-md', 'pl-upload', 'dialog'],
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
            'pl-upload' => [
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
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionMultipleUpload()
    {
        //响应格式
        Yii::$app->response->format = Response::FORMAT_JSON;
        //上传文件对象
        $uploadedFile = UploadedFile::getInstanceByName($this->inputName);
        //上传的原始文件名
        $originalName = Yii::$app->request->post('name');
        //文件临时存储路径
        $tempName = $this->module->getUserDirPath() . $originalName;
        //请求的参数
        $params = Yii::$app->request->post();
        //当前上传的分片
        $chunk = (int)Yii::$app->request->post('chunk', 0);
        //总分片数
        $chunks = (int)Yii::$app->request->post('chunks', 0);

        //判断上传状态
        if (!$uploadedFile || $uploadedFile->getHasError()) {
            throw new Exception(Yii::t('system','Failed to upload file'));
        }

//        if ($originalName != 'blob') {//如果是第一个分片就验证文件后缀和分片大小
//            $validator = new FileValidator();
//            $validator->extensions = $this->module->extensions;
//            $validator->maxSize = $this->module->chunkSize * 1048576;
//            //$validator->checkExtensionByMimeType = false;
//            $error = '';
//            if (!$validator->validate($uploadedFile, $error)) {
//                throw new Exception($error);
//            }
//        }

        //分片临时文件
        $out = fopen($tempName, $chunk == 0 ? 'wb' : 'ab');
        if (!$out) {
            throw new Exception(Yii::t('system','Failed to open output stream'));
        }
        // 读取当前上传的分片流写入分片临时文件
        $tmp = fopen($uploadedFile->tempName, 'rb');
        if ($tmp) {
            while ($buff = fread($tmp, 4096)) {
                fwrite($out, $buff);
            }
        } else {
            throw new Exception(Yii::t('system','Failed to open input stream'));
        }
        fclose($tmp);
        fclose($out);
        //删除分片临时文件
        unlink($uploadedFile->tempName);
        // 检查所有分片是否已经上传OK
        if (!$chunks || $chunk == $chunks - 1) {
            //rename($filePath, $filePath);
            return $this->module->save($tempName, $originalName, $params);
        }
        return null;
    }
}