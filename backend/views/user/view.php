<?php

use yii\grid\GridView;
use yii\grid\SerialColumn;
use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $user common\models\User */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'User: ' . $user->username;
YiiAsset::register($this);
?>
<div class="user-view">

    <h1>User: <?= $user->username ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $user->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(
            Yii::t('app', 'Delete'),
            ['delete', 'id' => $user->id],
            [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                    'method' => 'post',
                ],
            ]
        ) ?>
    </p>

    <?= DetailView::widget(
        [
            'model' => $user,
            'attributes' => [
                'id',
                'username',
                'email:email',
                [
                    'attribute' => 'status',
                    'value' => $user->getStatusLabel(),
                ],
                'is_admin:boolean',
                [
                    'attribute' => 'created_at',
                    'value' => Yii::$app->formatter->asDatetime($user->created_at, 'YYYY-mm-dd hh:mm:ss'),
                ],
                'last_login_time',
                [
                    'attribute' => 'tickets',
                    'value' => $user->getAmountOfTicketsLabel(),
                ],
            ],
        ]
    ) ?>

    <hr class="mt-4">

    <div class="user-tickets">
        <h2><?= $user->username ?>'s tickets:</h2>

        <?php Pjax::begin(); ?>

        <?= GridView::widget(
            [
                'dataProvider' => $dataProvider,
                'columns' => [
                    ['class' => SerialColumn::class],

                    'title',
                    [
                        'attribute' => 'status',
                        'value' => 'statusLabel',
                    ],
                    'updated_at',
                    [
                        'attribute' => 'latest_comment',
                        'value' => 'latestCommentTimeLabel',
                    ],
                    [
                        'attribute' => 'assigned_admin_id',
                        'value' => 'adminLabel',
                    ],
                ],
            ]
        ) ?>

        <?php Pjax::end(); ?>
    </div>


</div>
