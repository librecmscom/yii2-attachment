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

/**
 * Class UploadController
 * @package yuncms\attachment\controllers
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
                        'actions' => ['upload', 'ueditor'],
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
                'class' => 'yuncms\attachment\actions\UEditorAction',
            ],
            'um-upload' => [
                'class' => 'xutl\umeditor\UMeditorAction',
                'onComplete' => [$this, 'saveFile']
            ],
            'sn-upload' => [
                'class' => 'xutl\summernote\SummerNoteAction',
                'onComplete' => [$this, 'saveFile']
            ],
        ];
    }

    /**
     * 保存附件
     * @param string $filename
     * @param array $params
     * @return string 附件访问Url
     */
    protected function saveFile($filename, $params)
    {
        //返回图像的Url地址
        return '';
    }


    public function actionUpload()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['url' => '/uploads/img.img'];
    }
}