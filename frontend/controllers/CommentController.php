<?php

namespace frontend\controllers;

use common\models\Comment;
use Throwable;
use Yii;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class CommentController extends Controller
{

    /**
     * Allow access behaviors for the comment section
     *
     * @return array[]
     */
    public function behaviors()
    {
        return [
            // Only allow logged in users to comment
            'access' => [
                'class' => AccessControl::class,
                'only' => ['create'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'content' => [
                'class' => ContentNegotiator::class,
                'only' => ['create', 'update', 'delete'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            // Only allow POST requests
            'verb' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Handle comment creation
     * Saves new comment, and returns it as a view to be displayed
     *
     * @return Response|array
     */
    public function actionCreate()
    {
        $comment = new Comment();

        if ($comment->load(Yii::$app->request->post(), '') && $comment->save()) {
            TicketController::openStatus($comment->ticket_id);
            $comment = Comment::find()->with(['createdBy'])->ticketId($comment->ticket_id)->one();

            return $this->redirect(['/ticket/view', 'id' => $comment->ticket_id]);
        }

        return [
            'success' => false,
            'errors' => $comment->errors,
        ];
    }

    /**
     * Handle comment update
     * Saves updated comment, and returns it as a view to be displayed
     *
     * @param $id
     *
     * @return array|bool[]
     */
    public function actionUpdate($id)
    {
        $comment = $this->findModel($id);
        if ($comment->belongsTo(Yii::$app->user->id)) {
            $commentText = Yii::$app->request->post('comment');
            $comment->comment = $commentText;
            $transaction = Yii::$app->db->beginTransaction();

            if ($comment->save()) {
                $transaction->commit();
                return [
                    'success' => true,
                    // returns only a partial to render without applying a whole layout
                    'comment' => $this->renderPartial(
                        '@frontend/views/ticket/_comment_item',
                        [
                            'model' => $comment,
                        ]
                    ),
                ];
            }

            $transaction->rollBack();
            return [
                'success' => false,
                'errors' => $comment->errors,
            ];
        }

        Yii::error('Comment update is not allowed.');
        Yii::$app->session->setFlash('error', 'Comment update is not allowed.');
        return Response::$httpStatuses[403];
    }

    /**
     * Handle comment delete
     * Deletes comment
     *
     * @param $id
     *
     * @return bool[]
     */
    public function actionDelete($id)
    {
        $comment = $this->findModel($id);
        $transaction = Yii::$app->db->beginTransaction();

        if ($comment->belongsTo(Yii::$app->user->id)) {
            try {
                $comment->delete();
                $transaction->commit();
            } catch (StaleObjectException $e) {
                $transaction->rollBack();
                Yii::error($e, __METHOD__);
                Yii::$app->session->setFlash('error', 'Error while deleting comment #' . $id);
                return Response::$httpStatuses[400];
            } catch (Throwable $e) {
                $transaction->rollBack();
                Yii::error($e, __METHOD__);
                Yii::$app->session->setFlash('error', 'Error while deleting comment #' . $id);
                return Response::$httpStatuses[400];
            }

            return ['success' => true];
        }

        Yii::error('Comment deletion is not allowed.');
        Yii::$app->session->setFlash('error', 'Comment deletion is not allowed.');
        return Response::$httpStatuses[403];
    }

    /**
     * Searches for the model with id
     *
     * @param $id
     *
     * @return Comment
     */
    protected function findModel($id)
    {
        $comment = Comment::findOne($id);
        if (!$comment) {
            Yii::error('Error: comment #' . $id . ' not found.');
            Yii::$app->session->setFlash('error', 'Error: comment #' . $id . ' not found.');
            return Response::$httpStatuses[404];
        }

        return $comment;
    }
}