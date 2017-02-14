<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\attachment\controllers;

use Yii;

/**
 * Class TestController
 * @package yuncms\attachment\controllers
 */
class TestController extends \yii\web\Controller
{
    public function actionIndex(){
        $path = Yii::getAlias('@root/LICENSE.md');
        $a =  $this->module->save($path);
        print_r($a);
    }
}