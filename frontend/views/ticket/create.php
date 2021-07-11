<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Ticket */

$this->title = 'Create Ticket';
?>
<div class="ticket-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-md-6">
            <?= $this->render(
                '_form',
                [
                    'model' => $model,
                ]
            ) ?>
        </div>
    </div>


</div>
