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
    public $config;


    private $base64; //文件上传对象

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
    private $stateMap = [ //上传状态映射表，国际化用户需考虑此处数据的国际化
        "SUCCESS", //上传成功标记，在UEditor中内不可改变，否则flash判断会出错
        "文件大小超出 upload_max_filesize 限制",
        "文件大小超出 MAX_FILE_SIZE 限制",
        "文件未被完整上传",
        "没有文件被上传",
        "上传文件为空",
        "ERROR_CREATE_DIR" => "目录创建失败",
        "ERROR_DIR_NOT_WRITEABLE" => "目录没有写权限",
        "ERROR_FILE_MOVE" => "文件保存时出错",
        "ERROR_WRITE_CONTENT" => "写入文件内容错误",
        "ERROR_UNKNOWN" => "未知错误",
        "ERROR_DEAD_LINK" => "链接不可用",
        "ERROR_HTTP_LINK" => "链接不是http链接",
        "ERROR_HTTP_CONTENTTYPE" => "链接contentType不正确",
        "INVALID_URL" => "非法 URL",
        "INVALID_IP" => "非法 IP"
    ];

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
            $this->stateInfo = 'SUCCESS';
        }
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
            $this->stateInfo = $this->getStateInfo("ERROR_HTTP_LINK");
            return;
        }

        preg_match('/(^https*:\/\/[^:\/]+)/', $imgUrl, $matches);
        $host_with_protocol = count($matches) > 1 ? $matches[1] : '';

        // 判断是否是合法 url
        if (!filter_var($host_with_protocol, FILTER_VALIDATE_URL)) {
            $this->stateInfo = $this->getStateInfo("INVALID_URL");
            return;
        }

        preg_match('/^https*:\/\/(.+)/', $host_with_protocol, $matches);
        $host_without_protocol = count($matches) > 1 ? $matches[1] : '';

        // 此时提取出来的可能是 ip 也有可能是域名，先获取 ip
        $ip = gethostbyname($host_without_protocol);
        // 判断是否是私有 ip
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
            $this->stateInfo = $this->getStateInfo("INVALID_IP");
            return;
        }

        //获取请求头并检测死链
        $heads = get_headers($imgUrl, 1);
        if (!(stristr($heads[0], "200") && stristr($heads[0], "OK"))) {
            $this->stateInfo = $this->getStateInfo("ERROR_DEAD_LINK");
            return;
        }
        //格式验证(扩展名验证和Content-Type验证)
        $fileType = strtolower(strrchr($imgUrl, '.'));
        if (!in_array($fileType, $this->config['allowFiles']) || !isset($heads['Content-Type']) || !stristr($heads['Content-Type'], "image")) {
            $this->stateInfo = $this->getStateInfo("ERROR_HTTP_CONTENTTYPE");
            return;
        }

        //打开输出缓冲区并获取远程图片
        ob_start();
        $context = stream_context_create(
            array('http' => array(
                'follow_location' => false // don't follow redirects
            ))
        );
        readfile($imgUrl, false, $context);
        $img = ob_get_contents();
        ob_end_clean();
        preg_match("/[\/]([^\/]*)[\.]?[^\.\/]*$/", $imgUrl, $m);

        $this->oriName = $m ? $m[1] : "";
        $this->fileSize = strlen($img);
        $this->fileType = $this->getFileExt();
        $this->fullName = $this->getFullName();
        $this->filePath = $this->getFilePath();
        $this->fileName = $this->getFileName();
        $dirname = dirname($this->filePath);

        //检查文件大小是否超出限制
        if (!$this->checkSize()) {
            $this->stateInfo = Yii::t('attachment', 'The file size exceeds the site limit.');
            return;
        }

        //创建目录失败
        if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
            $this->stateInfo = $this->getStateInfo("ERROR_CREATE_DIR");
            return;
        } else if (!is_writeable($dirname)) {
            $this->stateInfo = $this->getStateInfo("ERROR_DIR_NOT_WRITEABLE");
            return;
        }

        //移动文件
        if (!(file_put_contents($this->filePath, $img) && file_exists($this->filePath))) { //移动失败
            $this->stateInfo = $this->getStateInfo("ERROR_WRITE_CONTENT");
        } else { //移动成功
            $this->stateInfo = $this->stateMap[0];
        }

    }

    /**
     * 上传错误检查
     * @param $errCode
     * @return string
     */
    private function getStateInfo($errCode)
    {
        return !$this->stateMap[$errCode] ? $this->stateMap["ERROR_UNKNOWN"] : $this->stateMap[$errCode];
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
            "title" => $this->fileName,
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
        return in_array($this->getExtension(), Yii::$app->getModule('attachment')->fileAllowFiles);
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
}