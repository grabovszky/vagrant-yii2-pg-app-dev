<?php

/* @var $this yii\web\View */

$this->title = 'My Yii Application';
?>
<div class="site-index">
    <div class="jumbotron">

        <!-- If user is guest or not logged in show guest screen -->
        <?php if (Yii::$app->user->isGuest): ?>
            <h1>Well hello there!</h1>

            <p class="lead">Please log in or sign up to continue!</p>

            <p>
                <a class="btn btn-lg btn-success" href="/site/signup"><i class="fas fa-user-plus"></i> Sign Up</a>
                <a class="btn btn-lg btn-primary" href="/site/login"><i class="fas fa-sign-in-alt"></i> Log In</a>
            </p>

            <!-- If user is logged in show welcome screen -->
        <?php else: ?>
            <h1>Well hello there <?php echo Yii::$app->user->identity->username ?>!</h1>

            <p class="lead">Welcome to the ticket system!</p>

            <p>
                <a class="btn btn-lg btn-success" href="/ticket/index"><i class="fas fa-ticket-alt"></i> My Tickets</a>
            </p>
        <?php endif ?>

    </div>
</div>
