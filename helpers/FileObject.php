<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\attachment\helpers;

use Yii;
use yii\base\Object;
use yii\helpers\FileHelper;
use yii\base\ArrayableTrait;
use yii\base\InvalidConfigException;

/**
 * Class FileObject
 *
 * You can call [[getInstance()]] to retrieve the instance of an uploaded file,
 * and then use [[saveAs()]] to save it on the server.
 *
 * @property string $baseName Original file base name. This property is read-only.
 * @property string $extension File extension. This property is read-only.
 * @package yuncms\attachment
 */
class FileObject extends Object
{
    use ArrayableTrait;

    /**
     * @var string the original name of the file being
     */
    public $name;

    /**
     * @var string the path of the file on the server.
     */
    public $tempName;

    /**
     * @var string the MIME-type of the file (such as "image/gif").
     * Since this MIME type is not checked on the server side, do not take this value for granted.
     * Instead, use [[\Leaps\Helper\FileHelper::getMimeType()]] to determine the exact MIME type.
     */
    public $mime;

    public $type;

    /**
     * @var integer the actual size of the uploaded file in bytes
     */
    public $size;

    /**
     * @var string md5 hash
     */
    public $hash;

    public static function getInstances($path, $name = null)
    {
        return new static(['tempName' => $path, 'name' => $name]);
    }

    /**
     * 初始化文件对象
     */
    public function init()
    {
        parent::init();
        if (empty ($this->tempName)) {
            throw new InvalidConfigException ('The "filePath" property must be set.');
        } elseif (!file_exists($this->tempName)) {
            throw new InvalidConfigException (Yii::t('attachment', 'File does not exist.'));
        }
        if (empty ($this->name)) {
            $this->name = pathinfo($this->tempName, PATHINFO_BASENAME);
        }
        if (empty ($this->hash)) {
            $this->hash = hash_file('md5', $this->tempName);
        }
        if (empty ($this->mime)) {
            $this->mime = FileHelper::getMimeType($this->tempName);
        }
        if (empty ($this->type)) {
            $this->type = stristr($this->mime, '/', true);
        }
        if (empty ($this->size)) {
            $this->size = filesize($this->tempName);
        }
    }

    /**
     * String output.
     * This is PHP magic method that returns string representation of an object.
     * The implementation here returns the uploaded file's name.
     * @return string the string representation of the object
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return string original file base name
     */
    public function getBaseName()
    {
        return mb_substr(pathinfo('_' . $this->name, PATHINFO_FILENAME), 1, null, '8bit');
    }

    /**
     * 生成新的文件名
     * @return string
     */
    public function getFullName()
    {
        return $this->hash . '.' . $this->getExtension();
    }

    /**
     * @return string file extension
     */
    public function getExtension()
    {
        return strtolower(pathinfo($this->name, PATHINFO_EXTENSION));
    }

    /**
     * Saves the file.
     * @param string $file the file path used to save the file
     * @param boolean $deleteTempFile whether to delete the temporary file after saving.
     * If true, you will not be able to save the file again in the current request.
     * @return boolean true whether the file is saved successfully
     * @see error
     */
    public function saveAs($file, $deleteTempFile = true)
    {
        if ($deleteTempFile) {
            if (copy($this->tempName, $file) && unlink($this->tempName)) {
                return true;
            }
            return false;
        } else {
            return copy($this->tempName, $file);
        }
    }
}
