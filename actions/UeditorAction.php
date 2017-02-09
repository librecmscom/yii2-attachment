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

/**
 * Class UEditorAction
 * @package yuncms\attachment\actions
 */
class UEditorAction extends Action
{
    /**
     * Initializes the action and ensures the temp path exists.
     */
    public function init()
    {
        parent::init();
    }

    /**
     * Runs the action.
     * This method displays the view requested by the user.
     * @throws HttpException if the view is invalid
     */
    public function run()
    {
        $action = '';
        if ($action == '') {
            
        }
    }

}