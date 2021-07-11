<?php

namespace common\models;

use common\models\query\TicketQuery;
use Throwable;
use Yii;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\StaleObjectException;
use yii\helpers\FileHelper;
use yii\helpers\HtmlPurifier;
use yii\web\UploadedFile;

/**
 * This is the model class for table "{{%ticket}}".
 *
 * @property string $ticket_id
 * @property string $title
 * @property string|null $description
 * @property bool $status
 * @property int|null $created_by
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $assigned_admin_id
 *
 * @property Comment[] $comments
 * @property User $createdBy
 * @property User $assignedAdmin
 */
class Ticket extends ActiveRecord
{
    /**
     * @var UploadedFile[]
     */
    public $imageFiles;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%ticket}}';
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
            [['ticket_id', 'title'], 'required'],
            [['title'], 'string', 'max' => 255, 'min' => 3],
            [['description'], 'string'],
            [['status'], 'boolean'],
            [['created_by', 'assigned_admin_id'], 'default', 'value' => null],
            [['created_by', 'assigned_admin_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['ticket_id'], 'string', 'max' => 16],
            [['ticket_id'], 'unique'],
            [
                ['created_by'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['created_by' => 'id'],
            ],
            [
                ['assigned_admin_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['assigned_admin_id' => 'id'],
            ],
            [
                ['imageFiles'],
                'file',
                'skipOnEmpty' => true,
                'extensions' => 'png, jpg',
                'maxFiles' => 5,
            ],
            [['title', 'description'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ticket_id' => Yii::t('app', 'Ticket ID'),
            'title' => Yii::t('app', 'Title'),
            'description' => Yii::t('app', 'Description'),
            'status' => Yii::t('app', 'Status'),
            'created_by' => Yii::t('app', 'Created By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'assigned_admin_id' => Yii::t('app', 'Assigned Admin'),
        ];
    }

    /**
     * Returns the status as human readable format
     *
     * @return string
     */
    public function getStatusLabel()
    {
        return $this->status ? 'Open' : 'Closed';
    }

    /**
     * Return the ticket creators username
     *
     * @return string
     */
    public function getCreatorLabel()
    {
        return User::findIdentity($this->created_by)->username;
    }

    /**
     * Return the admin name from the admin id
     *
     * @return string
     */
    public function getAdminLabel()
    {
        if (isset($this->assigned_admin_id)) {
            return User::findIdentity($this->assigned_admin_id)->username;
        }

        return "Not assigned";
    }

    /**
     * Return the latest comments time associated with the ticket
     *
     * @return string
     */
    public function getLatestCommentTimeLabel()
    {
        $latestComment = $this->getComments()->latestComment();

        if (isset($latestComment)) {
            return $latestComment->created_at;
        }

        return "No comments";
    }

    /**
     * {@inheritdoc}
     * @return TicketQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TicketQuery(static::class);
    }

    /**
     * Gets query for [[Comments]].
     *
     * @return ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::class, ['ticket_id' => 'ticket_id']);
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
     * Gets query for [[AssignedAdmin]].
     *
     * @return ActiveQuery
     */
    public function getAssignedAdmin()
    {
        return $this->hasOne(User::class, ['id' => 'assigned_admin_id']);
    }

    /**
     * Changes the status of the ticket
     *
     * @param $status
     *
     * @return bool
     */
    public function setTicketStatus($status)
    {
        $this->status = $status;

        return true;
    }

    /**
     * Updates the updated at to now
     *
     * @return bool
     */
    public function updateTime()
    {
        $this->updated_at = new Expression('NOW()');

        return true;
    }

    /**
     * Overrides the default save method, automatically generates random string as ticket id
     *
     * @param bool $runValidation
     * @param null $attributeNames
     *
     * @return bool if ticket is saved or not
     * @throws Exception
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        $this->imageFiles = UploadedFile::getInstances($this, 'imageFiles');

        $isInsert = $this->isNewRecord;
        if ($isInsert) {
            $this->ticket_id = Yii::$app->security->generateRandomString(8);
        }

        $saved = parent::save($runValidation, $attributeNames);

        if ($saved) {
            foreach ($this->imageFiles as $file) {
                $filepath = Yii::getAlias(
                    '@frontend/web/storage/ticketImages/' . $this->ticket_id . '/' . $file->baseName . '.' . $file->extension
                );

                if (!is_dir(dirname($filepath))) {
                    FileHelper::createDirectory(dirname($filepath));
                }

                $file->saveAs($filepath);
            }
            return true;
        }

        return false;
    }

    /**
     * @throws ErrorException
     */
    public function deleteImgs()
    {
        $filepath = Yii::getAlias('@frontend/web/storage/ticketImages/' . $this->ticket_id);
        if (is_dir(dirname($filepath))) {
            FileHelper::removeDirectory(dirname($filepath));
        }
    }

    /**
     * Deletes all comments and images before deletion
     *
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function beforeDelete()
    {
        // delete all comments associated with the ticket before deletion
        foreach ($this->comments as $comment) {
            $comment->delete();
        }

        $this->deleteImgs();

        return parent::beforeDelete();
    }

    /**
     * Doesnt allow XSS in ticket forms
     *
     * @return bool
     */
    public function beforeValidate()
    {
        $purifiedTitle = HtmlPurifier::process($this->title);
        $purifiedDescription = HtmlPurifier::process(
            $this->description,
            [
                'Core.NormalizeNewlines' => false,
            ]
        );

        if ($this->title !== $purifiedTitle) {
            $this->title = $purifiedTitle;
            Yii::$app->session->setFlash('error', 'Title can\'t contain HTML tags!');
            return false;
        }

        if ($this->description !== $purifiedDescription) {
            $this->description = $purifiedDescription;
            Yii::$app->session->setFlash('error', 'Description can\'t contain HTML tags!');
            return false;
        }

        return parent::beforeValidate();
    }
}
