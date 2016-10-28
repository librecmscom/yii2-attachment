<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\attachment;

use Yii;
use yii\helpers\FileHelper;

/**
 * Class Module
 * @package yuncms\attachment
 */
class Module extends \yii\base\Module
{

    /**
     * @var string 附件存储路径
     */
    public $storagePath = '@storageRoot';

    /**
     * @var string 附件访问路径
     */
    public $storage = '@storage';

    /**
     * @var integer the permission to be set for newly created directories.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * Defaults to 0775, meaning the directory is read-writable by owner and group,
     * but read-only for other users.
     */
    public $dirMode = 0775;

    /**
     * @var string 附件存储路径格式
     */
    public $pathFormat = '{yyyy}/{mm}{dd}';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->storagePath = Yii::getAlias($this->storagePath);
        if (!is_dir($this->storagePath)) {
            FileHelper::createDirectory($this->storagePath, $this->dirMode, true);
        }
    }

    /**
     * 返回允许上传的最大大小
     * @return int the max upload size in MB
     */
    public function getMaxUploadSize()
    {
        $maxUpload = (int)(ini_get('upload_max_filesize'));
        $maxPost = (int)(ini_get('post_max_size'));
        $memoryLimit = (int)(ini_get('memory_limit'));
        return min($maxUpload, $maxPost, $memoryLimit);
    }

    /**
     * 获取附件的存储路径
     * @param string $fileName 文件名不包含路径
     * @return string
     */
    public function getFilePath($fileName)
    {
        $filePath = Yii::getAlias($this->storagePath) . '/' . $this->getFileHome($fileName);
        if (!is_dir($filePath)) {//递归创建保存目录
            FileHelper::createDirectory($filePath,$this->dirMode,true);
        }
        return $filePath;
    }

    /**
     * 获取附件访问Url
     * @param string $filePath 附件相对路径
     * @return string
     */
    public function getFileUrl($filePath)
    {
        return Yii::getAlias($this->storage) . '/' . $filePath;
    }
    
    /**
     * 生成文件存储路径
     * @return mixed
     */
    public function getFileHome()
    {
        $time = time();
        $date = explode('-', date("Y-y-m-d-H-i-s"));
        $path = str_replace(
            ["{yyyy}", "{yy}", "{mm}", "{dd}", "{hh}", "{ii}", "{ss}", "{time}"],
            [$date[0], $date[1], $date[2], $date[3], $date[4], $date[5], $date[6], $time],
            $this->pathFormat
        );
        return $path;
    }
}