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
     * 获取允许上传的图像 mimeTypes 列表
     * @return bool|mixed|string
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