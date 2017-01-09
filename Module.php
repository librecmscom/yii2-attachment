<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\attachment;

use Yii;
use yii\web\UploadedFile;
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
    public $uploadRoot = '@uploadroot';

    /**
     * @var string 附件访问路径
     */
    public $uploads = '@web/uploads';

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
     * @var string 最大允许上传大小需小于系统限制才能生效
     */
    public $maxUploadSize = '2M';

    /**
     * 初始化附件存储路径
     */
    public function init()
    {
        parent::init();
        $this->uploadRoot = Yii::getAlias($this->uploadRoot);
        if (!is_dir($this->uploadRoot)) {
            FileHelper::createDirectory($this->uploadRoot, $this->dirMode, true);
        }
        $this->uploads = Yii::getAlias($this->uploads);
    }

    /**
     * 返回允许上传的最大大小单位 MB
     * @return int the max upload size in MB
     */
    public function getMaxUploadSize()
    {
        $maxUpload = (int)(ini_get('upload_max_filesize'));
        $maxPost = (int)(ini_get('post_max_size'));
        $memoryLimit = (int)(ini_get('memory_limit'));
        $this->maxUploadSize = (int)$this->maxUploadSize;
        return min($maxUpload, $maxPost, $memoryLimit, $this->maxUploadSize);
    }

    /**
     * 返回允许上传的最大大小单位 Byte
     * @return int the max upload size in Byte
     */
    public function getMaxUploadByte()
    {
        return $this->getMaxUploadSize() * 1024 * 1024;
    }

    /**
     * 获取附件的存储路径
     * @return string
     */
    public function getFilePath()
    {
        $filePath = $this->uploadRoot . '/' . $this->getFileHome();
        if (!is_dir($filePath)) {//递归创建保存目录
            FileHelper::createDirectory($filePath, $this->dirMode, true);
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
        return $this->uploads . '/' . $filePath;
    }

    /**
     * 生成文件存储路径
     * @return string
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

    /**
     * 保存文件
     * @param string|UploadedFile $file
     * @return bool
     */
    public function save($file)
    {
        $fileName = rand(1000, 1000000000);
        if ($file instanceof UploadedFile) {
            $filePath = $this->getFilePath() . $fileName . '.' . $file->extension;
            return $file->saveAs($filePath);
        } else if ((is_string($file) && is_file($file)) && file_exists($file)) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $filePath = $this->getFilePath() . $fileName . '.' . $extension;
            return rename($file, $filePath);
        } else {
            return false;
        }
    }
}