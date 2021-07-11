<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%comment}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%ticket}}`
 * - `{{%user}}`
 */
class m210511_101811_create_comment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(
            '{{%comment}}',
            [
                'id' => $this->primaryKey(),
                'comment' => $this->text()->notNull(),
                'ticket_id' => $this->string(16)->notNull(),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
                'created_by' => $this->integer(8),
            ]
        );

        // creates index for column `ticket_id`
        $this->createIndex(
            '{{%idx-comment-ticket_id}}',
            '{{%comment}}',
            'ticket_id'
        );

        // add foreign key for table `{{%ticket}}`
        $this->addForeignKey(
            '{{%fk-comment-ticket_id}}',
            '{{%comment}}',
            'ticket_id',
            '{{%ticket}}',
            'ticket_id',
            'CASCADE'
        );

        // creates index for column `created_by`
        $this->createIndex(
            '{{%idx-comment-created_by}}',
            '{{%comment}}',
            'created_by'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-comment-created_by}}',
            '{{%comment}}',
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
        // drops foreign key for table `{{%ticket}}`
        $this->dropForeignKey(
            '{{%fk-comment-ticket_id}}',
            '{{%comment}}'
        );

        // drops index for column `ticket_id`
        $this->dropIndex(
            '{{%idx-comment-ticket_id}}',
            '{{%comment}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-comment-created_by}}',
            '{{%comment}}'
        );

        // drops index for column `created_by`
        $this->dropIndex(
            '{{%idx-comment-created_by}}',
            '{{%comment}}'
        );

        $this->dropTable('{{%comment}}');
    }
}
