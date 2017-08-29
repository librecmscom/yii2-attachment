<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\attachment\backend\models;

use Yii;
use yii\base\Model;

/**
 * 实名认证设置
 * @package yuncms\attachment\backend\models
 */
class Setting extends Model
{
    /**
     * @var string 附件存储路径
     */
    public $uploadRoot = '@root/uploads';

    /**
     * @var string 附件访问路径
     */
    public $uploads = '@web/uploads';

    /**
     * @var string the directory to store temporary files during conversion. You may use path alias here.
     * If not set, it will use the "plupload" subdirectory under the application runtime path.
     */
    public $tempPath = '@runtime/attach';

    /**
     * @var integer the permission to be set for newly created directories.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * Defaults to 0775, meaning the directory is read-writable by owner and group,
     * but read-only for other users.
     */
    public $dirMode = 0775;

    /**
     * @var string 图片上传最大大小
     */
    public $imageMaxSize = '2M';

    /**
     * @var array 允许上传的图片文件
     */
    public $imageAllowFiles = 'png,jpg,jpeg,gif,bmp';

    /**
     * @var string 视频上传最大大小
     */
    public $videoMaxSize = '100M';

    /**
     * @var array 允许的视频后缀
     */
    public $videoAllowFiles = 'flv,swf,mkv,avi,rm,rmvb,mpeg,mpg,ogg,ogv,mov,wmv,mp4,webm,mp3,wav,mid';

    /**
     * @var string 文件上传最大大小
     */
    public $fileMaxSize = '100M';

    /**
     * @var array 允许的文件后缀
     */
    public $fileAllowFiles = 'rar,zip,tar,gz,7z,bz2,cab,iso,doc,docx,xls,xlsx,ppt,pptx,pdf,txt,md,xml,xmind';

    /**
     * 定义字段类型
     * @return array
     */
    public function getTypes()
    {
        return [
            'enableMachineReview' => 'boolean',
            'idCardUrl' => 'string',
            'idCardPath' => 'string',
        ];
    }

    public function rules()
    {
        return [
            [[
                'uploadRoot',
                'uploads',
                'tempPath',
                'dirMode',
                'imageMaxSize',
                'imageAllowFiles',
                'videoMaxSize',
                'videoAllowFiles',
                'fileMaxSize',
                'fileAllowFiles'
            ], 'string'],

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('attachment', 'ID'),
            'uploads' => Yii::t('attachment', 'Storage Url'),
            'uploadRoot' => Yii::t('attachment', 'Store'),
            'tempPath'=>Yii::t('attachment', 'Store'),
            'maxSize' => Yii::t('attachment', 'Max Size'),
            'minSize' => Yii::t('attachment', 'Min Size'),
            'fileMaxSize' => Yii::t('attachment', 'File Max Size'),
            'fileAllowFiles' => Yii::t('attachment', 'File Allow Files'),
            'videoMaxSize' => Yii::t('attachment', 'Video Max Size'),
            'videoAllowFiles' => Yii::t('attachment', 'Video Allow Files'),
            'imageMaxSize' => Yii::t('attachment', 'Image Max Size'),
            'imageAllowFiles' => Yii::t('attachment', 'Image Allow Files'),
        ];
    }

    /**
     * 返回标识
     */
    public function formName()
    {
        return 'attachment';
    }
}