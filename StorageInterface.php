<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\attachment;

use yuncms\attachment\FileInfoInterface;

interface StorageInterface
{
    /**
     * Upload file
     *
     * This method is responsible for uploading an `\Upload\FileInfoInterface` instance
     * to its intended destination. If upload fails, an exception should be thrown.
     *
     * @param  FileInfoInterface $fileInfo
     * @throws Exception   If upload fails
     */
    public function upload(FileInfoInterface $fileInfo);
}