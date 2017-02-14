<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\attachment\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * Class Attachment
 * @property int $id
 * @property int $user_id 上传用户uID
 * @property string $filename 文件名
 * @property string $original_name 文件原始名称
 * @property string $model 上传模型
 * @property string $hash 文件哈希
 * @property int $size 文件大小
 * @property string $type 文件类型
 * @property string $mine_type 文件类型
 * @property string $ext 文件后缀
 * @property string $path 存储路径
 * @property string $ip 用户IP
 * @property int $created_at 创建时间
 * @package yuncms\attachment\models
 */
class Attachment extends ActiveRecord
{
    /**
     * 定义数据表
     */
    public static function tableName()
    {
        return '{{%attachment}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    BaseActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
            [
                'class' => 'yii\behaviors\AttributeBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'ip'
                ],
                'value' => function ($event) {
                    return Yii::$app->request->userIP;
                }
            ],
            [
                'class' => 'yii\behaviors\AttributeBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'user_id'
                ],
                'value' => function ($event) {
                    return Yii::$app->user->id;
                }
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('attachment', 'ID'),
            'user_id' => Yii::t('attachment', 'User Id'),
            'filename' => Yii::t('attachment', 'Filename'),
            'original_name' => Yii::t('attachment', 'Original FileName'),
            'hash' => Yii::t('attachment', 'File Hash'),
            'size' => Yii::t('attachment', 'File Size'),
            'type' => Yii::t('attachment', 'File Type'),
            'mine_type' => Yii::t('attachment', 'File mineType'),
            'ext' => Yii::t('attachment', 'File Ext'),
            'path' => Yii::t('attachment', 'Path'),
            'ip' => Yii::t('attachment', 'User Ip'),
            'created_at' => Yii::t('attachment', 'Created At'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['filename', 'original_name'], 'required'],
        ];
    }

    public function getUrl()
    {
        return Yii::$app->getModule('attachment')->getUrl($this->path);
    }
}