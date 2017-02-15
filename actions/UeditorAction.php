<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\attachment\actions;

use Yii;
use yii\base\Action;
use yii\validators\FileValidator;
use yii\web\Response;
use yii\web\HttpException;
use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;
use yuncms\attachment\ModuleTrait;
use yuncms\attachment\models\Attachment;
use yuncms\attachment\helpers\FileObject;
use yuncms\attachment\components\Uploader;

/**
 * Class UEditorAction
 * @package yuncms\attachment\actions
 */
class UEditorAction extends Action
{
    use ModuleTrait;

    /**
     * @var array 客户端配置参数
     */
    public $options = [];
    /**
     * @var array 允许上传的图片文件后缀
     */
    public $imageAllowFiles;

    /**
     * @var array 允许上传的视频文件后缀
     */
    public $videoAllowFiles;

    /**
     * @var array 允许上传的普通文件后缀
     */
    public $fileAllowFiles;

    /**
     * @var string 图片上传最大大小
     */
    public $imageMaxSize = '2M';

    /**
     * @var string 视频上传最大大小
     */
    public $videoMaxSize = '100M';

    /**
     * @var string 文件上传最大大小
     */
    public $fileMaxSize = '100M';

    private $maxUploadSize;

    /**
     * Initializes the action and ensures the temp path exists.
     */
    public function init()
    {
        parent::init();
        //关闭CSRF
        $this->controller->enableCsrfValidation = false;
        //将系统默认的后缀限制,转换成ue专用的
        $this->imageAllowFiles = $this->normalizeExtension($this->getModule()->imageAllowFiles);
        $this->videoAllowFiles = $this->normalizeExtension($this->getModule()->videoAllowFiles);
        $this->fileAllowFiles = $this->normalizeExtension($this->getModule()->fileAllowFiles);
        //获取系统上传限制
        $this->maxUploadSize = $this->getModule()->getMaxUploadSize();

        $this->options = ArrayHelper::merge([
            "imageActionName" => "upload-image",
            "imageFieldName" => "upfile",
            /* 上传大小限制，单位B */
            "imageMaxSize" => $this->getMaxUploadByte($this->getModule()->imageMaxSize),
            /* 上传图片格式显示 */
            "imageAllowFiles" => $this->imageAllowFiles,
            "imageCompressEnable" => true,
            "imageCompressBorder" => 1600,
            "imageInsertAlign" => "none",
            "imageUrlPrefix" => "",
            /* 涂鸦图片上传配置项 */
            "scrawlActionName" => "upload-scrawl",
            "scrawlFieldName" => "upfile",
            /* 上传大小限制，单位B */
            "scrawlMaxSize" => $this->getMaxUploadByte($this->getModule()->imageMaxSize),
            /* 图片访问路径前缀 */
            "scrawlUrlPrefix" => "",
            "scrawlInsertAlign" => "none",
            /* 截图工具上传 */
            /* 执行上传截图的action名称 */
            "snapscreenActionName" => "upload-image",
            /* 上传保存路径,可以自定义保存路径和文件名格式 */
            "snapscreenUrlPrefix" => "",
            "snapscreenInsertAlign" => "none",
            /* 抓取远程图片配置 */
            "catcherLocalDomain" => ["127.0.0.1", "localhost"],
            "catcherActionName" => "catch-image",
            "catcherFieldName" => "source",
            "catcherUrlPrefix" => "",
            /* 上传大小限制，单位B */
            "catcherMaxSize" => $this->getMaxUploadByte($this->getModule()->imageMaxSize),
            /* 抓取图片格式显示 */
            "catcherAllowFiles" => $this->imageAllowFiles,

            /* 上传视频配置 */
            "videoActionName" => "upload-video",
            "videoFieldName" => "upfile",
            "videoUrlPrefix" => "",
            /* 视频访问路径前缀 */
            "videoMaxSize" => $this->getMaxUploadByte($this->getModule()->videoMaxSize),
            /* 上传大小限制，单位B，默认100MB */
            "videoAllowFiles" => $this->videoAllowFiles,

            /* 上传文件配置 */
            "fileActionName" => "upload-file",
            "fileFieldName" => "upfile",
            "fileUrlPrefix" => "",
            "fileMaxSize" => $this->getMaxUploadByte($this->getModule()->fileMaxSize),
            /* 上传大小限制，单位B，默认50MB */
            "fileAllowFiles" => $this->fileAllowFiles,
            /* 上传文件格式显示 */
            "imageManagerActionName" => "list-image",
            /* 执行图片管理的action名称 */
            "imageManagerListPath" => "",
            "imageManagerListSize" => 20,
            "imageManagerUrlPrefix" => "",
            "imageManagerInsertAlign" => "none",
            "imageManagerAllowFiles" => $this->imageAllowFiles,
            /* 列出的文件类型 */
            "fileManagerActionName" => "list-file",
            "fileManagerListPath" => "",
            "fileManagerUrlPrefix" => "",
            "fileManagerListSize" => 20,
            "fileManagerAllowFiles" => $this->fileAllowFiles
            /* 列出的文件类型 */
        ], $this->options);
    }

