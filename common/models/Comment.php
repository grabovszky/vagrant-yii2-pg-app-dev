<?php

namespace common\models;

use common\models\query\CommentQuery;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\HtmlPurifier;

/**
 * This is the model class for table "{{%comment}}".
 *
 * @property int $id
 * @property string $comment
 * @property string $ticket_id
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $created_by
 *
 * @property Ticket $ticket
 * @property User $createdBy
 */
class Comment extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%comment}}';
    }

    /**
     * Allows fo the timestamp to be automatically created on create
     *
     * @return array[]
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::class,
                'updatedByAttribute' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['comment', 'ticket_id'], 'required'],
            [['comment'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['created_by'], 'default', 'value' => null],
            [['created_by'], 'integer'],
            [['ticket_id'], 'string', 'max' => 16],
            [
                ['ticket_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Ticket::class,
                'targetAttribute' => ['ticket_id' => 'ticket_id'],
            ],
            [
                ['created_by'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['created_by' => 'id'],
            ],
            [['comment'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'comment' => Yii::t('app', 'Comment'),
            'ticket_id' => Yii::t('app', 'Ticket'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'created_by' => Yii::t('app', 'Created By'),
        ];
    }

    /**
     * Gets query for [[Ticket]].
     *
     * @return ActiveQuery
     */
    public function getTicket()
    {
        return $this->hasOne(Ticket::class, ['ticket_id' => 'ticket_id']);
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Returns the comment creator id
     *
     * @param $id
     *
     * @return bool
     */
    public function belongsTo($id)
    {
        return $this->created_by === $id;
    }

    /**
     * {@inheritdoc}
     * @return CommentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CommentQuery(static::class);
    }

    /**
     * Prevents username to contain XSS
     *
     * @return bool
     */
    public function beforeValidate()
    {
        $purified = HtmlPurifier::process(
            $this->comment,
            [
                'Core.NormalizeNewlines' => false,
            ]
        );

        if ($this->comment !== $purified) {
            $this->comment = $purified;
            Yii::$app->session->setFlash('error', 'Comment can\'t contain HTML tags!');
            Yii::$app->controller->redirect(['/ticket/view', 'id' => $this->ticket_id]);
            return false;
        }

        return parent::beforeValidate();
    }
}
