<?php

namespace common\models\query;

use common\models\Comment;
use common\models\User;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\common\models\Comment]].
 *
 * @see \common\models\Comment
 */
class CommentQuery extends ActiveQuery
{
    /**
     * Returns the ticket id associated with the comment
     *
     * @param $ticketId
     *
     * @return CommentQuery
     */
    public function ticketId($ticketId)
    {
        return $this->andWhere(['ticket_id' => $ticketId]);
    }

    /**
     * Return with the comment array ordered by newest to latest
     *
     * @return CommentQuery
     */
    public function latest()
    {
        return $this->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * Returns with the latest comment
     *
     * @return array|Comment|null
     */
    public function latestComment()
    {
        return $this->latest()->one();
    }

    /**
     * Return with the latest user comment
     *
     * @return Comment|mixed|null
     */
    public
    function latestUserComment()
    {
        $comments = $this->latest()->all();

        foreach ($comments as $comment) {
            if (User::isAdmin($comment->created_by)) {
                return $comment;
            }
        }

        return null;
    }
}
