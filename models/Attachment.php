<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\attachment\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Class Attachment
 * @property int $id
 * @property int $user_id 上传用户uID
 * @property string $name 文件名
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
}