<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\attachment;

use Yii;

/**
 * Trait ModuleTrait
 * @property-read Module $module
 * @package yuncms\attachment
 */
trait AttachmentTrait
{
    /**
     * 获取附件模块配置
     * @param string $key
     * @param null $default
     * @return bool|mixed|string
     */
    public function getSetting($key, $default = null)
    {
        $value = Yii::$app->settings->get($key, 'attachment', $default);
        if ($key == 'storePath' || $key == 'storeUrl') {
            return Yii::getAlias($value);
        }
        return $value;
    }

    /**
     * 获取允许上传的最大图像大小
     * @return int
     */
    public function getImageMaxSizeByte()
    {
        $imageMaxSize = $this->getSetting('imageMaxSize');
        return $this->getMaxUploadByte($imageMaxSize);
    }

    /**
     * 获取允许上传的最大视频大小
     * @return int
     */
    public function getVideoMaxSizeByte()
    {
        $videoMaxSize = $this->getSetting('videoMaxSize');
        return $this->getMaxUploadByte($videoMaxSize);
    }

    /**
     * 获取允许上传的最大文件大小
     * @return int
     */
    public function getFileMaxSizeByte()
    {
        $fileMaxSize = $this->getSetting('fileMaxSize');
        return $this->getMaxUploadByte($fileMaxSize);
    }

    /**
     * 获取允许上传的图像 mimeTypes 列表
     * @return array ['image/jpg','image/png']
     */
    public function getAcceptImageMimeTypes()
    {
        $imageAllowFiles = $this->getSetting('imageAllowFiles');
        $extensions = explode(',', $imageAllowFiles);
        array_walk($extensions, function (&$value) {
            $value = 'image/' . $value;
        });
        return $extensions;
    }

    /**
     * 格式化后缀
     *
     * @param string $extensions 后缀数组 jpg,png,gif,bmp
     * @return mixed ['.jpg','.png']
     */
    public function normalizeExtension($extensions)
    {
        $extensions = explode(',', $extensions);
        array_walk($extensions, function (&$value) {
            $value = '.' . $value;
        });
        return $extensions;
    }

    /**
     * 获取一个暂未使用的路径用来存放临时文件
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
     * 返回允许上传的最大大小单位 Byte
     * @param string $maxSize 最大上传大小MB
     * @return int the max upload size in Byte
     */
    public function getMaxUploadByte($maxSize = null)
    {
        return $this->getMaxUploadSize($maxSize) * 1024 * 1024;
    }

    /**
     * 返回允许上传的最大大小单位 MB
     * @param string $maxSize 最大上传大小MB
     * @return int the max upload size in MB
     */
    public function getMaxUploadSize($maxSize = null)
    {
        $maxUpload = (int)(ini_get('upload_max_filesize'));
        $maxPost = (int)(ini_get('post_max_size'));
        $memoryLimit = (int)(ini_get('memory_limit'));
        $min = min($maxUpload, $maxPost, $memoryLimit);
        if ($maxSize) {
            $maxSize = (int)$maxSize;
            return min($maxSize, $min);
        }
        return $min;
    }
}