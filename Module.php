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
     * @var string the directory to store temporary files during conversion. You may use path alias here.
     * If not set, it will use the "plupload" subdirectory under the application runtime path.
     */
    public $tempPath = '@runtime/attach';

    /**
     * @var integer the permission to be set for newly created directories.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * Defaults to 0775, meaning the directory is read-writable by owner and group,
     * but read-only for other users.
     */
    public $dirMode = 0775;

    /**
     * @var string 图片上传最大大小
     */
    public $imageMaxSize = '2M';

    /**
     * @var array 允许上传的图片文件
     */
    public $imageAllowFiles = 'png,jpg,jpeg,gif,bmp';

    /**
     * @var string 视频上传最大大小
     */
    public $videoMaxSize = '100M';

    /**
     * @var array 允许的视频后缀
     */
    public $videoAllowFiles = 'flv,swf,mkv,avi,rm,rmvb,mpeg,mpg,ogg,ogv,mov,wmv,mp4,webm,mp3,wav,mid';

    /**
     * @var string 文件上传最大大小
     */
    public $fileMaxSize = '100M';

    /**
     * @var array 允许的文件后缀
     */
    public $fileAllowFiles = 'rar,zip,tar,gz,7z,bz2,cab,iso,doc,docx,xls,xlsx,ppt,pptx,pdf,txt,md,xml,xmind';

    /**
     * @var array 存储配置
     * @see https://github.com/creocoder/yii2-flysystem
     */
    public $disk = [];

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
        $this->tempPath = Yii::getAlias($this->tempPath);
        if (!is_dir($this->tempPath)) {
            FileHelper::createDirectory($this->tempPath, $this->dirMode, true);
        }
        $this->uploads = Yii::getAlias($this->uploads);
    }

    /**
     * 获取存储磁盘
     * @param string $name 磁盘名称
     * @return bool|object
     */
    public function disk($name)
    {
        if(isset($this->disk[$name])){
            return Yii::createObject($this->disk[$name]);
        } else {
            return false;
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
        return $this->uploads . $filePath;
    }
}