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
     * @var string the directory to store temporary files during conversion. You may use path alias here.
     * If not set, it will use the "plupload" subdirectory under the application runtime path.
     */
    public $tempPath = '@runtime/attach';

    /**
     * 初始化附件存储路径
     */
    public function init()
    {
        parent::init();
        $this->tempPath = Yii::getAlias($this->tempPath);
        if (!is_dir($this->tempPath)) {
            FileHelper::createDirectory($this->tempPath, 0775, true);
        }
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
        return min($maxUpload, $maxPost, $memoryLimit);
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
     * Returns an unused file path by adding a filename suffix if necessary.
     * @param string $path
     * @return string
     */
    protected function getUnusedPath($path)
    {
        $newPath = $path;
        $info = pathinfo($path);
        $suffix = 1;
        while (file_exists($newPath)) {
            $newPath = $info['dirname'] . DIRECTORY_SEPARATOR . "{$info['filename']}_{$suffix}";
            if (isset($info['extension'])) {
                $newPath .= ".{$info['extension']}";
            }
            $suffix++;
        }
        return $newPath;
    }

    /**
     * 获取附件访问Url
     * @param string $filePath 附件相对路径
     * @return string
     */
    public function getUrl($filePath)
    {
        return static::getStoreUrl() . $filePath;
    }

    /**
     * 获取附件访问路径
     * @param string $fileName 附件文件名
     * @return bool|string
     */
    public static function getStoreUrl($fileName = null)
    {
        $storeUrl = Yii::getAlias(Yii::$app->settings->get('storeUrl', 'attachment'));
        return $fileName ? $storeUrl . $fileName : $storeUrl;
    }

    /**
     * 获取存储跟路径
     * @return bool|string
     */
    public static function getStorePath()
    {
        $storePath = Yii::getAlias(Yii::$app->settings->get('storePath', 'attachment'));
        if (!is_dir($storePath)) {
            FileHelper::createDirectory($storePath, 0775, true);
        }
        return $storePath;
    }
}