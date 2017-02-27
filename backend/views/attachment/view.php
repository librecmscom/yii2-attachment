<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yuncms\admin\widgets\Jarvis;

/* @var $this yii\web\View */
/* @var $model yuncms\attachment\models\Attachment */

$this->title = $model->filename;
$this->params['breadcrumbs'][] = ['label' => Yii::t('attachment', 'Manage Attachment'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<section id="widget-grid">
    <div class="row">
        <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12 attachment-view">
            <?php Jarvis::begin([
                'noPadding' => true,
                'editbutton' => false,
                'deletebutton' => false,
                'header' => Html::encode($this->title),
                'bodyToolbarActions' => [
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
                ]
            ]); ?>
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'user.username',
                    'filename',
                    'original_name',
                    'size',
                    'type',
                    'path',
                    'ip',
                    'created_at',
                ],
            ]) ?>
            <?php Jarvis::end(); ?>
        </article>
    </div>
</section>