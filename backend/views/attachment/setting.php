<?php

use yii\helpers\Html;
use xutl\inspinia\Box;
use xutl\inspinia\Toolbar;
use xutl\inspinia\Alert;
use xutl\inspinia\ActiveForm;
use yuncms\attachment\models\Setting;

/* @var $this yii\web\View */
/* @var $model yuncms\user\backend\models\Settings */

$this->title = Yii::t('attachment', 'Settings');
$this->params['breadcrumbs'][] = Yii::t('attachment', 'Manage Attachment');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12 authentication-update">
            <?= Alert::widget() ?>
            <?php Box::begin([
                'header' => Html::encode($this->title),
            ]); ?>
            <div class="row">
                <div class="col-sm-4 m-b-xs">
                    <?= Toolbar::widget([
                        'items' => [
                            [
                                'label' => Yii::t('attachment', 'Manage Attachment'),
                                'url' => ['index'],
                            ],
                            [
                                'label' => Yii::t('attachment', 'Settings'),
                                'url' => ['setting'],
                            ],
                        ]
                    ]); ?>
                </div>
                <div class="col-sm-8 m-b-xs">

                </div>
            </div>

            <?php $form = ActiveForm::begin([
                'layout' => 'horizontal'
            ]); ?>

            <?= $form->field($model, 'storePath') ?>
            <?= $form->field($model, 'storeUrl') ?>
            <?= $form->field($model, 'imageMaxSize') ?>
            <?= $form->field($model, 'imageAllowFiles') ?>
            <?= $form->field($model, 'videoMaxSize') ?>
            <?= $form->field($model, 'videoAllowFiles') ?>
            <?= $form->field($model, 'fileMaxSize') ?>
            <?= $form->field($model, 'fileAllowFiles') ?>

            <?= Html::submitButton(Yii::t('attachment', 'Settings'), ['class' => 'btn btn-primary']) ?>

            <?php ActiveForm::end(); ?>
            <?php Box::end(); ?>
        </div>
    </div>
</div>