    /**
     * 执行该Action
     *
     * @param string $action 操作名称
     * @param string $callback 回调方法
     * @return string
     */
    public function run($action, $callback = null)
    {
        if ($action == 'config') {
            $result = $this->options;
        } else if (in_array($action, ['upload-file', 'upload-image'])) {
            $result = $this->upload($action);
        } else if (in_array($action, ['list-image', 'list-file'])) {
            $result = $this->lists($action);
        } else if ($action == 'catch-image') {
            $result = $this->uploadCrawler();
        } else if ($action == 'upload-scrawl') {//涂鸦上传
            $result = $this->uploadScrawl();
        } else {
            $result = ['state' => 'Request address error'];
        }
        if (is_null($callback)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $result;
        } else {
            Yii::$app->response->format = Response::FORMAT_JSONP;
            return ['callback' => $callback, 'data' => $result];
        }
    }

    /**
     * 上传
     * @param $action
     * @return array|string
     */
    protected function upload($action)
    {
        switch ($action) {
            case 'upload-image':
                $fieldName = $this->options['imageFieldName'];
                $fileType = Attachment::TYPE_IMAGE;
                $config = [
                    'maxFiles' => 1,
                    'extensions' => $this->getModule()->imageAllowFiles,
                    'checkExtensionByMimeType' => false,
                    "maxSize" => $this->options['imageMaxSize'],
                ];
                break;
            case 'upload-video':
                $fieldName = $this->options['videoFieldName'];
                $fileType = Attachment::TYPE_VIDEO;
                $config = [
                    'maxFiles' => 1,
                    'extensions' => $this->getModule()->videoAllowFiles,
                    'maxSize' => $this->options['videoMaxSize'],
                    'checkExtensionByMimeType' => false,
                ];
                break;
            default:
                $fieldName = $this->options['fileFieldName'];
                $fileType = Attachment::TYPE_FILE;
                $config = [
                    'maxFiles' => 1,
                    'extensions' => $this->getModule()->fileAllowFiles,
                    'maxSize' => $this->options['fileMaxSize'],
                    'checkExtensionByMimeType' => false,
                ];
                break;
        }
        $file = UploadedFile::getInstanceByName($fieldName);
        $validator = new FileValidator($config);
        if ($validator->validate($file, $error)) {
            $file = FileObject::getInstances($file->tempName, $file->name);
            $file->type = $fileType;
            return $this->saveFile($file);
        } else {
            $result = [
                'state' => $error,
            ];
        }
        return $result;
    }

    /**
     * 涂鸦上传
     * @return array|string
     */
    protected function uploadScrawl()
    {
        $base64Data = Yii::$app->request->post($this->options['scrawlFieldName']);
        $img = base64_decode($base64Data);
        $size = strlen($img);
        $tempName = $this->getModule()->tempPath . DIRECTORY_SEPARATOR . $size . '.png';
        if (file_put_contents($tempName, $img)) {
            $file = FileObject::getInstances($tempName, 'scrawl.png');
            $validator = new FileValidator([
                'extensions' => $this->getModule()->imageAllowFiles,
                'checkExtensionByMimeType' => false,
                "maxSize" => $this->options['scrawlMaxSize'],
            ]);
            if ($validator->validate($file, $error)) {
                $file->type = Attachment::TYPE_IMAGE;
                return $this->saveFile($file);
            } else {
                return [
                    'state' => $error,
                ];
            }
        }
        return [
            'state' => Yii::t('attachment', 'File write failed.'),
        ];
    }

    /**
     * 远程图片本地化
     */
    protected function uploadCrawler()
    {
        $sources = Yii::$app->request->post($this->options['catcherFieldName']);
        if (is_array($sources)) {
            $lists = [];
            foreach ($sources as $imgUrl) {
                array_push($lists, $this->getRemoteImage($imgUrl));
            }
            return $lists;
        } else {
            return [
                'state' => Yii::t('attachment', 'File write failed.'),
            ];
        }
    }

