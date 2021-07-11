<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%ticket}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m210513_103558_add_assigned_admin_id_column_to_ticket_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%ticket}}',
            'assigned_admin_id',
            $this->integer(11)
        );

        // creates index for column `assigned_admin_id`
        $this->createIndex(
            '{{%idx-ticket-assigned_admin_id}}',
            '{{%ticket}}',
            'assigned_admin_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-ticket-assigned_admin_id}}',
            '{{%ticket}}',
            'assigned_admin_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-ticket-assigned_admin_id}}',
            '{{%ticket}}'
        );

        // drops index for column `assigned_admin_id`
        $this->dropIndex(
            '{{%idx-ticket-assigned_admin_id}}',
            '{{%ticket}}'
        );

        $this->dropColumn('{{%ticket}}', 'assigned_admin_id');
    }
}
