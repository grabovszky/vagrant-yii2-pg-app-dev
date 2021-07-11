<?php

/** @var $model Comment */

/** @var $ticket Ticket */

use common\models\Comment;
use common\models\Ticket;
use yii\helpers\Url;

?>

<!-- This is a single comment item -->
<div class="media comment-item mb-3">

    <!-- Show user avatar on the left -->
    <img class="mr-3 comment-avatar" src="/img/avatar.svg" alt="Profile avatar">


    <div class="media-body">

        <!-- User name and timestamp of comment creation -->
        <h6 class="mt-0">

            <a class="text-dark" href="">
                <?php echo $model->createdBy->username ?>
            </a>

            <small class="text-muted comment-timestamp">
                <?php echo $model->created_at ?>

                <!--Compare creation and update date, if it differs, it means it was updated -->
                <?php if ($model->created_at !== $model->updated_at): ?>
                    (edited)
                <?php endif; ?>
            </small>
        </h6>

        <!-- Comment primary text -->
        <div class="comment-text">
            <div class="text-wrapper">
                <?php echo $model->comment ?>
            </div>
        </div>

        <!-- Comment utility section on top right corner, only show if comment belongs to user -->
        <?php if ($model->belongsTo(Yii::$app->user->id)): ?>
            <div class="dropdown comment-actions dropleft">
                <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton"
                        data-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                    <a class="dropdown-item item-edit-comment text-primary" href="#">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a class="dropdown-item item-delete-comment text-danger"
                       href="<?php echo Url::to(['/comment/delete', 'id' => $model->id]) ?>">
                        <i class="fas fa-trash"></i> Delete
                    </a>

                </div>
            </div>
        <?php endif; ?>

        <!-- Hidden comment edit section, only show when edit button was pressed -->
        <form class="comment-edit-section" method="post"
              action="<?php echo Url::to(['/comment/update', 'id' => $model->id]) ?>">
            <textarea class="form-control" name="comment"
                      placeholder="Add a comment..."><?php echo $model->comment ?></textarea>
            <div class="text-right mt-2 action-buttons">
                <button type="button" class="btn btn-light button-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary button-save">Save</button>
            </div>
        </form>
    </div>
</div>