    /**
     * 获取已上传的文件列表
     * @param $action
     * @return array
     */
    protected function lists($action)
    {
        //查询实例
        $query = Attachment::find()->where(['user_id' => Yii::$app->user->id])->orderBy(['id' => SORT_DESC]);
        /* 判断类型 */
        switch ($action) {
            /* 列出文件 */
            case 'listfile':
                $query->andWhere(['ext' => $this->getModule()->fileAllowFiles]);
                break;
            /* 列出图片 */
            case 'listimage':
            default:
                $query->andWhere(['ext' => $this->getModule()->imageAllowFiles]);
        }
        $offset = Yii::$app->request->get('start', 0);
        $limit = Yii::$app->request->get('size', $this->options['imageManagerListSize']);
        $total = $query->count();
        if ($total > 0) {
            $files = $query->limit($limit)->offset($offset)->asArray()->all();
            $lists = [];
            foreach ($files as $file) {
                array_push($lists, [
                    'original' => $file['name'],
                    'url' => $this->getModule()->uploads . $file['path'],
                    'mtime' => $file['created_at']
                ]);
            }
            return ["state" => "SUCCESS", "list" => $lists, "start" => 0, "total" => $total];
        } else {
            return ["state" => "no match file", "list" => [], "start" => $offset, "total" => $total];
        }
    }

    /**
     * 格式化后缀
     *
     * @param string $extensions 后缀数组
     * @return mixed
     */
    private function normalizeExtension($extensions)
    {
        $extensions = explode(',', $extensions);
        array_walk($extensions, function (&$value) {
            $value = '.' . $value;
        });
        return $extensions;
    }

    /**
     * 获取远程图片
     * @param $imgUrl
     * @return array|string
     */
    private function getRemoteImage($imgUrl)
    {
        $imgUrl = str_replace("&amp;", "&", $imgUrl);
        //http开头验证
        if (strpos($imgUrl, "http") !== 0) {
            return [
                'state' => Yii::t('attachment', 'Link is not a HTTP link.'),
            ];
        }
        //获取请求头并检测死链
        $heads = get_headers($imgUrl, 1);
        if (!(stristr($heads[0], "200") && stristr($heads[0], "OK"))) {
            return [
                'state' => Yii::t('attachment', 'Link unavailable.'),
            ];
        }

        //格式验证(扩展名验证和Content-Type验证)
        $fileType = strtolower(pathinfo($imgUrl, PATHINFO_EXTENSION));
        if (!in_array($fileType, $this->getModule()->imageAllowFiles) || !stristr($heads['Content-Type'], "image")) {
            return [
                'state' => Yii::t('attachment', 'Link contentType not correct.'),
            ];
        }

        //打开输出缓冲区并获取远程图片
        ob_start();
        $context = stream_context_create(
            ['http' => [
                'follow_location' => false // don't follow redirects
            ]]
        );
        readfile($imgUrl, false, $context);
        $img = ob_get_contents();
        ob_end_clean();

        $size = strlen($img);
        $tempName = $this->getModule()->tempPath . DIRECTORY_SEPARATOR . $size . '.png';
        if (file_put_contents($tempName, $img)) {
            $file = FileObject::getInstances($tempName, 'scrawl.png');
            $validator = new FileValidator([
                'extensions' => $this->getModule()->imageAllowFiles,
                'checkExtensionByMimeType' => false,
                "maxSize" => $this->options['catcherMaxSize'],
            ]);
            $error = '';
            if ($validator->validate($file, $error)) {
                $file->type = Attachment::TYPE_IMAGE;
                return $this->saveFile($file);
            } else {
                return [
                    'state' => $error,
                ];
            }
        }
        return [
            'state' => Yii::t('attachment', 'File write failed.'),
        ];
    }

    /**
     * 保存文件
     * @param FileObject $file
     * @return array
     */
    private function saveFile(FileObject $file)
    {
        $result = [];
        if (file_exists($file->tempName)) {
            //实例化File模型准备入库
            $fileModel = new Attachment($file->toArray());
            $fileModel->ext = $file->getExtension();
            if ($fileModel->save()) {
                $result['state'] = 'SUCCESS';//上传状态，上传成功时必须返回"SUCCESS"
                $result['url'] = $this->controller->module->getStorageUrl(str_replace('\\', '/', $fileModel->path));//返回的地址
                $result['title'] = basename($fileModel->path);//新文件名
                $result['original'] = $file->name;//原始文件名
                $result['type'] = $file->type;//文件类型
                $result['size'] = $file->size;//文件大小
            } else {
                $result['state'] = array_shift(array_shift($fileModel->getErrors()));
            }
        } else {
            $result['state'] = Yii::t('attachment','File does not exist.');//文件不存在
        }
        return $result;
    }

    /**
     * 返回允许上传的最大大小单位 Byte
     * @param string $maxSize 最大上传大小MB
     * @return int the max upload size in Byte
     */
    public function getMaxUploadByte($maxSize)
    {
        $maxSize = (int)$maxSize;
        return min($this->maxUploadSize,$maxSize) * 1024 * 1024;
    }
}