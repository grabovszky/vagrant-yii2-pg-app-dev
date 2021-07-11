<?php

/* @var $this yii\web\View */

/** @var  User $user */

use common\models\User;

$this->title = 'Profile';
?>
<div class="site-profile">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Last login: <?php echo Yii::$app->user->identity->last_login_time ?></div>
                <div class="card-body">
                    <?php $success = false;
                    echo $this->render(
                        'user_account',
                        [
                            'user' => $user,
                            'success' => $success,
                        ]
                    ) ?>
                </div>

            </div>
        </div>
    </div>
