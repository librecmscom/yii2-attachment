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
use yuncms\attachment\models\Attachment;

/**
 * Class Module
 * @package yuncms\attachment
 */
class Module extends \yii\base\Module
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
     * @var integer the permission to be set for newly created directories.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * Defaults to 0775, meaning the directory is read-writable by owner and group,
     * but read-only for other users.
     */
    public $dirMode = 0775;

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
     * 获取附件访问Url
     * @param string $filePath 附件相对路径
     * @return string
     */
    public function getUrl($filePath)
    {
        return $this->uploads . '/' . $filePath;
    }

    /**
     * 获取文件后缀
     * @param string $fileName 文件名
     * @return string file extension
     */
    public function getExtension($fileName)
    {
        return strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    }

    /**
     * 获取附件文件名
     * @param string $extension
     * @return string
     */
    public function getFilename($extension)
    {
        return date('Ymdhis') . rand(100, 999) . '.' . $extension;
    }

    /**
     * 保存文件
     * @param string|UploadedFile $tempName 临时文件名或路径
     * @param string $uploadDir 存储路径
     * @return bool|Attachment
     */
    public function save($tempName, $uploadDir = null)
    {
        if ($tempName instanceof UploadedFile) {
            $extension = $tempName->extension;
            $size = $tempName->size;
            $originalName = $tempName->name;
            $tempName = $tempName->tempName;
        } elseif ((is_string($tempName) && is_file($tempName)) && file_exists($tempName)) {
            $extension = $this->getExtension($tempName);
            $size = filesize($tempName);
            $originalName = basename($tempName);
        } else {
            return false;
        }
        if (!is_null($uploadDir)) {
            $uploadDir = $uploadDir . DIRECTORY_SEPARATOR;
        }

        $savePath = $this->uploadRoot . DIRECTORY_SEPARATOR . $uploadDir . date('Y') . DIRECTORY_SEPARATOR . date('md') . DIRECTORY_SEPARATOR;
        if (!is_dir($savePath)) {//递归创建保存目录
            FileHelper::createDirectory($savePath, $this->dirMode, true);
        }

        $newFileName = $this->getFilename($extension);
        $saveFile = $savePath . $newFileName;
        $filePath = str_replace([$this->uploadRoot, DIRECTORY_SEPARATOR], ['', '/'], $saveFile);
        if (copy($tempName, $saveFile) && unlink($tempName)) {
            $mineType = FileHelper::getMimeType($saveFile);
            list($type) = explode('/', $mineType);
            $hash = hash_file('md5', $saveFile);
            $at = new Attachment();
            $at->filename = $newFileName;
            $at->original_name = $originalName;
            $at->path = $filePath;
            $at->size = $size;
            $at->ext = $extension;
            $at->type = $type;
            $at->mine_type = $mineType;
            $at->hash = $hash;
            $at->save();
            return $at;
        }
        return false;
    }
}