<?php

namespace common\models\query;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\common\models\User]].
 *
 * @see \common\models\User
 */
class UserQuery extends ActiveQuery
{
    /**
     * Return all non admin users
     *
     * @return UserQuery
     */
    public function regularUsers()
    {
        return $this->andWhere(['is_admin' => false])->orderBy(
            ['last_login_time' => SORT_DESC, 'username' => SORT_ASC]
        );
    }
}
