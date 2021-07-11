<?php

use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;

NavBar::begin(
    [
        'brandLabel' => '<i class="fas fa-clipboard-list"></i>' . ' ' . Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'fixed-top navbar-expand-lg navbar-dark bg-dark shadow-sm',
        ],
    ]
);

$menuItems = [
    ['label' => '<i class="fas fa-home"></i>' . ' Home', 'url' => ['/site/index']],
];

if (Yii::$app->user->isGuest) {
    // if admin is not logged in show login button
    $menuItems[] = ['label' => '<i class="fas fa-sign-in-alt"></i>' . ' Login', 'url' => ['/site/login']];
} else {
    // if admin is logged in show tickets, and profile dropdown
    $menuItems[] = ['label' => '<i class="fas fa-users"></i>' . ' Users', 'url' => ['/user/index']];
    $menuItems[] = ['label' => '<i class="fas fa-ticket-alt"></i>' . ' Tickets', 'url' => ['/ticket/index']];
    $menuItems[] = [
        'label' => '<i class="fas fa-user"></i>' . ' (' . Yii::$app->user->identity->username . ') ',
        'items' => [
            [
                'label' => '<i class="fas fa-sign-out-alt"></i>' . ' Logout',
                'url' => ['/site/logout'],
                'linkOptions' => ['data-method' => 'post'],
            ],
        ],
        'options' => [
            'class' => 'navbar-nav',
        ],

    ];
}

echo Nav::widget(
    [
        'options' => ['class' => 'navbar-nav ml-auto'],
        'items' => $menuItems,
        'encodeLabels' => false,
    ]
);
NavBar::end();
