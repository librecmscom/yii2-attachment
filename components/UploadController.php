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
                        'actions' => ['upload', 'ueditor','um-upload','sn-upload','editor-md'],
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
}