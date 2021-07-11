<?php

use yii\db\Migration;

/**
 * Class m210507_091808_add_is_admin_to_user
 */
class m210507_091808_add_is_admin_to_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'is_admin', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'is_admin');
    }
}
