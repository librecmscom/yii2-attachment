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
    public $storagePath = '@storageRoot';

    public $storage = '@storage';

    /**
     * @var integer the permission to be set for newly created directories.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * Defaults to 0775, meaning the directory is read-writable by owner and group,
     * but read-only for other users.
     */
    public $dirMode = 0775;

    /**
     * @var string 附件存储格式
     */
    public $pathFormat = '{yyyy}/{mm}{dd}/{time}{rand:6}';

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
     * @param int $userId
     * @return string
     */
    public function getFilePath($userId)
    {
        $avatarPath = Yii::getAlias($this->avatarPath) . '/' . $this->getAvatarHome($userId);
        if (!is_dir($avatarPath)) {
            FileHelper::createDirectory($avatarPath);
        }
        return $avatarPath . substr($userId, -2);
    }

    /**
     * 获取附件访问Url
     * @param int $userId 用户ID
     * @return string
     */
    public function getFileUrl($path)
    {
        return Yii::getAlias($this->storage) . '/' . $this->getFileHome($oriName) . substr($userId, -2);
    }


    /**
     * 生成文件存储路径
     * @param string $fileName
     * @return mixed
     */
    public function getFileHome($fileName)
    {
        $time = time();
        $date = explode('-', date("Y-y-m-d-H-i-s"));
        $format = str_replace(
            ["{yyyy}", "{yy}", "{mm}", "{dd}", "{hh}", "{ii}", "{ss}", "{time}"],
            [$date[0], $date[1], $date[2], $date[3], $date[4], $date[5], $date[6], $time],
            $this->pathFormat
        );
        $oriName = substr($oriName, 0, strrpos($oriName, '.'));
        $oriName = preg_replace("/[\|\?\"\<\>\/\*\\\\]+/", '', $oriName);
        $format = str_replace("{filename}", $oriName, $format);

        $randNum = rand(1, 10000000000) . rand(1, 10000000000);
        if (preg_match("/\{rand\:([\d]*)\}/i", $format, $matches)) {
            $format = preg_replace("/\{rand\:[\d]*\}/i", substr($randNum, 0, $matches[1]), $format);
        }
        return $format;
    }
}