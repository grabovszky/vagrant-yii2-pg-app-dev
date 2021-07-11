<?php

use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Users');
?>
<div class="user-index">

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
                'id',
                'username',
                'email:email',
                'last_login_time',
                [
                    'attribute' => 'Number of tickets',
                    'value' => 'amountOfTicketsLabel',
                ],

                ['class' => ActionColumn::class],
            ],
        ]
    ) ?>

    <?php Pjax::end(); ?>

</div>
