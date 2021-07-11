<?php

namespace common\models\query;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\common\models\Ticket]].
 *
 * @see \common\models\Ticket
 */
class TicketQuery extends ActiveQuery
{
    /**
     * Return all tickets by a given status
     *
     * @param $status
     *
     * @return TicketQuery
     */
    public function byStatus($status)
    {
        return $this->andWhere(['status' => $status]);
    }

    /**
     * Returns only the tickets which has been created by the user
     *
     * @param $userId
     *
     * @return TicketQuery
     */
    public function creator($userId)
    {
        // use andWhere instead of where to not mess up chained commands
        return $this->andWhere(['created_by' => $userId]);
    }

    /**
     * Implement an auto sort first by status then by update timestamp
     *
     * @return TicketQuery
     */
    public function sortByStatus()
    {
        return $this->orderBy(['status' => SORT_DESC, 'updated_at' => SORT_DESC]);
    }
}
