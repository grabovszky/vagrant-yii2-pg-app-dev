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
    // if user is not logged in or guest show login and signup buttons
    $menuItems[] = ['label' => '<i class="fas fa-user-plus"></i>' . ' Signup', 'url' => ['/site/signup']];
    $menuItems[] = ['label' => '<i class="fas fa-sign-in-alt"></i>' . ' Login', 'url' => ['/site/login']];
} else {
    // if user is logged in show tickets, new ticket creation and profile dropdown
    $menuItems[] = ['label' => '<i class="fas fa-ticket-alt"></i>' . ' My Tickets', 'url' => ['/ticket/index']];
    $menuItems[] = ['label' => '<i class="fas fa-plus"></i>' . ' Add New Ticket', 'url' => ['/ticket/create']];
    // profile dropdown with profile page link and logout
    $menuItems[] = [
        'label' => '<i class="fas fa-user"></i>' . ' (' . Yii::$app->user->identity->username . ') ',
        'items' => [
            ['label' => '<i class="fas fa-address-card"></i>' . ' Profile', 'url' => ['/site/profile']],
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
