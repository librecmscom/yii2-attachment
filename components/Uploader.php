<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\attachment\components;

use Yii;
use yii\base\Component;
use yii\web\UploadedFile;
use yii\httpclient\Client;
use yii\helpers\FileHelper;
use yii\validators\FileValidator;
use yuncms\attachment\models\Attachment;
use yuncms\attachment\AttachmentTrait;

/**
 * Class Uploader
 * @package yuncms\attachment\components
 */
class Uploader extends Component
{
    use AttachmentTrait;

    /**
     * @var integer the permission to be set for newly created directories.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * Defaults to 0775, meaning the directory is read-writable by owner and group,
     * but read-only for other users.
     */
    public $dirMode = 0775;

    /**
     * @var string 文件上传字段
     */
    public $fileField;

    /**
     * @var array 配置数组
     */
    public $config = [];

    /**
     * @var string 原始文件名
     */
    private $oriName;

    /**
     * @var string 文件大小
     */
    private $fileSize;

    /**
     * @var string 新文件名
     */
    private $fileName;

    /**
     * @var string 相对路径的文件名
     */
    private $fullName;

    /**
     * @var string 绝对路径文件名
     */
    private $filePath;
    private $fileType; //文件类型
    private $stateInfo; //上传状态信息,

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->config = array_merge([
            'maxFiles' => 1,
            'maxSize' => $this->getMaxUploadByte(),
            'extensions' => $this->getSetting('fileAllowFiles'),
            'checkExtensionByMimeType' => false,
        ], $this->config);

