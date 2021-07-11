<?php

use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\grid\SerialColumn;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'My Tickets';
?>

<!-- User ticket screen -->
<div class="ticket-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(
            '<i class="fas fa-plus-square"></i>' . ' ' . 'New Ticket',
            ['create'],
            ['class' => 'btn btn-success']
        ) ?>
    </p>

    <?php Pjax::begin(); ?>

    <!-- List all tickets of user -->
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
                ['class' => SerialColumn::class],

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
