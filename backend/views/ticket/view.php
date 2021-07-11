<?php

use backend\controllers\TicketController;
use yii\bootstrap4\Carousel;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Ticket */
/* @var $comments common\models\Comment[] */

$this->title = $model->title;
YiiAsset::register($this);
?>
<div class="ticket-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <!-- Ticket buttons -->
    <div class="mb-3">

        <!-- Delete button -->
        <?= Html::a(
            Yii::t('app', 'Delete'),
            ['delete', 'id' => $model->ticket_id],
            [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                    'method' => 'post',
                ],
            ]
        ) ?>

        <!-- Assign to current logged in admin button -->
        <?php if (!isset($model->assigned_admin_id)): ?>
            <form id="assign-form" class="d-inline" method="post" action="<?php echo Url::to(
                ['/ticket/assign', 'id' => $model->ticket_id, 'userId' => Yii::$app->user->id]
            ) ?>">
                <button id="assign-button" class="btn btn-success">Assign to Me</button>
            </form>
        <?php endif ?>

        <!-- Close ticket button -->
        <?php if ($model->status === true): ?>
            <form id="close-form" class="d-inline" method="post"
                  action="<?php echo Url::to(['/ticket/close', 'id' => $model->ticket_id]) ?>">
                <button id="close-button" class="btn btn-primary">Close Ticket</button>
            </form>
        <?php endif ?>
    </div>

    <div class="row">

        <!-- Ticket details -->
        <div class="col-md-6">
            <?= DetailView::widget(
                [
                    'model' => $model,
                    'attributes' => [
                        'ticket_id',
                        [
                            'attribute' => 'created_by',
                            'value' => $model->getCreatorLabel(),
                        ],
                        'title',
                        [
                            'attribute' => 'status',
                            // show as a human readable format
                            'value' => $model->getStatusLabel(),
                        ],
                        'description:ntext',
                        'created_at',
                        'updated_at',
                        [
                            'attribute' => 'assigned_admin_id',
                            'value' => $model->getAdminLabel(),
                        ],
                    ],
                ]
            ) ?>
        </div>

        <!-- Ticket images carousel -->
        <div class="col-md-6">
            <?= Carousel::widget(
                [
                    'items' => TicketController::generateCarouselItems($model->ticket_id),
                    'options' => ['class' => 'carousel slide'],
                ]
            )
            ?>
        </div>
    </div>


    <!-- Comment section -->
    <div class="comments mt-5">
        <hr>
        <!-- Count and display comment count -->
        <h4 class="mb-3"><span id="comment-count"><?php echo count($comments) ?></span> Comments</h4>

        <!-- New comment creation form -->
        <div class="create-comment mb-4">
            <div class="media">
                <img class="mr-3 comment-avatar" src="/img/avatar.svg" alt="Profile avatar">
                <div class="media-body">

                    <form id="create-comment-form" method="post" action="<?php echo Url::to(['/comment/create']) ?>">
                        <input type="hidden" name="ticket_id" value="<?php echo $model->ticket_id ?>" data-pjax="1">
                        <textarea id="leave-comment" class="form-control" rows="1" name="comment"
                                  placeholder="Add a comment..."></textarea>
                        <div class="text-right mt-2 action-buttons">
                            <button type="button" id="cancel-comment" class="btn btn-light">Cancel</button>
                            <button id="submit-comment" class="btn btn-primary">Submit</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <!-- Display all previous comments below -->
        <div id="comments-wrapper" class="comment-wrapper">
            <?php foreach ($comments as $comment) {
                echo $this->render(
                    '_comment_item',
                    ['model' => $comment,]
                );
            } ?>
        </div>

    </div>

</div>
