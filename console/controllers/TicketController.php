<?php

namespace console\controllers;

use common\models\Ticket;
use Yii;
use yii\base\Exception;
use yii\console\Controller;

class TicketController extends Controller
{
    /**
     * Attempts to close all tickets that are open, the last comment is admins, and user didn't comment in 2 weeks
     *
     * @return void
     */
    public function actionClose()
    {
        echo 'Attempting to close open tickets, where last comment is an admins and user didn\'t respond in 2 weeks.' . PHP_EOL;

        // PostgreSQL command
        $connection = Yii::$app->getDb();
        $command = $connection->createCommand(
            "
            SELECT DISTINCT ON (ticket_id) comment.ticket_id            /* selects only one comment per ticket */
            FROM comment
            LEFT JOIN ticket t ON comment.ticket_id = t.ticket_id       /* connect the ticket to comment */   
            LEFT JOIN \"user\" ON comment.created_by = \"user\".id      /* connect the user to the comment */
            WHERE t.status = true                                       /* select only the open tickets */
            AND comment.updated_at < now() - interval '14 days'         /* select only comment that are older than 2 weeks */
            AND \"user\".is_admin = true                                /* select only the comments that are posted by admins */
            ORDER BY comment.ticket_id, comment.updated_at DESC
        "
        );

        try {
            $result = $command->queryAll();
        } catch (\yii\db\Exception $e) {
            Yii::error($e, __METHOD__);
            echo 'Database query error' . PHP_EOL . $e->getMessage() . PHP_EOL;
            exit("Fatal error, exiting.");
        }

        // extracts the ticket id into an iterable array
        $idArray = [];
        foreach ($result as $item) {
            $idArray[] = $item["ticket_id"];
        }

        // returns all ticket models by the extracted idArray
        $tickets = Ticket::findAll(['ticket_id' => $idArray]);

        // loops through all queryed tickets and closes
        $counter = 0;
        foreach ($tickets as $ticket) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $ticket->setTicketStatus(false);
                $ticket->save();
                $transaction->commit();
                $counter++;
                echo $counter . ': Closing ticket: id:' . $ticket->ticket_id . ', title: ' . $ticket->title . '.' . PHP_EOL;
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::error($e, __METHOD__);
                echo 'Error at closing ticket: #' . $ticket->ticket_id . PHP_EOL . $e->getMessage() . PHP_EOL;
            }
        }

        if ($counter > 0) {
            echo 'Closed ' . $counter . ' tickets!' . PHP_EOL;
        } else {
            echo 'No open tickets found.' . PHP_EOL;
        }
    }
}
