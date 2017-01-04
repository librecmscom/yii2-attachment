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