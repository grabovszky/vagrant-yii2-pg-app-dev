<?php

use yii\db\Migration;

/**
 * Class m210510_092246_add_last_login_time_to_user_table
 */
class m210510_092246_add_last_login_time_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'last_login_time', $this->dateTime());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'last_login_time');
    }
}
