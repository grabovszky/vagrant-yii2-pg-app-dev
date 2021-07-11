<?php

/** @var User $user */

/** @var View $this */

use common\models\User;
use yii\bootstrap4\ActiveForm;
use yii\web\View;
use yii\widgets\Pjax;

?>

<?php Pjax::begin(
    [
        'enablePushState' => false,
    ]
) ?>

<!-- Alert user if update was successful -->
<?php if (isset($success) && $success): ?>
    <div class="alert alert-success">
        Your account was updated successfully!
    </div>
<?php endif ?>

<!-- User profile is an active form, and the user can edit name, email and password -->
<?php $form = ActiveForm::begin(
    [
        'action' => ['/site/update-account'],
        'options' => [
            'data-pjax' => 1,
        ],
    ]
); ?>

<?= $form->field($user, 'username')->textInput(['autofocus' => true]) ?>

<?= $form->field($user, 'email') ?>

<div class="row">
    <div class="col">
        <?= $form->field($user, 'password')->passwordInput() ?>
    </div>

    <div class="col">
        <?= $form->field($user, 'password_confirm')->passwordInput() ?>
    </div>
</div>

<div class="row">
    <div class="col">
        <button class="btn btn-primary">Update</button>
    </div>
</div>

<?php ActiveForm::end(); ?>
<?php Pjax::end() ?>
