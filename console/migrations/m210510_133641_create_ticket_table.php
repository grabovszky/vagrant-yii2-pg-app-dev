<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ticket}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m210510_133641_create_ticket_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(
            '{{%ticket}}',
            [
                'ticket_id' => $this->string(16)->notNull(),
                'title' => $this->string(255)->notNull(),
                'description' => $this->text(),
                'status' => $this->boolean()->notNull()->defaultValue(true),
                'created_by' => $this->integer(11),
                'created_at' => $this->dateTime()->defaultValue('NOW()'),
                'updated_at' => $this->dateTime(),
            ]
        );

        $this->addPrimaryKey('PK_ticket_ticket_id', '{{%ticket}}', 'ticket_id');

        // creates index for column `created_by`
        $this->createIndex(
            '{{%idx-ticket-created_by}}',
            '{{%ticket}}',
            'created_by'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-ticket-created_by}}',
            '{{%ticket}}',
            'created_by',
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
            '{{%fk-ticket-created_by}}',
            '{{%ticket}}'
        );

        // drops index for column `created_by`
        $this->dropIndex(
            '{{%idx-ticket-created_by}}',
            '{{%ticket}}'
        );

        $this->dropTable('{{%ticket}}');
    }
}
