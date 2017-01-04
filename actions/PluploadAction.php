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
use yii\helpers\FileHelper;


/**
 * Class PluploadAction
 * @package yuncms\attachment\actions
 */
class PluploadAction extends Action
{
    /**
     * @var string file input name.
     */
    public $inputName = 'file';

    /**
     * @var callable success callback with signature: `function($filename, $params)`
     */
    public $onComplete;

    /**
     * Initializes the action and ensures the temp path exists.
     */
    public function init()
    {
        parent::init();
        Yii::$app->response->format = Response::FORMAT_JSON;
        $this->tempPath = Yii::getAlias($this->tempPath);
        if (!is_dir($this->tempPath)) {
            FileHelper::createDirectory($this->tempPath, $this->dirMode, true);
        }
    }

    /**
     * Runs the action.
     * This method displays the view requested by the user.
     * @throws HttpException if the view is invalid
     */
    public function run()
    {
        $uploadedFile = UploadedFile::getInstanceByName($this->inputName);
        $filename = $this->getUnusedPath($this->tempPath . DIRECTORY_SEPARATOR . $uploadedFile->name);
        $isUploadComplete = ChunkUploader::process($uploadedFile, $filename);
        if ($isUploadComplete) {
            $file = FileObject::getInstances($filename);
            $validator = new FileValidator([
                'extensions' => $this->imageAllowFiles,
                'checkExtensionByMimeType' => false,
                //"maxSize" => $this->options['scrawlMaxSize'],
            ]);
            if ($validator->validate($file, $error)) {
                $file->type = FileModel::TYPE_IMAGE;
                return $this->saveFile($file);
            } else {
                return [
                    'state' => $error,
                ];
            }
        }
        return null;
    }

    /**
     * 保存文件
     * @param \yii\System\Service\FileObject $file
     * @return array
     */
    private function saveFile(FileObject $file)
    {
        $result = [];
        if (file_exists($file->tempName)) {
            //实例化File模型准备入库
            $fileModel = new FileModel($file->toArray());
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
            $result['state'] = Yii::t('system','File does not exist.');//文件不存在
        }
        return $result;
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
     * 获取用户临时存储目录
     *
     * @return string 该用户的临时存储路径
     * @throws \yii\base\Exception
     */
    public function getUserDirPath()
    {
        $session = Yii::$app->getSession();
        $session->open();
        $userDirPath = $this->tempPath . DIRECTORY_SEPARATOR . $session->getId();
        if (!is_dir($userDirPath)) {
            FileHelper::createDirectory($userDirPath, $this->dirMode, true);
        }
        return $userDirPath . DIRECTORY_SEPARATOR;
    }
}