<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use xutl\inspinia\Box;
use xutl\inspinia\Toolbar;
use xutl\inspinia\Alert;

/* @var $this yii\web\View */
/* @var $model yuncms\attachment\models\Attachment */

$this->title = $model->filename;
$this->params['breadcrumbs'][] = ['label' => Yii::t('attachment', 'Manage Attachment'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12 attachment-view">
            <?= Alert::widget() ?>
            <?php Box::begin([
                'header' => Html::encode($this->title),
            ]); ?>
            <div class="row">
                <div class="col-sm-4 m-b-xs">
                    <?= Toolbar::widget(['items' => [
                        [
                            'label' => Yii::t('attachment', 'Manage Attachment'),
                            'url' => ['index'],
                        ],
                        [
                            'label' => Yii::t('attachment', 'Delete Attachment'),
                            'url' => ['delete', 'id' => $model->id],
                            'options' => [
                                'class' => 'btn btn-danger btn-sm',
                                'data' => [
                                    'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                                    'method' => 'post',
                                ],
                            ]
                        ],
                    ]]); ?>
                </div>
                <div class="col-sm-8 m-b-xs">

                </div>
            </div>
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'user.nickname',
                    'filename',
                    'original_name',
                    'size',
                    'type',
                    'path',
                    'ip',
                    'created_at:datetime',
                ],
            ]) ?>
            <?php Box::end(); ?>
    </div>
    </div>
    </div>
