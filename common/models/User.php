<?php

namespace common\models;

use common\models\query\UserQuery;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\StaleObjectException;
use yii\helpers\HtmlPurifier;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property int $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string|null $password_reset_token
 * @property string $email
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 * @property string|null $verification_token
 * @property bool|null $is_admin
 * @property string|null $last_login_time
 *
 * @property Comment[] $comments
 * @property Ticket[] $tickets
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;

    public $password;
    public $password_confirm;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'email'], 'required'],
            ['username', 'unique'],
            ['username', 'match', 'pattern' => '/^[A-Za-z0-9_]+$/u'],
            ['email', 'unique'],
            [['username', 'email'], 'string', 'max' => 255],
            ['status', 'default', 'value' => self::STATUS_INACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],
            ['is_admin', 'boolean'],
            ['is_admin', 'default', 'value' => false],
            ['password', 'string', 'min' => 8],
            ['password_confirm', 'compare', 'compareAttribute' => 'password'],
            ['last_login_time', 'safe'],
            ['last_login_time', 'default', 'value' => new Expression('NOW()')],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'username' => Yii::t('app', 'Username'),
            'auth_key' => Yii::t('app', 'Auth Key'),
            'password_hash' => Yii::t('app', 'Password Hash'),
            'password_reset_token' => Yii::t('app', 'Password Reset Token'),
            'email' => Yii::t('app', 'Email'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'verification_token' => Yii::t('app', 'Verification Token'),
            'is_admin' => Yii::t('app', 'Is Admin'),
            'last_login_time' => Yii::t('app', 'Last Login Time'),
        ];
    }

    /**
     * Return with the amount of tickets owned by the user
     *
     * @return null|int|string
     */
    public function getAmountOfTicketsLabel()
    {
        return Ticket::find()->creator($this->id)->count();
    }

    /**
     * Return the status in human readable form
     *
     * @return string
     */
    public function getStatusLabel()
    {
        return $this->status === 10 ? 'Active' : 'Inactive';
    }

    /**
     * {@inheritdoc}
     * @return UserQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserQuery(static::class);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     * @throws NotSupportedException
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     *
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     *
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne(
            [
                'password_reset_token' => $token,
                'status' => self::STATUS_ACTIVE,
            ]
        );
    }

    /**
     * Finds user by verification email token
     *
     * @param string $token verify email token
     *
     * @return static|null
     */
    public static function findByVerificationToken($token)
    {
        return static::findOne(
            [
                'verification_token' => $token,
                'status' => self::STATUS_INACTIVE,
            ]
        );
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     *
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * Checks if user is an admin
     *
     * @param $username
     *
     * @return bool if user is admin
     */
    public static function isUserAdmin($username)
    {
        return (bool)static::findOne(['username' => $username, 'is_admin' => true]);
    }

    /**
     * Checks if user is an admin by id
     *
     * @param $id
     *
     * @return bool
     */
    public static function isAdmin($id)
    {
        return (bool)static::findOne(['id' => $id, 'is_admin' => true]);
    }

    /**
     * Gets query for [[Comments]].
     *
     * @return ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::class, ['created_by' => 'id']);
    }

    /**
     * Gets query for [[Tickets]].
     *
     * @return ActiveQuery
     */
    public function getTickets()
    {
        return $this->hasMany(Ticket::class, ['created_by' => 'id']);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     *
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     *
     * @throws Exception
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     *
     * @throws Exception
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     *
     * @throws Exception
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Generates new token for email verification
     *
     * @throws Exception
     */
    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Overrides default save() method to update password.
     *
     * @param bool $runValidation
     * @param null $attributeNames
     *
     * @return bool
     * @throws Exception
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        $this->afterValidate();
        if ($this->password) {
            $this->password_hash = Yii::$app->security->generatePasswordHash($this->password);
        }

        return parent::save($runValidation, $attributeNames);
    }

    /**
     * Before deleting the user it deleted all ticket owned by the user.
     *
     * @return bool
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function beforeDelete()
    {
        foreach ($this->tickets as $ticket) {
            $ticket->delete();
        }

        return parent::beforeDelete();
    }

    /**
     * Prevents username to contain XSS
     *
     * @return bool
     */
    public function beforeValidate()
    {
        $purified = HtmlPurifier::process($this->username);

        if ($this->username !== $purified) {
            $this->username = $purified;
            Yii::$app->session->setFlash('error', 'Username can\'t contain HTML tags!');
            return false;
        }

        return parent::beforeValidate();
    }
}
