<?php

use frontend\controllers\TicketController;
use yii\bootstrap4\Carousel;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Ticket */

$this->title = 'Update Ticket: ' . $model->title;
?>
<div class="ticket-update">

    <h1><?= Html::encode($this->title) ?></h1>


    <div class="row mt-3">
        <div class="col-md-6">
            <?= $this->render(
                '_form',
                [
                    'model' => $model,
                ]
            ) ?>
        </div>

        <div class="col-md-6">

            <?= Carousel::widget(
                [
                    'items' => TicketController::generateCarouselItems($model->ticket_id),
                    'options' => ['class' => 'carousel slide'],
                ]
            ) ?>
            
        </div>
    </div>

</div>
