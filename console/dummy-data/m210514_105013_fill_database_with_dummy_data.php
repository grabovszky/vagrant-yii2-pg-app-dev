<?php

use common\models\Comment;
use common\models\Ticket;
use common\models\User;
use yii\db\Expression;
use yii\db\Migration;

/**
 * Class m210514_105013_fill_database_with_dummy_data
 */
class m210514_105013_fill_database_with_dummy_data extends Migration
{
    /**
     * {@inheritdoc}
     * @throws \yii\base\Exception
     */
    public function safeUp()
    {
        $baseEpoch = 1619852400; // 2021-05-03 9:00:00

        $this->batchInsert(
            'user',
            [
                'id',
                'username',
                'auth_key',
                'password_hash',
                'password_reset_token',
                'email',
                'status',
                'created_at',
                'updated_at',
                'verification_token',
                'is_admin',
                'last_login_time',
            ],
            [
                // simple user 1
                [
                    1,
                    'luke',
                    Yii::$app->security->generateRandomString(),
                    Yii::$app->security->generatePasswordHash('password'),
                    null,
                    'luke@jedi.com',
                    10,
                    $baseEpoch,
                    $baseEpoch,
                    Yii::$app->security->generateRandomString() . '_' . time(),
                    false,
                    $baseEpoch,
                ],
                // simple user 2
                [
                    2,
                    'leia',
                    Yii::$app->security->generateRandomString(),
                    Yii::$app->security->generatePasswordHash('password'),
                    null,
                    'leia@jedi.com',
                    10,
                    $baseEpoch,
                    $baseEpoch,
                    Yii::$app->security->generateRandomString() . '_' . time(),
                    false,
                    $baseEpoch,
                ],
                // simple user 3
                [
                    3,
                    'han',
                    Yii::$app->security->generateRandomString(),
                    Yii::$app->security->generatePasswordHash('password'),
                    null,
                    'han@jedi.com',
                    10,
                    $baseEpoch,
                    $baseEpoch,
                    Yii::$app->security->generateRandomString() . '_' . time(),
                    false,
                    $baseEpoch,
                ],
                // simple user 4
                [
                    4,
                    'chewbacca',
                    Yii::$app->security->generateRandomString(),
                    Yii::$app->security->generatePasswordHash('password'),
                    null,
                    'chewbacca@jedi.com',
                    10,
                    $baseEpoch,
                    $baseEpoch,
                    Yii::$app->security->generateRandomString() . '_' . time(),
                    false,
                    $baseEpoch,
                ],
                // simple user 5
                [
                    5,
                    'obi-wan',
                    Yii::$app->security->generateRandomString(),
                    Yii::$app->security->generatePasswordHash('password'),
                    null,
                    'obi-wan@jedi.com',
                    10,
                    $baseEpoch,
                    $baseEpoch,
                    Yii::$app->security->generateRandomString() . '_' . time(),
                    false,
                    $baseEpoch,
                ],
                // admin 1
                [
                    6,
                    'vader',
                    Yii::$app->security->generateRandomString(),
                    Yii::$app->security->generatePasswordHash('password'),
                    null,
                    'vader@sith.com',
                    10,
                    $baseEpoch,
                    $baseEpoch,
                    Yii::$app->security->generateRandomString() . '_' . time(),
                    true,
                    $baseEpoch,
                ],
                // admin 2
                [
                    7,
                    'palpatine',
                    Yii::$app->security->generateRandomString(),
                    Yii::$app->security->generatePasswordHash('password'),
                    null,
                    'palpatine@sith.com',
                    10,
                    $baseEpoch,
                    $baseEpoch,
                    Yii::$app->security->generateRandomString() . '_' . time(),
                    true,
                    $baseEpoch,
                ],
            ]
        );

        $this->batchInsert(
            'ticket',
            [
                'ticket_id',
                'title',
                'description',
                'status',
                'created_by',
                'created_at',
                'updated_at',
                'assigned_admin_id',
            ],
            [
                // User 1 tickets
                [
                    1,
                    'Stuck on sandy planet',
                    'If there\'s a bright center to the universe, you\'re on the planet that it\'s farthest from',
                    true,
                    1,
                    $baseEpoch,
                    $baseEpoch,
                    6,
                ],
                [
                    2,
                    'Accidentally kissed my sister',
                    'The Force is strong in my family. My father has it. I have it. My sister has it. You have that power, too.',
                    true,
                    1,
                    $baseEpoch,
                    $baseEpoch,
                    6,
                ],
                [
                    3,
                    'R2D2 weird noise',
                    'But I was going into Tosche Station to pick up some power converters!',
                    true,
                    1,
                    $baseEpoch,
                    $baseEpoch,
                    null,
                ],
                [
                    4,
                    'My father is evil',
                    'Your Thoughts Betray You, Father.',
                    false,
                    1,
                    $baseEpoch,
                    $baseEpoch,
                    7,
                ],
                // User 2 tickets
                [
                    5,
                    'Held captive on Deathstar',
                    'Aren\'t you a little short for a stormtrooper?',
                    true,
                    2,
                    $baseEpoch,
                    $baseEpoch,
                    7,
                ],
                [
                    6,
                    'Han is a jerk',
                    'Why, you stuck-up, half-witted, scruffy-looking nerf herder!',
                    false,
                    2,
                    $baseEpoch,
                    $baseEpoch,
                    7,
                ],
                [
                    7,
                    'My planet was destroyed',
                    'Help me Obi-Wan Kenobi, you\'re my only hope.',
                    false,
                    2,
                    $baseEpoch,
                    $baseEpoch,
                    7,
                ],
                // User 3 tickets
                [
                    8,
                    'Millenium Falcon warpdrive failure',
                    'You\'ve never heard of the Millennium Falcon? It\'s the ship that made the Kessel run in less than 12 parsecs.',
                    true,
                    3,
                    $baseEpoch,
                    $baseEpoch,
                    6,
                ],
                [
                    9,
                    'Leia ordering me around',
                    'Look, your worshipfulness, let\'s get one thing straight. I take orders from just one person: me.',
                    false,
                    3,
                    $baseEpoch,
                    $baseEpoch,
                    6,
                ],
                // User 4 tickets
                [
                    10,
                    'Rrrwggr',
                    'Wwwwwwwggggrrrh!',
                    true,
                    4,
                    $baseEpoch,
                    $baseEpoch,
                    null,
                ],
                [
                    11,
                    'Aaaarrgwhhhhh',
                    'Wwaarrrrgwwhhhaaarh!',
                    true,
                    4,
                    $baseEpoch,
                    $baseEpoch,
                    null,
                ],
                // User 5 tickets
                [
                    12,
                    'Hello there!',
                    'General Kenobi! You are a bold one… Kill him!',
                    true,
                    5,
                    $baseEpoch,
                    $baseEpoch,
                    6,
                ],
            ]
        );

        $this->batchInsert(
            'comment',
            [
                'id',
                'comment',
                'ticket_id',
                'created_at',
                'updated_at',
                'created_by',
            ],
            [
                // ticket 1 comments
                [
                    1,
                    'Soon I\'ll be dead, and you with me.',
                    1,
                    $baseEpoch,
                    $baseEpoch,
                    1,
                ],
                [
                    2,
                    'You don\'t know the power of the dark side! I must obey my master.',
                    1,
                    $baseEpoch,
                    $baseEpoch,
                    6,
                ],
                [
                    3,
                    'Be careful not to choke on your aspirations.',
                    1,
                    $baseEpoch,
                    $baseEpoch,
                    6,
                ],
                // ticket 2 comments
                [
                    4,
                    'You have controlled your fear. Now, release your anger. Only your hatred can destroy me.',
                    2,
                    $baseEpoch,
                    $baseEpoch,
                    6,
                ],
                [
                    5,
                    'Your overconfidence is your weakness.',
                    2,
                    $baseEpoch,
                    $baseEpoch,
                    1,
                ],
                // ticket 3 comments
                [
                    6,
                    'This little droid. I think he\'s searching for his former master. I\'ve never seen such devotion in a droid before. Ah, he claims to be the property of an Obi-Wan Kenobi. Is he a relative of yours? Do you know what he\'s talking about?',
                    3,
                    $baseEpoch,
                    $baseEpoch,
                    1,
                ],
                // ticket 4 comments
                [
                    7,
                    'I can\'t kill my own father.',
                    4,
                    $baseEpoch,
                    $baseEpoch,
                    1,
                ],
                [
                    8,
                    'Your feeble skills are no match for the power of the dark side.',
                    4,
                    $baseEpoch,
                    $baseEpoch,
                    7,
                ],
                [
                    9,
                    'Alderaan? I’m not going to Alderaan, I\'ve gotta get home, it\'s late, I\'m in for it as it is!”',
                    4,
                    $baseEpoch,
                    $baseEpoch,
                    1,
                ],
                [
                    10,
                    'So be it... Jedi.',
                    4,
                    $baseEpoch,
                    $baseEpoch,
                    7,
                ],
                // ticket 5 comments
                [
                    11,
                    'There is a great disturbance in the Force.',
                    5,
                    $baseEpoch,
                    $baseEpoch,
                    7,
                ],
                [
                    12,
                    'Would it help if I got out and pushed?',
                    5,
                    $baseEpoch,
                    $baseEpoch,
                    2,
                ],
                [
                    13,
                    'Young fool. Only now, at the end, do you understand.',
                    5,
                    $baseEpoch,
                    $baseEpoch,
                    7,
                ],
                // ticket 6 comments
                [
                    14,
                    'Oh, no, my young Jedi. You will find that it is you who are mistaken, about a great many things.',
                    6,
                    $baseEpoch,
                    $baseEpoch,
                    7,
                ],
                [
                    15,
                    'Long have I waited, and now your coming together is your undoing.',
                    6,
                    $baseEpoch,
                    $baseEpoch,
                    7,
                ],
                // ticket 7 comments
                [
                    16,
                    'We have powerful friends. You\'re going to regret this',
                    7,
                    $baseEpoch,
                    $baseEpoch,
                    2,
                ],
                [
                    17,
                    'This will be the final word in the story of Skywalker.',
                    7,
                    $baseEpoch,
                    $baseEpoch,
                    7,
                ],
                // ticket 8 comments
                [
                    18,
                    'Look, I ain\'t in this for your revolution, and I\'m not in it for you Princess. I expect to be well paid. I\'m in it for the money.',
                    8,
                    $baseEpoch,
                    $baseEpoch,
                    3,
                ],
                [
                    19,
                    'Just for once let me look on you with my own eyes. You were right. You were right about me. Tell your sister you were right.',
                    8,
                    $baseEpoch,
                    $baseEpoch,
                    6,
                ],
                [
                    20,
                    'This will be a day long remembered.',
                    8,
                    $baseEpoch,
                    $baseEpoch,
                    6,
                ],
                [
                    21,
                    'What good is a reward if you ain\'t around to use it? Besides, attacking that battle station is not my idea of courage. It\'s more like, suicide.',
                    8,
                    $baseEpoch,
                    $baseEpoch,
                    3,
                ],
                [
                    22,
                    'All I am surrounded by is fear, and dead men.',
                    8,
                    $baseEpoch,
                    $baseEpoch,
                    6,
                ],
                // ticket 9 comments
                [
                    23,
                    'Listen, big deal. You got another problem. Women always figure out the truth. Always.',
                    9,
                    $baseEpoch,
                    $baseEpoch,
                    3,
                ],
                // ticket 10 comments
                [
                    24,
                    'Arrrrgwwwrragrh!',
                    10,
                    $baseEpoch,
                    $baseEpoch,
                    4,
                ],
                // ticket 11 comments
                [
                    25,
                    'Grrrrawwwrh!',
                    11,
                    $baseEpoch,
                    $baseEpoch,
                    4,
                ],
                [
                    26,
                    'Argarrrrhhhhwwwwrrrgggg!',
                    11,
                    $baseEpoch,
                    $baseEpoch,
                    4,
                ],
                // ticket 12 comments
                [
                    27,
                    'For over a thousand generations, the Jedi Knights were the guardians of peace and justice in the Old Republic. Before the dark times, before the Empire.',
                    12,
                    $baseEpoch,
                    $baseEpoch,
                    5,
                ],
                [
                    28,
                    'I do not want the Emperor\'s prize damaged.',
                    12,
                    $baseEpoch,
                    $baseEpoch,
                    6,
                ],
                [
                    29,
                    'Obi-Wan never told you what happened to your father.',
                    12,
                    $baseEpoch,
                    $baseEpoch,
                    6,
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        Comment::deleteAll();
        Ticket::deleteAll();
        User::deleteAll();

        return 0;
    }
}
