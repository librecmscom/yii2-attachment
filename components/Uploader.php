<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\attachment\components;

use Yii;
use yii\base\Object;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use yii\validators\FileValidator;
use yii\httpclient\Client;
use yuncms\attachment\models\Attachment;

/**
 * Class Uploader
 * @package yuncms\attachment\components
 */
class Uploader extends Object
{

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
        if (!is_array($this->config['extensions'])) {
            $this->config['extensions'] = preg_split('/[\s,]+/', strtolower($this->config['extensions']), -1, PREG_SPLIT_NO_EMPTY);
        } else {
            $this->config['extensions'] = array_map('strtolower', $this->config['extensions']);
        }
    }

    /**
     * 上传文件的主处理方法
     * @return mixed
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
                FileHelper::createDirectory($dirName, $this->getModule()->dirMode, true);
            }
            if (!($file->saveAs($this->filePath) && file_exists($this->filePath))) {
                $this->stateInfo = Yii::t('attachment', 'An error occurred while saving the file.');
            } else { //移动成功
                $this->saveModel();
                $this->stateInfo = 'SUCCESS';
            }
        }
        return;
    }

    /**
     * 处理base64编码的图片上传
     * @return mixed
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
            FileHelper::createDirectory($dirName, $this->getModule()->dirMode, true);
        }
        //检查文件大小是否超出限制
        if (!$this->checkSize()) {
            $this->stateInfo = Yii::t('attachment', 'The file size exceeds the site limit.');
            return;
        }

        //移动文件
        if (!(file_put_contents($this->filePath, $img) && file_exists($this->filePath))) { //移动失败
            $this->stateInfo = Yii::t('attachment', 'An error occurred while saving the file.');
        } else { //移动成功
            $this->saveModel();
            $this->stateInfo = 'SUCCESS';
        }
        return;
    }

    /**
     * 拉取远程图片
     * @return mixed
     */
    public function saveRemote()
    {
        $imgUrl = htmlspecialchars($this->fileField);
        $imgUrl = str_replace("&amp;", "&", $imgUrl);

        //http开头验证
        if (strpos($imgUrl, "http") !== 0) {
            $this->stateInfo = Yii::t('attachment', 'The link is not an http link.');
            return;
        }
        $http = new Client();
        $response = $http->get($imgUrl)->send();
        if (!$response->isOk) {
            $this->stateInfo = Yii::t('attachment', 'The link is not available.');
            return;
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
                return;
            }
            if (!isset($response->headers['content-type']) || !stristr($response->headers['content-type'], "image")) {
                $this->stateInfo = Yii::t('attachment', 'The link contentType is incorrect.');
                return;
            }

            //检查文件大小是否超出限制
            if (!$this->checkSize()) {
                $this->stateInfo = Yii::t('attachment', 'The file size exceeds the site limit.');
                return;
            }

            if (!is_dir($dirName)) {//递归创建保存目录
                FileHelper::createDirectory($dirName, $this->getModule()->dirMode, true);
            }
            //检查文件大小是否超出限制
            if (!$this->checkSize()) {
                $this->stateInfo = Yii::t('attachment', 'The file size exceeds the site limit.');
                return;
            }
            //移动文件
            if (!(file_put_contents($this->filePath, $response->content) && file_exists($this->filePath))) { //移动失败
                $this->stateInfo = Yii::t('attachment', 'An error occurred while saving the file.');
            } else { //移动成功
                $this->saveModel();
                $this->stateInfo = 'SUCCESS';
            }
        }
        return;
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
        $fullName = $this->getModule()->uploads . '/' . str_replace('\\', '/', $this->fullName);
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
        $rootPath = $this->getModule()->uploadRoot;
        if (substr($fullName, 0, 1) != '/') {
            $fullName = '/' . $fullName;
        }
        return $rootPath . $fullName;
    }

    /**
     * 获取附件模块实例
     * @return \yuncms\attachment\Module
     */
    public function getModule()
    {
        return Yii::$app->getModule('attachment');
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