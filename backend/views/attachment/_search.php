<?php

use yii\helpers\Html;
use xutl\inspinia\ActiveForm;

/* @var $this yii\web\View */
/* @var $model yuncms\attachment\backend\models\AttachmentSearch */
/* @var $form ActiveForm */
?>

<div class="attachment-search pull-right">

    <?php $form = ActiveForm::begin([
        'layout' => 'inline',
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id', [
        'inputOptions' => [
            'placeholder' => $model->getAttributeLabel('id'),
        ],
    ]) ?>

    <?= $form->field($model, 'user_id', [
        'inputOptions' => [
            'placeholder' => $model->getAttributeLabel('user_id'),
        ],
    ]) ?>

    <?= $form->field($model, 'original_name', [
        'inputOptions' => [
            'placeholder' => $model->getAttributeLabel('original_name'),
        ],
    ]) ?>

<!--    --><?//= $form->field($model, 'model', [
//        'inputOptions' => [
//            'placeholder' => $model->getAttributeLabel('id'),
//        ],
//    ]) ?>

    <?php // echo $form->field($model, 'hash') ?>

    <?php // echo $form->field($model, 'size') ?>

    <?php // echo $form->field($model, 'type') ?>

    <?php // echo $form->field($model, 'mine_type') ?>

    <?php // echo $form->field($model, 'ext') ?>

    <?php // echo $form->field($model, 'path') ?>

    <?php // echo $form->field($model, 'ip') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
