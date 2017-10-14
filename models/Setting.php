<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\attachment\models;

use Yii;
use yii\base\Model;

/**
 * 实名认证设置
 * @package yuncms\attachment\models
 */
class Setting extends Model
{
    /**
     * @var string 附件存储路径
     */
    public $storePath;

    /**
     * @var string 附件访问路径
     */
    public $storeUrl;

    /**
     * @var string 图片上传最大大小
     */
    public $imageMaxSize;

    /**
     * @var array 允许上传的图片文件
     */
    public $imageAllowFiles;

    /**
     * @var string 视频上传最大大小
     */
    public $videoMaxSize;

    /**
     * @var array 允许的视频后缀
     */
    public $videoAllowFiles;

    /**
     * @var string 文件上传最大大小
     */
    public $fileMaxSize;

    /**
     * @var array 允许的文件后缀
     */
    public $fileAllowFiles;

    /**
     * 定义字段类型
     * @return array
     */
    public function getTypes()
    {
        return [
            'storePath' => 'string',
            'storeUrl' => 'string',
            'imageMaxSize' => 'string',
            'imageAllowFiles' => 'string',
            'videoMaxSize' => 'string',
            'videoAllowFiles' => 'string',
            'fileMaxSize' => 'string',
            'fileAllowFiles' => 'string',
        ];
    }

    /**
     * 验证规则
     * @return array
     */
    public function rules()
    {
        return [
            [['storePath', 'storeUrl', 'imageMaxSize', 'imageAllowFiles', 'videoMaxSize', 'videoAllowFiles', 'fileMaxSize', 'fileAllowFiles'], 'string'],
            ['storePath', 'default', 'value' => '@root/uploads'],
            ['storeUrl', 'url'],
            ['imageMaxSize', 'default', 'value' => '2M'],
            ['imageAllowFiles', 'default', 'value' => 'png,jpg,jpeg,gif,bmp'],
            ['videoMaxSize', 'default', 'value' => '100M'],
            ['videoAllowFiles', 'default', 'value' => 'flv,swf,mkv,avi,rm,rmvb,mpeg,mpg,ogg,ogv,mov,wmv,mp4,webm,mp3,wav,mid'],
            ['fileMaxSize', 'default', 'value' => '100M'],
            ['fileAllowFiles', 'default', 'value' => 'rar,zip,tar,gz,7z,bz2,cab,iso,doc,docx,xls,xlsx,ppt,pptx,pdf,txt,md,xml,xmind'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'storePath' => Yii::t('attachment', 'Store Path'),
            'storeUrl' => Yii::t('attachment', 'Storage Url'),
            'imageMaxSize' => Yii::t('attachment', 'Image Max Size'),
            'imageAllowFiles' => Yii::t('attachment', 'Image Allow Files'),
            'videoMaxSize' => Yii::t('attachment', 'Video Max Size'),
            'videoAllowFiles' => Yii::t('attachment', 'Video Allow Files'),
            'fileMaxSize' => Yii::t('attachment', 'File Max Size'),
            'fileAllowFiles' => Yii::t('attachment', 'File Allow Files'),
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