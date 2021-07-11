<?php

use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Tickets');
?>
<div class="ticket-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php Pjax::begin(); ?>

    <?= GridView::widget(
        [
            'tableOptions' => [
                'class' => 'table table-striped',
            ],
            'options' => [
                'class' => 'table-responsive',
            ],
            'dataProvider' => $dataProvider,
            'columns' => [
                [
                    'attribute' => 'created_by',
                    'value' => 'creatorLabel',
                ],
                'title',
                [
                    'attribute' => 'status',
                    'value' => 'statusLabel',
                ],
                [
                    'attribute' => 'latest_comment',
                    'value' => 'latestCommentTimeLabel',
                ],
                [
                    'attribute' => 'assigned_admin_id',
                    'value' => 'adminLabel',
                ],

                ['class' => ActionColumn::class],
            ],
        ]
    ) ?>

    <?php Pjax::end(); ?>

</div>