        if (!is_array($this->config['extensions'])) {
            $this->config['extensions'] = preg_split('/[\s,]+/', strtolower($this->config['extensions']), -1, PREG_SPLIT_NO_EMPTY);
        } else {
            $this->config['extensions'] = array_map('strtolower', $this->config['extensions']);
        }
    }

    /**
     * 存储由UploadedFile上传的文件
     * @param UploadedFile $file
     * @return bool
     */
    public function up(UploadedFile $file)
    {
        //设置文件名称
        $this->oriName = $file->name;
        //设置文件大小
        $this->fileSize = $file->size;
        $this->fileType = $this->getExtension();
        $this->fullName = $this->getFullName();
        $this->filePath = $this->getFilePath();
        $this->fileName = $this->getFileName();
        $dirName = dirname($this->filePath);
        if (!is_dir($dirName)) {//递归创建保存目录
            FileHelper::createDirectory($dirName, $this->dirMode, true);
        }
        if (!($file->saveAs($this->filePath) && file_exists($this->filePath))) {
            $this->stateInfo = Yii::t('attachment', 'An error occurred while saving the file.');
            return false;
        } else { //移动成功
            $this->saveModel();
            $this->stateInfo = 'SUCCESS';
            return true;
        }
    }

    /**
     * 上传文件的主处理方法
     * @return bool
     */
    public function upFile()
    {
        $file = UploadedFile::getInstanceByName($this->fileField);
        $validator = new FileValidator($this->config);
        if ($validator->validate($file, $error)) {
            //设置文件名称
            $this->oriName = $file->name;
            //设置文件大小
            $this->fileSize = $file->size;
            $this->fileType = $this->getExtension();
            $this->fullName = $this->getFullName();
            $this->filePath = $this->getFilePath();
            $this->fileName = $this->getFileName();
            $dirName = dirname($this->filePath);
            if (!is_dir($dirName)) {//递归创建保存目录
                FileHelper::createDirectory($dirName, $this->dirMode, true);
            }
            if (!($file->saveAs($this->filePath) && file_exists($this->filePath))) {
                $this->stateInfo = Yii::t('attachment', 'An error occurred while saving the file.');
                return false;
            } else { //移动成功
                $this->saveModel();
                $this->stateInfo = 'SUCCESS';
                return true;
            }
        }
        return false;
    }

    /**
     * 处理base64编码的图片上传
     * @return bool
     */
    public function upBase64()
    {
        $base64Data = Yii::$app->request->post($this->fileField);
        $img = base64_decode($base64Data);
        $this->oriName = $this->config['oriName'];
        $this->fileSize = strlen($img);
        $this->fileType = $this->getExtension();
        $this->fullName = $this->getFullName();
        $this->filePath = $this->getFilePath();
        $this->fileName = $this->getFileName();
        $dirName = dirname($this->filePath);

        if (!is_dir($dirName)) {//递归创建保存目录
            FileHelper::createDirectory($dirName, $this->dirMode, true);
        }
        //检查文件大小是否超出限制
        if (!$this->checkSize()) {
            $this->stateInfo = Yii::t('attachment', 'The file size exceeds the site limit.');
            return false;
        }

        //移动文件
        if (!(file_put_contents($this->filePath, $img) && file_exists($this->filePath))) { //移动失败
            $this->stateInfo = Yii::t('attachment', 'An error occurred while saving the file.');
            return false;
        } else { //移动成功
            $this->saveModel();
            $this->stateInfo = 'SUCCESS';
            return true;
        }
    }

    /**
     * 拉取远程图片
     * @return bool
     */
    public function saveRemote()
    {
        $imgUrl = htmlspecialchars($this->fileField);
        $imgUrl = str_replace("&amp;", "&", $imgUrl);

        //http开头验证
        if (strpos($imgUrl, "http") !== 0) {
            $this->stateInfo = Yii::t('attachment', 'The link is not an http link.');
            return false;
        }
        $http = new Client();
        $response = $http->get($imgUrl)->send();
        if (!$response->isOk) {
            $this->stateInfo = Yii::t('attachment', 'The link is not available.');
            return false;
        } else {
            //格式验证(扩展名验证和Content-Type验证)
            $this->oriName = basename($imgUrl);
            $this->fileSize = strlen($response->content);
            $this->fileType = $this->getExtension();
            $this->fullName = $this->getFullName();
            $this->filePath = $this->getFilePath();
            $this->fileName = $this->getFileName();
            $dirName = dirname($this->filePath);

            //检查文件类型
            if (!$this->checkType()) {
                $this->stateInfo = Yii::t('attachment', 'The link contentType is incorrect.');
                return false;
            }
            if (!isset($response->headers['content-type']) || !stristr($response->headers['content-type'], "image")) {
                $this->stateInfo = Yii::t('attachment', 'The link contentType is incorrect.');
                return false;
            }

            //检查文件大小是否超出限制
            if (!$this->checkSize()) {
                $this->stateInfo = Yii::t('attachment', 'The file size exceeds the site limit.');
                return false;
            }

            if (!is_dir($dirName)) {//递归创建保存目录
                FileHelper::createDirectory($dirName, $this->dirMode, true);
            }
            //检查文件大小是否超出限制
            if (!$this->checkSize()) {
                $this->stateInfo = Yii::t('attachment', 'The file size exceeds the site limit.');
                return false;
            }
            //移动文件
            if (!(file_put_contents($this->filePath, $response->content) && file_exists($this->filePath))) { //移动失败
                $this->stateInfo = Yii::t('attachment', 'An error occurred while saving the file.');
                return false;
            } else { //移动成功
                $this->saveModel();
                $this->stateInfo = 'SUCCESS';
                return true;
            }
        }
    }

    /**
     * 保存本地其他地方上传的文件
     * @return bool
     */
    public function saveLocal()
    {
        $file = file_get_contents($this->fileField);
        $this->oriName = basename($this->fileField);
        $this->fileSize = strlen($file);
        $this->fileType = $this->getExtension();
        $this->fullName = $this->getFullName();
        $this->filePath = $this->getFilePath();
        $this->fileName = $this->getFileName();
        $dirName = dirname($this->filePath);

        if (!is_dir($dirName)) {//递归创建保存目录
            FileHelper::createDirectory($dirName, $this->dirMode, true);
        }
        //检查文件大小是否超出限制
        if (!$this->checkSize()) {
            $this->stateInfo = Yii::t('attachment', 'The file size exceeds the site limit.');
            return false;
        }

        //移动文件
        if (!(file_put_contents($this->filePath, $file) && file_exists($this->filePath))) { //移动失败
            $this->stateInfo = Yii::t('attachment', 'An error occurred while saving the file.');
            return false;
        } else { //移动成功
            @unlink($this->fileField);
            $this->saveModel();
            $this->stateInfo = 'SUCCESS';
            return true;
        }
    }

    /**
     * 文件大小检测
     * @return bool
     */
    private function checkSize()
    {
        return $this->fileSize <= ($this->config["maxSize"]);
    }

    /**
     * 获取当前上传成功文件的各项信息
     * @return array
     */
    public function getFileInfo()
    {
        $fullName = $this->getSetting('storeUrl') . '/' . str_replace('\\', '/', $this->fullName);
        return [
            "state" => $this->stateInfo,
            "url" => $fullName,
            "title" => $this->oriName,
            "original" => $this->oriName,
            "type" => $this->fileType,
            "size" => $this->fileSize
        ];
    }

    /**
     * 文件类型检测
     * @return bool
     */
    private function checkType()
    {
        return in_array($this->getExtension(), $this->config['extensions']);
    }

    /**
     * 获取文件扩展名
     * @return string
     */
    private function getExtension()
    {
        return strtolower(pathinfo($this->oriName, PATHINFO_EXTENSION));
    }

    /**
     * 重命名文件
     * @return string
     */
    private function getFullName()
    {
        $extension = $this->getExtension();
        return date('Y') . DIRECTORY_SEPARATOR . date('md') . DIRECTORY_SEPARATOR . date('Ymdhis') . rand(100, 999) . '.' . $extension;
    }

    /**
     * 获取文件名
     * @return string
     */
    private function getFileName()
    {
        return substr($this->filePath, strrpos($this->filePath, '/') + 1);
    }

    /**
     * 获取文件完整路径
     * @return string
     */
    private function getFilePath()
    {
        $fullName = $this->fullName;
        if (substr($fullName, 0, 1) != '/') {
            $fullName = '/' . $fullName;
        }
        return $this->getSetting('storePath') . $fullName;
    }


    /**
     * 保存到模型
     * @return bool
     */
    public function saveModel()
    {
        $fileName = basename($this->filePath);
        $fullName = str_replace('\\', '/', $this->fullName);
        $at = new Attachment([
            'filename' => $fileName,
            'original_name' => $this->oriName,
            'path' => $fullName,
            'size' => $this->fileSize,
            'type' => $this->fileType,
        ]);
        return $at->save();
    }
}