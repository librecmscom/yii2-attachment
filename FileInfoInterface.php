<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\attachment;

interface FileInfoInterface
{
    public function getPathname();
    public function getName();
    public function setName($name);
    public function getExtension();
    public function setExtension($extension);
    public function getNameWithExtension();
    public function getMimetype();
    public function getSize();
    public function getMd5();
    public function getDimensions();
    public function isUploadedFile();
}