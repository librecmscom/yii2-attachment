<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\attachment\actions;

use Yii;
use yii\base\Action;
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
     * @var array 配置参数
     */
    public $options = [

    ];

    public $ueditorImageAllowFiles;
    public $ueditorVideoAllowFiles;
    public $ueditorFileAllowFiles;

    /**
     * Initializes the action and ensures the temp path exists.
     */
    public function init()
    {
        parent::init();
        $this->controller->enableCsrfValidation = false;
        //预处理
        $this->ueditorImageAllowFiles = $this->normalizeExtension(explode(',', $this->getModule()->imageAllowFiles));
        $this->ueditorVideoAllowFiles = $this->normalizeExtension(explode(',', $this->getModule()->videoAllowFiles));
        $this->ueditorFileAllowFiles = $this->normalizeExtension(explode(',', $this->getModule()->fileAllowFiles));//转数组
        $this->options = ArrayHelper::merge([
            /* 上传图片配置项 */
            /* 执行上传图片的action名称 */
            "imageActionName" => "uploadimage",
            /* 提交的图片表单名称 */
            "imageFieldName" => "upfile",
            /* 上传大小限制，单位B */
            "imageMaxSize" => $this->getModule()->getMaxUploadByte(),
            /* 上传图片格式显示 */
            "imageAllowFiles" => $this->ueditorImageAllowFiles,
            /* 是否压缩图片,默认是true */
            "imageCompressEnable" => true,
            /* 图片压缩最长边限制 */
            "imageCompressBorder" => 1600,
            /* 插入的图片浮动方式 */
            "imageInsertAlign" => "none",
            /* 图片访问路径前缀 */
            "imageUrlPrefix" => "",
            /* 上传保存路径,可以自定义保存路径和文件名格式 */
            /* {filename} 会替换成原文件名,配置这项需要注意中文乱码问题 */
            /* {rand:6} 会替换成随机数,后面的数字是随机数的位数 */
            /* {time} 会替换成时间戳 */
            /* {yyyy} 会替换成四位年份 */
            /* {yy} 会替换成两位年份 */
            /* {mm} 会替换成两位月份 */
            /* {dd} 会替换成两位日期 */
            /* {hh} 会替换成两位小时 */
            /* {ii} 会替换成两位分钟 */
            /* {ss} 会替换成两位秒 */
            /* 非法字符 \ => * ? " < > | */
            /* 具请体看线上文档=> fex.baidu.com/ueditor/#use-format_upload_filename */
            //"imagePathFormat" => "/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}",

            /* 涂鸦图片上传配置项 */
            /* 执行上传涂鸦的action名称 */
            "scrawlActionName" => "uploadscrawl",
            /* 提交的图片表单名称 */
            "scrawlFieldName" => "upfile",
            /* 上传保存路径,可以自定义保存路径和文件名格式 */
            // "scrawlPathFormat" => "/ueditor/php/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}",
            /* 上传大小限制，单位B */
            "scrawlMaxSize" => $this->getModule()->getMaxUploadByte(),
            /* 图片访问路径前缀 */
            "scrawlUrlPrefix" => "",

            "scrawlInsertAlign" => "none",
            /* 截图工具上传 */
            /* 执行上传截图的action名称 */
            "snapscreenActionName" => "uploadimage",
            /* 上传保存路径,可以自定义保存路径和文件名格式 */
            // "snapscreenPathFormat" => "/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}",
            /* 图片访问路径前缀 */
            "snapscreenUrlPrefix" => "",
            /* 插入的图片浮动方式 */
            "snapscreenInsertAlign" => "none",

            /* 抓取远程图片配置 */
            "catcherLocalDomain" => ["127.0.0.1", "localhost"],
            /* 执行抓取远程图片的action名称 */
            "catcherActionName" => "catchimage",
            /* 提交的图片列表表单名称 */
            "catcherFieldName" => "source",
            /* 上传保存路径,可以自定义保存路径和文件名格式 */
            //"catcherPathFormat" => "/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}",
            /* 图片访问路径前缀 */
            "catcherUrlPrefix" => "",
            /* 上传大小限制，单位B */
            "catcherMaxSize" => $this->getModule()->getMaxUploadByte(),
            /* 抓取图片格式显示 */
            "catcherAllowFiles" => $this->ueditorImageAllowFiles,

            /* 上传视频配置 */
            /* 执行上传视频的action名称 */
            "videoActionName" => "uploadvideo",
            /* 提交的视频表单名称 */
            "videoFieldName" => "upfile",
            /* 上传保存路径,可以自定义保存路径和文件名格式 */
            //"videoPathFormat" => "/upload/video/{yyyy}{mm}{dd}/{time}{rand:6}",

            "videoUrlPrefix" => "",
            /* 视频访问路径前缀 */
            "videoMaxSize" => $this->getModule()->getMaxUploadByte(),
            /* 上传大小限制，单位B，默认100MB */
            "videoAllowFiles" => $this->ueditorVideoAllowFiles,
            /* 上传视频格式显示 */
            /* 上传文件配置 */
            "fileActionName" => "uploadfile",
            /* controller里,执行上传视频的action名称 */
            "fileFieldName" => "upfile",
            /* 提交的文件表单名称 */
            //"filePathFormat" => "/upload/file/{yyyy}{mm}{dd}/{time}{rand:6}",
            /* 上传保存路径,可以自定义保存路径和文件名格式 */
            "fileUrlPrefix" => "",
            /* 文件访问路径前缀 */
            "fileMaxSize" => $this->getModule()->getMaxUploadByte(),
            /* 上传大小限制，单位B，默认50MB */
            "fileAllowFiles" => $this->ueditorFileAllowFiles,
            /* 上传文件格式显示 */
            /* 列出指定目录下的图片 */
            "imageManagerActionName" => "listimage",
            /* 执行图片管理的action名称 */
            "imageManagerListPath" => "/ueditor/php/upload/image/",
            /* 指定要列出图片的目录 */
            "imageManagerListSize" => 20,
            /* 每次列出文件数量 */
            "imageManagerUrlPrefix" => "",
            /* 图片访问路径前缀 */
            "imageManagerInsertAlign" => "none",
            /* 插入的图片浮动方式 */
            "imageManagerAllowFiles" => $this->ueditorImageAllowFiles,
            /* 列出的文件类型 */
            /* 列出指定目录下的文件 */
            "fileManagerActionName" => "listfile",
            /* 执行文件管理的action名称 */
            "fileManagerListPath" => "/ueditor/php/upload/file/",
            /* 指定要列出文件的目录 */
            "fileManagerUrlPrefix" => "",
            /* 文件访问路径前缀 */
            "fileManagerListSize" => 20,
            /* 每次列出文件数量 */
            "fileManagerAllowFiles" => $this->ueditorFileAllowFiles
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
        } else if (in_array($action, ['uploadfile', 'uploadimage'])) {
            $uploader = new Uploader();
            $uploader->upFile($this->options['imageFieldName']);
            exit;
            $result = $this->upload($action);
        } else if (in_array($action, ['listimage', 'listfile'])) {
            $result = $this->lists($action);
        } else if ($action == 'catchimage') {
            $result = $this->uploadCrawler();
        } else if ($action == 'uploadscrawl') {//涂鸦上传
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
            case 'uploadimage':
                $fieldName = $this->options['imageFieldName'];
                $fileType = Attachment::TYPE_IMAGE;
                $config = [
                    'maxFiles' => 1,
                    'extensions' => $this->getModule()->imageAllowFiles,
                    'checkExtensionByMimeType' => false,
                    "maxSize" => $this->options['imageMaxSize'],
                ];
                break;
            case 'uploadvideo':
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
        $validator = new UploadValidator($config);
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
     * @param array $extensions 后缀数组
     * @return mixed
     */
    private function normalizeExtension($extensions)
    {
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

}